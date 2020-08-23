<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";

extract($_POST);
$user = getCurrUser();
if ($user->usertype != UserType::Admin && $user->usertype != UserType::CKAdmin) { print "You are not authorized to add a User"; return; }

$errors=array();
$success=array();
try {
	$_SESSION['form_post'] = $_POST;
	$db = new DBConn();
	$fullname = isset($fullname) && trim($fullname) != "" ? $db->safe($fullname) : false;
	if (!$fullname) { $errors['fullname'] = "Please enter the Full Name"; }
        $contactPerson = isset($contactPerson) && trim($contactPerson) != "" ? $db->safe($contactPerson) : false;
        if (!$contactPerson) { $errors['contactPerson'] = "Please enter name of Person to be contact"; }
        $address  = isset($address) && trim($address) != "" ? $db->safe($address) : false;
        if(!$address) {$errors['address'] = "Please enter the Address";}
        $email = isset($email) && trim($email) != "" ? $db->safe($email) : false;
        if (!$email) { $errors['email'] = "Please enter the Email"; }
        $phone = isset($phone) && trim($phone) != "" ? $db->safe($phone) : false;
        if(!$phone) {$errors['phone'] = "Please enter Phone Number";}
        $vatno = isset($vatno) && trim($vatno) != "" ? $db->safe($vatno) : false;
        if(!$vatno) { $errors['vatno'] = "Please enter VAT No";}
	if (count($errors) == 0) {
		$query = "insert into it_suppliers set name=$fullname, contact_person=$contactPerson, address=$address, emailaddress=$email, phoneno=$phone, vatno=$vatno";
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
	$redirect = "suppliers/add";
} else {
	unset($_SESSION['form_errors']);
//        $_SESSION['form_success'] = $success;
	$redirect = "suppliers";	 
}
session_write_close();
header("Location: ".DEF_SITEURL.$redirect);
exit;

?>
