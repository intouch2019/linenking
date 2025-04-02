<?php

include "checkAccess.php";//live
require_once "../../it_config.php";
require_once "lib/logger/clsLogger.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/sms/SMSHelper.php";

extract($_POST);
$db = new DBConn();
$mobile_no = $_POST['mobile_no'];//live
$netbill_value = $_POST['netbill_value'];//live
$OTP =$_POST['otp'];
//$store_id = $gCodeId; //live

//$netbill_value=5999;
//$mobile_no = "9881677716"; //test
//$OTP = 6581;
//$store_id = 161; //test
//$netbill_value=9999;
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

if (!isset($OTP) || !preg_match('/^\d{4}$/', $OTP)) {
    $result = array(
        "status" => "Error",
        "errordesc" => "Invalid OTP. It must be a 4-digit number."
    );
    print_r($result);
    return;
}

$checkotp = "select id from mobile_otp_verification where mobile_number=$mobile_no and otp_status=" . OTP_Status::OTP_send . " and otp=$OTP and updatetime >= now() - interval 15 minute";
$validateotp = $db->fetchObject($checkotp);
if ($validateotp) {
    //update OTP verified status
    $chngsts = "update mobile_otp_verification set otp_status=" . OTP_Status::Verified . ", updatetime=now()";
    $db->execUpdate($chngsts);

    if (!empty($mobile_no) && !empty($store_id) && intval($store_id) !== 0) {
        $check_curr_scheme = "select mm.enrollment_fee, mm.min_amount_to_applied_scheme, mm.discount_value from membership_scheme_masters mm join storewise_membership_schemes ss on mm.id = ss.scheme_id where mm.is_scheme_active = 1 and mm.is_scheme_delete = 0 and mm.start_date < now() and mm.end_date > now() and ss.is_data_deleted = 0 and ss.store_id = $store_id and mm.min_amount_to_applied_scheme <= $netbill_value order by mm.min_amount_to_applied_scheme desc limit 1";
        $curr_scheme_obj = $db->fetchObject($check_curr_scheme);
        $membership_fee = isset($curr_scheme_obj->enrollment_fee) && !empty($curr_scheme_obj->enrollment_fee) ? $curr_scheme_obj->enrollment_fee : 0.00;
        $min_bill_value_required = isset($curr_scheme_obj->min_amount_to_applied_scheme) && !empty($curr_scheme_obj->min_amount_to_applied_scheme) ? $curr_scheme_obj->min_amount_to_applied_scheme : 0.00;
        $discount_value = isset($curr_scheme_obj->discount_value) && !empty($curr_scheme_obj->discount_value) ? $curr_scheme_obj->discount_value : 0.00;

        if (isset($curr_scheme_obj)) {
            $checkenrollmentstatus = "select * from membership_customer_details where member_mobno=$mobile_no and is_membership_active=1 and membership_enroll_date < now() and membership_expiry_date > now()";
            $checkenrollmentobj = $db->fetchObject($checkenrollmentstatus);
            if (isset($checkenrollmentobj)) {//Already register user
                if ($checkenrollmentobj->member_last_purchase != NULL) {//Not purchase in last 24 hour
                   $purchase_date = date('Y-m-d', strtotime($checkenrollmentobj->member_last_purchase)); // Extract date only
                $today_date = date('Y-m-d'); // Get today's date

                    
                        //now the user is already registerd and not used scheme discount in last 24 hours
                        $result = array(
                            "mobile_number" => "$mobile_no",
                            "registration_status" => "1", //already register
                            "membership_fee" => $membership_fee,
                            "min_bill_value_required" => $min_bill_value_required,
                            "discount_value" => $discount_value,
                            "status" => "Sucess",
                            "errordesc" => ""
                        );
                        print_r($result);
                        return;
                    
                } else {
                    $result = array(
                        "status" => "Error",
                        "errordesc" => "Member Last Purchase date is null."
                    );
                    print_r($result);
                    return;
                }
            } else {
                //unregisterd user.
                //Sucessfully send OTP.
                $result = array(
                    "mobile_number" => "$mobile_no",
                    "registration_status" => "0", //not register
                    "membership_fee" => $membership_fee,
                    "min_bill_value_required" => $min_bill_value_required,
                    "discount_value" => $discount_value,
                    "status" => "Sucess",
                    "errordesc" => ""
                );
                print_r($result);
                return;
            }
        } else {
            $result = array(
                "status" => "Error",
                "errordesc" => "Store not having any active scheme."
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
} else {
    $result = array(
        "status" => "Error",
        "errordesc" => "Invalid OTP/OTP Expired"
    );
    print_r($result);
    return;
}


