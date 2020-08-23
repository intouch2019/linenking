<?php
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";

extract($_POST);
//extract($_GET);
//for testing
$db = new DBConn();
//$gCodeId = 98;
if (!isset($storeId)) {
	print "1::Missing parameter";
	return;
}

$gCodeInfo = $db->fetchObject("select id,state_id from it_codes where id = $storeId");

$state = $db->fetchObject("select state from states where id=$gCodeInfo->state_id");

print "0::".$state->state;



