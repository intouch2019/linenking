<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";

$db = new DBConn();
$result = $db->execQuery("select * from it_ck_pickgroup where active_time is null");
if (!$result) { return; }
while ($obj = $result->fetch_object()) {
$query = "select max(active_time) as max_active_time from it_ck_orders where id in ($obj->order_ids)";
$obj2 = $db->fetchObject($query);
if (!$obj2) { continue; }
$updQuery = "update it_ck_pickgroup set active_time='$obj2->max_active_time' where id=$obj->id";
$db->execUpdate($updQuery);
print "$updQuery\n";
}
$result->close();
