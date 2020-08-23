<?php
include "checkAccess.php";
require_once "../../it_config.php";

require_once "lib/logger/clsLogger.php";
require_once "lib/db/DBConn.php";
require_once "lib/logger/clsLogger.php";

$clsLogger = new clsLogger();
// it is for return from warehouse to factory

extract($_POST);
$logger = new clsLogger();
//$records="2002100000001<>0010040000007<>1.000||2002100000001<>0010040000075<>1.000||2002100000001<>0010040000077<>1.000||2002100000001<>0010040000078<>3.000||2002100000001<>0010040000784<>2.000||2002100000001<>0010040000842<>1.000||2002100000001<>0010040000881<>3.000||2002100000001<>0010040000888<>1.000||2002100000001<>0010040000889<>19.000||2002100000001<>0010040001479<>12.000||2002100000001<>0010040001480<>1.000||2002100000001<>0010040001481<>7.000||2002100000001<>0010040001482<>1.000||2002100000001<>0010040001486<>1.000||2002100000001<>0010040001487<>29.000||2002100000001<>0010040001488<>19.000||2002100000001<>0010040001489<>13.000||2002100000001<>0010040003551<>2.000||2002100000001<>0010040003552<>14.000||2002100000001<>0010040003553<>6.000|| ";
//if (!$records) {
if (!isset($records) || trim($records) == "") {
	$logger->logError("CK:Missing parameter [records]:".print_r($_POST, true));
	print "Missing parameter [records]";
	return;
}
//qty is being received as -ve
require_once "lib/db/DBConn.php";
$db = new DBConn();
$arr = explode("||",$records);
foreach ($arr as $record) {
	if (trim($record) == "") { continue; }
	$fields = explode("<>",$record);
	$doc_no = $db->safe($fields[0]);
	$item_code = $db->safe($fields[1]);
//	$sp_item_code = $db->safe($fields[2]);
//	$item_qty = intval($fields[3]);
       	$item_qty = intval($fields[2]);
//        $date = $db->safe($fields[3]);
        $dt = $fields[3];
        $dt /= 1000;
        $date = $db->safe(date("Y-m-d H:i:s", $dt));
	$neg_item_qty = 0 - $item_qty;
//	$query = "insert into it_sp_purchasereturns set doc_no = $doc_no, item_code = $item_code, sp_item_code=$sp_item_code, item_qty = $item_qty";
        $query = "insert into it_purchasereturns set doc_no = $doc_no, item_code = $item_code, item_qty = $item_qty , date = $date";
	$db->execInsert($query);
	$obj = $db->fetchObject("select * from it_items where barcode = $item_code");
	if ($obj) {
            $updatedQTY = $obj->curr_qty + $item_qty;
            if($updatedQTY > 0){
		$query = "update it_items set curr_qty = curr_qty + $item_qty, updatetime=now() where id=$obj->id";
		$db->execUpdate($query);
            }else{
                $query = "update it_items set curr_qty = 0 , updatetime=now() where id=$obj->id";
		$db->execUpdate($query);
            }  
            //--> code to log it_items update track
            $ipaddr =  $_SERVER['REMOTE_ADDR'];
            $pg_name = __FILE__;              
            $clsLogger->logInfo($query,false, $pg_name,$ipaddr);
            //--> log code ends here
	}
}
$db->closeConnection();
print "0::Success";
?>
