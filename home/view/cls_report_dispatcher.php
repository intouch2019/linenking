<?php
require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once "session_check.php";

class cls_report_dispatcher extends cls_renderer {
    var $params;
    var $dtrange;
    var $dispatcherid;
    var $currUser;
    function __construct($params=null) {
	//parent::__construct(array(UserType::Admin, UserType::CKAdmin, UserType::Manager));
        $this->params = $params;
        $this->currUser = getCurrUser();
	if (isset($_SESSION['account_dtrange'])) { $this->dtrange = $_SESSION['account_dtrange']; }
	else { $this->dtrange = date("d-m-Y"); }
        
        if (isset($_SESSION['dispatcherid'])) { $this->dispatcherid = $_SESSION['dispatcherid']; }
	else { $this->dispatcherid = "All Dispatchers"; }
    }
    function extraHeaders() {
?>
		<script type="text/javascript" src="jqueryui/js/jquery-ui-1.7.1.custom.min.js"></script>
		<script type="text/javascript" src="js/daterangepicker.jQuery.js"></script>
		<link rel="stylesheet" href="css/ui.daterangepicker.css" type="text/css" />
		<link rel="stylesheet" href="css/redmond/jquery-ui-1.7.1.custom.css" type="text/css" title="ui-theme" />
                <script type="text/javascript"> 
                </script>
<link rel="stylesheet" href="css/prettyPhoto.css" type="text/css" media="screen" title="prettyPhoto main stylesheet" charset="utf-8" />
<script src="js/prettyPhoto/jquery.prettyPhoto.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript">
    <!--//--><![CDATA[//><!--
    $(function(){
	var isOpen=false;
        $("a[rel^='prettyPhoto']").prettyPhoto({animation_speed:'fast',slideshow:3000, hideflash: true});
	$('#dateselect').daterangepicker({
	 	dateFormat: 'dd-mm-yy',
		arrows:false,
		closeOnSelect:true,
		onOpen: function() { isOpen=true; },
		onClose: function() { isOpen=false; },
		onChange: function() {
		if (isOpen) { return; }
		var dtrange = $("#dateselect").val();
		$.ajax({
			url: "savesession.php?name=account_dtrange&value="+dtrange,
			success: function(data) {
				window.location.reload();
			}
		});
		}
	});
    });

	function reload() {
		var dtrange = $("#dateselect").val();
		$.ajax({
			url: "savesession.php?name=account_dtrange&value="+dtrange,
			success: function(data) {
				window.location.reload();
			}
		});
	}
        
        $(function(){
            $("#storeselect").change(function () {
                var dispatcherid= $('select option:selected').val();
                $.ajax({
			url: "savesession.php?name=dispatcherid&value="+dispatcherid,
			success: function(data) {
				window.location.reload();
			}
		});
            });
        });

</script>
    <?php
    }
    //extra-headers close

