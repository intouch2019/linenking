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
		if (!$selectItems || count($selectItems) == 0) { $errors['selectItems']='Please select one or more items from the product list'; }
		else
		if (!$ctgSelect) { $errors['ctgSelect'] = 'Please select a category to assign the products to'; }
		else {
			$clsItems = new clsItems();
			$clsItems->assignItems($currStore->id, $ctgSelect, $selectItems);
			$_SESSION['form_success']=count($selectItems)." items were assigned";
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
