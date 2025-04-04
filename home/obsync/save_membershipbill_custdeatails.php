<?php

include "checkAccess.php";//live
require_once "../../it_config.php";
require_once "lib/logger/clsLogger.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/sms/SMS_Membership.php";

extract($_POST);
$db = new DBConn();
$member_mobno = $_POST['member_mobno'];//live
$member_name = $_POST['member_name'];
$membership_enroll_amt = $_POST['membership_enroll_amt'];
$member_already_register = $_POST['member_already_register'];
$store_id = $gCodeId; 
//print_r($_POST);
//echo '<br>';
//$member_mobno = "9881677716"; //Test
//$member_name = "Shubham";
//$membership_enroll_amt = "100";
//$member_already_register = "0";
//$store_id = 161;

$logger = new clsLogger();
$saveresult = array();

if ($member_mobno === null || $member_mobno === '') {
    $saveresult = array(
        "status" => "Error",
        "errordesc" => "Mobile number is required"
    );
    print_r($saveresult);
    return;
}

if (!preg_match('/^\d{10}$/', $member_mobno)) {
    $saveresult = array(
        "status" => "Error",
        "errordesc" => "Invalid mobile number. It must be exactly 10 digits."
    );
    print_r($saveresult);
    return;
}

if ($membership_enroll_amt === null || $membership_enroll_amt === '') {
    $saveresult = array(
        "status" => "Error",
        "errordesc" => "Membership enrollment amount is required."
    );
    print_r($saveresult);
    return;
}

if ($member_already_register === null || $member_already_register === '') {
    $saveresult = array(
        "status" => "Error",
        "errordesc" => "Member registration status is required."
    );
    print_r($saveresult);
    return;
}

if ($store_id === null || $store_id === '') {
    $saveresult = array(
        "status" => "Error",
        "errordesc" => "Store ID is required."
    );
    print_r($saveresult);
    return;
}

$member_mobno = isset($member_mobno) ? $member_mobno : "";
$member_name = isset($member_name) ? $member_name : "";
$membership_enroll_amt = isset($membership_enroll_amt) ? $membership_enroll_amt : "0.00";
$member_already_register = isset($member_already_register) ? $member_already_register : "";

