<?php
include "checkAccess.php";
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";

extract($_POST);
extract($_GET);

//if ($gCodeId != 61) { print "1::Unauthorized access"; return; }

//$lastid=0;$storeid=62;
if (!isset($lastid) || trim($lastid) == "" || !isset($storeid) || trim($storeid) == "") {
	print "1::Missing parameters";
	return;
}

$db = new DBConn();
$items = $db->fetchObjectArray("select * from it_order_items where id>$lastid and store_id=$storeid order by id");
$json_objs = array();
foreach ($items as $item) {
$json_objs[]=array(
$item->id,
$item->item_id,
$item->barcode,
$item->quantity
);
}
$db->closeConnection();
$json_str=json_encode($json_objs);
print "0::$json_str";
