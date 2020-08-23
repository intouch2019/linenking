<?php
require_once "../../it_config.php";
require_once 'lib/db/DBConn.php';
require_once 'lib/core/Constants.php';
require_once "lib/grnPDFClass/EmailHelper.php";
require_once "lib/logger/clsLogger.php";


    $db = new DBconn();
    $emailHelper = new EmailHelper();
    $toArray=array();
    $pdf_ids = ""; 
    $fpatharr = array(); 
    
    $date = date('Y-m-d');
     $query= "select * from it_grn_pdfs where is_mailed_1=0 and createtime >= '$date 00:00:00' and createtime <= '$date 23:59:59'";
     //echo $query;
//      error_log("\n|Query: $query ",3,"tmp_1.txt");
        $pdfobjs= $db->fetchObjectArray($query);
        if(isset($pdfobjs)){
            foreach($pdfobjs as $pdfobj){
                $pdfname = $pdfobj->pdf_file_path; 
                array_push($fpatharr,$pdfname);
                $pdf_ids .= $pdfobj->id.",";
            }
            $pdf_id_list = rtrim($pdf_ids,",");
           
        }
        sleep(1200);
//        $query = "select * from it_codes where usertype=".UserType::Dealer." and inactive=0 and is_closed=0 ";
        $query="select id,email,email2 from  it_codes where usertype = ".UserType::Dealer." and is_autorefill = 1 and is_closed = 0 and inactive = 0 and sbstock_active = 1 and sequence is not null and sequence > 0 order by sequence";
       // $query = "select * from it_codes where id in (67,218,98)";
        $objs = $db->fetchObjectArray($query);
        
        foreach($objs as $obs){
            if(trim($obs->email)!=""){
              $arr = explode(",", $obs->email);
              foreach($arr as $key => $email){
                 array_push($toArray,$email);   
              }
//              array_push($toArray,$obs->email);  
            }
            if(trim($obs->email2)!=""){
               $arr2 = explode(",", $obs->email2);
              foreach($arr2 as $key => $email2){
                 array_push($toArray,$email2);   
              } 
              //array_push($toArray,$obs->email2);  
            }
       
	
	
        
        
      //  print_r($fpatharr);
        if(count($fpatharr)>0 &&count($toArray) >=90){
        $subject = "New Designs Released";
        $body = "<br>Dear All,<br><br><br>";
        $body .= "<p>Please find the below given attachment of the new designs released. Please have a look and place your orders on the portal as per your requirements.</p>";
        $body .= "<b>Note : This is auto generated email ,please do not reply this email.</b><br/><br>";
        $body .= "<b><br>From</b><br/>";
        $body .= "<b>Dispatch Department,</b><br/>";
        $body .= "<b>Baramati</b>";
        $body .= "<br/>";
   
        $errormsg = $emailHelper->send($toArray, $subject, $body, $fpatharr);
          unset($toArray);
          $toArray=array();
          $clsLogger = new clsLogger();
          $ipaddr =  $_SERVER['REMOTE_ADDR'];
          $pg_name = __FILE__;                
          $clsLogger->logInfo($errormsg,false, $pg_name,$ipaddr);
          }
        }
 
          //Send email to remaining dealer
      if(count($fpatharr)>0){
        $subject = "New Designs Released";
        $body = "<br>Dear All,<br><br><br>";
        $body .= "<p>Please find the below given attachment of the new designs released. Please have a look and place your orders on the portal as per your requirements.</p>";
        $body .= "<b>Note : This is auto generated email ,please do not reply this email.</b><br/><br>";
        $body .= "<b><br>From</b><br/>";
        $body .= "<b>Dispatch Department,</b><br/>";
        $body .= "<b>Baramati</b>";
        $body .= "<br/>";
       
 
        $errormsg = $emailHelper->send($toArray, $subject, $body, $fpatharr);
//        print_r($errormsg);
        //--> code to log email tracking
          $clsLogger = new clsLogger();
          $ipaddr =  $_SERVER['REMOTE_ADDR'];
          $pg_name = __FILE__;                
          $clsLogger->logInfo($errormsg,false, $pg_name,$ipaddr);
          }
          if(count($fpatharr)>0){
          //--> log code ends here
        if ($errormsg != "0") {
            $errors['mail'] = " <br/> Error in sending mail, please try again later.";
//             print"mail send failed<br>";
            return -1;
        } 
        else
        {
            //foreach ($pdfidarr as $pdfid){ 
                $query= "update it_grn_pdfs set is_mailed_1=1  where id in ($pdf_id_list)";
                $db->execUpdate($query);
           // }
//            print"mail send success<br>";
            return 1;
        }
    
    }else{
//      print"<br> No PDF to send<br>"; 
   }
   
