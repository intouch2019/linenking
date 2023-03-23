<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once 'lib/users/clsUsers.php';
extract($_POST);
$user = getCurrUser();
$userpage = new clsUsers();
$db = new DBConn();
$pagecode = $db->safe($_SESSION['pagecode']);
$page = $db->fetchObject("select * from it_pages where pagecode = $pagecode");
if($page){
    $allowed = $userpage->isAuthorized($user->id, $page->pagecode);
    if (!$allowed) { header("Location: ".DEF_SITEURL."unauthorized"); return; }
}else{ header("Location:".DEF_SITEURL."nopagefound"); return; }
//if ($user->usertype != UserType::Admin && $user->usertype != UserType::CKAdmin) { print "You are not authorized to add a User"; return; }
if (!$userid) { print "Missing parameter. Please report this error"; return; }

$errors=array();
try {
	$_SESSION['form_post'] = $_POST;
	$db = new DBConn();
	$fullname = isset($fullname) && trim($fullname) != "" ? $db->safe($fullname) : false;
	if (!$fullname) { $errors['fullname'] = "Please enter the Full Name"; }
	if ($user->usertype != UserType::NoLogin) {
		$email = isset($email) && trim($email) != "" ? $db->safe($email) : false;
		if (!$email) { $errors['email'] = "Please enter the Email"; }
                
                $mobile = isset($mobile) && trim($mobile) != "" ? $db->safe($mobile) : false;
		if (!$mobile) { $errors['mobile'] = "Please enter the Mobile No."; }
                
                $rolltype = isset($rolltype) && trim($rolltype) != "" ? $db->safe($rolltype) : false;
		if (!$rolltype) { $errors['rolltype'] = "Please enter the Department."; }
                
		if ($password != $password2) { $errors['password2'] = "Confirm Password does not match the value entered in the Password field"; }
	}
	if (count($errors) == 0) {
		$query = "update it_codes set store_name=$fullname, phone=$mobile, roles=$rolltype";
		if ($email) { $query .= ", email=$email"; }
		if ($password) {
	        	$password=$db->safe(md5($password));
			$query .= ", password=$password";
		}
		$query .= " where id=$userid";
		$db->execUpdate($query);
		$success = "$fullname has been updated";
		unset($_SESSION['form_post']);
	}
} catch (Exception $xcp) {
	$clsLogger = new clsLogger();
	$clsLogger->logError("Failed to update $fullname:".$xcp->getMessage());
	$errors['status']="There was a problem processing your request. Please try again later";
}
if (count($errors) > 0) {
	$_SESSION['form_errors'] = $errors;
	$redirect = "admin/users/edit/id=$userid";
} else {
	unset($_SESSION['form_errors']);
//        $_SESSION['form_success'] = $success;
	$redirect = "admin/users";	 
}
session_write_close();
header("Location: ".DEF_SITEURL.$redirect);
exit;

?>