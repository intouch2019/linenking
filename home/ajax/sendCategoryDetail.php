<?php

ini_set('max_execution_time', 3000);
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
//require_once "lib/serverChanges/clsServerChanges.php";
//require_once "lib/logger/clsLogger.php";
//$date=date("d/M/Y h:i:s A");
extract($_POST);
if(isset($records))
{
    $Brand=$records;

try
{
     $db = new DBConn();
     $cat_query="select id,name from it_categories where active = 1";
     $Active_categories = $db->fetchObjectArray($cat_query);
     
     echo json_encode(array("Result"=>$Active_categories));
} catch (Exception $ex) {

}
}
else
{
    echo"Brand missing";
}