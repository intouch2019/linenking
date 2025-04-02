<?php

require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/serverChanges/clsServerChanges.php";
require_once 'lib/users/clsUsers.php';

$_SESSION['form_post'] = $_POST;
extract($_POST);

$currentuserid = getCurrUser()->id;


// Validate that no fields are empty
if (
    empty($schemeName) ||
    empty($schemeMinAmount) ||
    empty($discountValue) ||
    empty($ckEnrollmentFee) ||
    empty($start_date) ||
    empty($end_date) ||
    $schemeActive === null
) {
    die("Error: All fields are required and cannot be empty.");
}

try {
    $db = new DBConn();

    // Sanitize input values  $start_date = trim($db->safe($start_date));
    $selectSchemeid = isset($selectScheme) ? intval($selectScheme) : 0;
    $schemeName = trim($db->safe($schemeName));
    $schemeMinAmount = floatval($schemeMinAmount);
    $discountValue = floatval($discountValue);
    $ckEnrollmentFee = floatval($ckEnrollmentFee);
    $start_date = isset($start_date) ? trim($db->safe($start_date . ' 00:00:00')) : null;
    $end_date = isset($end_date) ? trim($db->safe($end_date . ' 23:59:59')) : null;
    $schemeActive = intval($schemeActive);
    $currentuserid = isset($currentuserid) ? $currentuserid : 0;

    // Check if the scheme already exists
    $query = "SELECT id FROM membership_scheme_masters WHERE scheme_name = $schemeName";
    $existingScheme = $db->fetchObject($query);

    if ($existingScheme) {
        // If scheme exists, update it
        $updateQuery = "UPDATE membership_scheme_masters 
                        SET min_amount_to_applied_scheme = $schemeMinAmount,
                            discount_value = $discountValue,
                            enrollment_fee = $ckEnrollmentFee,
                            start_date = $start_date,
                            end_date = $end_date,
                            is_scheme_active = $schemeActive,
                            update_date = now()
                        WHERE scheme_name = $schemeName and id=$selectSchemeid";
//        print_r($updateQuery);        exit();
        $db->execUpdate($updateQuery);
        
        //logsinsertion
        $updateQuery_logs = "INSERT INTO membership_scheme_masters_logs 
                        (scheme_name,scheme_id, min_amount_to_applied_scheme, discount_value, enrollment_fee, start_date, end_date, is_scheme_active,update_date,execute_by_user,query_type) 
                        VALUES ($schemeName,$selectSchemeid, $schemeMinAmount, $discountValue, $ckEnrollmentFee, $start_date, $end_date, $schemeActive,now(),$currentuserid,".Membership_querytype::Update.")";
        $db->execInsert($updateQuery_logs);
        
        $_SESSION['form_success'] = "Scheme updated successfully!";
    } else {
        // If scheme doesn't exist, insert a new one
        $insertQuery = "INSERT INTO membership_scheme_masters 
                        (scheme_name, min_amount_to_applied_scheme, discount_value, enrollment_fee, start_date, end_date, is_scheme_active,update_date) 
                        VALUES ($schemeName, $schemeMinAmount, $discountValue, $ckEnrollmentFee, $start_date, $end_date, $schemeActive,now())";
        
        $db->execInsert($insertQuery);
        
        //Logs_Insert
        $insertQuery_logs = "INSERT INTO membership_scheme_masters_logs 
                        (scheme_name, min_amount_to_applied_scheme, discount_value, enrollment_fee, start_date, end_date, is_scheme_active,update_date,execute_by_user,query_type) 
                        VALUES ($schemeName, $schemeMinAmount, $discountValue, $ckEnrollmentFee, $start_date, $end_date, $schemeActive,now(),$currentuserid,".Membership_querytype::Insert.")";
        $db->execInsert($insertQuery_logs);
        
        $_SESSION['form_success'] = "New scheme inserted successfully!";
    }

    $db->closeConnection();
} catch (Exception $e) {
    $_SESSION['form_error'] = "Error: " . $e->getMessage();
}

// Redirect with success message
$redirect = "enrollmembersscheme/masters";
session_write_close();
header("Location: " . DEF_SITEURL . $redirect);
exit;
?>
