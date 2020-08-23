<?php
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";

$db = new DBConn();

//$store_id = 150;

$query = "select * from it_store_pingtime order by store_id desc ";

$obj_store = $db->fetchObjectArray($query);
$count=1;
if(isset($obj_store)){
    
foreach ($obj_store as $obj){
    $storeid = $obj->store_id;
    $store_pingtime = $obj->pingtime;
  print "$count ) Store id: ".$storeid." "."PingTime  ".$store_pingtime ;
  echo '</br>';
  echo '</br>';
  $count++;
}

}
echo 'Total Store Record  '.$count=$count-1;








