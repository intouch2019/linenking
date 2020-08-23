<?php
require_once "../../it_config.php";
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";

extract($_POST);
$store = getCurrUser();
if ($store->usertype != UserType::Admin && $store->usertype != UserType::CKAdmin) { print "You are not authorized to add a Store Discount "; return; }
$errors=array();
try {
	$_SESSION['form_post'] = $_POST;
	$db = new DBConn();
        $addquery = '';
        $check = $db->fetchObject("select * from it_ck_storediscount where store_id=$storeid");
        if ($check!=null) { $errors['exs']="that store already exists with discount information."; }
        if ($storeid == 0) {$errors['str']="please select a store."; } 
        //if (!$storename )  { $errors['fullname'] = "Please enter the store name"; }
        //if (!$location) { $errors['location'] = "Please enter the store location"; }
//        if (!$polaris) { $errors['polaris']= "please enter polaris code"; }
        if ($dealerdisc) $addquery .= ", dealer_discount=$dealerdisc " ; 
        if ($adddisc) $addquery .= ", additional_discount=$adddisc ";
        if ($vat) $addquery .= ", vat = $vat ";
        if ($cst) $addquery .= ", cst = $cst ";
        if ($transport) $addquery .= ", transport = $transport ";
        if ($octroi) $addquery .= ", octroi = $octroi";
        if ($cash) $addquery .= ",cash = $cash";
        if ($nonclaim) $addquery .= ", nonclaim=$nonclaim";
	//$storename = isset($storename) && trim($storename) != "" ? $db->safe($storename) : false;
	//$location = isset($location) && trim($location) != "" ? $db->safe($location) : false;
//        $polaris = isset($polaris) && trim($polaris) != "" ? $db->safe($polaris) : false;
        
	if (count($errors) == 0) {
		//$query = "insert into it_ck_storediscount set store_id=$storeid, storename=$storename, location = $location, polariscode=$polaris $addquery";
//            $query = "insert into it_ck_storediscount set store_id=$storeid, polariscode=$polaris $addquery";
            $query = "insert into it_ck_storediscount set store_id=$storeid  $addquery";
		$db->execUpdate($query);
//		$success = "$fullname has been updated";
                $success = "Discount has been added";
		unset($_SESSION['form_post']);
	}
} catch (Exception $xcp) {
	$errors['status']="There was a problem processing your request. Please try again later";
}
if (count($errors) > 0) {
	$_SESSION['form_errors'] = $errors;
	$redirect = "admin/discounts/add";
} else {
	unset($_SESSION['form_errors']);
//        $_SESSION['form_success'] = $success;
	$redirect = "admin/discounts";	 
}
session_write_close();
header("Location: ".DEF_SITEURL.$redirect);
exit;

?>
