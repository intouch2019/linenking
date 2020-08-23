<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";

extract($_POST);
$user = getCurrUser();
if ($user->usertype != UserType::Admin && $user->usertype != UserType::GoodsInward) { print "You are not authorized to add a User"; return; }

$errors=array();
$success=array();
try {
	$_SESSION['form_post'] = $_POST;
	$db = new DBConn();
	$fullname = isset($fullname) && trim($fullname) != "" ? $db->safe($fullname) : false;
	if (!$fullname) { $errors['fullname'] = "Please enter the Transporter Name"; }
	if (count($errors) == 0) {
		$query = "insert into it_transporters set name=$fullname";
		echo $query;
                $db->execInsert ($query);
		$success = "$fullname has been added";
	}
} catch (Exception $xcp) {
	$clsLogger = new clsLogger();
	$clsLogger->logError("Failed to add $fullname:".$xcp->getMessage());
	$errors['status']="There was a problem processing your request. Please try again later";
}
if (count($errors) > 0) {
	$_SESSION['form_errors'] = $errors;
	$redirect = "transporters/add";
} else {
	unset($_SESSION['form_errors']);
//        $_SESSION['form_success'] = $success;
	$redirect = "transporters";	 
}
session_write_close();
//header("Location: ".DEF_SITEURL.$redirect);
exit;

?>
