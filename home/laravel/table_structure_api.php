<?php
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";

$db = new DBConn();

$input = file_get_contents("php://input");
$data = json_decode($input, true);

$table_name = isset($data['table_name']) ? trim($data['table_name']) : '';
$api_key = isset($data['api_key']) ? trim($data['api_key']) : '';

header('Content-Type: application/json');

// Basic validation
if ($api_key == '' || $table_name == '') {
    echo json_encode(array(
        "status" => "error",
        "message" => "Missing or invalid parameters",
        "required" => array("api_key", "table_name")
    ));
    exit;
}

// Validate API key
$valid_api_key = "RavikantSecretKey777";
if ($api_key !== $valid_api_key) {
    echo json_encode(array(
        "status" => "error",
        "message" => "Invalid API key"
    ));
    exit;
}

// Check if table exists in the database
$table_check_query = "SHOW TABLES LIKE '" . $table_name . "'";
$tableObj = $db->fetchObject($table_check_query);
if (!$tableObj) {
    echo json_encode(array(
        "status" => "error",
        "message" => "Table does not exist"
    ));
    exit;
}

// Fetch table description
$desc_query = "DESCRIBE `" . $table_name . "`";

try {
    $columns = $db->fetchObjectArray($desc_query);

    if ($columns && count($columns) > 0) {
        echo json_encode(array(
            "status" => "success",
            "table" => $table_name,
            "columns" => $columns
        ));
    } else {
        echo json_encode(array(
            "status" => "empty",
            "message" => "No columns found"
        ));
    }
} catch (Exception $ex) {
    echo json_encode(array(
        "status" => "error",
        "message" => "Query execution failed",
        "error" => $ex->getMessage()
    ));
}
?>
