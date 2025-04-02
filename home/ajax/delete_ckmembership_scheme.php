<?php
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
require_once "session_check.php";
require_once "lib/logger/clsLogger.php";
require_once "lib/core/Constants.php";

header("Content-Type: application/json");

// Check if schemeId is set and valid
if (!isset($_POST['schemeId']) || !is_numeric($_POST['schemeId'])) {
    echo json_encode(["status" => "error", "message" => "Invalid Scheme ID."]);
    exit;
}

$currentuserid = getCurrUser()->id;
$currentuserid = isset($currentuserid) ? $currentuserid : 0;
$scheme_id = intval($_POST['schemeId']);
$db = new DBConn();
$logger = new clsLogger();

try {
    // Check if the scheme exists
    $checkQuery = "SELECT id,is_scheme_delete,scheme_name FROM membership_scheme_masters WHERE id = $scheme_id";
    $result = $db->fetchObject($checkQuery);

    if (!$result) {
        echo json_encode(["status" => "error", "message" => "Scheme not found."]);
        exit;
    }

    if ($result->is_scheme_delete == 1) {
        echo json_encode(["status" => "error", "message" => "Scheme is already deleted."]);
        exit;
    }
    
    if ($result->is_scheme_delete == 0) {
        $qry = "select count(id) as active_schemestore from storewise_membership_schemes where scheme_id=$scheme_id";
        $resultactivescheme = $db->fetchObject($qry);
        if ($resultactivescheme->active_schemestore > 0) {
            echo json_encode(["status" => "error", "message" => "This scheme is active for some store. please deactive that defore delete scheme."]);
            exit;
        }
    }

    $selectqry="select * from membership_scheme_masters WHERE id = $scheme_id";
    $singleqryobj=$db->fetchObject($selectqry);
    
$schemeName = isset($singleqryobj->scheme_name) ? $singleqryobj->scheme_name : '';
$schemeMinAmount = isset($singleqryobj->min_amount_to_applied_scheme) ? $singleqryobj->min_amount_to_applied_scheme : 0;
$discountValue = isset($singleqryobj->discount_value) ? $singleqryobj->discount_value : 0;
$ckEnrollmentFee = isset($singleqryobj->enrollment_fee) ? $singleqryobj->enrollment_fee : 0;
$start_date = isset($singleqryobj->start_date) ? $singleqryobj->start_date : '';
$end_date = isset($singleqryobj->end_date) ? $singleqryobj->end_date : '';
$schemeActive = isset($singleqryobj->is_scheme_active) ? $singleqryobj->is_scheme_active : 0;


    $insertQuery_logs = "INSERT INTO membership_scheme_masters_logs 
                        (scheme_name,scheme_id, min_amount_to_applied_scheme, discount_value, enrollment_fee, start_date, end_date, is_scheme_active, update_date, is_scheme_delete, execute_by_user, query_type) 
                        VALUES ('$schemeName',$scheme_id, $schemeMinAmount, $discountValue, $ckEnrollmentFee, '$start_date', '$end_date', $schemeActive, now(), 1, $currentuserid, " . Membership_querytype::Delete . ")";

    $db->execInsert($insertQuery_logs);
    
    // Perform the delete operation
    $query = "UPDATE membership_scheme_masters SET is_scheme_delete = 1 WHERE id = $scheme_id";
    $roweffected = $db->execUpdate($query);
    


    if ($roweffected > 0) {
        $logger->logInfo("Scheme ID $scheme_id marked as deleted.");
        echo json_encode(["status" => "success", "message" => "Scheme deleted successfully."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Scheme could not be deleted."]);
    }
} catch (Exception $e) {
    $logger->logError("Error deleting scheme ID $scheme_id: " . $e->getMessage());
    echo json_encode(["status" => "error", "message" => "Error deleting scheme."]);
}

$db->closeConnection();
?>
