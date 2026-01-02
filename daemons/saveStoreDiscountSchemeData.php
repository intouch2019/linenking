<?php
include '/var/www/html/linenking/it_config.php';
require_once 'lib/db/DBConn.php';
require_once 'lib/core/Constants.php';
require_once "Classes/PHPExcel.php";
require_once 'Classes/PHPExcel/Writer/Excel2007.php';
require_once "lib/email/EmailHelper.php";

$start_date = date('Y-m-d H:i:s');
echo "<br>Execution start...<br> datetime: ".$start_date."<br>";

try {
    $db = new DBConn();
    $cnt = 0;

    $query = "select * from it_codes where usertype = " . UserType::Dealer;
    $all_stores = $db->fetchObjectArray($query);

    $store_ids = array_map(function ($store) {
        return (int) $store->id;
    }, $all_stores);

    $store_id_str = implode(",", array_map('intval', $store_ids)); // final sanitized comma-separated string
    // Get current date
    $now = new DateTime();

    // Go to the first day of the current month
    $now->modify('first day of this month');

    // Go back one month to get previous month
    $from = clone $now;
    $from->modify('-1 month'); // Start of previous month
    $from_dt = $from->format('Y-m-01 00:00:00');

    // Generate month_key in YYYYMM format
    $month_key = $from->format('Ym');

    // End of previous month
    $to = clone $from;
    $to->modify('last day of this month');
    $to_dt = $to->format('Y-m-d 23:59:59');

    // Result
//    echo "From: $from_dt\n";
//    echo "To: $to_dt\n";
//    exit();

    //<------------       Discount_scheme::loyalty_membership data insertion starts        ------------>

      $loyaltyquery = "SELECT c.id AS store_id,c.store_name,c.discountset, CONCAT( CASE WHEN ict.tax_rate IS NOT NULL THEN ict.tax_rate * 100 WHEN i.MRP > 2625 THEN 18 ELSE 5 END, '-', CASE WHEN ict.tax_rate IS NOT NULL THEN ict.tax_rate * 100 WHEN ( CASE WHEN o.discount_pct IS NOT NULL THEN (((100 - o.discount_pct) / 100) * oi.price) * CASE WHEN o.tickettype = 1 THEN ABS(oi.quantity) ELSE oi.quantity END ELSE oi.price * CASE WHEN o.tickettype = 1 THEN ABS(oi.quantity) ELSE oi.quantity END END ) > 2625 THEN 18 ELSE 5 END ) AS tax_combo, SUM(i.MRP) AS total_mrp, SUM(IFNULL(oi.discount_val, 0.0)) AS total_discount, SUM( CASE WHEN o.discount_pct IS NOT NULL THEN (((100 - o.discount_pct) / 100) * oi.price) * CASE WHEN o.tickettype = 1 THEN ABS(oi.quantity) ELSE oi.quantity END ELSE oi.price * CASE WHEN o.tickettype = 1 THEN ABS(oi.quantity) ELSE oi.quantity END END ) AS totalvalue FROM it_orders o JOIN it_order_items oi ON oi.order_id = o.id JOIN it_items i ON i.id = oi.item_id JOIN it_codes c ON o.store_id = c.id JOIN states s ON s.id = c.state_id JOIN region r ON c.region_id = r.id LEFT JOIN it_category_taxes ict ON ict.category_id = i.ctg_id JOIN ( SELECT o.id AS order_id, o.tickettype, MAX(CASE WHEN oi.quantity < 0 THEN 1 ELSE 0 END) AS has_negative_qty, MAX(CASE WHEN IFNULL(oi.discount_val, 0.0) > 0 THEN 1 ELSE 0 END) AS has_discount FROM it_orders o JOIN it_order_items oi ON oi.order_id = o.id JOIN it_order_payments p ON p.order_id = o.id AND TRIM(p.payment_name) = 'Loyalty' AND p.amount not between 599 and 601 WHERE o.tickettype = 0 AND o.store_id IN ($store_id_str) AND o.bill_datetime BETWEEN '$from_dt' AND '$to_dt' GROUP BY o.id, o.tickettype ) AS bt ON bt.order_id = o.id WHERE o.store_id IN ($store_id_str) AND o.bill_datetime BETWEEN '$from_dt' AND '$to_dt' GROUP BY c.id, tax_combo ORDER BY c.id, tax_combo;";
    
    $saleObjs = $db->fetchObjectArray($loyaltyquery);
//    echo '<pre>'; print_r($saleObjs); echo '</pre>'; exit();

    if (isset($saleObjs) && !empty($saleObjs)) {
        foreach ($saleObjs as $sobj) {

            $iquery = "insert into it_store_discountscheme_summary set store_id = $sobj->store_id, store_name = '$sobj->store_name', discountset=$sobj->discountset,tax_combo= '$sobj->tax_combo', total_mrp=$sobj->total_mrp, total_discount=" . round($sobj->total_discount) . ",totalvalue=" . round($sobj->totalvalue) . ",scheme_type=" . Discount_scheme::loyalty_membership . ",month_key=$month_key,createtime = now() ";
            $db->execInsert($iquery);
//        print "\n$iquery"; exit();

            $cnt++;
        }
    }
    //<------------       Discount_scheme::loyalty_membership data insertion ends        ------------>

    
$end_date = date('Y-m-d H:i:s');
echo "Execution end.<br> datetime: ".$end_date;
echo '<br>';

} catch (Exception $xcp) {
    print $xcp->getMessage();
}

print "Tot_rows inserted: ".$cnt;