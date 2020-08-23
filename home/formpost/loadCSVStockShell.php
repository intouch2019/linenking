#!/usr/bin/php -q
<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";

$commit=true;
if (count($argv) != 2 || ($handle = fopen($argv[1], "r")) === FALSE) {
print "File not found\n";
return;
}

$db=new DBConn();

$rowno=0;$tot_qty=0;$skip_qty=0;
$error="Error:";
$items = array();
while (($data = fgetcsv($handle, 1000, "\t")) !== FALSE) {
	$rowno++;
	$item_code=trim($data[0]);
	$curr_qty=trim($data[1]);
	$item = $db->fetchObject("select * from it_items where barcode='$item_code'");
	if ($item) {
		$items[$item->id] = $curr_qty;
		if ($commit) $db->execUpdate("update it_items set curr_qty=$curr_qty, updatetime=now() where id=$item->id");
		$tot_qty += $curr_qty;
	} else {
		$error .= "Skipping Row No:$rowno::$item_code,$curr_qty\n<br />";
		$skip_qty += $curr_qty;
	}
}
if ($commit) {
	if ($skip_qty == 0) {
		$db->execUpdate("update it_items set curr_qty = 0, updatetime=now()");
		foreach ($items as $item_id => $curr_qty) {
			$db->execUpdate("update it_items set curr_qty=$curr_qty, updatetime=now() where id=$item_id");
		}
		$obj = $db->fetchObject("select sum(curr_qty) as total from it_items");
		if ($obj) { print "UPLOADED STOCK COUNT=$obj->total\n"; }
	} else {
		print "MISSING ITEMS....\n";
	}
}
$db->closeConnection();

print "Total Qty=$tot_qty, Skipped Qty=$skip_qty\n";
if ($commit) print "Database Committed\n";
else print "No Database Commit\n";
