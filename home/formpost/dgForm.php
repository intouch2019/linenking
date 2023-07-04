<?php

require_once("../../it_config.php");
require_once("session_check.php");
require_once "../lib/db/DBConn.php";
require_once "lib/core/Constants.php";

extract($_POST);
//print_r($_POST);
//exit();
try {
    $db = new DBConn();

    $cust_name = isset($cust_name) && trim($cust_name) != "" ? $db->safe($cust_name) : false;
    $cust_namequery = "customer_name=$cust_name";
    if (!$cust_name) {
        $cust_namequery = "";
    }


    $c_mobile_no = isset($c_mobile_no) && trim($c_mobile_no) != "" ? $db->safe($c_mobile_no) : false;
    $c_mobile_noquery = ",customer_mobile_no=$c_mobile_no";
    if (!$c_mobile_no) {
        $c_mobile_noquery = "";
    }


    $c_old_bill_no = isset($c_old_bill_no) && trim($c_old_bill_no) != "" ? $db->safe($c_old_bill_no) : false;
    $c_old_bill_noquery = ",cust_old_bill_no=$c_old_bill_no";
    if (!$c_old_bill_no) {
        $c_old_bill_noquery = "";
    }


    $c_old_bill_no_date = isset($c_old_bill_no_date) && trim($c_old_bill_no_date) != "" ? $db->safe($c_old_bill_no_date) : false;
    $c_old_bill_no_datequery = ",old_bill_date=$c_old_bill_no_date";
    if (!$c_old_bill_no_date) {
        $c_old_bill_no_datequery = "";
    }

    $og_purchase_store_name = isset($og_purchase_store_name) && trim($og_purchase_store_name) != "" ? $db->safe($og_purchase_store_name) : false;
    $og_purchase_store_namequery = ",orignal_purchase_store_name=$og_purchase_store_name";
    if (!$og_purchase_store_name) {
        $og_purchase_store_namequery = "";
    }


    $exg_bill_no = isset($exg_bill_no) && trim($exg_bill_no) != "" ? $db->safe($exg_bill_no) : false;
    $exg_bill_noquery = ",exchange_bill_no=$exg_bill_no";
    if (!$exg_bill_no) {
        $exg_bill_noquery = "";
    }

    $exg_bill_no_date = isset($exg_bill_no_date) && trim($exg_bill_no_date) != "" ? $db->safe($exg_bill_no_date) : false;
    $exg_bill_no_datequery = ",exchange_bill_date=$exg_bill_no_date";
    if (!$exg_bill_no_date) {
        $exg_bill_no_datequery = "";
    }

    $exchange_given_at_store = isset($exchange_given_at_store) && trim($exchange_given_at_store) != "" ? $db->safe($exchange_given_at_store) : false;
    $exchange_given_at_storequery = ",exchange_given_at_store=$exchange_given_at_store";
    if (!$exchange_given_at_store) {
        $exchange_given_at_storequery = "";
    }

    $st_address = isset($st_address) && trim($st_address) != "" ? $db->safe($st_address) : false;
    $st_addressquery = ",store_address=$st_address";
    if (!$st_address) {
        $st_addressquery = "";
    }

    $st_manager_name = isset($st_manager_name) && trim($st_manager_name) != "" ? $db->safe($st_manager_name) : false;
    $st_manager_namequery = ",store_manager_name=$st_manager_name";
    if (!$st_manager_name) {
        $st_manager_namequery = "";
    }

    $st_manager_mob_no = isset($st_manager_mob_no) && trim($st_manager_mob_no) != "" ? $db->safe($st_manager_mob_no) : false;
    $st_manager_mob_noquery = ",store_manager_mob_no=$st_manager_mob_no";
    if (!$st_manager_mob_no) {
        $st_manager_mob_noquery = "";
    }

    $prod = isset($prod) && trim($prod) != "" ? $db->safe($prod) : false;
    $prodquery = ",product=$prod";
    if (!$prod) {
        $prodquery = "";
    }


    $design_no = isset($design_no) && trim($design_no) != "" ? $db->safe($design_no) : false;
    $design_noquery = ",design_no=$design_no";
    if (!$design_no) {
        $design_noquery = "";
    }

    $size = isset($size) && trim($size) != "" ? $db->safe($size) : false;
    $sizequery = ",size=$size ";
    if (!$size) {
        $sizequery = "";
    }


    $barcode = isset($barcode) && trim($barcode) != "" ? $db->safe($barcode) : false;
    $barcodequery = ",barcode=$barcode ";
    if (!$barcode) {
        $barcodequery = "";
    }
    

    $mrp = isset($mrp) && trim($mrp) != "" ? $db->safe($mrp) : false;
    $mrpquery = ",mrp=$mrp ";
    if (!$mrp) {
        $mrpquery = "";
    }


    $style = isset($style) && trim($style) != "" ? $db->safe($style) : false;
    $stylequery = ",style=$style";
    if (!$style) {
        $stylequery = "";
    }


    //Emppty string to store defects as string
    $defectStrInitial = "";

    if (isset($_POST['color_fade']) && trim($_POST['color_fade']) != "") {
        $defectStrInitial .= $_POST['color_fade'] . ", ";
    }
    if (isset($_POST['pilling']) && trim($_POST['pilling']) != "") {
        $defectStrInitial .= $_POST['pilling'] . ", ";
    }
    if (isset($_POST['slippage']) && trim($_POST['slippage']) != "") {
        $defectStrInitial .= $_POST['slippage'] . ", ";
    }
    if (isset($_POST['shrinkage']) && trim($_POST['shrinkage'])) {
        $defectStrInitial .= $_POST['shrinkage'] . ", ";
    }
    if (isset($_POST['tearing']) && trim($_POST['tearing']) != "") {
        $defectStrInitial .= $_POST['tearing'] . ", ";
    }
    if (isset($_POST['staining']) && trim($_POST['staining']) != "") {
        $defectStrInitial .= $_POST['staining'] . ", ";
    }
    if (isset($_POST['soap_mark']) && trim($_POST['soap_mark']) != "") {
        $defectStrInitial .= $_POST['soap_mark'] . ", ";
    }
    if (isset($_POST['cust_defect_but_change_for_service']) && trim($_POST['cust_defect_but_change_for_service']) != "") {
        $defectStrInitial .= $_POST['cust_defect_but_change_for_service'] . ", ";
    }
    if (isset($_POST['other']) && trim($_POST['other']) != "") {
        $defectStrInitial .= $_POST['other'] . ", ";
    }


    $remarkTxtArea = isset($remarkTxtArea) && trim($remarkTxtArea) != "" ? $db->safe($remarkTxtArea) : false;
    $remarkTxtAreaquery = ",remark_for_other_defects=$remarkTxtArea";
    if (!$remarkTxtArea) {
        $remarkTxtAreaquery = "";
        $defectStrInitial = rtrim($defectStrInitial, ", ");
    } else {
        $defectStrInitial = rtrim($defectStrInitial, ", ");
    }

    $defectStringquery = ",defects=" . $db->safe($defectStrInitial);

    $insertQuery = "insert into defective_garment_form set $cust_namequery $c_mobile_noquery"
            . " $c_old_bill_noquery $c_old_bill_no_datequery"
            . " $og_purchase_store_namequery $exg_bill_noquery $exg_bill_no_datequery"
            . " $exchange_given_at_storequery $st_addressquery $st_manager_namequery"
            . " $st_manager_mob_noquery $prodquery $design_noquery $defectStringquery"
            . " $remarkTxtAreaquery $sizequery $stylequery $barcodequery $mrpquery";

    //    print_r("<br><br>");
//    print_r($insertQuery);
//    exit();
//    print_r("<br><br>");
//    print_r($db);
    


    if ($db->execInsert($insertQuery)) {
        $success = "Form has been saved successfully.";
    } else {
        $errors['status'] = "There was a problem saving form. Please contact Intouch.";
    }
} catch (Exception $ex) {
    $clsLogger = new clsLogger();
    $clsLogger->logError("Failed to save form details" . $ex->getMessage());
    $errors['status'] = "There was a problem processing form. Please try again later.";
}


if (count($errors) > 0) {
    $_SESSION['form_errors'] = $errors;
    $redirect = "form";
} else {
    unset($_SESSION['form_errors']);
    $_SESSION['form_success'] = $success;
    $redirect = "form";
}

session_write_close();
header("Location: " . DEF_SITEURL . "dg/$redirect");
exit;
