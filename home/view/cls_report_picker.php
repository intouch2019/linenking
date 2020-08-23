<?php
require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once "session_check.php";

class cls_report_picker extends cls_renderer {
    var $params;
    var $dtrange;
    var $pickerid;
    var $currUser;
    function __construct($params=null) {
	//parent::__construct(array(UserType::Admin, UserType::CKAdmin, UserType::Manager));
        $this->params = $params;
        $this->currUser = getCurrUser();
	if (isset($_SESSION['account_dtrange'])) { $this->dtrange = $_SESSION['account_dtrange']; }
	else { $this->dtrange = date("d-m-Y"); }
        
        if (isset($_SESSION['pickerid'])) { $this->pickerid = $_SESSION['pickerid']; }
	else { $this->pickerid = "All Pickers"; }
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
                var pickerid= $('select option:selected').val();
                $.ajax({
			url: "savesession.php?name=pickerid&value="+pickerid,
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
        $menuitem = "reportpicker";
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
        
        if ($this->pickerid=="All Pickers") {
            $sQuery = "";
        } else {
            $sQuery = " and p.picker_id=$this->pickerid";
        }

        $query="select o.id,c.store_name,p.picker_id,count(*) as ordernum from it_ck_orders o, it_ck_pickgroup p, it_codes c where o.pickgroup = p.id and p.picker_id=c.id and p.order_ids is not null $dQuery $sQuery group by p.picker_id";
	$orders = $db->fetchObjectArray($query);
        $query="select p.picker_id, sum(p.order_qty) as total_orders, sum(p.shipped_qty) as shipped_qty, avg(timestampdiff(MINUTE,p.picking_time,p.printing_time)) as avg_print, avg(timestampdiff(MINUTE,p.picking_time,p.shipped_time)) as avg_ship from it_ck_pickgroup p where p.order_ids is not null $dQuery $sQuery group by p.picker_id";
        $orders2 = $db->fetchObjectArray($query);
        $allPickers = $db->fetchObjectArray("select * from it_codes where usertype=5");
        $totdispatchers = count($allPickers);
        $ordersplaced=""; $ordersumqty=""; $shiptotqty=""; $tot=0; $totprinth=0; $totprintm=0; $totshiph=0; $totshipm=0;?>
        
    <div class="box">
        <h2>Picker Report</h2><br>
<span style="font-weight:bold;">Date Filter : </span> <input type="text" id="dateselect" name="dateselect" value="<?php echo $this->dtrange; ?>" /> (Click in the box to see date options)
<span style="font-weight:bold; margin-left:40px;">Select Picker :</span><select id="storeselect" name="storeselect" style="width:180px; text-align: center;"><option >All Pickers</option><?php foreach ($allPickers as $picker) { ?><option value="<?php echo $picker->id;?>" <?php if ($this->pickerid==$picker->id) {?> selected="selected" <?php } ?>><?php echo $picker->store_name; ?></option><?php } ?></select><br /><br />
        <div style="margin-top: 0px; margin-right: 0px; margin-bottom: 0px; margin-left: 0px; position: static; overflow:auto; ">
                    <table>
                        <tr>
                            <th>Picker Name</th>
                            <th style="text-align:right;">No. of orders</th>
                            <th style="text-align:right;">Ordered Qty</th>
                            <th style="text-align:right;">Average Ordered Qty</th>
                            <th style="text-align:right;">Shipped Qty</th>
                            <th style="text-align:right;">Average Shipped Qty</th>
                            <th style="text-align:right;">Drop Percentage (%)</th>
                            <th>Average Time to Print Order after Order Picked</th>
                            <th>Average Time from Printing an Order to Shipped</th>
                        </tr>
                        <?php $tot_avg_shiptime = 0; $tot_avg_printtime=0; $tot=0; 
                        //foreach ($orders as $order) { 
                        for ($i=0;$i<count($orders);$i++) { ?>
                        <tr>
                            <td><?php echo $orders[$i]->store_name; ?></td>
                            <td style="text-align:right;"><?php echo $orders[$i]->ordernum; ?></td>
                            <td style="text-align:right;"><?php echo $orders2[$i]->total_orders; ?></td>
                            <td style="text-align:right;"><?php echo intval($orders2[$i]->total_orders/$orders[$i]->ordernum);?></td>
                            <td style="text-align:right;"><?php echo $orders2[$i]->shipped_qty; ?></td>
                            <td style="text-align:right;"><?php echo intval($orders2[$i]->shipped_qty/$orders[$i]->ordernum);?></td>
                            <td style="text-align:right;"><?php if ($orders2[$i]->total_orders > 0) echo sprintf("%0.2f",((($orders2[$i]->total_orders-$orders2[$i]->shipped_qty)/($orders2[$i]->total_orders))*100)); else echo "0"; ?></td>
                            <td><?php if (intval($orders2[$i]->avg_print/60)>0) { $h=intval($orders2[$i]->avg_print/60); $totshiph+=$h;echo intval($h)."h ";} $m=$orders2[$i]->avg_print%60; $totshipm+=$m; echo ($m)."m"; ?></td>                            
                            <td><?php if (intval($orders2[$i]->avg_ship/60)>0) { $h=intval($orders2[$i]->avg_ship/60); $totshiph+=$h;echo intval($h)."h ";} $m=$orders2[$i]->avg_ship%60; $totshipm+=$m; echo ($m)."m"; ?></td>
                        </tr>
			<?php $tot++; $ordersplaced+=$orders[$i]->ordernum; $ordersumqty+=$orders2[$i]->total_orders; $shiptotqty+=$orders2[$i]->shipped_qty;  $tot_avg_shiptime+=$orders2[$i]->avg_ship; $tot_avg_printtime +=$orders2[$i]->avg_print;
			} 
				if ($ordersumqty == 0) { $pct = ""; }
				else {	$pct = sprintf("%0.2f",(($ordersumqty-$shiptotqty)/$ordersumqty)*100); }
				if ($tot == 0) { $pct_ship_time = ""; $pct_print_time= ""; }
				else {
					$tot_avg_shiptime = $tot_avg_shiptime / $tot;
                                        $tot_avg_printtime = $tot_avg_printtime / $tot;
					$pct_ship_time = "";
                                        $pct_print_time= "";
                            		if (intval($tot_avg_shiptime/60)>0) {
						$h=intval($tot_avg_shiptime/60);
						$pct_ship_time .= $h."h ";
					}
                                        if (intval($tot_avg_printtime/60)>0) {
						$h=intval($tot_avg_printtime/60);
						$pct_print_time .= $h."h ";
					}
					$mship=$tot_avg_shiptime%60;
                                        $mprint=$tot_avg_printtime%60;
					$pct_ship_time .= $mship."m";
                                        $pct_print_time .= $mprint."m";
				}
			?>
                        <tr style="font-weight:bold;">
                            <td>TOTALS</td>
                            <td style="text-align:right;"><?php echo $ordersplaced; ?></td>
                            <td style="text-align:right;"><?php echo $ordersumqty; ?></td>
                            <td style="text-align:right;"><?php if ($ordersplaced) echo intval($ordersumqty/$ordersplaced); ?></td>
                            <td style="text-align:right;"><?php echo $shiptotqty; ?></td>
                            <td style="text-align:right;"><?php if ($ordersplaced) echo intval($shiptotqty/$ordersplaced);?></td>
                            <td style="text-align:right;"><?php echo $pct; ?></td>
                            <td><?php echo $pct_print_time; ?></td>
                            <td><?php echo $pct_ship_time; ?></td>
                        </tr>
                    </table>
        </div>
    </div>
</div>
    <?php
    }
}
?>
