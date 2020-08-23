<?php
require_once "../../it_config.php";
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php"; 
require_once 'lib/users/clsUsers.php';
//print_r ($_POST);
extract($_POST);
$store = getCurrUser();
$db = new DBConn();
$userpage = new clsUsers();
$pagecode = $db->safe($_SESSION['pagecode']);
$page = $db->fetchObject("select * from it_pages where pagecode = $pagecode");
if($page){
    $allowed = $userpage->isAuthorized($store->id, $page->pagecode);
    if (!$allowed) { header("Location: ".DEF_SITEURL."unauthorized"); return; }
}else{ header("Location:".DEF_SITEURL."nopagefound"); return; }
$storeid = getCurrUserId();
//print_r ($storeid);print "<br>";
$errors=array();
$success=array();
try {
	$db = new DBConn();
        $exist = $db->fetchObject("select * from it_ck_pickgroup where id=$pickid");
        //print_r ($exist);
	if (!$exist) {$errors['design']="selected order was not found"; }
        else if ($exist->dispatcher_id != $storeid) { $errors['disp']="Only dispatcher who shipped the order allowed to edit transport"; } 
        else {
            $transport_dtl = $db->safe(trim($transport_dtl));
            if(isset($remark) && trim($remark) != ""){ $remark = $db->safe($remark);$rclause = " , remark = $remark ";}else{ $rclause = ""; }
            $update = $db->execUpdate("update it_ck_pickgroup set transport_dtl=$transport_dtl $rclause where id=$pickid");
            if ($update != "0") { $success = "The transport details for the order updated"; }
        }
} catch (Exception $xcp) {
	$clsLogger = new clsLogger();
	$clsLogger->logError("Failed to add $fullname:".$xcp->getMessage());
	$errors['status']="There was a problem processing your request. Please try again later";
}

if (count($errors) > 0) {
	$_SESSION['form_errors'] = $errors;
} else {
	unset($_SESSION['form_errors']);
        $_SESSION['form_success'] = $success;
}
$redirect= "dispatch/edittransport/pid=$pickid"; 
session_write_close();
header("Location: ".DEF_SITEURL.$redirect);
exit;
?>
