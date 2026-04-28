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
    $invoice_dt = $db->fetchObject("select invoice_dt from it_invoices where  invoice_no = '$invoice_no' and store_id = $store_id")->invoice_dt;
    $query =" update it_invoices set invoice_status = 1, invoice_pull_date = now(), lead_time = TIMESTAMPDIFF( HOUR, STR_TO_DATE('$invoice_dt', '%Y-%m-%d %H:%i:%s'), NOW() ) where invoice_no = '$invoice_no' and store_id = $store_id ";
//    print_r("query = ".$query); exit();
    $updated = $db->execUpdate($query);
    echo($updated); 
    






    $db->closeConnection();
} catch (Exception $ex) {
    
}