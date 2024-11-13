<?php

require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
require_once "lib/orders/clsOrders.php";
require_once ("session_check.php");
require_once "lib/logger/clsLogger.php";
    extract($_GET);
     $id = ($_GET['id']);
        $db = new DBConn();

$query = "update it_sp_invoices set is_procsdForRetail = 1 where id = '$id'";
$obj = $db->execUpdate($query);

if($obj == "1"){
    echo "1";
} else {
    echo "0"; // return error if update fails
}
?>