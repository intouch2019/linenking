<?php

include "checkAccess.php";//live
require_once "../../it_config.php";
require_once "lib/logger/clsLogger.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/sms/SMS_Membership.php"; 

extract($_POST);
$db = new DBConn();
$netbill_value = $_POST['netbill_amt'];//live
//$store_id = $gCodeId; //live


//$netbill_value = 4999; //test
//$store_id = 161; //test
$logger = new clsLogger();
$result = array();



if (!empty($store_id) && intval($store_id) !== 0) {
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
        }else{
            $result = array(
                "status" => "Success",
                "errordesc" => ""
            );
            print_r($result);
//            print_r("0");
            return;
        }

       
    } else {
        $result = array(
            "status" => "Error",
            "errordesc" => "The store does not have any active schemes/the net bill value of $netbill_value is not eligible for any scheme."
        );
        print_r($result);
        return;
    }
} else {
    $result = array(
        "status" => "Error",
        "errordesc" => "Invalid store id."
    );
    print_r($result);
    return;
}

