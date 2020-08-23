<?php
// code need to be modified.
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once 'Classes/PHPExcel/IOFactory.php';
return;exit;//code need to be changed as per req wen told to do
$commit=true;
$newfile = $argv[1];
$fh = fopen($newfile, "r");
if (!$fh) { print "File not found\n"; return; }

$db=new DBConn();

$rowno=0;$tot_qty=0;$skip_qty=0;
if ($commit) $db->execUpdate("update it_items set curr_qty = 0, updatetime=now()");
$error="Error:";

while(($row=fgetcsv($fh,0,"\t")) !== FALSE) {
$colno=0;
$item_code=false; $mrp=false; $ctg_name=false; $ctg_id=false; $style_name=false; $style_id=false; $size_name=false; $size_id=false; $design_no=false; $prod_type=false; $curr_qty=false;
$item_code=$row[0];
$curr_qty=intval($row[1]);
	$item = $db->fetchObject("select * from it_items where barcode='$item_code'");
	if ($item) {
		if ($commit) $db->execUpdate("update it_items set curr_qty=$curr_qty, updatetime=now() where id=$item->id");
		$tot_qty += $curr_qty;
	} else {
		print "Item not found:$item_code\n";
		$skip_qty += $curr_qty;
	}
$rowno++;
}
fclose($fh);
$db->closeConnection();
if ($error == "Error:") {
print "Total Qty=$tot_qty, Skipped Qty=$skip_qty\n<br />";
if ($commit) print "Database Committed\n<br />";
else print "No Database Commit\n<br />";
} else {
print "Error:[$error]\n<br />";
print "Total Qty=$tot_qty, Skipped Qty=$skip_qty\n<br />";
}
