<?php
//ini_set('max_execution_time', 300);
include "../../it_config.php";
require_once "session_check.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/logger/clsLogger.php";
require_once ("lib/core/strutil.php");

$currStore = getCurrUser();
if (!$currStore) {
    print "User session timedout. Please login again";
    return;
}
 
//$logger = new clsLogger();

$aColumns = array( 'id','invoice_no', 'approve_dt', 'store_name','invoice_amt', 'invoice_qty', 'created_by','approve_by','is_approved', 'details');
$sColumns = array('i.invoice_no','i.approve_dt','i.store_name');
/* Indexed column (used for fast and accurate table cardinality) */
//$sIndexColumn = "iid";
//$sTable = "it_invoices";
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
if ( isset($_GET['sSearch']) &&  $_GET['sSearch'] != "") {
    $sWhere = "Where (";
    for ($i = 0; $i < count($sColumns); $i++) {
        $sWhere .= $sColumns[$i] . " LIKE '%" . $db->getConnection()->real_escape_string($_GET['sSearch']) . "%' OR ";
    }
    $sWhere = substr_replace($sWhere, "", -3);
    $sWhere .= ')';
}

/* Individual column filtering */
for ($i = 0; $i < count($sColumns); $i++) {
    //if ($_GET['bSearchable_' . $i] == "true" && $_GET['sSearch_' . $i] != '') {
    if ( isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == "true" && isset($_GET['sSearch_'.$i]) && $_GET['sSearch_'.$i] != '' ){
        if ($sWhere == "") {
            $sWhere = "WHERE ";
        } else {
            $sWhere .= " AND ";
        }
        $sWhere .= $sColumns[$i] . " LIKE '%" . $db->getConnection()->real_escape_string($_GET['sSearch_' . $i]) . "%' ";
    }
}

if ($sWhere == "") {
    $sWhere = " where ";
} else {
    $sWhere .= " and ";
}




// $sWhere .= "  i.invoice_type in (0,6)";
 
//	if ($sOrder == "") { $sOrder = " order by iid desc "; }
//$logger->logInfo("sOrder=$sOrder");

/*
 * SQL queries
 * Get data to display
 */
 //$sOrder="";
$sQuery = "select SQL_CALC_FOUND_ROWS  i.* from it_portalinv_creditnote i $sWhere i.id=i.id group by i.id order by i.id desc ";
//echo $sQuery;

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
//    $spObj = $db->fetchObject("select * from it_codes where id = ".DEF_SP_LIFESTYLE_ID); // S.P. Life Style ID
    for ($i = 0; $i < count($aColumns); $i++) {
        if ($aColumns[$i] == "id") {
            //$invno = $db->safe(trim($obj->invoice_no));            
            $row[] = trim($obj->id);
          
        }else if ($aColumns[$i] == "invoice_no") {
            //$invno = $db->safe(trim($obj->invoice_no));            
            $row[] = trim($obj->invoice_no);
            
        } else if ($aColumns[$i] == "approve_dt") {            
                $row[] = $obj->approve_dt;            
        } else if ($aColumns[$i] == "invoice_amt") {  
               $inv_amt = sprintf("%0.02f", $obj->invoice_amt);
                $row[] = $inv_amt;            
        }else if ($aColumns[$i] == "invoice_qty") {            
                $row[] = $obj->invoice_qty;            
        }else if ($aColumns[$i] == "store_name") {            
                $row[] = $obj->store_name;            
        }else if ($aColumns[$i] == "created_by") { 
            $storeobj = $db->fetchObject("select store_name from it_codes where id = $obj->created_by ");
            $st_name="-";
            if(isset($storeobj)){
                $st_name=$storeobj->store_name;   }
            $row[] = $st_name;            
        }else if ($aColumns[$i] == "approve_by") { 
            $storeobj = $db->fetchObject("select store_name from it_codes where id = $obj->approve_by");
            $st_name="-";
            if(isset($storeobj)){
                $st_name=$storeobj->store_name;   }
            $row[] = $st_name;            
        } else if ($aColumns[$i] == "is_approved") {
            if($obj->is_approved==1){
               $row[] ="Approved"; 
            } else {
               $row[] ="-"; 
            }  
//              
        }else if ($aColumns[$i] == "details") {            
                $row[] = '<a onclick="showInvoiceDetails('.$obj->id.')" href="javascript:void(0);"><u>View</u></a>';            
        } else {         
            /* General output */
            $row[] = $obj->$aColumns[$i];
        }
//         error_log("\n row[$i]: ".$row[$i]."\n",3,"tmp_1.txt");////
    }
//   error_log("\nInvs query: ".$row[0]."\n",3,"tmp_1.txt");///
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
//error_log("\nInvs query: ".$output."\n",3,"tmp_1.txt");
//	$logger->logInfo(json_encode($output));
echo json_encode($output);
?>
