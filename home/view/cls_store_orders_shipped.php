<?php
require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once "session_check.php";

class cls_store_orders_shipped extends cls_renderer {
    var $currStore;
    var $params;
    function __construct($params=null) {
        $this->currStore = getCurrUser();
        $this->params = $params;
    }
    function extraHeaders() {
        if (!$this->currStore) {
            ?>
<h2>Session Expired</h2>
Your session has expired. Click <a href="">here</a> to login.
            <?php
            return;
        }
        ?>
<script type="text/javascript" src="js/expand.js"></script>
<link rel="stylesheet" href="jqueryui/css/custom-theme/jquery-ui-1.8.14.custom.css" type="text/css" media="screen" charset="utf-8" />
<link rel="stylesheet" href="css/prettyPhoto.css" type="text/css" media="screen" title="prettyPhoto main stylesheet" charset="utf-8" />
<script src="js/prettyPhoto/jquery.prettyPhoto.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript">
    <!--//--><![CDATA[//><!--
    $(function() {
        // --- Using the default options:
        //$("h2.expand").toggler();
        // --- Other options:
        //$("h2.expand").toggler({method: "toggle", speed: "slow"});
        //$("h2.expand").toggler({method: "toggle"});
        //$("h2.expand").toggler({speed: "fast"});
        //$("h2.expand").toggler({method: "fadeToggle"});
        $("h2.expand").toggler({method: "slideFadeToggle"});
        $("#content").expandAll({trigger: "h2.expand"});
    });

    $(function(){
        $("a[rel^='prettyPhoto']").prettyPhoto({animation_speed:'fast',slideshow:3000, hideflash: true});
    });

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

    //--><!]]>
</script>
    <?php
    }
    //extra-headers close

    public function pageContent() {
        $menuitem = "sshippedorders";
        include "sidemenu.".$this->currStore->usertype.".php";
        ?>

<div id="orderinfo"></div>
<div class="grid_10">
            <?php
            $db = new DBConn();
            $store_id = getCurrUserId();

//            $orders = $db->fetchObjectArray ("select o.*, c.store_name, u.store_name as dispatcher from it_ck_orders o, it_codes c, it_codes u where o.store_id = $store_id and o.store_id = c.id and o.status=".OrderStatus::Shipped." and o.dispatcher_id = u.id order by o.active_time desc");
//            $orders = $db->fetchObjectArray ("select o.*, c.store_name, u.store_name as dispatcher, p.store_name as picker from it_ck_orders o left outer join it_codes p on o.picker_id = p.id, it_codes c, it_codes u where o.store_id = $store_id and o.store_id = c.id and o.status=".OrderStatus::Shipped." and o.dispatcher_id = u.id order by o.shipped_time desc");
	    $orders = $db->fetchObjectArray("select p.*, max(o.active_time) as active_time, c2.store_name as picker, u.store_name as dispatcher, c.store_name from it_ck_orders o, it_ck_pickgroup p left outer join it_codes c2 on p.picker_id = c2.id, it_codes c, it_codes u where p.storeid = $store_id and o.store_id = c.id and p.dispatcher_id = u.id and o.pickgroup = p.id and o.status = ".OrderStatus::Shipped." group by o.pickgroup order by p.picking_time desc");

            ?>
    <div class="box">
        <h2>
            <a href="#" id="toggle-accordion" style="cursor: pointer; ">Shipped Orders</a>
        </h2><br>
        <div style="margin-top: 0px; margin-right: 0px; margin-bottom: 0px; margin-left: 0px; position: static; overflow-x: hidden; overflow-y: hidden; ">
            <div class="block" id="accordion" style="margin-top: 0px; margin-right: 0px; margin-bottom: 0px; margin-left: 0px; ">
                <div id="accordion">
                    <table>
                        <tr>
                            <th>Order No</th>
                            <th>Order Date</th>
                            <th>Shipped Date</th>
                            <th>Total Items</th>
                            <th>Total Price</th>
                            <th>Number Of Designs</th>
                            <th>Total Shipped Quantity</th>
                            <th>Total Shipped Price</th>
                            <th>Total Shipped Cheque Amount</th>
                            <th>Dispatcher</th>
                            <th></th>
                        </tr>
                            <?php foreach ($orders as $order) {
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
			    ?>
                        <tr>
                            <td><?php echo $order->order_nos; ?> <a style="text-decoration:underline;" href="#" onclick='javascript:showDialog(<?php echo $dialogHtml; ?>);return false;'><br />Details</a></td>
                            <td><?php echo mmddyy($order->active_time); ?></td>
                            <td><?php echo mmddyy($order->shipped_time); ?></td>
                            <td><?php echo $order->order_qty; ?></td>
                            <td><?php echo $order->order_amount; ?></td>
                            <td><?php echo $order->num_designs; ?></td>
                            <td><?php echo $order->shipped_qty; ?></td>
                            <td><?php echo $order->shipped_mrp; ?></td>
                            <td><?php echo $order->cheque_amt; ?></td>
                            <td><?php echo $order->dispatcher; ?></td>
                            <td><a href="store/vieworder/pid=<?php echo $order->id;?>"><button>View Order</button></a></td>
                        </tr>
			<?php } ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
    <?php
    }
}
?>
