<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once 'lib/users/clsUsers.php';

extract($_POST);
$errors = array();
$success = array();
$db = new DBConn();
//$startdate = $db->safe(yymmdd($from));
//$enddate = $db->safe(yymmdd($to));

$user = getCurrUser();
$userpage = new clsUsers();
$pagecode = $db->safe($_SESSION['pagecode']);
$page = $db->fetchObject("select pagecode from it_pages where pagecode = $pagecode");
if ($page) {
    $allowed = $userpage->isAuthorized($user->id, $page->pagecode);
    if (!$allowed) {
        header("Location: " . DEF_SITEURL . "unauthorized");
        return;
    }
} else {
    header("Location:" . DEF_SITEURL . "nopagefound");
    return;
}

if ($tallytype == "1") { // means All BHM store counter sale xml
    $redirect = "formpost/generateBHMCounterSaleXML.php?d1=$from&d2=$to";
    header("Location: " . DEF_SITEURL . "$redirect");
}
if ($tallytype == "2") { // means All BHM Store Cash Receipt Voucher lk
    $redirect = "formpost/generateBHMCashReceiptVoucherXML.php?d1=$from&d2=$to";
    header("Location: " . DEF_SITEURL . "$redirect");
}
if ($tallytype == "3") { // means All BHM Store Counter sale lk
    $redirect = "formpost/generateBHM50CounterSaleXML.php?d1=$from&d2=$to";
    header("Location: " . DEF_SITEURL . "$redirect");
}

if (count($errors) > 0) {
    $_SESSION['form_errors'] = $errors;
    session_write_close();
    header("Location: " . DEF_SITEURL . "bhm/tallytransfer");
    exit;
} else {
    unset($_SESSION['form_errors']);
    $_SESSION['form_success'] = $success;
}

