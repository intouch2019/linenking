<?php
include "checkAccess.php";
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";


extract($_POST);
extract($_GET);
//$lastid=91;
//$gCodeId = 61;
if (!isset($lastid) || trim($lastid) == "") {
	print "1::Missing parameter";
	return;
}

$db = new DBConn();
//$returns = $db->fetchObjectArray("select * from it_sp_returns where store_id=$gCodeId and id>$lastid order by id limit 10");
$returns = $db->fetchObjectArray("select * from it_sp_returns where id>$lastid order by id limit 10");
$json_objs = array();
foreach ($returns as $return) {
        //$storeobj = $db->fetchObject("select ")
	$json_return = array();
        $json_return['return_storeid'] = $return->store_id;
	$json_return['return_id'] = $return->id;
	$json_return['return_no'] = $return->return_no;
	$json_return['return_dt'] = $return->return_dt;
	$json_return['return_amt'] = $return->return_amt;
	$json_return['return_qty'] = $return->return_qty;
	$items = $db->fetchObjectArray("select * from it_sp_return_items where return_id = $return->id");
	$json_items = array();
	foreach ($items as $item) {
		$json_inv_item = array($item->barcode,$item->price,$item->quantity);
		$json_items[] = $json_inv_item;
	}
	$json_return['items']=$json_items;
	$json_objs[] = $json_return;
}
$db->closeConnection();
$json_str=json_encode($json_objs);
print "0::$json_str";