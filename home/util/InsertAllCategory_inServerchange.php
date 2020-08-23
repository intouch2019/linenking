<?php

require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/serverChanges/clsServerChanges.php";
//require_once 'lib/users/clsUsers.php';

try{
$serverCh = new clsServerChanges();
$db = new DBConn();
$query="select * from it_categories";
$name="";
$count=0;
$objs=$db->fetchObjectArray($query);
foreach($objs as $obj)
{
     $server = json_encode($obj);
     $server_ch = "[".$server."]";
     $ser_type = constant("changeType::categories"); 
     $serverCh->insert($ser_type, $server_ch,$obj->id); 
      $name.=$obj->name."|";
      $count++;
     
}
echo"Total ".$count." row inserted in it_server_changes <br/> For Categories=><br/>".$name;
}catch(Exception $e)
{
  echo $xcp->getMessage() ;
}




