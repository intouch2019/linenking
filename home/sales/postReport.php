<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once("lib/supplier/clsSupplier.php");
require_once("lib/reorder/clsReorder.php");
extract($_POST);
$errors=array();
try {
	$clsReorder = new clsReorder();
	if(isset($_POST['supid'])) { 
		$supId= $_POST['supid']; 
		$reorderId = $clsReorder->addReorder($supId);
	} else {
		$errors['supid'] = "Please select Supplier from list";
	}
	foreach($_POST  as $key => $value) {
		$itemList = array();
		if(is_null($value) || $value=="" || $key=="supid" || $key=="genInvoice" ) {
			continue;
		} else {
			$itemList['reorderid']=$reorderId;
			$itemList['rawitemid']=$key;
			$itemList['orderquantity']=$value;
		}	
		$obj = $clsReorder->addReorderItems($itemList);
	}
	if (count($errors) == 0) {
	}
} catch (Exception $xcp) {
	$clsLogger = new clsLogger();
	$clsLogger->logError("Failed to Add Supplier Info:".$xcp->getMessage());
	$errors['status']="There was a problem processing your request. Please try again later";
}
if (count($errors) > 0) {
	$_SESSION['form_errors'] = $errors;
	$redirect = "store/inventory/reorderreport";
} else {
	unset($_SESSION['form_errors']);
	$redirect = "store/inventory/purchaseorders";
}
session_write_close();
header("Location: ".DEF_SITEURL.$redirect);
exit;
?>
