<?php
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
include "checkAccess.php";

extract($_POST);

//if ($gCodeId != 61) { print "1::Unauthorized access"; return; }

if (!isset($store_name)) {
	print "1::Missing parameters";
	return;
}

$code=time();
$db = new DBConn();
if (isset($store_id)) {
$db->execUpdate("update it_codes set store_name='$store_name' where id=$store_id");
} else {
$store_id=$db->execInsert("insert into it_codes set code='$code', store_name='$store_name', creator=0");
}
$db->closeConnection();
print "0::$store_id";
