<?php
include "checkAccess.php";
require_once "../../ck_config.php";

require_once "lib/logger/clsLogger.php";
require_once "lib/db/DBConn.php";

extract($_POST);
if (!$syncType) { print "1:Missing parameter [syncType]"; return; }

$logger = new clsLogger();

$queries = array(
	"1" => "select challan_no as doc_no from it_ck_challans order by id desc limit 1",
	"2" => "select receipt_no as doc_no from it_ck_goodsreceipt order by id desc limit 1",
	"3" => "select doc_no from it_ck_purchasereturns order by id desc limit 1",
	"4" => "select doc_no from it_ck_salesreturns order by id desc limit 1",
	"5" => "select invoice_no as doc_no from it_ck_invoices order by id desc limit 1"
);
$defaults = array(
	"1" => "S00000027849",
	"2" => "2002200008096",
	"3" => "2002300000012",
	"4" => "2002100000015",
	"5" => "2002200003246"
);

if (!isset($queries[$syncType])) {
	print "1:Incorrect vlaue for parameter [syncType]:$syncType";
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
