<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once("lib/items/clsItems.php");

$errors=array();
try {
	$_SESSION['form_id'] = $form_id;
	$currStore = getCurrStore();
	if ($currStore) {
		$title = trim($_POST['title']);
		if (!$title) { $errors['title']='Enter the title'; }
		else {
			$clsItems = new clsItems();
			$clsItems->addScenario($currStore->id, $title);
			$_SESSION['form_success']="Scenario '$title' added";
			unset($_SESSION['scenarioid']);
		}
	}
} catch (Exception $xcp) {
	$clsLogger = new clsLogger();
	$clsLogger->logError("Failed to add scenario $title:".$xcp->getMessage());
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
