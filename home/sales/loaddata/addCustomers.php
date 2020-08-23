<?php
require_once("../../../it_config.php");
require_once("lib/codes/clsCodes.php");
require_once("lib/user/clsUser.php");
require_once("lib/messages/clsFanMessage.php");
require_once("lib/sms/clsSMSHelper.php");
require_once("lib/logger/clsLogger.php");

function addCustomer($storecode, $phoneno, $custname, $email) {
print "$storecode, $phoneno, $custname, $email - ";
if ($phoneno == "" || $phoneno == "-") { print "Skipping...\n"; return; }
	$storecode = trim($storecode);
	$email = trim($email);
	$clsCodes = new clsCodes();
	$codeInfo = $clsCodes->getCodeInfo($storecode);
	if (!$codeInfo) {
		$errors[] = "Code does not exist:$storecode";
		showErrors($errors);
		return;
	}
	
	$phoneno=trim($phoneno);
	$phoneno = strip_non_numerics($phoneno);
	$custname=trim($custname);
	if (!$phoneno) { $errors[]='Enter the Customers Phone Number'; }
	else
	if (strlen($phoneno) == 12 && string_begins_with($phoneno, "91")) {
		// valid phone no
	} else
	if (strlen($phoneno) != 10) { $errors[]="Invalid phone number:$phoneno"; }
	 
	if (count($errors) > 0) { return showErrors($errors); }

	try {
		// if phoneno is 10 long then prefix "91" for India (hack)
		if (strlen($phoneno) == 10) { $phoneno = "91$phoneno"; }
		$clsUser = new clsUser();
		$userInfo = $clsUser->createOnce($phoneno, "-1", $custname, $email);
		$intouchno = $clsUser->generateIntouchno($userInfo->id);

		if ($clsCodes->fanExists($codeInfo->id, $userInfo->id)) {
			$errors[] = "This customer is already a fan";
			return showErrors($errors,null,"store/addcust/done");
		}
		$clsCodes->addFan($codeInfo->id, $userInfo->id, "-1");
		if ($codeInfo->signupmsg) {
			$smsMessage = clsFanMessage::addCustomerSignupOffer($codeInfo->code, $codeInfo->store_name, $intouchno, $codeInfo->signupmsg, DEF_SMS_PHONENO)->getMessage($userInfo->locale);
		} else {
			$smsMessage = clsFanMessage::addCustomer($codeInfo->code, $codeInfo->store_name, $intouchno, DEF_SMS_PHONENO)->getMessage($userInfo->locale);
		}

		// send signup message to customer
		$smsHelper = new clsSMSHelper();
		$retval = $smsHelper->sendOne($userInfo->phoneno, $smsMessage);
		$logger = new clsLogger();
		$logger->logInfo($smsMessage."|".$retval);

		$success = "Customer successfully added.";
		return showErrors($errors,$success,"store/addcust/");
	} catch (Exception $xcp) {
		$clsLogger = new clsLogger();
		$clsLogger->logError("Failed to addcust $codeInfo->code, $phoneno:".$xcp->getMessage());
		$errors['status']="There was a problem processing your request. Please try again later";
	}
	showErrors($errors);
}

function showErrors($errors, $success=null, $redirect="store/addcust") {
$form_response=false;
if (count($errors) > 0) {
	$error_msg = join("\n", $errors);
	$form_response="1,$error_msg";
} else if ($success) {
	$form_response="0,$success";
}
print "$form_response\n";
}

function strip_non_numerics( $subject )
{
    return preg_replace( '/[^0-9]/i', '', $subject );
}

function string_begins_with($string, $search)
{
    return (strncmp($string, $search, strlen($search)) == 0);
}

// main
if (count($argv) != 3) {
   print "Usage:code filename\n";
   exit();
}
$storecode = $argv[1];
$file = $argv[2];
$handle = fopen($file, "r");
$first=true;
while ($row = fgetcsv($handle)) {
if ($first) { $first=false; continue; } // ignore the header row
$phoneno = trim($row[4]);
addCustomer($storecode, $phoneno, $row[1], $row[3]);
}
fclose($handle);

?>
