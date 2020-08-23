<?php
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
require_once("session_check.php");
require_once "lib/core/Constants.php";
//require_once "lib/serverChanges/clsServerChanges.php";

extract($_GET);

if (!$cid && !$status) { return error("missing parameters"); }

try{
    $db = new DBConn();
//    $serverCh = new clsServerChanges();
   // $cat=$db->safe($cat);
    //echo "update it_categories set active=$status where id=$cid";
    $update=$db->execUpdate("update it_categories set active=$status , updatetime = now() where id=$cid");
    //echo "</br>".$update;
    if ($status == 1) $msg = "The category has been set to active";
    else $msg = "The category has been deactivated";
    catlogPgVisibility($cid,$status);
    if ($update != 0) {
        $obj = $db->fetchObject("select * from it_categories where id = $cid");
        //$server_ch = json_encode($obj);
//        if($status == 1){
//            $ser_type = changeType::ctg_activated;
//        }else{
//            $ser_type = changeType::ctg_inactivated;
//        }
//        no need to update server changes
//        $server = json_encode($obj);
//        $server_ch = "[".$server."]"; // converting n storing in obj format so that easy retrival at pos side
//        $ser_type = changeType::categories;
//        $serverCh->insert($ser_type, $server_ch);
        success($msg); /*return;*/
    } else {  return error("Update not successful, contact Intouch"); }
}catch(Exception $xcp){
    echo "error:There was a problem processing your request. Please try again later.";
 //   return;
}

function error($msg) {
    print json_encode(array(
            "error" => "1",
            "message" => $msg
            ));
}

function success($msg) {
    print json_encode(array(
            "error" => "0",
            "message" => $msg
            ));
}

function catlogPgVisibility($cid,$status){
    $db = new DBConn();
    if($status==0){ // means inactivated
        //then hide its visibilty from the catlog
        $query = "update it_pages set sequence = 0 where pagecode = '$cid'";
        //error_log("\nQ1:$query\n",3,"tmp.txt");
        $db->execUpdate($query);        
    }else if($status==1){ // means activated
        //then make the pg visible in the catlog
        $qry = "select * from it_pages where menuhead = 'Catalog' and sequence != 0 order by id desc limit 1";
        //error_log("\nQ2:$qry\n",3,"tmp.txt");
        $catlogdb = $db->fetchObject($qry);
        $query = "update it_pages set sequence = $catlogdb->sequence where pagecode = '$cid'";
        //error_log("\nQ3:$query\n",3,"tmp.txt");
        $db->execUpdate($query);
    }
}
?>
