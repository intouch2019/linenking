<?php

require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/serverChanges/clsServerChanges.php";
require_once 'lib/users/clsUsers.php';
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

