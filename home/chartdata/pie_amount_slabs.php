<?php

include "../../it_config.php";
require_once "../session_check.php";
include "ofc/php-ofc-library/open-flash-chart.php";
require_once "lib/db/DBConn.php";
//require_once "lib/codes/clsCodes.php";
//require_once "lib/codes/CodeProps.php";
require_once "lib/logger/clsLogger.php";
require_once "chartProperties.php";
require_once "lib/core/strutil.php";

error_reporting(E_ALL);
ini_set('display_errors', '1');

$d1Clause="";
if (isset($_GET['d1'])) {
$d1Clause = " date(shipped_time) >= '".$_GET['d1']."' ";
}
$d2Clause="";
if (isset($_GET['d2'])) {
$d2Clause = " and date(shipped_time) <= '".$_GET['d2']."' ";
}
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
	//$obj = $db->fetchObject("select propvalue from it_code_props where propname='".CodeProps::AmountSlabs."' $sClause");
	if ($type==2) { 
            $amountslabs = "10000,30000,60000,100000,150000,200000,250000"; 
            $arr = explode(",",$amountslabs);
            $prev = 0;
            $when = "";
            foreach ($arr as $slab) {
                    $when .= " WHEN order_amount >= $prev AND order_amount < $slab THEN 'Rs.$prev - Rs.$slab' ";
                    $prev = $slab;
            }
            $when .= " ELSE '> Rs.$slab' ";
            $query = "SELECT value_range, sum(order_amount) as totalamount, count(*) AS numorders FROM (SELECT CASE $when END as value_range, order_amount FROM it_ck_pickgroup WHERE $d1Clause $d2Clause) AS  price_summaries GROUP BY value_range";

            $slabs = $db->fetchObjectArray($query);
        } else {
            $quantityslabs = "30,70,130,200,300,400"; 
            $arr = explode(",",$quantityslabs);
            $prev = 0;
            $when = "";
            foreach ($arr as $slab) {
                    $when .= " WHEN order_qty >= $prev AND order_qty < $slab THEN 'QTY: $prev - $slab' ";
                    $prev = $slab;
            }
            $when .= " ELSE '> QTY: $slab' ";
            $query = "SELECT quantity_range, sum(order_qty) as totalqty, count(*) AS numorders FROM (SELECT CASE $when END as quantity_range, order_qty FROM it_ck_pickgroup WHERE $d1Clause $d2Clause) AS  quantity_summaries GROUP BY quantity_range order by totalqty";

            $slabs = $db->fetchObjectArray($query);
        }
	//if ($obj && $obj->propvalue) { $amountslabs = $obj->propvalue; }
	
	$db->closeConnection();
} catch (Exception $xcp) {
	$logger->logException($xcp);
	print "Unexpected error occurred. Please try again later";
	return;
}

if ($type==1) { $title = new title( "By Order Quantity" ); }
else { $title = new title( "By Order Amount" ); }

$pie = new pie();
$pie->set_alpha(0.6);
$pie->set_start_angle( 0 );
//$pie->radius(70);
$pie->add_animation( new pie_fade() );
$pie->set_tooltip( '#val# of #total#<br>#percent# of 100%' );
$pValues = array();
$pColors = array(); $count=0;
foreach ($slabs as $slab) {
	if ($type == 2) {
            $valrange=($slab->value_range);
            $pValues[] = new pie_value(intval($slab->numorders), $valrange."\n".$slab->numorders." sales");
	} else {
            $qtyrange=$slab->quantity_range;
            $pValues[] = new pie_value(intval($slab->totalqty), $qtyrange."\n ".intval($slab->totalqty));
	}
	$pColors[] = prop_getcolor($count); $count++;
}

$pie->set_colours( $pColors );
$pie->set_values( $pValues );
$pie->add_animation( new pie_fade() );
$pie->add_animation( new pie_bounce(10) );
$pie->gradient_fill();

$chart = new open_flash_chart();
$chart->set_title( $title );
$chart->add_element( $pie );


$chart->x_axis = null;

echo $chart->toPrettyString();
?>
