<?php

include "../../it_config.php";
require_once "session_check.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";

$currStore = getCurrUser();
//1) Store Name 2) Store Address  3) Store Contact Number 4) Owner Name

//$aColumns = array( 'id', 'store_name', 'curr_stock','stock_intransit', 'tot_stock','min_stock_level','difference');

$aColumns = array('id', 'store_name', 'store_address', 'owner_name', 'contact_no','Action Edit');

//$sColumns = array('c.id', 'c.store_name', 'c.min_stock_level');
$sColumns = array( 'c.store_name','c.address');
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
$sOrder=" order by createtime desc";

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

//if ($sWhere == "") {
//    $sWhere .= " where c.usertype=4 and is_closed=0";
//} else {
//    $sWhere .= " and ";
//}.
if ($sWhere == "") {
    $sWhere .= " where ";
} else {
    $sWhere .= " and ";
}
$sWhere .= " usertype = " . UserType::Dealer . "   and is_closed = 0";
//if ($currStore->usertype == UserType::BHMAcountant) {
//    $sWhere .= " usertype = " . UserType::Dealer . "   and is_closed = 0 and  min_stock_level is not null  and  (is_bhmtallyxml=1 or store_type=3) "; //and inactive = 0
//} else {
//    $sWhere .= " usertype = " . UserType::Dealer . "   and is_closed = 0 and  min_stock_level is not null "; //and inactive = 0   
//}

$sQuery = "
	select SQL_CALC_FOUND_ROWS c.id,c.store_name,c.owner,c.phone,c.address
	from it_codes c 
	$sWhere 
	$sOrder
	$sLimit
";
//  error_log("\nMSL query: ".$sQuery."\n",3,"tmp_1.txt");
$objs = $db->fetchObjectArray($sQuery);

/* Data set length after filtering */
$sQuery = "
	SELECT FOUND_ROWS() AS TOTAL_ROWS
";
$obj = $db->fetchObject($sQuery);
$iFilteredTotal = $obj->TOTAL_ROWS;
$sr=0;
$rows = array();
$iTotal = 0;
foreach ($objs as $obj) {
   $sr++;
    $row = array();
    for ($i = 0; $i < count($aColumns); $i++) {
        if ($aColumns[$i] == 'id') {
//            $row[] = $obj->id;
            $row[] =$sr;
        } else if ($aColumns[$i] == 'store_name') {
            $row[] = $obj->store_name;
        }
         else if ($aColumns[$i] == 'store_address') {
            $row[] = $obj->address;
        }
         else if ($aColumns[$i] == 'owner_name') {
            $row[] = $obj->owner;
        }
         else if ($aColumns[$i] == 'contact_no') {
            $row[] = $obj->phone;
        }
          else if($aColumns[$i] == "Action Edit"){
                  
          
             
             
             if($currStore->usertype == UserType::Admin || $currStore->usertype == UserType::CKAdmin ) { 
          //   if(true ) { 
              $row[] ='<button class="btn btn-primary" style="width:70px" onclick="storeEdit('.$obj->id.')">Edit</button>';
       
             }
             else{
                 $row[] ="";
                 
             }
           
       
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