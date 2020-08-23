<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "Classes/PHPExcel.php";
require_once 'Classes/PHPExcel/Writer/Excel2007.php';
require_once "lib/core/strutil.php";

extract($_GET);
$db = new DBConn();

$storeids = $_GET['storeids'] ? $_GET['storeids'] : 0;
$qtr = $_GET['atypes'] ? $_GET['atypes'] : 0;
//yrtypes
$finacialyear = $_GET['yrtypes'] ? $_GET['yrtypes'] : 0;


if(isset($storeids) && trim($storeids)!="-1"){
    $sClause = "si.store_id in ($storeids) and ";
}else{
    $sClause = "";
}
//atypes


if(isset($dtrange) && trim($dtrange) != ""){
        $dtarr = explode(" - ", $dtrange);
           
	if (count($dtarr) == 1) {
		//list($dd,$mm,$yy) = explode("-",$dtarr[0]);
		$sdate = "$yy-$mm-$dd";		
                $dQuery = "si.start_date >= '$sdate 00:00:00' and si.end_date <= '$sdate 23:59:59' ";
	} else if (count($dtarr) == 2) {
          
		//list($dd,$mm,$yy) = explode("-",$dtarr[0]);//
		$sdate = "$dtarr[0]";
		//list($dd,$mm,$yy) = explode("-",$dtarr[1]);
		$edate = "$dtarr[1]";		
                $dQuery = "si.start_date >= '$sdate 00:00:00' and si.end_date <= '$edate 23:59:59' ";
	} else {
		$dQuery = "";
	}
}
else{
        $dQuery = "";
}
//   $qry ="select * from it_sales_incentive si where  $sClause $dQuery";
//   print $qry;
//   exit;


$filename = "Incentive_Quarter_".$qtr.".csv";

// output headers so that the file is downloaded rather than displayed
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename='.$filename);

// create a file pointer connected to the output stream
$output = fopen('php://output', 'w');

$tcell = array();$headers=array();
$headers = array(
         'Sr No',
         'Store Id',
         'Store Name',
         'Financial Year',
         'Quarter',
         'Sales Representive Name',
         'SalesMan Incentive',
         'Store Incentive',
         'Dated'
          );

foreach($headers as $harr){
    if ($harr != "") {
       $tcell[] .= $harr;
    }                
}
//fputcsv($output, $tcell,',',chr(0));
    fputcsv($output, array_values($tcell));
    $qry ="select * from it_sales_incentive si where  $sClause $dQuery";
    $result = $db->execQuery($qry);
    $count=0;
    while ($obj = $result->fetch_object()) { 
         $count++;
          if(isset($obj->createtime)){
                                $createtime=mmddyy($obj->createtime);
                            }else{
                                $createtime=" - ";
                            }
            $tcell=null;
         
                $tcell[] .= $count; 
                $tcell[] .= $obj->store_id;
                $tcell[] .= $obj->store_name;
                $tcell[] .= $finacialyear;
                $tcell[] .= $obj->quarter; 
                $tcell[] .= $obj->createdby_name;     
                $tcell[] .= $obj->salesman_incentive;
                $tcell[] .= $obj->store_incentive;
                 $tcell[] .= $createtime;
                //fputcsv($output, $tcell,',',chr(0));
                fputcsv($output, array_values($tcell));
       
    }    