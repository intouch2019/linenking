<?php

include "../appConfig.php";
require_once "store/session_check.php";
include "ofc/php-ofc-library/open-flash-chart.php";
require_once "lib/db/DBConn.php";
require_once "lib/logger/clsLogger.php";
require_once "chartProperties.php";

$d1Clause="";
if (isset($_GET['d1'])) {
$d1Clause = " and to_days(bill_datetime) >= ".$_GET['d1']." ";
}
$d2Clause="";
if (isset($_GET['d2'])) {
$d2Clause = " and to_days(bill_datetime) <= ".$_GET['d2']." ";
}
$type=1; // 1=numOrders, 2=revenue
if (isset($_GET['type'])) { $type=$_GET['type']; }

$logger = new clsLogger();
$currStore = getCurrStore();
if (!$currStore) { print "User session timedout. Please login again"; return; }
if ($currStore->id != 20) { print "Unauthorized Access"; return; }
$storeid=$_GET['id'];
try {
	$db = new DBConn();
	$query = "select date(bill_datetime) as bdate, count(*) as numOrders, sum(bill_quantity) as totalQuantity, round(sum(bill_amount)) as totalAmount, round(sum(bill_amount)/sum(bill_quantity)) as avgPrice from it_orders where storeid=$storeid and inactive=0 and status>0 $d1Clause $d2Clause group by bdate order by bdate";
	$objs = $db->fetchObjectArray($query);
	$db->closeConnection();
} catch (Exception $xcp) {
	$logger->logException($xcp);
	print "Unexpected error occurred. Please try again later";
	return;
}

$pValues = array();
$ymin = 200000000; $ymax = 0;
$count=0;
$total = 0;
$d1 = null;
$d2 = null;
foreach ($objs as $obj) {
	if ($d1 == null) { $d1 = $obj->bdate; }
	$d2 = $obj->bdate;
	if ($type == 1) {
		if ($obj->numOrders < $ymin) { $ymin = $obj->numOrders; }
		if ($obj->numOrders > $ymax) { $ymax = $obj->numOrders; }
		$total += $obj->numOrders;
		$tmp = new bar_value(intVal($obj->numOrders));
		$tmp->set_tooltip( "#val# orders<br>".$obj->bdate );
		$tmp->set_colour(prop_getcolor($count));
	} else {
		if ($obj->totalAmount < $ymin) { $ymin = $obj->totalAmount; }
		if ($obj->totalAmount > $ymax) { $ymax = $obj->totalAmount; }
		$total += $obj->totalAmount;
		$tmp = new bar_value(intVal($obj->totalAmount));
		$tmp->set_tooltip( "Rs. #val#<br>".$obj->bdate );
		$tmp->set_colour(prop_getcolor($count));
	}
	$tmp->set_on_click( "daily_orders('".$obj->bdate."')" );
	$pValues[] = $tmp;
	$count++; if ($count == 7) $count = 0;
}

if ($type == 2) { $total = "Rs. $total"; }
$title = new title("Store $storeid: Total $total, Start $d1, End $d2");

$bar = new bar_glass();
$bar->set_values( $pValues );

$chart = new open_flash_chart();
$chart->set_title( $title );
$chart->add_element( $bar );

$chart->set_y_axis(create_y_axis($ymin,$ymax));

echo $chart->toString();

?>
