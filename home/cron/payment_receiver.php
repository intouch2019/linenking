<?php

ini_set('memory_limit', '-1');
ini_set('max_execution_time', '-1');
require_once("/var/www/html/linenking/it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/serverChanges/clsServerChanges.php";

$db = new DBConn();
$stats = "";
$Amt_paid = "";
$error = "";
$username = 'rzp_live_pbnA6VXqiDfrQ6';
$password = 'TI3RGPLdTX7Tz0mvsPXzV21N';
$credentials = base64_encode($username . ':' . $password);

$pay_done = $db->fetchObjectArray("select id,store_id,store_name,status ,Invoice_amtid,invoice_nos from it_payment_gateway where is_sent=1");

if (isset($pay_done)) {
    foreach ($pay_done as $paysep) {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.razorpay.com/v1/payment_links/' . $paysep->Invoice_amtid,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Basic ' . $credentials
            ),
        ));

        $response = curl_exec($curl);
        $var = json_decode($response, true);

        print_r($response);

        if (isset($var['status'])) {
            $stats = $var['status'];
        }

        if (isset($var['amount_paid'])) {
            $Amt_paid = $var['amount_paid'];
        }


        if ($stats == "created" || $stats == "paid" || $stats == "failed" || $stats == "expired") {

            if ($stats == "paid") {
                $updatequery = "update it_payment_gateway set Receive_response='$response',status='$stats',  amount_paid='$Amt_paid',is_sent=2, updatetime = now() where id=$paysep->id";
                $updatedresponse = $db->execUpdate($updatequery);

                $query1 = "update it_codes set inactive=0, inactivated_by = '' , inactivating_reason = '', paymentlink='', inactive_dttm = now() where id =$paysep->store_id";
                $rowaffect = $db->execUpdate($query1);

                $invoice_done = $db->fetchObject("select id,store_name from it_codes where id=$paysep->store_id and is_natch_required=0 and store_type !=3");

                if (isset($invoice_done) && $invoice_done->id) {

                    $inv_ups = "update it_sp_invoices set non_nach_p=2 where store_id=$invoice_done->id and invoice_no=$paysep->invoice_nos";
                    $inv_up = $db->execUpdate($inv_ups);
                }
            } elseif ($stats == "failed") {

                $updatequ = "update it_payment_gateway set Receive_response='$response',status='$stats', updatetime = now() where id=$paysep->id";
                $updatedre = $db->execUpdate($updatequ);
                $query4 = "update it_codes set inactivating_reason='Payment failed please try again later' where id =$paysep->store_id";
                $quu = $db->execUpdate($query4);
                
            } elseif ($stats == "expired") {

                $updatequery = "update it_payment_gateway set Receive_response='$response',is_sent=3,status='$stats', updatetime = now() where id=$paysep->id";
                $updatedresponse = $db->execUpdate($updatequery);
                $query3 = "update it_codes set inactivating_reason='Payment link is expired please contact Account Team',paymentlink='' where id =$paysep->store_id";
                $qu = $db->execUpdate($query3);
            } else {
                continue;
            }
        } else {
            $updatequery = "update it_payment_gateway set Receive_response='$response',is_sent=3,status='$stats', updatetime = now() where id=$paysep->id";
            $updatedresponse = $db->execUpdate($updatequery);
            $query3 = "update it_codes set inactivating_reason='Something went wrong please contact IT Teams',paymentlink='' where id =$paysep->store_id";
            $qu = $db->execUpdate($query3);
        }
        curl_close($curl);
    }
}
