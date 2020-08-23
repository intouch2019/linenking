<?php
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";

define("REALM", "Intouch-Orders-Processing");

extract($_GET);

if (!isset($license) || trim($license) == "") {
	print "1::Missing parameter1";
	return;
}
$license = trim($license);
if (!isset($macaddress) || trim($macaddress) == "") {
	print "1::Missing parameter2";
	return;
}
$macaddress = trim($macaddress);
$commit=true;
/*
if (!isset($password) || trim($password) == "") {
	print "1::Missing parameter3";
	return;
}
*/

$db = new DBConn();
$license=$db->safe(trim($license));
$query = "select * from it_codes where licence=$license";
$obj = $db->fetchObject("select * from it_codes where license=$license");
if (!$obj || $obj->macaddress != null) {
	print "1::Incorrect License Key";
	$db->closeConnection();
	return;
}

$expdt = "31122099";
$cmd = "/usr/bin/java -jar /home/cottonking/crypt/IntouchLicense.jar $macaddress $expdt";
$pos_license = exec($cmd);

$username = $obj->id."-sync";
$t = strrev(time()+"");
$password = substr(md5($username.$t), 1, 12);

$userpass = "$username:$password";
$cmd = "/usr/bin/java -jar /home/cottonking/crypt/IntouchLicense.jar $license $userpass";
$access = exec($cmd);

$str = $username.":".REALM.":".$password;
$a1hash = $db->safe(md5($str));
print "$str-$a1hash<br>";

$macaddress=$db->safe($macaddress);
$username=$db->safe($username);
if ($commit) {
$db->execUpdate("update it_codes set macaddress=$macaddress, username=$username, a1hash=$a1hash where id=".$obj->id);
}
$json_str=json_encode((object)array("license"=>$pos_license, "access"=>$access));
print "0::[$json_str]";
