<?php

include "../../appConfig.php";
require_once "store/session_check.php";
include "ofc/php-ofc-library/open-flash-chart.php";
require_once "lib/db/DBConn.php";
require_once "lib/logger/clsLogger.php";
require_once "../chartProperties.php";

$order=""; if (isset($_GET['order'])) $order = $_GET['order'];
$num = $_GET['num'];
$field = $_GET['field'];
$title = $_GET['title'];
$d1Clause="";
if (isset($_GET['d1'])) {
$d1Clause = " and date(o.bill_datetime) >= '".$_GET['d1']."' ";
}
$d2Clause="";
if (isset($_GET['d2'])) {
$d2Clause = " and date(o.bill_datetime) <= '".$_GET['d2']."' ";
}
if (isset($_GET['d1']) && isset($_GET['d2'])) { $_SESSION['daterange']=$_GET['d1'].",".$_GET['d2']; }
$logger = new clsLogger();
$currStore = getCurrStore();
if (!$currStore) { print "User session timedout. Please login again"; return; }
try {
	$db = new DBConn();
	$query = "select r.id, r.itemname, r.font, sum(rl.$field) as total from it_orders o, it_rawitems r, it_rawitemlines rl where o.storeid=$currStore->id and o.inactive=0 and o.status>0 $d1Clause $d2Clause and o.id = rl.orderid and r.id = rl.rawitemid group by rl.rawitemid order by total $order";
	$objs = $db->fetchObjectArray($query);
	$pValues = array();
	$pColors = array(); $count=0;
	$rest = 0;
//	print "$query<br />";
//	print count($objs)."<br />";
	foreach ($objs as $obj) {
		$total = intval($obj->total);	
		if ($count < $num) {
			$pieValue = new pie_value($total, "$total");
			if ($obj->font) {
				$tooltip = '<span style="font-family:'.$obj->font.';font-size:3.0em;">'.$obj->itemname.'</span>';
			} else {
				$tooltip = '<span style="font-size:2.0em;">'.$obj->itemname.'</span>';
			}
			$tooltip = rawurlencode($tooltip);
//			$pieValue->set_tooltip( $tooltip );
			$pieValue->on_click("clickEvent('".$tooltip."')");
			$pValues[] = $pieValue;
			$pColors[] = prop_getcolor($count);
		} else {
			$rest += $total;
		}
		$count++;
	}
//	$pValues[] = new pie_value(intval($rest), "Other: Rs. $rest");
//	$pColors[] = prop_getcolor($num); $count++;
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
