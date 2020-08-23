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
	if (isset($_SESSION['form_billno'])) unset($_SESSION['form_billno']);
	if (isset($_SESSION['form_billamt'])) unset($_SESSION['form_billamt']);
	if (isset($_SESSION['form_response'])) unset($_SESSION['form_response']);
	if (isset($_SESSION['product_list'])) unset($_SESSION['product_list']);
	return page_refresh($errors, null, "store");
}

if ($product && $product_ID) {
	if ($product_ID != "N") { 
		if (!isset($_SESSION['product_list'])) {
			$_SESSION['product_list'] = array();
		}
		$_SESSION['product_list'][]=array($_POST['product_ID'],$_POST['product']);
	}
	return page_refresh($errors);
}

try {
	$intouchno=trim($_POST['intouchno']);
	$_SESSION['form_intouchno']=$intouchno;
	$billno=trim($_POST['billno']);
	$_SESSION['form_billno']=$billno;
	$billamt=trim($_POST['billamt']);
	$_SESSION['form_billamt']=$billamt;
	if (!$intouchno) { $errors[]='Enter the InTouch Number or the Phone Number'; }
	if (!$billno) { $errors[]='Enter the Bill Number'; }
	if (!$billamt) { $errors[]='Enter the Bill Amount'; }
	if (count($errors) > 0) { return page_refresh($errors); }

	$clsUser = new clsUser();
	$userInfo = $clsUser->lookup($intouchno);
	if (!$userInfo) {
		$errors[]="No customer found for number:$intouchno";
		return page_refresh($errors);
	}
	
	// get the list of products entered
	$product_ids = array();
	foreach ($_POST as $key => $value) {
		$pos = strpos($key, "product_list");
		if ($pos === false) { continue; }
		if ($pos > 0) { continue; }
		$product_ids[] = $value;
	}
	
	$clsCodes = new clsCodes();
	$pointsArr = $clsCodes->addPoints($userInfo, $gCurrStore, $billno, $billamt);
	if (count($product_ids) > 0) { 
	$clsCodes->addPurchases($pointsArr[0], $userInfo, $gCurrStore, $_SESSION['product_list']);
	}

	// send sms to customer notifying points and asking for review($storename, $currPoints, $totalPoints, $checkinid, $phoneno)
	$smsMessage = clsFanMessage::addpointsDone($gCurrStore->store_name, $pointsArr[1], $pointsArr[2], $pointsArr[0], DEF_SMS_PHONENO)->getMessage($user->locale);
	$smsHelper = new clsSMSHelper();
	$retval = $smsHelper->sendOne($userInfo->phoneno, $smsMessage);
	$logger = new clsLogger();
	$logger->logInfo($smsMessage."|".$retval);


	unset($_SESSION['product_list']);
	$success = "Points earned now:$pointsArr[1]<br />Total points:$pointsArr[2]";
	return page_refresh($errors,$success,"store/addpoints/done");
} catch (Exception $xcp) {
	$clsLogger = new clsLogger();
	$clsLogger->logError("Failed to login $storecode:".$xcp->getMessage());
	$errors['status']="There was a problem processing your request. Please try again later";
}
page_refresh($errors);
exit();

function page_refresh($errors, $success=null, $redirect="store/addpoints") {
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
