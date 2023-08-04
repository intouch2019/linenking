<?php

require_once("../../it_config.php");
require_once("session_check.php");
require_once "../lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/serverChanges/clsServerChanges.php";
require_once 'lib/users/clsUsers.php';
require_once "lib/core/strutil.php";

extract($_GET);

$user = getCurrUser();

$db = new DBConn();
$userpage = new clsUsers();
$id = "";
$ref_id = "";
$pay_url = "";
$status = "";
$pagecode = "";
$inv_nos = "";
$inv_ids = "";
$errors = array();
$inv = "";

if (!isset($storeid) || trim($storeid) == "") {
    $errors['storeid'] = 'Please select a  "Store Name"';
}
if (!isset($Mobile_no) || trim($Mobile_no) == "") {
    $errors['Mobile_no'] = 'Please select a value for "Mobile No"';
}
if (!isset($Email_id) || trim($Email_id) == "") {
    $errors['Email_id'] = 'Please select a value for "Email Id"';
}
if (!isset($invoiceid) || trim($invoiceid) == "") {
    $errors['invoiceid'] = 'Please add a "Invoive Number"';
}
if (!isset($mrp) || trim($mrp) == "") {
    $errors['mrp'] = 'Please add a value for "Invoice Amount"';
} else if (!ctype_digit(strval($mrp))) {
    $errors['mrp'] = 'Please add a number for "Invoice Amount"';
}
if (!isset($description) || trim($description) == "") {
    $description = "payment for the following invoice no :  $invoiceid. of amount : $mrp";
} else {

    $description;
}

$serverCh = new clsServerChanges();

if (count($errors) == 0) {
    try {

        $currentDate = time();
$threeMonthsLater = strtotime("+3 months", $currentDate);
echo "$currentDate: " . $threeMonthsLater;
        

        $query = "select id,store_name from it_codes where id = $storeid";
        $obj = $db->fetchObject($query);

        $query = "insert into it_payment_gateway set store_id = $obj->id ,store_name='$obj->store_name' , phone = '$Mobile_no' ,  email = '$Email_id' , invoice_amt = $mrp ,Remark_text ='$description', invoice_nos = '$invoiceid' , createtime = now() ";

        $inserted_id = $db->execInsert($query);

        if (isset($inserted_id) && !empty($inserted_id) && $inserted_id != null) {

            $mrp2 = $mrp . '00';

            $username = 'rzp_live_pbnA6VXqiDfrQ6';
            $password = 'TI3RGPLdTX7Tz0mvsPXzV21N';
            $credentials = base64_encode($username . ':' . $password);
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.razorpay.com/v1/payment_links/',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => '{
  "amount":' . $mrp2 . ',
  "currency": "INR",
  "accept_partial": false,
  "first_min_partial_amount": ' . $mrp2 . ',
  "expire_by":'.$threeMonthsLater.',
  "reference_id": "' . $invoiceid . '",
  "description": " ' . $description . '",
  "customer": {
    "name": "' . $obj->store_name . '",
    "contact": "+91' . $Mobile_no . '",
    "email": "' . $Email_id . '"
  },
  "notify": {
    "sms": true,
    "email": true
  },
  "reminder_enable": true,
  "notes": {
    "policy_name": "Invoice Amount"
  },
  "callback_url": "",
  "callback_method": "get"
}',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'Authorization: Basic ' . $credentials
                ),
            ));

            $response = curl_exec($curl);

            $var = json_decode($response, true);

            if (isset($var['id'])) {
                $id = $var['id'];
            }

            if (isset($var['reference_id'])) {
                $reference_id = $var['reference_id'];
            }

            if (isset($var['short_url'])) {
                $short_url = $var['short_url'];
            }
            if (isset($var['status'])) {
                $status = $var['status'];
            }
            if (isset($var['error'])) {
                $error = $var['error'];
            }

            if(isset($error)){
            foreach ($error as $key => $value) {
                if ($key == "description") {

                    $descc = $value;
                }
            }
            }


            if ($status == "created") {//-------------payment created
                $updatequery = "update it_payment_gateway set Invoice_amtid='$id', reference_id='$reference_id', Send_response='$response',Paymenturl='$short_url',status='$status',is_sent=1, updatetime = now() where id=$inserted_id ";
                $updatedresponse = $db->execUpdate($updatequery);

                $query = "update it_codes set inactive=1, inactivated_by = $storeid , inactivating_reason = '$description',paymentlink='$short_url',  inactive_dttm = now() where id =$storeid";
                $rowaffected = $db->execUpdate($query);

                $success = "Payment request sent to store - .'$obj->store_name";
            } else {//---------------------payment not created
                $updatequery = "update it_payment_gateway set Send_response='$response',status='$status',is_sent=3, updatetime = now() where id=$inserted_id ";
                $updatedresponse = $db->execUpdate($updatequery);
                $errors['Payment'] = "$descc.Please try again";
            }
            curl_close($curl);
        }

        unset($_SESSION['form_post']);
    } catch (Exception $xcp) {
        $clsLogger = new clsLogger();
        $clsLogger->logError("Failed to add batch:" . $xcp->getMessage());
        $errors['status'] = "There was a problem processing your request. Please try again later";
    }
}
if (count($errors) > 0) {
    $_SESSION['form_errors'] = $errors;
    $redirect = "gateway";
} else {
    $_SESSION['form_success'] = $success;
    $redirect = "gateway";
}
session_write_close();
header("Location: " . DEF_SITEURL . "admin/payment/$redirect");
exit;
?>