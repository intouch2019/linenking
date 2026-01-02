<?php

require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once "Classes/PHPExcel.php";
require_once "Classes/PHPExcel/Writer/Excel2007.php";

try {
    $sheetIndex = 0;
    $cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_in_memory_serialized;
    $cacheSettings = array('memoryCacheSize' => '1500MB');
    PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
    // Create new PHPExcel object
    $objPHPExcel = new PHPExcel();
    // Create a first sheet
    $objPHPExcel->setActiveSheetIndex($sheetIndex);
    $objPHPExcel->getActiveSheet()->setTitle('Credit Point Calculations');
  
    //define style for column name
    $styleArray = array(
        'font' => array(
            'bold' => true,
//            'color' => array('rgb' => 'FF0000'),
            'size' => 11,
    ));
// Set the value in the merged cell(1st row)
    $objPHPExcel->getActiveSheet()->mergeCells('G1:J1');
    $objPHPExcel->getActiveSheet()->setCellValue('G1', 'P18 S5')->getStyle('G1')->applyFromArray($styleArray)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->mergeCells('K1:N1');
    $objPHPExcel->getActiveSheet()->setCellValue('K1', 'P5 S5')->getStyle('K1')->applyFromArray($styleArray)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->mergeCells('O1:R1');
    $objPHPExcel->getActiveSheet()->setCellValue('O1', 'P18 S18')->getStyle('O1')->applyFromArray($styleArray)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    
//set the value in 2nd row
    $objPHPExcel->getActiveSheet()->setCellValue('A2', 'Sr No')->getStyle('A2')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->setCellValue('B2', 'Row Labels')->getStyle('B2')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->setCellValue('C2', 'Store ID')->getStyle('C2')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->setCellValue('D2', 'Credit Point Heading')->getStyle('D2')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->setCellValue('E2', 'Dealer Margin')->getStyle('E2')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->setCellValue('F2', 'Scheme Discount')->getStyle('F2')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->setCellValue('G2', 'MRP Sale')->getStyle('G2')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->setCellValue('H2', 'Sale Without Discount')->getStyle('H2')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->setCellValue('I2', 'Discount')->getStyle('I2')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->setCellValue('J2', 'Total Value')->getStyle('J2')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->setCellValue('K2', 'MRP Sale')->getStyle('K2')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->setCellValue('L2', 'Sale Without Discount')->getStyle('L2')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->setCellValue('M2', 'Discount')->getStyle('M2')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->setCellValue('N2', 'Total Value')->getStyle('N2')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->setCellValue('O2', 'MRP Sale')->getStyle('O2')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->setCellValue('P2', 'Sale Without Discount')->getStyle('P2')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->setCellValue('Q2', 'Discount')->getStyle('Q2')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->setCellValue('R2', 'Total Value')->getStyle('R2')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->setCellValue('S2', 'Non Scheme Sale')->getStyle('S2')->applyFromArray($styleArray);
    
    // Set width for each column
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(35);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(7);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(45);
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(13);
    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('U')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('V')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('W')->setWidth(15);
    
// Redirect output to a client’s web browser (Excel5)
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="cpcalculations.xls"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

    $objWriter->save('php://output');
} catch (Exception $xcp) {
    print $xcp->getMessage();
}