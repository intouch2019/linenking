<?php
 require_once("../../it_config.php");
require_once("session_check.php");
require_once "../lib/db/DBConn.php";
extract($_POST);
$itemid=0;
$store_id=0;
$errors=array(); 
if(isset($item_id) && $item_id !="")
{
    $itemid=$item_id;
}else{$errors['itemid'] = "Invalid item id";}
if(isset($store) && $store !="")
{
    $store_id=$store;
}else{$errors['store'] = "Store not select properly";}
try{
    $db = new DBConn();
    $dquery="delete from it_portalinv_items_creditnote where id=$itemid and store_id=$store_id";
    
    $db->execUpdate($dquery);
} catch (Exception $ex) {

}
if (count($errors) > 0) {
	$_SESSION['form_errors'] = $errors;
} else {
    unset($_SESSION['form_errors']);}
session_write_close();
header("Location: ".DEF_SITEURL."create/creditnote/storeid=$store_id");
exit;