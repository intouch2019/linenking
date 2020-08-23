<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once("lib/items/clsItems.php");
extract($_POST);
//print_r($_POST);
//exit;
$errors=array();
try {
	$_SESSION['form_id'] = $form_id;
	$currStore = getCurrStore();
	$storeid = $currStore->id;
	$stockInfo = array();
	if(isset($storeid)) { $stockInfo['storeid']=$storeid; }
	if(isset($shipmentid)) { 
		$stockInfo['shipmentid']=$shipmentid;  
	}
	if(isset($rawitemid)) { 
		$stockInfo['rawitemid']=$rawitemid; 
	} else { $errors['rawitemid']="Please enter Item Name."; }
	if(isset($quantity)) { $stockInfo['quantity']=$quantity; } else { $errors['quantity']="Please enter Quantity."; }
	if (count($errors) == 0) {
		$clsItems = new clsItems();
		$result = $clsItems->addStock($stockInfo);
		if ($result) {
			$_SESSION['form_success']="Stock Added Successfully.";
		} else {
			$errors['status']='Error while adding stock.';
		}
	}
} catch (Exception $xcp) {
	$clsLogger = new clsLogger();
	$clsLogger->logError("Failed to Add stock".$xcp->getMessage());
	$errors['status']="There was a problem processing your request. Please try again later";
}
if (count($errors) > 0) {
	$_SESSION['form_errors'] = $errors;
	$redirect = "store/inventory/newshipment/sid=$shipmentid";
} else {
	unset($_SESSION['form_errors']);
	$redirect = "store/inventory/newshipment/sid=$shipmentid";
}
session_write_close();
header("Location: ".DEF_SITEURL.$redirect);
exit;
?>
