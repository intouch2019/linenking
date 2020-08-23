<?php


include "../../appConfig.php";
require_once "store/session_check.php";
include "ofc/php-ofc-library/open-flash-chart.php";
require_once "lib/db/DBConn.php";
require_once "lib/logger/clsLogger.php";
require_once "../chartProperties.php";
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
	$query = "select count(*) as total, case when year(p.dateofbirth) > 1985 then '25 and below' when year(p.dateofbirth) between 1980 and 1985 then '25 to 30' when year(p.dateofbirth) between 1975 and 1980 then '30 to 35' when year(p.dateofbirth) between 1970 and 1975 then '35 to 40' when year(p.dateofbirth) between 1965 and 1970 then '40 to 45' when year(p.dateofbirth) between 1960 and 1965 then '45 to 50' when year(p.dateofbirth) between 1955 and 1960 then '50 to 55' when year(p.dateofbirth) between 1950 and 1955 then '55 to 60' when year(p.dateofbirth) between 1945 and 1950 then '60 to 65' when year(p.dateofbirth) between 1940 and 1945 then '65 to 70' else 'Above 70' end as ageband from it_orders o, it_userprofile p where o.userid=p.userid $d1Clause $d2Clause group by ageband";
	$objs = $db->fetchObjectArray($query);
	$db->closeConnection();
} catch (Exception $xcp) {
	$logger->logException($xcp);
	print "Unexpected error occurred. Please try again later";
	return;
}

$totalAmounts = array();
$labels = array();
$pValues = array();
$ymin = 200000000; $ymax = 0;
foreach ($objs as $obj) {
	$total = intval($obj->total);
	if ($total < $ymin) { $ymin = $total; }
	if ($total > $ymax) { $ymax = $total; }
	$tmp = new bar_value(intval($obj->total));
//	$tmp->set_tooltip( "Rs. #val#<br>".$obj->bdate );
	$tmp->set_colour( prop_getcolor(0) );
	$pValues[] = $tmp;
	$labels[] = $obj->ageband;
}

$title = new title("Age Groups");

$bar = new bar_glass();
$bar->colour( prop_getcolor(0) );
$bar->set_values( $pValues );

$chart = new open_flash_chart();
$chart->set_title( $title );
$chart->add_element( $bar );

$x_labels = new x_axis_labels();
//$x_labels->set_steps( 2 );
//$x_labels->set_vertical();
$x_labels->set_colour( '#A2ACBA' );
$x_labels->set_labels( $labels );

$x = new x_axis();
$x->set_colour( '#A2ACBA' );
$x->set_grid_colour( '#D7E4A3' );
$x->set_offset( false );
//$x->set_steps(4);
// Add the X Axis Labels to the X Axis
$x->set_labels( $x_labels );

$chart->set_x_axis( $x );

$chart->set_y_axis( create_y_axis($ymin, $ymax) );

echo $chart->toString();

?>
