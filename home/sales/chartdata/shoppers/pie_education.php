<?php

include "../../appConfig.php";
require_once "store/session_check.php";
include "ofc/php-ofc-library/open-flash-chart.php";
require_once "lib/db/DBConn.php";
require_once "lib/logger/clsLogger.php";
require_once "lib/user/clsUserProfile.php";
require_once "../chartProperties.php";

$order=""; if (isset($_GET['order'])) $order = $_GET['order'];
$field = $_GET['field'];
$title = $_GET['title'];
$d1Clause="";
if (isset($_GET['d1'])) {
$d1Clause = " and to_days(o.bill_datetime) >= ".$_GET['d1']." ";
}
$d2Clause="";
if (isset($_GET['d2'])) {
$d2Clause = " and to_days(o.bill_datetime) <= ".$_GET['d2']." ";
}
$logger = new clsLogger();
$currStore = getCurrStore();
if (!$currStore) { print "User session timedout. Please login again"; return; }
try {
	$db = new DBConn();
	$query = "select p.education, count(*) as num from it_orders o, it_userprofile p where o.userid=p.userid $d1Clause $d2Clause group by p.education";
	$objs = $db->fetchObjectArray($query);
	$pValues = array();
	$pColors = array(); $count=0;
	foreach ($objs as $obj) {
		$pieText = $UserProfile["education"][$obj->education];
		$pValues[] = new pie_value(intval($obj->num), $pieText);
		$pColors[] = prop_getcolor($count); $count++;
	}
	$db->closeConnection();
} catch (Exception $xcp) {
	$logger->logException($xcp);
	print "Unexpected error occurred. Please try again later";
	return;
}


$chart_title = new title( $title, "{font-size: 8px; color:#0000ff; font-family: Verdana; text-align: center;}" );

$pie = new pie();
$pie->set_alpha(0.6);
$pie->set_start_angle( 35 );
//$pie->radius(70);
$pie->add_animation( new pie_fade() );
$pie->set_colours( $pColors );
$pie->set_values( $pValues );

$chart = new open_flash_chart();
$chart->set_title( $chart_title );
$chart->add_element( $pie );


$chart->x_axis = null;

echo $chart->toPrettyString();
?>
