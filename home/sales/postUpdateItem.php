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
	if(isset($storeid)) { $storeid=$storeid; }
	if(isset($itemid)) { $itemid=$itemid; } 
	if(isset($reorderlevel)) { $reorderlevel=$reorderlevel; } else { $errors['reorderlevel']="Please enter Reorder level."; }
	if(isset($supplierid)) { $supplierid=$supplierid; } else { $errors['supplierid']="Please select supplier."; }
	if (count($errors) == 0) {
		$clsItems = new clsItems();
		$result = $clsItems->updateItemDetails($storeid,$itemid,$reorderlevel,$supplierid);
		if ($result) {
			$_SESSION['form_success']="Item Details Updated Successfully.";
		} else {
			$errors['status']='Error while updating item details.';
		}
	}
} catch (Exception $xcp) {
	$clsLogger = new clsLogger();
	$clsLogger->logError("Failed to Update item details".$xcp->getMessage());
	$errors['status']="There was a problem processing your request. Please try again later";
}
if (count($errors) > 0) {
	$_SESSION['form_errors'] = $errors;
	$redirect = "store/inventory/itemmaster";
} else {
	unset($_SESSION['form_errors']);
	$redirect = "store/inventory/itemmaster";
}
session_write_close();
header("Location: ".DEF_SITEURL.$redirect);
exit;
?>
