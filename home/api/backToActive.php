<?php
//include "checkAccess.php";
require_once "../../it_config.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once "lib/db/DBConn.php";

extract($_POST);

//extract($_GET);

$order=false;

if (isset($_POST['order'])){
	$order = $_POST['order'];
}


try{
    $db = new DBConn();
    
    if(!$order){ print "1::Not able to get Order"; }else{

        //$order = '{"active_time":"2014-12-05 08:49:47","ctime":"2015-01-28 18:41:52","dispatcher_id":101,"id":33,"num_designs":11,"orderItems":[],"order_amount":10165.0,"order_ids":"21186","order_nos":"AT019523","order_qty":11,"pick_group_id":15209,"picking_time":"2015-01-28 18:41:52","status":2,"store_id":106,"store_name":"Chinchwad (KAPIL COLLECTIONS)"}';

        $obj_order =  json_decode($order);         

        //print_r($obj_order);
        
        $picking_id = $obj_order->pick_group_id;
        $store_id = $obj_order->store_id;
        $order_nos = $db->safe($obj_order->order_nos);
        $status = 0;
        $order_type = 0; //ck orders
        $attributes = "";

        $query = "update it_ck_orders set updatetime = now(), status = ".OrderStatus::Active.", pickgroup = null where pickgroup = $picking_id";
        $db->execUpdate($query);
        $query = "delete from it_ck_pickgroup where id = $picking_id";
        $db->execQuery($query);
        print "0::success";
    return;
    }
}catch(Exception $ex){
    print "1::Error-".$ex->getMessage();
}