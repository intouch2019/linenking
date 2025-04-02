<?php
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
require_once "lib/orders/clsOrders.php";
require_once "session_check.php";
require_once "lib/logger/clsLogger.php";

header("Content-Type: application/json");
$response = array("success" => false);

if (isset($_POST["schemeId"])) {  
    $schemeId = intval($_POST["schemeId"]); 

    try {
        $db = new DBConn();

        $query = "SELECT scheme_name, 
                 min_amount_to_applied_scheme, 
                 discount_value, 
                 enrollment_fee, 
                 is_scheme_active,  
                 DATE_FORMAT(start_date, '%d-%m-%Y') AS start_date, 
                 DATE_FORMAT(end_date, '%d-%m-%Y') AS end_date
          FROM membership_scheme_masters 
          WHERE id = $schemeId";

        $scheme = $db->fetchObject($query);

        if ($scheme) {
           
            $response["success"] = true;
            $response["data"] = array(
                "schemeName" => !empty($scheme->scheme_name) ? $scheme->scheme_name : "N/A",
                "schemeMinAmount" => isset($scheme->min_amount_to_applied_scheme) ? $scheme->min_amount_to_applied_scheme : 0,
                "discountValue" => isset($scheme->discount_value) ? $scheme->discount_value : 0,
                "ckEnrollmentFee" => isset($scheme->enrollment_fee) ? $scheme->enrollment_fee : 0,
                "start_date" => !empty($scheme->start_date) ? $scheme->start_date : "0000-00-00 00:00:00",
                "end_date" => !empty($scheme->end_date) ? $scheme->end_date : "0000-00-00 00:00:00",
                "schemeActive" => isset($scheme->is_scheme_active) ? $scheme->is_scheme_active : 0
            );
        } else {
            $response["message"] = "No scheme found with the given ID.";
        }
    } catch (Exception $e) {
        $response["message"] = "Error fetching scheme details: " . $e->getMessage();
    }
} else {
    $response["message"] = "Invalid request. Scheme ID is missing.";
}

echo json_encode($response);
?>
