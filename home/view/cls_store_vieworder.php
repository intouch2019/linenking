<?php
require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once "session_check.php";

class cls_store_vieworder extends cls_renderer {
    var $currStore;
    var $params;
    var $order_id;
    var $pickgroup_id;
    function __construct($params=null) {
        $this->currStore = getCurrUser();
	if (!$this->currStore) {
		header("Location: ".DEF_SITEURL."timeout");
		exit;
	}
        $this->params = $params;
        if (isset($params['oid']))
            $this->order_id = $params['oid'];
        if (isset($params['pid']))
            $this->pickgroup_id = $params['pid'];
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
	$menuitem="";
        include "sidemenu.".$this->currStore->usertype.".php";
        ?>

<div class="grid_10">
            <?php
            $db = new DBConn();
            $store_id = getCurrUserId();

	    if ($this->order_id) {
            $order = $db->fetchObject("select * from it_ck_orders where id = $this->order_id");
//            $query = "SELECT o.*, sum(oi.order_qty) as disqty FROM `it_ck_orders` o , it_ck_orderitems oi , it_items i WHERE oi.order_id = o.id and o.id = $this->order_id and oi.item_id = i.id and i.ctg_id != (select id from it_categories where name = 'Others') ";    
//	    $order = $db->fetchObject($query);            
            $order->order_ids = $order->id;
	    $order->order_nos = $order->order_no;
//                $invquery = "select id from it_invoices where invoice_no = '$order->invoice_no' order by id desc limit 1";
//                //echo "<br/>INV QRY $invquery <br/>";
//                $invno = $db->fetchObject($invquery); 
            }
	    if ($this->pickgroup_id) {
//	    $query = "select p.*, o.pickgroup, o.status, max(o.active_time) as active_time, c.store_name from it_ck_orders o, it_ck_pickgroup p, it_codes c where o.store_id = c.id and o.pickgroup = p.id and o.status = ".OrderStatus::Picking." and p.id=$this->pickgroup_id and p.storeid=$store_id group by o.pickgroup";
	    $query = "select p.*, max(o.active_time) as active_time, o.status, c.store_name from it_ck_pickgroup p, it_ck_orders o, it_codes c where p.id=$this->pickgroup_id and o.pickgroup = p.id and p.storeid = c.id group by o.pickgroup";
            $order = $db->fetchObject($query);
            
            $invnoarr = explode(",",$order->invoice_no);
            //print_r($invnoarr);
            //$inv_nos = implode(",",$invnoarr);
            $inv_nos = "'".implode("','", $invnoarr)."'";
            //print "$inv_nos";
            $invquery = "select id from it_invoices where invoice_no in ($inv_nos) order by id desc "; //limit 1
            //echo "<br/>INV QRY $invquery <br/>";
            $invnos = $db->fetchObjectArray($invquery);        
            $invids = "";
            if(isset($invnos)){
                $i = array();
                foreach( $invnos as $invno){
                    array_push($i,$invno->id);
                }
                $invids = implode(",", $i);
            }
            //print $invids;
            //$invquery = "select id from it_invoices where invoice_no like '%0$order->invoice_no'";
            //$invno = $db->fetchObject($invquery);
//            $invno = null;
//            $invquery = "select id from it_invoices where invoice_no = '$order->invoice_no' order by id desc limit 1";
//            //echo "<br/>INV QRY $invquery <br/>";
//            $invno = $db->fetchObject($invquery);
	    }
            ?>
    <div class="box">
        <h2>
            <a href="#" id="toggle-accordion" style="cursor: pointer; ">Order No(s): <?php echo $order->order_nos; ?></a>
        </h2><br>
        <div style="margin-top: 0px; margin-right: 0px; margin-bottom: 0px; margin-left: 0px; position: static; overflow-x: hidden; overflow-y: hidden; ">
            <div class="block" id="accordion" style="margin-top: 0px; margin-right: 0px; margin-bottom: 0px; margin-left: 0px; ">
                <div id="accordion">
                    <table>
                        <tr>
                            <th>Status</th>
                            <th>Order Date</th>
                            <th>Total Items</th>
                            <th>Total Price</th>
                            <th>Number Of Designs</th>
                        </tr>
                        <tr>
                            <td><?php echo OrderStatus::getName($order->status); ?></td>
                            <td><?php echo mmddyy($order->active_time); ?></td>
                            <td><?php echo $order->order_qty; ?></td>
                            <td><?php echo $order->order_amount; ?></td>
                            <td><?php echo $order->num_designs; ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div><div class="clear"></div>
            <?php
                //$query = "SELECT o.item_id as order_item_id , d.image,i.design_no, i.ctg_id, ctg.name, i.MRP, sum(o.order_qty) as total_qty ,o.remarks from it_ck_designs d, it_items i,it_categories ctg, it_ck_orderitems o where o.item_id=i.id and i.ctg_id=ctg.id and o.design_no=d.design_no and i.ctg_id=d.ctg_id and o.mrp=i.mrp and o.store_id=$store_id and i.ctg_id != 29 and  o.order_id in ($order->order_ids) group by i.ctg_id, i.design_no ,i.mrp order by o.order_no desc,i.ctg_id,i.design_no";
                $query = "SELECT o.item_id as order_item_id , d.image,i.design_no, i.ctg_id, ctg.name, i.MRP, sum(o.order_qty) as total_qty ,o.remarks from it_ck_designs d, it_items i,it_categories ctg, it_ck_orderitems o where o.item_id=i.id and i.ctg_id=ctg.id and o.design_no=d.design_no and i.ctg_id=d.ctg_id and o.mrp=i.mrp and i.ctg_id != 29 and o.order_id in ($order->order_ids) group by i.ctg_id, i.design_no ,i.mrp order by o.order_no desc,i.ctg_id,i.design_no";                
                $allDesigns = $db->fetchObjectArray($query);
//                $query = "SELECT d.image,i.design_no, i.ctg_id, ctg.name, i.MRP, sum(o.order_qty) as total_qty,o.remarks from it_ck_designs d, it_items i,it_categories ctg, it_ck_orderitems o where o.item_id=i.id and i.ctg_id=ctg.id and o.design_no=d.design_no and i.ctg_id=d.ctg_id and o.mrp=i.mrp and o.store_id=$store_id and o.order_id in ($order->order_ids)  and i.ctg_id != (select id from it_categories where name = 'Others' ) group by i.ctg_id, i.design_no order by o.order_no desc,i.ctg_id,i.design_no";
//                $allDesigns = $db->fetchObjectArray($query);
//                print $query;
            $row_no = 0;
            $totord=0;
            $totdel=0;
            $totextra = 0;
            $itemcodelist = "";
            foreach ($allDesigns as $design) {
                $row_no++;
                $design_no = $db->safe($design->design_no);
                $ctg_id = $db->safe($design->ctg_id);
                $divid = "accordion-" . $row_no;
                ?>
    <div class="clear"></div>
    <div class="box">
        <h2>Category: <?php echo $design->name;?> | Design No: <?php echo $design->design_no." [ MRP: ".$design->MRP." ]"; ?></h2>
        <div class="block" id="<?php echo $divid; ?>">
                <input type="hidden" name="ctg_id" value="<?php echo $design->ctg_id; ?>" />
                <input type="hidden" name="design_no" value="<?php echo $design->design_no; ?>" />
                <input type="hidden" name="mrp" value="<?php echo $design->MRP; ?>" />
                <a href="images/stock/<?php echo $design->image; ?>" rel="prettyPhoto"><img class="grid_2" align="left" src="images/stock/<?php echo $design->image; ?>" height="170px" width="170px" /></a>
                <div class="block grid_10">
                    <b>Ordered : </b>
                    <table>
                                    <?php
                                    $styleobj = $db->fetchObjectArray("select s1.style_id,s2.name as style_name from it_ck_styles s1,it_styles s2 where s1.ctg_id=$ctg_id and s1.style_id=s2.id and s2.is_active = 1  order by s1.sequence");
                                    $no_styles = count($styleobj);
                                    $sizeobj = $db->fetchObjectArray("select s1.size_id, s2.name as size_name from it_ck_sizes s1,it_sizes s2 where s1.ctg_id=$ctg_id and s1.size_id=s2.id order by s1.sequence");
                                    $no_sizes = count($sizeobj);
                                    ?>
                        <thead>
                            <tr><th></th>
                                            <?php
                                            $width = intval(100/($no_sizes+1));
                                            for ($i = 0; $i < $no_sizes; $i++) {
                                                print '<th style="text-align:left;" width="'.$width.'%">';
                                                echo $sizeobj[$i]->size_name;
                                                print "</th>";  //print sizes
                                            }
                                            ?>
                            </tr>
                        </thead>
                        <tbody>
                                        <?php
                                        //for each unique style print style
                                        for ($k = 0; $k < $no_styles; $k++) {
                                        //print style names
                                            print "<tr><th>";
                                            echo $styleobj[$k]->style_name;
                                            print"</th>";
                                            $stylcod = $styleobj[$k]->style_id;
                                            //store style id in $stylecod
                                            //$db_style_id = $db->safe($styleobj[$k]->style_id);
                                            //for each unique size check if size for that particular style is available. if available->show input box. if in order->show qty.
                                            for ($i = 0; $i < $no_sizes; $i++) {
                                                ?><td><?php
                                                    $sizeid = $sizeobj[$i]->size_id;
                                                    //to get the grouped sizes
                                                    //$size_group= isset($sizeobj[$i]->size_group) ? $sizeobj[$i]->size_group : "'".$sizeobj[$i]->size_id."'";
                                                    $query = "select id from it_items where design_no = $design_no and MRP=$design->MRP and ctg_id=$ctg_id and style_id = '$stylcod' and size_id = '$sizeid'";
                                                    $getitm = $db->fetchObject($query);
                                                    if ($getitm) { 
                                                        $q1 = "select o.item_id as id from it_ck_orderitems o , it_items i where o.order_id in ( $order->order_ids )  and o.item_id = i.id and i.style_id = '$stylcod' and i.size_id = '$sizeid' and i.design_no = $design_no and i.ctg_id =  $ctg_id and i.MRP = $design->MRP ";
//                                                            echo "$q1";
                                                        $getOitm = $db->fetchObject($q1);
                                                        if($getOitm){ $idVal = " $getOitm->id ";}else{ $idVal = " $getitm->id"; }
                                                        $query = "select sum(order_qty) as sum_qty from it_ck_orderitems where order_id in ( $order->order_ids ) and item_id= $idVal";
                                                        //$exist = $db->fetchObject("select order_qty as sum_qty from it_ck_orderitems where item_id=$getitm->id and order_id in ($order->order_ids)");
                                                       // print "\n".$query."\n";
                                                        $exist = $db->fetchObject($query);
                                                        if ($exist) {
                                                           // print "\nInside";
                                                             echo $exist->sum_qty;
                                                             $totord += $exist->sum_qty;
                                                             //echo "TOT:".$totord;
                                                        }
                                                    }
                                                ?></td><?php
                                            }
                                            print "</tr>";
                                        } ?>                                        
                        </tbody>
                    </table></br>
                    <?php if ($order->status==OrderStatus::Shipped) { ?>
                    <b>Shipped : </b>
                    <table>
                        <thead>
                            <tr><th></th>
                                            <?php
                                            $width = intval(100/($no_sizes+1));
                                            for ($i = 0; $i < $no_sizes; $i++) {
                                                print '<th style="text-align:left;" width="'.$width.'%">';
                                                echo trim($sizeobj[$i]->size_name);
                                                print "</th>";  //print sizes
                                            }
                                            ?>
                            </tr>
                        </thead>
                        <tbody>
                                        <?php
                                        //for each unique style print style
                                        for ($k = 0; $k < $no_styles; $k++) {
                                        //print style names
                                            print "<tr><th>";
                                            echo trim($styleobj[$k]->style_name);
                                            print"</th>";
                                            //store style id in $stylecod
                                            $db_style_id = $db->safe($styleobj[$k]->style_id);
                                            //for each unique size check if size for that particular style is available. if available->show input box. if in order->show qty.
                                            for ($i = 0; $i < $no_sizes; $i++) {
                                                ?><td><?php
                                                    //to get the grouped sizes
                                                    $shippedexist = null;
                                                    $sizeid=$sizeobj[$i]->size_id;
                                                    //$size_group= isset($sizeobj[$i]->size_group) ? $sizeobj[$i]->size_group : "'".$sizeobj[$i]->size_id."'";
                                                    $exist = $db->fetchObjectArray("select barcode from it_items where design_no=$design_no and ctg_id=$ctg_id and style_id=$db_style_id and MRP=$design->MRP and size_id = $sizeid");
//                                                    echo "<br/>select barcode from it_items where design_no=$design_no and ctg_id=$ctg_id and style_id=$db_style_id and MRP=$design->MRP and size_id = $sizeid<br/>";
                                                    
                                                    if (isset($exist)) {
                                                        $itemcodes = '';
                                                        foreach ($exist as $ex) {
                                                            $itemcodes .= $ex->barcode.",";
                                                        }
                                                        $itemcodes = substr($itemcodes,0,-1);
//                                                        echo "<br/>ITEMCODES:$itemcodes<br/>";
                                                        $shippedexist = $db->fetchObject("select sum(quantity) as quantity from it_invoice_items where invoice_id in ( $invids ) and item_code in ($itemcodes)");
//                                                        echo "<br/>select sum(quantity) as quantity from it_invoice_items where invoice_id='$invno->id' and item_code in ($itemcodes)<br/>";
                                                    }
                                                    if (isset($shippedexist)) {
                                                        foreach ($exist as $ex) {
                                                            $itemcodelist .= "'$ex->barcode',";
                                                        }
                                                        echo $shippedexist->quantity;
                                                        $totdel+=$shippedexist->quantity;
                                                    }
                                                ?></td><?php
                                            }
                                            print "</tr>";
                                        }
                                        ?>
                        </tbody>
                    </table>
                    <?php } ?>
                </div> <!-- end class=grid_10 -->
        </div> <!-- end class="block" --><div class="clear"></div>
    </div> <!-- end class="box" -->
    <div class="clear"></div>
            <?php
            } ?>
    <br/><br/> <?php
            $allDesigns = $db->fetchObjectArray("SELECT d.lineno, d.rackno, d.image,d.design_no,o.remarks, d.ctg_id, o.order_qty as total_qty from it_ck_designs d, it_ck_orderitems o, it_items i where o.item_id = i.id and i.ctg_id=29 and o.design_no=d.design_no and o.order_id in ($order->order_ids) order by d.design_no, o.order_id");
           foreach ($allDesigns as $design) { ?>
    <div class="box">
        <h2>Product : <?php echo $design->design_no; ?></h2>
        <div class="block" id="<?php echo $divid; ?>">
             <a href="images/stock/<?php //echo $design->image; ?>" rel="prettyPhoto"><img class="grid_2" align="left" src="images/stock/<?php echo $design->image; ?>" width="170" /></a>
                <div class="block grid_10" style="margin-top:30px;">
                    <?php if ($design->total_qty) { ?><label style="font-weight: bold; margin-left:15px;">Order Quantity : </label> <?php print $design->total_qty; } ?>
                    <?php if ($design->remarks != "") { ?><label style="font-weight: bold; margin-left:15px;">Additional Information : </label><?php print $design->remarks; } ?>
                </div>                    
        </div><!-- end class="block"--> 
         <div class="clear"></div>
        </div>     
        <div class="clear"></div>
    
    
<?php } 
            if ($order->status==OrderStatus::Shipped) {
            $itemcodelist = substr($itemcodelist, 0, -1);
            if ($itemcodelist != "") { $subq = "and item_code not in ($itemcodelist)"; } else { $subq = ""; }
            //$otheritemsquery="select item_code,quantity from it_ck_invoicedetails where invoice_id='$invno->id' and item_code not in ($itemcodelist) and item_code != '0010060000007' and item_code != '0010140000016'";
            $otheritemsquery="select item_code,quantity from it_invoice_items where invoice_id in ( $invids ) $subq ";
            $otheritems = $db->fetchObjectArray($otheritemsquery);
            $otheritemlist = "";
            if (count($otheritems)>0) { ?></hr></br><h3>Additional Products</h3><?php }
            foreach ($otheritems as $o) {  
                //CATEGORY: TROUSER | DESIGN NO: 10460 [ MRP: 1395 ]
                $newitem = $db->fetchObject("select i.*, ctg.name as ctg_name, pt.name as prod_type , st.name as style_name , sz.name as size_name from it_items i, it_categories ctg , it_prod_types pt , it_sizes sz , it_styles st where i.ctg_id = ctg.id and i.prod_type_id = pt.id and i.style_id = st.id and i.size_id = sz.id and i.barcode='$o->item_code'");                
                $newdesign = $db->fetchObject("select image  from it_ck_designs where ctg_id = '$newitem->ctg_id' and design_no='$newitem->design_no'"); ?>
                <div class="box">
                    <h2>CATEGORY: <?php echo $newitem->ctg_name." | DESIGN NO: $newitem->design_no | ";  echo "[ MRP: ".$newitem->MRP." ]"; ?></h2>
                    <div class="block" id="<?php echo $divid; ?>">
                         <a href="images/stock/<?php echo $design->image; ?>" rel="prettyPhoto"><img class="grid_2" align="left" src="images/stock/<?php echo $newdesign->image; ?>" width="170" /></a>
                            <div class="block grid_10" style="margin-top:30px;">
                                <label style="font-weight: bold; margin-left:10px;">Style : </label> <?php  print $newitem->style_name; ?>
                                <label style="font-weight: bold; margin-left:10px;">Size : </label> <?php  print $newitem->size_name; ?>
                                <label style="font-weight: bold; margin-left:10px;">Quantity : </label> <?php  print $o->quantity; $totextra += $o->quantity; ?>
                                </br>
                            </div>
                    </div><!-- end class="block"--> 
                     <div class="clear"></div>
                </div>     

      <?php } } /*echo "TOTAL ORDER : $totord</br>TOTAL SHIPPED : $totdel"; */ ?>
    <div>
        <h4>Total Ordered : <?php echo $totord; ?></h4>
        <h4>Total Shipped : <?php echo $totdel; ?></h4>       
        <?php if ($order->status == OrderStatus::Shipped)  { if ($totextra>0) { echo "<h4>Total Extra Shipped : $totextra</h4>"; $totdel += $totextra; } ?>
        <?php $diff=$totord-$totdel; if ($diff >0 ) echo "<h4>Total Dropped : $diff</h4>"; else { $diff=$diff*-1; echo "<h4>Total Additional : $diff</h4>"; } } ?>
    </div>
</div>
    <?php
    }
}
?>