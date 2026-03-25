<?php
include '/var/www/html/linenking/it_config.php';
require_once 'lib/db/DBConn.php';
require_once 'lib/core/Constants.php';
require_once "Classes/PHPExcel.php";
require_once 'Classes/PHPExcel/Writer/Excel2007.php';
require_once "lib/email/EmailHelper.php";

$start_date = date('Y-m-d H:i:s');
echo "<br>Execution start...<br> datetime: ".$start_date."<br>";
//Cron Frequency Once in Month on day 1st

try {
    $db = new DBConn();
    $cnt = 0;
    
// Get current date
$now = new DateTime();

// Go to the first day of the current month (e.g., July)
$now->modify('first day of this month');

// Calculate the start of the 3-month range (e.g., 1st April)
$from = clone $now;
$from->modify('-3 months'); // Go back 3 months
$from_dt = $from->format('Y-m-01 00:00:00'); // First day of that month

// Calculate the end of last month (e.g., 30th June)
$to = clone $now;
$to->modify('-1 day'); // Last day of previous month
$to_dt = $to->format('Y-m-d 23:59:59'); // End of that day

// Output example
//echo "From: $from_dt\n";
//echo "To: $to_dt\n";
//exit();
    //<------------       Sale Data of last 3 months insertion       ------------>
    
    $query = "SELECT c.id as store_id,c.store_name, ic.id as category_id,sy.id as style_id,sz.id as size_id,ic.name as category, sy.name as style, sz.name as size , SUM(CASE WHEN (o.tickettype IN (0, 1, 6)) THEN oi.quantity ELSE 0 END) AS quantity, SUM(CASE WHEN (o.discount_pct IS NOT NULL) THEN ((((100 - o.discount_pct) / 100) * oi.price) * (CASE WHEN (o.tickettype IN (0, 1, 6)) THEN oi.quantity ELSE 0 END)) ELSE oi.price * (CASE WHEN (o.tickettype IN (0, 1, 6)) THEN oi.quantity ELSE 0 END) END) AS totalvalue FROM it_orders o JOIN it_order_items oi ON oi.order_id = o.id JOIN it_items i ON i.id = oi.item_id JOIN it_sizes sz ON sz.id = i.size_id JOIN it_codes c ON o.store_id = c.id join it_categories ic on i.ctg_id = ic.id join it_styles sy on sy.id = i.style_id WHERE o.store_id IN (SELECT id FROM it_codes WHERE usertype = 4) AND o.bill_datetime between '$from_dt' and '$to_dt' GROUP BY c.id,ic.name,sy.name, i.size_id ORDER BY ic.name,sz.name";
//    echo $query; exit();
    
    $saleObjs = $db->fetchObjectArray($query);
//    echo '<pre>'; print_r($saleObjs); echo '</pre>'; exit();
    
    if (isset($saleObjs) && !empty($saleObjs)) {
        
        //Truncate Table to remove old data and insert new data everytime
        $qry = "truncate table it_store_threemonthsale_summary";
        $db->execQuery($qry);
    
        foreach ($saleObjs as $sobj) {

            $iquery = "insert into it_store_threemonthsale_summary set store_id=$sobj->store_id , store_name='$sobj->store_name', category_id=$sobj->category_id,style_id=$sobj->style_id, size_id=$sobj->size_id, category='$sobj->category', style='$sobj->style', size='$sobj->size', quantity=$sobj->quantity, totalvalue=$sobj->totalvalue";
            $db->execInsert($iquery);
//        print "\n$iquery"; exit();

            $cnt++;
        }
    }
    
$end_date = date('Y-m-d H:i:s');
echo "Execution end.<br> datetime: ".$end_date;
echo '<br>';

} catch (Exception $xcp) {
    print $xcp->getMessage();
}

print "Tot_rows inserted: ".$cnt;