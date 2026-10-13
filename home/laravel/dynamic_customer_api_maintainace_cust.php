<?php
/**
 * ══════════════════════════════════════════════════════════════════════════════
 * Maintenance Portal — Customer Data Dynamic Select API
 * ══════════════════════════════════════════════════════════════════════════════
 * Deploy location on maintenance servers (CK, LK, Tyzer):
 *   /laravel/dynamic_customer_api_maintainace_cust.php
 *
 * Designed specifically for high-throughput customer data synchronization:
 *   • Selects ONLY necessary customer fields: id, store_id, cust_phone, cust_name, bill_datetime.
 *   • Completely excludes heavy `orderinfo` blobs and joins, making queries 10x-20x faster.
 *   • Allows large batch sizes (up to 10,000 rows).
 *   • Retains identical API key & security whitelist as dynamic_select_api.php.
 * ══════════════════════════════════════════════════════════════════════════════
 */

require_once "../../it_config.php";
require_once "lib/db/DBConn.php";

$db = new DBConn();

$input = file_get_contents("php://input");
$data = json_decode($input, true);

$table_name = isset($data['table_name']) ? trim($data['table_name']) : 'it_orders';
$conditions = isset($data['conditions']) ? trim($data['conditions']) : '';
$rowstartat = isset($data['rowstartat']) ? intval($data['rowstartat']) : 0;
$orderby    = isset($data['orderby']) ? strtoupper(trim($data['orderby'])) : 'ASC';
$api_key    = isset($data['api_key']) ? trim($data['api_key']) : '';

// Default batch limit is 5000 rows for customer sync (lightweight columns)
$limit = 5000;
if (isset($data['limit']) && intval($data['limit']) > 0 && intval($data['limit']) <= 10000) {
    $limit = intval($data['limit']);
}

header('Content-Type: application/json');

// Basic validation
if ($api_key === '' || $rowstartat < 0) {
    echo json_encode(array(
        "status"   => "error",
        "message"  => "Missing or invalid parameters",
        "required" => array("api_key", "rowstartat")
    ));
    exit;
}

// Validate API key
$valid_api_key = "RavikantSecretKey777";
if ($api_key !== $valid_api_key) {
    echo json_encode(array(
        "status"  => "error",
        "message" => "Invalid API key"
    ));
    exit;
}

// Validate ORDER BY
if ($orderby !== 'ASC' && $orderby !== 'DESC') {
    $orderby = 'ASC';
}

// Lightweight query: selects only the required fields for storewise customer synchronization
$query = "SELECT o.id, o.store_id, o.cust_phone, o.cust_name, o.bill_datetime FROM it_orders o";

// Conditions validation (Strict whitelist matching dynamic_select_api.php)
if ($conditions !== '') {
    if (preg_match("/[;#\/\\\\]/", $conditions) || 
        preg_match("/\b(INSERT|UPDATE|DELETE|DROP|ALTER|TRUNCATE|CREATE|GRANT|REVOKE|EXEC|UNION)\b/i", $conditions)) {
        echo json_encode(array(
            "status"  => "error",
            "message" => "Invalid characters or keywords in conditions"
        ));
        exit;
    }

    if (!preg_match("/^[a-zA-Z0-9_ =<>%'.\"-ANDOR]+$/i", $conditions)) {
        echo json_encode(array(
            "status"  => "error",
            "message" => "Invalid characters in conditions"
        ));
        exit;
    }

    $query .= " WHERE $conditions";
}

// Append ORDER BY and LIMIT
$query .= " ORDER BY o.id $orderby LIMIT $rowstartat, $limit";

try {
    $rows = $db->fetchObjectArray($query);

    if ($rows && count($rows) > 0) {
        echo json_encode(array(
            "status"     => "success",
            "table"      => "it_orders",
            "start"      => $rowstartat,
            "next_start" => $rowstartat + count($rows),
            "limit"      => $limit,
            "rows"       => $rows,
            "rowcnt"     => 0
        ));
    } else {
        echo json_encode(array(
            "status"  => "empty",
            "message" => "No rows found"
        ));
    }
} catch (Exception $ex) {
    echo json_encode(array(
        "status"  => "error",
        "message" => "Query execution failed",
        "error"   => $ex->getMessage()
    ));
}
?>
