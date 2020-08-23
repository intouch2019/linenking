<?php
include "checkAccess.php";
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";

extract($_POST);
extract($_GET);

if (!isset($lastid) || trim($lastid) == "") {
	print "1::Missing parameter";
	return;
}

$db = new DBConn();
//$gCodeId = 62;
$invoices = $db->fetchObjectArray("select * from it_sp_invoices where store_id=$gCodeId and id>$lastid order by id limit 10");
$json_objs = array();
foreach ($invoices as $invoice) {
	$json_invoice = array();
	$json_invoice['invoice_id'] = $invoice->id;
	$json_invoice['invoice_no'] = $invoice->invoice_no;
	$json_invoice['invoice_dt'] = $invoice->invoice_dt;
	$json_invoice['invoice_amt'] = $invoice->invoice_amt;
	$json_invoice['invoice_qty'] = $invoice->invoice_qty;
	$items = $db->fetchObjectArray("select * from it_sp_invoice_items where invoice_id = $invoice->id");
	$json_items = array();
	foreach ($items as $item) {
		$json_inv_item = array($item->barcode,$item->price,$item->quantity);
		$json_items[] = $json_inv_item;
	}
	$json_invoice['items']=$json_items;
	$json_objs[] = $json_invoice;
}
$db->closeConnection();
$json_str=json_encode($json_objs);
print "0::$json_str";
