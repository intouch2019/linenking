<?php
include '/var/www/html/linenking/it_config.php'; //For Live Testing
//include '/var/www/limelight_new/it_config.php';

//require_once("../it_config.php"); //For Local Testing
require_once 'lib/db/DBConn.php';
require_once 'lib/core/Constants.php';
require_once "Classes/PHPExcel.php";
require_once 'Classes/PHPExcel/Writer/Excel2007.php';
require_once "lib/email/EmailHelper.php";

//excel file name while be as below 
// dealersBelowMSL_".date("Ymd-His").".xls


try{
    $db = new DBConn();
    $cnt=0;
    $dealersList = array();
    $alldealersobj = $db->fetchObjectArray("select id,store_name from it_codes where usertype = ".UserType::Dealer."  and is_closed = 0 " );  // and inactive = 0 
    foreach($alldealersobj as $dealerobj){ 
//        if(trim($dealerobj->min_stock_level)!=""){
//            //step 1: fetch current stock value
//            $query = "select sum(c.quantity * i.MRP) as curr_stock_value from it_current_stock c , it_items i where c.store_id = $dealerobj->id  and c.barcode = i.barcode and i.ctg_id not in (53,54) ";
//            $cobj = $db->fetchObject($query);
//            if(isset($cobj) && trim($cobj->curr_stock_value)!=""){ $store_stock_val = $cobj->curr_stock_value ;}
//            else{ $store_stock_val = 0; }
            
            //step 2: fetch intransit stock value
//            $query2 = "select sum(invoice_amt) as intransit_stock_value from it_sp_invoices where  invoice_type in (0,6) and store_id = $dealerobj->id and is_procsdForRetail = 0 and i.ctg_id not in (53,54)";
            $query2 = "select sum(invoice_amt) as intransit_stock_value from it_sp_invoices where  invoice_type in (0,6) and store_id = $dealerobj->id and is_procsdForRetail = 0 group by store_id having intransit_stock_value >= 500000";
//            Print_r($query2);exit();
            $tobj = $db->fetchObject($query2);
            if(isset($tobj) && !empty($tobj) && trim($tobj->intransit_stock_value)!=""){
                $intransit_stock_val = $tobj->intransit_stock_value ;
            $dealersList[$dealerobj->id] = $dealerobj->store_name."::".$intransit_stock_val;
   
            }
//            else{ $intransit_stock_val = 0; }
            
//            $tot_stk_val = $store_stock_val + $intransit_stock_val;
//            if($tot_stk_val < $dealerobj->min_stock_level){
//                   $diff = $dealerobj->min_stock_level - $tot_stk_val;
//                $dealersList[$dealerobj->id] = $dealerobj->store_name."::".$store_stock_val."::".$intransit_stock_val."::".$tot_stk_val."::".$dealerobj->min_stock_level;
//            }
            
//        }     
    } 
    $db->closeConnection();
//    print_r($dealersList);
    if(!empty($dealersList)) {
       $fpath = createexcel($dealersList);             
       unset($dealersList);   
       sendEmail($fpath);
    }
}catch(Exception $xcp){
    print $xcp->getMessage();
}

