<?php
require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "session_check.php";

class cls_admin_orders_active extends cls_renderer {
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

    //--><!]]>
</script>
    <?php
    }
    //extra-headers close

    public function pageContent() {
        $menuitem = "aactiveorders";
        include "sidemenu.".$this->currStore->usertype.".php";
//        if ($this->currStore->usertype == UserType::Admin || $this->currStore->usertype == UserType::CKAdmin || $this->currStore->usertype == UserType::Manager) {} else { print "Unauthorized Access"; return; }
        ?>

<div class="grid_10">
            <?php
            $db = new DBConn();
            $store_id = getCurrUserId();

            $orders = $db->fetchObjectArray ("select o.*, c.store_name from it_ck_orders o, it_codes c where o.store_id = c.id and o.status=".OrderStatus::Active." order by o.active_time desc");

            ?>
    <div class="box">
        <h2>
            <a href="#" id="toggle-accordion" style="cursor: pointer; ">Active Orders</a>
        </h2><br>
        <div style="margin-top: 0px; margin-right: 0px; margin-bottom: 0px; margin-left: 0px; position: static; overflow-x: hidden; overflow-y: hidden; ">
            <div class="block" id="accordion" style="margin-top: 0px; margin-right: 0px; margin-bottom: 0px; margin-left: 0px; ">
                <div id="accordion">
                    <table>
                        <tr>
                            <th>Sr. No</th>
                            <th style="width: 110px;">Store</th>
                            <th>Order No</th>
                            <th>Status</th>
                            <th style="width: 110px;">Order Date</th>
                            <th style="width: 110px;">Total Items</th>
                            <th style="width: 90px;">Total Price</th>
                            <th>Number Of Designs</th>
                            <th>View Order</th>
                        </tr>
                        
                         <?php
                                        $tq =0;
                                        $ta =0;
                                        $membershiporderqty=0;
                                        $membershipordervalue=0;
                                    foreach ($orders as $order) {
                                        $membershipquery = "SELECT EXISTS ( SELECT 1 FROM it_ck_orders o JOIN it_ck_orderitems oi ON o.id = oi.order_id JOIN it_items i ON oi.item_id = i.id WHERE o.id = $order->id AND i.ctg_id = 65 ) AS result";//check the order contains Membership ctg barcode
                                        $resultmembershipquery=$db->fetchObject($membershipquery);
                                        if ($resultmembershipquery && $resultmembershipquery->result > 0) {$membershiporderqty+=$order->order_qty; $membershipordervalue+=$order->order_amount;}                      
                                        $tq += $order->order_qty;
                                        $ta += $order->order_amount;
                                    }
                                 ?>

                         <tr style="font-weight:bold;">
                            <td></td>
                            <td>TOTAL</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td><?php echo $tq." (MB = $membershiporderqty)"; ?></td>
                            <td><?php echo $ta." (MB = $membershipordervalue)"; ?></td>
                            <td></td>
                            <td></td>
                        </tr>
                        
                            <?php
				$count=0;
				$tot_qty=0;
				$tot_amount=0;
				foreach ($orders as $order) {
				$count++;
				$tot_qty += $order->order_qty;
				$tot_amount += $order->order_amount;
			    ?>
                        <tr>
                            <td><?php echo $count; ?></td>
                            <td><?php echo $order->store_name; ?></td>
                            <td><?php echo $order->order_no; ?></td>
                            <td><?php echo OrderStatus::getName($order->status); ?></td>
                            <td><?php echo $order->active_time; ?></td>
                            <td><?php echo $order->order_qty; ?></td>
                            <td><?php echo $order->order_amount; ?></td>
                            <td><?php echo $order->num_designs; ?></td>
                            <td><a href="store/vieworder/oid=<?php echo $order->id;?>"><button type="button">View Order</button></a></td>
                        </tr>
			<?php } ?>
                        <tr style="font-weight:bold;">
                            <td></td>
                            <td>TOTAL</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td><?php echo $tot_qty."  (MB = $membershiporderqty)"; ?></td>
                            <td><?php echo $tot_amount."  (MB = $membershipordervalue)"; ?></td>
                            <td></td>
                            <td></td>
                        </tr>
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