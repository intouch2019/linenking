<?php

include "../../it_config.php";
require_once "session_check.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";

$currStore = getCurrUser();

//$aColumns = array( 'id', 'store_name', 'curr_stock','stock_intransit', 'tot_stock','min_stock_level','difference');
//$aColumns = array('id', 'store_name', 'appreal_curr_stock', 'mask_curr_stock', 'total_curr_stock', 'appreal_stock_intransit', 'mask_stock_intransit', 'total_stock_intransit', 'appreal_tot_stock', 'mask_tot_stock', 'tot_stock', 'min_stock_level', 'difference');
//$aColumns = array('id', 'store_name', 'appreal_curr_stock', 'total_curr_stock', 'appreal_stock_intransit', 'total_stock_intransit', 'appreal_tot_stock', 'tot_stock', 'min_stock_level', 'max_stock_level', 'difference');
$aColumns = array('id', 'store_name', 'appreal_curr_stock', 'appreal_stock_intransit', 'appreal_tot_stock', 'min_stock_level', 'max_stock_level', 'min_difference', 'max_difference');

//$sColumns = array('c.id', 'c.store_name', 'c.min_stock_level');
$sColumns = array('c.id', 'c.store_name');
/* Indexed column (used for fast and accurate table cardinality) */
$db = new DBConn();

/*
 * Paging....
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
    $sOrder = " ORDER BY  ";
    for ($i = 0; $i < intval($_GET['iSortingCols']); $i++) {
        if ($_GET['bSortable_' . intval($_GET['iSortCol_' . $i])] == "true") {
            $sOrder .= $aColumns[intval($_GET['iSortCol_' . $i])] . "
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
 * NOTE this does not match the built-in DataTables filtering which does it
 * word by word on any field. It's possible to do here, but concerned about efficiency
 * on very large tables, and MySQL's regex functionality is very limited
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

/* Individual column filtering */
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

/*
 * SQL queries
 * Get data to display
 */

if ($sWhere == "") {
    $sWhere .= " where ";
} else {
    $sWhere .= " and ";
}
if ($currStore->usertype == UserType::BHMAcountant) {
    $sWhere .= " usertype = " . UserType::Dealer . "   and is_closed = 0 and  min_stock_level is not null  and  (is_bhmtallyxml=1 or store_type=3) "; //and inactive = 0
} elseif ($currStore->usertype == UserType::Dealer) {
    $sWhere .= " usertype = " . UserType::Dealer . "   and is_closed = 0 and  min_stock_level is not null and id =$currStore->id  "; //and inactive = 0   
} else {
    $sWhere .= " usertype = " . UserType::Dealer . "   and is_closed = 0 and  min_stock_level is not null "; //and inactive = 0   
}

$sQuery = "
	select SQL_CALC_FOUND_ROWS c.id,c.store_name,c.min_stock_level,c.max_stock_level
	from it_codes c
	$sWhere 
	$sOrder
	$sLimit
";
//   error_log("\nMSL query: ".$sQuery."\n",3,"tmp_1.txt");
$objs = $db->fetchObjectArray($sQuery);

/* Data set length after filtering */
$sQuery = "
	SELECT FOUND_ROWS() AS TOTAL_ROWS
";
$obj = $db->fetchObject($sQuery);
$iFilteredTotal = $obj->TOTAL_ROWS;

