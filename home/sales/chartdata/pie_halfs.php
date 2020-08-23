<?php

include "../appConfig.php";
require_once "store/session_check.php";
include "ofc/php-ofc-library/open-flash-chart.php";
require_once "lib/db/DBConn.php";
require_once "lib/logger/clsLogger.php";
require_once "chartProperties.php";

$title = "By Day Halfs";
$d1Clause="";
if (isset($_GET['d1'])) {
$d1Clause = " and date(bill_datetime) >= '".$_GET['d1']."' ";
}
$d2Clause="";
if (isset($_GET['d2'])) {
$d2Clause = " and date(bill_datetime) <= '".$_GET['d2']."' ";
}
if (isset($_GET['d1']) && isset($_GET['d2'])) { $_SESSION['daterange']=$_GET['d1'].",".$_GET['d2']; }
$type=1; // 1=numOrders, 2=revenue
if (isset($_GET['type'])) { $type=$_GET['type']; }

$logger = new clsLogger();
$currStore = getCurrStore();
if (!$currStore) { print "User session timedout. Please login again"; return; }
try {
	$db = new DBConn();
	$query = "select count(*) as numOrders, round(sum(bill_amount)) as totalAmount from it_orders where storeid=$currStore->id and inactive=0 and status>0 and time(bill_datetime) < '17:00:00' $d1Clause $d2Clause";
	$obj = $db->fetchObject($query);
	$half1Amount = intVal($obj->totalAmount); $half1Orders = intVal($obj->numOrders);
	$query = "select count(*) as numOrders, round(sum(bill_amount)) as totalAmount from it_orders where storeid=$currStore->id and inactive=0 and status>0 and time(bill_datetime) >= '17:00:00' $d1Clause $d2Clause";
	$obj = $db->fetchObject($query);
	$half2Amount = intVal($obj->totalAmount); $half2Orders = intVal($obj->numOrders);
	$db->closeConnection();
} catch (Exception $xcp) {
	$logger->logException($xcp);
	print "Unexpected error occurred. Please try again later";
	return;
}

$title = new title( $title );

$pie = new pie();
$pie->set_alpha(0.6);
$pie->set_start_angle( 35 );
//$pie->radius(70);
$pie->add_animation( new pie_fade() );
$pie->set_tooltip( '#val# of #total#<br>#percent# of 100%' );
$pie->set_colours( array(prop_getcolor(5), prop_getcolor(6)) );
if ($type == 1) {
$pie->set_values( array(new pie_value($half1Orders, "Before 5pm\n$half1Orders sales"),new pie_value($half2Orders, "After 5pm\n$half2Orders sales")) );
} else {
$pie->set_values( array(new pie_value($half1Amount, "Before 5pm\nRs. $half1Amount"),new pie_value($half2Amount, "After 5pm\nRs. $half2Amount")) );
}

$chart = new open_flash_chart();
$chart->set_title( $title );
$chart->add_element( $pie );


$chart->x_axis = null;

echo $chart->toPrettyString();
?>
