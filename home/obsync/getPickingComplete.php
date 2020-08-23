<?php
include "checkAccess.php";
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
extract($_POST);
//echo $lastid;

//return;
//extract($_GET);


if(!isset($lastid) || trim($lastid) == ""){
//if(trim($lastid) == ""){
    print "1::Missing parameter";
    return;
}
try{
    $db = new DBConn();
    $query = "select order_nos,id as pickingId,storeid from it_ck_pickgroup where id > $lastid and shipped_time is not null ";
    $pickingObjs = $db->fetchObjectArray($query);
    $json_objs = array();
    foreach($pickingObjs as $obj){
        $json_objs[] = array(
            $obj->order_nos,
            $obj->pickingId,
            $obj->storeid
        );
    }
}catch(Exception $xcp){
    print $xcp->getMessage();
}
$db->closeConnection();
$json_str = json_encode($json_objs);
print "0::$json_str";
?>
