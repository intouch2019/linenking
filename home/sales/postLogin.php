<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once("lib/codes/clsCodes.php");
require_once("lib/logger/clsLogger.php");

extract($_POST);
$errors=array();
$logger = new clsLogger();
try {
	$storecode=trim($storecode);
	$_SESSION['form_storecode']=$storecode;
	$password=urldecode($password);
	if (!$storecode) { $errors['storecode']='Enter your Username'; }
	if (!$password) { $errors['password']='Enter your Password'; }
	if (count($errors) == 0) {
		$clsCodes = new clsCodes();
		$codeInfo = $clsCodes->isAuthentic($storecode, $password);
		if (!$codeInfo) {
			$errors['password']='Incorrect Username or Password';
		} else {
			$_SESSION['currStore'] = $codeInfo;
			$logger->logInfo("Login:$storecode");
		}
	}
} catch (Exception $xcp) {
	$logger->logError("Failed to login $storecode:".$xcp->getMessage());
	$errors['status']="There was a problem processing your request. Please try again later";
}
if (count($errors) > 0) {
	$_SESSION['form_errors'] = $errors;
	$redirect = "store";
} else {
	unset($_SESSION['form_errors']);
	if ($codeInfo->id == "20") { // mall
		$redirect = "store/mall/overview";
	} else if ($codeInfo->id == 37) { // mretail
		$redirect = "store/corporate";
	} else {
		$redirect = "store/dashboard";
	}
}
session_write_close();
header("Location: ".DEF_SITEURL.$redirect);
exit;

?>
