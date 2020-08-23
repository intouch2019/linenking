<?php 
require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once "session_check.php";

class cls_dispatch_edittransport extends cls_renderer {
    var $currStore;
    var $params;
    var $pid;
    
    function __construct($params=null) {
        $this->currStore = getCurrUser();
        $this->params = $params;
        if (isset($params['pid'])) { $this->pid = $params['pid']; }
        
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
        $menuitem = "activeorders";
        include "sidemenu.".$this->currStore->usertype.".php";
//	if ($this->currStore->usertype != UserType::Dispatcher) { print "Unauthorized access"; return; }
        ?>

<div class="grid_10">
            <?php
            $db = new DBConn();
            $store_id = getCurrUserId();
            $order = $db->fetchObject("select p.*,c.store_name from it_ck_pickgroup p,it_codes c where p.storeid=c.id and p.id=$this->pid");
            if ($store_id != $order->dispatcher_id) { print "Unauthorized access"; return; }
            ?>
    <div class="box" >
        <h2>Edit Transport Details</h2><br>
            <div class="block" style="display:inline; padding:10px;">
                <div style="width:38%; margin-left: 2%; font-weight: bold; font-size:medium; display:inline; float:left;" >Shipped Order Details : <br/><br/>
                    <div style ="font-size:small;">Store : <?php echo $order->store_name; ?></div>
                    <div style ="font-size:small;">Order Date : <?php echo mmddyy($order->active_time); ?></div>
                    <div style ="font-size:small;">Order Numbers : <?php echo $order->order_nos; ?></div>
                    <div style ="font-size:small;">Order Qty : <?php echo $order->order_qty; ?></div>
                    <div style ="font-size:small;">Order Amount : <?php echo $order->order_amount; ?></div>
                    <div style ="font-size:small;">Invoice Number : <?php echo $order->invoice_no; ?></div>
                    <div style ="font-size:small;">Shipped Date : <?php echo $order->shipped_time; ?></div>
                    <div style ="font-size:small;">Shipped Qty : <?php echo $order->shipped_qty; ?></div>                 
                </div>
                <div style="width:58%; margin-left: 2%; font-weight: bold; font-size:medium;  float:left;">Edit Shipment Details : <br/><br/>
                    <form method="post" action="formpost/updateTransport.php" >
                    <input type="hidden" name="pickid" value="<?php echo $order->id;?>"/>
                    <l style="font-size:small; margin-right: 35px;">Shipped Qty :</l><input type="number"  name="shippedqty" value="<?php echo $order->shipped_qty; ?>"/><br/>
                    <l style="font-size:small; margin-right: 26px;">Shipped MRP :</l><input type="text"  name="shippedmrp" value="<?php echo $order->shipped_mrp; ?>"/><br/>
                    <l style="font-size:small; margin-right: 10px;">Cheque Amount :</l><input type="text"  name="chequeamt" value="<?php echo $order->cheque_amt; ?>"/><br/>
                    <l style="font-size:small; margin-right: 17px;">Cheque Details :</l><input type="text"  name="chequedtl" value="<?php if (isset($order->cheque_dtl)) echo $order->cheque_dtl; ?>"/><br/>
                    <l style="font-size:small; margin-right: 4px;">Transport Details :</l><textarea cols="30" rows="3" name="transport_dtl"><?php if (isset($order->transport_dtl)) echo $order->transport_dtl; ?></textarea><br/>
                    <l style="font-size:small; margin-right: 55px;">Remarks :</l><textarea cols="30" rows="3" name="remark"><?php if (isset($order->remark)) echo $order->remark; ?></textarea>
                    <br/><input style="width:80px;"type="submit" value="UPDATE"/>
                    </form>
                </div><div class="clear"></div>
                <button onclick="window.location='orders/shipped' "style="margin-left:42%;">BACK</button>
                <div class="clear"></div>
            </div>
    </div>
</div>
    <?php
    }
}
?>
