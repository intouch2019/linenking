<?php
//ini_set('max_execution_time', 300);
include "../../it_config.php";
require_once "session_check.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
//require_once "lib/logger/clsLogger.php";
require_once ("lib/core/strutil.php");

$currStore = getCurrUser();
if (!$currStore) {
    print "User session timedout. Please login again";
    return;
}

//$logger = new clsLogger();
$aColumns = array( 'id','store_name', 'scheme_name', 'scheme_start_date','scheme_end_date','delete');
$sColumns = array('c.store_name');

$db = new DBConn();

/*
 * Paging
 */
$sLimit = "";
if (isset($_GET['iDisplayStart']) && $_GET['iDisplayLength'] != '-1') {
    $sLimit = "LIMIT " . $db->getConnection()->real_escape_string($_GET['iDisplayStart']) . ", " .
            $db->getConnection()->real_escape_string($_GET['iDisplayLength']);
}


/*
 * Ordering
 */
if (isset($_GET['iSortCol_0'])) {
    $sOrder = "ORDER BY  ";
    for ($i = 0; $i < intval($_GET['iSortingCols']); $i++) {
        if ($_GET['bSortable_' . intval($_GET['iSortCol_' . $i])] == "true") {
            $sOrder .= $aColumns[intval($_GET['iSortCol_' . $i])] . "
				 	" . $db->getConnection()->real_escape_string($_GET['sSortDir_' . $i]) . ", ";
        }
    }

    $sOrder = substr_replace($sOrder, "", -2);
    if ($sOrder == "ORDER BY") {
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
if ($sWhere == "") {
    $sWhere = " where sm.id=ss.scheme_id and ss.store_id=c.id and c.usertype=4 and is_data_deleted=0 ";
} else {
    $sWhere .= " and ";
}
if ( isset($_GET['sSearch']) &&  $_GET['sSearch'] != "") {
    $sWhere .= " and (";
    for ($i = 0; $i < count($sColumns); $i++) {
        $sWhere .= $sColumns[$i] . " LIKE '%" . $db->getConnection()->real_escape_string($_GET['sSearch']) . "%' OR ";
    }
    $sWhere = substr_replace($sWhere, "", -3);
    $sWhere .= ')';
}



/* Individual column filtering */
//for ($i = 0; $i < count($sColumns); $i++) {
//    //if ($_GET['bSearchable_' . $i] == "true" && $_GET['sSearch_' . $i] != '') {
//    if ( isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == "true" && isset($_GET['sSearch_'.$i]) && $_GET['sSearch_'.$i] != '' ){
//        if ($sWhere == "") {
//            $sWhere = "WHERE ";
//        } else {
//            $sWhere .= " AND ";
//        }
//        $sWhere .= $sColumns[$i] . " LIKE '%" . $db->getConnection()->real_escape_string($_GET['sSearch_' . $i]) . "%' ";
//    }
//}






// $sWhere .= "  i.invoice_type in (0,6)";

$sQuery = "
            select SQL_CALC_FOUND_ROWS  ss.id,c.store_name,sm.scheme_name,sm.start_date,sm.end_date
            from membership_scheme_masters sm,storewise_membership_schemes ss, it_codes c 
            $sWhere
                 group by ss.id
            $sOrder    
            $sLimit
	";
//echo $sQuery;
//error_log("\nInvs query: ".$sQuery."\n",3,"tmp.txt");
//$logger->logInfo($sQuery);
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

    $row = array();
    $spObj = $db->fetchObject("select * from it_codes where id = ".DEF_SP_LIFESTYLE_ID); // S.P. Life Style ID

    for ($i = 0; $i < count($aColumns); $i++) {
        if ($aColumns[$i] == "id") {           
            $row[] = !empty($obj->id) ? trim($obj->id) : "NA";

        } else if ($aColumns[$i] == "store_name") {            
            $row[] = !empty($obj->store_name) ? trim($obj->store_name) : "NA";

        } else if ($aColumns[$i] == "scheme_name") {            
            $row[] = !empty($obj->scheme_name) ? $obj->scheme_name : "NA";            

        } else if ($aColumns[$i] == "scheme_start_date") {               
            $row[] = (!empty($obj->start_date) && $obj->start_date !== "0000-00-00 00:00:00") ? date("m/d/Y h:i:s A", strtotime($obj->start_date)) : "-";

        }else if ($aColumns[$i] == "scheme_end_date") {               
            $row[] = (!empty($obj->end_date) && $obj->end_date !== "0000-00-00 00:00:00") ? date("m/d/Y h:i:s A", strtotime($obj->end_date)) : "-";

        }else if ($aColumns[$i] == "delete") {  
           
                $row[] = '<button onclick="deletestoreassigscheme('.$obj->id.')" href="javascript:void(0);"><u>Delete</u></a>';
        
        }else {         
            /* General output */
            $row[] = !empty($obj->$aColumns[$i]) ? $obj->$aColumns[$i] : "NA";
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
//error_log("\nInvs query: " . json_encode($output) . "\n", 3, "tmp.txt");
//	$logger->logInfo(json_encode($output));
echo json_encode($output);
?>
