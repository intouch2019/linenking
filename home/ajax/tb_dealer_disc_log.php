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

$aColumns = array( 'store_name','dealer_discount','additional_discount', 'modified_by','modified_datetime');
//$aColumns = array( 'id','order_qty','order_no', 'store_name','order_amount', 'active_time','order_num_design','details');
$sColumns = array('c.store_name');
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
$sOrder="";
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




 $sWhere .= "s.storeid=c.id";
 
//	if ($sOrder == "") { $sOrder = " order by iid desc "; }
//$logger->logInfo("sOrder=$sOrder");

/*
 * SQL queries
 * Get data to display
 */
$sQuery = "
            select SQL_CALC_FOUND_ROWS s.*,c.store_name from it_dealer_discount_log s, it_codes c
            
             $sWhere
             $sOrder
             
             $sLimit
	";
//echo $sQuery;
//error_log("\nInvs query: ".$sQuery."\n",3,"tmp.txt");
//$logger->logInfo($sQuery);
$objs = $db->fetchObjectArray($sQuery);
//print_r($objs);
/* Data set length after filtering */
$sQuery = "
		SELECT FOUND_ROWS() AS TOTAL_ROWS
	";
$obj = $db->fetchObject($sQuery);
$iFilteredTotal = $obj->TOTAL_ROWS;

$rows = array();
$iTotal = 0;
$count=1;
foreach ($objs as $obj) {
    $row = array();

    for ($i = 0; $i < count($aColumns); $i++) {
        if ($aColumns[$i] == "dealer_discount") {
                       
            $row[] ="".$obj->old_disc."%";
            //$count++;
            
        }else if ($aColumns[$i] == "store_name") {
            //$invno = $db->safe(trim($obj->invoice_no)); 
            $str = "";
            $str = trim($obj->store_name);
            /**$str .= '<br>[ <a href="ck/invoice/id=<?php echo $obj->id; ?>/">View</a> ]';**/
            //$str .= '<br> [ <a onclick="showCKInvoiceDetails('. $obj->id.')" href="javascript:void(0);"><u>View</u></a> ] ';
            $row[] = $str;
            
       }
       else if($aColumns[$i] == "additional_discount")
       {
           $row[] ="".$obj->new_disc."%";
       }
        else if ($aColumns[$i] == "modified_by") {  
               $inv_amt = $obj->modified_by;
                $row[] = $inv_amt;            
        }else if ($aColumns[$i] == "modified_datetime") {            
                $row[] = $obj->modified_datetime;            
       }


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
