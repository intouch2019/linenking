<?php
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/email/EmailHelper.php";
require_once "lib/sms/SMSHelper.php";

//$db = new DBConn();

extract($_GET);
//print_r($_GET);

////send sms starts here///////

    $db = new DBconn();
    $sent_status = 1;

    $store = $db->fetchObject("select phone,email,email2,store_name from it_codes where id = $storeid");
    $invoice = $db->fetchObject("select id,invoice_no,now() as datetime from it_sp_invoices where id = $invoiceid");
    
    $phoneno = $store->phone;
    $message = "Stock for the Invoice $invoice->invoice_no is dispatched. Driver Name $drivername, Mobile no. $drivermob & Vehicle no $vehicleno."; //%26 for &

    $transport_insert_id = $db->execInsert("insert into it_invoice_transport_details set store_id=$storeid, invoice_id=$invoiceid,invoice_no='$invoice->invoice_no',transporter='$transporter',vehicleno='$vehicleno',driver_name='$drivername',driver_mob='$drivermob'");

    $smsHelper = new SMSHelper();
    $errormsg = $smsHelper->sendSMS($phoneno,$message);
    //print_r($errormsg);

//    if(strpos($errormsg, 'Message Submitted Successfully') !== false){
//
//        $db->execQuery("update it_invoice_transport_details set is_sms_sent=1, sent_sms_response='$errormsg', updatetime=now() where id = $transport_insert_id");
//    }else{
//        $sent_status = 2;
//    }

    //exit();
    ////send sms ends here///////


/////////////////Email send code starts here RGhule/////////

     $emailHelper = new EmailHelper();
     
     $toArray = array();
     $ccArray = array();     
     
     if(isset($store)){
         if($store->email != null){array_push($toArray,$store->email);}
         if($store->email2 != null){array_push($toArray,$store->email2);}
     }
     
//     array_push($toArray,"rghule@intouchrewards.com");
        
        if(!empty($toArray)){
            $subject = "LK-The stock for Invoice $invoice->invoice_no has been dispatched on $invoice->datetime.";
            $body = "<p>Hi ". $store->store_name.",</p>";
            $body .= "<p>The stock for Invoice $invoice->invoice_no has been dispatched on $invoice->datetime. </p>";
            $body .= "<p>Vehicle No : ".$vehicleno." <br>";
            $body .= "Driver Name : ".$drivername." <br>";
            $body .= "Driver Mobile : ".$drivermob."</p>";
            
            $body .= "<p>Thanks and Regards <br>";
            $body .= "Dispatch Team </p>";
            
            $body .= "<p>Fashioking brands pvt. ltd. </p><br>";
            
            $body .= "<p>Note:  This is a system generated mail. Do not reply to this email. </p>";
            
//            $body .= "PFA , <br/>";
            $errormsg = $emailHelper->send($toArray, $subject, $body ,array(), $ccArray);
            print "<br>EMAIL SENT RESP:".$errormsg;
            if ($errormsg != "0") {
                $errors['mail'] = " <br/> Error in sending mail, please try again later.";
                $sent_status = 2;
            }else{
                $db->execQuery("update it_invoice_transport_details set is_email_sent=1, updatetime=now() where id = $transport_insert_id");
            }
        }
// }
/////////////////Email send code ends here RGhule/////////
        

//header("Location: " . DEF_SITEURL . "invoice/sendmailandsms/sid=".$storeid."/invoiceid=".$invoiceid."/sent=1" );
header("Location: " . DEF_SITEURL . "invoice/sendmailandsms/sent=$sent_status" );
exit;