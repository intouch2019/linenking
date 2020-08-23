<?php

include "../../it_config.php";
require_once "session_check.php";
include "ofc/php-ofc-library/open-flash-chart.php";
require_once "lib/db/DBConn.php";
require_once "lib/logger/clsLogger.php";
require_once "lib/core/strutil.php";
require_once "chartProperties.php";

$currStore = getCurrUser();
if (!$currStore) { print "User session timedout. Please login again"; return; }
//$storeid=$currStore->id;
//if ($storeid == 0) { $sClause = " "; }
//else { $sClause = " and storeid = $storeid "; }
if (isset($_GET['storeid'])) {
    $storeid = $_GET['storeid']; 
    $sClause=" and o.storeid=$storeid ";
} else { $sClause= ""; }
$limit = "";
$pageno = false;
if (isset($_GET['barFrom']) && isset($_GET['barNum'])) {
	$barFrom = $_GET['barFrom'];
	$barNum = $_GET['barNum'];
	$limit = "limit $barFrom, $barNum";
	$pageno = intval($barFrom/$barNum) + 1;
}
$bdate=$_GET['bdate'];
//$field="bill_amount";
//if (isset($_GET['type']) && $_GET['type'] == "1") {
	//$field="bill_quantity";
//}

$logger = new clsLogger();
$ymin = 200000000; $ymax = 0;
try {
	$db = new DBConn();
	$query = "select o.*,c.store_name from it_ck_pickgroup o,it_codes c where o.storeid=c.id $sClause and date(o.shipped_time) = '$bdate' and o.shipped_time is not null order by o.active_time $limit";
//	print $query;
        $objs = $db->fetchObjectArray($query);
	$pValues = array();
	$pColors = array();
//	print "$query<br />";
//	print count($objs)."<br />";
	$count=0;
	foreach ($objs as $obj) {
		$total = intval($obj->order_amount);
		if ($total < $ymin) { $ymin = $total; }
		if ($total > $ymax) { $ymax = $total; }
		$value = new bar_value($total);
//		$value->set_colour(prop_getcolor($count));
		$value->set_tooltip( "Order No: $obj->order_nos<br>STORE NAME: $obj->store_name<br>INVOICE NO  : $obj->invoice_no<br>ORDER AMOUNT: Rs. $obj->order_amount,    ORDERED QTY: $obj->order_qty<br>SHIPPED MRP    : Rs. $obj->shipped_mrp,    SHIPPED QTY: $obj->shipped_qty<br>ORDER TIME     :   ".mmddyy($obj->active_time)."<br>SHIPPED TIME  :   ".mmddyy($obj->shipped_time));
		$value->set_on_click( "loadpage($obj->id)" );
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
$chart_title = new title( "$bdate Orders" );

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
$x_labels->set_steps( 1 );
$x_labels->set_colour( '#FFFFFF' );
$x_labels->set_labels( array() );
$x = new x_axis();
$x->set_colour( '#A2ACBA' );
$x->set_grid_colour( '#D7E4A3' );
$x->set_offset( 1 );
// Add the X Axis Labels to the X Axis
$x->set_labels( $x_labels );
$chart->set_x_axis( $x );
/* END set invisible x axis labels */

echo $chart->toPrettyString();
?>
