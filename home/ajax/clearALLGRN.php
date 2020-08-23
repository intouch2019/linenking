<?php
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";


try{
    $db = new DBConn();
    $query = "update it_items set grn_qty = 0";
    $db->execUpdate($query);
    $msg = "GRN Cleared Successfully ";
    echo json_encode(array("error"=>"0","message" => $msg));
}catch(Exception $xcp){
    
}