function createexcel($dealersList){
    $db = new DBConn();
    $sheetIndex=0;
    // Create new PHPExcel object
    $objPHPExcel = new PHPExcel();
    // Create a first sheet
    $objPHPExcel->setActiveSheetIndex($sheetIndex);
    $objPHPExcel->getActiveSheet()->setTitle('Stores Stock Intransit');
    $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Store ID');
    $objPHPExcel->getActiveSheet()->setCellValue('B1', 'Store Name');
    $objPHPExcel->getActiveSheet()->setCellValue('C1', 'Store Stock in Transit');
//    $objPHPExcel->getActiveSheet()->setCellValue('D1', 'Store Stock in Transit');
//    $objPHPExcel->getActiveSheet()->setCellValue('E1', 'Store Total Stock');    
//    $objPHPExcel->getActiveSheet()->setCellValue('F1', 'Store Minimum Stock Level');
//    $objPHPExcel->getActiveSheet()->setCellValue('G1', 'Difference');
        
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
//    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
//    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
//    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
//      $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
    
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
//    $objPHPExcel->getActiveSheet()->getStyle('D1')->applyFromArray($styleArray);
//    $objPHPExcel->getActiveSheet()->getStyle('E1')->applyFromArray($styleArray);
//    $objPHPExcel->getActiveSheet()->getStyle('F1')->applyFromArray($styleArray);
//     $objPHPExcel->getActiveSheet()->getStyle('G1')->applyFromArray($styleArray);
     
    $objPHPExcel->getActiveSheet()->getStyle('A')->applyFromArray($cellstyleArray);
    $objPHPExcel->getActiveSheet()->getStyle('B')->applyFromArray($cellstyleArray);
    $objPHPExcel->getActiveSheet()->getStyle('C')->applyFromArray($cellstyleArray);
//    $objPHPExcel->getActiveSheet()->getStyle('D')->applyFromArray($cellstyleArray);
//    $objPHPExcel->getActiveSheet()->getStyle('E')->applyFromArray($cellstyleArray);
//    $objPHPExcel->getActiveSheet()->getStyle('F')->applyFromArray($cellstyleArray);
//     $objPHPExcel->getActiveSheet()->getStyle('G')->applyFromArray($cellstyleArray);
    $colCount=0;
    $rowCount=2;
    
    foreach ($dealersList as $key => $value) {
        $arr = explode("::",$value);
        $store_name = trim($arr[0]);$stock_intransit = trim($arr[1]);
//        $tot_stk = trim($arr[3]);$msl = trim($arr[4]); 
//         $diff = trim($arr[5]);
        
        
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $rowCount, $key);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $rowCount, $store_name);
//        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $rowCount, $curr_stock);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $rowCount, $stock_intransit);
//        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $rowCount, $tot_stk);        
//        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $rowCount, $msl);        
//          $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $rowCount, $diff);   
        $rowCount++;
    }    
    
    
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $filename = "dealerStockIntransitAboveFiveLakhs_".date("Ymd-His").".xls";  
//    $fpath = "C:/xampp/htdocs/limelight_new/daemons/dealerStockIntransitAboveFiveLakhsFiles/$filename";  //For Local
     $fpath = "/var/www/html/linenking/daemons/dealerStockIntransitAboveFiveLakhsFiles/$filename";  //For Live
//    $fpath = "/var/www/cottonking_new/daemons/dealerBelowMSLFiles/$filename";
    $objWriter->save(str_replace(__FILE__, $fpath, __FILE__));    
    
//    return $filename;
    return $fpath;	
}

 function sendEmail($fpath){    
     $filename=basename($fpath);
     $db = new DBconn();
     $emailHelper = new EmailHelper();
//     $qry = "select email from it_codes where usertype = ".UserType::CKAdmin." and id in (68,129,130)" ; 
//     $aobjs = $db->fetchObjectArray($qry);
     // sends email to koushik,kunal
//     if($aobjs){
        $toArray = array();
//        foreach($aobjs as $aobj){ 
//            $emails = explode(",",$aobj->email);
//            foreach($emails as $email){ array_push($toArray, $email);}              
//        }
         array_push($toArray, 'koushik.marathe@kinglifestyle.com');
         array_push($toArray, 'kunal.marathe@kinglifestyle.com');
         array_push($toArray, 'harshada.marathe@kinglifestyle.com');
         array_push($toArray, 'rohan.phalke@kinglifestyle.com');
         array_push($toArray, 'prashant.mane@kinglifestyle.com');
         array_push($toArray,"samir.joshi@kinglifestyle.com");
//         array_push($toArray, 'abhoir@intouchrewards.com');
        
        
        if(!empty($toArray)){                                   
            print "<br>";
            //print_r($toArray);
            $subject = "Linenking Franchisee(s) list having Stock In Transit Above Rs 5 Lakhs.";           
            $body = "<p>This weekly email provides a list of franchisees(s) whose store stock in transit is Above Rs. 5 Lakhs. </p><br/>";
            $body .= "PFA $filename<br/>";
            $errormsg = $emailHelper->send($toArray, $subject, $body ,array($fpath));
            print "<br>EMAIL SENT RESP:".$errormsg;
            if ($errormsg != "0") {
                $errors['mail'] = " <br/> Error in sending mail, please try again later.";
            } 
        }
//     }
 }