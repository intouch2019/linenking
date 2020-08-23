<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once("lib/items/clsItems.php");

extract($_POST);
$errors=array();
try {
	$_SESSION['form_id'] = $form_id;
	$currStore = getCurrStore();
	if ($currStore) {
		$ctgname = trim($ctgname);
		if (!$ctgname) { $errors['ctgname']='Enter the Category name'; }
		else
		if (!$scenarioid) { $errors['ctgname']='Product Segmention not selected'; }
		else {
			$clsItems = new clsItems();
			$clsItems->addCategory($currStore->id, $scenarioid, $ctgname);
			$_SESSION['form_success']="Category '$ctgname' added";
		}
	}
} catch (Exception $xcp) {
	$clsLogger = new clsLogger();
	$clsLogger->logError("Failed to login $storecode:".$xcp->getMessage());
	$errors['status']="There was a problem processing your request. Please try again later";
}
if (count($errors) > 0) {
	$_SESSION['form_errors'] = $errors;
} else {
	unset($_SESSION['form_errors']);
}
session_write_close();
header("Location: ".DEF_SITEURL."store/products/categories/manage");
exit;

?>
