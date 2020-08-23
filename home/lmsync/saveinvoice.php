<?php
include "checkAccess.php";
require_once "../../ck_config.php";

require_once "lib/logger/clsLogger.php";
require_once "lib/db/DBConn.php";

extract($_POST);
$logger = new clsLogger();
if (!$invoicebatch) {
	$logger->logError("CK:Missing-invoice-info:".print_r($_POST, true));
	print "The invoice information is missing. Please make sure the receipt information is displayed before re-submitting.";
	return;
}

require_once "lib/codes/clsCodes.php";
$clsCodes = new clsCodes();
$codeInfo = $clsCodes->getCodeInfoById($gCodeId);
if (!$codeInfo) {
	$logger->logError("Code-not-found:$gCodeId");
	print "error:Unable to authenticate the application. Please contact customer support.";
	return;
}

/*
  `item_code` varchar(50) NOT NULL,
  `curr_qty` int(8) not null default 0,
  `createtime` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `updatetime` timestamp,

  `invoice_no` varchar(50) NOT NULL,
  `invoice_dt` varchar(20) not null,
  `invoice_amt` double not null,
  `cust_code` varchar(25) not null,
  `cust_phoneno` varchar(16) null,
*/

$logger->logInfo("Invoice:$gCodeId:$invoicebatch");
$db = new DBConn();
$arr = explode("||",$invoicebatch);
$last_invoice_no=false;
foreach ($arr as $record) {
	$fields = explode("<>",$record);
	$invoice_no = $db->safe($fields[0]);
	$invoice_dt = $db->safe($fields[1]);
	$cust_code = $db->safe($fields[2]);
	$invoice_amt = floatval($fields[3]);
	$cust_phoneno = $fields[4];
	$query = "insert into it_ck_invoices set invoice_no = $invoice_no, invoice_dt = $invoice_dt, invoice_amt = $invoice_amt, cust_code = $cust_code";
	if (trim($cust_phoneno) != "") {
		$query .= ", cust_phoneno=".$db->safe($cust_phoneno);
	}
	$db->execInsert($query);
	$last_invoice_no = $invoice_no;
}
if ($last_invoice_no) {
	$db->execUpdate("update it_codes set order_format=$last_invoice_no where id=$gCodeId");
}
$db->closeConnection();
print "Success";
?>
