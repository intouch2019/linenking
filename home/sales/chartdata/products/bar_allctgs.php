<?php

include "../../appConfig.php";
require_once "store/session_check.php";
include "ofc/php-ofc-library/open-flash-chart.php";
require_once "lib/db/DBConn.php";
require_once "lib/logger/clsLogger.php";
require_once "../chartProperties.php";

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
$curr_scenarioid=null;
if (isset($_SESSION['scenarioid'])) { $curr_scenarioid = intval($_SESSION['scenarioid']); }
if (!$curr_scenarioid) { print "Product Segmentation Scenario not selecte"; return; }
$ymin = 200000000; $ymax = 0;
try {
	$db = new DBConn();
//	$query = "select c.id, c.name, sum(rl.$field) as total from (it_rawitems r left join it_categories c on c.id = r.catid), it_rawitemlines rl, it_orders o where o.storeid=$currStore->id and o.inactive=0 and o.status > 0 $d1Clause $d2Clause and o.id = rl.orderid and r.id = rl.rawitemid group by r.catid order by total desc";
//	$query = "select c.id, c.name, sum(rl.$field) as total from (it_catitems ci left join it_categories c on c.id = ci.catid and ci.scenarioid=$curr_scenarioid), it_rawitemlines rl, it_orders o where o.storeid=$currStore->id and o.inactive=0 and o.status > 0 $d1Clause $d2Clause and o.id = rl.orderid and r.id = rl.rawitemid group by r.catid order by total desc";
	$query = "select c.id, c.name, sum(rl.$field) as total from it_categories c, it_catitems ci, it_rawitemlines rl, it_orders o where c.scenarioid=$curr_scenarioid and c.id = ci.catid and ci.itemid = rl.rawitemid and rl.orderid = o.id and o.storeid=$currStore->id and o.status > 0 $d1Clause $d2Clause and o.inactive=0 group by c.id order by total desc";

//print "$query<br />";
	$objs = $db->fetchObjectArray($query);
	$pValues = array();
	$pColors = array();
//	print "$query<br />";
//	print count($objs)."<br />";
	$count=0;
	foreach ($objs as $obj) {
		$total = intval($obj->total);
		if ($total < $ymin) { $ymin = $total; }
		if ($total > $ymax) { $ymax = $total; }
		$value = new bar_value($total);
		$value->set_colour(prop_getcolor($count));
		$ctgid = $obj->id; if (!$ctgid) { $ctgid=0; }
		$ctgname = $obj->name; if (!$ctgname) { $ctgname = "Other"; }
		$value->set_tooltip( "$ctgname, $total" );
		$value->set_on_click( "ctgSelect($ctgid,'".addslashes($ctgname)."')" );
		$pValues[] = $value;
		$count++;
	}
	$db->closeConnection();
} catch (Exception $xcp) {
	$logger->logException($xcp);
	print "Unexpected error occurred. Please try again later";
	return;
}

$bar1 = new bar_glass();
$bar1->set_values( $pValues );

$chart_title = new title( $title );

//$hbar->set_colours( $pColors );

$chart = new open_flash_chart();
$chart->set_title( $chart_title );
$chart->add_element( $bar1 );
$chart->set_y_axis( create_y_axis($ymin, $ymax) );

/* BEGIN set invisible x axis labels */
$x_labels = new x_axis_labels();
$x_labels->set_steps( 1000 );
$x_labels->set_colour( '#FFFFFF' );
$x_labels->set_labels( array() );
$x = new x_axis();
$x->set_colour( '#FFFFFF' );
$x->set_grid_colour( '#D7E4A3' );
$x->set_offset( false );
// Add the X Axis Labels to the X Axis
$x->set_labels( $x_labels );
$chart->set_x_axis( $x );
/* END set invisible x axis labels */

echo $chart->toPrettyString();
?>
