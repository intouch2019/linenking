<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "Classes/PHPExcel.php";
require_once 'Classes/PHPExcel/Writer/Excel2007.php';



$db = new DBConn();
$dtrange = $_GET['dtrange'] ? $_GET['dtrange'] : false;
$storeid = $_GET['storeid'] ? $_GET['storeid'] : false;

if(isset($dtrange) && trim($dtrange)!=""){
    $dtClause = "" ;
    $dtarr = explode(" - ", $dtrange);
    //$_SESSION['storeid'] = $this->storeidreport;
    if (count($dtarr) == 1) {
            list($dd,$mm,$yy) = explode("-",$dtarr[0]);
            $sdate = "$yy-$mm-$dd";		
            $dtClause = " and s.stock_datetime >= '$sdate 00:00:00' and s.stock_datetime <= '$sdate 23:59:59' ";
    } else if (count($dtarr) == 2) {
            list($dd,$mm,$yy) = explode("-",$dtarr[0]);
            $sdate = "$yy-$mm-$dd";
            list($dd,$mm,$yy) = explode("-",$dtarr[1]);
            $edate = "$yy-$mm-$dd";		
            $dtClause = " and s.stock_datetime >= '$sdate 00:00:00' and s.stock_datetime <= '$edate 23:59:59' ";
    } else {
            $dtClause = "";
    }
}else{ $dtClause=""; }

if(isset($storeid) && trim($storeid)!="" && trim($storeid) != "-1"){
   $sClause=" and s.store_id in ($storeid)";
   
}else{ $sClause="" ;}



$sheetIndex=0;
// Create new PHPExcel object
$objPHPExcel = new PHPExcel();
// Create a first sheet
$objPHPExcel->setActiveSheetIndex($sheetIndex);
$objPHPExcel->getActiveSheet()->setTitle('Store Stock Summary');
$objPHPExcel->getActiveSheet()->setCellValue('A1', 'Store ID');
$objPHPExcel->getActiveSheet()->setCellValue('B1', 'Store Name');
$objPHPExcel->getActiveSheet()->setCellValue('C1', 'Store Stock Limit');
$objPHPExcel->getActiveSheet()->setCellValue('D1', 'Stock DateTime');
$objPHPExcel->getActiveSheet()->setCellValue('E1', 'Store Stock in Value');    
$objPHPExcel->getActiveSheet()->setCellValue('F1', 'Store Stock in Quantity');
$objPHPExcel->getActiveSheet()->setCellValue('G1', 'Store Stock in Transit');

$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);


$styleArray = array(
    'font'  => array(
        'bold'  => true,    
        'size'  => 10,           

    ),
    'alignment' => array(
        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
    )
    );

$styleArray1 = array(
    'font' => array(
        'bold' => true,
//        'color' => array('rgb' => 'FF0000'),
        'size' => 10,
    ),
    'alignment' => array(
        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
    )
);

 $cellstyleArray = array(
    'font' => array(
        'bold' => false,
//        'color' => array('rgb' => 'FF0000'),
        'size' => 10,
    ),
    'alignment' => array(
        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
    )
);
$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('B1')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('C1')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('D1')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('E1')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('F1')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('G1')->applyFromArray($styleArray);

$objPHPExcel->getActiveSheet()->getStyle('A')->applyFromArray($cellstyleArray);
$objPHPExcel->getActiveSheet()->getStyle('B')->applyFromArray($cellstyleArray);
$objPHPExcel->getActiveSheet()->getStyle('C')->applyFromArray($cellstyleArray);
$objPHPExcel->getActiveSheet()->getStyle('D')->applyFromArray($cellstyleArray);
$objPHPExcel->getActiveSheet()->getStyle('E')->applyFromArray($cellstyleArray);
$objPHPExcel->getActiveSheet()->getStyle('F')->applyFromArray($cellstyleArray);
$objPHPExcel->getActiveSheet()->getStyle('G')->applyFromArray($cellstyleArray);


$rowCount=2;

$query = "select  s.*,c.store_name,c.min_stock_level from it_store_stock_summary s, it_codes c where s.store_id = c.id  $dtClause $sClause";
$objs = $db->fetchObjectArray($query);

foreach ($objs as $obj) {    
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $rowCount, $obj->id);
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $rowCount, $obj->store_name);
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $rowCount, $obj->min_stock_level);
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $rowCount, $obj->stock_datetime);
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $rowCount, $obj->stock_value);        
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $rowCount, $obj->stock_qty);        
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $rowCount, $obj->stock_intransit);        
    $rowCount++;
}    


// Redirect output to a client’s web browser (Excel5)
$filename = "StoreStockSummary_".date("Ymd-His").".xls";     
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename='.$filename);
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');    
	


