<?php
require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once "session_check.php";

class cls_store_orders_active extends cls_renderer {
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
    
    function getPaymentLink(orderid){
    
    const url = "formpost/genProformaInvPdf.php?orderid=" + orderid;
    window.location.href = url;
    }
</script>
    <?php
    }
    //extra-headers close

    public function pageContent() {
        $menuitem = "sactiveorders";
        include "sidemenu.".$this->currStore->usertype.".php";
        ?>

<div class="grid_10">
            <?php
            $db = new DBConn();
            $store_id = getCurrUserId();

            $query = "select * from it_ck_orders where store_id=$store_id and status in(".OrderStatus::Active.",".OrderStatus::Proforma.") order by id desc";
            $orders = $db->fetchObjectArray ($query);
//              $query = "SELECT o.*, sum(oi.order_qty) as disqty FROM `it_ck_orders` o , it_ck_orderitems oi , it_items i WHERE oi.order_id = o.id and o.status = ".OrderStatus::Active." and o.store_id = $store_id and oi.item_id = i.id and i.ctg_id != (select id from it_categories where name = 'Others') group by o.id order by o.active_time desc";
//              $orders = $db->fetchObjectArray($query);
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
                            <th>Order No</th>
                            <th>Status</th>
                            <th>Order Date</th>
                            <th>Total Items</th>
                            <th>Total Price</th>
                            <th>Number Of Designs</th>
                            <th>View Order</th>
                            <th>Get Payment Link</th>
                        </tr>
                            <?php foreach ($orders as $order) {
			    ?>
                        <tr>
                            <td><?php echo $order->order_no; ?></td>
                            <td><?php echo OrderStatus::getName($order->status); ?></td>
                            
                            <?php if($order->status== OrderStatus::Proforma){ ?>
                            <td><?php echo mmddyy($order->proforma_time); ?></td>
                            <?php } else {?>
                            <td><?php echo mmddyy($order->active_time); ?></td>
                            <?php } ?>
                            
                            <td><?php echo $order->order_qty; ?></td>
                            <td><?php echo $order->order_amount; ?></td>
                            <td><?php echo $order->num_designs; ?></td>
                            
                            <td><a href="store/vieworder/oid=<?php echo $order->id;?>"><button>View Order</button></a></td>
                            <?php if($order->status== OrderStatus::Proforma){ ?><td><button onclick="getPaymentLink(<?php echo $order->id;?>)">Get Payment Link</button></td><?php }else{ echo "<td></td>";}?>
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
