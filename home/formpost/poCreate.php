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
        $supId = -1;
        $potype = isset($consignee) ? intval($potype) : false;
        if($potype < 0){ $errors['potype'] = "Please select PO Type";}
        echo "PoType ".$potype."\n";
        if(isset($supplier) && trim($supplier) != ""){
            $objs = $db->fetchObject("select id from it_suppliers where name='$supplier'");
            if($objs == null){
                $errors['supplier'] = "Supplier you entered does not exsists";
            }else{
                $supId = $objs->id;
            }
            if($supId < 0){
                $errors['supplier'] = "Error while selecting supplier"; 
            }
        }else{
            $errors['supplier'] = "Please select a Supplier"; 
        }

        $consignee = isset($consignee) && trim($consignee) != "" ? $db->safe($consignee) : false;
	if (!$consignee) { $errors['consignee'] = "Please enter Consignee Name"; }

        if (count($errors) == 0) {
            $pono = 0;
            $obj = $db->fetchObject("select max(pono) as pono from it_purchaseorder");
            if($obj->pono == null){
                $pono = 1;
            }else{
                $pono = $obj->pono + 1;
            }
            
            $query = "insert into it_purchaseorder set supplier_id=$supId, potype=$potype, pono=$pono, po_status=0, consignee=$consignee, preparedby_id=$userid";
            $po_id = $db->execInsert ($query);
	}
} catch (Exception $xcp) {
	$clsLogger = new clsLogger();
	$clsLogger->logError("Failed to do gate entry:".$xcp->getMessage());
	$errors['status']="There was a problem processing your request. Please try again later";
}
if (count($errors) > 0) {
	$_SESSION['form_errors'] = $errors;
	$redirect = "po/create";
} else {
	unset($_SESSION['form_errors']);
//        $_SESSION['form_success'] = $success;
	$redirect = "po/additems/id=$po_id/";	 
}
session_write_close();
header("Location: ".DEF_SITEURL.$redirect);
exit;

?>
