<?php
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
require_once 'lib/core/Constants.php';
require_once "lib/serverChanges/clsServerChanges.php";
$db = new DBConn();
$serverCh = new clsServerChanges();
$count=0;
try{
    
    $query = "select * from it_rules where id = 25";
    //$query = "select * from it_rules order by id desc limit 1";
    $obj = $db->fetchObject($query);
    $sch_id = $obj->ID;
    $server_ch = json_encode($obj);
    $ser_type = changeType::rules;
    //insert scheme update data against all the existing store in server changes table
//    $query = "select * from it_codes where usertype = ".UserType::Dealer." and inactive = 0 and is_closed = 0";
$query = "select * from it_codes where usertype = ".UserType::Dealer." and inactive = 0 and is_closed = 0 and id in (87)";
//$query = "select * from it_codes where id in (99,81,87,83,62,86)";
    //$query = "select store_id from it_server_changes where changedata like '%20% discount on purchase of more than 1 garment. Accessories sale not allowed.%'";
    $allStores = $db->fetchObjectArray($query);
    foreach($allStores as $store){
      $serverCh->save($ser_type, $server_ch,$store->id,$sch_id);
      $count++;
    }
    
}catch( Exception $xcp){
    print $xcp->getMessage();
}
print "0::success \n Total ".$count." rows inserted in server changes table ";
?>
