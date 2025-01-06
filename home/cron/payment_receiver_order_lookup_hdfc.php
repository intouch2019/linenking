<?php

//for live
ini_set('memory_limit', '-1');
ini_set('max_execution_time', '-1');
require_once("/var/www/html/linenking/it_config.php");
require_once "lib/db/DBConn.php";
require_once("session_check.php");
require_once "lib/core/Constants.php";
require_once 'lib/users/clsUsers.php';
require_once "lib/logger/clsLogger.php";
require_once ("util/Crypto.php");

//for testing
//require_once("../../it_config.php");
//require_once "lib/db/DBConn.php";
//require_once("session_check.php");
//require_once "lib/core/Constants.php";
//require_once 'lib/users/clsUsers.php';
//require_once "lib/logger/clsLogger.php";
//require_once ("util/Crypto.php");

$store = getCurrUser();
$db = new DBConn();


$starttime = "select now() as sttime";
print_r("Start time --> ");
print_r($db->fetchObject($starttime)->sttime);

// Test Credentials Fashionking
//$working_key = 'C31BB3656BDB9000D656BDA362A9F258';
//$access_code = 'ATBH45KL25CL13HBLC';
//$merchant_id = '3142863';


// Live Credentials Fashionking
$working_key = 'B2D18CFF8EFD4FFF3EAD0A1D37047B50';
$access_code = 'AVYK24LJ63AQ54KYQA';
$merchant_id = '3876548';

// // (is_sent = 1) -> Payment link is sent to mail but payment is not received.
// (is_sent = 2) -> Payment Successful or Shipped
// (is_sent = 3) -> Payment Unsuccessful and other status
// (is_sent = 4) -> If Invoice no sent is not same as invoice no received while creating the payment link
// non_nach_p=0 -> Default
// non_nach_p=1 -> When Payment link is created 
// non_nach_p=2 -> When Payment is received

$pay_done = $db->fetchObjectArray("select id, store_id, store_name, status, invoice_nos, reference_id,invoice_amt, createtime from it_payment_gateway_hdfc where is_sent=1");

