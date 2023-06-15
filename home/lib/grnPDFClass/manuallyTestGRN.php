<?php
//@set_magic_quotes_runtime(false);
//ini_set('magic_quotes_runtime', 0);
require_once "../../it_config.php";
require_once 'lib/db/DBConn.php';
require_once 'lib/core/Constants.php';
require_once "lib/grnPDFClass/EmailHelper.php";
require_once "lib/logger/clsLogger.php";
//require_once "lib/email/EmailHelper.php";

//class GRN_PDF_Mail {
    
//function sendMail()//$pdfidarr)
  //  {
//    print"<br>in send mail<br>";
    $db = new DBconn();
    $emailHelper = new EmailHelper();
    $toArray=array();
    $pdf_ids = "";
    //$dt=  date('Y-m-d');
    $fpatharr = array();//pdfname array
   // array_push($fpatharr,'/var/www/cottonking_new/pdf/2776_5900111072.pdf');
//    foreach ($pdfidarr as $pdfid){ 
//        $query= "select * from it_grn_pdfs where id=$pdfid and is_mailed=0";
//        $pdfobj= $db->fetchObject($query);
//        if(isset($pdfobj)){
//           $pdfname=$pdfobj->pdfname; 
//           array_push($fpatharr,$pdfname);
//        }
//    }
    
    //$date = date('Y-m-d');
    $date='2023-06-14';
     $query= "select * from it_grn_pdfs where is_mailed=0 and createtime >= '$date 00:00:00' and createtime <= '$date 23:59:59'";
//     echo $query;
        $pdfobjs= $db->fetchObjectArray($query);
        if(isset($pdfobjs)){
            foreach($pdfobjs as $pdfobj){
                $pdfname = $pdfobj->pdf_file_path; 
                array_push($fpatharr,$pdfname);
                $pdf_ids .= $pdfobj->id.",";
            }
            $pdf_id_list = rtrim($pdf_ids,",");
           
        }
//        echo "PDF IDS: ".$pdf_id_list;
        //email to all dealers
//        $query = "select id,email,email2 from it_codes where id not in(select id from  it_codes where usertype = ".UserType::Dealer." and is_autorefill = 1 and is_closed = 0 and inactive = 0 and sbstock_active = 1 and sequence is not null and sequence > 0 order by sequence) and usertype=".UserType::Dealer." and inactive=0 and is_closed=0 or id in (68)";
//        $query="select id,email,email2 from it_codes where usertype=".UserType::Dealer." and inactive=0 and is_closed=0 or id in (68)";
//        // $query = "select * from it_codes where id in (68,90,70)";
//        $objs = $db->fetchObjectArray($query);
//        
//         foreach($objs as $obs){
//            if(trim($obs->email)!=""){
//              $arr = explode(",", $obs->email);
//              foreach($arr as $key => $email){
//                 array_push($toArray,$email);   
//              }
////              array_push($toArray,$obs->email);  
//            }
//            if(trim($obs->email2)!=""){
//               $arr2 = explode(",", $obs->email2);
//              foreach($arr2 as $key => $email2){
//                 array_push($toArray,$email2);   
//              } 
//              //array_push($toArray,$obs->email2);  
//            }
//        }
       array_push($toArray,"djagtap@intouchrewards.com");        
//	array_push($toArray,"samir.joshi@kinglifestyle.com");
//        array_push($toArray,"ranjeet.mundekar@kinglifestyle.com");
        
      //  print_r($fpatharr);
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
          //--> log code ends here
        if ($errormsg != "0") {
            $errors['mail'] = " <br/> Error in sending mail, please try again later.";
//             print"mail send failed<br>";
            return -1;
        } 
        else
        {
            //foreach ($pdfidarr as $pdfid){ 
                $query= "update it_grn_pdfs set is_mailed=1  where id in ($pdf_id_list)";
                $db->execUpdate($query);
           // }
//            print"mail send success<br>";
            return 1;
        }
    
    }else{
//      print"<br> No PDF to send<br>"; 
   }
  // }
//}
