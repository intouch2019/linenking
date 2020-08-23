<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once("lib/supplier/clsSupplier.php");
extract($_POST);
$errors=array();
try {
	$_SESSION['form_id'] = $form_id;
	$currStore = getCurrStore();
	$storeid = $currStore->id;
	$supplierProfile = array();
	if($storeid) { $supplierProfile['storeid']=$storeid; }
	if(is_null($suppliername) || $suppliername =="")  { $errors['suppliername']="Please Enter Supplier's Name"; } else { $supplierProfile['suppliername']=trim($suppliername); }
	if(is_null($address) || $address =="") { $supplierProfile['address']=null; } else  { $supplierProfile['address']=trim($address); } 
	if(is_null($mobile) || $mobile =="") { $supplierProfile['mobile']=null; } 
	elseif(preg_match('/^[0-9]{10}+$/', $mobile)) { 
		$supplierProfile['mobile']=trim($mobile); 
	} else { $errors['mobile']="Please Enter 10 digit Mobile Number."; }
	if(is_null($phone) || $phone =="") { $supplierProfile['phone']=null; } else  { $supplierProfile['phone']=trim($phone); }
	if(is_null($email) || $email =="") { $supplierProfile['email']=null; } else  { $supplierProfile['email']=trim($email); } 
        if (!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $email)){
                $errors['email']='Enter valid email address(e.g. abc@xyz.com)';
        }

	if (count($errors) == 0) {
		$clsSupplier = new clsSupplier();
		$supplierInfo = $clsSupplier->addSupplier($supplierProfile);
		if (!$supplierInfo) {
			$errors['status']='Error while updating information..';
		} else {
			$_SESSION['form_success']="Supplier Added Successfully..";
		}
	}
} catch (Exception $xcp) {
	$clsLogger = new clsLogger();
	$clsLogger->logError("Failed to Add Supplier Info:".$xcp->getMessage());
	$errors['status']="There was a problem processing your request. Please try again later";
}
if (count($errors) > 0) {
	$_SESSION['form_errors'] = $errors;
	$redirect = "store/inventory/suppliers";
} else {
	unset($_SESSION['form_errors']);
	$redirect = "store/inventory/suppliers";
}
session_write_close();
header("Location: ".DEF_SITEURL.$redirect);
exit;
?>
