<?php

include "checkAccess.php";
require_once "../../it_config.php";
require_once "lib/logger/clsLogger.php";
require_once "lib/db/DBConn.php";

extract($_POST);
$logger = new clsLogger();

if (!isset($records) || trim($records) == "") {
    $logger->logError("CK:Missing parameter [records]:" . print_r($_POST, true));
    print "Missing parameter [records]";
    return;
}
$db = new DBConn();
$arr = explode("||", $records);
foreach ($arr as $record) {
    if (trim($record) == "") {
        continue;
    }
    $fields = explode("<>", $record);
    $invoiceNo = $db->safe($fields[0]);
    $salebackPulled = $db->safe($fields[1]);
    $storeId = $db->safe($fields[2]);
    $isSBTransitComplete = $db->safe($fields[3]);

    $updateQuery = "update it_saleback_invoices set is_sb_transit_complete=1 where invoice_no= $invoiceNo and store_id=$storeId and is_sb_transit_complete=$isSBTransitComplete ";
    $updateStatus = $db->execUpdate($updateQuery);
}
$db->closeConnection();
print "0::Success";
?>