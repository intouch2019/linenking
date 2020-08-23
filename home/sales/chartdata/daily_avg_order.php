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
	$query = "select date(bill_datetime) as bdate, count(*) as numOrders, sum(bill_quantity) as totalQuantity, round(sum(bill_amount)) as totalAmount, round(sum(bill_amount)/sum(bill_quantity)) as avgPrice from it_orders where storeid=$currStore->id and inactive=0 and status>0 $d1Clause $d2Clause group by bdate order by bdate";
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
foreach ($objs as $obj) {
	if ($type == 1) {
		if ($obj->numOrders < $ymin) { $ymin = $obj->numOrders; }
		if ($obj->numOrders > $ymax) { $ymax = $obj->numOrders; }
		$tmp = new bar_value(intVal($obj->numOrders));
		$tmp->set_tooltip( "#val# sales<br>".$obj->bdate );
		$tmp->set_colour(prop_getcolor($count));
	} else {
		if ($obj->totalAmount < $ymin) { $ymin = $obj->totalAmount; }
		if ($obj->totalAmount > $ymax) { $ymax = $obj->totalAmount; }
		$tmp = new bar_value(intVal($obj->totalAmount));
		$tmp->set_tooltip( "Rs. #val#<br>".$obj->bdate );
		$tmp->set_colour(prop_getcolor($count));
	}
	$tmp->set_on_click( "daily_orders('".$obj->bdate."')" );
	$pValues[] = $tmp;
	$count++; if ($count == 7) $count = 0;
}

$title = new title("A:Daily Statistics");

$bar = new bar_glass();
$bar->set_values( $pValues );

$chart = new open_flash_chart();
$chart->set_title( $title );
$chart->add_element( $bar );

$chart->set_y_axis(create_y_axis($ymin,$ymax));

/* BEGIN set invisible x axis labels */
$x_labels = new x_axis_labels();
$x_labels->set_steps( 1 );
$x_labels->set_colour( '#FFFFFF' );
$x_labels->set_labels( array() );
$x = new x_axis();
$x->set_colour( '#A2ACBA' );
$x->set_grid_colour( '#D7E4A3' );
$x->set_offset( false );
// Add the X Axis Labels to the X Axis
$x->set_labels( $x_labels );
$chart->set_x_axis( $x );
/* END set invisible x axis labels */

echo $chart->toString();

?>
