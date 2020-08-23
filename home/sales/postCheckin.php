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
	if (isset($_SESSION['form_intouchno'])) unset($_SESSION['form_intouchno']);
	if (isset($_SESSION['form_response'])) unset($_SESSION['form_response']);
	return page_refresh($errors, null, "store");
}

try {
	$intouchno=trim($intouchno);
	$_SESSION['form_intouchno']=$intouchno;
	$server_name=trim($server_name);
	$_SESSION['form_server_name']=$server_name;
	$password=urldecode($password);
	if (!$intouchno) { $errors[]='Enter the InTouch Number or the Phone Number'; }
	if (!$server_name) { $errors[]='Enter the Name of the person serving'; }
	if (count($errors) > 0) { return page_refresh($errors); }

	$clsUser = new clsUser();
	$userInfo = $clsUser->lookup($intouchno);
	if (!$userInfo) {
		$errors[]="No customer found for number:$intouchno";
		return page_refresh($errors);
	}
	
	$clsCodes = new clsCodes();
	if (!isset($_POST['action_repeat_ok'])) { // donot perform this check if the user has ok'ed the repeat checkin
	$repeatHours = $gConfig['checkin_repeat_hours'];
	if ($clsCodes->alreadyCheckedin($userInfo, $gCurrStore, $repeatHours)) {
		$errors[]='Customer has already checked in the last '.$repeatHours.' hours. Press "Ok" to continue the Checkin or "Cancel" to go back.';
		return page_refresh($errors, null, "store/checkin/repeat");
	}
	}
	
	$checkinid = $clsCodes->checkin($userInfo, $gCurrStore, $server_name);

	// send SMS to user asking for a review
	$smsMessage = clsFanMessage::checkinThankYou($gCurrStore->store_name, $server_name)->getMessage($user->locale);
	$smsHelper = new clsSMSHelper();
	$retval = $smsHelper->sendOne($userInfo->phoneno, $smsMessage);
	$logger = new clsLogger();
	$logger->logInfo($smsMessage."|".$retval);

	$success = 'Customer checkin completed';
	return page_refresh($errors,$success,"store/checkin/done");
} catch (Exception $xcp) {
	$clsLogger = new clsLogger();
	$clsLogger->logError("Failed to login $storecode:".$xcp->getMessage());
	$errors['status']="There was a problem processing your request. Please try again later";
}
page_refresh($errors,$success);
exit();

function page_refresh($errors, $success=null, $redirect="store/checkin") {
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

?>
