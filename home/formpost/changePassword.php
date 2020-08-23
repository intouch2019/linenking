<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once 'lib/users/clsUsers.php';

extract($_POST);
$user = getCurrUser();
$db = new DBConn();
$userpage = new clsUsers();
$pagecode = $db->safe($_SESSION['pagecode']);
$page = $db->fetchObject("select * from it_pages where pagecode = $pagecode");
if($page){
    $allowed = $userpage->isAuthorized($user->id, $page->pagecode);
    if (!$allowed) { header("Location: ".DEF_SITEURL."unauthorized"); return; }
}else{ header("Location:".DEF_SITEURL."nopagefound"); return; }

$errors=array();
$success=array();
try {
	$db = new DBConn();
        $currUser = getCurrUser();
        $userid = $currUser->id;
	$userp=trim($password);
        $pass=$db->safe(md5($password));
	if (trim($password) == "" || trim($password2) == "") {
            $errors['password']='Password cannot be empty';
	} else
         if ($userp != $password2) {
            $errors['password']='Passwords do not match';
 	} else {
            $up = $db->execUpdate ("update it_codes set password=$pass where id=$userid");
            $success = 'Password changed';
 	}
 

} catch (Exception $xcp) {
	$clsLogger = new clsLogger();
	$clsLogger->logError("Failed to change password:$userid:".$xcp->getMessage());
	$errors['password']="There was a problem processing your request. Please try again later";
}
if (count($errors) > 0) {
	$_SESSION['form_errors'] = $errors;
	$redirect = "user/settings";
} else {
	unset($_SESSION['form_errors']);
        $_SESSION['form_success'] = $success;
	$redirect = "user/settings";
}
session_write_close();
header("Location: ".DEF_SITEURL.$redirect);
exit;

?>
