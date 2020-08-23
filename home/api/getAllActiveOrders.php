<?php
//include "checkAccess.php";
require_once "../../it_config.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once "lib/db/DBConn.php";

extract($_POST);
try{
    
    
    $db = new DBConn();
    $orders = $db->fetchObjectArray("select o.id, o.store_id, o.order_no, o.order_qty, o.order_amount, o.num_designs, o.status,"
            . " o.active_time, o.createtime, c.store_name from it_ck_orders o, it_codes c where"
            . " o.store_id = c.id and o.status=".OrderStatus::Active." order by o.active_time desc");    
    
    if(isset($orders)){
        print "0::".json_encode($orders);
        return;
    }
    
}catch(Exception $ex){
    print "1::Error-".$ex->getMessage();
}
