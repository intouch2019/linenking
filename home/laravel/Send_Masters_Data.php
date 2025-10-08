<?php
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";

$db = new DBConn();

$input = file_get_contents("php://input");
$data = json_decode($input, true);

$table_name = isset($data['table_name']) ? trim($data['table_name']) : '';
$rowstartat = isset($data['rowstartat']) ? intval($data['rowstartat']) : 0;
$api_key = isset($data['api_key']) ? trim($data['api_key']) : '';
$orderby = isset($data['orderby']) ? trim($data['orderby']) : 'ASC'; // default ASC

header('Content-Type: application/json');


if ($api_key == '' || $table_name == '' || $rowstartat < 0) {
    echo json_encode(array(
        "status" => "error",
        "message" => "Missing or invalid parameters",
        "required" => array("api_key", "table_name", "rowstartat")
    ));
    exit;
}


$valid_api_key = "RavikantSecretKey777";

if ($api_key !== $valid_api_key) {
    echo json_encode(array(
        "status" => "error",
        "message" => "Invalid API key"
    ));
    exit;
}


$allowed_tables = array(
    "it_brands",
    "it_categories",
    "it_category_taxes",
    "it_ck_designs",
    "it_ck_sizes",
    "it_ck_styles",
    "it_codes",
    "it_designs",
    "it_fabric_types",
    "it_items",
    "it_materials",
    "it_mfg_by",
    "it_mrp_taxes",
    "it_sizes",
    "it_styles",
    "it_taxes",
    "region",
    "states",
    "it_barcode_batches"
);

if (!in_array($table_name, $allowed_tables)) {
    echo json_encode(array(
        "status" => "error",
        "message" => "Invalid table name"
    ));
    exit;
}


$orderby = strtoupper($orderby);
if (!in_array($orderby, ['ASC', 'DESC'])) {
    $orderby = 'ASC';
}


$limit = 100;
$query = "SELECT * FROM $table_name ORDER BY id $orderby LIMIT $rowstartat, $limit";

$rows = $db->fetchObjectArray($query);


$cntquery = "SELECT COUNT(*) as rowcnt FROM $table_name";
$rowcntObj = $db->fetchObject($cntquery);
$rowcnt = $rowcntObj ? intval($rowcntObj->rowcnt) : 0;

if ($rows && count($rows) > 0) {
    echo json_encode(array(
        "status" => "success",
        "table" => $table_name,
        "start" => $rowstartat,
        "next_start" => $rowstartat + $limit,
        "rows" => $rows,
        "rowcnt" => $rowcnt
    ));
} else {
    echo json_encode(array(
        "status" => "empty",
        "message" => "No rows found"
    ));
}
?>
