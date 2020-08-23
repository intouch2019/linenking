<?php
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
require_once "lib/orders/clsOrders.php";
require_once ("session_check.php");
require_once "lib/logger/clsLogger.php";

$db=new DBConn();

$str = getCurrUser();
$store_id = getCurrUserId();
       
$clsOrders = new clsOrders();
$cart = $clsOrders->getCartt($store_id);
//print("Cart=".print_r($cart,true));
//return; 
           
$clsLogger = new clsLogger();
$redirect="store/orders/active";
if ($cart) {
$query="select * from it_ck_orderitems where order_id=$cart->id";
$orderitems=$db->fetchObjectArray("select * from it_ck_orderitems where order_id=$cart->id");
if (count($orderitems) == 0) { print "Failed - no orderitems found [$query]"; return; }

foreach ($orderitems as $ord)
{
        // there could be multiple item-codes for the same ctg-design-size-style-mrp
        // pick the first one and deduct the quantity from it
//	$query = "select * from it_ck_sizes where ctg_id='$ord->ctg_id' and size_id='$ord->size_id'";
//	$obj = $db->fetchObject($query);
//	if (!$obj) {
//		error_log("Size not found:$query:".print_r($ord,true)."\n", 3, "/var/www/intouch/logs/ck-finalorder.log");
//		continue;
//	}
	//$size_group = $obj->size_group ? $obj->size_group : "'".$obj->size_id."'";
        //$query = "select id from it_ck_items where ctg_id='$ord->ctg_id' and design_no='$ord->design_no' and MRP=$ord->MRP and style_id='$ord->style_id' and size_id in ($size_group) order by curr_qty desc limit 1";
        //$item = $db->fetchObject($query);
        //if ($item) {
                $query = "update it_items set updatetime=now(),curr_qty=curr_qty - ".$ord->order_qty." where id=$ord->item_id";
//		error_log($query, 3, "/var/www/intouch/logs/ck-finalorder.log\n");
                //--> code to log it_items update track
                $ipaddr =  $_SERVER['REMOTE_ADDR'];
                $pg_name = __FILE__;                
                $clsLogger->logInfo($query,false, $pg_name,$ipaddr);
                //--> log code ends here
                $db->execUpdate($query);
//        } else {
//		error_log("Item not found:$query:".print_r($ord,true)."\n", 3, "/var/www/intouch/logs/ck-finalorder.log");
//	}
}   
//$query = "select sum(order_qty) as tot_qty, sum(order_qty * MRP) as tot_amt, count(distinct(design_no)) as num_designs from it_ck_orderitems where order_id=$cart->id";
$query = "select sum(oi.order_qty) as tot_qty, sum(oi.order_qty * oi.MRP) as tot_amt, count(distinct(oi.design_no)) as num_designs from it_ck_orderitems oi, it_items i where oi.order_id=$cart->id and oi.item_id = i.id and i.ctg_id != 29";
$obj = $db->fetchObject($query);
$cartinfo = "";
if ($obj) {
$cartinfo = ", order_qty=$obj->tot_qty, order_amount=$obj->tot_amt, num_designs=$obj->num_designs";
}
$query="update it_ck_orders set status=1,active_time=now() $cartinfo where id=$cart->id";
//print $query."<br/>";
$db->execUpdate($query);
//$redirect="store/vieworder/oid=$cart->id";
$redirect="store/orders/active";
} else {
//print "cart not found<br />";
}

//print '<a href="'.$redirect.'" />click here</a>';
$db->log("REDIRECT=$redirect");
header("Location: ".DEF_SITEURL.$redirect);
exit;
?>