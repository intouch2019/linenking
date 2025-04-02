<?php

include "checkAccess.php";//live
require_once "../../it_config.php";
require_once "lib/logger/clsLogger.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/sms/SMS_Membership.php";

extract($_POST);
$db = new DBConn();
//$store_id = $gCodeId; //live

//$store_id = 161; //test
$logger = new clsLogger();
$result = array();

if (!empty($store_id) && intval($store_id) !== 0) {
    //check netbill_amt
    $check_curr_scheme = "select GROUP_CONCAT(mm.scheme_name SEPARATOR ' or ') as storeactive_scheme from membership_scheme_masters mm join storewise_membership_schemes ss on mm.id = ss.scheme_id where mm.is_scheme_active = 1 and mm.is_scheme_delete = 0 and mm.start_date < now() and mm.end_date > now() and ss.is_data_deleted = 0 and ss.store_id = $store_id order by mm.min_amount_to_applied_scheme";
    $curr_scheme_obj = $db->fetchObject($check_curr_scheme);

    if ($curr_scheme_obj && !empty($curr_scheme_obj->storeactive_scheme)) {

       $result = array(
        "status" => "Success",
        "errordesc" => $curr_scheme_obj->storeactive_scheme
    );
        print_r($result);
        return;
    } else {
        $result = array(
            "status" => "Error",
            "errordesc" => "The store does not have any active schemes."
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

