<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/serverChanges/clsServerChanges.php";
require_once 'lib/users/clsUsers.php';
require_once "lib/core/clsProperties.php";
require_once "lib/logger/clsLogger.php";

extract($_POST);
//print_r($_POST);
$errors=array();
$success=array();
$db = new DBConn();
$store = getCurrUser();
$userpage = new clsUsers();

$pagecode = $db->safe($_SESSION['pagecode']);
$page = $db->fetchObject("select * from it_pages where pagecode = $pagecode");
if($page){
    $allowed = $userpage->isAuthorized($store->id, $page->pagecode);
    if (!$allowed) { header("Location: ".DEF_SITEURL."unauthorized"); return; }
}else{ header("Location:".DEF_SITEURL."nopagefound"); return; }

if(trim($edreason)=="" && trim($edreason)=="$paymentlink"){
    $errors[] = "Please Enter Disabling reason or Paymentlink";
}
  $paymentlink = isset($paymentlink) && trim($paymentlink) != "" ? $db->safe($paymentlink) : false;
    $paymentlinkquery = ",paymentlink=$paymentlink";
    if (!$paymentlink) {
       $paymentlinkquery =",paymentlink= null";
    }
try{
    if(count($errors)==0){
        $reason_db = $db->safe(trim($edreason));
        if($cid=="-1"){ //means disabling stores login all together at one click
           $dbProperties = new dbProperties();
           $dbProperties->setBoolean(Properties::DisableUserLogins, true); 
           //$query = "update it_codes set inactive=1 inactivated_by = $store->id , inactivating_reason = $reason_db , inactive_dttm = now() where usertype = ".UserType::Dealer; 
           $query = "update it_codes set loginsdisable_by = $store->id , disablelogins_reason = $reason_db , disablelogins_dttm = now() where usertype = ".UserType::Dealer; 
        }else{
           $query = "update it_codes set inactive=1, inactivated_by = $store->id , inactivating_reason = $reason_db $paymentlinkquery, inactive_dttm = now() where id = $cid";
        }
       print "<br>".$query;
        $rowaffected = $db->execUpdate($query);
        $clsLogger = new clsLogger();
        $ipaddr = $_SERVER['REMOTE_ADDR'];
        $pg_name = __FILE__;
        $clsLogger->it_codes_logInfo($query, $store->id, $pg_name, $ipaddr);
//        if($rowaffected > 0){
//           $success = "Store Disabled successfully " ;
//        }else{
//            $errors[] = "Something went wrong. Please try again later";
//        }
    }
    
}catch(Exception $xcp){
 print $xcp->getMessage();   
}

if (count($errors) > 0) {
	$_SESSION['form_errors'] = $errors;
        if($cid=="-1"){ //means disabling stores login all together at one click
            $redirect = "admin/stores/disablelogins";
        }else{
	    $redirect = "stores/disablelogin/reason/id=".$cid;
        }
} else {
	unset($_SESSION['form_errors']);
        $_SESSION['form_success'] = $success;
	$redirect = "admin/stores";	 
}
session_write_close();
header("Location: ".DEF_SITEURL.$redirect);
exit;
