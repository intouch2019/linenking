<?php

require_once("../../it_config.php");
//require_once("session_check.php");
require_once "../lib/db/DBConn.php";
require_once "lib/core/Constants.php";
//require_once 'lib/users/clsUsers.php';
require_once "lib/core/strutil.php";
require_once ("util/Crypto.php");
require_once "lib/logger/clsLogger.php";


//print_r($_GET['invoice']);exit();


$inv = $_GET['invoice'];



//$user = getCurrUser();

$db = new DBConn();
$user = $db->fetchObject("select id from it_codes where id = 1");


//$userpage = new clsUsers();
$ref_id = "";
$pay_url = "";
$payment_status = "";
$decrypted_response = "";
$pagecode = "";
$inv_nos = "";
$inv_ids = "";
$errors = array();

$reference_no = "";
$invObj = $db->fetchObject("select store_id, invoice_no , invoice_amt as mrp from it_invoices where invoice_no = '$inv'");
$invoiceid=$invObj->invoice_no;
$mrp=$invObj->mrp;
$storeid=$invObj->store_id;



    $description = "Payment for the following invoice no :  $invoiceid. of amount : $mrp is generated.";

   
// $invoiceids = "select invoice_nos from it_payment_gateway_hdfc where invoice_nos = '$invoiceid'";
// $obj = $db->fetchObject($invoiceids);
// 
//  if($obj!="" || $obj!=null || isset($obj)){
//    $errors['duplicate_invoiceno'] = 'Payment link is already generated for invoice '.$invoiceid.'';
// }

