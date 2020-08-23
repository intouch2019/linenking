<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";

$db = new DBConn();
$result = $db->execQuery("select * from it_ck_items order by id");
if (!$result) { return; }
while ($obj = $result->fetch_object()) {
$item_code=$obj->item_code;
//$item_code=str_replace("'","",$item_code);
$db->execUpdate("update it_ck_items set item_code=$item_code where id=$obj->id");
print "$item_code\n";
}
$result->close();
