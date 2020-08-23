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

$aColumns = array('d.design_no','ctg.name', 'i.MRP', 'd.lineno', 'd.rackno','i.is_design_mrp_active');
$sColumns = array('d.design_no','ctg.name', 'i.MRP', 'd.lineno', 'd.rackno','i.is_design_mrp_active');
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

$state = isset($_GET['is_design_mrp_active']) ? $_GET['is_design_mrp_active'] : 0;


 $sWhere .= " d.ctg_id=ctg.id and d.design_no = i.design_no and d.ctg_id = i.ctg_id and d.id = i.design_id and  i.is_design_mrp_active = $state ";
 
//	if ($sOrder == "") { $sOrder = " order by iid desc "; }
//$logger->logInfo("sOrder=$sOrder");

/*
 * SQL queries
 * Get data to display
 */
$sQuery = "
            select d.*,ctg.name as ctg_name,i.MRP,i.is_design_mrp_active 
            from it_ck_designs d,it_categories ctg , it_items i               
            $sWhere   
                 group by d.design_no,d.ctg_id,i.MRP
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
    for ($i = 0; $i < count($aColumns); $i++) {
        if ($aColumns[$i] == "d.design_no") {
            //$invno = $db->safe(trim($obj->invoice_no));  
            if(trim($obj->design_no)!=""){
                $row[] = trim($obj->design_no);
            }else{
                $row[] = "-";
            }
            
            
        }else if ($aColumns[$i] == "ctg.name") {
            //$invno = $db->safe(trim($obj->invoice_no));   
            if(trim($obj->ctg_name)!=""){
             $row[] = trim($obj->ctg_name);
            }else{
                $row[] = "-";
            }
            
        } else if ($aColumns[$i] == "i.MRP") { 
               if(trim($obj->MRP)!=""){ 
                $row[] = trim($obj->MRP);            
               }else{
                $row[] = "-";
               } 
        } else if ($aColumns[$i] == "d.lineno") {
            
                if(trim($obj->lineno)!=""){
                  $row[] = trim($obj->lineno);              
                }else{
                  $row[] = "-";
                }                            
        }else if ($aColumns[$i] == "d.rackno") {    
            
                if(trim($obj->rackno)!=""){
                  $row[] = trim($obj->rackno);            
                }else{
                  $row[] = "-";
                }  
        }else if ($aColumns[$i] == "i.is_design_mrp_active") {  
               if(trim($obj->is_design_mrp_active)!=""){
                   $str="-";
                    if(trim($obj->is_design_mrp_active)==0){
                        $str="Inactive";
                    }else if(trim($obj->is_design_mrp_active)==1){
                        $str="Active";
                    }   
                 $row[] = $str ;            
               }else{
                  $row[] = "-"; 
               } 
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
