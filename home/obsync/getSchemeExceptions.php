<?php
require_once("../../it_config.php");
include "checkAccess.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";

//$user = getCurrUser();
extract($_POST);
if (!isset($scheme_id)) { print "1::missing scheme_id"; return; }

$db = new DBConn();
$obj = $db->fetchObject("select * from it_rule_exceptions where id=$scheme_id");
if (!$obj) { print "0::Rule Exceptions not found. Kindly report this to Intouch"; return; }
print "0::$obj->BARCODES";
$db->closeConnection();
