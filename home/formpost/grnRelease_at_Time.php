<?php

//require_once("../../it_config.php");
require_once("/var/www/html/linenking/it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once 'lib/users/clsUsers.php';
require_once "lib/logger/clsLogger.php";
require_once "lib/grnPDFClass/GeneratePDF.php";
require_once "lib/orders/clsOrders.php";

extract($_POST);
//exit;
$db = new DBConn();
$clsLogger = new clsLogger();
$clsOrders = new clsOrders();
//print_r($_POST);


$date1 = "13:30:00";
$date2 = "20:30:00";
$date3 = "24:00:00";
$dt = date('Y-m-d H:i:s');
$date = new DateTime($dt);
$activedt_new = $date->format('H:i:s');

print_r($date);

echo "<pre>";
print_r($activedt_new);
echo "</pre>";


$sq = "select id,designid from release_orders where Rel_sent =0 and Release_time ='13:30:00'";
$iobj = $db->fetchObjectArray($sq);


foreach ($iobj as $io) {
//echo "<pre>";
//    print_r($io);
//   echo "</pre>"; 
    if ($activedt_new >= $date1 && $activedt_new < $date2) {


        $duqr = "update release_orders set rel_sent=1,Rel_sent_temp=1 where id=$io->id";
        $db->execUpdate($duqr);

        $fields = unserialize($io->designid);
        $fields['id']=$io->id;
        print_r($fields);
       
        $url = 'http://linenking.intouchrewards.com/formpost/grnReleaseAll.php';
        //$fields = array( 'cdesp' => urlencode($record));

        $fields_string = "";
        $fields_string = http_build_query($fields);
        rtrim($fields_string, '&');

////open connection
        $ch = curl_init();

////set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, count($fields));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);

////execute post
        $result = curl_exec($ch);
////close connection
        curl_close($ch);
    }
}


$sqs = "select id,designid from release_orders where Rel_sent =0 and Release_time ='20:30:00'";
$iobs = $db->fetchObjectArray($sqs);

foreach ($iobs as $iob) {


    if ($activedt_new >= $date2 && $activedt_new < $date3) {


        $duqry = "update release_orders set rel_sent=1,Rel_sent_temp=1 where id=$iob->id";
        $db->execUpdate($duqry);


        $fields = unserialize($iob->designid);

        $fields['id']=$iob->id;
        print_r($fields);
        
        $url = 'http://linenking.intouchrewards.com/formpost/grnReleaseAll.php';


        $fields_string = "";
        $fields_string = http_build_query($fields);
        rtrim($fields_string, '&');

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, count($fields));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);

        $result = curl_exec($ch);

        curl_close($ch);
    }
}


$db->closeConnection();

