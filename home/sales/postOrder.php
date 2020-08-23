<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once("lib/reorder/clsReorder.php");
extract($_POST);
$errors=array();
try {
	$_SESSION['form_id'] = $form_id;
	$invoiceDetail = array();
	$clsReorder = new clsReorder();
	if(isset($_POST['supplierid'])) { 
		$supId= $_POST['supplierid']; 
		$result = $clsReorder->addReorder($supId);
	} else {
		$errors['supid'] = "Please select Supplier from list";
	}
	if (count($errors) == 0) {
		if($result) {
			$_SESSION['form_success'] = "Order created Successfully ";
		}
	}
} catch (Exception $xcp) {
	$clsLogger = new clsLogger();
	$clsLogger->logError("Failed to Add Supplier Info:".$xcp->getMessage());
	$errors['status']="There was a problem processing your request. Please try again later";
}
if (count($errors) > 0) {
	$_SESSION['form_errors'] = $errors;
	$redirect = "store/inventory/purchaseorders";
} else {
	unset($_SESSION['form_errors']);
	$redirect = "store/inventory/reorderreport";
}
session_write_close();
header("Location: ".DEF_SITEURL.$redirect);
exit;
?>
