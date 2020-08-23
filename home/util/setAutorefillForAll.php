<?php

require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";

try{
$db = new DBConn();
$name="";
$count=0;
$page_id=0;
$user_id=0;
$query=" select id from it_pages where pagename='Auto Refill Orders'";
$pobj=$db->fetchObject($query);
if(isset($pobj))
{
    $page_id=$pobj->id;
}


$store_query="select id ,store_name from it_codes where usertype=4 and is_closed=0 ";
$objs=$db->fetchObjectArray($store_query);

foreach($objs as $obj)
{
     $user_id=$obj->id;
     $name=$obj->store_name;
     $insert_query=" insert into it_user_pages set page_id=$page_id , user_id=$user_id";
     $db->execInsert($insert_query);
     echo "Permission of page id $page_id set for Store name:".$name."<br>";
      $count++;
     
}
echo"Total ".$count." row inserted ";
}catch(Exception $e)
{
  echo $xcp->getMessage() ;
}




