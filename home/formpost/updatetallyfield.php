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


if (!$retail_saletally_name || !$retail_sale_cash_name || !$retail_sale_card_name) {
    $errors['storec'] = "Please enter value for all required field marked with *";
} else {
    try {
        $retail_saletally_name = $db->safe($retail_saletally_name);
        $retail_sale_cash_name = $db->safe($retail_sale_cash_name);
        $retail_sale_card_name = $db->safe($retail_sale_card_name);

        $query = "update it_codes set retail_saletally_name = $retail_saletally_name, retail_sale_cash_name=$retail_sale_cash_name, retail_sale_card_name=$retail_sale_card_name where id = $store->id";
        print $query;
        $db->execUpdate($query);
        $success = 'Store information updated.';
    } catch (Exception $ex) {
        $clsLogger = new clsLogger();
        $clsLogger->logError("Failed to add $storecode:" . $xcp->getMessage());
        $errors['status'] = "There was a problem processing your request. Please try again later";
    }
    if (count($errors) > 0) {
        $_SESSION['form_errors'] = $errors;
        $redirect = "store/tallytransfer";
    } else {
        unset($_SESSION['form_errors']);
        $_SESSION['form_success'] = $success;
        $redirect = "store/tallytransfer";
    }
    session_write_close();
header("Location: ".DEF_SITEURL.$redirect);
exit; 
}
