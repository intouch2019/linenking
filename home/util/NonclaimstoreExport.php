<?php

require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "Classes/PHPExcel.php";
require_once 'Classes/PHPExcel/Writer/Excel2007.php';


//$cnt=0;
//$dealersList = array();
$table = "it_codes";
$db = new DBConn();
$query = "select id,store_number,store_name,IF(is_claim = 1, 'Yes', 'No') as is_claim from $table where usertype=4  and is_closed=0 order by createtime";

$alldealersobj = $db->fetchObjectArray($query);

$objPHPExcel = new PHPExcel();
if (!empty($alldealersobj)) {
    $fpath = createexcel($alldealersobj, $objPHPExcel);
    unset($dealersList);
}

function createexcel($alldealersobj, $objPHPExcel) {

    $sheetIndex = 0;
    // Create new PHPExcel object
    // Create a first sheet
    $objPHPExcel->setActiveSheetIndex($sheetIndex);
    $objPHPExcel->getActiveSheet()->setTitle('Franchisees below MSL');
    $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Id');
    $objPHPExcel->getActiveSheet()->setCellValue('B1', 'Store Name');
    $objPHPExcel->getActiveSheet()->setCellValue('C1', 'Non Claim');
   


    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
   


    $colCount = 0;
    $rowCount = 2;

   
    foreach ($alldealersobj as $dealer) {

     
        $diff = 0;
        //$arr = explode("::",$value);
        $id = trim($dealer->id);
        $store_name = trim($dealer->store_name);
        $is_claim = trim($dealer->is_claim);
        

        //$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $rowCount, $key);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $rowCount, $id);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $rowCount, $store_name);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $rowCount, $is_claim);
       

        $rowCount++;
    }
}

//echo "Row Count=======>".$rowCount."\n";
$filename = "NonClaimStoreDetail_" . date('Y-m-d H:i:s') . ".xls";
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename=' . $filename);
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');