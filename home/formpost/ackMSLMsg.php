<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";

extract($_POST);
$errors=array();
$success=array();
$db = new DBConn();
$store = getCurrUser();

//print_r($_POST);
try{
    $ack_msg = $db->safe(trim($mslmsgpost));
    $query = "update it_ck_orders set msl_ack=1 , msl_ack_dttm = now() , msl_ack_text = $ack_msg where id = $order_id ";
//    print $query;
    $db->execUpdate($query);
}catch(Exception $xcp){
    $errors[] = $xcp->getMessage();
}

header("Location: ".DEF_SITEURL."ajax/finalOrder.php");
exit;