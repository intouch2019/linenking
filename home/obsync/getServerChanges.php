<?php
include "checkAccess.php";
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";

// enable for warehouse and ckacedharampeth
//if ($gCodeId != 192 && $gCodeId != 119) { return; }

extract($_POST);
//extract($_GET);

if ((!isset($lastid) || trim($lastid) == "") && (!isset($batch_size) || trim($batch_size) == "")) {
//if ((!isset($lastid) || trim($lastid) == "")) {
    print "1::Missing parameters";
    return;
}

try {
    $db = new DBConn();
    $store_id = $gCodeId;
    // $store_id = 110;
//    $store_id = 70;
    $json_objs = array();
    //$batch_size = 100;

    $qry = "update it_codes set server_change_id = $lastid where id = $store_id";
    $db->execQuery($qry);

    if ($store_id != "84") {// skip warehouse id

        $query = "select * from it_store_pingtime where store_id= $store_id";
        $result = $db->fetchObject($query);
        if (isset($result)) {
            $qryupdate = "update it_store_pingtime set pingtime = now() where store_id = $store_id ";
            $db->execUpdate($qryupdate);
        } else {
            $queryinsert = "insert into it_store_pingtime set store_id=$store_id";
            $db->execInsert($queryinsert);
        }
    }

    //$query = "select id,type,changedata from it_server_changes where id > $lastid and id < $pid and ( store_id is null or store_id = $store_id ) order by id limit $batch_size ";
    $query = "select id,type,changedata from it_server_changes where id > $lastid and ( store_id is null or store_id = $store_id or store_id = 0) order by id limit $batch_size ";
    $objs = $db->fetchObjectArray($query);

    foreach ($objs as $obj) {
//        $json_serchgs = array();
//        $json_serchgs['type'] = $obj->type;
//        $json_serchgs['changedata'] = $obj->changedata;
//        $json_objs[] = "[".json_encode($json_serchgs)."]";
        //$json_objs[] = $json_serchgs;
        $json_objs[] = json_encode($obj);
//        $json_objs[] = $obj;  
    }
    $db->closeConnection();
    $json_str = json_encode($json_objs);
//    $json_str = json_encode($objs);
    print "0::$json_str";
} catch (Exception $ex) {
    print "1::Error" . $ex->getMessage();
}
?>