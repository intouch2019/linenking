<?php
require_once("../../it_config.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "session_check.php";

$pickgroup_id=false;
if (isset($_POST['pid']))
	$pickgroup_id = $_POST['pid'];
if (!$pickgroup_id) { print "Missing pickgroup id. Please report this error."; return; }
$db = new DBConn();
try
{
$db->execUpdate("update it_ck_orders set status=2 where pickgroup=$pickgroup_id");
//header("Location: ".DEF_SITEURL."admin/orders/packing");
echo json_encode(array("error" => "0"));
}
 catch (Exception $xcp) {
    echo json_encode(array("error" => "1", "message" => "Exception:" . $xcp->getMessage()));
}
?>