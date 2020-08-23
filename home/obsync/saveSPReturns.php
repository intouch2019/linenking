<?php
include "checkAccess.php";
require_once "../../it_config.php";

require_once "lib/db/DBConn.php";

extract($_POST);
if (!$record) {
	print "1::The order information is missing. Please make sure the receipt information is displayed before re-submitting.";
	return;
}

try {
$db = new DBConn();
$store_id = $gCodeId;
//$store_id = 62;
//$record = "111<>1393912002000<>11160.0<>8<==>8900000017218<>SLIM SHIRT<>1395.0<>8<>11160.0<++>|||||";
$arr = explode("|||||", $record);
foreach ($arr as $ticketInfo) {
	$ticketInfo = trim($ticketInfo);
	if ($ticketInfo == "") { continue; }
	$records = explode("<==>", $ticketInfo);
	if (count($records) == 0) { continue; }
	list($return_no, $timeInMillis, $return_amt, $return_qty) = explode("<>", $records[0]);
//	$store_id=$records[1];
	$timeInSeconds=intval(doubleVal($timeInMillis)/1000); // convert millis to seconds
	$return_dt = $db->safe(date("Y-m-d H:i:s",intval($timeInSeconds)));

	$query = "insert into it_sp_returns set store_id=$store_id, return_no=$return_no, return_dt=$return_dt, return_amt=$return_amt, return_qty=$return_qty";
	$return_id = $db->execInsert($query);
//	$itemlines = explode("<++>", $records[2]);
        $itemlines = explode("<++>", $records[1]);
	foreach ($itemlines as $currlineitem) {
		$currlineitem=trim($currlineitem);
		if ($currlineitem == "") { continue; }
		list($barcode, $lineItemName, $unitPrice, $lineQuantity, $lineTotal) = explode("<>", $currlineitem);
		$query = "insert into it_sp_return_items set return_id=$return_id, barcode=$barcode, price=$unitPrice, quantity=$lineQuantity";
		$db->execInsert($query);
                $barcode = $db->safe($barcode);
                $qry = "select * from it_current_stock where barcode = $barcode and store_id = $store_id ";
                $exists = $db->fetchObject($qry);
                if($exists){
                    $db->execUpdate("update it_current_stock set quantity = quantity - $lineQuantity where id = $exists->id ");
                }
	}
}
$db->closeConnection();
print "0::Success";
} catch (Exception $ex) {
$msg = print_r($ex,true);
print "1::Error-$msg";
}
