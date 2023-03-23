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


$errors=array();
$success=array();
try {
	$_SESSION['form_post'] = $_POST;
	$db = new DBConn();
	$usertype = isset($fullname) ? intval($usertype) : false;
	if ($usertype <= 0) { $errors['usertype'] = "Please select a valid User Type"; }
	else {
	$fullname = isset($fullname) && trim($fullname) != "" ? $db->safe($fullname) : false;
	if (!$fullname) { $errors['fullname'] = "Please enter the Full Name"; }
	if ($usertype != UserType::NoLogin) {
		$username = isset($username) && trim($username) != "" ? $db->safe($username) : false;
		if (!$username) { $errors['username'] = "Please enter the Username"; }
		$exist = $db->fetchObject("select * from it_codes where code=$username");
		if ($exist) {
			$errors['username']="Username $username already exists";
		}
		$email = isset($email) && trim($email) != "" ? $db->safe($email) : false;
		if (!$email) { $errors['email'] = "Please enter the Email"; }
                
                $mobile = isset($mobile) && trim($mobile) != "" ? $db->safe($mobile) : false;
		if (!$mobile) { $errors['mobile'] = "Please enter the Mobile No."; }
                
                $rolltype = isset($rolltype) && trim($rolltype) != "" ? $db->safe($rolltype) : false;
		if (!$rolltype) { $errors['rolltype'] = "Please enter the Department."; }
                
		if (!$password) { $errors['password'] = "Please enter the Password"; }
		if ($password != $password2) { $errors['password2'] = "Confirm Password does not match the value entered in the Password field"; }
	} else {
		$obj = $db->fetchObject("select max(id) as maxid from it_codes");
		$id = $obj->maxid + 1;
		$username = $db->safe("user$id");
		$password = $db->safe(md5($username.rand(103989,1883928)));
	}
	}
	if (count($errors) == 0) {
	        $pass=$db->safe(md5($password));
		$query = "insert into it_codes set usertype=$usertype, code=$username, password=$pass, store_name=$fullname, phone=$mobile, roles=$rolltype";
		if ($email) { $query .= ", email=$email"; }
		$user_id = $db->execInsert ($query);
                if($user_id){
                    $query=" select id, menuhead, pagename from it_pages where id in (select page_id from it_usertype_pages where usertype = $usertype) group by menuhead,pagename";
                    $allpgs = $db->fetchObjectArray($query);
                    foreach($allpgs as $pg){                        
                              $iq = "insert into it_user_pages set page_id = $pg->id , user_id = $user_id";
                              $db->execInsert($iq);                                                     
                    }
                }
		$success = "$fullname has been added";
	}
} catch (Exception $xcp) {
	$clsLogger = new clsLogger();
	$clsLogger->logError("Failed to add $fullname:".$xcp->getMessage());
	$errors['status']="There was a problem processing your request. Please try again later";
}
if (count($errors) > 0) {
	$_SESSION['form_errors'] = $errors;
	$redirect = "admin/users/add";
} else {
	unset($_SESSION['form_errors']);
//        $_SESSION['form_success'] = $success;
	$redirect = "admin/users";	 
}
session_write_close();
header("Location: ".DEF_SITEURL.$redirect);
exit;

?>