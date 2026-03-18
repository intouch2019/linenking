<?php

require_once "../../it_config.php";
require_once "lib/db/DBConn.php";

$db = new DBConn();

$new_store_id = $_GET['new_store_id'];

$row = $db->fetchObject("select old_id from it_codes where id = $new_store_id");

$result = array();

if($row && $row->old_id != ""){

$result = explode(",", $row->old_id);

}

echo json_encode($result);

?>