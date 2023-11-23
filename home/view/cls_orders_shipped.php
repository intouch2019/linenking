<?php
require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once "session_check.php";

class cls_orders_shipped extends cls_renderer {
    var $params;
    var $dtrange;
    var $store_id;
    var $dispatcherid;
    var $currStore;
    function __construct($params=null) {
	//parent::__construct(array(UserType::Admin, UserType::CKAdmin, UserType::Dispatcher, UserType::Manager));
        $this->params = $params;
        $this->currStore = getCurrUser();
	if (isset($_SESSION['shipped_dtrange'])) { $this->dtrange = $_SESSION['shipped_dtrange']; }
	else { $this->dtrange = date("d-m-Y"); }
        if (isset($params['sid'])) { $this->store_id = $params['sid']; }
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
			url: "savesession.php?name=shipped_dtrange&value="+dtrange,
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
			url: "savesession.php?name=shipped_dtrange&value="+dtrange,
			success: function(data) {
				window.location.reload();
			}
		});
	}

	$(function(){
		modal = $("#orderinfo").dialog({
			autoOpen: false,
			title: 'Order Details'
		});
	});
	function showDialog(content) {
		$("#orderinfo").html(content);
		modal.dialog('open');
	}
        $(function(){
            $("#dispatcherselect").change(function () {
                var dispatcherid= $('#dispatcherselect option:selected').val();
                //alert(dispatcherid);
                $.ajax({
			url: "savesession.php?name=dispatcherid&value="+dispatcherid,
			success: function(data) {
				window.location.reload();
			}
		});
            });
        });
    //--><!]]>
