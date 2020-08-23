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
if (isset($_GET['d1']) && isset($_GET['d2'])) { $_SESSION['daterange']=ddmmyy($_GET['d1']).",".ddmmyy($_GET['d2']); $whereClause = " where "; }
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
//	$query = "select date(shipped_time) as bdate,weekday(shipped_time) as dayofweek, count(*) as numOrders, sum(order_qty) as totalQuantity, sum(shipped_qty) as totalQuantityShipped, round(sum(order_amount)) as totalAmount, round(sum(cheque_amt)) as totalAmountCheque, round(sum(shipped_mrp)) as totalAmountShipped, round(sum(order_amount)/sum(order_qty)) as avgPrice from it_ck_pickgroup where $d1Clause $d2Clause and shipped_time is not null group by bdate order by bdate";
        $query = "select date(shipped_time) as bdate,weekday(shipped_time) as dayofweek, count(*) as numOrders, sum(order_qty) as totalQuantity, sum(shipped_qty) as totalQuantityShipped, round(sum(order_amount)) as totalAmount, round(sum(cheque_amt)) as totalAmountCheque, round(sum(shipped_mrp)) as totalAmountShipped, round(sum(order_amount)/sum(order_qty)) as avgPrice from it_ck_pickgroup $whereClause $d1Clause $d2Clause and shipped_time is not null group by bdate order by bdate";
//       error_log("\n avg_order_line:- ".$query."\n",3,"tmp.txt");
//print "$query<br />";
	$objs = $db->fetchObjectArray($query);
//	$db->closeConnection();
} catch (Exception $xcp) {
	$logger->logException($xcp);
	print "Unexpected error occurred. Please try again later";
	return;
}

//$pValues = array();
$data_1= array();
$data_2= array();
$data_3= array();
$ymin = 200000000; $ymax = 0;
//$count=0;
//if($objs){
    foreach ($objs as $obj) {
            $dayofweek = $prop_daynames[$obj->dayofweek];
            if ($type == 1) {              
                    if ($obj->totalQuantity < $ymin) { $ymin = $obj->totalQuantity; }
                    if ($obj->totalQuantity > $ymax) { $ymax = $obj->totalQuantity; }
                    //$tmp = new bar_value(intVal($obj->numOrders));
                    $d1 = new hollow_dot(intVal($obj->totalQuantity));
                    $d2 = new solid_dot(intVal($obj->totalQuantityShipped));
                    $data_1[]=$d1->tooltip('Ordered Qty:#val#<br>Date:'.ddmmyy2($obj->bdate).'<br>Day:'.$dayofweek);
                    $data_2[]=$d2->tooltip('Shipped Qty:#val#<br>Date:'.ddmmyy2($obj->bdate).'<br>Day:'.$dayofweek);
                    //$tmp1->set_tooltip( "#val# sales<br>".$obj->bdate );
                    //$tmp1->set_colour(prop_getcolor($count));
            } else {
                    if ($obj->totalAmount < $ymin) { $ymin = $obj->totalAmount; }
                    if ($obj->totalAmount > $ymax) { $ymax = $obj->totalAmount; }
                    $d1 = new hollow_dot(intVal($obj->totalAmount));
                    $d2 = new solid_dot(intVal($obj->totalAmountShipped));
                    $d3 = new solid_dot(intVal($obj->totalAmountCheque));
                    $data_1[]=$d1->tooltip('Ordered Amt:#val#<br>Date:'.ddmmyy2($obj->bdate).'<br>Day:'.$dayofweek);
                    $data_2[]=$d2->tooltip('Shipped Amt:#val#<br>Date:'.ddmmyy2($obj->bdate).'<br>Day:'.$dayofweek);
                    $data_3[]=$d3->tooltip('Cheque Amt:#val#<br>Date:'.ddmmyy2($obj->bdate).'<br>Day:'.$dayofweek);
                    //$tmp = new bar_value(intVal($obj->totalAmount));
                    //$tmp->set_tooltip( "Rs. #val#<br>".$obj->bdate );
                    //$tmp->set_colour(prop_getcolor($count));
            }
            //$tmp->set_on_click( "daily_orders('".$obj->bdate."')" );
            //$pValues[] = $tmp;
            //$count++; if ($count == 7) $count = 0;
    }
//}

if ($type==1) { $title = new title("Order Qty vs. Shipped Qty"); }
else {$title = new title("Order Amount vs Shipped MRP vs Cheque Amount"); }

//$d1 = new hollow_dot();
$d1->size(6)->halo_size(3)->colour('#CC3399');

$line1 = new line();
$line1->set_default_dot_style($d1);
$line1->set_values( $data_1 );
$line1->set_width( 1.5 );
$line1->set_colour( '#CC3399' );
if ($type==1) { $line1->set_key( "Order Quantity", 12 ); }
else { $line1->set_key ("Order Amount",12); }

//$d2 = new solid_dot();
$d2->size(4)->halo_size(1)->colour('#668053');

$line2 = new line();
$line2->set_default_dot_style($d2);
$line2->set_values( $data_2 );
$line2->set_width( 1.5 );
$line2->set_colour( '#3D5C56' );
if ($type==1) { $line2->set_key( "Shipped Quantity", 12 ); }
else { $line2->set_key ("Shipped MRP",12); }

if (isset($d3)) {
    $d3->size(2)->halo_size(1)->colour('#FF5050');

    $line3 = new line();
    $line3->set_default_dot_style($d3);
    $line3->set_values( $data_3 );
    $line3->set_width( 1.5 );
    $line3->set_colour( '#FF5050' );
    $line3->set_key( "Cheque Amount", 12 );
}
$y = new y_axis();
$y->set_range( 0, $ymax+(intval($ymax*0.03)) );


$chart = new open_flash_chart();
$chart->set_title( $title );
$chart->add_element( $line1 );
$chart->add_element( $line2 );
if (isset($d3)) {$chart->add_element ($line3); }
$chart->set_y_axis( $y );


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





//$bar = new bar_glass();
/*$bar->set_values( $pValues );

$chart = new open_flash_chart();
$chart->set_title( $title );
$chart->add_element( $bar );

$chart->set_y_axis(create_y_axis(0,$ymax+1));*/




?>
