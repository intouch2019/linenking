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
    $objPHPExcel->getActiveSheet()->setTitle('Membership Order Placement');
    $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Id');
    $objPHPExcel->getActiveSheet()->setCellValue('B1', 'Store Name');
    $objPHPExcel->getActiveSheet()->setCellValue('C1', 'Quantity');
    
            
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(50);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
    
    $styleArray = array(
        'font'  => array(
            'bold'  => false,
    //        'color' => array('rgb' => 'FF0000'),
            'size'  => 10,
        ));
    $objPHPExcel->getActiveSheet()->getStyle('A')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('B')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('C')->applyFromArray($styleArray);
    
    
    $colCount=0;
    $rowCount=2;
    
    $query="select c.id,c.store_name from it_codes c where c.usertype = ".UserType::Dealer." and c.is_closed = 0 and id not in (70,147,160,162,168) order by c.id";
    //error_log("\nSalesOvr Exl TAB 1:".$query."\n",3,"tmp.txt");
    //$objs = $db->fetchObjectArray($query);
     $objs = $db->getConnection()->query($query);
     while($obj=$objs->fetch_object()){    
         $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $rowCount, $obj->id);
         $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $rowCount, $obj->store_name);     
        
         $rowCount++;
     }   
    

// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="LkStore_membershiporder_placement.xls"');
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');     
}catch(Exception $xcp){
    print $xcp->getMessage();
}