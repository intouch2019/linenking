<?php

include "checkAccess.php";//live
require_once "../../it_config.php";
require_once "lib/logger/clsLogger.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/sms/SMS_Membership.php"; 

extract($_POST);
$db = new DBConn();
$mobile_no = $_POST['mobile_no'];//live
$netbill_value = $_POST['netbill_amt'];//live
$store_id = $gCodeId; //live

//$mobile_no = "9881677716"; //test
//$netbill_value = 5999; //test
//$store_id = 161; //test
$logger = new clsLogger();
$result = array();

if (empty($mobile_no)) {
    $result = array(
        "status" => "Error",
        "errordesc" => "Mobile number is required"
    );
    print_r($result);
    return;
}

if (!preg_match('/^\d{10}$/', $mobile_no)) {
    $result = array(
        "status" => "Error",
        "errordesc" => "Invalid mobile number. It must be exactly 10 digits."
    );
    print_r($result);
    return;
}

if (!empty($mobile_no) && !empty($store_id) && intval($store_id) !== 0) {
           //check netbill_amt
            $check_curr_scheme = "select mm.enrollment_fee, mm.min_amount_to_applied_scheme, mm.discount_value from membership_scheme_masters mm join storewise_membership_schemes ss on mm.id = ss.scheme_id where mm.is_scheme_active = 1 and mm.is_scheme_delete = 0 and mm.start_date < now() and mm.end_date > now() and ss.is_data_deleted = 0 and ss.store_id = $store_id and mm.min_amount_to_applied_scheme <= $netbill_value order by mm.min_amount_to_applied_scheme desc limit 1";
        $curr_scheme_obj = $db->fetchObject($check_curr_scheme);


    if (isset($curr_scheme_obj)) {

        if ($curr_scheme_obj->min_amount_to_applied_scheme > $netbill_value) {
            $result = array(
                "status" => "Error",
                "errordesc" => "The bill's net value is lower. The scheme has not been applied to this bill."
            );
            print_r($result);
            return;
        }



        $checkenrollmentstatus = "select id from membership_customer_details where member_mobno=$mobile_no and is_membership_active=1 and membership_enroll_date < now() and membership_expiry_date > now()";
        $checkenrollmentobj = $db->fetchObject($checkenrollmentstatus);
        if (isset($checkenrollmentobj)) {//Already register user
           
             

            //now the user is already registerd and not used scheme discount in last 24 hours
            $otp_send = send_avilofferotp($mobile_no); //send OTP to mobile number
            if ($otp_send != 0) {
                //Successfully send OTP.
                $result = array(
                    "mobile_number" => "$mobile_no",
                    "registration_status" => "1", //already register
                    "OTP_sendstatus" => "1", //Success
                    "status" => "Success",
                    "errordesc" => ""
                );
                print_r($result);
                return;
            } else {
                //Fail to send OTP.
                $result = array(
                    "status" => "Error",
                    "errordesc" => "Error in OTP send to mobile number."
                );
                print_r($result);
                return;
            }
        } else {
            //unregisterd user.
            $otp_send = send_otpforenrollment($mobile_no); //send OTP to mobile number

            if ($otp_send != 0) {
                //Successfully send OTP.
                $result = array(
                    "mobile_number" => "$mobile_no",
                    "registration_status" => "0", //not register
                    "OTP_sendstatus" => "1", //sucess
                    "status" => "Success",
                    "errordesc" => ""
                );
                print_r($result);
                return;
            } else {
                //Fail to send OTP.
                $result = array(
                    "status" => "Error",
                    "errordesc" => "Error in OTP send to mobile number."
                );
                print_r($result);
                return;
            }
        }
    } else {
        $result = array(
            "status" => "Error",
            "errordesc" => "Store not having any active scheme. $netbill_value"
        );
        print_r($result);
        return;
    }
} else {
    $result = array(
        "status" => "Error",
        "errordesc" => "Invalid mobile number or store id."
    );
    print_r($result);
    return;
}

function send_avilofferotp($mobile_no) {
    $otp = mt_rand(1000, 9999); 
    $message = "Dear Linenking Member , Your OTP to avail for Linenking Membership Discount is ".$otp." and is valid for 15 mins.";



    $smsHelper = new SMS_Membership();
    $errormsg = $smsHelper->sendSMS($mobile_no, $message);

    // Decode JSON into an associative array
    $responseArray = json_decode($errormsg, true);

    if (isset($responseArray['message']) && stripos($responseArray['message'], 'message Submitted successfully') !== false) {
        // Success
        $res = updatesend_otpstatus($mobile_no, $otp, OTP_Status::OTP_send);
        if ($res == 1) {
            return $otp;
        } else {
            return 0;
        }
    } else {
        return 0;
    }
}

function send_otpforenrollment($mobile_no) {
    $otp = mt_rand(1000, 9999);
    $message = "Dear Customer, Your OTP to enroll for Linenking Membership Program is ".$otp." and is valid for 15 mins.";

    $smsHelper = new SMS_Membership();
    $errormsg = $smsHelper->sendSMS($mobile_no, $message);

    // Decode JSON into an associative array
    $responseArray = json_decode($errormsg, true);

    if (isset($responseArray['message']) && stripos($responseArray['message'], 'message Submitted successfully') !== false) {
        // Success
        $res = updatesend_otpstatus($mobile_no, $otp, OTP_Status::OTP_send);
        if ($res == 1) {
            return $otp;
        } else {
            return 0;
        }
    } else {
        return 0;
    }
}

function updatesend_otpstatus($mobile_no, $otp, $otpstatus) {
    $db = new DBConn();
    $returnstatus = 0;
    $selqry = "select * from mobile_otp_verification where mobile_number=$mobile_no";
    $existingmob = $db->fetchObject($selqry);

    if ($existingmob) {
        $updtstsqry = "update mobile_otp_verification set otp=$otp,otp_status=$otpstatus,updatetime=now() where mobile_number=$mobile_no";
        $updtres = $db->execUpdate($updtstsqry);
        if ($updtres > 0) {
            $returnstatus = 1;
        }
    } else {
        $insrtstsqry = "insert into mobile_otp_verification set mobile_number=$mobile_no,otp=$otp,otp_status=$otpstatus,updatetime=now()";
        $insrtres = $db->execInsert($insrtstsqry);
        if ($insrtres > 0) {
            $returnstatus = 1;
        }
    }
    return $returnstatus;
}
