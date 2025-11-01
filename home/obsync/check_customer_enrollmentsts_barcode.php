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
        
        // membership barcode
        $membershipquery = "select barcode from it_items where ctg_id = 65 limit 1";
        $result = $db->fetchObject($membershipquery);
        $membership_barcode = ($result !== null) ? $result->barcode : "";

        if (isset($checkenrollmentobj)) {//Already register user
           
            
    $check_curr_scheme = "select mm.enrollment_fee, mm.min_amount_to_applied_scheme, mm.discount_value from membership_scheme_masters mm join storewise_membership_schemes ss on mm.id = ss.scheme_id where mm.is_scheme_active = 1 and mm.is_scheme_delete = 0 and mm.start_date < now() and mm.end_date > now() and ss.is_data_deleted = 0 and ss.store_id = $store_id and mm.min_amount_to_applied_scheme <= $netbill_value order by mm.min_amount_to_applied_scheme desc limit 1";
    $curr_scheme_obj = $db->fetchObject($check_curr_scheme);
            if ($curr_scheme_obj->min_amount_to_applied_scheme > $netbill_value) {
            $result = array(
                "status" => "Error",
                "errordesc" => "The bill's net value is lower. The scheme has not been applied to this bill."
            );
            print_r($result);
            return;
        }
            //now the user is already registerd and not used scheme discount in last 24 hours
            //Successfully send OTP.
            $result = array(
                "mobile_number" => "$mobile_no",
                "membership_barcode" => $membership_barcode,
                "registration_status" => "1", //already register
                "status" => "Success",
                "errordesc" => ""
            );
            print_r($result);
            return;
        } else {
            //unregisterd user.

            $result = array(
                "mobile_number" => "$mobile_no",
                "membership_barcode" => $membership_barcode,                
                "registration_status" => "3", //not register
                "status" => "Success",
                "errordesc" => ""
            );
            print_r($result);
            return;
        }
    } else {
        $result = array(
            "status" => "Error",
            "errordesc" => "The store does not have any active schemes. / The net bill value of the garments is $netbill_value which is not eligible for any scheme."
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