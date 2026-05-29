<?php
include "../../it_config.php";
require_once "session_check.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";

$calc = isset($_GET['calc']) ? strtolower(trim($_GET['calc'])) : "percentage";
if ($calc !== "average") { $calc = "percentage"; }

if ($calc === "average") {
    $aColumns = array('store_name', 'dtrange', 'avg_min_stock_limit', 'avg_max_stock_limit', 'avg_stock_value', 'avg_percentage', 'avg_difference', 'avg_status');
    $sColumns = array('c.store_name');
} else {
    $aColumns = array('store_name', 'stock_datetime', 'min_stock_limit', 'max_stock_limit', 'stock_value', 'percentage', 'difference', 'status');
    $sColumns = array('c.store_name', 's.stock_datetime', 's.min_stock_limit', 's.max_stock_limit', 's.stock_value', 'percentage', 'difference', 'status');
}

$db = new DBConn();

$storeid = isset($_GET['storeid']) ? $_GET['storeid'] : false;
$dtrange = isset($_GET['dtrange']) ? $_GET['dtrange'] : false;

/*
 * Paging
 */
$sLimit = "";
if (isset($_GET['iDisplayStart']) && $_GET['iDisplayLength'] != '-1') {
    $sLimit = " LIMIT " . $db->getConnection()->real_escape_string($_GET['iDisplayStart']) . ", " .
            $db->getConnection()->real_escape_string($_GET['iDisplayLength']);
}

/*
 * Ordering
 */
$sOrder = "";
if (isset($_GET['iSortCol_0'])) {
    $sOrder = " ORDER BY ";
    for ($i = 0; $i < intval($_GET['iSortingCols']); $i++) {
        if ($_GET['bSortable_' . intval($_GET['iSortCol_' . $i])] == "true") {
            $sortCol = $aColumns[intval($_GET['iSortCol_' . $i])];
            if ($calc === "average" && $sortCol === "dtrange") { $sortCol = "store_name"; }
            $sOrder .= $sortCol . "
			 	" . $db->getConnection()->real_escape_string($_GET['sSortDir_' . $i]) . ", ";
        }
    }

    $sOrder = substr_replace($sOrder, "", -2);
    if ($sOrder == " ORDER BY ") {
        $sOrder = "";
    }
}

/*
 * Filtering
 */
$sWhere = "";
if (isset($_GET['sSearch']) && $_GET['sSearch'] != "") {
    $sWhere = "WHERE (";
    for ($i = 0; $i < count($sColumns); $i++) {
        $sWhere .= $sColumns[$i] . " LIKE '%" . $db->getConnection()->real_escape_string($_GET['sSearch']) . "%' OR ";
    }
    $sWhere = substr_replace($sWhere, "", -3);
    $sWhere .= ')';
}

for ($i = 0; $i < count($sColumns); $i++) {
    if (isset($_GET['bSearchable_' . $i]) && $_GET['bSearchable_' . $i] == "true" && isset($_GET['sSearch_' . $i]) && $_GET['sSearch_' . $i] != '') {
        if ($sWhere == "") {
            $sWhere = "WHERE ";
        } else {
            $sWhere .= " AND ";
        }
        $sWhere .= $sColumns[$i] . " LIKE '%" . $db->getConnection()->real_escape_string($_GET['sSearch_' . $i]) . "%' ";
    }
}

if ($sWhere == "") {
    $sWhere .= " where ";
} else {
    $sWhere .= " and ";
}

$dtClause = "";
if (isset($dtrange) && trim($dtrange) != "") {
    $dtarr = explode(" - ", $dtrange);
    if (count($dtarr) == 1) {
        list($dd, $mm, $yy) = explode("-", $dtarr[0]);
        $sdate = "$yy-$mm-$dd";
        $dtClause = " and s.stock_datetime >= '$sdate 00:00:00' and s.stock_datetime <= '$sdate 23:59:59' ";
    } else if (count($dtarr) == 2) {
        list($dd, $mm, $yy) = explode("-", $dtarr[0]);
        $sdate = "$yy-$mm-$dd";
        list($dd, $mm, $yy) = explode("-", $dtarr[1]);
        $edate = "$yy-$mm-$dd";
        $dtClause = " and s.stock_datetime >= '$sdate 00:00:00' and s.stock_datetime <= '$edate 23:59:59' ";
    } else {
        $dtClause = "";
    }
}

// Show data after 23 April 2026 only.
$dtClause .= " and s.stock_datetime >= '2026-04-23 00:00:00' ";

$sClause = "";
if (isset($storeid) && trim($storeid) != "" && trim($storeid) != "-1") {
    $sClause = " and s.store_id in ($storeid)";
}

$sWhere .= " s.store_id = c.id and c.is_closed= 0 and c.id in (select store_id from executive_assign where exe_id=" . getCurrUser()->id . " ) $dtClause $sClause";

