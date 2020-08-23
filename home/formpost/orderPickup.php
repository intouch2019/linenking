<?php
require_once("../../it_config.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "session_check.php";
require_once 'lib/users/clsUsers.php';

$order_id=false; $store_id=false;
if (isset($_GET['oid']))
	$order_id = $_GET['oid'];
if (isset($_GET['sid']))
	$store_id = $_GET['sid'];

$currStore = getCurrUser();
$db = new DBConn();
$userpage = new clsUsers();
$pagecode = $db->safe($_SESSION['pagecode']);
$page = $db->fetchObject("select * from it_pages where pagecode = $pagecode");
if($page){
    $allowed = $userpage->isAuthorized($currStore->id, $page->pagecode);
    if (!$allowed) { header("Location: ".DEF_SITEURL."unauthorized"); return; }
}else{ header("Location:".DEF_SITEURL."nopagefound"); return; }


$order_ids = false; $order_nos = false;
if ($order_id) {
	$query = "select id,order_no,store_id,active_time from it_ck_orders where id=$order_id and status=".OrderStatus::Active;
	$obj = $db->fetchObject($query);
	if (!$obj) { print "Order [$order_id] not found. Please report this error"; exit; }
	$order_ids = $obj->id;
	$order_nos = $obj->order_no;
	$store_id = $obj->store_id; 
	$active_time = $obj->active_time;
} else
if ($store_id) {
	$query = "select id,order_no,active_time from it_ck_orders where store_id=$store_id and status=".OrderStatus::Active." order by active_time";
	$objs = $db->fetchObjectArray($query);
	if (count($objs) == 0) { print "No orders in Active state"; return; }
	$ids = array();
	$nos = array();
	foreach ($objs as $obj) {
		$ids[] = $obj->id;
		$nos[] = $obj->order_no;
		$active_time = $obj->active_time;
	}
	$order_ids = implode(",", $ids);
	$order_nos = implode(", ", $nos);
} else {
	print "ERROR: missing parameters. Please report this."; return;
}

//$query = "select sum(order_qty) as tot_qty, sum(order_qty * MRP) as tot_amt, count(distinct(design_no)) as num_designs from it_ck_orderitems where order_id in ($order_ids)";
$query = "select sum(oi.order_qty) as tot_qty, sum(oi.order_qty * oi.MRP) as tot_amt, count(distinct(oi.design_no)) as num_designs from it_ck_orderitems oi, it_items i where oi.order_id in ($order_ids) and oi.item_id = i.id and i.ctg_id != 29";
$summary = $db->fetchObject($query);
if (!$summary) { print "Summary info not found for orders [$order_ids]. Please report this error."; return; }
$query = "insert into it_ck_pickgroup set storeid = $store_id, dispatcher_id=".$currStore->id.", order_ids = '$order_ids', order_nos='$order_nos', order_qty = $summary->tot_qty, order_amount = $summary->tot_amt, num_designs = $summary->num_designs, picking_time = now(), active_time='$active_time'";
$insert_id = $db->execInsert($query);
$query = "update it_ck_orders set status=2, pickgroup=$insert_id where id in ($order_ids)";
$db->execUpdate($query);
header("Location: ".DEF_SITEURL."dispatch/orders/packing");
