<?php
require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once "session_check.php";

class cls_dispatch_orders_active extends cls_renderer {
    var $currStore;
    var $params;
    var $store_id;
    function __construct($params=null) {
        $this->currStore = getCurrUser();
        $this->params = $params;
	if (isset($params['sid'])) { $this->store_id = $params['sid']; }
    }
    function extraHeaders() {
//        if (!$this->currStore) {
        ?>
<!--<h2>Session Expired</h2>-->
<!--Your session has expired. Click <a href="">here</a> to login.-->
            <?php
//            return;
//        }
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

	function selectStore(store_id) {
		alert(store_id);
	}

    //--><!]]>
</script>
    <?php
    }
    //extra-headers close

    public function pageContent() {
        $menuitem = "dactiveorders";
        include "sidemenu.".$this->currStore->usertype.".php";
	//if ($this->currStore->usertype != UserType::Dispatcher) { print "Unauthorized access"; return; }
        ?>

<div class="grid_10">
            <?php
            $db = new DBConn();
            $membershiporderqty=0;
            $membershipordervalue=0;
	    $sQuery = "";
	    if ($this->store_id) { $sQuery = " and o.store_id=$this->store_id"; }
            $orders = $db->fetchObjectArray ("select o.*, c.store_name from it_ck_orders o, it_codes c where o.store_id = c.id $sQuery and o.status=".OrderStatus::Active." order by o.active_time desc");

            ?>
    <div class="box">
        <h2>
            <a href="#" id="toggle-accordion" style="cursor: pointer; ">Active Orders</a>
        </h2><br />
	<div style="font-weight:bold;font-size:1.2em;">
		SELECT STORE: <select name="storeSelect" onchange="location=this.options[this.selectedIndex].value;">
<?php
		$arr = $db->fetchObjectArray("select distinct o.store_id, c.store_name from it_ck_orders o, it_codes c where o.store_id = c.id and o.status=".OrderStatus::Active." order by c.store_name");
		print '<option value="dispatch/orders/active">Show All</option>';
		foreach ($arr as $store) {
			if ($store->store_id == $this->store_id) { $selected = "selected"; } else { $selected = ""; }
?>
			<option <?php echo $selected; ?> value="dispatch/orders/active/sid=<?php echo $store->store_id; ?>"><?php echo $store->store_name; ?></option>
<?php
		}
?>
		</select>
                <span style="display: inline-block; width: 12px; height: 12px; background-color: #87CEEB; border: 1px solid black; border-radius: 5px; margin-left: 20px;"></span> Membership Order
	</div>
        <div style="margin-top: 0px; margin-right: 0px; margin-bottom: 0px; margin-left: 0px; position: static; overflow-x: hidden; overflow-y: hidden; ">
            <div class="block" id="accordion" style="margin-top: 0px; margin-right: 0px; margin-bottom: 0px; margin-left: 0px; ">
                <div id="accordion">
                    <table>
                        <tr>
                            <th>Sr. No</th>
                            <th>Store</th>
                            <th>Order No</th>
                            <th><?php if ($this->store_id) { ?><input type="checkbox" name="all" id="checkall">Check All</button><?php } ?> </th>
                            <th>Status</th>
                            <th>Membership Order</th>
                            <th>Order Date</th>
                            <th>Total Items</th>
                            <th>Total Price</th>
                            <th>Number Of Designs</th>
                            <th></th>
                        </tr>
                         <?php
			$count = 0; $tot_qty=0; $tot_amount=0; $tot_qtyt=0; $tot_amountt=0;
			 $ids = "";
			foreach ($orders as $order) {
                        $tot_qtyt += $order->order_qty;
			$tot_amountt += $order->order_amount;
                        $membershipquery = "SELECT EXISTS ( SELECT 1 FROM it_ck_orders o JOIN it_ck_orderitems oi ON o.id = oi.order_id JOIN it_items i ON oi.item_id = i.id WHERE o.id = $order->id AND i.ctg_id = 65 ) AS result";//check the order contains Membership ctg barcode
                        $resultmembershipquery=$db->fetchObject($membershipquery);
                        if ($resultmembershipquery && $resultmembershipquery->result > 0) {$membershiporderqty+=$order->order_qty; $membershipordervalue+=$order->order_amount;}
                        }
			?>
                        <tr style="font-weight:bold;">
                            <td></td>
                            <td>TOTAL</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td><?php echo $tot_qtyt."  (MB = $membershiporderqty)"; ?></td>
                            <td><?php echo $tot_amountt."  (MB = $membershipordervalue)"; ?></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <?php
			$count = 0; $tot_qty=0; $tot_amount=0;
			 $ids = "";
			foreach ($orders as $order) {
			$count++;
			$tot_qty += $order->order_qty;
			$tot_amount += $order->order_amount;
			$ids .= $order->id.",";
                        $membershipquery = "SELECT EXISTS ( SELECT 1 FROM it_ck_orders o JOIN it_ck_orderitems oi ON o.id = oi.order_id JOIN it_items i ON oi.item_id = i.id WHERE o.id = $order->id AND i.ctg_id = 65 ) AS result";//check the order contains Membership ctg barcode
                        $resultmembershipquery=$db->fetchObject($membershipquery);
			?>
                        <tr>
                            <td><?php echo $count; ?><span style="display:none;"><?php echo $order->id; ?></span></td>
                            <td><?php echo $order->store_name; ?></td>
                            <td style="background-color: <?php echo ($resultmembershipquery && $resultmembershipquery->result > 0) ? '#87CEEB' : ''; ?>"><?php echo $order->order_no; ?></td>
                            <td><?php if ($this->store_id) { ?><input type="checkbox" id="ch_<?php echo $order->id; ?>"><?php } ?></td>
                            <td><?php echo OrderStatus::getName($order->status); ?></td>
                            <?php if ($resultmembershipquery && $resultmembershipquery->result > 0) { ?>     <td><?php echo "Yes" ?></td> <?php } else { ?>     <td><?php echo "No" ?></td> <?php } ?> 
                            <td><?php echo mmddyy($order->active_time); ?></td>
                            <td><?php echo $order->order_qty; ?></td>
                            <td><?php echo $order->order_amount; ?></td>
                            <td><?php echo $order->num_designs; ?></td>
                            <td><button onclick='window.location="formpost/orderPickup.php?oid=<?php echo $order->id; ?>"'>Pickup</button></td>
                        </tr>
                            <script>

                               $('#checkall:checkbox').change(function () {
                               if($(this).attr("checked")) $('input:checkbox').attr('checked','checked');
                               else $('input:checkbox').removeAttr('checked');
                               });
         
   
                        function myFunction() {
                            var abc = $('#ids').val();
                             abc = abc.trim(',');
                             var arr = abc.split(',');        
                             var send_ids = "";
                                for(var i=0; i<arr.length-1; i++){
                                if(document.getElementById('ch_'+arr[i]).checked){
                                    send_ids += arr[i]+",";
                                    
                                }  
                            }
                            window.location="formpost/orderPickup.php?oids="+send_ids;
                          }
                    </script>

			<?php } ?>
                        <tr style="font-weight:bold;">
                            <td></td>
                            <td>TOTAL</td>
                            <td><input type="hidden" id="ids" name="ids" value="<?php echo $ids; ?>"/></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td><?php echo $tot_qty."  (MB = $membershiporderqty)"; ?></td>
                            <td><?php echo $tot_amount."  (MB = $membershipordervalue)"; ?></td>
                            <td></td>
                            <td></td>
                        </tr>
                    </table>
<?php if ($this->store_id) { ?>
		<div><button onclick="myFunction();">Pickup selected</button></div><br>
		
<?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>
    <?php
    }
}
?>
