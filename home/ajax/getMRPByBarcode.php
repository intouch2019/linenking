<?php

require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
require_once "lib/orders/clsOrders.php";
require_once("session_check.php");
require_once "lib/logger/clsLogger.php";
$db = new DBConn();

$values = $_GET["values"];

//print_r($valuearray);
//exit();
$barcode = $db->safe($values);

$query = "select mrp from it_items where barcode = $barcode";
$barcodeMRP = $db->fetchObject($query);


if(isset($barcodeMRP) || !empty($barcodeMRP)){
    $mrp = $barcodeMRP->mrp;
   echo $mrp;
} else {
    echo 'Not Found';
}

