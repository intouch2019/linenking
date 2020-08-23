<?php
require_once "../../it_config.php";
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once 'lib/users/clsUsers.php';

extract($_POST);
$store = getCurrUser();
$db = new DBConn();
$userpage = new clsUsers();
$pagecode = $db->safe($_SESSION['pagecode']);
$page = $db->fetchObject("select * from it_pages where pagecode = $pagecode");
if($page){
    $allowed = $userpage->isAuthorized($store->id, $page->pagecode);
    if (!$allowed) { header("Location: ".DEF_SITEURL."unauthorized"); return; }
}else{ header("Location:".DEF_SITEURL."nopagefound"); return; }
//if ($store->usertype != UserType::Admin && $store->usertype != UserType::CKAdmin ) { print "You are not authorized to update a Store Discount"; return; }
$errors=array();
try {
	$_SESSION['form_post'] = $_POST;
	
        $addquery = '';
        
        if ($storeid == 0) {$errors['str']="please select a store."; } 
        //$check = $db->fetchObject("select * from it_ck_storediscount where store_id=$storeid");
       // if ($check!=null) { $errors['exs']="that store already exists with discount information."; }
        //if (!$storename )  { $errors['fullname'] = "Please enter the store name"; }
        //if (!$location) { $errors['location'] = "Please enter the store location"; }
//        if (!$polaris) { $errors['polaris']= "please enter polaris code"; }
        if ($dealerdisc) $addquery .= " dealer_discount=$dealerdisc " ; 
        if ($adddisc) $addquery .= ", additional_discount=$adddisc ";
        if ($vat) $addquery .= ", vat = $vat ";
        if ($cst) $addquery .= ", cst = $cst ";
        if ($transport) $addquery .= ", transport = $transport ";
        if ($octroi) $addquery .= ", octroi = $octroi";
        if ($cash) $addquery .= ",cash = $cash";
        if ($nonclaim) $addquery .= ", nonclaim=$nonclaim";
//	$storename = isset($storename) && trim($storename) != "" ? $db->safe($storename) : false;
//	$location = isset($location) && trim($location) != "" ? $db->safe($location) : false;
//        $polaris = isset($polaris) && trim($polaris) != "" ? $db->safe($polaris) : false;
        
	if (count($errors) == 0) {
		//$query = "update it_ck_storediscount set store_id=$storeid, storename=$storename, location = $location, polariscode=$polaris $addquery where id=$discid";
//		$query = "update it_ck_storediscount set  polariscode=$polaris $addquery where store_id=$storeid";
                $query = "update it_ck_storediscount set   $addquery where store_id=$storeid";
//                error_log("\n UPDATE DISC:- ".$query."\n",3,"tmp.txt");
                $db->execUpdate($query);
//		$success = "$fullname has been updated";
                $success = "Discount has been updated";
		unset($_SESSION['form_post']);
	}
} catch (Exception $xcp) {
	$clsLogger = new clsLogger();
	$clsLogger->logError("Failed to update $fullname:".$xcp->getMessage());
	$errors['status']="There was a problem processing your request. Please try again later";
}
if (count($errors) > 0) {
	$_SESSION['form_errors'] = $errors;
	//$redirect = "admin/discounts/update/uid=$discid";
        $redirect = "admin/discounts/update/sid=$storeid";
} else {
	unset($_SESSION['form_errors']);
//        $_SESSION['form_success'] = $success;
	$redirect = "admin/discounts";	 
}
session_write_close();
header("Location: ".DEF_SITEURL.$redirect);
exit;

?>
