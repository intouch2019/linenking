<?php
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";

$db = new DBConn();

$input = file_get_contents("php://input");
$data = json_decode($input, true);

$table_name = isset($data['table_name']) ? trim($data['table_name']) : '';
$conditions = isset($data['conditions']) ? trim($data['conditions']) : '';
$rowstartat = isset($data['rowstartat']) ? intval($data['rowstartat']) : 0;
$orderby = isset($data['orderby']) ? strtoupper(trim($data['orderby'])) : 'ASC';
$api_key = isset($data['api_key']) ? trim($data['api_key']) : '';
$limit = 3000; // hard limit
if ($table_name == 'it_invoices') {
    $limit = 500; // these carry longtext columns — smaller batches avoid memory blowout
}

header('Content-Type: application/json');

// Basic validation
if ($api_key == '' || $table_name == '' || $rowstartat < 0) {
    echo json_encode(array(
        "status" => "error",
        "message" => "Missing or invalid parameters",
        "required" => array("api_key", "table_name", "rowstartat")
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
$table_check_query = "SHOW TABLES LIKE '"  . $table_name . "'";
$tableObj = $db->fetchObject($table_check_query);
if (!$tableObj) {
    echo json_encode(array(
        "status" => "error",
        "message" => "Table does not exist"
    ));
    exit;
}

// Validate ORDER BY
if ($orderby != 'ASC' && $orderby != 'DESC') {
    $orderby = 'ASC';
}

if($table_name=="it_items"){
    $query="SELECT i.*, c.name AS category_name, s.name AS style_name, sz.name AS size_name, pt.name AS product_type_name, d.design_no FROM it_items i LEFT JOIN it_categories c ON i.ctg_id = c.id LEFT JOIN it_styles s ON i.style_id = s.id LEFT JOIN it_sizes sz ON i.size_id = sz.id LEFT JOIN it_prod_types pt ON i.prod_type_id = pt.id LEFT JOIN it_ck_designs d ON i.design_id = d.id ";
}else if($table_name=="it_codes"){
$query = "SELECT ic.id as maintenance_store_id ,ic.code, ic.store_name, ic.sequence, ic.tally_name, ic.accountinfo, ic.owner, ic.address, ic.city, ic.zipcode, ic.phone, ic.phone2, ic.email, ic.email2, ic.gstin_no, ic.store_number, ic.pancard_no, ic.min_stock_level, ic.max_stock_level, CASE ic.tax_type WHEN 1 THEN 'Inside Maharashtra' WHEN 2 THEN 'Outside Maharashtra' ELSE 'NA' END AS tax_type, CASE ic.is_autorefill WHEN 1 THEN 'YES' WHEN 0 THEN 'NO' ELSE 'NA' END AS is_autorefill, CASE ic.is_tallyxml WHEN 1 THEN 'YES' WHEN 0 THEN 'NO' ELSE 'NA' END AS is_tallyxml, ic.retail_saletally_name, ic.retail_sale_card_name, ic.retail_sale_cash_name, ic.retail_sale_upi_name, ic.retail_sale_bank_name, ic.cash_receipt_name, CASE ic.is_bhmtallyxml WHEN 1 THEN 'YES' WHEN 0 THEN 'NO' ELSE 'NA' END AS is_bhmtallyxml, ic.autorefil_dttm, CASE ic.is_closed WHEN 1 THEN 'YES' WHEN 0 THEN 'NO' ELSE 'NA' END AS is_closed, ic.inactivated_by, ic.inactivating_reason, ic.isastore, ic.disablelogins_reason, ic.inactive_dttm, ic.loginsdisable_by, ic.disablelogins_dttm, ic.distance, s.state AS state_id, ic.cust_tobe_debited, ic.UMRN, ic.cust_debit_account, ic.cust_ifsc_or_mcr, ic.Area, CASE ic.is_natch_required WHEN 1 THEN 'YES' WHEN 0 THEN 'NO' ELSE 'NA' END AS is_natch_required, ic.discountset, ic.Location, ic.ds_taxable_amt, ic.remark, CASE ic.composite_billing_opted WHEN 1 THEN 'YES' WHEN 0 THEN 'NO' ELSE 'NA' END AS composite_billing_opted, ic.ds_remark, CASE ic.status WHEN 1 THEN 'L to L' WHEN 2 THEN 'New L to L' WHEN 3 THEN 'New' WHEN 4 THEN 'Closed' ELSE 'NA' END AS status, r.region AS region_id, CASE ic.mask_margin WHEN 1 THEN 'YES' WHEN 0 THEN 'NO' ELSE 'NA' END AS mask_margin, ic.emandate_msgid, ic.upi_name, ic.upi_id, ic.paymentlink,  ic.level, CASE ic.sbstock_active WHEN 1 THEN 'YES' WHEN 0 THEN 'NO' ELSE 'NA' END AS sbstock_active, ic.carpet, ic.monthlyrent, ic.facade, ic.inactive_bydatasync, ic.nach_limit, ic.saleback_starttime, ic.cust_bank_name,  ic.saleback_endtime, old_store.code AS old_id, ic.inactive,  CASE ic.store_type WHEN 1 THEN 'NormalStore' WHEN 2 THEN 'Store50percent' WHEN 3 THEN 'CompanyStore' ELSE 'NA' END AS store_type FROM it_codes ic LEFT JOIN states s ON s.id = ic.state_id LEFT JOIN region r ON r.id = ic.region_id LEFT JOIN it_codes old_store ON old_store.id = ic.old_id
WHERE ic.usertype = 4";
}else if($table_name=="it_current_stock"){
$query = "SELECT ic.store_id,ic.quantity,ic.barcode FROM it_current_stock ic JOIN it_codes c ON ic.store_id = c.id WHERE c.is_closed = 0 AND c.usertype = 4 AND ic.quantity > 0 AND ic.barcode LIKE '890000%'";
}else if($table_name=="it_store_ratios"){
$query = "SELECT sr.store_id, c.name AS category, d.design_no AS design, st.name AS style, sz.name AS size, sr.mrp, CASE sr.ratio_type WHEN 1 THEN 'STANDING_RATIO' WHEN 2 THEN 'BASE_RATIO' ELSE 'UNKNOWN' END AS ratio_type, sr.ratio, sr.updated_by, CASE WHEN sr.is_exceptional = 1 THEN 'YES' ELSE 'NO' END AS is_exceptional, CASE WHEN sr.is_exceptional_active = 1 THEN 'YES' ELSE 'NO' END AS is_exceptional_active, CASE WHEN sr.core = 1 THEN 'YES' ELSE 'NO' END AS core, sr.createtime, sr.updatetime FROM it_store_ratios sr LEFT JOIN it_categories c ON sr.ctg_id = c.id LEFT JOIN it_ck_designs d ON sr.design_id = d.id LEFT JOIN it_styles st ON sr.style_id = st.id LEFT JOIN it_sizes sz ON sr.size_id = sz.id";
}else if($table_name=="stock_master_qty_wise"){
$query ="SELECT sm.store_id, c.name AS category, sm.design_id , st.name AS style, sz.name AS size, sm.min_qty_allowed FROM stock_master_qty_wise sm INNER JOIN it_categories c ON sm.category_id = c.id INNER JOIN it_styles st ON sm.style_id = st.id INNER JOIN it_sizes sz ON sm.size_id = sz.id LEFT JOIN it_ck_designs d ON sm.design_id = d.id";
}else if($table_name=="it_ck_orders"){
    $query = "SELECT io.barcode, oi.order_qty AS item_order_qty, o.* FROM it_ck_orders o INNER JOIN it_ck_orderitems oi ON o.id = oi.order_id INNER JOIN it_items io ON oi.item_id = io.id WHERE o.createtime > '2026-04-01 00:00:00'";
}else if($table_name=="it_saleback_invoices"){
    // invoice_text (POS blob, 1-20KB+/row) intentionally excluded — ERP sync doesn't need it
    $query = "SELECT id, invoice_no, invoice_dt, invoice_type, invoice_amt, invoice_qty, total_mrp, tax, store_id, is_procsdForRetail, procsd_date, rate_subtotal, discount_val, total_taxable_value, cgst_total, sgst_total, igst_total, round_off, is_sb_transit_complete, utr, remark, createtime, updatetime FROM it_saleback_invoices";
}else if($table_name=="it_ck_pickgroup"){
    $query = "SELECT pg.id, pg.storeid, ic.code AS store_code, pg.order_ids, pg.order_nos, pg.order_qty, pg.order_amount, pg.num_designs, pg.dispatcher_id, pg.invoice_no, pg.shipped_qty, pg.shipped_mrp, pg.cheque_amt, pg.cheque_dtl, pg.cheque_print, pg.transport_dtl, pg.picker_id, pg.active_time, pg.picking_time, pg.pickingComplete_time, pg.printing_time, pg.shipped_time, pg.remark, pg.createtime FROM it_ck_pickgroup pg LEFT JOIN it_codes ic ON ic.id = pg.storeid";

}else if($table_name=="it_invoice_items"){
    $query = "SELECT ii.*, COALESCE(io.barcode, io2.barcode, ii.item_code) AS item_barcode FROM it_invoice_items ii LEFT JOIN it_items io ON ii.item_code = io.id LEFT JOIN it_items io2 ON ii.item_code = io2.barcode";
}else if($table_name=="it_invoices"){
   $query = "SELECT i.* FROM it_invoices i ";
}else{
// Build query safely
$query = "SELECT * FROM `" .$table_name . "`";
}

// Conditions validation
if ($conditions != '') {
    // Very strict whitelist: only allow column names, numbers, quotes, spaces, =, <, >, %, _, AND, OR
    // Disallow any semicolons, comments, or SQL keywords
    if (preg_match("/[;#\/\\\\]/", $conditions) || 
        preg_match("/\b(INSERT|UPDATE|DELETE|DROP|ALTER|TRUNCATE|CREATE|GRANT|REVOKE|EXEC|UNION)\b/i", $conditions)) {
        echo json_encode(array(
            "status" => "error",
            "message" => "Invalid characters or keywords in conditions"
        ));
        exit;
    }

    // Allowable characters: a-z, A-Z, 0-9, space, quotes, %, _, =, <, >, AND, OR
    if (!preg_match("/^[a-zA-Z0-9_ =<>%'.\"-ANDOR]+$/i", $conditions)) {
        echo json_encode(array(
            "status" => "error",
            "message" => "Invalid characters in conditions"
        ));
        exit;
    }

if($table_name=="it_ck_orders" || $table_name=="it_codes" || $table_name=="it_current_stock"){
    $query .= " AND $conditions";
}else{
    $query .= " WHERE $conditions";
}
}

// Append ORDER BY and LIMIT
if($table_name=="it_ck_orders"){
    $query .= " ORDER BY o.id $orderby LIMIT $rowstartat, $limit";
}else if($table_name=="it_ck_pickgroup"){
    $query .= " ORDER BY pg.id $orderby LIMIT $rowstartat, $limit";
}else if($table_name=="it_invoice_items"){
    $query .= " ORDER BY ii.id $orderby LIMIT $rowstartat, $limit";
}else if($table_name=="it_invoices"){
    $query .= " ORDER BY i.id $orderby LIMIT $rowstartat, $limit";
}else if($table_name!="it_codes" && $table_name!="it_current_stock" && $table_name!="it_store_ratios" && $table_name!="stock_master_qty_wise"){
    $query .= " ORDER BY id $orderby LIMIT $rowstartat, $limit";
}

if($table_name == "it_store_ratios" || $table_name == "it_current_stock" ||  $table_name == "stock_master_qty_wise") {
    $query .= " LIMIT $rowstartat, $limit";
}
try {
    $rows = $db->fetchObjectArray($query);

if($table_name=="it_ck_orders" || $table_name=="it_ck_pickgroup" || $table_name=="it_invoice_items"){
    $rowcnt = 0;
}else{
    $cntquery = "SELECT COUNT(*) as rowcnt FROM `" . $table_name . "`";
    if ($conditions != '') {
        $cntquery .= " WHERE $conditions";
    }
    $rowcntObj = $db->fetchObject($cntquery);
    $rowcnt = $rowcntObj ? intval($rowcntObj->rowcnt) : 0;
}

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
} catch (Exception $ex) {
    echo json_encode(array(
        "status" => "error",
        "message" => "Query execution failed",
        "error" => $ex->getMessage()
    ));
}
?>
