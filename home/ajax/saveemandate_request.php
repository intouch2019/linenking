<?php

require_once "../../it_config.php";
require_once "lib/db/DBConn.php";

if (isset($_POST['store_id']))
	$store_id = $_POST['store_id'];
if (isset($_POST['msg_id']))
	$msg_id = $_POST['msg_id'];

try {
  $db = new DBConn();
  
  
 
  $updquery="update it_codes set emandate_msgid=emandate_msgid+1 where id=$store_id";
  $db->execUpdate($updquery);
  
$insquery="insert into it_emandate_msgid set store_id=$store_id ,msg_id='".$msg_id."'";
$db->execInsert($insquery);
  
  
} catch (Exception $ex) {
    
}
