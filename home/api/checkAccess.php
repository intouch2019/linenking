<?php
require_once "lib/db/DBConn.php";

$android_id = false;
$hash1 = false;
$t = false;
$params = array();
ksort($_POST);
foreach ($_POST as $key => $value) {
	if ($key == 'android_id') { $android_id = $value; continue; }
	if ($key == 'hash') { $hash1 = $value; continue; }
	if ($key == 't') { $t = $value; }
	$params[] = "$key:$value";
}
//print_r($params);
$params_str = implode(",",$params);
if (!$android_id || !$hash1) { print "1::Authentication failure1"; exit; }
$db = new DBConn();
//$obj = $db->fetchObject("select s.*, p.PASSCODE from it_stores s, it_stores_pass p where s.ID=$id and s.ID = p.STORE_ID and p.PASSCODE is not null");
$obj = $db->fetchObject("select * from it_pickup_instances where android_id =$id and license is not null and is_active = 0");
$db->closeConnection();
//if (!$obj || !isset($obj->PASSCODE) || trim($obj->PASSCODE) == "") { print "1::Authentication failure2"; exit; }
if (!$obj || !isset($obj->license) || trim($obj->license) == "") { print "1::Authentication failure2"; exit; }
//$hash2 = sha1($params_str.",".$obj->PASSCODE);
$hash2 = sha1($params_str.",".$obj->license);
//print "hash2=>".$hash2;
if ($hash1 != $hash2) { print "1::Authentication failure3:"; exit; }
$mytime = time();
if ($mytime > $t) { $diff = $mytime - $t; }
else { $diff = $t - $mytime; }
//if ($diff > 300) { // more than 5 minutes
//	print "1::Authentication failure4:$t:$mytime"; exit;
//}
$gStoreId = $android_id;
$obj->LICENSE=null;
$gStore = $obj;

function getCurrStoreId() {
	global $gStoreId;
	return $gStoreId;
}
