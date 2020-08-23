<?php
include "checkAccess.php";
require_once "../../it_config.php";

require_once "lib/logger/clsLogger.php";
require_once "lib/db/DBConn.php";

extract($_POST);
$logger = new clsLogger();
if (!$records) {
	$logger->logError("CK:Missing parameter [records]:".print_r($_POST, true));
	print "Missing parameter [records]";
	return;
}
//$records = "501<>8900000002641<>-1<>2147483647||502<>8900000002788<>-2<>2147483647||";
//qty is being received as -ve
//require_once "lib/db/DBConn.php";
try{
$db = new DBConn();
$arr = explode("||",$records);
foreach ($arr as $record) {
	if (trim($record) == "") { continue; }
	$fields = explode("<>",$record);
	$doc_no = $db->safe($fields[0]);
//	$item_code = $db->safe($fields[1]);
	$sp_item_code = $db->safe($fields[1]);
	$item_qty = intval($fields[2]);
        $dt = $fields[3];
        $dt /= 1000;
        $date = $db->safe(date("Y-m-d H:i:s", $dt));
	//$query = "insert into it_sp_salesreturns set doc_no = $doc_no, item_code = $item_code, sp_item_code=$sp_item_code, item_qty = $item_qty";
        $query = "insert into it_sp_salesreturns set doc_no = $doc_no, sp_item_code=$sp_item_code, item_qty = $item_qty ,date = $date ";
	$db->execInsert($query);
	$obj = $db->fetchObject("select * from it_items where barcode = $sp_item_code");
	if ($obj) {
		$db->execUpdate("update it_items set curr_qty = curr_qty - $item_qty, updatetime=now() where id=$obj->id");
	}
}
$db->closeConnection();
}catch(Exception $xcp){
    print $xcp->getMessage();
}
print "0::Success";
?>
