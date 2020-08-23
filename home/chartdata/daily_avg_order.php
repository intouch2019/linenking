<?php
include "../../it_config.php";
require_once "session_check.php";
include "ofc/php-ofc-library/open-flash-chart.php";
require_once "lib/db/DBConn.php";
require_once "lib/logger/clsLogger.php";
require_once "chartProperties.php";
require_once "lib/core/strutil.php";

$d1Clause="";
$whereClause = "";
if (isset($_GET['d1'])) {
$d1Clause = " date(shipped_time) >= '".$_GET['d1']."' ";
$whereClause = " where ";
}
$d2Clause="";
if (isset($_GET['d2'])) {
$d2Clause = " and date(shipped_time) <= '".$_GET['d2']."' ";
$whereClause = " where ";
}
if (isset($_GET['storeid'])) {
    $storeid = $_GET['storeid']; 
    $sClause=" storeid=$storeid "; 
    $d1Clause = " and date(shipped_time) >= '".$_GET['d1']."' ";
} else { $storeid= ""; $sClause= ""; }
if (isset($_GET['d1']) && isset($_GET['d2'])) { $_SESSION['daterange']=ddmmyy($_GET['d1']).",".ddmmyy($_GET['d2']); }
$type=1; // 1=numOrders, 2=revenue
if (isset($_GET['type'])) { $type=$_GET['type']; }

$logger = new clsLogger();
$currStore = getCurrUser();
if (!$currStore) { print "User session timedout. Please login again"; return; }
//$storeid=$currStore->id;
//if ($storeid == 0) { $sClause = " "; }
//else { $sClause = " and storeid = $storeid "; }
try {
	$db = new DBConn();
//	$query = "select date(shipped_time) as bdate , weekday(shipped_time) as dayofweek, count(*) as numOrders, sum(order_qty) as totalQuantity, sum(shipped_qty) as totalQuantityShipped, round(sum(order_amount)) as totalAmount, round(sum(cheque_amt)) as totalAmountCheque, round(sum(shipped_mrp)) as totalAmountShipped, round(sum(order_amount)/sum(order_qty)) as avgPrice from it_ck_pickgroup where $sClause $d1Clause $d2Clause group by bdate order by bdate";
        $query = "select date(shipped_time) as bdate , weekday(shipped_time) as dayofweek, count(*) as numOrders, sum(order_qty) as totalQuantity, sum(shipped_qty) as totalQuantityShipped, round(sum(order_amount)) as totalAmount, round(sum(cheque_amt)) as totalAmountCheque, round(sum(shipped_mrp)) as totalAmountShipped, round(sum(order_amount)/sum(order_qty)) as avgPrice from it_ck_pickgroup $whereClause $sClause $d1Clause $d2Clause group by bdate order by bdate";
//print "$query<br />";
//        error_log("\n avg_order:- ".$query."\n",3,"tmp.txt");
	$objs = $db->fetchObjectArray($query);
//	$db->closeConnection();
} catch (Exception $xcp) {
	$logger->logException($xcp);
	print "Unexpected error occurred. Please try again later";
	return;
}

$pValues = array();
$ymin = 200000000; $ymax = 0;
$count=0;
foreach ($objs as $obj) {
        //$dayofweek = $prop_daynames[$obj->dayofweek];
         $dayofweek = $prop_daynames[$obj->dayofweek];
	if ($type == 1) {
		if ($obj->numOrders < $ymin) { $ymin = $obj->numOrders; }
		if ($obj->numOrders > $ymax) { $ymax = $obj->numOrders; }
		$tmp = new bar_value(intVal($obj->numOrders));
		$tmp->set_tooltip( "#val# order/s<br>".ddmmyy($obj->bdate)."<br>Tot Ordered Qty: ".$obj->totalQuantity."<br>Tot Shipped Qty:".$obj->totalQuantityShipped.'<br>Day:'.$dayofweek );
		$tmp->set_colour(prop_getcolor($count));
	} else {
		if ($obj->totalAmount < $ymin) { $ymin = $obj->totalAmount; }
		if ($obj->totalAmount > $ymax) { $ymax = $obj->totalAmount; }
		$tmp = new bar_value(intVal($obj->totalAmount));
		$tmp->set_tooltip( "Rs. #val#<br>".ddmmyy($obj->bdate)."<br>Tot Ordered Amt: ".$obj->totalAmount."<br>Tot Shipped Amt: ".$obj->totalAmountShipped."<br>Tot Cheque Amt : ".$obj->totalAmountCheque.'<br>Day:'.$dayofweek );
		$tmp->set_colour(prop_getcolor($count));
	}
	if ($storeid=="") { $tmp->set_on_click( "daily_orders('".$obj->bdate."')" ); }
        else {$tmp->set_on_click( "daily_orders('".$obj->bdate."',$storeid)" );}
	$pValues[] = $tmp;
	$count++; if ($count == 7) $count = 0;
}

$title = new title("Daily Order Statistics");

$bar = new bar_glass();
$bar->set_values( $pValues );

$chart = new open_flash_chart();
$chart->set_title( $title );
$chart->add_element( $bar );

if ($storeid=="") $chart->set_y_axis(create_y_axis(0,$ymax));
else $chart->set_y_axis(create_y_axis($ymin,$ymax));

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

echo $chart->toString();

?>
