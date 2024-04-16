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

$aColumns = array( 'id','invoice_no', 'invoice_dt', 'invoice_amt', 'invoice_qty', 'store_name', 'is_sb_transit_complete', 'details','utr','remark','submit');
$sColumns = array('i.id','i.invoice_no', 'i.invoice_dt', 'i.invoice_amt','i.invoice_qty','i.createtime','i.is_sb_transit_complete','i.remark','c.store_name');
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
if (isset($_GET['sSearch']) && $_GET['sSearch'] != "") {
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
    $sWhere = " where ";
} else {
    $sWhere .= " and ";
}




$sWhere .= "  i.invoice_type in (7)";

//	if ($sOrder == "") { $sOrder = " order by iid desc "; }
//$logger->logInfo("sOrder=$sOrder");

/*
 * SQL queries
 * Get data to display
 */
$sQuery = "
            select SQL_CALC_FOUND_ROWS  i.*  
            from it_saleback_invoices i ,it_codes c
            $sWhere and i.store_id=c.id
                 group by i.id
            $sOrder    
            $sLimit
	";

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
    for ($i = 0; $i < count($aColumns); $i++) {
        if ($aColumns[$i] == "id") {
            $row[] = trim($obj->id);
        } else if ($aColumns[$i] == "invoice_no") {
            $str = "";
            $str = trim($obj->invoice_no);
            $str .= '<br> [ <a onclick="showInvoiceDetails(' . $obj->id . ')" href="javascript:void(0);"><u>View</u></a> ] ';
            $row[] = $str;
        } else if ($aColumns[$i] == "invoice_dt") {
            $row[] = $obj->invoice_dt;
        } else if ($aColumns[$i] == "invoice_amt") {
            $inv_amt = sprintf("%0.02f", $obj->invoice_amt);
            $row[] = $inv_amt;
        } else if ($aColumns[$i] == "invoice_qty") {
            $row[] = $obj->invoice_qty;
        } else if ($aColumns[$i] == "store_name") {
            $obj1 = $db->fetchObject("select store_name from it_codes where id = $obj->store_id ");
            if (isset($obj1)) {
                $st_name = $obj1->store_name;
            } else {
                $st_name = "-";
            }
            $row[] = "$st_name";
        } else if ($aColumns[$i] == "is_sb_transit_complete") {
            if($obj->is_sb_transit_complete==0){
                $row[] = 'In Transit';            
            }else if($obj->is_sb_transit_complete==1){
                $row[] = 'Received At Warehouse';            
            }else if($obj->is_sb_transit_complete==2){
                $row[] = 'Payment Done';            
            }
        } else if ($aColumns[$i] == "details") {
            $row[] = '<a onclick="showInvoiceDetails(' . $obj->id . ')" href="javascript:void(0);"><u>View</u></a>';
        }else if($aColumns[$i] == "utr"){
            if($currStore->usertype==UserType::Accounts || $currStore->id==100){
                if($obj->utr!=0){
                $row[] = '<input type="text" name="utr" id="utr'.$obj->id.'" value="' . htmlspecialchars($obj->utr) . '" readonly>';
            }else{
                $row[] = '<input type="text" name="utr" id="utr'.$obj->id.'" value="' . htmlspecialchars($obj->utr) . '">';
            }
            }else{
                $row[] = '<input type="text" name="utr" id="utr'.$obj->id.'" value="' . htmlspecialchars($obj->utr) . '" readonly>';
            }

//            $row[] = '<input type="text" name="utr" id="utr'.$obj->id.'" value="' . htmlspecialchars($obj->utr) . '">';
        }else if($aColumns[$i] == "remark"){
            if($currStore->usertype==UserType::Accounts || $currStore->id==100){
                if($obj->remark!=""){

                $row[] = '<input type="text" name="remark" id="remark'.$obj->id.'" value="' . htmlspecialchars($obj->remark) . '" readonly>';
            }else{
                $row[] = '<input type="text" name="remark" id="remark'.$obj->id.'" value="' . htmlspecialchars($obj->remark) . '">';
            }
            }else{
                $row[] = '<input type="text" name="remark" id="remark'.$obj->id.'" value="' . htmlspecialchars($obj->remark) . '" readonly>';
            }

            //$row[] = '<input type="text" name="remark" id="remark'.$obj->id.'" value="' . htmlspecialchars($obj->remark) . '">';
        }else if ($aColumns[$i] == "submit") {  
            if($currStore->usertype==UserType::Accounts || $currStore->id==100){
                if($obj->utr!=0 && $obj->remark!=""){
                $row[] = '<button disabled onclick="Saveinvoicedetails('.$obj->id.')" href="javascript:void(0);"><u>Submit</u></a>';
            }else if($obj->is_sb_transit_complete==0 || $obj->is_sb_transit_complete==2){
                $row[] = '<button disabled onclick="Saveinvoicedetails('.$obj->id.')" href="javascript:void(0);"><u>Submit</u></a>';
            }else{
                $row[] = '<button onclick="Saveinvoicedetails('.$obj->id.')" href="javascript:void(0);"><u>Submit</u></a>';
            }
            }else{
                $row[] = '<button disabled onclick="Saveinvoicedetails('.$obj->id.')" href="javascript:void(0);"><u>Submit</u></a>';
            }

//                $row[] = '<button onclick="Saveinvoicedetails('.$obj->id.')" href="javascript:void(0);"><u>Submit</u></a>';            
        } else {
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

echo json_encode($output);
?>