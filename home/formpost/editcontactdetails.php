<?php

require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once 'lib/users/clsUsers.php';

extract($_POST);
//print_r($_POST['Designation']);
if (isset($_POST['id']) && $_POST['id'] !== "") {
    $id = $_POST['id'];
}
if (isset($_POST['addnew']) && $_POST['addnew'] !== "") {
    $addnew = $_POST['addnew'];
} else {
    $addnew = 0;
}
$db = new DBConn();
$designation = $db->safe($_POST['Designation']);
$name = $db->safe($_POST['Name']);
$contactno = $db->safe($_POST['Contactno']);
$email= $db->safe($_POST['Email']);
$user = getCurrUser();
$db = new DBConn();
$userpage = new clsUsers();
$pagecode = $db->safe($_SESSION['pagecode']);
$page = $db->fetchObject("select * from it_pages where pagecode = $pagecode");
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

$errors = array();
$success = array();
try {
    $db = new DBConn();
    if (isset($id) && $id !== "") {
        $record = $db->fetchObject("select * from contactdetails where id = $id");
    }
    if (isset($record) && $record !== "" && isset($id) && $id !== "") {
        $db->execUpdate("update contactdetails set Name=$name, contactno=$contactno, designation=$designation, email=$email where id=$id");
    } else if ($addnew == 1) {
        $db->execInsert("insert into contactdetails set Name=$name, contactno=$contactno, designation=$designation, email=$email, createtime=now(), inactive=0");
    }
} catch (Exception $xcp) {
    $clsLogger = new clsLogger();
    $clsLogger->logError("Failed to change password:$userid:" . $xcp->getMessage());
    $errors['password'] = "There was a problem processing your request. Please try again later";
}
if (count($errors) > 0) {
    $_SESSION['form_errors'] = $errors;
    $redirect = "companydetails";
} else {
    unset($_SESSION['form_errors']);
    $_SESSION['form_success'] = $success;
    $redirect = "companydetails";
}
session_write_close();
echo $redirect;
header("Location: ".DEF_SITEURL.$redirect);
exit;
?>