<?php

include "../../it_config.php";
require_once "session_check.php";
include "ofc/php-ofc-library/open-flash-chart.php";
require_once "lib/db/DBConn.php";
require_once "lib/logger/clsLogger.php";
require_once "chartProperties.php";
require_once "lib/core/strutil.php";

$byAmount=false;
if (isset($_GET['amount'])) { $amount = $_GET['amount']; }
else { $amount = 200; }
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
extract($_GET);
$logger = new clsLogger();
$currStore = getCurrUser();
if (!$currStore) { print "User session timedout. Please login again"; return; }
//$storeid=$currStore->id;
//$storeid=37;
//print_r ($currStore);
//print_r ($_SESSION['currStore']->id);

//if ($storeid == 0) { $sClause = " "; }
//else { $sClause = " and storeid = $storeid "; }
try {
	$db = new DBConn();
	$query = "select weekday(active_time) as dayofweek,dayofweek(active_time) as day, count(*) as numOrders, round(sum(order_amount)) as totalAmount from it_ck_pickgroup where $d1Clause $d2Clause group by dayofweek";
//	print $query;
//        $dayquery = "select dayofweek(date()) as day from it_ck_pickgroup where $d1Clause $d2Clause";
        $objs = $db->fetchObjectArray($query);
 //       $dayobj = $db->fetchObjectArray($dayquery);
	$pValues = array();
        $tooltip = array();
	$pColors = array(); $count=0;

        $wd = countWeekdays($d1,$d2);//counts number of weekdays bw time intervals and icreases d1 to d2+1

	foreach ($objs as $obj) {
		$dayofweek = $prop_daynames[$obj->dayofweek];
		if ($type == 1) { 
                        $avg=sprintf("%0.2f",$obj->numOrders/$wd[($obj->day-1)]);
			$pValues[] = new pie_value(intval($obj->numOrders), "$dayofweek\n".$obj->numOrders." orders\nAvg : ".$avg);
                        //$tooltip[] = '#val# of #total# orders<br>#percent# of 100%<br>Average Order: '.$avg;
		} else {
                        $avg=intval($obj->totalAmount/$wd[($obj->day-1)]);
			$pValues[] = new pie_value(intval($obj->totalAmount), "$dayofweek\nTot Rs. ".$obj->totalAmount."\nAvg : ".$avg);
		}
		$pColors[] = prop_getcolor($count); $count++;           
	}
	$db->closeConnection();
} catch (Exception $xcp) {
	$logger->logException($xcp);
	print "Unexpected error occurred. Please try again later";
	return;
}

function countWeekdays($d1, $d2) {
    $weekdays=array();
    do {
    $t = strtotime($d1);
    $dow = date("w", $t);
    if (!isset($weekdays[$dow])) {
            $weekdays[$dow] = 0;
    }
    $weekdays[$dow] += 1;
    $tmp = date_create($d1);
    $tmp->add(new DateInterval('P1D'));
    $d1 = date_format($tmp, 'Y-m-d');
    } while ($d1 <= $d2);
    return $weekdays;
}

$title = new title( "By Day of Week" );

$pie = new pie();
$pie->set_alpha(0.6);
$pie->set_start_angle( 35 );
//$pie->radius(70);
$pie->add_animation( new pie_fade() );
$pie->add_animation( new pie_bounce(10) );
$pie->gradient_fill();
$pie->set_tooltip( '#val# of #total# orders<br>#percent# of 100%' );
//$pie->set_tooltip($tooltip);
$pie->set_colours( $pColors );
$pie->set_values( $pValues );

$chart = new open_flash_chart();
$chart->set_title( $title );
$chart->add_element( $pie );


$chart->x_axis = null;

echo $chart->toPrettyString();
?>
