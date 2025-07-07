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
    return "SELECT c.store_name, o.bill_no,o.createtime, o.bill_datetime AS date, o.tickettype, CASE WHEN o.tickettype = 3 THEN 'Cancelled' WHEN bt.has_negative_qty = 1 THEN CASE WHEN bt.has_discount = 1 THEN 'Discount' ELSE 'Credit Note' END WHEN bt.has_discount = 1 THEN 'Loyalty Discount' ELSE 'Sale' END AS transaction, oi.barcode, i.MRP, IFNULL(oi.discount_val, 0.0) AS itmdiscv, CASE WHEN o.tickettype = 1 THEN ABS(oi.quantity) ELSE oi.quantity END AS quantity, CASE WHEN ict.tax_rate IS NOT NULL THEN ict.tax_rate * 100 WHEN i.MRP > 1050 THEN 12 ELSE 5 END AS tax_rate, ((i.MRP / (100 + CASE WHEN ict.tax_rate IS NOT NULL THEN ict.tax_rate * 100 WHEN i.MRP > 1050 THEN 12 ELSE 5 END)) * CASE WHEN ict.tax_rate IS NOT NULL THEN ict.tax_rate * 100 WHEN i.MRP > 1050 THEN 12 ELSE 5 END) AS mrptaxperitem, CASE WHEN ict.tax_rate IS NOT NULL THEN ict.tax_rate * 100 WHEN ( CASE WHEN o.discount_pct IS NOT NULL THEN (((100 - o.discount_pct) / 100) * oi.price) * CASE WHEN o.tickettype = 1 THEN ABS(oi.quantity) ELSE oi.quantity END ELSE oi.price * CASE WHEN o.tickettype = 1 THEN ABS(oi.quantity) ELSE oi.quantity END END ) > 1050 THEN 12 ELSE 5 END AS taxrateperitemaspersalesvalue, IFNULL(( CASE WHEN o.discount_pct IS NOT NULL THEN (((100 - o.discount_pct) / 100) * oi.price) * CASE WHEN o.tickettype = 1 THEN ABS(oi.quantity) ELSE oi.quantity END ELSE oi.price * CASE WHEN o.tickettype = 1 THEN ABS(oi.quantity) ELSE oi.quantity END END ), 0.0) AS totalvalue, IFNULL(( ( CASE WHEN o.discount_pct IS NOT NULL THEN (((100 - o.discount_pct) / 100) * oi.price) * CASE WHEN o.tickettype = 1 THEN ABS(oi.quantity) ELSE oi.quantity END ELSE oi.price * CASE WHEN o.tickettype = 1 THEN ABS(oi.quantity) ELSE oi.quantity END END ) / (100 + CASE WHEN ict.tax_rate IS NOT NULL THEN ict.tax_rate * 100 WHEN ( CASE WHEN o.discount_pct IS NOT NULL THEN (((100 - o.discount_pct) / 100) * oi.price) * CASE WHEN o.tickettype = 1 THEN ABS(oi.quantity) ELSE oi.quantity END ELSE oi.price * CASE WHEN o.tickettype = 1 THEN ABS(oi.quantity) ELSE oi.quantity END END ) > 1050 THEN 12 ELSE 5 END ) * CASE WHEN ict.tax_rate IS NOT NULL THEN ict.tax_rate * 100 WHEN ( CASE WHEN o.discount_pct IS NOT NULL THEN (((100 - o.discount_pct) / 100) * oi.price) * CASE WHEN o.tickettype = 1 THEN ABS(oi.quantity) ELSE oi.quantity END ELSE oi.price * CASE WHEN o.tickettype = 1 THEN ABS(oi.quantity) ELSE oi.quantity END END ) > 1050 THEN 12 ELSE 5 END ), 0.0) AS taxperitemaspersalevalue, CASE WHEN ( CASE WHEN ict.tax_rate IS NOT NULL THEN ict.tax_rate * 100 WHEN i.MRP > 1050 THEN 12 ELSE 5 END ) = ( CASE WHEN ict.tax_rate IS NOT NULL THEN ict.tax_rate * 100 WHEN ( CASE WHEN o.discount_pct IS NOT NULL THEN (((100 - o.discount_pct) / 100) * oi.price) * CASE WHEN o.tickettype = 1 THEN ABS(oi.quantity) ELSE oi.quantity END ELSE oi.price * CASE WHEN o.tickettype = 1 THEN ABS(oi.quantity) ELSE oi.quantity END END ) > 1050 THEN 12 ELSE 5 END ) THEN 'NO' ELSE 'YES' END AS taxslabchange,"
                    . " IFNULL(( ( ( i.MRP / (100 + CASE WHEN ict.tax_rate IS NOT NULL THEN ict.tax_rate * 100 WHEN i.MRP > 1050 THEN 12 ELSE 5 END ) * CASE WHEN ict.tax_rate IS NOT NULL THEN ict.tax_rate * 100 WHEN i.MRP > 1050 THEN 12 ELSE 5 END ) - ( ( CASE WHEN o.discount_pct IS NOT NULL THEN (((100 - o.discount_pct) / 100) * oi.price) * CASE WHEN o.tickettype = 1 THEN ABS(oi.quantity) ELSE oi.quantity END ELSE oi.price * CASE WHEN o.tickettype = 1 THEN ABS(oi.quantity) ELSE oi.quantity END END ) / (100 + CASE WHEN ict.tax_rate IS NOT NULL THEN ict.tax_rate * 100 WHEN ( CASE WHEN o.discount_pct IS NOT NULL THEN (((100 - o.discount_pct) / 100) * oi.price) * CASE WHEN o.tickettype = 1 THEN ABS(oi.quantity) ELSE oi.quantity END ELSE oi.price * CASE WHEN o.tickettype = 1 THEN ABS(oi.quantity) ELSE oi.quantity END END ) > 1050 THEN 12 ELSE 5 END ) * CASE WHEN ict.tax_rate IS NOT NULL THEN ict.tax_rate * 100 WHEN ( CASE WHEN o.discount_pct IS NOT NULL THEN (((100 - o.discount_pct) / 100) * oi.price) * CASE WHEN o.tickettype = 1 THEN ABS(oi.quantity) ELSE oi.quantity END ELSE oi.price * CASE WHEN o.tickettype = 1 THEN ABS(oi.quantity) ELSE oi.quantity END END ) > 1050 THEN 12 ELSE 5 END ) ) ), 0.0) AS taxdifference FROM it_orders o JOIN it_order_items oi ON oi.order_id = o.id JOIN it_items i ON i.id = oi.item_id JOIN it_codes c ON o.store_id = c.id JOIN states s ON s.id = c.state_id JOIN region r ON c.region_id = r.id LEFT JOIN it_category_taxes ict ON ict.category_id = i.ctg_id JOIN ( SELECT o.id AS order_id, o.tickettype, MAX(CASE WHEN oi.quantity < 0 THEN 1 ELSE 0 END) AS has_negative_qty, MAX(CASE WHEN IFNULL(oi.discount_val, 0.0) > 0 THEN 1 ELSE 0 END) AS has_discount FROM it_orders o JOIN it_order_items oi ON oi.order_id = o.id JOIN it_order_payments p ON p.order_id = o.id AND TRIM(p.payment_name) = 'Loyalty'"
                    . " WHERE o.tickettype = 0 AND o.store_id IN ($store_id_str) AND o.bill_datetime BETWEEN '$from_dt' AND '$to_dt' GROUP BY o.id, o.tickettype ) AS bt ON bt.order_id = o.id "
                    . "WHERE o.store_id IN ($store_id_str) AND o.bill_datetime BETWEEN '$from_dt' AND '$to_dt' ORDER BY o.bill_datetime,o.bill_no, oi.barcode";
}