if ($member_already_register == 0) {//not already register
    $checkmemberstatus = "select id from membership_customer_details where member_mobno='$member_mobno' and is_membership_active=1 and membership_enroll_date < now() and membership_expiry_date > now()";
    $checkenrollmentobj = $db->fetchObject($checkmemberstatus);
    if (isset($checkenrollmentobj)) {//alreday register
        $saveresult = array(
            "status" => "Error",
            "errordesc" => "This Member is already register, wrong field in request object."
        );
        print_r($saveresult);
        return;
    } else {//not register
        $unique_membership_number = generateUniqueMembershipNumber($store_id,$member_mobno);
        $membership_expiry_date = date('Y-m-d H:i:s', strtotime('+1 year')); //its fixed 1 year no change in future
        $insertmemebrdetail = "insert into membership_customer_details set membership_number = '$unique_membership_number', member_mobno = '$member_mobno', member_name = '$member_name', membership_enroll_amt = '$membership_enroll_amt', membership_enroll_date = now(), membership_expiry_date = '$membership_expiry_date', member_enroll_bystore = '$store_id', member_last_purchase = now(), is_membership_active = 1, update_date = now()";
        $resultinsert = $db->execInsert($insertmemebrdetail);

        //send message for sucessfully registration done.
        if ($resultinsert) {
            send_registrationmsg($member_mobno, $membership_expiry_date);
        }

        //Logs of insertion
        $seleforlogs = "SELECT * FROM membership_customer_details WHERE member_mobno = '$member_mobno' AND is_membership_active = 1";
        $selectlogsobj = $db->fetchObject($seleforlogs);

        if ($selectlogsobj) {

            $membership_number = isset($selectlogsobj->membership_number) ? $selectlogsobj->membership_number : 0;
            $member_mobno = isset($selectlogsobj->member_mobno) ? $selectlogsobj->member_mobno : '';
            $member_name = isset($selectlogsobj->member_name) ? $selectlogsobj->member_name : '';
            $membership_enroll_amt = isset($selectlogsobj->membership_enroll_amt) ? $selectlogsobj->membership_enroll_amt : 0;
            $membership_enroll_date = isset($selectlogsobj->membership_enroll_date) ? $selectlogsobj->membership_enroll_date : '0000-00-00';
            $membership_expiry_date = isset($selectlogsobj->membership_expiry_date) ? $selectlogsobj->membership_expiry_date : '0000-00-00';
            $member_enroll_bystore = isset($selectlogsobj->member_enroll_bystore) ? $selectlogsobj->member_enroll_bystore : '';
            $member_last_purchase = isset($selectlogsobj->member_last_purchase) ? $selectlogsobj->member_last_purchase : '0000-00-00 00:00:00';
            $is_membership_active = isset($selectlogsobj->is_membership_active) ? $selectlogsobj->is_membership_active : 0;
            $query_executedby = isset($store_id) ? $store_id : 0;
            $query_type = Membership_querytype::Insert;

            $inserlogs = "insert into membership_customer_details_logs SET membership_number = '$membership_number', member_mobno = '$member_mobno', member_name = '$member_name', membership_enroll_amt = '$membership_enroll_amt', membership_enroll_date = '$membership_enroll_date', membership_expiry_date = '$membership_expiry_date', member_enroll_bystore = '$member_enroll_bystore', member_last_purchase = '$member_last_purchase', is_membership_active = '$is_membership_active', query_type = '$query_type', query_executedby = '$query_executedby', query_exe_time = now(), update_date = now()";
            $insertresult = $db->execInsert($inserlogs);
        }


        // Execute the query and check if successful
        if ($resultinsert) {
            $saveresult = array(
                "status" => "Sucess",
                "errordesc" => "insert"
            );
            print_r($saveresult);
            return;
        } else {
            $saveresult = array(
                "status" => "Error",
                "errordesc" => "Error in query execution"
            );
            print_r($saveresult);
            return;
        }
    }
} else if ($member_already_register == 1) {//Already Register
    $checkmemberstatus = "select id from membership_customer_details where member_mobno='$member_mobno' and is_membership_active=1 and membership_enroll_date < now() and membership_expiry_date > now()";
    $checkenrollmentobj = $db->fetchObject($checkmemberstatus);
    if (isset($checkenrollmentobj)) {//alreday register
                $updatelastpurchase = "update membership_customer_details set member_last_purchase=now(),update_date=now() where member_mobno='$member_mobno' and is_membership_active=1";
                $updateresult = $db->execUpdate($updatelastpurchase);

                $seleforlogs = "SELECT * FROM membership_customer_details WHERE member_mobno = '$member_mobno' AND is_membership_active = 1";
                $selectlogsobj = $db->fetchObject($seleforlogs);

                if ($selectlogsobj) {

                    $membership_number = isset($selectlogsobj->membership_number) ? $selectlogsobj->membership_number : 0;
                    $member_mobno = isset($selectlogsobj->member_mobno) ? $selectlogsobj->member_mobno : '';
                    $member_name = isset($selectlogsobj->member_name) ? $selectlogsobj->member_name : '';
                    $membership_enroll_amt = isset($selectlogsobj->membership_enroll_amt) ? $selectlogsobj->membership_enroll_amt : 0;
                    $membership_enroll_date = isset($selectlogsobj->membership_enroll_date) ? $selectlogsobj->membership_enroll_date : '0000-00-00';
                    $membership_expiry_date = isset($selectlogsobj->membership_expiry_date) ? $selectlogsobj->membership_expiry_date : '0000-00-00';
                    $member_enroll_bystore = isset($selectlogsobj->member_enroll_bystore) ? $selectlogsobj->member_enroll_bystore : '';
                    $member_last_purchase = isset($selectlogsobj->member_last_purchase) ? $selectlogsobj->member_last_purchase : '0000-00-00 00:00:00';
                    $is_membership_active = isset($selectlogsobj->is_membership_active) ? $selectlogsobj->is_membership_active : 0;
                    $query_executedby = isset($store_id) ? $store_id : 0;
                    $query_type = Membership_querytype::Update;

                    $inserlogs = "insert into membership_customer_details_logs SET membership_number = '$membership_number', member_mobno = '$member_mobno', member_name = '$member_name', membership_enroll_amt = '$membership_enroll_amt', membership_enroll_date = '$membership_enroll_date', membership_expiry_date = '$membership_expiry_date', member_enroll_bystore = '$member_enroll_bystore', member_last_purchase = '$member_last_purchase', is_membership_active = '$is_membership_active', query_type = '$query_type', query_executedby = '$query_executedby', query_exe_time = now(), update_date = now()";
                    $insertresult = $db->execInsert($inserlogs);
                }

                if ($updateresult > 0) {
                    $saveresult = array(
                        "status" => "Sucess",
                        "errordesc" => "update"
                    );
                    print_r($saveresult);
                    return;
                } else {
                    $saveresult = array(
                        "status" => "Error",
                        "errordesc" => "Error in update last purchase details."
                    );
                    print_r($saveresult);
                    return;
                }
    } else {
        $saveresult = array(
            "status" => "Error",
            "errordesc" => "This Member is not register, wrong field in request object."
        );
        print_r($saveresult);
        return;
    }
} else {
    $saveresult = array(
        "status" => "Error",
        "errordesc" => "Invalid Member registration status found."
    );
    print_r($saveresult);
    return;
}

function generateUniqueMembershipNumber($storeid,$member_mobno) {
  
    $datetime = date('YmdHis'); 
    return $storeid . $member_mobno . $datetime;
}

function send_registrationmsg($mobile_no, $exp_date) {

    $message = "Dear Linenking Member, You are successfully registered for the Linenking Membership Program, this membership is valid till " . $exp_date . "";

    $smsHelper = new SMS_Membership();
    $errormsg = $smsHelper->sendSMS($mobile_no, $message);
    
      // Decode JSON into an associative array
    $responseArray = json_decode($errormsg, true);
    
     if (isset($responseArray['message']) && stripos($responseArray['message'], 'message Submitted successfully') !== false) {
        // Success
        return 1;
    } else {
        return 0;
    }
}