</script>
    <?php
    }
    //extra-headers close

    public function pageContent() {
        $menuitem = "shippedorders";
        include "sidemenu.".$this->currStore->usertype.".php";
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
        $sQuery = "";
	if ($this->store_id) { $sQuery = " and p.storeid=$this->store_id"; }
        if ($this->dispatcherid=="All Dispatchers") {
            $dsQuery = "";
        } else {
            $dsQuery = " and p.dispatcher_id=$this->dispatcherid";
        }
	$query = "select p.*, max(o.active_time) as active_time, c2.store_name as picker, u.store_name as dispatcher, c.store_name,c.is_natch_required from it_ck_orders o, it_ck_pickgroup p left outer join it_codes c2 on p.picker_id = c2.id, it_codes c, it_codes u where o.store_id = c.id and p.dispatcher_id = u.id and o.pickgroup = p.id and o.status = ".OrderStatus::Shipped." $dQuery $sQuery $dsQuery group by o.pickgroup order by p.shipped_time desc";
        $orders = $db->fetchObjectArray($query);
        $allDispatchers = $db->fetchObjectArray("select * from it_codes where usertype=2");
        $currdispatcher = $db->fetchObject("select * from it_codes where id=$store_id and usertype=2");
	?>
    <div class="box">
        <h2>Shipped Orders</h2><br>
<span style="font-weight:bold; margin-left: 30px;">DATE FILTER:</span> <input type="text" style="margin-left: 10px;"id="dateselect" name="dateselect" value="<?php echo $this->dtrange; ?>" /> (Click in the box to see date options)
<span style="font-weight:bold; margin-left: 30px;">SELECT STORE:</span><select name="storeSelect" onchange="location=this.options[this.selectedIndex].value;" style="margin-left: 10px; width:15%;">
    	<?php	$arr = $db->fetchObjectArray("select distinct p.storeid, c.store_name from it_ck_pickgroup p, it_codes c where p.storeid = c.id and p.shipped_qty > 0 order by c.store_name");
		print '<option value="orders/shipped">Show All</option>';
		foreach ($arr as $store) {
			if ($store->storeid == $this->store_id) { $selected = "selected"; } else { $selected = ""; }
        ?>
			<option <?php echo $selected; ?> value="orders/shipped/sid=<?php echo $store->storeid; ?>"><?php echo $store->store_name; ?></option>
        <?php  } ?>
		</select><br /><br />
                <span style="font-weight:bold; margin-left:30px;">SELECT DISPATCHER : </span><select id="dispatcherselect" name="dispatcherselect" style="width:180px; text-align: center; margin-left:10px;"><option >All Dispatchers</option><?php foreach ($allDispatchers as $dispatcher) { ?><option value="<?php echo $dispatcher->id;?>" <?php if ($this->dispatcherid==$dispatcher->id) {?> selected="selected" <?php } ?>><?php echo $dispatcher->store_name; ?></option><?php } ?></select><br /><br />
                
        <div style="margin-top: 0px; margin-right: 0px; margin-bottom: 0px; margin-left: 0px; position: static; overflow:auto; ">
                    <table>
                        <tr>
                            <th>Sr. No</th>
                            <th>Store</th>
                            <th>Order No</th>
                            <th>Order Date</th>
                            <th>Picking Date</th>
                            <th>Shipped Date</th>
                            <th>Number Of Designs</th>
                            <th>Total Ordered Items</th>
                            <th>Total Shipped Quantity</th>
                            <th>Total Shipped Price</th>
                            <th>Is Nach Available</th>
                            <th>Total Shipped Cheque Amount</th>
                            <th>Transport Details</th>
                            <th>Dispatcher</th>
                            <th></th>
                            <th></th>
                        </tr>
                        
                        
                                <?php
                                    $cnt = 0;
                                    $t_qty = 0;
                                    $t_shipped_qty = 0;
                                    $t_shipped_mrp = 0;
                                    $t_cheque_amt = 0;
                                    foreach ($orders as $order) {
                                        $cnt++;
                                        $t_qty += $order->order_qty;
                                        $t_shipped_qty += $order->shipped_qty;
                                        $t_shipped_mrp += $order->shipped_mrp;
                                        $t_cheque_amt += intval($order->cheque_amt);
                                    }
                                ?>
                        
                        
                          <tr style="font-weight:bold;">
                            <td></td>
                            <td>TOTALS</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td><?php echo $t_qty; ?></td>
                            <td><?php echo $t_shipped_qty; ?></td>
                            <td><?php echo $t_shipped_mrp; ?></td>
                            <td></td>
                            <td><?php echo $t_cheque_amt; ?></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        
                        
                            <?php
				$count=0; $tot_qty=0; $tot_shipped_qty=0; $tot_shipped_mrp=0; $tot_cheque_amt=0;
				foreach ($orders as $order) {
					$count++;
$dialogHtml = '<table border="0">';
$dialogHtml .= "<tr>";
$dialogHtml .= "<th colspan=2>$order->store_name</th>";
$dialogHtml .= "</tr>";
$dialogHtml .= "<tr>";
$dialogHtml .= "<td>Order Date:</td><td>".mmddyy($order->active_time)."</td>";
$dialogHtml .= "</tr>";
$dialogHtml .= "<tr>";
$dialogHtml .= "<td>Order No:</td><td>$order->order_nos</td>";
$dialogHtml .= "</tr>";
$dialogHtml .= "<tr>";
$dialogHtml .= "<td>Order Quantity:</td><td>$order->order_qty</td>";
$dialogHtml .= "</tr>";
$dialogHtml .= "<tr>";
$dialogHtml .= "<td>Order Amount:</td><td>$order->order_amount</td>";
$dialogHtml .= "</tr>";
$dialogHtml .= "<tr>";
$dialogHtml .= "<td>Designs:</td><td>$order->num_designs</td>";
$dialogHtml .= "</tr>";
$dialogHtml .= "<tr>";
$dialogHtml .= "<td>Invoice No:</td><td>$order->invoice_no</td>";
$dialogHtml .= "</tr>";
$dialogHtml .= "<tr>";
$dialogHtml .= "<td>Shipped Qty:</td><td>$order->shipped_qty</td>";
$dialogHtml .= "</tr>";
$dialogHtml .= "<tr>";
$dialogHtml .= "<td>Shipped MRP:</td><td>$order->shipped_mrp</td>";
$dialogHtml .= "</tr>";
$dialogHtml .= "<tr>";
$dialogHtml .= "<td>Cheque Amount:</td><td>$order->cheque_amt</td>";
$dialogHtml .= "</tr>";
$dialogHtml .= "<tr>";
$dialogHtml .= "<td>Cheque Detail:</td><td>$order->cheque_dtl</td>";
$dialogHtml .= "</tr>";
$dialogHtml .= "<tr>";
$dialogHtml .= "<td>Transport Detail:</td><td>$order->transport_dtl</td>";
$dialogHtml .= "</tr>";
$dialogHtml .= "<tr>";
$dialogHtml .= "<td>Dispatcher</td><td>$order->dispatcher</td>";
$dialogHtml .= "</tr>";
$dialogHtml .= "<tr>";
$dialogHtml .= "<td>Picker:</td><td>$order->picker</td>";
$dialogHtml .= "</tr>";
$dialogHtml .= "<tr>";
$dialogHtml .= '<td colspan=2 style="font-weight:bold;color:#ff0000;">Remarks:<br />'.$order->remark.'</td>';
$dialogHtml .= "</tr>";
$dialogHtml .= "<tr>";
$dialogHtml .= "<td>Picking Date:</td><td>".mmddyy($order->picking_time)."</td>";
$dialogHtml .= "</tr>";
$dialogHtml .= "<tr>";
$dialogHtml .= "<td>Printing Date:</td><td>".mmddyy($order->printing_time)."</td>";
$dialogHtml .= "</tr>";
$dialogHtml .= "<tr>";
$dialogHtml .= "<td>Shipped Date:</td><td>".mmddyy($order->shipped_time)."</td>";
$dialogHtml .= "</tr>";
$dialogHtml .= '</table>';
$dialogHtml = json_encode($dialogHtml);
				$tot_qty+=$order->order_qty;
				$tot_shipped_qty+=$order->shipped_qty;
				$tot_shipped_mrp+=$order->shipped_mrp;
				$tot_cheque_amt+=intval($order->cheque_amt);
			    ?>
                        <tr>
                            <td><?php echo $count; ?></td>
                            <td><?php echo $order->store_name; ?></td>
                            <td><?php echo $order->order_nos; ?> <a style="text-decoration:underline;" href="#" onclick='javascript:showDialog(<?php echo $dialogHtml; ?>);return false;'>Details</a></td>
                            <td><?php echo mmddyy($order->active_time); ?></td>
                            <td><?php echo mmddyy($order->picking_time); ?></td>
                            <td><?php echo mmddyy($order->shipped_time); ?></td>
                            <td><?php echo $order->num_designs;?></td>
                            <td><?php echo $order->order_qty; ?></td>
                            <td><?php echo $order->shipped_qty; ?></td>
                            <td><?php echo $order->shipped_mrp; ?></td>
                            <?php if($order->is_natch_required==0) echo "<td style='color:red;'>Nach Not Available</td>"; else{echo "<td style='color:green;'>This Is Natch Customer</td>";} ?>
                            <td><?php echo $order->cheque_amt; if ($order->cheque_print==0) $prn = "print"; else $prn="reprint"; echo "<a href='formpost/printCheque.php?id=".$order->id." '></br>[$prn]</a>";?></td>
                            <td><?php echo $order->transport_dtl; ?></td>
                            <td><?php echo $order->dispatcher; ?></td>
                            <td><a href="dispatch/vieworder/pid=<?php echo $order->id;?>"><button>View Order</button></a></td>
                            <td><?php if ($order->dispatcher_id == getCurrUserId()) { ?><a href="dispatch/edittransport/pid=<?php echo $order->id;?>"><button>Edit Shipping Details</button></a> <?php } ?></td>

                        </tr>
			<?php } ?>
                        <tr style="font-weight:bold;">
                            <td></td>
                            <td>TOTALS</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td><?php echo $tot_qty; ?></td>
                            <td><?php echo $tot_shipped_qty; ?></td>
                            <td><?php echo $tot_shipped_mrp; ?></td>
                            <td></td>
                            <td><?php echo $tot_cheque_amt; ?></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    </table>
        </div>
    </div>
</div>
    <?php
    }
}
?>