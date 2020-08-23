<?php
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/serverChanges/clsServerChanges.php";

$db = new DBConn();
$serverCh = new clsServerChanges();
$count = 0;

try{
    
    $query = "select * from it_ck_designs";
    $resultset = $db->execQuery($query);
    
    while($obj = $resultset->fetch_object()){
        if(isset($obj)){
                print "\nCNT:".$count++;
                $server_ch = "[".json_encode($obj)."]";
               // $ser_type = changeType::design_line_rack_updated;
               // $store_id = DEF_WAREHOUSE_ID;
                $ser_type = changeType::ck_designs;               
               // $serverCh->save($ser_type, $server_ch, $store_id,$obj->id);
                $ck_warehouse_id = DEF_CK_WAREHOUSE_ID;
                $serverCh->save($ser_type, $server_ch, $ck_warehouse_id,$obj->id);
        }
    }
    
} catch (Exception $ex) {
  print $ex->getMessage();
}

print "\nDone\n Tot designs pushed to server changes $count \n";

