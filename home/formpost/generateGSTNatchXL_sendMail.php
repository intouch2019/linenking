<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "Classes/PHPExcel.php";
require_once 'Classes/PHPExcel/Writer/Excel2007.php';
require_once "lib/core/strutil.php";
require_once "lib/email/EmailHelper.php";

$db = new DBConn();
extract($_GET);
$d1 = $_GET['d1'] ? $_GET['d1'] : false;
$d2 = $_GET['d2'] ? $_GET['d2'] : false;
$txndt = $_GET['txndt'] ? $_GET['txndt'] : false;

$startdate = yymmdd($d1);
$enddate = yymmdd($d2);

//print "<br>sedate==   $startdate-$enddate";
if(isset($startdate) && trim($startdate)!="" && isset($enddate) && trim($enddate)!=""){
    $dtClause = "" ;       
            $dtClause = " and s.invoice_dt >= '$startdate 00:00:00' and s.invoice_dt <= '$enddate 23:59:59' ";
 
}else{ $dtClause=""; }

//if(isset($storeid) && trim($storeid)!="" && trim($storeid) != "-1"){
//   $sClause=" and s.store_id>1";
//  
//}else{ $sClause="and c.is_natch_required=1" ;}
//

$query = "select  s.*,c.store_name,c.umrn,c.cust_debited,c.cust_ifsc_micr,c.cust_debit_ac from it_invoices s, it_codes c where s.store_id = c.id and c.is_nach_required=1 $dtClause";
$objs = $db->fetchObjectArray($query);

if(isset($objs) && $objs != NULL){
    
$sheetIndex=0;
// Create new PHPExcel object
$objPHPExcel = new PHPExcel();
// Create a first sheet
$objPHPExcel->setActiveSheetIndex($sheetIndex);
$objPHPExcel->getActiveSheet()->setTitle('Store Natch Report');
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
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(22);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(22);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
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
$Totalamt = 0;
//$query = "select  s.*,c.store_name,c.umrn,c.cust_debited,c.cust_ifsc_micr,c.cust_debit_ac from it_invoices s, it_codes c where s.store_id = c.id and c.is_nach_required=1 $dtClause";
//$objs = $db->fetchObjectArray($query);


foreach ($objs as $obj) {  
//    $invdt=ddmmyy($obj->invoice_dt);
        
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $rowCount, 'NACH00000000006237',PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $rowCount,'SPLIFESTYLEBRANDSPVTLTD',PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $rowCount, $obj->umrn,PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $rowCount, $obj->cust_debited,PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $rowCount, $obj->cust_ifsc_micr,PHPExcel_Cell_DataType::TYPE_STRING);    
    
    $objPHPExcel->getActiveSheet()->setCellValueExplicit('F'.$rowCount, $obj->cust_debit_ac,PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->setCellValueExplicit('G'.$rowCount, $obj->invoice_no,PHPExcel_Cell_DataType::TYPE_STRING);
  
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $rowCount, $obj->invoice_amt,PHPExcel_Cell_DataType::TYPE_NUMERIC);
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $rowCount, $txndt,PHPExcel_Cell_DataType::TYPE_STRING);
    
//    
    
//    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $rowCount, 'NACH00000000006237');
//    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $rowCount,'SPLIFESTYLEBRANDSPVTLTD');
//    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $rowCount, $obj->umrn);
//    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $rowCount, $obj->cust_debited);
//    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $rowCount, $obj->cust_ifsc_micr);       
//    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $rowCount, $obj->cust_debit_ac);       
//    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $rowCount, $obj->invoice_no);
//    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $rowCount, ddmmyy($obj->invoice_dt));
//    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $rowCount, $obj->invoice_amt);
//    
    $Totalamt += $obj->invoice_amt;
    $rowCount++;
}   
$rowCount = $rowCount-2;

// Redirect output to a client’s web browser (Excel5)
//$filename = "StoreNatchReport_".date("Ymd-His").".xls";    
//header('Content-Type: application/vnd.ms-excel');
//header('Content-Disposition: attachment;filename='.$filename);
//header('Cache-Control: max-age=0');
//$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
////$objWriter->save('php://output');  
//
//$fpath = "/var/www/ck_new_y/home/GST_NatchXLs/$filename";
//$objWriter->save(str_replace(__FILE__, $fpath, __FILE__));

// Redirect output to a client’s web browser (Excel5)
$filename = "StoreNatchReport_".date("Ymd-His").".xls";     
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename='.$filename);
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
//$objWriter->save('php://output');    

$fpath = "/var/www/html/tyson/home/GST_NatchXLs/$filename";
//$fpath = "/var/www/tyzer_new_y/home/GST_NatchXLs/$filename";
$objWriter->save(str_replace(__FILE__, $fpath, __FILE__));


function sendEmail($fpath,$rowCount,$txndt,$Totalamt){
     $filename=basename($fpath);
     $db = new DBconn();
     $emailHelper = new EmailHelper();
     
     $toArray = array();
     $ccArray = array();
     
//        array_push($toArray,"rghule@intouchrewards.com");
//        array_push($ccArray,"akpatil@intouchrewards.com");
//        array_push($ccArray,"abhamare@intouchrewards.com");
        
        array_push($toArray,"cmsdirect.debit@axisbank.com");
//        array_push($ccArray,"shubhra1.singh@axisbank.com");
     array_push($ccArray,"Sachit.Pandey@axisbank.com");
        array_push($ccArray,"sampat.kumar@kinglifestyle.com");
	array_push($ccArray,"ganesh.kavle@kinglifestyle.com");
        
        if(!empty($toArray)){
//            print "<br>";
            //print_r($toArray);
            $subject = "SP Lifestyle Brands Pvt Ltd - Tyzer Nach Debit file ".$txndt;           
            $body = "<p>Please find the attached <b>SP Lifestyle Brands Pvt Ltd - Tyzer</b> NACH Debit File. </p>";
            $body .= "<p>Total Transactions are ".$rowCount." and Amount is ".$Totalamt." </p>";
//            $body .= "PFA , <br/>";
            $errormsg = $emailHelper->send($toArray, $subject, $body ,array($fpath), $ccArray);
            print "<br>EMAIL SENT RESP:".$errormsg;
            if ($errormsg != "0") {
                $errors['mail'] = " <br/> Error in sending mail, please try again later.";
            } 
        }
//     }
 }
 
 sendEmail($fpath,$rowCount,$txndt,$Totalamt);
 // echo "Email Send Successfully..!!!";
}else{
    print "No Record Found";
}
