<?php

ini_set('memory_limit', '-1');
ini_set('max_execution_time', '-1');
require_once("/var/www/html/linenking/it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";




//require_once("/../../it_config.php");
//require_once("session_check.php");
//require_once "lib/db/DBConn.php";
//require_once "lib/logger/clsLogger.php";


$db = new DBConn();

$result = "select distinct productUploadHistId from it_new_barcode_batch where is_sent=1 and main_file is not null ";

$receivedatas = $db->fetchObjectArray($result);

if (isset($receivedatas)) {

    foreach ($receivedatas as $receivedata) {

        $productid = $receivedata->productUploadHistId;

        $start_date = date('Y-m-d H:i:s');
        echo "<br>Execution start of Receiver batch...<br> datetime: " . $start_date . "<br>";

///////////https://bu-fashionking-wc1wyu.truevuecloud.com/api/v1/productUpload/893fdfa9-1197-490a-bd4d-9e1a4938dba0?businessUnitId=61830fe6-e3b5-4553-bda1-cc91944cea19&apikey=eeYNNlKSGf42Aon9pLLN8cZZaw9GE8ub&delimiter=,

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://bu-fashionking-wc1wyu.truevuecloud.com//api/v1/productUpload/' . $productid . '?businessUnitId=61830fe6-e3b5-4553-bda1-cc91944cea19&apikey=eeYNNlKSGf42Aon9pLLN8cZZaw9GE8ub',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $response = curl_exec($curl);
        $var = json_decode($response, true);

        echo "<pre>";
        print_r($var);
        echo "</pre>";

        if (isset($var['status'])) {
            $stats = $var['status'];
        }
        if (isset($var['productUploadHistId'])) {
            $productidd = $var['productUploadHistId'];
        }

        if (isset($var['totalRecordsWithError'])) {
            $errar = $var['totalRecordsWithError'];
        }




        if ($stats == "COMPLETE" && $productidd == $productid) {


            $finalup = "update it_new_barcode_batch set response='$response',status='$stats',is_sent=2,updatetime=now() where productUploadHistId='$productidd'";

            $inserted = $db->execUpdate($finalup);
        }
        if ($stats == "FAILED") {

            $finalup = "update it_new_barcode_batch set response='$response',status='$stats',updatetime=now() where productUploadHistId='$productidd'";

            $inserted = $db->execUpdate($finalup);
        }
    }
    $end_date = date('Y-m-d H:i:s');
    echo "<br>Execution end of sender batch...<br> datetime: " . $end_date . "<br>";
}