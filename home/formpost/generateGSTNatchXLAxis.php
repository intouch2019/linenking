<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "Classes/PHPExcel.php";
require_once 'Classes/PHPExcel/Writer/Excel2007.php';
require_once "lib/core/strutil.php";


$db = new DBConn();
extract($_GET);

$startdate = yymmdd($d1);
$enddate = yymmdd($d2);


if (isset($startdate) && trim($startdate) != "" && isset($enddate) && trim($enddate) != "") {
    $dtClause = " and s.invoice_dt >= '$startdate 00:00:00' and s.invoice_dt <= '$enddate 23:59:59' ";
} else {
    $dtClause = "";
}

$sClause = " and c.is_natch_required=1 and c.is_closed=0 ";

$sheetIndex=0;
// Create new PHPExcel object
$objPHPExcel = new PHPExcel();
// Create a first sheet
$objPHPExcel->setActiveSheetIndex($sheetIndex);
$objPHPExcel->getActiveSheet()->setTitle('Store Nach Report');
$objPHPExcel->getActiveSheet()->setCellValue('A1', 'Corporate Utility code');
$objPHPExcel->getActiveSheet()->setCellValue('B1', 'Corporate Name');
$objPHPExcel->getActiveSheet()->setCellValue('C1', 'UMRN');
$objPHPExcel->getActiveSheet()->setCellValue('D1', 'Customer to be Debited');
$objPHPExcel->getActiveSheet()->setCellValue('E1', 'Customer IFSC/MICR');
$objPHPExcel->getActiveSheet()->setCellValue('F1', 'Customer Debit AC');
$objPHPExcel->getActiveSheet()->setCellValue('G1', 'Transaction ID');
$objPHPExcel->getActiveSheet()->setCellValue('H1', 'Transaction Amount');
$objPHPExcel->getActiveSheet()->setCellValue('I1', 'Transaction Date');
$objPHPExcel->getActiveSheet()->setCellValue('J1', 'File No.');

$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);



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
        'size' => 10,
    ),
    'alignment' => array(
        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
    )
);

 $cellstyleArray = array(
    'font' => array(
        'bold' => false,
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
$objPHPExcel->getActiveSheet()->getStyle('H1')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('I1')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('J1')->applyFromArray($styleArray);


$objPHPExcel->getActiveSheet()->getStyle('A')->applyFromArray($cellstyleArray);
$objPHPExcel->getActiveSheet()->getStyle('B')->applyFromArray($cellstyleArray);
$objPHPExcel->getActiveSheet()->getStyle('C')->applyFromArray($cellstyleArray);
$objPHPExcel->getActiveSheet()->getStyle('D')->applyFromArray($cellstyleArray);
$objPHPExcel->getActiveSheet()->getStyle('E')->applyFromArray($cellstyleArray);
$objPHPExcel->getActiveSheet()->getStyle('F')->applyFromArray($cellstyleArray);
$objPHPExcel->getActiveSheet()->getStyle('G')->applyFromArray($cellstyleArray);
$objPHPExcel->getActiveSheet()->getStyle('H')->applyFromArray($cellstyleArray);
$objPHPExcel->getActiveSheet()->getStyle('I')->applyFromArray($cellstyleArray);
$objPHPExcel->getActiveSheet()->getStyle('J')->applyFromArray($cellstyleArray);


$rowCount=2;

//$query = "select  s.*,c.store_name,c.UMRN,c.cust_tobe_debited,c.cust_ifsc_or_mcr,c.cust_debit_account,(select State from states where id=c.state_id) as State,(select region from region where id=c.region_id)as region_name ,c.Area,c.Location from it_invoices s, it_codes c where s.store_id = c.id  $dtClause $sClause";
$query = "select s.invoice_no,s.invoice_amt,s.invoice_dt,c.store_name,c.UMRN,c.cust_tobe_debited,c.cust_ifsc_or_mcr,c.cust_debit_account,c.cust_bank_name from it_invoices s, it_codes c where c.id not in (677,729) and s.store_id = c.id $dtClause $sClause and c.cust_bank_name like '%AXIS%' ";
//print_r($query);exit();
//return;
$objs = $db->fetchObjectArray($query);
$db->closeConnection();

foreach ($objs as $obj) { 
    $acc="".$obj->cust_debit_account;
    $objPHPExcel->getActiveSheet()->setCellValueExplicit('A'.$rowCount, 'NACH00000000004689',PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->setCellValueExplicit('B'.$rowCount, "FASHIONKINGBRANDSPVTLTD",PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->setCellValueExplicit('C'.$rowCount, $obj->UMRN,PHPExcel_Cell_DataType::TYPE_STRING2);
    $objPHPExcel->getActiveSheet()->setCellValueExplicit('D'.$rowCount, $obj->cust_tobe_debited,PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->setCellValueExplicit('E'.$rowCount, $obj->cust_ifsc_or_mcr,PHPExcel_Cell_DataType::TYPE_STRING);    
    $objPHPExcel->getActiveSheet()->setCellValueExplicit('F'.$rowCount, $acc,PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->setCellValueExplicit('G'.$rowCount, $obj->invoice_no,PHPExcel_Cell_DataType::TYPE_STRING);
//    $objPHPExcel->getActiveSheet()->setCellValueExplicit('H'.$rowCount, $obj->invoice_amt,PHPExcel_Cell_DataType::TYPE_NUMERIC);
    $objPHPExcel->getActiveSheet()->setCellValue('H'.$rowCount, (float)$obj->invoice_amt);
    $objPHPExcel->getActiveSheet()->getStyle('H'.$rowCount)->getNumberFormat()->setFormatCode('0.00'); // Two decimal places
    $objPHPExcel->getActiveSheet()->setCellValueExplicit('I'.$rowCount, ddmmyy($obj->invoice_dt),PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->setCellValueExplicit('J'.$rowCount, '');

    $rowCount++;
}    

// Redirect output to a client’s web browser (Excel5)
$filename = "StoreNatchReportAxis_".date("Ymd-His").".xls";     
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename='.$filename);
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');    
	