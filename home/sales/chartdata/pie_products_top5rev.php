<?php

include "../appConfig.php";
require_once "store/session_check.php";
include "ofc/php-ofc-library/open-flash-chart.php";
require_once "lib/db/DBConn.php";
require_once "lib/logger/clsLogger.php";
require_once "chartProperties.php";

$byAmount=false;
if (isset($_GET['amount'])) { $amount = $_GET['amount']; }
else { $amount = 200; }
$d1Clause="";
if (isset($_GET['d1'])) {
$d1Clause = " and to_days(bill_datetime) >= ".$_GET['d1']." ";
}
$d2Clause="";
if (isset($_GET['d2'])) {
$d2Clause = " and to_days(bill_datetime) <= ".$_GET['d2']." ";
}
$logger = new clsLogger();
$currStore = getCurrStore();
if (!$currStore) { print "User session timedout. Please login again"; return; }
try {
	$db = new DBConn();
	$query = "select weekday(bill_datetime) as dayofweek, count(*) as numOrders, round(sum(bill_amount)) as totalAmount from it_orders where storeid=$currStore->id and inactive=0 and status>0 $d1Clause $d2Clause group by dayofweek";
	$objs = $db->fetchObjectArray($query);
	$pValues = array();
	$pColors = array(); $count=0;
	foreach ($objs as $obj) {
		$dayofweek = $prop_daynames[$obj->dayofweek];
		$pValues[] = new pie_value(intval($obj->totalAmount), "$dayofweek\n".$obj->numOrders." orders");
		$pColors[] = prop_getcolor($count); $count++;
	}
	$db->closeConnection();
} catch (Exception $xcp) {
	$logger->logException($xcp);
	print "Unexpected error occurred. Please try again later";
	return;
}

$title = new title( "By Day of Week" );

$pie = new pie();
$pie->set_alpha(0.6);
$pie->set_start_angle( 35 );
//$pie->radius(70);
$pie->add_animation( new pie_fade() );
$pie->set_tooltip( '#val# of #total#<br>#percent# of 100%' );
$pie->set_colours( $pColors );
$pie->set_values( $pValues );

$chart = new open_flash_chart();
$chart->set_title( $title );
$chart->add_element( $pie );


$chart->x_axis = null;

echo $chart->toPrettyString();
?>
