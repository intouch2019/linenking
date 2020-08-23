<?php

include "../appConfig.php";
require_once "store/session_check.php";
include "ofc/php-ofc-library/open-flash-chart.php";
require_once "lib/db/DBConn.php";
require_once "lib/logger/clsLogger.php";
require_once "lib/codes/CodeProps.php";
require_once "chartProperties.php";

$title = "By Time of the Day";
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
$storeid=$currStore->id;
try {
	$db = new DBConn();
	$obj = $db->fetchObject("select propvalue from it_code_props where storeid=$storeid and propname='".CodeProps::HourOfDaySlabs."'");
	$hourslabs = "10,14,18,22"; // default value
	if ($obj && $obj->propvalue) { $hourslabs = $obj->propvalue; }
	$arr = explode(",",$hourslabs);
	$prev = 0;
	$when = "";
	foreach ($arr as $slab) {
		$prevt = "$prev:00:00";
		$currt = "$slab:00:00";
		$range1 = getPrintTime($prev);
		$range2 = getPrintTime($slab);
		if ($prev > 0) {
			$when .= " WHEN time(bill_datetime) >= '$prevt' AND time(bill_datetime) < '$currt' THEN '$range1 - $range2' ";
		} else {
			$when .= " WHEN time(bill_datetime) < '$currt' THEN '< $range2' ";
		}
		$prev = $slab;
	}
	$when .= " ELSE '> $range2' ";
	$query = "SELECT value_range, sum(bill_amount) as totalamount, count(*) AS numorders FROM (SELECT CASE $when END as value_range, bill_amount FROM it_orders WHERE storeid=$storeid and status > 0 $d1Clause $d2Clause ) AS  time_summaries GROUP BY value_range";
	$slabs = $db->fetchObjectArray($query);
	$db->closeConnection();
} catch (Exception $xcp) {
	$logger->logException($xcp);
	print "Unexpected error occurred. Please try again later";
	return;
}

function getPrintTime($t) {
$t = intval($t);
if ($t < 12) { return $t."am"; }
else { $t -= 12; return $t."pm"; }
}

$title = new title( $title );

$pie = new pie();
$pie->set_alpha(0.6);
$pie->set_start_angle( 35 );
//$pie->radius(70);
$pie->add_animation( new pie_fade() );
$pie->set_tooltip( '#val# of #total#<br>#percent# of 100%' );

$pValues = array();
$pColors = array(); $count=0;
foreach ($slabs as $slab) {
	if ($type == 1) {
	$pValues[] = new pie_value(intval($slab->numorders), $slab->value_range."\n".$slab->numorders." sales");
	} else {
	$pValues[] = new pie_value(intval($slab->totalamount), $slab->value_range."\n Rs. ".intval($slab->totalamount));
	}
	$pColors[] = prop_getcolor($count); $count++;
}

$pie->set_colours( $pColors );
$pie->set_values( $pValues );

$chart = new open_flash_chart();
$chart->set_title( $title );
$chart->add_element( $pie );


$chart->x_axis = null;

echo $chart->toPrettyString();
?>