$rows = array();
$iTotal = 0;
foreach ($objs as $obj) {
    $tot_stk = 0;
    $tot_curr_stk = 0;
    $tot_intransit_stk = 0;
    $tot_mask_stk = 0;
    $appreal_tot_stock_incl_intransit = 0;
    $row = array();
    for ($i = 0; $i < count($aColumns); $i++) {
        if ($aColumns[$i] == 'id') {
            $row[] = $obj->id;
        } else if ($aColumns[$i] == 'store_name') {
            $row[] = $obj->store_name;
        }

        //appreal_curr_stock
        else if ($aColumns[$i] == 'appreal_curr_stock') {//---------1
            $cquery = "select sum(c.quantity * i.MRP) as appreal_curr_stock from it_current_stock c , it_items i where c.store_id = $obj->id  and c.barcode = i.barcode and i.ctg_id not in (42,43)";

            $cobj = $db->fetchObject($cquery);
            if (isset($cobj) && trim($cobj->appreal_curr_stock) != "") {
                $store_appreal_stock_val = $cobj->appreal_curr_stock;
            } else {
                $store_appreal_stock_val = 0;
            }
            // stock including Mask
            $cquery = "select sum(c.quantity * i.MRP) as mask_curr_stock from it_current_stock c , it_items i where c.store_id = $obj->id  and c.barcode = i.barcode and i.ctg_id in(42,43)";
            //  error_log("\nMSL query: " . $cquery . "\n", 3, "tmp_1.txt");
            $cobj = $db->fetchObject($cquery);
            if (isset($cobj) && trim($cobj->mask_curr_stock) != "") {
                $store_mask_stock_val = $cobj->mask_curr_stock;
            } else {
                $store_mask_stock_val = 0;
            }
            //$row[] = $store_mask_stock_val;
            //$tot_curr_stk += $store_mask_stock_val;

            $row[] = $store_appreal_stock_val + $store_mask_stock_val;
            $tot_curr_stk += $store_appreal_stock_val + $store_mask_stock_val;
        }
        //mask_curr_stock 
        else if ($aColumns[$i] == 'mask_curr_stock') {
            $cquery = "select sum(c.quantity * i.MRP) as mask_curr_stock from it_current_stock c , it_items i where c.store_id = $obj->id  and c.barcode = i.barcode and i.ctg_id in(42,43)";
            //  error_log("\nMSL query: " . $cquery . "\n", 3, "tmp_1.txt");
            $cobj = $db->fetchObject($cquery);
            if (isset($cobj) && trim($cobj->mask_curr_stock) != "") {
                $store_mask_stock_val = $cobj->mask_curr_stock;
            } else {
                $store_mask_stock_val = 0;
            }
            $row[] = $store_mask_stock_val;
            $tot_curr_stk += $store_mask_stock_val;
        }
        //total_curr_stock
        else if ($aColumns[$i] == 'total_curr_stock') {

            $row[] = $tot_curr_stk;
        }


        //appreal_stock_intransit
        else if ($aColumns[$i] == 'appreal_stock_intransit') {//---------------------------1
            $tquery2 = "select sum(i.MRP*oi.quantity) as appreal_stock_intransit from it_invoices o , it_invoice_items oi , it_items i where oi.invoice_id = o.id and o.invoice_type in ( 0 , 6, 7) and o.store_id = $obj->id and o.is_procsdForRetail = 0 and oi.item_code = i.barcode and i.ctg_id not in (42,43)";
            $tobj = $db->fetchObject($tquery2);
            if (isset($tobj) && trim($tobj->appreal_stock_intransit) != "") {
                $intransit_appreal_val = $tobj->appreal_stock_intransit;
            } else {
                $intransit_appreal_val = 0;
            }
            //$row[] = $intransit_appreal_val;
            //$tot_mask_stk += $intransit_appreal_val;
            //mask stock intransit
            $tquery2 = "select sum(i.MRP*oi.quantity) as mask_stock_intransit from it_invoices o , it_invoice_items oi , it_items i where oi.invoice_id = o.id and o.invoice_type in ( 0 , 6, 7) and o.store_id = $obj->id and o.is_procsdForRetail = 0 and oi.item_code = i.barcode and i.ctg_id in(42,43)";
            $tobj = $db->fetchObject($tquery2);
            if (isset($tobj) && trim($tobj->mask_stock_intransit) != "") {
                $intransit_mask_val = $tobj->mask_stock_intransit;
            } else {
                $intransit_mask_val = 0;
            }
            $row[] = $intransit_mask_val + $intransit_appreal_val;
            $tot_mask_stk += $intransit_mask_val + $intransit_appreal_val;
        }
        //mask_stock_intransit  
        else if ($aColumns[$i] == 'mask_stock_intransit') {
            $tquery2 = "select sum(i.MRP*oi.quantity) as mask_stock_intransit from it_invoices o , it_invoice_items oi , it_items i where oi.invoice_id = o.id and o.invoice_type in ( 0 , 6, 7) and o.store_id = $obj->id and o.is_procsdForRetail = 0 and oi.item_code = i.barcode and i.ctg_id in(42,43)";
            $tobj = $db->fetchObject($tquery2);
            if (isset($tobj) && trim($tobj->mask_stock_intransit) != "") {
                $intransit_mask_val = $tobj->mask_stock_intransit;
            } else {
                $intransit_mask_val = 0;
            }
            $row[] = $intransit_mask_val;
            $tot_mask_stk += $intransit_mask_val;
        }
        //total_stock_intransit' 
        else if ($aColumns[$i] == 'total_stock_intransit') {

            $row[] = $tot_intransit_stk;
        }

        //appreal_tot_stock
        else if ($aColumns[$i] == 'appreal_tot_stock') {

            //$appreal_tot_stock = $store_appreal_stock_val + $intransit_appreal_val;
            $appreal_tot_stock_incl_intransit += $tot_intransit_stk + $tot_curr_stk;

            $row[] = $appreal_tot_stock_incl_intransit;
        }
        //mask_tot_stock
//        else if ($aColumns[$i] == 'mask_tot_stock') {
//
//            $mask_tot_stock = $store_mask_stock_val + $intransit_mask_val;
//            $row[] = $mask_tot_stock;
//        }
        //tot_stock
        else if ($aColumns[$i] == 'tot_stock') {
            $tot_stk = $appreal_tot_stock;  //+ $mask_tot_stock;
            $row[] = $tot_stk;
        }
        //min_stock_level
        else if ($aColumns[$i] == 'min_stock_level') {
            $row[] = $obj->min_stock_level;
        }
        //max_stock_level
        else if ($aColumns[$i] == 'max_stock_level') {
            if ($obj->max_stock_level == null) {
                $row[] = 0;
            } else {
                $row[] = $obj->max_stock_level;
            }
        }
        //min_difference
        else if ($aColumns[$i] == 'min_difference') {
            $row[] = $appreal_tot_stock_incl_intransit - $obj->min_stock_level;
            // $row[] = $tot_stk - $obj->min_stock_level;
        }
        //max_difference
        else if ($aColumns[$i] == 'max_difference') {
            $row[] = $appreal_tot_stock_incl_intransit - $obj->max_stock_level;
            //$row[] = $tot_stk - $obj->max_stock_level;
        } else {
            $row[] = "-";
        }
    }
    $rows[] = $row;
    $iTotal++;
}

$db->closeConnection();
/*
 * Output
 */
$output = array(
    //"sEcho" => intval($_GET['sEcho']),
    "iTotalRecords" => $iTotal,
    "iTotalDisplayRecords" => $iFilteredTotal,
    "aaData" => $rows
);

echo json_encode($output);
?>