<?php
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
require_once "lib/orders/clsOrders.php";
require_once ("session_check.php");
require_once "lib/logger/clsLogger.php";
$db = new DBConn();
$storeid=$_GET["storeid"];

$storeaddress=$db->fetchObject("select address from it_codes where id=$storeid");
if(isset($storeaddress)){
    echo $storeaddress->address;
}else{
    echo "Store address not found";
}

?>