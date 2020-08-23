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
	$intouchno=trim($_POST['intouchno']);
	$_SESSION['form_intouchno']=$intouchno;
	$offerid=trim($_POST['offerid']);
	if (!$offerid) { $errors[]='Select an offer to Redeem'; }
	if (!$intouchno) { $errors[]='Enter the InTouch Number or the Phone Number'; }
	if (count($errors) > 0) { return page_refresh($errors); }

	$clsUser = new clsUser();
	$userInfo = $clsUser->lookup($intouchno);
	if (!$userInfo) {
		$errors[]="No customer found for number:$intouchno";
		return page_refresh($errors);
	}
	
	$clsCodes = new clsCodes();
	$date = $clsCodes->getOfferRedemptionDate($userInfo, $gCurrStore, $offerid);
	if ($date) {
		$errors[]="Customer has already redeemed this offer on $date";
		return page_refresh($errors,null,"store/redeem/done");
	}
	
	$clsCodes->redeemOffer($userInfo, $gCurrStore, $offerid);

	// send SMS to user asking for a review
	$smsMessage = clsFanMessage::redeemThankYou($gCurrStore->store_name, $intouchno)->getMessage($user->locale);
	$smsHelper = new clsSMSHelper();
	$retval = $smsHelper->sendOne($userInfo->phoneno, $smsMessage);
	$logger = new clsLogger();
	$logger->logInfo($smsMessage."|".$retval);

	$success = 'Offer successfully redeemed.';
	return page_refresh($errors,$success,"store/redeem/done");
} catch (Exception $xcp) {
	$clsLogger = new clsLogger();
	$clsLogger->logError("Failed to login $storecode:".$xcp->getMessage());
	$errors['status']="There was a problem processing your request. Please try again later";
}
page_refresh($errors,$success);
exit();

function page_refresh($errors, $success=null, $redirect="store/redeem") {
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