$query = getReportQuery($store_id_str, $start_date, $end_date);
$rows = $db->fetchObjectArray($query);

// Set headers for CSV download
header("Content-type: text/csv");
header("Content-Disposition: attachment; filename=SalesTaxReport.csv");
header("Pragma: no-cache");
header("Expires: 0");

// Output CSV headers
$headers = array(
    "Store", "Bill No", "Date", "Ticket Type", "Barcode", "MRP", "Item Disc Value", "Qty",
    "Tax Rate", "MRP Tax per Item", "Tax Rate (Sales Value)", "Total Value",
    "Tax per Item (Sales Value)", "Tax Slab Changed?", "Tax Difference","create time"
);
echo implode(",", $headers) . "\n";

// Output each row
foreach ($rows as $row) {
    $line = array(
        $row->store_name,
        $row->bill_no,
        $row->date,
        $row->transaction,
        $row->barcode,
        formatSmart($row->MRP),
        formatSmart($row->itmdiscv),
        formatSmart($row->quantity),
        formatSmart($row->tax_rate) . "%",
        formatSmart($row->mrptaxperitem),
        formatSmart($row->taxrateperitemaspersalesvalue) . "%",
        formatSmart($row->totalvalue),
        formatSmart($row->taxperitemaspersalevalue),
        $row->taxslabchange,
        formatSmart($row->taxdifference),
        $row->createtime
    );
    echo implode(",", array_map(function($v) {
        return '"' . str_replace('"', '""', $v) . '"';
    }, $line)) . "\n";
}
?>
