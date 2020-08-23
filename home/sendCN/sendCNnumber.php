<?php

//include '../../it_config.php';
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/conv/CurrencyConv.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once 'lib/users/clsUsers.php';

//include '../lib/db/DBLogic.php';
extract($_POST);

//$records = "16973<>2";
if (!isset($records) || trim($records) == "") {
    // $logger->logError("Missing parameter [records]:".print_r($_POST, true));
    print "1::Missing parameter [records]";
    return;
}

if (isset($records) || trim($records) != "") {

    $wo = $records;
     //print_r($records);
    $wo_arr = explode("<>", $wo);
    $wo_id = $wo_arr[0];
    $sr_no=$wo_id;
    
    $wo_brand = $wo_arr[1];
    // print " select * from it_workorder where id = $wo_id and brand=$wo_brand";

    $db = new DBConn();
    //$dbl=new DBLogic();
    print_r($db);


    $srno_updatequery="update creditnote_no set cn_no=$sr_no";
    

//print "$srno_updatequery";
$z=$db->execUpdate($srno_updatequery);

print "$z";

    //echo json_encode($obj_workorder);
    //print_r($obj_workorder);
    //print "0::success";
} else {
    print "1::error";
}