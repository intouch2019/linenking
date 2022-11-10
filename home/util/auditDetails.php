<?php

require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "Classes/PHPExcel.php";
require_once 'Classes/PHPExcel/Writer/Excel2007.php';


//$cnt=0;
//$dealersList = array();
$table = "it_auditdetails,it_codes";
if (isset($_GET["dtrange"]) && $_GET["dtrange"] != "") {
  $dtrange = $_GET["dtrange"]; }
$db = new DBConn();
$dtarr = explode(" - ", $dtrange);
//print_r($dtrange);
//exit;


                        if (count($dtarr) == 1) {
                                list($dd,$mm,$yy) = explode("-",$dtarr[0]);
                                $sdate = "$yy-$mm-$dd";		
                                $dQuery = " and a.SubmittedDate >= '$sdate 00:00:00' and a.SubmittedDate <= '$sdate 23:59:59' ";
                        } else if (count($dtarr) == 2) {
                                list($dd,$mm,$yy) = explode("-",$dtarr[0]);
                                $sdate = "$yy-$mm-$dd";
                                list($dd,$mm,$yy) = explode("-",$dtarr[1]);
                                $edate = "$yy-$mm-$dd";		
                                $dQuery = " and a.SubmittedDate >= '$sdate 00:00:00' and a.SubmittedDate <= '$edate 23:59:59' ";
                        } else {
                                $dQuery = "";
                        } 
                        
                        
                        
                        
                      
//                        if (isset($sid) && $sid !== "" && $sid == -1) {
//                            $qsid = "order by s.store_name";
//                        } else if (isset($sid) && $sid !== "") {
//                            $qsid = "and a.store_id=sid order by a.id desc";
//                        } else {
//                            $qsid = "and a.store_id=-1 order by a.id desc";
//                        }
$query = "select a.*,s.store_name ,(select count(*)as score from it_auditresponse where "
                                . "audit_id = a.id and is_opted=1)as score,(select count(*)as score from it_auditresponse where audit_id = a.id )as outof from "
                                . "it_auditdetails a, it_codes s  where a.store_id= s.id $dQuery order by s.store_name desc  ";
$objsdetails = $db->fetchObjectArray($query);
//print $query;
//exit;
//print_r($alldealersobj);
//return;
//$db->closeConnection();
$objPHPExcel = new PHPExcel();
if (!empty($objsdetails)) {
    $fpath = createexcel($objsdetails, $objPHPExcel);
    unset($dealersList);
}

function createexcel($objsdetails, $objPHPExcel) {

    $sheetIndex = 0;
    // Create new PHPExcel object
    // Create a first sheet
    $objPHPExcel->setActiveSheetIndex($sheetIndex);
    $objPHPExcel->getActiveSheet()->setTitle('Audit Details');
    $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Id');
    $objPHPExcel->getActiveSheet()->setCellValue('B1', 'store_name');
    $objPHPExcel->getActiveSheet()->setCellValue('C1', 'Manager_name');
    $objPHPExcel->getActiveSheet()->setCellValue('D1', 'Managerphone');
    $objPHPExcel->getActiveSheet()->setCellValue('E1', 'Auditor_name');
    $objPHPExcel->getActiveSheet()->setCellValue('F1', 'AuditDate');
    $objPHPExcel->getActiveSheet()->setCellValue('G1', 'SubmittedDate');
    $objPHPExcel->getActiveSheet()->setCellValue('H1', 'score');
    
    
    
    
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(10);

    $colCount = 0;
    $rowCount = 2;

    //foreach ($alldealersobj as $key => $value){
    foreach ($objsdetails as $obj) {

        /*      print_r($dealer);
          return; */

        $diff = 0;
        //$arr = explode("::",$value);
        $id = trim($obj->id);
        $store_name = trim($obj->store_name);
        $Manager_name = trim($obj->Manager_name);
        $Managerphone = trim($obj->Managerphone);
        $Auditor_name = trim($obj->Auditor_name);
        $AuditDate = trim($obj->AuditDate);
        $SubmittedDate = trim($obj->SubmittedDate);
        $score = trim($obj->score);


        
        //mask_
        //
        //margin mask_margin,c.store_type
        //
        //$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $rowCount, $key);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $rowCount, $id);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $rowCount, $store_name);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $rowCount, $Manager_name);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $rowCount, $Managerphone);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $rowCount, $Auditor_name);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $rowCount, $AuditDate);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $rowCount, $SubmittedDate);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $rowCount, $score);
       

        


        $rowCount++;
    }
}

//echo "Row Count=======>".$rowCount."\n";
$filename = "AuditDetail_" . date('Y-m-d H:i:s') . ".xls";
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename=' . $filename);
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');