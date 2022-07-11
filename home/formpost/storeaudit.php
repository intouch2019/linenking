<?php

require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/serverChanges/clsServerChanges.php";
require_once 'lib/users/clsUsers.php';
require_once "lib/email/EmailHelper.php";
require_once "lib/sms/SMSHelper.php";
//print "Yes";

$_SESSION['form_post'] = $_POST;
extract($_POST);

//print_r($_POST);

//exit;
$errors = array();
$success = array();
$db = new DBConn();
$store = getCurrUser();
$userpage = new clsUsers();


$pagecode = $db->safe($_SESSION['pagecode']);
$page = $db->fetchObject("select * from it_pages where pagecode = $pagecode");


if ($page) {
    $allowed = $userpage->isAuthorized($store->id, $page->pagecode);
    
    if (!$allowed) {
        header("Location: " . DEF_SITEURL . "unauthorized");
        return;
    }
} else {
    header("Location:" . DEF_SITEURL . "nopagefound");
    return;
}






if (!$Manager_name || !$Managerphone || !$Auditor_name || !$AuditDate ||  !$remark ||"") {
    $errors['storec'] = "Please enter value for all required field marked with *";
} else {
    try {

       
        $Manager_name = $db->safe($Manager_name);
        $Managerphone = $db->safe($Managerphone);
        $Auditor_name = $db->safe($Auditor_name);
        $AuditDate = $db->safe($AuditDate);
        $remark = $db->safe($remark);
        
                
                $qry = "insert into it_auditdetails set store_id=$store_id,Manager_name=$Manager_name,Managerphone=$Managerphone, Auditor_name=$Auditor_name,"
                        . " AuditDate=$AuditDate, SubmittedDate=now(), remark=$remark, auditby_id=$store->id ";
                //print_r($qry);
                $audit_id = $db->execInsert($qry);
               
                $objs = $db->fetchObjectArray("select id from it_auditquestions ");
                
                
                foreach ($objs as $obj) {
                    $opt= "que".$obj->id;
                    $isopted = $$opt;
                     $qry = "insert into it_auditresponse set audit_id=$audit_id,question_id=$obj->id,is_opted=$isopted";
                    // print_r($qry);
                     $discinsert = $db->execInsert($qry);
                  } 
                  
                  
      ////send sms starts here///////

    //$db = new DBconn();
//    $sent_status = 1;    

//    $stores = $db->fetchObjectArray("select id,phone,email,store_name from it_codes where id in ($store_id)");
    $stores = $db->fetchObjectArray("select a.Managerphone,a.AuditDate,a.remark,s.id,s.phone,s.email,s.store_name from it_auditdetails a, it_codes s  where a.store_id= s.id and s.id=$store_id and a.id=$audit_id");                
    foreach ($stores as $store) {

        $date=date('Y-m-d', strtotime($store->AuditDate));
//        print_r($date);exit();
        $Managerphone= $store->Managerphone;
        $phoneno = $store->phone;
       
        //$message = "Audit report for the store - $store->store_name is submitted on the portal. Date $date"; //%26 for &
        $message =  "LK Audit report for the store - $store->store_name is submitted on the portal. Date $date";
        $smsHelper = new SMSHelper();
        
        if($phoneno!= null || $phoneno!= "")
        {
        $errormsg = $smsHelper->sendSMS($phoneno,$message);
//        print_r($errormsg);
        }
       
        
        if($Managerphone!= null || $Managerphone!= "")
        {
        $errormsg = $smsHelper->sendSMS($Managerphone,$message);
//         print_r($errormsg);
        }

        //print_r($errormsg);

        //exit();
        ////send sms ends here///////


    /////////////////Email send code starts here /////////

         $emailHelper = new EmailHelper();

         $toArray = array();
         $ccArray = array();     

         if(isset($store)){
             if($store->email != null){array_push($toArray,$store->email);}
             if($store->email2 != null){array_push($toArray,$store->email2);}
         }

         array_push($toArray,"abhoir@intouchrewards.com");

            if(!empty($toArray)){
    //            $subject = "CK-The stock for Invoice $invoice->invoice_no has been dispatched on $invoice->datetime.";
                $subject = "Store audit report, audit conducted on $date";
                //$body = "<p>To </p>";
                $body .= "<p>Dear Store Owner/ Store Manager, </p>";
                $body .= "<p>Audit for the store $store->store_name is conducted on date $date, <br>";
                $body .= "and is submitted on the portal. The observations and remarks are as follows: <br>";
                $body .= "$store->remark</p>";

                $body .= "<p>You are expected to take the appropriate actions as suggested by the auditor before the next audit. <br>";
                $body .= "For any queries or clarifications, reach out to the respective store auditor.   </p>";
                
                $body .= "<p>**** This is a system generated email, Do not reply to this email. ****   </p>";

    //            $body .= "PFA , <br/>";
                $errormsg = $emailHelper->send($toArray, $subject, $body ,array(), $ccArray);
                print "<br>EMAIL SENT RESP:".$errormsg;
            }
    }
/////////////////Email send code ends here /////////            
                
            
        
    } catch (Exception $xcp) {
        $clsLogger = new clsLogger();
        $clsLogger->logError("Failed to add $storecode:" . $xcp->getMessage());
        $errors['status'] = "There was a problem processing your request. Please try again later";
    }
}
if (count($errors) > 0) {
    $_SESSION['form_errors'] = $errors;
    $redirect = "addstoreadit";
} else {
    unset($_SESSION['form_errors']);
    $_SESSION['form_success'] = $success;
    $redirect = "store/audit/sid=$store_id";
}
session_write_close();
header("Location: ".DEF_SITEURL.$redirect);
exit;


?>

