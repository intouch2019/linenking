<?php
require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once "session_check.php";

class cls_dispatch_droporder extends cls_renderer {

    var $currStore;
    var $params;
    var $pickgroup_id;
    function __construct($params=null) {
        $this->currStore = getCurrUser();
        $this->params = $params;
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
<script type="text/javascript" src="js/jquery.print.js"></script>
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

                // When the document is ready, initialize the link so
                // that when it is clicked, the printable area of the
                // page will print.
	function printOrder(pickgroup_id) {
		$.ajax({
			type: "POST",
			url: "ajax/setPrintTime.php",
			data: "pid="+pickgroup_id
		});
		$( ".printable" ).print();
	}

    //--><!]]>
</script>


               
        <?php
    }

    //extra-headers close

    public function pageContent() {
        $db = new DBConn();
        $store_id = getCurrUserId();
//	$order = $db->fetchObject("select p.*, o.status, max(o.active_time) as active_time, c.store_name from it_ck_orders o, it_ck_pickgroup p, it_codes c where o.store_id = c.id and o.pickgroup = p.id and o.status = ".OrderStatus::Picking." and p.id=$this->pickgroup_id group by o.pickgroup");
        $order = $db->fetchObject("select p.*, o.status, max(o.active_time) as active_time, c.store_name from it_ck_orders o, it_ck_pickgroup p, it_codes c where o.store_id = c.id and o.pickgroup = p.id and p.id=$this->pickgroup_id group by o.pickgroup");
        $menuitem = "";
        $invnoarr = explode(",", $order->invoice_no);
        //print_r($invnoarr);
        //$inv_nos = implode(",",$invnoarr);
        $inv_nos = "'" . implode("','", $invnoarr) . "'";
        //print "$inv_nos";
        $invquery = "select id from it_invoices where invoice_no in ($inv_nos) order by id desc "; //limit 1
        //echo "<br/>INV QRY $invquery <br/>";
        $invnos = $db->fetchObjectArray($invquery);
        $invids = "";
        if (isset($invnos)) {
            $i = array();
            foreach ($invnos as $invno) {
                array_push($i, $invno->id);
            }
            $invids = implode(",", $i);
        }

        include "sidemenu." . $this->currStore->usertype . ".php";

        $printHtml = "LINEN KING -- DROP ORDERS.<br/> STORE: $order->store_name <br />";
        $printHtml .= "<p align='left'>PICKING ID: $order->id</p><br />";
        ?>

                <div class="grid_10">
                    <div class="box">
                        <h2>
                            Order No(s): <?php echo $order->order_nos; ?> [ <?php echo $order->store_name; ?> ]
                        </h2><br />
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
        <?php
        if ($this->currStore->usertype == UserType::Admin || $this->currStore->usertype == UserType::Dispatcher) {
            if ($order->status == OrderStatus::Picking_Complete) {
                ?>  <tr><td colspan="4"></td><td  align="centere"><input type="button" onclick="backtoInpic('<?php echo $this->pickgroup_id; ?>')" value="Change back to In-Picking"/></td></tr><?php
                                            }
                                        }
                                        ?>
                                    </table>
                                        <?php if ($this->currStore->id == $order->dispatcher_id && $order->status == OrderStatus::Picking) { ?>
                                        <button onclick="javascript:printOrder(<?php echo $this->pickgroup_id; ?>);">Print Drop Orders</button>
                                        <button onclick='window.location = "formpost/orderRevert.php?pid=<?php echo $this->pickgroup_id; ?>"'>Change back to Active</button>
                                        <button onclick='window.location = "dispatch/shipped/pid=<?php echo $this->pickgroup_id; ?>"'>Picking Complete</button>
                                        &nbsp;&nbsp;
                                        <button onclick='window.location = "dispatch/droporder/pid=<?php echo $this->pickgroup_id; ?>"'>Drops and Extra Products</button>
        <?php } elseif ($this->currStore->usertype == UserType::Admin || $this->currStore->usertype == UserType::Dispatcher) { ?>
                                        <button onclick='window.location = "formpost/orderRevert.php?pid=<?php echo $this->pickgroup_id; ?>"'>Change back to Active</button>
                                    
        <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
        <?php
        $totalQ = 0;
//                $allDesigns = $db->fetchObjectArray("SELECT d.image,i.design_no,d.prod_type, i.ctg_id, i.ctg_name, i.style_id, i.size_id, i.MRP , sum(o.order_qty) as total_qty from it_ck_designs d, it_ck_items i, it_ck_orderitems o where o.ctg_id=i.ctg_id and o.ctg_id=d.ctg_id and o.design_no=i.design_no and o.design_no=d.design_no and o.mrp=i.mrp and o.order_id in ($order->order_ids) group by i.ctg_id, i.design_no order by o.order_no desc,i.ctg_id,i.design_no");
        $query = "SELECT convert(d.lineno, unsigned int) dlineno, convert(d.rackno, unsigned int) drackno, d.image,i.design_no, i.MRP , i.ctg_id, ctg.name as ctg_name,br.name as brand,pt.name as prod_type,mt.name as material,ft.name as fabric, sum(o.order_qty) as total_qty from it_ck_designs d, it_items i, it_categories ctg, it_ck_orderitems o,it_brands br, it_fabric_types ft, it_materials mt,it_prod_types pt where o.item_id=i.id and i.ctg_id=ctg.id and i.brand_id=br.id and i.prod_type_id=pt.id and i.material_id=mt.id and i.fabric_type_id=ft.id and o.design_no=d.design_no and i.ctg_id=d.ctg_id and o.order_id in ($order->order_ids) group by i.ctg_id, i.design_no order by d.lineno, d.rackno";
        $allDesigns = $db->fetchObjectArray("SELECT convert(d.lineno,unsigned int) as dlineno,d.rackno as drackno ,convert(d.rackno,unsigned int) as drackno1, d.image,i.design_no, i.MRP , i.ctg_id, ctg.name as ctg_name,br.name as brand,pt.name as prod_type,mt.name as material,ft.name as fabric, sum(o.order_qty) as total_qty from it_ck_designs d, it_items i, it_categories ctg, it_ck_orderitems o,it_brands br, it_fabric_types ft, it_materials mt,it_prod_types pt where o.item_id=i.id and i.ctg_id=ctg.id and i.brand_id=br.id and i.prod_type_id=pt.id and i.material_id=mt.id and i.fabric_type_id=ft.id and o.design_no=d.design_no and i.ctg_id=d.ctg_id and i.ctg_id != 29 and o.order_id in ($order->order_ids) group by i.ctg_id, i.design_no ,i.MRP order by dlineno, drackno1,drackno");

        $prevLineno = false;
        $row_no = 0;
        $totord = 0;
        $totdel = 0;
        $totextra = 0;
        $itemcodelist = "";
        foreach ($allDesigns as $design) {
            $row_no++;
            $design_no = $db->safe($design->design_no);
            $ctg_id = $db->safe($design->ctg_id);
            $currLineno = $design->dlineno;
            $divid = "accordion-" . $row_no;
            ?>
                        <div class="clear"></div>

            <?php
            $styleobj = $db->fetchObjectArray("select s1.style_id, s2.name as style_name from it_ck_styles s1,it_styles s2 where s1.style_id=s2.id and ctg_id=$ctg_id order by sequence");
            $no_styles = count($styleobj);
            $sizeobj = $db->fetchObjectArray("select s1.size_id, s2.name as size_name from it_ck_sizes s1,it_sizes s2 where s1.size_id=s2.id and ctg_id=$ctg_id order by sequence");
            $no_sizes = count($sizeobj);
            ?>
                        <?php
                        //for each unique style print style
                        for ($k = 0; $k < $no_styles; $k++) {
                            $stylcod = $styleobj[$k]->style_id;
                            for ($i = 0; $i < $no_sizes; $i++) {
                            
                                $sizeid = $sizeobj[$i]->size_id;

                                $query = "select id from it_items where design_no = $design_no and MRP=$design->MRP and ctg_id=$ctg_id and style_id = '$stylcod' and size_id = '$sizeid'";
                                $getitms = $db->fetchObjectArray($query);
                                //echo $order->order_ids;
                                if (count($getitms) > 0) {
//echo "select sum(order_qty) as sum_qty from it_ck_orderitems where item_id=$getitm->id"."</br>";
                                    $itemids = array();
                                    foreach ($getitms as $getitm) {
                                        $itemids[] = $getitm->id;
                                    }
                                    $itemids_str = join(",", $itemids);
                                    $exist = $db->fetchObject("select sum(order_qty) as sum_qty from it_ck_orderitems where item_id in ($itemids_str) and order_id in ($order->order_ids)");
                                    if ($exist) { //echo $getitm->id;
                                        //$tr .= $exist->sum_qty;
                                        $totord += $exist->sum_qty;
                                        $totalQ += $exist->sum_qty;
                                    }
                                }
                            }
                        }
                        ?>
                        <?php
                        //for each unique style print style
                        for ($k = 0; $k < $no_styles; $k++) {
                            //print style names
                            //store style id in $stylecod
                            $db_style_id = $db->safe($styleobj[$k]->style_id);
                            //for each unique size check if size for that particular style is available. if available->show input box. if in order->show qty.
                            for ($i = 0; $i < $no_sizes; $i++) {

                                //to get the grouped sizes
                                $shippedexist = null;
                                $sizeid = $sizeobj[$i]->size_id;
                                //$size_group= isset($sizeobj[$i]->size_group) ? $sizeobj[$i]->size_group : "'".$sizeobj[$i]->size_id."'";
                                $exist = $db->fetchObjectArray("select barcode from it_items where design_no=$design_no and ctg_id=$ctg_id and style_id=$db_style_id and MRP=$design->MRP and size_id = $sizeid");
//                                                    echo "<br/>select barcode from it_items where design_no=$design_no and ctg_id=$ctg_id and style_id=$db_style_id and MRP=$design->MRP and size_id = $sizeid<br/>";

                                if (isset($exist)) {
                                    $itemcodes = '';
                                    foreach ($exist as $ex) {
                                        $itemcodes .= $ex->barcode . ",";
                                    }
                                    $itemcodes = substr($itemcodes, 0, -1);
//                                                        echo "<br/>ITEMCODES:$itemcodes<br/>";
                                    $shippedexist = $db->fetchObject("select sum(quantity) as quantity from it_invoice_items where invoice_id in ( $invids ) and item_code in ($itemcodes)");
                                    //echo "<br/>select sum(quantity) as quantity from it_invoice_items where invoice_id='$invno->id' and item_code in ($itemcodes)<br/>";
                                }
                                if (isset($shippedexist)) {
                                    foreach ($exist as $ex) {
                                        $itemcodelist .= "'$ex->barcode',";
                                    }
                                 $shippedexist->quantity;
                                    $totdel += $shippedexist->quantity;
                                }
                            }
                        }
                    }

                    if ($order->status == OrderStatus::Shipped) {

                        $orderitemarray = array();


                        $existt = $db->fetchObjectArray("select i.barcode,sum(c.order_qty) as order_qty from it_items i, it_ck_orderitems c where i.id=c.item_id and c.order_id in ($order->order_ids) group by i.barcode");

                        if (isset($existt)) {


                            foreach ($existt as $orderqtyy) {

                                $orderitemarray[$orderqtyy->barcode] = $orderqtyy->order_qty;
                            }
                        }
                        $shippeditemarray = array();


                        $existtt = $db->fetchObjectArray("select item_code,sum(quantity) as quantity from it_invoice_items where invoice_id in ($invids) group by item_code");

                        if (isset($existtt)) {

                            foreach ($existtt as $orderqtyyy) {

                                $shippeditemarray[$orderqtyyy->item_code] = $orderqtyyy->quantity;
                            }
                        }
                        $totplaced = 0;
                        foreach ($shippeditemarray as $key => $value) {
                            $totplaced += $value;
                        }


                        //--------------------------------------drop


                        $unmatchedKeyValues1 = array();
                        foreach ($orderitemarray as $key => $value) {     //ordered
                            if (!array_key_exists($key, $shippeditemarray)) {     //shipped
                                $unmatchedKeyValues1[$key] = $value;    //barcode match
                                //print_r($key);
                            }

                            if (array_key_exists($key, $shippeditemarray)) {      //ordered 2 shipped 1 avilable  difference 2-1  
                                if ($value > $shippeditemarray[$key]) {

                                    $unmatchedKeyValues1[$key] = $value - $shippeditemarray[$key];    // quantity
                                }
                            }
                        }

                        //--------------------------------extra

                        $unmatchedKeyValues2 = array();

                        foreach ($shippeditemarray as $key => $value) {   //shipped 
                            if (!array_key_exists($key, $orderitemarray)) {       //ordered
                                $unmatchedKeyValues2[$key] = $value;    //barcode match
                                //print_r($key);
                            }

                            if (array_key_exists($key, $orderitemarray)) {      //ordered 2 shipped 1 avilable  difference 2-1  
                                if ($value > $orderitemarray[$key]) {

                                    $unmatchedKeyValues2[$key] = $value - $orderitemarray[$key];    // quantity
                                }
                            }
                        }
                    }



                    $totdrop = 0;

                    if (isset($unmatchedKeyValues1) && $unmatchedKeyValues1 != null) {
                        ?></hr></br></br><h3>Drop Products</h3></br><?php
                        foreach ($unmatchedKeyValues1 as $key => $value) {
                            $barr1 = $key;
                            $qty1 = $value;
                            $totdrop += $qty1;

                            $newitem = $db->fetchObject("select i.*, ctg.name as ctg_name, pt.name as prod_type , st.name as style_name , sz.name as size_name from it_items i, it_categories ctg , it_prod_types pt , it_sizes sz , it_styles st where i.ctg_id = ctg.id and i.prod_type_id = pt.id and i.style_id = st.id and i.size_id = sz.id and i.barcode='$barr1'");
                            //print "select i.*, ctg.name as ctg_name, pt.name as prod_type , st.name as style_name , sz.name as size_name from it_items i, it_categories ctg , it_prod_types pt , it_sizes sz , it_styles st where i.ctg_id = ctg.id and i.prod_type_id = pt.id and i.style_id = st.id and i.size_id = sz.id and i.barcode='$o->item_code'";
                            if (isset($newitem)) {
                                $newdesign = $db->fetchObject("select image,lineno,rackno,design_no,ctg_id from it_ck_designs where ctg_id = $newitem->ctg_id and design_no='$newitem->design_no'");
                                //print "select image, prod_type from it_ck_designs where ctg_id = $newitem->ctg_id and design_no='$newitem->design_no'";
                                ?>
                                <div class="box">
                                    <h2>CATEGORY: <?php
                                echo $newitem->ctg_name . " | DESIGN NO: $newitem->design_no | ";
                                echo "[ MRP: " . $newitem->MRP . " ]";
                                ?></h2>
                                    <div class="block" id="<?php echo $divid; ?>">
                                   <a href="<?php echo $this->designImageUrl($newdesign->image); ?>" rel="prettyPhoto"><img class="grid_2" align="left" src="<?php echo $this->designImageUrl($newdesign->image); ?>" height="170px" width="170px" /></a>
                                        <div class="block grid_10" style="margin-top:30px;">
                                            <label style="font-weight: bold; margin-left:10px;">Style : </label> <?php print $newitem->style_name; ?>
                                            <label style="font-weight: bold; margin-left:10px;">Size : </label> <?php print $newitem->size_name; ?>
                                            <label style="font-weight: bold; margin-left:10px;">Quantity : </label> <?php
                                  $printHtml .= '<div style="clear:both;font-weight:bold;">LINE: ' . $newdesign->lineno . '</div>';
                    $printHtml .= '<table width="48%"style="border: 1px solid black;">';
                    $printHtml .= '<tr><th style="border-bottom: 1px solid black;text-align:left;" colspan=' . ($no_sizes + 1) . '>' . "D.No : $newdesign->design_no , &nbsp; CTG : $newitem->ctg_name" . '</th></tr>';
                    $printHtml .= '<tr><th style="border-bottom:1px solid black;text-align:left;">' . "RS : $newitem->MRP , &nbsp; RACK : $newdesign->rackno" . '</th></tr>';
                    $printHtml .= '<tr><th style="text-align:left;">' . "STYLE : $newitem->style_name  , &nbsp;  SIZE :  $newitem->size_name , &nbsp; QTY : $qty1" . '</th></tr>';
                    $printHtml .= '</table>';
                    $printHtml .= '</br>';           
                    print $qty1;
                    ?>
                                            </br>
                                        </div>
                                    </div><!-- end class="block" -->     
                                    <div class="clear"></div>
                                </div>

                    <?php
                }
            }
        } /* echo "TOTAL ORDER : $totord</br>TOTAL SHIPPED : $totdel"; */
        
                        if (isset($unmatchedKeyValues2) && $unmatchedKeyValues2 != null) {        //----------drop orders
                ?></hr></br></br><h3>Extra Products</h3></br><?php
                foreach ($unmatchedKeyValues2 as $key => $value) {
                    
                            $barr2 = $key;
                    $qty2 = $value;
                    $totextra += $qty2;

                            $newitem = $db->fetchObject("select i.*, ctg.name as ctg_name, pt.name as prod_type , st.name as style_name , sz.name as size_name from it_items i, it_categories ctg , it_prod_types pt , it_sizes sz , it_styles st where i.ctg_id = ctg.id and i.prod_type_id = pt.id and i.style_id = st.id and i.size_id = sz.id and i.barcode='$barr2'");
                            //print "select i.*, ctg.name as ctg_name, pt.name as prod_type , st.name as style_name , sz.name as size_name from it_items i, it_categories ctg , it_prod_types pt , it_sizes sz , it_styles st where i.ctg_id = ctg.id and i.prod_type_id = pt.id and i.style_id = st.id and i.size_id = sz.id and i.barcode='$o->item_code'";
                            if (isset($newitem)) {
                                $newdesign = $db->fetchObject("select image from it_ck_designs where ctg_id = $newitem->ctg_id and design_no='$newitem->design_no'");
                                //print "select image, prod_type from it_ck_designs where ctg_id = $newitem->ctg_id and design_no='$newitem->design_no'";
                                ?>
                                <div class="box">
                                    <h2>CATEGORY: <?php
                                echo $newitem->ctg_name . " | DESIGN NO: $newitem->design_no | ";
                                echo "[ MRP: " . $newitem->MRP . " ]";
                                ?></h2>
                                    <div class="block" id="<?php echo $divid; ?>">
                                     <a href="<?php echo $this->designImageUrl($newdesign->image); ?>" rel="prettyPhoto"><img class="grid_2" align="left" src="<?php echo $this->designImageUrl($newdesign->image); ?>" height="170px" width="170px" /></a>
                                        <div class="block grid_10" style="margin-top:30px;">
                                            <label style="font-weight: bold; margin-left:10px;">Style : </label> <?php print $newitem->style_name; ?>
                                            <label style="font-weight: bold; margin-left:10px;">Size : </label> <?php print $newitem->size_name; ?>
                                            <label style="font-weight: bold; margin-left:10px;">Quantity : </label> <?php
                    print $qty2;
                    
                    ?>
                                            </br>
                                        </div>
                                    </div><!-- end class="block" -->     
                                    <div class="clear"></div>
                                </div>

                    <?php
                }
            }
        } 
        
        ?>
                    <div>
                        <h4>Total Ordered : <?php echo $totord; ?></h4>
                        <h4>Total Shipped : <?php echo $totdel; ?></h4>       
                    <?php
                    if ($order->status == OrderStatus::Shipped) {
                        ?>
            <?php
            if ($totdrop > 0) {
                echo "<h4>Total Dropped : $totdrop</h4>";
            }
            if ($totextra > 0) {
                echo "<h4>Total Extra : $totextra</h4>";
            }
            if ($totplaced > 0) {
                echo "<h4>Total Placed : $totplaced</h4>";
            }
        }
        ?>
                    </div>
                </div>
                <div class="printable" style="display:none;"><?php echo $printHtml; ?></div>
                        <?php
                    }

                }
                ?>