if (isset($pay_done)) {
    foreach ($pay_done as $inv) {
        $from_date = date("d-m-Y", strtotime($inv->createtime));
        $merchant_json_data = array(
            "order_no" => $inv->reference_id, //$inv->reference_id
            "from_date" => $from_date, // $inv->createtime
            "page_number" => 1
        );


        $merchant_data = json_encode($merchant_json_data);
        $encrypted_data = encrypt($merchant_data, $working_key);
        $string_JSON = "enc_request=$encrypted_data&access_code=$access_code&request_type=JSON&response_type=JSON&command=orderLookup&version=1.2";
        $curl = curl_init();
        curl_setopt_array($curl, array(
            //Test URL
//            CURLOPT_URL => "https://apitest.ccavenue.com/apis/servlet/DoWebTrans?",
            //Live URL
            CURLOPT_URL => "https://api.ccavenue.com/apis/servlet/DoWebTrans?",
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_VERBOSE => 1,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $string_JSON,
            CURLOPT_RETURNTRANSFER => true
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        $payment_status = '';
        $information = explode('&', $response);
        $dataSize = sizeof($information);
        $api_status = explode('=', $information[0])[1];

        if ($api_status == 1) {//API call failed.
            $updatequery = "update it_payment_gateway_hdfc set Send_response='$response',status='API call was unsuccessful',is_sent=1, updatetime = now() where id=$inv->id ";
            $updatedresponse = $db->execUpdate($updatequery);

            $query3 = "update it_codes set inactivating_reason='Something went wrong please contact IT Team',paymentlink='' where id =$inv->store_id";
            $qu = $db->execUpdate($query3);
        } else {
            for ($i = 0; $i < $dataSize; $i++) {
                $info_value = explode('=', $information[$i]);
                if ($info_value[0] == 'enc_response') {
                    $decrypted_response = decrypt(trim($info_value[1]), $working_key);
                }
            }

            $var = json_decode($decrypted_response, true);
            $data = $var['order_Status_List'];

            // Extract 'order_status_date_time' column
            $order_status_dates = array_column($data, 'order_status_date_time');

            // Convert dates to timestamps for sorting
            $order_status_timestamps = array_map('strtotime', $order_status_dates);

            // Sort data by 'order_status_date_time' in descending order
            array_multisort($order_status_timestamps, SORT_DESC, $data);
           
            
            // API call successful but some error
            if (!empty($var['error_desc']) || !empty($var['error_code'])) {
                $error_desc = $var['error_desc'];
                $updatequery = "update it_payment_gateway_hdfc set receive_response='$response',status='$error_desc',is_sent=1, updatetime = now() where id=$inv->id ";
                $updatedresponse = $db->execUpdate($updatequery);
            }

            // We will consider only zeroth index as it is sorted descending by order_status_date_time
            if (isset($data[0]['order_status'])) {
                $payment_status = $data[0]['order_status'];
            }

            if (isset($data[0]['order_amt'])) {
                $Amt_paid = $data[0]['order_amt'];
            }

            if (isset($data[0]['merchant_param1'])) {  // merchant_param1=Invoice no(eg. 242500001)
                $id = $data[0]['merchant_param1'];
            }


            if ($payment_status == "Awaited" || $payment_status == "Initiated" || $payment_status == "Shipped" || $payment_status == "Cancelled" ||
                    $payment_status == "Successful" || $payment_status == "Unsuccessful" || $payment_status == "failed" || $payment_status == "System refund" ||
                    $payment_status == "Fraud" || $payment_status == "Chargeback" || $payment_status == "Auto-Reversed" || $payment_status == "Auto-Cancelled" ||
                    $payment_status == "Aborted" || $payment_status == "Invalid" || $payment_status=="Timeout") {

                if ($payment_status == "Shipped" || $payment_status == "Successful") {
                    $updatequery = "update it_payment_gateway_hdfc set receive_response='$response',status='$payment_status',  amount_paid='$Amt_paid',is_sent=2, updatetime = now(), remark_text = 'Amount paid successfully for $id' where id=$inv->id";
                    $updatedresponse = $db->execUpdate($updatequery);
                    $query1 = "update it_codes set inactive=0, inactivated_by = '' , inactivating_reason = '', paymentlink='', inactive_dttm = now() where id =$inv->store_id";
                    $rowaffect = $db->execUpdate($query1);

                    $invoice_done = $db->fetchObject("select id,store_name from it_codes where id=$inv->store_id and is_natch_required=0 and store_type != 3");

                    if (isset($invoice_done) && $invoice_done->id) {
                        $inv_ups = "update it_sp_invoices set non_nach_p=2 where store_id=$invoice_done->id and invoice_no='$inv->invoice_nos'";
                        $inv_up = $db->execUpdate($inv_ups);
                    }
                }elseif($payment_status=="Timeout"){
                    $updatequ = "update it_payment_gateway_hdfc set receive_response='$response',status='$payment_status', updatetime = now() where id=$inv->id";
                    $updatedre = $db->execUpdate($updatequ);
                    $query4 = "update it_codes set inactivating_reason='Payment for your invoice timed out please reinitiate your payment.' where id =$inv->store_id";
                    $quu = $db->execUpdate($query4);
                } elseif ($payment_status == "failed" || $payment_status == "Unsuccessful" || $payment_status == "Aborted" || $payment_status == "Cancelled") {
                    $updatequ = "update it_payment_gateway_hdfc set receive_response='$response',status='$payment_status', updatetime = now() where id=$inv->id";
                    $updatedre = $db->execUpdate($updatequ);
                    $query4 = "update it_codes set inactivating_reason='Payment failed please try again later' where id =$inv->store_id";
                    $quu = $db->execUpdate($query4);
                } elseif ($payment_status == "Auto-Cancelled") {
                    $updatequ = "update it_payment_gateway_hdfc set receive_response='$response',status='$payment_status',is_sent=3, updatetime = now() where id=$inv->id";
                    $updatedre = $db->execUpdate($updatequ);
                    $query4 = "update it_codes set inactivating_reason='Transaction has not confirmed within 12 days hence auto cancelled by system' where id =$inv->store_id";
                    $quu = $db->execUpdate($query4);
                } elseif ($payment_status == "Awaited" || $payment_status == "Initiated") {
                    $updatequ = "update it_payment_gateway_hdfc set receive_response='$response',status='$payment_status', updatetime = now() where id=$inv->id";
                    $updatedre = $db->execUpdate($updatequ);
                    $invoice_amount=$db->fetchObject("select invoice_amt from it_sp_invoices where store_id=$inv->store_id and invoice_no='$inv->invoice_nos'");
                    $query4 = "update it_codes set inactivating_reason='Payment for the following invoice no :  $inv->invoice_nos. of amount of : $invoice_amount is generated.' where id =$inv->store_id";
                    $quu = $db->execUpdate($query4);
                } elseif ($payment_status == "System refund" || $payment_status == "Chargeback" || $payment_status == "Auto-Reversed") {
                    $updatequery = "update it_payment_gateway_hdfc set receive_response='$response',status='$payment_status', updatetime = now() where id=$inv->id";
                    $updatedresponse = $db->execUpdate($updatequery);
                    $query3 = "update it_codes set inactivating_reason= 'Refunded by HDFC for various find of reversals by HDFC' , paymentlink='' where id =$inv->store_id";
                    $qu = $db->execUpdate($query3);
                } elseif ($payment_status == "Fraud" || $payment_status == "Invalid") {
                    $updatequery = "update it_payment_gateway_hdfc set receive_response='$response',is_sent=3,status='$payment_status', updatetime = now() where id=$inv->id";
                    $updatedresponse = $db->execUpdate($updatequery);
                    $query3 = "update it_codes set inactivating_reason= 'Transaction sent to HDFC with Invalid parameters OR tampering, hence could not be processed further ' , paymentlink='' where id =$inv->store_id";
                    $qu = $db->execUpdate($query3);
                }
            }
        }
    }
}

$starttime = "select now() as edtime";
print_r("<br>End time --> ");
print_r($db->fetchObject($starttime)->edtime);

