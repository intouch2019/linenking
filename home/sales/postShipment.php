<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once("lib/items/clsItems.php");
extract($_POST);
$errors=array();
try {
	$_SESSION['form_id'] = $form_id;
	$currStore = getCurrStore();
	$storeid = $currStore->id;
	$shipmentInfo = array();
	if(isset($storeid)) { $shipmentInfo['storeid']=$storeid; }
	if(isset($arrival_date)) { $shipmentInfo['arrival_date']=$arrival_date; } else { $errors['arrival_date']="Please enter Arrival Date."; }
	if(isset($stocked_date)) { $shipmentInfo['stocked_date']=$stocked_date; } else { $errors['stocked_date']="Please enter Stocked Date."; }
	if (count($errors) == 0) {
		$clsItems = new clsItems();
		$result = $clsItems->addShipment($storeid,$shipmentInfo);
		if ($result) {
			$_SESSION['form_success']="Shipment Created Successfully.";
		} else {
			$errors['status']='Error while adding shipment.';
		}
	}
} catch (Exception $xcp) {
	$clsLogger = new clsLogger();
	$clsLogger->logError("Failed to Add shipment".$xcp->getMessage());
	$errors['status']="There was a problem processing your request. Please try again later";
}
if (count($errors) > 0) {
	$_SESSION['form_errors'] = $errors;
	$redirect = "store/inventory/newshipment";
} else {
	unset($_SESSION['form_errors']);
	$redirect = "store/inventory/newshipment";
}
session_write_close();
header("Location: ".DEF_SITEURL.$redirect);
exit;
?>
