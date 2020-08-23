<?php

include "../../../it_config.php";
require_once "session_check.php";
include "ofc/php-ofc-library/open-flash-chart.php";
require_once "lib/db/DBConn.php";
require_once "lib/logger/clsLogger.php";
require_once "../chartProperties.php";
require_once "lib/core/strutil.php";

$order=""; if (isset($_GET['order'])) $order = $_GET['order'];
$num = $_GET['num'];
$field = $_GET['field'];
$title = $_GET['title'];
$d1Clause="";
if (isset($_GET['d1'])) {
$d1Clause = " and date(p.shipped_time) >= '".$_GET['d1']."' ";
}
$d2Clause="";
if (isset($_GET['d2'])) {
$d2Clause = " and date(p.shipped_time) <= '".$_GET['d2']."' ";
}
if (isset($_GET['d1']) && isset($_GET['d2'])) { $_SESSION['daterange']=ddmmyy($_GET['d1']).",".ddmmyy($_GET['d2']); }
$logger = new clsLogger();
$currStore = getCurrUser();
if (!$currStore) { print "User session timedout. Please login again"; return; }
if ($field=="linequantity") { $orderby="totQty"; } else { $orderby="totAmt"; }
//$storeid=$currStore->id;
//print_r ($storeid);
//print_r ($_SESSION['currStore']->id);
//if ($storeid == 0) { $sClause = " "; }
//else { $sClause = " o.storeid = $storeid and"; }
try {
	$db = new DBConn();
        $catid=$db->safe($_GET['catid']);
        $mrp=$_GET['mrp'];
        $mrpQuery ="";
        if ($mrp!=1) { $mrpQuery = " and oi.MRP=$mrp "; }
	//$query = "select r.id, r.itemname, r.font, sum(rl.$field) as total from it_orders o, it_rawitems r, it_rawitemlines rl where $sClause o.inactive=0 and o.status>0 $d1Clause $d2Clause and o.id = rl.orderid and r.id = rl.rawitemid group by rl.rawitemid having total>0 order by total $order";
        
        //$query = "select d.ctg_id,d.design_no,d.image,sum(i.order_qty) as totQty,sum(i.MRP) as totAmt from it_ck_pickgroup o,it_ck_orderitems i,it_ck_designs d where i.ctg_id=$catid $mrpQuery and i.order_id in (o.order_ids) and i.ctg_id=d.ctg_id and i.design_no=d.design_no $d1Clause $d2Clause group by ctg_id,design_no order by $orderby $order limit $num";
        $query = "select i.ctg_id,c.name as ctg_name,d.design_no,d.image,sum(oi.order_qty) as totQty,sum(oi.MRP) as totAmt from it_ck_pickgroup p,it_ck_orderitems oi,it_ck_designs d , it_items i, it_categories c where oi.item_id = i.id and i.ctg_id=$catid $mrpQuery and oi.order_id in (p.order_ids) and i.ctg_id=d.ctg_id and i.design_no=d.design_no  and i.ctg_id = c.id $d1Clause $d2Clause group by i.ctg_id,i.design_no order by $orderby $order limit $num";
//	print $query;
        $objs = $db->fetchObjectArray($query);
	$pValues = array();
	$pColors = array(); $count=0;
	$rest = 0;
//	print "$query<br />";
//	print count($objs)."<br />";
        //$topdesigns="";
        //$_SESSION['topdesigns'] = "";
	$sumQty=0; $sumAmt=0;
	foreach($objs as $obj) { $sumQty += $obj->totQty; $sumAmt += $obj->totAmt; }
	foreach ($objs as $obj) {
                
                //$topdesigns .= "'".$obj->design_no."',";
		if ($count < $num) {
			if ($field=="linequantity") {
                            $pieValue = new pie_value(intval($obj->totQty), "$obj->totQty");
				$pct = sprintf("%.02f",$obj->totQty*100/$sumQty);
			    $tooltip = 'Category: '.$obj->ctg_name.'<br>Design:'.$obj->design_no."<br>$obj->totQty of $sumQty ordered quantity<br>$pct of 100%";
                        } else {
                            $pieValue = new pie_value(intval($obj->totAmt), "$obj->totAmt");
				$pct = sprintf("%.02f",$obj->totAmt*100/$sumAmt);
			    $tooltip = 'Category: '.$obj->ctg_name.'<br>Design:'.$obj->design_no."<br>$obj->totAmt of $sumAmt ordered quantity<br>$pct of 100%";
                        }
			$pieValue->set_tooltip( $tooltip );
			$pieValue->on_click("clickEvent('aa','$obj->ctg_id','$obj->design_no')");
			$pValues[] = $pieValue;
			$pColors[] = prop_getcolor($count);
		} else {
			$rest += $obj->totQty;
		}
		$count++;
	}
//	$pValues[] = new pie_value(intval($rest), "Other: Rs. $rest");
//	$pColors[] = prop_getcolor($num); $count++;
        //$topdesigns = substr($topdesigns, 0, -1);
        //$_SESSION['topdesigns']=$topdesigns;
	$db->closeConnection();
} catch (Exception $xcp) {
	$logger->logException($xcp);
	print "Unexpected error occurred. Please try again later";
	return;
}


$chart_title = new title( $title, "{font-size: 8px; color:#0000ff; font-family: Verdana; text-align: center;}" );

$pie = new pie();
$pie->set_alpha(0.6);
$pie->set_start_angle( 0 );
//$pie->radius(70);
$pie->add_animation( new pie_fade(0) );
$pie->add_animation( new pie_bounce(20) );
$pie->gradient_fill( 1 );
$pie->set_colours( $pColors );
$pie->set_values( $pValues );

$chart = new open_flash_chart();
$chart->set_title( $chart_title );
$chart->add_element( $pie );


$chart->x_axis = null;

echo $chart->toPrettyString();
?>
