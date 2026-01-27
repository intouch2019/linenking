<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "Classes/PHPExcel.php";
require_once 'Classes/PHPExcel/Writer/Excel2007.php';
require_once "lib/core/strutil.php";


$db = new DBConn();
$db1 = new DBConn();
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
$objPHPExcel->getActiveSheet()->setCellValue('A1', 'UserNumber');
$objPHPExcel->getActiveSheet()->setCellValue('B1', 'Settlement Date');
$objPHPExcel->getActiveSheet()->setCellValue('C1', 'UMRN');
$objPHPExcel->getActiveSheet()->setCellValue('D1', 'Amount');
$objPHPExcel->getActiveSheet()->setCellValue('E1', 'Transaction ID');
$objPHPExcel->getActiveSheet()->setCellValue('F1', 'Product Code');
$objPHPExcel->getActiveSheet()->setCellValue('G1', 'Account No');  

$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
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

$objPHPExcel->getActiveSheet()->getStyle('A')->applyFromArray($cellstyleArray);
$objPHPExcel->getActiveSheet()->getStyle('B')->applyFromArray($cellstyleArray);
$objPHPExcel->getActiveSheet()->getStyle('C')->applyFromArray($cellstyleArray);
$objPHPExcel->getActiveSheet()->getStyle('D')->applyFromArray($cellstyleArray);
$objPHPExcel->getActiveSheet()->getStyle('E')->applyFromArray($cellstyleArray);
$objPHPExcel->getActiveSheet()->getStyle('F')->applyFromArray($cellstyleArray);
$objPHPExcel->getActiveSheet()->getStyle('G')->applyFromArray($cellstyleArray);

$rowCount=2;

$query = "select s.invoice_no,s.invoice_amt,s.invoice_dt,c.store_name,c.UMRN,c.cust_tobe_debited,c.cust_ifsc_or_mcr,c.cust_debit_account,c.cust_bank_name from it_invoices s, it_codes c where s.store_id = c.id  $dtClause $sClause and s.invoice_type!=7  and c.cust_bank_name like '%HDFC%'";
//$query="select s.*,c.store_name,c.UMRN,c.cust_tobe_debited,c.cust_ifsc_or_mcr,c.cust_debit_account,(select State from states where id=c.state_id) as State,c.Area,c.Location from it_invoices s, it_codes c where s.store_id = c.id and s.invoice_dt >= '2017-02-01 00:00:00' and s.invoice_dt <= '2018-02-28 23:59:59' and c.is_natch_required=1";
//print "$query";
//return
$objs = $db->fetchObjectArray($query);
$db->closeConnection();
//$state="";
foreach ($objs as $obj) { 

    $invdt=yymmdd($obj->invoice_dt);
    $datetime = new DateTime($obj->invoice_dt);

    $objPHPExcel->getActiveSheet()->setCellValueExplicit('A'.$rowCount, "NACH00000000004689",PHPExcel_Cell_DataType::TYPE_STRING);

    $formattedDate = $datetime->format('d/m/Y');
    $objPHPExcel->getActiveSheet()->setCellValueExplicit('B' . $rowCount, $formattedDate, PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->setCellValueExplicit('C'.$rowCount, $obj->UMRN,PHPExcel_Cell_DataType::TYPE_STRING2);
//    $objPHPExcel->getActiveSheet()->setCellValueExplicit('D'.$rowCount, $obj->invoice_amt,PHPExcel_Cell_DataType::TYPE_NUMERIC);
    $objPHPExcel->getActiveSheet()->setCellValue('D'.$rowCount, (float)$obj->invoice_amt);
    $objPHPExcel->getActiveSheet()->getStyle('D'.$rowCount)->getNumberFormat()->setFormatCode('0.00'); // Two decimal places
    $objPHPExcel->getActiveSheet()->setCellValueExplicit('E'.$rowCount, $obj->invoice_no,PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->setCellValueExplicit('F' . $rowCount, "10", PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->setCellValueExplicit('G' . $rowCount, "01497630000436", PHPExcel_Cell_DataType::TYPE_STRING);

    $rowCount++;
}


// Redirect output to a client’s web browser (Excel5)
$filename = "StoreNatchReportHdfc_".date("Ymd-His").".xls";     
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename='.$filename);
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');    
	


