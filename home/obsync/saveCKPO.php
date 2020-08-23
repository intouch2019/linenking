<?php
require_once "../../it_config.php";
include "checkAccess.php";

require_once "lib/db/DBConn.php";
require_once "lib/core/strutil.php";
require_once "lib/core/Constants.php";

extract($_POST);
if (!$record || trim($record) == "") {
	print "1::The order information is missing. Please make sure the receipt information is displayed before re-submitting.";
	return;
}

try {
	$db = new DBConn();
	$record = trim($record);
	$records = explode("<==>", $record);
	if (count($records) == 0) { throw new Exception("Incomplete order information"); }
	$po_number=$records[0];
	if (!isNumber($po_number)) { throw new Exception("PO Number is missing or invalid [$po_number]"); }
	$po_number = sprintf("SP%06d",intval($po_number));
	// $custInfo = $records[1];
	$itemlines = explode("<++>", $records[2]);
	$oitems = array();
	$order_qty = 0;
	$order_amt = 0;
	$designs = array();
	foreach ($itemlines as $currlineitem) {
		$currlineitem=trim($currlineitem);
		if ($currlineitem == "") { continue; }
		list($item_code, $quantity) = explode("<>", $currlineitem);
		$quantity = intval($quantity);
		// remove stock check
		$item = $db->fetchObject("select * from it_items where barcode='$item_code' and curr_qty > 0");
		//$item = $db->fetchObject("select * from it_items where barcode='$item_code'");
		if (!$item) { continue; }
		$designs[$item->design_no] = 1;
		// remove stock check
		$line_order_qty = $quantity < $item->curr_qty ? $quantity : $item->curr_qty;
		//$line_order_qty = $quantity;
		$oitems[$item->id]=(object)array("order_qty"=>$line_order_qty, "item" => $item);
		$order_qty += $line_order_qty;
		$order_amt += ($line_order_qty * $item->MRP);
	}

	if (count($oitems) >= 0) {
		$num_designs = count($designs);
		$store_id=62;
		$order_id=$db->execInsert("insert into it_ck_orders set store_id=$store_id, status=".OrderStatus::Active.", order_no='$po_number', order_qty=$order_qty, order_amount=$order_amt, num_designs=$num_designs, active_time=now()");
		foreach ($oitems as $item_id => $oinfo) {
			$order_qty = $oinfo->order_qty;
			$item = $oinfo->item;
			$db->execInsert("insert into it_ck_orderitems set order_id=$order_id, store_id=$store_id, item_id='$item->id', design_no='$item->design_no', order_qty=$order_qty, MRP=$item->MRP");
			$db->execUpdate("update it_items set curr_qty = curr_qty - $order_qty where id=$item_id");
		}
	}
	print "0::Success";
} catch (Exception $ex) {
	$msg = print_r($ex,true);
	error_log($msg, 3, "/home/limelight/logs/limelight-error.log");
	print "1::Error-$msg";
}
$db->closeConnection();
