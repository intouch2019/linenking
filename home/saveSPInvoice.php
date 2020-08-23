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
//$record = "822<>14150035<>0<>1397542545000<>6033.0<>5.0<>1741.6<>0.0<>Administrator<==>83<==>8900000249305<>SHIRT PIECE<>1595.0<>1.0<>1595.0<++>8900000249374<>SHIRT PIECE<>1495.0<>1.0<>1495.0<++>8900000248636<>SHIRT PIECE<>1495.0<>1.0<>1495.0<++>8900000248568<>SHIRT PIECE<>1595.0<>1.0<>1595.0<++>8900000249152<>SHIRT PIECE<>1595.0<>1.0<>1595.0<++>|||||822<>14150039<>0<>1397542545000<>6033.0<>5.0<>1741.6<>0.0<>Administrator<==>83<==>8900000249305<>SHIRT PIECE<>1595.0<>1.0<>1595.0<++>8900000249374<>SHIRT PIECE<>1495.0<>1.0<>1495.0<++>8900000248636<>SHIRT PIECE<>1495.0<>1.0<>1495.0<++>8900000248568<>SHIRT PIECE<>1595.0<>1.0<>1595.0<++>8900000249152<>SHIRT PIECE<>1595.0<>1.0<>1595.0<++>|||||";
$db = new DBConn();
$arr = explode("|||||", $record);
foreach ($arr as $ticketInfo) {
	$ticketInfo = trim($ticketInfo);
	if ($ticketInfo == "") { continue; }
	$records = explode("<==>", $ticketInfo);
	if (count($records) == 0) { continue; }
	list($ck_invoice_id, $invoice_no, $invoice_type, $timeInMillis, $invoice_amt, $invoice_qty, $discount_amt, $taxes, $obuser) = explode("<>", $records[0]);
	if ($ck_invoice_id=="11") { continue; } // for some reason - invoice-no 11 is being sent continuously
	$exists = $db->fetchObject("select * from it_sp_invoices where invoice_no='$invoice_no'");
        if ($exists) { continue; } // do not proceed if invoice_no already exists
        $store_id=$records[1];
	$timeInSeconds=intval(doubleVal($timeInMillis)/1000); // convert millis to seconds
	$invoice_dt = $db->safe(date("Y-m-d H:i:s",intval($timeInSeconds)));

	$query = "insert into it_sp_invoices set store_id=$store_id, invoice_no=$invoice_no, invoice_dt=$invoice_dt, invoice_amt=$invoice_amt, invoice_qty=$invoice_qty, discount_amt=$discount_amt, taxes=$taxes";
	if ($ck_invoice_id && trim($ck_invoice_id) != "" && intval($invoice_amt) == 0) {
		$query .= ", return_id=$ck_invoice_id";
	} else if ($ck_invoice_id && trim($ck_invoice_id) != "") {
		$query .= ", ck_invoice_id=$ck_invoice_id";
	}
	$invoice_id = $db->execInsert($query);
	if ($ck_invoice_id && trim($ck_invoice_id) != "") {
		$db->execUpdate("update it_invoices set sp_invoice_id=$invoice_id where id=$ck_invoice_id");
	}
	$itemlines = explode("<++>", $records[2]);
	foreach ($itemlines as $currlineitem) {
		$currlineitem=trim($currlineitem);
		if ($currlineitem == "") { continue; }
		list($barcode, $lineItemName, $unitPrice, $lineQuantity, $lineTotal) = explode("<>", $currlineitem);
		$query = "insert into it_sp_invoice_items set invoice_id=$invoice_id, barcode=$barcode, price=$unitPrice, quantity=$lineQuantity";
		$db->execInsert($query);
	}
}
$db->closeConnection();
print "0::Success";
} catch (Exception $ex) {
$msg = print_r($ex,true);
error_log($msg, 3, "/home/limelight/logs/limelight-error.log");
print "1::Error-$msg";
}
