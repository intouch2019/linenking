<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";

extract($_POST);
$user = getCurrUser();
if ($user->usertype != UserType::Admin && $user->usertype != UserType::GoodsInward) { print "You are not authorized to add a User"; return; }

$querytrans = "";
$errors=array();
$success=array();
try {
	$_SESSION['form_post'] = $_POST;
	$db = new DBConn();
	$supplier = isset($transporterDetails) ? intval($supplier) : false;
	if ($supplier <= 0) { $errors['supplier'] = "Please select a Supplier"; }
        $transporter = isset($transporterDetails) ? intval($transporter) : false;
        if ($transporter <= 0) { 
            $transname = isset($transname) && trim($transname) != "" ? $db->safe($transname) : false;
            if(!$transname){ 
                $error['transname'] ="Please enter name of transporter"; }
            else{
                $querytrans = "insert into it_transporters set name=$transname";
                echo $querytrans;
            }
        }                
	$transporterDetails = isset($transporterDetails) && trim($transporterDetails) != "" ? $db->safe($transporterDetails) : false;
	if (!$transporterDetails) { $errors['transporterDetails'] = "Please enter Transporter Details"; }
        $quantity = isset($quantity) && trim($quantity) != "" ? $db->safe($quantity) : false;
        if (!$quantity) { $errors['quantity'] = "Please enter Quantity"; }
        $dtreceived = isset($dtreceived) && trim($dtreceived) != "" ? $db->safe($dtreceived) : false;
        if(!$dtreceived) {$errors['dtreceived'] = "Please enter Date Received";}
	$users = isset($transporterDetails) ? intval($users) : false;
	if ($users <= 0) { $errors['users'] = "Please select the Person who receive the goods"; }

        if (count($errors) == 0) {
                if(trim($querytrans != "")){
                    $db->execInsert($querytrans);
                    $obj = $db->fetchObject("select id from it_transporters where name=$transname");
                    $transid = $obj->id;
                    $query = "insert into it_gateentry set supplier_id=$supplier, transport_id=$transid, transport_dtls=$transporterDetails, qty_received=$quantity, dt_received=$dtreceived, received_by=$users";
                    echo $query;
                    $db->execInsert ($query);
                    $success = "Gate entry has been done";
                }else{
                    $query = "insert into it_gateentry set supplier_id=$supplier, transport_id=$transporter, transport_dtls=$transporterDetails, qty_received=$quantity, dt_received=$dtreceived, received_by=$users";
                    $db->execInsert ($query);
                    $success = "Gate entry has been done";
                }
	}
} catch (Exception $xcp) {
	$clsLogger = new clsLogger();
	$clsLogger->logError("Failed to do gate entry:".$xcp->getMessage());
	$errors['status']="There was a problem processing your request. Please try again later";
}
if (count($errors) > 0) {
	$_SESSION['form_errors'] = $errors;
	$redirect = "inward/gateentry";
} else {
	unset($_SESSION['form_errors']);
//        $_SESSION['form_success'] = $success;
	$redirect = "inward/home";	 
}
session_write_close();
header("Location: ".DEF_SITEURL.$redirect);
exit;

?>
