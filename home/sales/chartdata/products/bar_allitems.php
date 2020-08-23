<?php

include "../../appConfig.php";
require_once "store/session_check.php";
include "ofc/php-ofc-library/open-flash-chart.php";
require_once "lib/db/DBConn.php";
require_once "lib/logger/clsLogger.php";
require_once "../chartProperties.php";

$field = $_GET['field'];
$title = stripslashes($_GET['title']);
$limit = "";
$pageno = false;
if (isset($_GET['barFrom']) && isset($_GET['barNum'])) {
	$barFrom = $_GET['barFrom'];
	$barNum = $_GET['barNum'];
	$limit = "limit $barFrom, $barNum";
	$pageno = intval($barFrom/$barNum) + 1;
}
$d1Clause="";
$ctgTable="";
if (isset($_GET['d1'])) {
$d1Clause = " and date(o.bill_datetime) >= '".$_GET['d1']."' ";
}
$d2Clause="";
if (isset($_GET['d2'])) {
$d2Clause = " and date(o.bill_datetime) <= '".$_GET['d2']."' ";
}
if (isset($_GET['d1']) && isset($_GET['d2'])) { $_SESSION['daterange']=$_GET['d1'].",".$_GET['d2']; }
if (isset($_GET['ctgid'])) {
$ctgTable = "it_catitems ci, ";
$ctgClause = "ci.catid = ".$_GET['ctgid']." and ci.itemid = r.id and ";
}
$logger = new clsLogger();
$currStore = getCurrStore();
if (!$currStore) { print "User session timedout. Please login again"; return; }
$ymin = 200000000; $ymax = 0;
try {
	$db = new DBConn();
//	$query = "select r.itemname, sum(rl.$field) as total from it_orders o, it_rawitems r, it_rawitemlines rl where o.storeid=$currStore->id and o.inactive=0 and o.status>0 $d1Clause $d2Clause and o.id = rl.orderid and r.id = rl.rawitemid $ctgClause group by rl.rawitemid order by total desc $limit";
//	$query = "select r.itemname, sum(rl.linequantity) as total from it_categories c, it_catitems ci, it_rawitems r, it_rawitemlines rl, it_orders o where c.scenarioid=2 $ctgClause and ci.itemid = r.id and r.id = rl.rawitemid and rl.orderid = o.id and o.storeid=10 and o.status > 0 and o.inactive=0 group by rl.rawitemid order by total desc";
	$query = "select r.itemname, sum(rl.$field) as total from $ctgTable it_rawitems r, it_rawitemlines rl, it_orders o where $ctgClause r.id = rl.rawitemid and rl.orderid = o.id and o.storeid=$currStore->id and o.status > 0 $d1Clause $d2Clause and o.inactive=0 group by rl.rawitemid order by total desc";
//print $query;

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
		$tooltip = '<span style="font-size:24px;">'.$obj->itemname.'</span>, '.$total;
		$value->set_tooltip( $tooltip );
//		$value->set_tooltip( "$obj->itemname, $total" );
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

if ($pageno) {
	$title = "$title - Page $pageno";
}
$chart_title = new title( "$title" );

//$hbar->set_colours( $pColors );

$chart = new open_flash_chart();
$chart->set_title( $chart_title );
$chart->add_element( $bar1 );
$chart->set_y_axis( create_y_axis($ymin, $ymax) );

if ($pageno) {
$m = new ofc_menu("#E0E0ff", "#707070");
$m_items = array();
if ($barFrom > 0) { $m_items[] = new ofc_menu_item("Prev", 'prevItems'); }
if ($count == $barNum) { $m_items[] = new ofc_menu_item("Next", 'nextItems'); }
$m->values($m_items);
$chart->set_menu($m);
}

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
