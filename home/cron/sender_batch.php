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

$errar = "";
$stats = "";
$start_date = date('Ymd');
$no = uniqid();
$barcode_batch = 'LK' . $start_date . $no;

$result = $db->execQuery("select bar_id, batch_id,barcode,Manufacturer,Product ,Design ,MRP ,Brand ,Style ,Size ,Production_Type ,Material ,Fabric_Type ,Units from it_new_barcode_batch_items where bar_id in (select id from it_new_barcode_batch where is_sent=0 and main_file is null )");

if (isset($result) && $result->num_rows != 0) {

    $fp = fopen("../cron/b_batch/$barcode_batch.csv", "w");
    fputs($fp, "Batch Id,Barcode,Manufacturer,Product,Design,MRP,Brand,Style,Size,Production Type,Material,Fabric Type,Units\n");

    while ($item = $result->fetch_object()) {
        fputs($fp, "$item->batch_id,$item->barcode,$item->Manufacturer,$item->Product,$item->Design,$item->MRP,$item->Brand,$item->Style,$item->Size,$item->Production_Type,$item->Material,$item->Fabric_Type,$item->Units \n");

        $mainbatchid = "update it_new_barcode_batch set main_file='$barcode_batch.csv' where id=$item->bar_id ";
        $insert_idd = $db->execUpdate($mainbatchid);
    }
    fclose($fp);
    system("../cron/b_batch/$barcode_batch.csv");
    $m_batchname = $barcode_batch . '.csv';

    apisender($m_batchname);
}

function apisender($m_batchname) {
    $db = new DBConn();

    $start_date = date('Y-m-d H:i:s');
    echo "<br>Execution start of sender batch...<br> datetime: " . $start_date . "<br>";

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://bu-fashionking-wc1wyu.truevuecloud.com/api/v1/productUpload?businessUnitId=61830fe6-e3b5-4553-bda1-cc91944cea19&apikey=eeYNNlKSGf42Aon9pLLN8cZZaw9GE8ub&delimiter=,',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
         CURLOPT_POSTFIELDS => array('file'=> new CURLFILE('/var/www/html/linenking/home/cron/b_batch/'.$m_batchname)),
      //  CURLOPT_POSTFIELDS => array('file' => new CURLFILE('C:/xampp/htdocs/ck_new_y/home/cron_barcode/m_batch/' . $m_batchname . '')),
        CURLOPT_HTTPHEADER => array(
            'accept: */*'
        ),
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
        $productid = $var['productUploadHistId'];
    }

    if (isset($var['totalRecordsWithError'])) {
        $errar = $var['totalRecordsWithError'];
    }

    if (isset($var['startDate'])) {
        $datee1 = $var['startDate'];
    }


    if ($stats == "UPLOADED") {

        $finalup = "update it_new_barcode_batch set response='$response',status='$stats',is_sent=1,productUploadHistId='$productid',updatetime=now() where main_file='$m_batchname'";
        $inserted = $db->execUpdate($finalup);
    }

    $end_date = date('Y-m-d H:i:s');
    echo "<br>Execution end of sender batch...<br> datetime: " . $end_date . "<br>";
}
