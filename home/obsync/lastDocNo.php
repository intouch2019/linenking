<?php
include "checkAccess.php";
require_once "../../it_config.php";

require_once "lib/logger/clsLogger.php";
require_once "lib/db/DBConn.php";

extract($_POST);
if (!$syncType) { print "1:Missing parameter [syncType]"; return; }

$queries = array(
	"2" => "select receipt_no as doc_no from it_goodsreceipt order by receipt_no desc limit 1",
	"3" => "select doc_no from it_purchasereturns order by doc_no desc limit 1",
	"4" => "select doc_no from it_salesreturns order by doc_no desc limit 1",
	"5" => "select invoice_no as doc_no from it_invoices order by invoice_no desc limit 1"
);
$defaults = array(
	"2" => "2002300006022",  // as of 24-02-2013
	"3" => "2002300000400",
	"4" => "2002300000002",
	"5" => "2002300006600"
);

if (!isset($queries[$syncType])) {
	print "1:Incorrect value for parameter [syncType]:$syncType";
	return;
}
$query = $queries[$syncType];
$default = $defaults[$syncType];

$db = new DBConn();
$obj = $db->fetchObject($query);
if ($obj) {
	print "0:".$obj->doc_no;
} else {
	print "0:$default";
}
$db->closeConnection();
?>
