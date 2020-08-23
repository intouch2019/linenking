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
$db1 = new DBConn();
extract($_GET);

$startdate = yymmdd($d1);
$enddate = yymmdd($d2);
//$txndt = yymmdd($txndt);


if(isset($startdate) && trim($startdate)!="" && isset($enddate) && trim($enddate)!=""){
    $dtClause = "" ;		
            $dtClause = " and s.invoice_dt >= '$startdate 00:00:00' and s.invoice_dt <= '$enddate 23:59:59' ";
  
}else{ $dtClause=""; }

if(isset($storeid) && trim($storeid)!="" && trim($storeid) != "-1"){
   $sClause=" ";
   
}else{ $sClause="and c.is_natch_required=1" ;}


//$query = "select  s.*,c.store_name,c.UMRN,c.cust_tobe_debited,c.cust_ifsc_or_mcr,c.cust_debit_account,(select State from states where id=c.state_id) as State,(select region from region where id=c.region_id)as region_name,c.Area,c.Location from it_invoices s, it_codes c where s.store_id = c.id  $dtClause $sClause";  /ozeewali
$query = "select  s.invoice_no,s.invoice_amt,c.UMRN,c.cust_tobe_debited,c.cust_ifsc_or_mcr,c.cust_debit_account from it_invoices s, it_codes c where s.store_id = c.id  $dtClause $sClause";   //Modified
$objs = $db->fetchObjectArray($query);
$db->closeConnection();


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

//$objPHPExcel->getActiveSheet()->setCellValue('E1', 'Name');  //E1
$objPHPExcel->getActiveSheet()->setCellValue('E1', 'Customer IFSC/MICR');    //G1 //F1
$objPHPExcel->getActiveSheet()->setCellValue('F1', 'Customer Debit AC');//H1   //G1
$objPHPExcel->getActiveSheet()->setCellValue('G1', 'Transaction ID');//I1    //H1

$objPHPExcel->getActiveSheet()->setCellValue('H1', 'Transaction Amount');//J1    //I1
$objPHPExcel->getActiveSheet()->setCellValue('I1', 'Transaction Date');//K1    //J1

$objPHPExcel->getActiveSheet()->setCellValue('J1', 'File No.');//L1   //K1





//$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(23);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(22);


$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(24);
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
//$objPHPExcel->getActiveSheet()->getStyle('K1')->applyFromArray($styleArray);


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

//$objPHPExcel->getActiveSheet()->getStyle('K')->applyFromArray($cellstyleArray);



