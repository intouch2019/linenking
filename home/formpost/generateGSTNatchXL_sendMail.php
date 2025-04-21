<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "Classes/PHPExcel.php";
require_once 'Classes/PHPExcel/Writer/Excel2007.php';
require_once "lib/core/strutil.php";
require_once "lib/email/EmailHelper.php";

/*
 * GST NACH REPORT HDFC BANK
 */
$db = new DBConn();
extract($_GET);
$txndate = new DateTime($txndt);
//print_r($_GET); exit();
$errors = array();
$success = "";

$startdate = yymmdd($d1);
$enddate = yymmdd($d2);

//print "<br>sedate==   $startdate-$enddate";
if (isset($startdate) && trim($startdate) != "" && isset($enddate) && trim($enddate) != "") {
    $dtClause = " and s.invoice_dt >= '$startdate 00:00:00' and s.invoice_dt <= '$enddate 23:59:59' ";
} else {
    $dtClause = "";
}

$sClause = " and c.is_natch_required=1 and c.is_closed=0 ";

$query = "select s.invoice_no,s.invoice_amt,s.invoice_dt,c.store_name,c.UMRN,c.cust_tobe_debited,c.cust_ifsc_or_mcr,c.cust_debit_account,c.cust_bank_name from it_invoices s, it_codes c where s.store_id = c.id  $dtClause $sClause and c.cust_bank_name like '%HDFC%'";
$objs = $db->fetchObjectArray($query);

if(isset($objs) && $objs != NULL){
    
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
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(22);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(22);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
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
$Totalamt = 0;

foreach ($objs as $obj) {  
    $txndate = new DateTime($txndt);
    $settlementDate = $txndate->format('d/m/Y');

    $objPHPExcel->getActiveSheet()->setCellValueExplicit('A'.$rowCount, "NACH00000000004689",PHPExcel_Cell_DataType::TYPE_STRING);

    $objPHPExcel->getActiveSheet()->setCellValueExplicit('B' . $rowCount, $settlementDate, PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->setCellValueExplicit('C'.$rowCount, $obj->UMRN,PHPExcel_Cell_DataType::TYPE_STRING2);
    $objPHPExcel->getActiveSheet()->setCellValue('D'.$rowCount, (float)$obj->invoice_amt);
    $objPHPExcel->getActiveSheet()->getStyle('D'.$rowCount)->getNumberFormat()->setFormatCode('0.00'); // Two decimal places
    $objPHPExcel->getActiveSheet()->setCellValueExplicit('E'.$rowCount, $obj->invoice_no,PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->setCellValueExplicit('F' . $rowCount, "10", PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->setCellValueExplicit('G' . $rowCount, "01497630000436", PHPExcel_Cell_DataType::TYPE_STRING);
    
    $Totalamt = $Totalamt + $obj->invoice_amt; 
    $rowCount++;
}
$rowCount -= 2;

// Redirect output to a client’s web browser (Excel5)
$filename = "HdfcStoreNachReport_".date("Ymd-His").".xls";     
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename='.$filename);
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');  

//$fpath = "C:/xampp/htdocs/linenking/home/GST_NatchXLs/$filename"; //Test
$fpath = "/var/www/html/linenking/home/GST_NatchXLs/$filename"; //Live
$objWriter->save(str_replace(__FILE__, $fpath, __FILE__));
    
    $responseObj = sendEmail($fpath,$rowCount,$txndt,$Totalamt);
    if($responseObj == 1){
        $errors['mail'] = " <br/> Error in sending mail, please try again later.";
    } else {
        $success = "Email Sent Successfully.";
    }

}else{
    $errors['msg'] =  "No Record Found";
}

if (count($errors) > 0) {
    $_SESSION['form_errors'] = $errors;
    $redirect = "admin/tallytransfer";
} else {
    unset($_SESSION['form_errors']);
    $_SESSION['form_success'] = $success;
    $redirect = "admin/tallytransfer";
}
session_write_close();
header("Location: ".DEF_SITEURL.$redirect);
exit;

function sendEmail($fpath,$rowCount,$txndt,$Totalamt){
    $emailHelper = new EmailHelper();
    $toArray = array();
    $ccArray = array();
    $txndate = new DateTime($txndt);
    $settlementDate = $txndate->format('d/m/Y');
     
    //Test
//    array_push($toArray, "nmoruskar@intouchrewards.com");
//    array_push($ccArray, "sampat.kumar@kinglifestyle.com");

    //Live
    array_push($toArray, "ecs.debit@hdfcbank.com");
    array_push($toArray, "Sunil.Jadhav@hdfcbank.com");
    array_push($toArray, "Makarand.Korgaonkar@hdfcbank.com");
    array_push($toArray, "Randhir.Dake@hdfcbank.com");
    array_push($ccArray, "accounts1@kinglifestyle.com");
    array_push($ccArray, "accounts2@kinglifestyle.com");
    array_push($ccArray, "accounts3@kinglifestyle.com");
    array_push($ccArray, "accounts4@kinglifestyle.com");
        
    if (!empty($toArray)) {
//            print "<br>";
        //print_r($toArray);
        $subject = $settlementDate." - ACH DR - Fashionking Brands Pvt Ltd - Count - " . $rowCount . " Amount Rs " . $Totalamt;
        $body = "<p>Settlement Date: ". $settlementDate ."</p>";
        $body .= "<p>Total Count: ". $rowCount ."</p>";
        $body .= "<p>Total Amount: ". $Totalamt ."</p>";
        $body .= "<p>Credit Account No.: 01497630000436</p>";
        $errormsg = $emailHelper->send($toArray, $subject, $body, array($fpath), $ccArray);
        print "<br>EMAIL SENT RESP:" . $errormsg;
        if ($errormsg != "0") {
            $response = 1;
        } else {
            $response = 0;
        }
    }
    return $response;
}
