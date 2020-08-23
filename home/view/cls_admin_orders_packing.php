<?php
require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once "session_check.php";

class cls_admin_orders_packing extends cls_renderer {
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
        $menuitem = "apackingorders";
        include "sidemenu.".$this->currStore->usertype.".php";
        //if ($this->currStore->usertype == UserType::Admin || $this->currStore->usertype == UserType::CKAdmin || $this->currStore->usertype == UserType::Manager) {} else { print "Unauthorized Access"; return; }
        ?>

<div class="grid_10">
            <?php
            $db = new DBConn();
            $store_id = getCurrUserId();

       //     $orders = $db->fetchObjectArray ("select o.*, c.store_name, u.store_name as dispatcher from it_ck_orders o, it_codes c, it_codes u where o.store_id = c.id and o.status=".OrderStatus::Picking." and o.dispatcher_id = u.id order by o.active_time desc");
	    $orders = $db->fetchObjectArray("select p.*, o.pickgroup, o.status, max(o.active_time) as active_time, u.store_name as dispatcher, c.store_name from it_ck_orders o, it_ck_pickgroup p, it_codes c, it_codes u where o.store_id = c.id and p.dispatcher_id = u.id and o.pickgroup = p.id and o.status = ".OrderStatus::Picking." group by o.pickgroup order by p.picking_time desc");

            ?>
    <div class="box">
        <h2>
            <a href="#" id="toggle-accordion" style="cursor: pointer; ">Orders currently being packed</a>
        </h2><br>
        <div style="margin-top: 0px; margin-right: 0px; margin-bottom: 0px; margin-left: 0px; position: static; overflow-x: hidden; overflow-y: hidden; ">
            <div class="block" id="accordion" style="margin-top: 0px; margin-right: 0px; margin-bottom: 0px; margin-left: 0px; ">
                <div id="accordion">
                    <table>
                        <tr>
                            <th>Sr. No</th>
                            <th>Store</th>
                            <th>Order No(s)</th>
                            <th>Order Date</th>
                            <th>Picking Date</th>
                            <th>Print Date</th>
                            <th>Total Items</th>
                            <th>Total Price</th>
                            <th>Designs</th>
                            <th>Dispatcher</th>
                            <th></th>
                        </tr>
                            <?php
				$count=0; $tot_qty = 0; $tot_amount=0;
				foreach ($orders as $order) {
					$count++;	
				$tot_qty += $order->order_qty;
				$tot_amount += $order->order_amount;
			    ?>
                        <tr>
                            <td><?php echo $count; ?></td>
                            <td><?php echo $order->store_name; ?></td>
                            <td><?php echo $order->order_nos; ?></td>
                            <td><?php echo mmddyy($order->active_time); ?></td>
                            <td><?php echo mmddyy($order->picking_time); ?></td>
                            <td><?php echo mmddyy($order->printing_time); ?></td>
                            <td><?php echo $order->order_qty; ?></td>
                            <td><?php echo $order->order_amount; ?></td>
                            <td><?php echo $order->num_designs; ?></td>
                            <td><?php echo $order->dispatcher;?></td>
                            <td><a href="dispatch/vieworder/pid=<?php echo $order->id;?>"><button>View Order</button></a></td>
                        </tr>
			<?php } ?>
                        <tr style="font-weight:bold;">
                            <td></td>
                            <td>TOTAL</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td><?php echo $tot_qty; ?></td>
                            <td><?php echo $tot_amount; ?></td>
                            <td></td>
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
