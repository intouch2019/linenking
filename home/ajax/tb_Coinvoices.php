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

$aColumns = array( 'id','store_name1','invoice_no', 'invoice_dt', 'invoice_amt', 'invoice_qty','sync_date', 'store_name', 'details','type');
$sColumns = array('i.id','i.bill_no', 'i.bill_datetime', 'i.amount','i.quantity','i.createtime');
/* Indexed column (used for fast and accurate table cardinality) */
//$sIndexColumn = "iid";
//$sTable = "it_invoices";
$db = new DBConn();

/*
 * Paging
 */
$sLimit = "";
$sOrder="";
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




 $sWhere .= "  i.tickettype in (6)";//co invoices
 
//	if ($sOrder == "") { $sOrder = " order by iid desc "; }
//$logger->logInfo("sOrder=$sOrder");

/*
 * SQL queries
 * Get data to display
 */
//$sQuery = "
//            select SQL_CALC_FOUND_ROWS  i.*  
//            from it_saleback_invoices i 
//            $sWhere
//                 group by i.id
//            $sOrder    
//            $sLimit
//	";
$sQuery = "select SQL_CALC_FOUND_ROWS  i.id,i.bill_no,i.bill_datetime,i.cust_name,i.amount,i.quantity,i.store_id,i.tickettype,i.createtime from it_orders i 
            $sWhere
                 group by i.id
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
    //print DEF_SP_LIFESTYLE_ID;
    //$spObj = $db->fetchObject("select * from it_codes where id = ".DEF_SP_LIFESTYLE_ID); // S.P. Life Style ID
    //print_r($spObj);
    for ($i = 0; $i < count($aColumns); $i++) {
        if ($aColumns[$i] == "id") {
            //$invno = $db->safe(trim($obj->invoice_no));            
            $row[] = trim($obj->id);
            
        }else if ($aColumns[$i] == "invoice_no") {
            //$invno = $db->safe(trim($obj->invoice_no));            
            $row[] = trim($obj->bill_no);
            
        } else if ($aColumns[$i] == "invoice_dt") {            
                $row[] = $obj->bill_datetime;            
        } else if ($aColumns[$i] == "invoice_amt") {  
               $inv_amt = sprintf("%0.02f", $obj->amount);
                $row[] = $inv_amt;            
        }else if ($aColumns[$i] == "invoice_qty") {            
                $row[] = $obj->quantity;            
        }else if ($aColumns[$i] == "sync_date") {            
                $row[] = mmddyy($obj->createtime);            
        }else if ($aColumns[$i] == "store_name") { 
           // $itemobj = $db->fetchObject("select * from it_saleback_invoice_items where invoice_id = $obj->id ");
            //if (strpos($itemobj->item_code,"89000") !== false) {
                if(isset($obj))
                {
                    $cust_name = $obj->cust_name;
                }
                else {
                    $cust_name="";
                }
                //$st_name="Test store";
//            }else{
//                $obj1 = $db->fetchObject("select store_name from it_codes where id = $obj->store_id ");
//                $st_name = $obj1->store_name;
//            }
                $row[] = "$cust_name";            
        }
        else if ($aColumns[$i] == "store_name1") { 
           $obj1 = $db->fetchObject("select store_name from it_codes where id = $obj->store_id ");
            //if (strpos($itemobj->item_code,"89000") !== false) {
                if(isset($obj))
                {
                    $st_name = $obj1->store_name;
                }
                else {
                    $st_name="";
                }
                //$st_name="Test store";
//            }else{
//                $obj1 = $db->fetchObject("select store_name from it_codes where id = $obj->store_id ");
//                $st_name = $obj1->store_name;
//            }
                $row[] = "$st_name";            
        }else if ($aColumns[$i] == "details") {            
                $row[] = '<a onclick="showInvoiceDetails('.$obj->id.')" href="javascript:void(0);"><u>View</u></a>';            
        }else if ($aColumns[$i] == "type") {
            //$invno = $db->safe(trim($obj->invoice_no));            
            $row[] = trim($obj->tickettype);
            
        }
        /*else if (!$obj->$aColumns[$i]) {
            $row[] = "-";
        } */
         else {         
            /* General output */
            $row[] = $obj->$aColumns[$i];
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

//	$logger->logInfo(json_encode($output));
echo json_encode($output);
?>