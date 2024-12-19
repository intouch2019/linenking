<?php

// For Live
ini_set('memory_limit', '-1');
ini_set('max_execution_time', '-1');
require_once("/var/www/html/linenking/it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once ("util/Crypto.php");


// For Test
//require_once("../../it_config.php");
//require_once("session_check.php");
//require_once "../lib/db/DBConn.php";
//require_once "lib/core/Constants.php";
//require_once ("util/Crypto.php");

$user = getCurrUser();
$db = new DBConn();

$reference_id = "";
$short_url = "";
$payment_status = "";
$inv = "";

$date = date("Y-m-d", strtotime('now'));
$date_arr = explode('-', $date);

if ($date_arr[1] < 4) {
    $date_arr[0] = $date_arr[0] - 1;
}



// (is_sent = 1) -> Payment link is sent to mail but payment is not received.
// (is_sent = 2) -> Payment Successful or Shipped
// (is_sent = 3) -> Payment Unsuccessful and other status
// (is_sent = 4) -> If Invoice no sent is not same as invoice no received while creating the payment link

// non_nach_p=0 -> Default
// non_nach_p=1 -> When Payment link is created 
// non_nach_p=2 -> When Payment is received


// Live
$squery = "select i.id,i.invoice_no,i.invoice_amt,i.store_id,c.phone,c.email,c.store_name,inactive from it_sp_invoices i ,it_codes c where i.store_id=c.id and c.is_natch_required=0 and i.is_procsdForRetail = 0 and i.non_nach_p=0 and c.inactive=0 and c.store_type !=3 and invoice_dt >= '$date_arr[0]-04-01 00:00:00' order by i.invoice_no asc ";
//print_r($squery);exit();
// Test 
//$squery = "select i.id,i.invoice_no,i.invoice_amt,i.store_id,c.phone,c.email,c.store_name,inactive from it_sp_invoices i ,it_codes c where i.store_id=c.id and c.is_natch_required=0 and i.is_procsdForRetail = 0 and i.non_nach_p=0 and c.inactive=0 and c.store_type !=3 and invoice_dt >= '2024-04-01 00:00:00' order by i.invoice_no asc";
$storeobjs = $db->fetchObjectArray($squery);

if (isset($storeobjs)) {
    foreach ($storeobjs as $inv) {
        $que = "select id,store_name,inactive from it_codes where id = $inv->store_id";
        $obj = $db->fetchObject($que);

        if ($obj->inactive == '0') { // active stores
            $query1 = "select id, invoice_nos, status, is_sent from it_payment_gateway_hdfc where invoice_nos='$inv->invoice_no'";
            $checkIfInvAvailableInPaymentGatewayTable = $db->fetchObject($query1);

            $insert_id = "";
            if (isset($checkIfInvAvailableInPaymentGatewayTable) && $checkIfInvAvailableInPaymentGatewayTable != null) {
                $insert_id = $checkIfInvAvailableInPaymentGatewayTable->id;
            } else {
                $insqry = "insert into it_payment_gateway_hdfc set store_id  = $inv->store_id,store_name ='$inv->store_name' ,remark_text='Paid full payment of Invoice No :- $inv->invoice_no', invoice_nos ='$inv->invoice_no', phone='$inv->phone', email ='$inv->email', createtime = now() ";
                $insert_id = $db->execInsert($insqry);
            }

//                    print_r($insert_id);exit();

            if (isset($insert_id) && !empty($insert_id) && $insert_id != null) {
                $mrp = $inv->invoice_amt;

// Test Credentials Fashionking
//            $working_key = 'C31BB3656BDB9000D656BDA362A9F258';
//            $access_code = 'ATBH45KL25CL13HBLC';
//            $merchant_id = '3142863';

                
                
// // Live Credentials Fashionking
            $working_key = 'B2D18CFF8EFD4FFF3EAD0A1D37047B50';
            $access_code = 'AVYK24LJ63AQ54KYQA';
            $merchant_id = '3876548';

            $phone = trim($inv->phone);
            $email = trim($inv->email);
            $store = str_replace(' ', '_', $obj->store_name);
                $merchant_json_data = array(
                    "customer_name" => $inv->store_name,
                    "bill_delivery_type" => "BOTH",
                    "customer_mobile_no" => $phone,
                    "customer_email_id" => $email,
                    "customer_email_subject" => "Fashionking Brands PVT. LTD.",
                    "invoice_description" => "Invoice for the amount of Rs " . $mrp . " INR is generated for your order",
                    "currency" => "INR",
                    "valid_for" => 3,
                    "valid_type" => "days",
                    "amount" => $mrp,
                    "merchant_reference_no" => $inv->invoice_no,
                    "merchant_reference_no2" => $store,
                    "terms_and_conditions" => "terms and condition",
                    "sms_content" => "Please make payment for Rs " . $mrp . " INR # Invoice_ID for Invoice_Currency Invoice_Amount or pay online at Pay_Link.",
                );

                $merchant_data = json_encode($merchant_json_data);
                $encrypted_data = encrypt($merchant_data, $working_key);
                $string_JSON = "enc_request=$encrypted_data&access_code=$access_code&request_type=JSON&response_type=JSON&command=generateQuickInvoice&version=1.2";

                $curl = curl_init();
                curl_setopt_array($curl, array(
                    // Test URL
//                    CURLOPT_URL => "https://apitest.ccavenue.com/apis/servlet/DoWebTrans?",
                   
                    // Live URL
                    CURLOPT_URL =>"https://api.ccavenue.com/apis/servlet/DoWebTrans?",
                  
                    CURLOPT_RETURNTRANSFER => 1,
                    CURLOPT_VERBOSE => 1,
                    CURLOPT_SSL_VERIFYPEER => 0,
                    CURLOPT_POST => 1,
                    CURLOPT_POSTFIELDS => $string_JSON,
                    CURLOPT_RETURNTRANSFER => true
                ));

                $response = curl_exec($curl);
                curl_close($curl);
//                print_r($response);exit();
                $information = explode('&', $response);
                $dataSize = sizeof($information);
                $api_status = explode('=', $information[0])[1];
//                print_r($api_status);exit();

                if ($api_status == 1) {//API call failed.
                    $updatequery = "update it_payment_gateway_hdfc set send_response='$response',status='API call was unsuccessful',is_sent=3, updatetime = now() where id=$insert_id ";
                    $updatedresponse = $db->execUpdate($updatequery);
//                    print_r($updatequery);exit();
                } else {
                    for ($i = 0; $i < $dataSize; $i++) {
                        $info_value = explode('=', $information[$i]);
                        if ($info_value[0] == 'enc_response') {
                            $decrypted_response = decrypt(trim($info_value[1]), $working_key);
                        }
                    }

                    $var = json_decode($decrypted_response, true);

                    if (isset($var['invoice_id'])) {       // Unique id generated by hdfc payment gateway
                        $reference_id = $var['invoice_id'];
                    }

                    if (isset($var['tiny_url'])) {
                        $short_url = $var['tiny_url'];
                    }
                    if (isset($var['invoice_status'])) {
                        $payment_status = $var['invoice_status'];
                    }
                    if (isset($var['merchant_reference_no'])) {
                        $merchant_reference_no = $var['merchant_reference_no'];
                    }


                    if ($merchant_reference_no == $inv->invoice_no) {// -----------Checking if supplied order_id is same in response
                        if ($payment_status == 0) { //-------------payment created
                            $updatequery = "update it_payment_gateway_hdfc set reference_id='$reference_id', send_response='$response',paymenturl='$short_url', status='Payment link created', invoice_amt=$mrp, is_sent=1, updatetime = now() where id=$insert_id ";
                            $updatedresponse = $db->execUpdate($updatequery);

                            $query = "update it_codes set inactive=1, inactivated_by =312, paymentlink='$short_url', inactivating_reason='Payment for the following invoice no :  $inv->invoice_no. of amount of : $mrp is generated.', inactive_dttm = now() where id = $inv->store_id";
                            $rowaffected = $db->execUpdate($query);

                            $inv_ups = "update it_sp_invoices set non_nach_p=1 where store_id=$inv->store_id and invoice_no='$inv->invoice_no'";
                            $inv_up = $db->execUpdate($inv_ups);
                        } else {
                            $updatequ = "update it_payment_gateway_hdfc set reference_id='$reference_id', send_response='$response', status='$payment_status', is_sent=3, updatetime = now() where id=$insert_id";
                            $updatedresp = $db->execUpdate($updatequ);
                        }
                    } else {
                        $updatequery = "update it_payment_gateway_hdfc set reference_id='$reference_id', send_response='$response',paymenturl='',status='Invoice no send and received is not same',is_sent=4, updatetime = now() where id=$insert_id ";
                        $updatedresponse = $db->execUpdate($updatequery);
                    }
                }
            } else {
                continue;
            }
        }
    }
}
?>