if ($calc === "average") {
    $sQuery = "
        select SQL_CALC_FOUND_ROWS
            c.store_name as store_name,
            round(avg(s.min_stock_limit),2) as avg_min_stock_limit, 
            round(avg(s.max_stock_limit),2) as avg_max_stock_limit,
            round(avg(s.stock_value),2) as avg_stock_value,
            round(avg((s.stock_value/nullif(s.min_stock_limit,0))*100),2) as avg_percentage,
            (round(avg(s.stock_value),2) - round(avg(s.min_stock_limit),2)) as avg_difference
        from it_store_stock_summary s, it_codes c
        $sWhere
        group by c.id
        $sOrder
        $sLimit
    ";
} else {
    $sQuery = "
        select SQL_CALC_FOUND_ROWS  s.store_id, s.stock_datetime, s.min_stock_limit, s.max_stock_limit, s.stock_value,
                   c.store_name,(s.stock_value-s.min_stock_limit) as difference,
                 round((s.stock_value/nullif(s.min_stock_limit,0))*100,2) as percentage
        from it_store_stock_summary s, it_codes c
        $sWhere
        $sOrder
        $sLimit
    ";
}

$objs = $db->fetchObjectArray($sQuery);

/* Data set length after filtering */
$obj = $db->fetchObject("SELECT FOUND_ROWS() AS TOTAL_ROWS");
$iFilteredTotal = $obj ? $obj->TOTAL_ROWS : 0;

$rows = array();
$iTotal = 0;
foreach ($objs as $obj) {
    $row = array();
    for ($i = 0; $i < count($aColumns); $i++) {
        $col = $aColumns[$i];
//        if ($col == 'id') {
//            $row[] = $obj->id;
//        } else 
            if ($col == 'store_name') {
            $row[] = $obj->store_name;
        } else if ($calc === "average" && $col == 'dtrange') {
            $row[] = ($dtrange && trim($dtrange) !== "") ? $dtrange : "-";
        } else if ($calc === "average" && $col == 'avg_min_stock_limit') {
            $row[] = ($obj->avg_min_stock_limit === null || $obj->avg_min_stock_limit === "") ? "-" : $obj->avg_min_stock_limit;
        } else if ($calc === "average" && $col == 'avg_max_stock_limit') {
            $row[] = ($obj->avg_max_stock_limit === null || $obj->avg_max_stock_limit === "") ? "-" : $obj->avg_max_stock_limit;
        } else if ($calc === "average" && $col == 'avg_stock_value') {
            $row[] = ($obj->avg_stock_value === null || $obj->avg_stock_value === "") ? "-" : $obj->avg_stock_value;
        } else if ($calc === "average" && $col == 'avg_percentage') {
            if ($obj->avg_percentage === null || $obj->avg_percentage === "") {
                $row[] = "-";
            } else {
                $pct = floatval($obj->avg_percentage);
                $pctText = $obj->avg_percentage . "%";
                if ($pct < 100) {
                    $row[] = "<span style=\"color:red; font-weight:bold;\">" . $pctText . "</span>";
                } else {
                    $row[] = $pctText;
                }
            }
        } else if ($col == 'stock_datetime') {
            $row[] = $obj->stock_datetime;
        } else if ($col == 'min_stock_limit') {
            $row[] = $obj->min_stock_limit;
        } else if ($col == 'max_stock_limit') {
            $row[] = $obj->max_stock_limit;
        } else if ($col == 'stock_value') {
            $row[] = $obj->stock_value;
        } else if ($col == 'percentage') {
            if ($obj->percentage === null || $obj->percentage === "") {
                $row[] = "-";
            } else {
                $pct = floatval($obj->percentage);
                $pctText = $obj->percentage . "%";
                if ($pct < 100) {
                    $row[] = "<span style=\"color:red; font-weight:bold;\">" . $pctText . "</span>";
                } else {
                    $row[] = $pctText;
                }
            }
        }
        else if ($col == 'difference') {
            
            if($obj->difference < 0){
                $row[] = "<span style=\"color:red; font-weight:bold;\">" . $obj->difference . "</span>";
            }else{
               $row[] = $obj->difference; 
            }
            
        }
         else if ($col == 'avg_difference') {
          
            if($obj->avg_difference <0){
                $row[] = "<span style=\"color:red; font-weight:bold;\">" . $obj->avg_difference . "</span>";
            }else{
               $row[] = $obj->avg_difference; 
            }
        }
        else if ($col == 'status') {
            $pct = floatval($obj->percentage);
            if ($pct < 100) {
            $row[] = "Stock Not Maintained";
            }else {
            $row[] = "Stock Maintained";    
            }
        }
         else if ($col == 'avg_status') {
            $pct = floatval($obj->avg_percentage);
            if ($pct < 100) {
            $row[] = "Stock Not Maintained";
            }else {
            $row[] = "Stock Maintained";    
            }
        }
//        else if ($col == 'stock_qty') {
//            $row[] = $obj->stock_qty;
//        } else if ($col == 'stock_intransit') {
//            $row[] = $obj->stock_intransit;
//        } 
        else {
            $row[] = "-";
        }
    }
    $rows[] = $row;
    $iTotal++;
}

$db->closeConnection();

$output = array(
    "iTotalRecords" => $iTotal,
    "iTotalDisplayRecords" => $iFilteredTotal,
    "aaData" => $rows
);

echo json_encode($output);
?>
