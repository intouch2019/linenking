<?php
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";

// Define a valid API key
$securedKey = "FK2024XeIV_NikAp3Lk";
//$securedKey = "secretpassword";
// SHA256 hash using the hash() function
$validApiKey = hash('sha256', $securedKey);

// Set header for JSON response
header('Content-Type: application/json');

// Check if the API key is provided in the request header
$headers = getallheaders();

if (!isset($headers['Authorization']) || $headers['Authorization'] !== "Bearer $validApiKey") {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// Get raw POST data
$rawData = file_get_contents('php://input');

// Decode the JSON data into a PHP array
$data = json_decode($rawData, true);

// Check and process the received data
if (isset($data['designNumbers'])) {
    // Convert the array to the desired string format
    $designNumbers = "('" . implode("','", $data['designNumbers']) . "')"; //output: ('8284','S8284','N5224')
    
} else {
    echo "No design numbers received.";
}
$db = new DBConn();

$query = "SELECT DATE_FORMAT(o.bill_datetime, '%Y-%m') AS month, i.design_no, SUM(CASE WHEN o.tickettype IN (0, 1, 6) THEN oi.quantity ELSE 0 END) AS quantity"
        . " FROM it_orders o JOIN it_order_items oi ON oi.order_id = o.id JOIN it_items i ON i.id = oi.item_id JOIN it_sizes sz ON sz.id = i.size_id"
        . " JOIN it_codes c ON c.id = o.store_id AND c.usertype = 4 WHERE o.bill_datetime >= DATE_FORMAT(DATE_SUB(CURRENT_DATE, INTERVAL 1 YEAR), '%Y-%m-01')"
        . " AND o.bill_datetime < DATE_FORMAT(CURRENT_DATE, '%Y-%m-01') AND i.design_no IN $designNumbers GROUP BY DATE_FORMAT(o.bill_datetime, '%Y-%m'), i.design_no"
        . " ORDER BY i.design_no";

//echo $query;

$items = $db->fetchObjectArray($query);

$json_objs = array();
if(!empty($items)){

foreach ($items as $item) {
$json_objs[]=array(
$item->month,
$item->design_no,
$item->quantity,
);
}
}
$db->closeConnection();
$json_str=json_encode($json_objs);
print $json_str;








