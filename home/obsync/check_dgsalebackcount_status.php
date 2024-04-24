<?php

include "checkAccess.php";
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
extract($_POST);
try {
//    echo $gCodeId .' amit <br>';
    $postData = $_POST;
$dateString = $postData['date'];
$formattedTimestamp = date("Y-m-d H:i:s", strtotime($dateString));
//echo $formattedTimestamp;
//    print_r($formattedTimestamp );exit();
    $store_id = $gCodeId;
//    $store_id = 271;
  
    $db = new DBConn();
    $query = "select barcode from defective_garment_form  where exchange_given_at_store = $store_id and exchange_bill_date > '$formattedTimestamp'";
//    $barcodes = "select barcode from defective_garment_form  where exchange_given_at_store = $store_id and exchange_bill_date > '$formattedTimestamp'";

//    print_r("query = ".$barcodes); exit();
    $objs = $db->fetchObjectArray($query);
//    $$barcodesofdg = $db->fetch($barcodes);

//    echo($countofdg->count); 
//    echo($$barcodesofdg->barcode); 
$barcodes1 = array();
foreach($objs as $obj){
    $barcodes1[] = $obj->barcode;
}

// Now $barcodes1 contains all the barcodes from the database query result

$count = count($barcodes1); // Count the number of barcodes
echo "Count = $count, Barcodes: " . implode(', ', $barcodes1);


    $db->closeConnection();
} catch (Exception $ex) {
    
}