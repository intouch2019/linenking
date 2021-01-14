<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/serverChanges/clsServerChanges.php";
require_once "lib/logger/clsLogger.php";

extract($_POST);
//print_r($_POST);


$errors = array();
$success = array();
$store = getCurrUser();
$clsLogger = new clsLogger();
$db = new DBConn();

$is_disc_log = false;
$ipaddr1 = $_SERVER['REMOTE_ADDR'];
if (!$storeid) {
    print "Missing parameter. Please report this error.";
    return;
}


if (!$store_name || !$address || !$owner || !$phone) {
    $errors['storec'] = "Please enter value for all required field marked with *";
} else {
    try {



        $serverCh = new clsServerChanges();
        $store_name = $db->safe($store_name);

        $address = $db->safe($address);

        $owner = $db->safe($owner);
        $phone = $db->safe($phone);
        if (count($errors) == 0) {



            $query = "update it_codes set store_name=$store_name, address=$address,  owner=$owner, phone=$phone ";  //, tally_name=$tallyname

            $query .= " where id=$storeid";
            $db->execUpdate($query);

            // error_log("\nUPDATE STORE DISC:-".$discquery."\n",3,"tmp.txt");

            $ipaddr = $_SERVER['REMOTE_ADDR'];
            $pg_name = __FILE__;
            $clsLogger->logInfo( $store->id, $pg_name, $ipaddr);

           $query = "select c.id as store_id,c.code,c.store_name,c.tally_name,c.accountinfo,c.owner,c.address,c.city,c.zipcode,c.phone,c.phone2,c.email,c.email2,c.vat,c.gstin_no,c.store_number,c.usertype,c.pancard_no,c.tax_type,c.server_change_id,c.username,c.a1hash,c.trust,c.password,c.createtime,c.inactive,c.is_closed,c.is_natch_required,d.dealer_discount,c.distance,c.state_id,c.composite_billing_opted ,c.region_id,IF(c.store_type =3, 1,0) as is_companystore,c.mask_margin from it_codes c left outer join it_ck_storediscount d on c.id = d.store_id where c.id = ".$storeid;

            $obj = $db->fetchObject($query);
            $server_ch = "[".json_encode($obj)."]";
            $ser_type = changeType::store;
            $store_id = DEF_CK_WAREHOUSE_ID;//changed on 15-03
            //here obj->store_id is  the id of table it_codes so it will become the data_id.
            $serverCh->save($ser_type, $server_ch,$store_id,$obj->store_id);
            $store_id = $storeid; // data for that store specifically
            $serverCh->save($ser_type, $server_ch,$storeid,$obj->store_id);
            $success = 'Store information updated.';
        }
    } catch (Exception $xcp) {
        $clsLogger = new clsLogger();
        $clsLogger->logError("Failed to add $storecode:" . $xcp->getMessage());
        $errors['status'] = "There was a problem processing your request. Please try again later";
    }
}
if (count($errors) > 0) {
    $_SESSION['form_errors'] = $errors;
    $redirect = "admin/stores/editlist/id=$storeid";
} else {

//        unset($_SESSION['fpath']);
//        unset($_SESSION['storeseq']);
//        $_SESSION['form_errors'] = $errors;
    
    unset($_SESSION['form_errors']);
    $_SESSION['form_success'] = $success;
    unset($_SESSION['form_success']);

    $redirect = "report/storelist";
}
session_write_close();
header("Location: " . DEF_SITEURL . $redirect);
exit;
?>