<?php

include "../appConfig.php";
require_once "store/session_check.php";
include "ofc/php-ofc-library/open-flash-chart.php";
require_once "lib/db/DBConn.php";
require_once "lib/logger/clsLogger.php";
require_once "chartProperties.php";

$byAmount=false;
if (isset($_GET['amount'])) { $amount = $_GET['amount']; }
else { $amount = 500; }
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
	$query = "select count(*) as numOrders, round(sum(bill_amount)) as totalAmount from it_orders where storeid=$currStore->id and inactive=0 and status>0 and bill_amount < $amount $d1Clause $d2Clause";
	$obj = $db->fetchObject($query);
	$belowAmount = intVal($obj->totalAmount); $belowOrders = intVal($obj->numOrders);
	$query = "select count(*) as numOrders, round(sum(bill_amount)) as totalAmount from it_orders where storeid=$currStore->id and inactive=0 and status>0 and bill_amount >= $amount $d1Clause $d2Clause";
	$obj = $db->fetchObject($query);
	$aboveAmount = intVal($obj->totalAmount); $aboveOrders = intVal($obj->numOrders);
	$db->closeConnection();
} catch (Exception $xcp) {
	$logger->logException($xcp);
	print "Unexpected error occurred. Please try again later";
	return;
}

$title = new title( "By Sales Amount [Rs. $amount]" );

$pie = new pie();
$pie->set_alpha(0.6);
$pie->set_start_angle( 35 );
//$pie->radius(70);
$pie->add_animation( new pie_fade() );
$pie->set_tooltip( '#val# of #total#<br>#percent# of 100%' );
$pie->set_colours( array(prop_getcolor(0), prop_getcolor(1)) );
if ($type == 1) {
$pie->set_values( array(new pie_value($belowOrders, "Below Rs. $amount\n$belowOrders sales"),new pie_value($aboveOrders, "Above Rs. $amount\n$aboveOrders sales")) );
} else {
$pie->set_values( array(new pie_value($belowAmount, "Below Rs. $amount\nRs. $belowAmount"),new pie_value($aboveAmount, "Above Rs. $amount\nRs. $aboveAmount")) );
}

$chart = new open_flash_chart();
$chart->set_title( $title );
$chart->add_element( $pie );


$chart->x_axis = null;

echo $chart->toPrettyString();
?>