$rowCount=2;
$Totalamt = 0;
//$query = "select  s.*,c.store_name,c.UMRN,c.cust_tobe_debited,c.cust_ifsc_or_mcr,c.cust_debit_account,(select State from states where id=c.state_id) as State,(select region from region where id=c.region_id)as region_name,c.Area,c.Location from it_invoices s, it_codes c where s.store_id = c.id  $dtClause $sClause";
////$query="select s.*,c.store_name,c.UMRN,c.cust_tobe_debited,c.cust_ifsc_or_mcr,c.cust_debit_account,(select State from states where id=c.state_id) as State,c.Area,c.Location from it_invoices s, it_codes c where s.store_id = c.id and s.invoice_dt >= '2017-02-01 00:00:00' and s.invoice_dt <= '2018-02-28 23:59:59' and c.is_natch_required=1";
////print "$query";
////return
//$objs = $db->fetchObjectArray($query);
//$db->closeConnection();
//$state="";
foreach ($objs as $obj) { 
//   if($obj->region_name==null || $obj->region_name==""){//region_name
//    //  $regions="NA";
//      $brand_name="NA";        
//    }else{
//          // $regions=$obj->region_name." "."Region"; 
//           $brand_name="Linenking";        
//    }
    
    $acc="".$obj->cust_debit_account;
    $objPHPExcel->getActiveSheet()->setCellValueExplicit('A'.$rowCount, 'NACH00000000004689',PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->setCellValueExplicit('B'.$rowCount, "FASHIONKINGBRANDSPVTLTD",PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->setCellValueExplicit('C'.$rowCount, $obj->UMRN,PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->setCellValueExplicit('D'.$rowCount, $obj->cust_tobe_debited,PHPExcel_Cell_DataType::TYPE_STRING);

    
//    $objPHPExcel->getActiveSheet()->setCellValueExplicit('E'.$rowCount,$brand_name,PHPExcel_Cell_DataType::TYPE_STRING); //E1
   // $objPHPExcel->getActiveSheet()->setCellValueExplicit('F'.$rowCount, $regions,PHPExcel_Cell_DataType::TYPE_STRING);//F1
    $objPHPExcel->getActiveSheet()->setCellValueExplicit('E'.$rowCount, $obj->cust_ifsc_or_mcr,PHPExcel_Cell_DataType::TYPE_STRING); //G1       
    //$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $rowCount,'="$acc"'); 
    $objPHPExcel->getActiveSheet()->setCellValueExplicit('F'.$rowCount,$acc,PHPExcel_Cell_DataType::TYPE_STRING);//H1
                                  
    $objPHPExcel->getActiveSheet()->setCellValueExplicit('G'.$rowCount, $obj->invoice_no,PHPExcel_Cell_DataType::TYPE_STRING);//I1
  
    $objPHPExcel->getActiveSheet()->setCellValueExplicit('H'.$rowCount, $obj->invoice_amt,PHPExcel_Cell_DataType::TYPE_NUMERIC);//J1
    
//    $objPHPExcel->getActiveSheet()->setCellValueExplicit('J'.$rowCount, ddmmyy($obj->invoice_dt),PHPExcel_Cell_DataType::TYPE_STRING);//K1
    $objPHPExcel->getActiveSheet()->setCellValueExplicit('I'.$rowCount, $txndt,PHPExcel_Cell_DataType::TYPE_STRING);//K1
    
    $objPHPExcel->getActiveSheet()->setCellValueExplicit('J'.$rowCount, '');//L1
    
    $Totalamt = $Totalamt + $obj->invoice_amt;
    
    $rowCount++;
}    
$rowCount = $rowCount-2;

// Redirect output to a client’s web browser (Excel5)
$filename = "StoreNatchReport_".date("Ymd-His").".xls";     
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename='.$filename);
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
//$objWriter->save('php://output');

//$fpath = "/var/www/limelight_new/home/GST_NatchXLs/$filename";
$fpath = "/var/www/html/linenking/home/GST_NatchXLs/$filename";
//$fpath = "/home/linenking/home/GST_NatchXLs/$filename";
//    $fpath = "/var/www/cottonking_new/daemons/dealerBelowMSLFiles/$filename";
$objWriter->save(str_replace(__FILE__, $fpath, __FILE__));    

    
function sendEmail($fpath,$rowCount,$txndt,$Totalamt){
     $filename=basename($fpath);
     $db = new DBconn();
     $emailHelper = new EmailHelper();
     
     $toArray = array();
     $ccArray = array();
     
//        array_push($toArray,"rghule@intouchrewards.com");
//        array_push($ccArray,"sampat.kumar@kinglifestyle.com");
        
        array_push($toArray,"cmsdirect.debit@axisbank.com");
        array_push($ccArray,"shubhra1.singh@axisbank.com");
        array_push($ccArray,"ganesh.kavle@kinglifestyle.com");
        
        if(!empty($toArray)){                                   
//            print "<br>";
            //print_r($toArray);
            $subject = "Fashionking Brands Pvt Ltd - Linenking Nach Debit file ".$txndt;           
            $body = "<p>Please find the attached <b>Fashionking Brands Pvt Ltd - Linenking</b> NACH Debit File. </p>";
            $body .= "<p>Total Transactions are ".$rowCount." and Amount is ".$Totalamt." </p>";
//            $body .= "PFA , <br/>";
            $errormsg = $emailHelper->send($toArray, $subject, $body ,array($fpath), $ccArray);
            print "<br>EMAIL SENT RESP:".$errormsg;
            if ($errormsg != "0") {
                $errors['mail'] = " <br/> Error in sending mail, please try again later.";
            } 
        }
 }
 
 sendEmail($fpath,$rowCount,$txndt,$Totalamt);
 // echo "Email Send Successfully..!!!";
}else{
    print "No Record Found";
}