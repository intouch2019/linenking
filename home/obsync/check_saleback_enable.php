<?php

include "checkAccess.php";
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
extract($_POST);

try {
    $store_id = $gCodeId;
    try{
//    $store_id = 442;
    $db = new DBConn();
    $obj = $db->fetchObject("select saleback_starttime,saleback_endtime from it_codes where id=$store_id");
    $st_time = $obj->saleback_starttime ;
    $ed_time = $obj->saleback_endtime;
    
    $current_time = date('Y-m-d H:i:s');
    if ($current_time > $st_time && $current_time < $ed_time) {
        print_r("0");
    } else {
        print_r("1");
    }
    } catch (Exception $e){
        print_r("1");
    }
    $db->closeConnection();
} catch (Exception $ex) {
    
}