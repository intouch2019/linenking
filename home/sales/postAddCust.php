<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once("lib/codes/clsCodes.php");
require_once("lib/user/clsUser.php");
require_once("lib/messages/clsFanMessage.php");
require_once("lib/sms/clsSMSHelper.php");
require_once("lib/logger/clsLogger.php");

extract($_POST);
$errors=array();
if (isset($_POST['action_cancel']) || isset($_POST['action_done'])) {
	if (isset($_SESSION['form_phoneno'])) unset($_SESSION['form_phoneno']);
	if (isset($_SESSION['form_custname'])) unset($_SESSION['form_custname']);
	if (isset($_SESSION['form_response'])) unset($_SESSION['form_response']);
	return page_refresh($errors, null, "store");
}

try {
	$phoneno=trim($_POST['phoneno']);
	$phoneno = strip_non_numerics($phoneno);
	$_SESSION['form_phoneno']=$phoneno;
	$custname=trim($_POST['custname']);
	$_SESSION['form_custname']=$custname;
	if (!$phoneno) { $errors[]='Enter the Customers Phone Number'; }
	else
	if (strlen($phoneno) == 12 && string_begins_with($phoneno, "91")) {
		// valid phone no
	} else
	if (strlen($phoneno) != 10) { $errors[]="Invalid phone number:$phoneno"; }
	 
	if (count($errors) > 0) { return page_refresh($errors); }

	// if phoneno is 10 long then prefix "91" for India (hack)
	if (strlen($phoneno) == 10) { $phoneno = "91$phoneno"; }
	$clsUser = new clsUser();
	$userInfo = $clsUser->createOnce($phoneno, "-1", $custname);
	$intouchno = $clsUser->generateIntouchno($userInfo->id);
	
	$clsCodes = new clsCodes();
	if ($clsCodes->fanExists($gCurrStore->id, $userInfo->id)) {
		$errors[] = "This customer is already a fan";
		return page_refresh($errors,null,"store/addcust/done");
	}
	$clsCodes->addFan($gCurrStore->id, $userInfo->id, "-1");
	if ($gCurrStore->signupmsg) {
		$smsMessage = clsFanMessage::addCustomerSignupOffer($gCurrStore->code, $gCurrStore->store_name, $intouchno, $gCurrStore->signupmsg, DEF_SMS_PHONENO)->getMessage($userInfo->locale);
	} else {
		$smsMessage = clsFanMessage::addCustomer($gCurrStore->code, $gCurrStore->store_name, $intouchno, DEF_SMS_PHONENO)->getMessage($userInfo->locale);
	}

	// send signup message to customer
	$smsHelper = new clsSMSHelper();
	$retval = $smsHelper->sendOne($userInfo->phoneno, $smsMessage);
	$logger = new clsLogger();
	$logger->logInfo($smsMessage."|".$retval);

	$success = "Customer successfully added.";
	return page_refresh($errors,$success,"store/addcust/");
} catch (Exception $xcp) {
	$clsLogger = new clsLogger();
	$clsLogger->logError("Failed to addcust $gCurrStore->code, $phoneno:".$xcp->getMessage());
	$errors['status']="There was a problem processing your request. Please try again later";
}
page_refresh($errors);
exit();

function page_refresh($errors, $success=null, $redirect="store/addcust") {
$form_response=false;
if (count($errors) > 0) {
	$error_msg = join("<br />", $errors);
	$form_response="1,$error_msg";
} else if ($success) {
	$form_response="0,$success";
}
if (!$form_response) {
	unset($_SESSION['form_response']);
} else {
	$_SESSION['form_response']=$form_response;
}
session_write_close();
header("Location: ".DEF_SITEURL.$redirect);
}

function strip_non_numerics( $subject )
{
    return preg_replace( '/[^0-9]/i', '', $subject );
}

function string_begins_with($string, $search)
{
    return (strncmp($string, $search, strlen($search)) == 0);
}

?>
