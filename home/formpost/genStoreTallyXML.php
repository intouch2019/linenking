<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once 'lib/users/clsUsers.php';

extract($_POST);
$user = getCurrUser();
$errors=array();
$success=array();
$db = new DBConn();
$startdate = $db->safe(yymmdd($from));
$enddate = $db->safe(yymmdd($to));

$user = getCurrUser();
$userpage = new clsUsers();
$pagecode = $db->safe($_SESSION['pagecode']);
$page = $db->fetchObject("select * from it_pages where pagecode = $pagecode");
if($page){
    $allowed = $userpage->isAuthorized($user->id, $page->pagecode);
    if (!$allowed) { header("Location: ".DEF_SITEURL."unauthorized"); return; }
}else{ header("Location:".DEF_SITEURL."nopagefound"); return; }

if($tallytype == "1"){ // means store purchase voucher xml
     $redirect = "formpost/genStorePurVoucherXML.php?d1=$from&d2=$to";
     header("Location: ".DEF_SITEURL."$redirect");
 }else if($tallytype == "2"){ // means store debit voucher xml
     $redirect = "formpost/genStoreDebitVoucherXML.php?d1=$from&d2=$to";
     header("Location: ".DEF_SITEURL."$redirect");
 }else if($tallytype == "3"){ // means store sales voucher xml
     $redirect = "formpost/genStoreSalesVoucherXML.php?d1=$from&d2=$to";
     header("Location: ".DEF_SITEURL."$redirect");     
 }else if($tallytype == "4"){ // means store sales voucher xml
     $redirect = "formpost/genStoreGSTPurVoucherXML.php?d1=$from&d2=$to";
     header("Location: ".DEF_SITEURL."$redirect");     
 }else if($tallytype == "5"){ // means store sales voucher xml
     $redirect = "formpost/genStoreGSTPurVoucherXML2018.php?d1=$from&d2=$to";
     header("Location: ".DEF_SITEURL."$redirect");     
 }else if($tallytype == "6"){ // means store sale back sales voucher xml
     $redirect = "formpost/genStoreGSTSalebackVoucherXML2018.php?d1=$from&d2=$to";
     header("Location: ".DEF_SITEURL."$redirect");     
 }else if ($tallytype == "7") {
    $redirect = "formpost/genStoreRetailSaleXML.php?d1=$from&d2=$to";
     header("Location: ".DEF_SITEURL."$redirect");     
}

     
if (count($errors) > 0) {
	$_SESSION['form_errors'] = $errors;
        session_write_close();
        header("Location: ".DEF_SITEURL."admin/tallytransfer");
        exit;
} else {
	unset($_SESSION['form_errors']);
        $_SESSION['form_success'] = $success;
}
 
?>
