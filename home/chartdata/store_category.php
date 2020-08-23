<?php
include "../../it_config.php";
require_once "session_check.php";
include "ofc/php-ofc-library/open-flash-chart.php";
require_once "lib/db/DBConn.php";
require_once "lib/logger/clsLogger.php";
require_once "chartProperties.php";
require_once "lib/core/strutil.php";

$d1Clause="";
if (isset($_GET['d1'])) {
$d1Clause = " date(p.shipped_time) >= '".$_GET['d1']."' ";
}
$d2Clause="";
if (isset($_GET['d2'])) {
$d2Clause = " and date(p.shipped_time) <= '".$_GET['d2']."' ";
}
if (isset($_GET['storeid'])) {
    $storeid = $_GET['storeid']; 
    $sClause=" p.storeid=$storeid "; 
    $d1Clause = " and date(p.shipped_time) >= '".$_GET['d1']."' ";
} else { $storeid= ""; $sClause= ""; }
if (isset($_GET['d1']) && isset($_GET['d2'])) { $_SESSION['daterange']=ddmmyy($_GET['d1']).",".ddmmyy($_GET['d2']); }
$type=1; // 1=numOrders, 2=revenue
if (isset($_GET['type'])) { $type=$_GET['type']; }
if ($type==1) {
    $orderClause = " order by totqty ";
} else {
    $orderClause = " order by totval ";
}


$logger = new clsLogger();
$currStore = getCurrStore();
if (!$currStore) { print "User session timedout. Please login again"; return; }
//$storeid=$currStore->id;
//if ($storeid == 0) { $sClause = " "; }
//else { $sClause = " and storeid = $storeid "; }
$ctgs = "";
foreach ($g_categories as $key => $value) {
    if ($key != "oth") { $ctgs .= "$key,"; }
}
$ctgs = substr($ctgs, 0, -1);
try {
	$db = new DBConn();
	$query = "select o.ctg_id,o.ctg_name,sum(o.order_qty) as totqty,sum(o.MRP) as totval from it_ck_pickgroup p, it_ck_orderitems o where $sClause and p.storeid = o.store_id and o.order_id in (p.order_ids) and o.ctg_id in ($ctgs) $d1Clause $d2Clause group by o.ctg_id $orderClause desc";
//print "$query<br />";
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
$xlabels[] = array();
foreach ($objs as $obj) {
	if ($type == 1) {
		if ($obj->totqty < $ymin) { $ymin = $obj->totqty; }
		if ($obj->totqty > $ymax) { $ymax = $obj->totqty; }
		$tmp = new bar_value(intVal($obj->totqty));
		$tmp->set_tooltip( "#val#<br>Category :".$obj->ctg_name."<br>Tot Ordered Qty: ".$obj->totqty );
		$tmp->set_colour(prop_getcolor($count));
	} else {
		if ($obj->totval < $ymin) { $ymin = $obj->totval; }
		if ($obj->totval > $ymax) { $ymax = $obj->totval; }
		$tmp = new bar_value(intVal($obj->totval));
		$tmp->set_tooltip( "Rs. #val#<br>Category : ".$obj->ctg_name."<br>Tot Ordered MRP: ".$obj->totval );
		$tmp->set_colour(prop_getcolor($count));
	}
        array_push($xlabels, $obj->ctg_name);
	if ($storeid=="") {} //$tmp->set_on_click( "daily_orders('".$obj->bdate."')" ); }
        else {} //$tmp->set_on_click( "daily_orders('".$obj->bdate."',$storeid)" );}
	$pValues[] = $tmp;
	$count++; if ($count == 7) $count = 0;
}
$xlabels = array_slice($xlabels, 1);

$title = new title("Store Category Wise Order Statistics");

$bar = new bar_glass();
$bar->set_values( $pValues );

$chart = new open_flash_chart();
$chart->set_title( $title );
$chart->add_element( $bar );

if ($storeid=="") $chart->set_y_axis(create_y_axis(0,$ymax));
else $chart->set_y_axis(create_y_axis($ymin,$ymax));

/* BEGIN set invisible x axis labels */
//$x_labels = new x_axis_labels();
//$x_labels->set_steps( 1 );
//$x_labels->set_colour( '#FFFFFF' );

$x = new x_axis();
$x->set_labels_from_array($xlabels);
$x->set_colour( '#A2ACBA' );
$x->set_grid_colour( '#D7E4A3' );
$x->set_offset( 1 );
// Add the X Axis Labels to the X Axis
//$x->set_labels( $x_labels );

$chart->set_x_axis( $x );
/* END set invisible x axis labels */

echo $chart->toString();

?>
