<?php
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";

// Define a valid API key
$securedKey = "FK2024XeWO_NikM93Lk";
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

$query = "SELECT i.id, c.name AS category, i.design_no, st.name AS style, c.id as cat_id FROM it_items i INNER JOIN it_categories c ON i.ctg_id = c.id"
        . " INNER JOIN it_styles st ON i.style_id = st.id WHERE i.design_no IN $designNumbers and issent_wip_refilldesign=0"
        . " GROUP BY i.design_no, c.name, st.name ORDER BY i.id;";


$items = $db->fetchObjectArray($query);
$json_objs = array();
if(!empty($items)){
    foreach ($items as $obj) {
    $uquery = "update it_items set issent_wip_refilldesign=1 where design_no='$obj->design_no'";
//    echo $uquery;
    $db->execUpdate($uquery);
    }

foreach ($items as $item) {
$json_objs[]=array(
$item->category,
$item->cat_id,
$item->design_no,
$item->style
);
}
}
$db->closeConnection();
$json_str=json_encode($json_objs);
print $json_str;

