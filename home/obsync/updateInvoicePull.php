<?php

include "checkAccess.php";
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
extract($_POST);
try {
//    echo $gCodeId .' amit <br>';
    $postData = $_POST;
$invoice_no = $postData['invoice_no'];
//echo $formattedTimestamp;
//    print_r($formattedTimestamp );exit();
    $store_id = $gCodeId;
//    $store_id = 271;
  // update it_invoices set invoice_status = 1 where invoice_no = $invoice_no;
    $db = new DBConn();
    $query = "update it_invoices set invoice_status = 1 where invoice_no = '$invoice_no' and store_id = $store_id";
//    print_r("query = ".$query); exit();
    $updated = $db->execUpdate($query);
    echo($updated); 
    






    $db->closeConnection();
} catch (Exception $ex) {
    
}