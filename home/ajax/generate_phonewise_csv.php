<?php
ini_set('memory_limit', '1024M');
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
require_once "lib/orders/clsOrders.php";
require_once ("session_check.php");
require_once "lib/logger/clsLogger.php";


// Validate input
if (!isset($_GET['storeidforvoucher']) || !isset($_GET['start_date']) || !isset($_GET['end_date'])) {
    http_response_code(400);
    echo "Invalid input";
    exit;
}


$store_ids = $_GET['storeidforvoucher'];
$start_date = $_GET['start_date'] . " 00:00:00";
$end_date = $_GET['end_date'] . " 23:59:59";

$db = new DBConn();

// If All Stores is selected (-1), get all store IDs
if (in_array("-1", $store_ids)) {
    $all_stores = $db->fetchObjectArray("SELECT id FROM it_codes WHERE usertype=4");
    $store_ids = array_map(function($s) { return $s->id; }, $all_stores);
}
$store_id_str = implode(",", array_map('intval', $store_ids));

function formatSmart($value, $precision = 2) {
    if (is_numeric($value)) {
        return floor($value) == $value ? number_format($value, 0) : number_format($value, $precision);
    }
    return $value;
}

// Your long SQL query
function getReportQuery($store_id_str, $from_dt, $to_dt) {
    // Use the same SQL you already created earlier in your class
    // To keep this answer readable, we'll include a placeholder here
    // Replace "SELECT ..." below with the full query from your app
    return "SELECT c.store_name, o.store_id, o.cust_phone, o.bill_no, o.sub_total, sub.loyalty_count FROM it_orders o JOIN it_codes c ON c.id = o.store_id JOIN it_order_payments iop ON iop.order_id = o.id JOIN ( SELECT o2.cust_phone, COUNT(*) AS loyalty_count FROM it_orders o2 JOIN it_order_payments iop2 ON iop2.order_id = o2.id WHERE o2.cust_phone IS NOT NULL AND o2.cust_phone != '' AND iop2.payment_name = 'loyalty' AND o2.store_id in($store_id_str) AND o2.bill_datetime BETWEEN  '$from_dt' AND '$to_dt'  GROUP BY o2.cust_phone HAVING COUNT(*) > 2 ) AS sub ON sub.cust_phone = o.cust_phone WHERE iop.payment_name = 'loyalty' AND o.store_id in($store_id_str) AND o.bill_datetime BETWEEN  '$from_dt' AND '$to_dt'  ORDER BY sub.loyalty_count DESC, o.cust_phone, o.id DESC";
}

$query = getReportQuery($store_id_str, $start_date, $end_date);
$rows = $db->fetchObjectArray($query);

// Set headers for CSV download
header("Content-type: text/csv");
header("Content-Disposition: attachment; filename=phonewisebills.csv");
header("Pragma: no-cache");
header("Expires: 0");

// Output CSV headers
$headers = array(
    "Store", "Mobile No", "Count", "Bill No", "Net Total"
);
echo implode(",", $headers) . "\n";

// Output each row
foreach ($rows as $row) {
    $line = array(
        $row->store_name,
        $row->cust_phone,
        $row->loyalty_count,
        $row->bill_no,
        $row->sub_total,
        
    );
    echo implode(",", array_map(function($v) {
        return '"' . str_replace('"', '""', $v) . '"';
    }, $line)) . "\n";
}
?>
