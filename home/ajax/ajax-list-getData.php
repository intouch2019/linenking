<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";

extract($_GET);
$user = getCurrUser();

if (!isset($_GET['type'])) { return; }
if (!isset($_GET['letters'])) { return; }

$errors=array();
$success=array();

try{
	$db = new DBConn();

	$likeclause = "";
	if ($letters != "*") { $likeclause = "where name like '%".$letters."%'"; }
	$query = "select name from it_$type $likeclause order by name";
	$objs = $db->fetchObjectArray($query);
	foreach ($objs as $obj) {
		echo $obj->id."###".$obj->name."|";
	}
}catch(Exception $xcp){
    
}
?>
