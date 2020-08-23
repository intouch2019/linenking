<?php

include "../appConfig.php";
require_once "store/session_check.php";
include "ofc/php-ofc-library/open-flash-chart.php";
require_once "lib/db/DBConn.php";
require_once "lib/logger/clsLogger.php";
require_once "chartProperties.php";

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
	$query = "select hour(bill_datetime) as hour, count(*) as numOrders, round(sum(bill_amount)) as totalAmount from it_orders where storeid=$currStore->id and inactive=0 and status>0 $d1Clause $d2Clause group by hour";
	$objs = $db->fetchObjectArray($query);
	$db->closeConnection();
} catch (Exception $xcp) {
	$logger->logException($xcp);
	print "Unexpected error occurred. Please try again later";
	return;
}

$totalAmounts = array();
$labels = array();
$pValues = array();
$ymin = 200000000; $ymax = 0;
foreach ($objs as $obj) {
	if ($type == 1) {
		if ($obj->numOrders < $ymin) { $ymin = $obj->numOrders; }
		if ($obj->numOrders > $ymax) { $ymax = $obj->numOrders; }
		$tmp = new bar_value(intval($obj->numOrders));
	} else {
		if ($obj->totalAmount < $ymin) { $ymin = $obj->totalAmount; }
		if ($obj->totalAmount > $ymax) { $ymax = $obj->totalAmount; }
		$tmp = new bar_value(intval($obj->totalAmount));
		$tmp->set_tooltip( "Rs. #val#<br>".$obj->bdate );
	}
	$tmp->set_colour( prop_getcolor(0) );
	$pValues[] = $tmp;
	$labels[] = getHourStr(intval($obj->hour));
}

function getHourStr($hour) {
	if ($hour < 12) { return $hour."am"; }
	else if ($hour == 12) { return "12pm"; }
	else { return ($hour-12)."pm"; }
}

$tstr = "Hourly Stats";
if ($type == 1) { $tstr .= " - By Number of Sales"; }
else { $tstr .= " - By Revenue"; }
$title = new title($tstr);

$bar = new bar_glass();
$bar->colour( prop_getcolor(0) );
$bar->set_values( $pValues );

$chart = new open_flash_chart();
$chart->set_title( $title );
$chart->add_element( $bar );

$x_labels = new x_axis_labels();
//$x_labels->set_steps( 2 );
//$x_labels->set_vertical();
$x_labels->set_colour( '#A2ACBA' );
$x_labels->set_labels( $labels );

$x = new x_axis();
$x->set_colour( '#A2ACBA' );
$x->set_grid_colour( '#D7E4A3' );
$x->set_offset( false );
//$x->set_steps(4);
// Add the X Axis Labels to the X Axis
$x->set_labels( $x_labels );

$chart->set_x_axis( $x );

$chart->set_y_axis( create_y_axis($ymin, $ymax) );

echo $chart->toString();

?>
