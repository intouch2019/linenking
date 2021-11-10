<?php
require_once("../../it_config.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "session_check.php";

$pickgroup_id=false;
if (isset($_GET['pid']))
	$pickgroup_id = $_GET['pid'];
if (!$pickgroup_id) { print "Missing pickgroup id. Please report this error."; return; }

$currStore = getCurrUser();
if ($currStore->usertype != UserType::Dispatcher && $currStore->usertype != UserType::Admin) { print "Unauthorized access"; return; }

$db = new DBConn();
$store_id = $currStore->id;

$obj = $db->fetchObject("select * from it_ck_pickgroup where id = $pickgroup_id");
if (!$obj) { print "Pickgroup [$pickgroup_id] not found. Please report this error."; }
else if ($obj->dispatcher_id != $store_id && $currStore->usertype != UserType::Admin) { print "Order state can only be changed by by the Dispatcher who picked it up."; }
else {
$db->execUpdate("update it_ck_orders set status=1, pickgroup=null where id in ($obj->order_ids)");
$db->execQuery("delete from it_ck_pickgroup where id = $pickgroup_id");
if ($currStore->usertype == UserType::Dispatcher){
header("Location: ".DEF_SITEURL."dispatch/orders/active");
}
else if($currStore->usertype == UserType::Admin){
header("Location: ".DEF_SITEURL."admin/orders/active");    
}
}
