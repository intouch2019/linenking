<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once "Classes/PHPExcel.php";
require_once  "Classes/PHPExcel/Writer/Excel2007.php";


try{
    $db = new DBConn();
    $sheetIndex=0;
    $cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_in_memory_serialized;
    $cacheSettings = array( 'memoryCacheSize' => '1500MB');
    PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
    // Create new PHPExcel object
    $objPHPExcel = new PHPExcel();
    // Create a first sheet
    $objPHPExcel->setActiveSheetIndex($sheetIndex);
    $objPHPExcel->getActiveSheet()->setTitle('Barcode Sequence');
//    $objPHPExcel->getActiveSheet()->setCellValue('A1', 'ID');
    $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Barcode');
    $objPHPExcel->getActiveSheet()->setCellValue('B1', '');
            
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
//    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
    $styleArray = array(
        'font'  => array(
            'bold'  => false,
    //        'color' => array('rgb' => 'FF0000'),
            'size'  => 11,
        ));
    $objPHPExcel->getActiveSheet()->getStyle('A')->applyFromArray($styleArray);

    
    
    $colCount=0;
    $rowCount=2;
// Redirect output to a client’s web browser (Excel5)
$uniqueexcelname= date("Y-m-d H:i:s");


header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="LK_Add_ReturnGarment_Barcode_"'.$uniqueexcelname.'".xls"');
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');     
}catch(Exception $xcp){
    print $xcp->getMessage();
}