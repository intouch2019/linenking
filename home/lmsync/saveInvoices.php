<?php
include "checkAccess.php";
require_once "../../ck_config.php";

require_once "lib/logger/clsLogger.php";
require_once "lib/db/DBConn.php";

error_log("in saveInvoices", 3, "/var/www/intouch/logs/ck-debug.log");
extract($_POST);
$logger = new clsLogger();
//$records="L00000000001<>0010010026221<>1.000||L00000000001<>0010010027299<>1.000||L00000000001<>0010010026289<>1.000||L00000000001<>0010010025248<>1.000||L00000000001<>0010010026300<>1.000||L00000000001<>0010010026285<>1.000||L00000000001<>0010160001025<>1.000||L00000000001<>0010010026284<>1.000||L00000000001<>0010160001168<>1.000||L00000000001<>0010010027381<>1.000||L00000000001<>0010170000467<>1.000||L00000000001<>0010170000345<>1.000||L00000000001<>0010010027379<>1.000||L00000000001<>0010170000416<>1.000||L00000000001<>0010170000410<>1.000||L00000000001<>0010170000468<>1.000||L00000000001<>0010040004991<>1.000||L00000000001<>0010040004991<>1.000||L00000000001<>0010040004991<>1.000||L00000000001<>0010010026275<>1.000||L00000000001<>0010040005478<>1.000||L00000000001<>0010040005476<>1.000||L00000000001<>0010040004929<>1.000||L00000000001<>0010040005442<>1.000||L00000000001<>0010040005402<>1.000||L00000000001<>0010040005440<>1.000||L00000000001<>0010010026292<>1.000||L00000000001<>0010040004996<>1.000||L00000000001<>0010010027383<>1.000||L00000000001<>0010040005378<>1.000||";
if (!$records) {
	$logger->logError("CK:Missing parameter [records]:".print_r($_POST, true));
	print "Missing parameter [records]";
	return;
}
$logger->logInfo("saveInvoices:$records");

require_once "lib/db/DBConn.php";
$db = new DBConn();
$arr = explode("||",$records);
$first=true;
$invoice_id=false;
$po_numbers = false;
foreach ($arr as $record) {
	if (trim($record) == "") { continue; }
	$fields = explode("<>",$record);
//error_log("$record", 3, "/var/www/intouch/logs/ck-debug.log");
	if ($first) {
		$first=false;
		$invoice_no = $db->safe($fields[0]);
		$invoice_dt = $db->safe($fields[1]);
		$invoice_type = $db->safe($fields[2]);
		$cust_code = $db->safe($fields[3]);
		$invoice_amt = floatval($fields[4]);
		$invoice_qty = intval($fields[5]);
		$po_numbers = trim($fields[6]) == "" ? false : trim($fields[6]); 
		$query = "insert into it_ck_invoices set invoice_no=$invoice_no, invoice_dt=$invoice_dt, invoice_type=$invoice_type, cust_code=$cust_code, invoice_amt=$invoice_amt, invoice_qty=$invoice_qty";
		if ($po_numbers) { $query .= ", po_numbers=".$db->safe($po_numbers); }
		$invoice_id = $db->execInsert($query);
		if (!$invoice_id) { break; }
	} else if ($invoice_id) {
		$item_code = $db->safe($fields[0]);
		$price = floatval($fields[1]);
		$quantity = intval($fields[2]);
		$query = "insert into it_ck_invoicedetails set invoice_id=$invoice_id, item_code=$item_code, price=$price, quantity=$quantity";
		$db->execInsert($query);
		$query = "select * from it_ck_items where item_code = $item_code";
//error_log($query."\n", 3, "/var/www/intouch/logs/ck-debug.log");
		$obj = $db->fetchObject($query);
		if (!$obj) { continue; }
		$ctg_id = $db->safe($obj->ctg_id);
		$style_id = $db->safe($obj->style_id);
		$size_id = $db->safe($obj->size_id);
		$design_no = $db->safe($obj->design_no);
		$MRP = $obj->MRP;
		// always deduct the stock
		$query = "update it_ck_items set curr_qty = curr_qty - $quantity where id=$obj->id";
//error_log($query."\n", 3, "/var/www/intouch/logs/ck-debug.log");
// CK will not take orders in the store - INVOICE related orders quantities should not be subtracted
// DISABLE-17-04-2012		$db->execUpdate($query);
	} else { // no invoice_id - stop
		break;
	}
}
//error_log("$invoice_id:$po_numbers\n", 3, "/var/www/intouch/logs/ck-debug.log");
if ($invoice_id && $po_numbers) { // Reverse the stock deductions from the related orders
	$po_list = explode(",", $po_numbers);
	$arr = array();
	foreach ($po_list as $po) {
		$po = $db->safe(trim($po));
		$arr[] = $po;
	}
	$po_list = implode(",", $arr);
	$query = "select i.* from it_ck_orderitems i, it_ck_orders o where o.order_no in ($po_list) and o.id = i.order_id";
	$orderitems = $db->fetchObjectArray($query);
	foreach ($orderitems as $ord) {
	        // there could be multiple item-codes for the same ctg-design-size-style-mrp
	        // pick the first one and add the quantity to it
	        $query = "select id from it_ck_items where ctg_id='$ord->ctg_id' and design_no='$ord->design_no' and MRP=$ord->MRP and style_id='$ord->style_id' and size_id='$ord->size_id' order by curr_qty desc limit 1";
	        $item = $db->fetchObject($query);
	        if ($item) {
	                $query = "update it_ck_items set curr_qty=curr_qty + ".$ord->order_qty." where id=$item->id";
//error_log($query."\n", 3, "/var/www/intouch/logs/ck-debug.log");
// DISABLED - see note above	                $db->execUpdate($query);
	        }
	}
}
$db->closeConnection();
print "Success";
?>
