<?php
ini_set('memory_limit', '-1');
ini_set('max_execution_time', '-1');
require_once("/var/www/html/linenking/it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/serverChanges/clsServerChanges.php";


$user = getCurrUser();
$db = new DBConn();

$id = "";
$reference_id = "";
$ref_id = "";
$pay_url = "";
$status = "";
$pagecode = "";
$inv_nos = "";
$inv_ids = "";
$short_url = "";
$status = "";
$errors = array();
$inv = "";

$date = date("Y-m-d", strtotime('now'));
$date_arr = explode('-', $date);
if ($date_arr[1] < 4) {
    $date_arr[0] = $date_arr[0] - 1;
}



$squery = "select i.id,i.invoice_no,i.invoice_amt,i.store_id,c.phone,c.email,c.store_name,inactive from it_sp_invoices i ,it_codes c where i.store_id=c.id and c.is_natch_required=0 and i.is_procsdForRetail = 0 and i.non_nach_p=0 and c.inactive=0 and c.store_type !=3  and invoice_dt >= '$date_arr[0]-04-01 00:00:00' order by i.invoice_no asc ";

$storeobjs = $db->fetchObjectArray($squery);

if (isset($storeobjs)) {

    foreach ($storeobjs as $inv) {

        $que = "select id,store_name,inactive from it_codes where id = $inv->store_id";
        $obj = $db->fetchObject($que);

        if ($obj->inactive == '0') {

            $insqry = "insert into it_payment_gateway set store_id  = $inv->store_id,store_name ='$inv->store_name' ,Remark_text='Paid full payment of Invoice No :- $inv->invoice_no', invoice_nos ='$inv->invoice_no', phone='$inv->phone', email ='$inv->email', createtime = now() ";
            $insert_id = $db->execInsert($insqry);

            if (isset($insert_id) && !empty($insert_id) && $insert_id != null) {

                $mrp = $inv->invoice_amt;
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
  "expire_by": 1691097057,
   "reference_id": "' . $inv->invoice_no . '",
  "description": "Payment for the following invoice no :'.$inv->invoice_no.' of amount of : '.$mrp.'",
  "customer": {
    "name": "' . $inv->store_name . '",
    "contact": "+91'.$inv->phone.'",
    "email": "'.$inv->email.'"
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
              
                print_r($response);
                
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

           if(isset($error)) {
            foreach ($error as $key => $value) {
                if ($key == "description") {

                    $descc = $value;
                }
            }
           }


                if ($status == "created") {

                    $updatequery = "update it_payment_gateway set Invoice_amtid='$id', reference_id='$reference_id', Send_response='$response',Paymenturl='$short_url',status='$status',invoice_amt=$mrp,is_sent=1, updatetime = now() where id=$insert_id ";

                    $updatedresponse = $db->execUpdate($updatequery);

                    $query = "update it_codes set inactive=1, inactivated_by = $inv->store_id ,paymentlink='$short_url',inactivating_reason='Payment for the following invoice no :  $inv->invoice_no. of amount of : $mrp',  inactive_dttm = now() where id = $inv->store_id";

                    $rowaffected = $db->execUpdate($query);

                    $inv_ups = "update it_sp_invoices set non_nach_p=1 where store_id=$inv->store_id and invoice_no=$inv->invoice_no";
                    $inv_up = $db->execUpdate($inv_ups);
                } else {

                    $updatequ = "update it_payment_gateway set reference_id='$reference_id', Send_response='$response',status='$status',is_sent=3, updatetime = now() where id=$insert_id";

                    $updatedresp = $db->execUpdate($updatequ);
                }
            }
            curl_close($curl);
        } else {
            continue;
        }
    }
}