if (count($errors) == 0) {
    try {


        $query = "select id,store_name,phone,email,inactive from it_codes where id = $storeid";
        $obj = $db->fetchObject($query);
         
        $Mobile_no=$obj->phone;
        $Email_id=$obj->email;
        $Mobile_no= trim($Mobile_no);
        $Email_id = trim($Email_id);
        $query = "insert into it_payment_gateway_hdfc set store_id = $obj->id ,store_name='$obj->store_name' , phone = '$Mobile_no' ,  email = '$Email_id' , invoice_amt = $mrp ,remark_text ='$description', invoice_nos = '$invoiceid' , createtime = now() ";
//print_r($query);exit();
        $inserted_id = $db->execInsert($query);

//            

        if (isset($inserted_id) && !empty($inserted_id) && $inserted_id != null) {

            //test credentials for hdfc
//            $working_key = 'C31BB3656BDB9000D656BDA362A9F258';
//            $access_code = 'ATBH45KL25CL13HBLC';
//            $merchant_id = '3142863';
            
           //Live credentials for hdfc
            $working_key = '17D3F7508797180F33196F48E32FD021';
            $access_code = 'AVKX40LL73BL72XKLB';
            $merchant_id = '4020205';
            
             $store = str_replace(' ', '_', $obj->store_name);
            $merchant_json_data = array(
                "customer_name" => $obj->store_name,
                "bill_delivery_type" => "BOTH",
                "customer_mobile_no" => $Mobile_no,
                "customer_email_id" => $Email_id,
                "customer_email_subject" => "Fashionking Brands PVT. LTD.",
                "invoice_description" => "Invoice for the amount of Rs " . $mrp . " INR is generated for your order",
                "currency" => "INR",
                "valid_for" => 3,
                "valid_type" => "days",
                "amount" => $mrp,
                "merchant_reference_no" => $invoiceid,
                 "merchant_reference_no2" => $store,
                "terms_and_conditions" => "terms and condition",
                "sms_content" => "Please make payment for Rs " . $mrp . " INR # Invoice_ID for Invoice_Currency Invoice_Amount or pay online at Pay_Link.",
            );

            $merchant_data = json_encode($merchant_json_data);

            $encrypted_data = encrypt($merchant_data, $working_key);
//echo 'data ' . ($encrypted_data) .'<br>';
////print_r($working_key);
//exit();
            $string_JSON = "enc_request=$encrypted_data&access_code=$access_code&request_type=JSON&response_type=JSON&command=generateQuickInvoice&version=1.2";
            $updateqry = "update it_payment_gateway_hdfc set sent_payload='$string_JSON', updatetime = now() where id=$inserted_id ";
            $db->execUpdate($updateqry);
//            print_r($string_JSON);            exit();
            $curl = curl_init();
            curl_setopt_array($curl, array(
                // Test URL
//                CURLOPT_URL => "https://apitest.ccavenue.com/apis/servlet/DoWebTrans?",
                
                //Live URL
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

            $information = explode('&', $response);
//                echo '<pre>';
//                print_r($information);
//                echo '<pre>';
//                exit();
            $dataSize = sizeof($information);
            $api_status = explode('=', $information[0])[1];
//                echo '<pre>';
//                print_r($api_status);
//                echo '<pre>';
//                exit();
            if ($api_status == 1) {//API call failed.
                $updatequery = "update it_payment_gateway_hdfc set Send_response='$response',status='API call was unsuccessfull',is_sent=3, updatetime = now() where id=$inserted_id ";
                $updatedresponse = $db->execUpdate($updatequery);
                $errors['status'] = "Cannot generate payment request. Please try again later";
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
                if(isset($var['merchant_reference_no'])){
                    $merchant_reference_no=$var['merchant_reference_no'];
                }
                if(isset($var['error_desc'])){
                  $error_desc=$var['error_desc'];
                }
                
                if(empty($error_desc)){
                if ($merchant_reference_no == $invoiceid) {
                    if ($payment_status == 0) {//-------------payment created
                        $updatequery = "update it_payment_gateway_hdfc set  reference_id='$reference_id', Send_response='$response',Paymenturl='$short_url',status='Invoice generated successfully',is_sent=1, updatetime = now() where id=$inserted_id ";
                        $updatedresponse = $db->execUpdate($updatequery);

                        $query = "update it_codes set inactive=1, inactivated_by = '$user->id' , inactivating_reason = '$description',paymentlink='$short_url',  inactive_dttm = now() where id =$storeid";
                        $rowaffected = $db->execUpdate($query);

                        $success = "Payment request sent to store - ".$obj->store_name;

                        $reasonQuery = "insert into store_disable_reason set store_id = $storeid, reason = 'Manual Payment Link - $description'";
                        print_r($reasonQuery);//exit();
                        $disableReason = $db->execInsert($reasonQuery);
                        $clsLogger = new clsLogger();
//                        $store = getCurrUser();
                        $ipaddr = $_SERVER['REMOTE_ADDR'];
                        $pg_name = __FILE__;
                        $clsLogger->it_codes_logInfo($query, $store->id, $pg_name, $ipaddr);
                        
                    }
                }else{
                     $updatequery = "update it_payment_gateway_hdfc set  reference_id='$reference_id', Send_response='$response',Paymenturl='',status='Invoice no send and received is not same',is_sent=4, updatetime = now() where id=$inserted_id ";
                     $updatedresponse = $db->execUpdate($updatequery);
                      $errors['status'] = "Invoice no send and received is not same.";
                }
                }else {
                    $updatequery = "update it_payment_gateway_hdfc set  reference_id='$reference_id', Send_response='$response',Paymenturl='',status='$error_desc',is_sent=3, updatetime = now() where id=$inserted_id ";
                    $updatedresponse = $db->execUpdate($updatequery);
                     $errors['status'] = $error_desc;
                }
            }

            curl_close($curl);
        }

        unset($_SESSION['form_post']);
    } catch (Exception $xcp) {
        $clsLogger = new clsLogger();
        $clsLogger->logError("Failed to create payment: " . $xcp->getMessage());
        $errors['status'] = "There was a problem processing your request. Please try again later";
    }
}
if (count($errors) > 0) {
    $_SESSION['form_errors'] = $errors;
    $redirect = "gateway/hdfc";
} else {
    $_SESSION['form_success'] = $success;
    $redirect = "gateway/hdfc";
}
session_write_close();
//header("Location: " . DEF_SITEURL . "admin/payment/$redirect");
//exit;
?>