    public function pageContent() {
        $menuitem = "reportdispatcher";
        include "sidemenu.".$this->currUser->usertype.".php";
        ?>

<div id="orderinfo"></div>
<div class="grid_10">
	<?php
	$db = new DBConn();
	$store_id = getCurrUserId();
	$dtarr = explode(" - ", $this->dtrange);
	if (count($dtarr) == 1) {
		list($dd,$mm,$yy) = explode("-",$dtarr[0]);
		$sdate = "$yy-$mm-$dd";
		$dQuery = " and date(p.shipped_time) = '$sdate'";
               
	} else if (count($dtarr) == 2) {
		list($dd,$mm,$yy) = explode("-",$dtarr[0]);
		$sdate = "$yy-$mm-$dd";
		list($dd,$mm,$yy) = explode("-",$dtarr[1]);
		$edate = "$yy-$mm-$dd";
		$dQuery = " and date(p.shipped_time) >= '$sdate' and date(p.shipped_time) <= '$edate'";
               

	} else {
		$dQuery = "";
	}
        if ($this->dispatcherid=="All Dispatchers") {
            $sQuery = "";
        } else {
            $sQuery = " and p.dispatcher_id=$this->dispatcherid";
        }

        $query="select o.id,c.store_name,p.dispatcher_id,count(*) as ordernum from it_ck_orders o, it_ck_pickgroup p, it_codes c where o.pickgroup = p.id and p.dispatcher_id=c.id and p.order_ids is not null $dQuery $sQuery group by p.dispatcher_id";
	$orders = $db->fetchObjectArray($query);
        $query="select p.dispatcher_id, sum(p.order_qty) as total_orders, sum(p.shipped_qty) as shipped_qty, avg(timestampdiff(MINUTE,p.active_time,p.picking_time)) as avg_pick, avg(timestampdiff(MINUTE,p.picking_time,p.shipped_time)) as avg_ship from it_ck_pickgroup p where p.order_ids is not null $dQuery $sQuery group by p.dispatcher_id";
        $orders2 = $db->fetchObjectArray($query);
        $allDispatchers = $db->fetchObjectArray("select * from it_codes where usertype=2");
        $totdispatchers = count($allDispatchers);
        $ordersplaced=""; $ordersumqty=""; $shiptotqty=""; $tot=0; $totpickh=0; $totpickm=0; $totshiph=0; $totshipm=0;?>
        
    <div class="box">
        <h2>Dispatcher Report</h2><br>
<span style="font-weight:bold;">Date Filter : </span> <input type="text" id="dateselect" name="dateselect" value="<?php echo $this->dtrange; ?>" /> (Click in the box to see date options)
<span style="font-weight:bold; margin-left:40px;">Select Dispatcher : </span><select id="storeselect" name="storeselect" style="width:180px; text-align: center;"><option >All Dispatchers</option><?php foreach ($allDispatchers as $dispatcher) { ?><option value="<?php echo $dispatcher->id;?>" <?php if ($this->dispatcherid==$dispatcher->id) {?> selected="selected" <?php } ?>><?php echo $dispatcher->store_name; ?></option><?php } ?></select><br /><br />
        <div style="margin-top: 0px; margin-right: 0px; margin-bottom: 0px; margin-left: 0px; position: static; overflow:auto; ">
                    <table>
                        <tr>
                            <th>Dispatcher Name</th>
                            <th style="text-align:right;">No. of orders</th>
                            <th style="text-align:right;">Ordered Qty</th>
                            <th style="text-align:right;">Average Order Qty</th>
                            <th style="text-align:right;">Shipped Qty</th>
                            <th style="text-align:right;">Average Shipped Qty</th>
                            <th style="text-align:right;">Drop Percentage (%)</th>
                            <th>Average Time to Pick an Order</th>
                            <th>Average Time to Ship an Order</th>
                        </tr>
                        <?php
				$tot_num_orders = 0; $tot_order_qty = 0; $tot_ship_qty = 0; 
				//foreach ($orders as $order) {
                                for ($i=0;$i<count($orders);$i++) {
			?>
                        <tr>
                            <td><?php echo $orders[$i]->store_name; ?></td>
                            <td style="text-align:right;"><?php echo $orders[$i]->ordernum; ?></td>
                            <td style="text-align:right;"><?php echo $orders2[$i]->total_orders; ?></td>
                            <td style="text-align:right;"><?php echo intval($orders2[$i]->total_orders/$orders[$i]->ordernum); ?></td>
                            <td style="text-align:right;"><?php echo $orders2[$i]->shipped_qty; ?></td>
                            <td style="text-align:right;"><?php echo intval($orders2[$i]->shipped_qty/$orders[$i]->ordernum); ?></td>
                            <td style="text-align:right;"><?php if ($orders2[$i]->total_orders=="0") echo "0"; else echo sprintf("%0.2f",(($orders2[$i]->total_orders-$orders2[$i]->shipped_qty)/($orders2[$i]->total_orders))*100); ?></td>
                            <td><?php if (intval($orders2[$i]->avg_pick/60)>0) { $h=intval($orders2[$i]->avg_pick/60); $totpickh+=$h;echo intval($h)."h ";} $m=$orders2[$i]->avg_pick%60; $totpickm+=$m; echo ($m)."m"; ?></td>
                            <td><?php if (intval($orders2[$i]->avg_ship/60)>0) { $h=intval($orders2[$i]->avg_ship/60); $totshiph+=$h;echo intval($h)."h ";} $m=$orders2[$i]->avg_ship%60; $totshipm+=$m; echo ($m)."m"; ?></td>
                        </tr>
			<?php
				$tot++; $ordersplaced+=$orders[$i]->ordernum; $ordersumqty+=$orders2[$i]->total_orders; $shiptotqty+=$orders2[$i]->shipped_qty;
				}
				if ($ordersumqty == 0) { $pct=""; }
				else { $pct=sprintf("%0.2f",(($ordersumqty-$shiptotqty)/$ordersumqty)*100); }
			?>
                        <tr style="font-weight:bold;">
                            <td>TOTALS</td>
                            <td style="text-align:right;"><?php echo $ordersplaced; ?></td>
                            <td style="text-align:right;"><?php echo $ordersumqty; ?></td>
                            <td style="text-align:right;"><?php if ($ordersplaced) echo intval($ordersumqty/$ordersplaced); ?></td>
                            <td style="text-align:right;"><?php echo $shiptotqty; ?></td>
                            <td style="text-align:right;"><?php if ($ordersplaced) echo intval($shiptotqty/$ordersplaced); ?></td>
                            <td style="text-align:right;"><?php echo $pct; ?></td>
                            <td><?php if ($tot>0) {echo intval($totpickh/$tot)."h ";echo intval($totpickm/$tot)." m"; }?></td>
                            <td><?php if ($tot>0) {echo intval($totshiph/$tot)."h ";echo intval($totshipm/$tot)." m"; }?></td>
                        </tr>
                    </table>
        </div>
    </div>
</div>
    <?php
    }
}
?>
