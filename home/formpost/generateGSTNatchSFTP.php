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
$filenamedt= "";
if (isset($startdate) && trim($startdate) != "" && isset($enddate) && trim($enddate) != "") {
    $dtClause = " and s.invoice_dt >= '$startdate 00:00:00' and s.invoice_dt <= '$enddate 23:59:59' ";
} else {
    $dtClause = "";
}

$sClause = " and c.is_natch_required=1 and c.is_closed=0 ";
//
//$checkomscofo = $_GET["checkomscofo"];
//if (isset($checkomscofo) && $checkomscofo == 1) {
//    $whquery = " and c.is_omscofo=1 ";
//} else {
//    $whquery = "";
//}

$sheetIndex = 0;

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();
$objPHPExcel->setActiveSheetIndex($sheetIndex);
$sheet = $objPHPExcel->getActiveSheet();
$sheet->setTitle('Store Nach Report');

// Header row
$sheet->setCellValue('A1', 'UserNumber');
$sheet->setCellValue('B1', 'Settlement Date');
$sheet->setCellValue('C1', 'UMRN');
$sheet->setCellValue('D1', 'Amount');
$sheet->setCellValue('E1', 'Transaction ID');
$sheet->setCellValue('F1', 'Product Code');
$sheet->setCellValue('G1', 'Account No');

// Column widths
$sheet->getColumnDimension('A')->setWidth(20);
$sheet->getColumnDimension('B')->setWidth(20);
$sheet->getColumnDimension('C')->setWidth(20);
$sheet->getColumnDimension('D')->setWidth(20);
$sheet->getColumnDimension('E')->setWidth(20);
$sheet->getColumnDimension('F')->setWidth(20);
$sheet->getColumnDimension('G')->setWidth(20);

// Force all columns as TEXT
$sheet->getStyle('A:G')
    ->getNumberFormat()
    ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

// Header style
$styleArray = array(
    'font' => array(
        'bold' => true,
        'size' => 10,
    ),
    'alignment' => array(
        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
    )
);

// Cell style
$cellstyleArray = array(
    'font' => array(
        'bold' => false,
        'size' => 10,
    ),
    'alignment' => array(
        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
    )
);

// Apply header style
$sheet->getStyle('A1:G1')->applyFromArray($styleArray);

// Apply data style
$sheet->getStyle('A:G')->applyFromArray($cellstyleArray);

$rowCount = 2;

$query = "select 
            s.invoice_no,
            s.invoice_amt,
            s.invoice_dt,
            c.store_name,
            c.UMRN,
            c.cust_tobe_debited,
            c.cust_ifsc_or_mcr,
            c.cust_debit_account,
            c.cust_bank_name 
          from it_invoices s, it_codes c 
          where s.invoice_type != 7 
          and s.store_id = c.id 
           
          $dtClause 
          $sClause  
          and c.cust_bank_name like '%HDFC%'";

// print_r($query); exit();

$objs = $db->fetchObjectArray($query);
$db->closeConnection();
if (!$objs || count($objs) == 0) {
    echo "No records found for selected criteria.";
    exit;
}
foreach ($objs as $obj) {

   $datetime = new DateTime($obj->invoice_dt);
$datetime->modify('+2 days');
$formattedDate = $datetime->format('d/m/Y');;
$filenamedt=$formattedDate;
    // Column A - UserNumber
    $sheet->setCellValueExplicit(
        'A' . $rowCount,
        "NACH00000000004689",
        PHPExcel_Cell_DataType::TYPE_STRING
    );

    // Column B - Settlement Date
    $sheet->setCellValueExplicit(
        'B' . $rowCount,
        $formattedDate,
        PHPExcel_Cell_DataType::TYPE_STRING
    );

    // Column C - UMRN
    $sheet->setCellValueExplicit(
        'C' . $rowCount,
        $obj->UMRN,
        PHPExcel_Cell_DataType::TYPE_STRING
    );

    // Column D - Amount (as text)
    $objPHPExcel->getActiveSheet()->setCellValue('D'.$rowCount, (float)$obj->invoice_amt);
    $objPHPExcel->getActiveSheet()->getStyle('D'.$rowCount)->getNumberFormat()->setFormatCode('0.00'); 
    // Column E - Transaction ID
    $sheet->setCellValueExplicit(
        'E' . $rowCount,
        $obj->invoice_no,
        PHPExcel_Cell_DataType::TYPE_STRING
    );

    // Column F - Product Code
    $sheet->setCellValueExplicit(
        'F' . $rowCount,
        "10",
        PHPExcel_Cell_DataType::TYPE_STRING
    );

    // Column G - Account No
    $sheet->setCellValueExplicit(
        'G' . $rowCount,
        "01497630000436",
        PHPExcel_Cell_DataType::TYPE_STRING
    );

    $rowCount++;
}

// Output to browser
$filename = "Fashionking_LK_" .$filenamedt . ".xls";

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename=' . $filename);
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;
?>