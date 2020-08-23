<?php
require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "session_check.php";

class cls_store_viewcart extends cls_renderer {

    var $currStore;
    var $reason="N_A";
    function __construct($params=null) {
        $this->currStore = getCurrUser();
         if ($this->currStore->inactive == 1) {
                $this->reason="You can not place Order Because, ".$this->currStore->inactivating_reason;
        }
    }

    function extraHeaders() {
        if (!$this->currStore) {
            return;
        }
        ?>
<link rel="stylesheet" href="css/prettyPhoto.css" type="text/css" media="screen" title="prettyPhoto main stylesheet" charset="utf-8" />
<link rel="stylesheet" type="text/css" href="css/dark-glass/sidebar.css" />
<script src="js/prettyPhoto/jquery.prettyPhoto.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript" src="js/sidebar/jquery.sidebar.js"></script>
<script type="text/javascript">
    $(function(){
	$("a[rel^='prettyPhoto']").prettyPhoto({animation_speed:'fast',slideshow:3000, hideflash: true});
     });
     
    $(function() {
        $("ul#demo_menu1").sidebar({
            width : 160,
            height : 110,
            injectWidth : 50,
            events:{
                item : {
                    enter : function(){
                        $(this).find("a").animate({color:"red"}, 250);
                    },
                    leave : function(){
                        $(this).find("a").animate({color:"white"}, 250);
                    }
                }
            }
        });
    });

    function addToCart(theForm) {
        var formName = theForm.name;
        var arr = formName.split("_");
        var form_id = arr[1];
        var params = $(theForm).serialize();
        var ajaxUrl = "ajax/setOrder.php?"+params;
        alert(ajaxUrl);
        $.getJSON(ajaxUrl, function(data){
            if (data.error == "0") {
                $("#status_"+form_id).removeClass().addClass("success");
            } else {
                $("#status_"+form_id).removeClass().addClass("error");
            }
            $("#status_"+form_id).html(data.message);
            $("#carttop").html(data.cartinfo);
            if (data.totqt) {
                $("#side_qty").html("QTY: "+data.totqt);
                $("#side_price").html("AMT: "+data.totmrp);
            }
        });

        return false;
    }
    
    function validate(item_id)
                 {
             var valid_id="validateid_"+item_id; 
//             alert(valid_id);
            var num =document.getElementById(valid_id).value; 
//            alert(num);
            if(num>10)
            {
                  alert("No of set/packet should not greater than 10. \n सेट / पॅकेटची संख्या 10 पेक्षा जास्त नसावी.");
                  document.getElementById(valid_id).value="";
                 }
                }
    
function add_toCart()
            {
                var reason='<?php echo $this->reason; ?>';
                alert(reason);
            }
    function deleteCartItem(formName)
    {
        var arr = formName.split("_");
        var form_id = arr[1];
        var theForm = document.forms[formName];
        var params = $(theForm).serialize();
        //alert(params);
        var r=confirm("remove this item ?");
        if (r==true)
        {
            var ajaxUrl = "ajax/removeOrder.php?"+params;
            $.getJSON(ajaxUrl, function(data){
                $("#carttop").html(data.cartinfo);
                //alert(data.message);//
                if (data.error=="1")
                { $("deletestatus_"+form_id).html(data.message); }
            });
            alert("Item Removed!");
        }
        else
        {
            alert("Cancelled!");
        }
        window.location.reload();
    }
</script>

    <?php
    } //extra-headers close
    public function pageContent() {
	$menuitem="viewcart";
        include "sidemenu.".$this->currStore->usertype.".php";
        ?>
<div class="grid_10">
            <?php
            $cart_page = true;
            include "cartinfo.php";
            $storeid = getCurrUserId();
            $db = new DBConn();
            $clsOrders = new clsOrders();
            //echo 'hiii'.$this->desig;
            $query21 = "SELECT * from it_ck_orderitems where  order_id=$cart->id";
           // print $query21;
            $datechecheck = $db->fetchObjectArray($query21);
            $link=false;
            if($datechecheck){
                 foreach ($datechecheck as $date){
                  $first = $date->createtime;
                  $item_id= $date->item_id;
                  $dt = new DateTime(); 
                  $last= $dt->format('Y-m-d H:i:s'); 
                  $hours = round((strtotime($last) - strtotime($first))/3600, 1);
                  if($hours>=48.00){
                        $query = "delete from it_ck_orderitems where order_id=$cart->id and store_id=$store_id and item_id=$item_id";
                        $db->execQuery($query);
	                 // update it_ck_orders
	                $clsOrders->updateCartTotals($cart->id);
                        if($clsOrders){
                                    
                                echo "<script type=\"text/javascript\">window.alert('Your cart is empty.');
                                window.location.href = 'store/viewcart';
                                </script>";
                        }
        
                    }
    
                 }

              
            }
            
            
            
            
            
            $query = "SELECT i.id,d.image,i.design_no,i.ctg_id,ctg.name as category,br.name as brand,ft.name as fabric,mt.name as material,pt.name as prodtype, i.MRP, sum(o.order_qty) as total_qty,o.remarks from it_ck_designs d, it_items i, it_ck_orderitems o, it_brands br, it_categories ctg, it_fabric_types ft, it_materials mt, it_prod_types pt where o.order_id=$cart->id and o.store_id=$storeid and o.item_id=i.id and o.design_no=d.design_no and i.ctg_id=d.ctg_id and i.ctg_id=ctg.id and i.brand_id=br.id and i.fabric_type_id=ft.id and i.prod_type_id=pt.id and i.material_id=mt.id group by i.ctg_id, i.design_no order by i.ctg_id,i.design_no";
            $db = new DBConn();
            $allDesigns = $db->fetchObjectArray($query);
            $db->closeConnection();
            ?>

            <?php
            $row_no = 0;
            foreach ($allDesigns as $design) {
                $row_no++;
                $design_no = $db->safe($design->design_no);
                $ctg_id = $db->safe($design->ctg_id);
                $divid = "accordion-" . $row_no;
                ?>
    <div class="clear"></div>
    <div class="box">
        <h2>Category: <?php echo $design->category." | Brand: $design->brand | Fabric: $design->fabric | Material: $design->material | Production Type: $design->prodtype";?> | Design No: <?php echo $design->design_no." [ MRP: ".$design->MRP." ]"; ?></h2>
        <div class="block" id="<?php echo $divid; ?>">
            <form name="order_<?php echo $row_no; ?>" method="post" action="" onsubmit="addToCart(this); return false;">
                <input type="hidden" name="design_no" value="<?php echo $design->design_no; ?>" />
                <input type="hidden" name="mrp" value="<?php echo $design->MRP; ?>" />
                <!--<input type="hidden" name="ctg" value="<?php //echo $design->ctg_id; ?>" />-->
                <a href="images/stock/<?php echo $design->image; ?>" rel="prettyPhoto"><img class="grid_2" align="left" src="images/stock/<?php echo $design->image; ?>" height="170px" width="170px" /></a>
                <div class="block grid_10">
                    <table>
                                    <?php
                                    $db = new DBConn();
                                    $styleobj = $db->fetchObjectArray("select s1.style_id,s2.name as style_name from it_ck_styles s1,it_styles s2 where s1.ctg_id=$ctg_id and s1.style_id=s2.id order by s1.sequence");
                                    $db->closeConnection();
                                    $no_styles = count($styleobj);
                                    $db = new DBConn();
                                    $sizeobj = $db->fetchObjectArray("select s1.size_id, s2.name as size_name from it_ck_sizes s1,it_sizes s2 where s1.ctg_id=$ctg_id and s1.size_id=s2.id order by s1.sequence");
                                    $db->closeConnection();
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
                                            print "<tr id='styles::".$design->id."::".$styleobj[$k]->style_id."'><th>";
                                            echo $styleobj[$k]->style_name;
                                            print"</th>";
                                            //store style id in $stylecod
                                            $stylcod = $styleobj[$k]->style_id;
                                            for ($i = 0; $i < $no_sizes; $i++) {
                                                ?><td><?php
                                                        //to get the quantity and stock id of specific item
                                                        $sizeid = $sizeobj[$i]->size_id;
							$query = "select id,sum(curr_qty) as qty from it_items where design_no = $design_no and MRP=$design->MRP and ctg_id=$ctg_id and style_id = '$stylcod' and size_id = '$sizeid'";
                                                         $db = new DBConn();
                                                        $getitm = $db->fetchObject($query);
                                                        $db->closeConnection();
                                                        //check to see if specific item exists in order, if exist -> stores qty in order_qty  
                                                        if ($getitm) {
                                                            $q1 = "select o.item_id as id from it_ck_orderitems o , it_items i where o.order_id = $cart->id and o.store_id = $storeid and o.item_id = i.id and i.style_id = '$stylcod' and i.size_id = '$sizeid' and i.design_no = $design_no and i.ctg_id =  $ctg_id and i.MRP = $design->MRP ";
//                                                            echo "$q1";
                                                            $db = new DBConn();
                                                            $getOitm = $db->fetchObject($q1);
                                                            $db->closeConnection();
                                                            if($getOitm){ $idVal = " $getOitm->id ";}else{ $idVal = " $getitm->id"; }
                                                            $query = "select sum(order_qty) as order_qty from it_ck_orderitems where order_id=$cart->id and store_id=$storeid  and item_id = $idVal";
//                                                            echo $query;
                                                            $db = new DBConn();
                                                            $exist = $db->fetchObject($query);
                                                            
                                                              $validate="";
                                                         $margin_query="select c.name as ctg_name,i.num_units as units,i.ctg_id  as ctg_id,c.margin from it_items i,it_categories c where i.ctg_id=c.id and i.id=$idVal";
                                                        $mobj=$db->fetchObject($margin_query);
                                                        if($mobj){
                                                            $margin=$mobj->margin;
                                                           if($margin==0 && $mobj->ctg_id !=41)
                                                            {
                                                               $validate="onkeyup='validate($getitm->id)'"; 
                                                            }
                                                        }
                                                            $db->closeConnection();
                                                            if ($getOitm) { $id = $getOitm->id; }else if ($getitm->id) { $id = $getitm->id; } else { $id="0"; }
                                                            if ($getitm->qty) { $qty=$getitm->qty; } else { $qty="0"; }
                                                            ?><input type='text'id="validateid_<?php echo $getitm->id ;?>" pattern= "[0-9]+" title="ONLY NUMBER" <?php echo $validate; ?> style='width: 30px;' name="item_<?php echo $id."_".$qty; ?>" <?php
                                                                if ($exist) {
                                                                    print "value='";
                                                                    echo $exist->order_qty;
                                                                    print "'";
                                                                }
                                                                ?>/><br>[ <?php echo $getitm->qty; ?> ]<?php
                                                        }
                                                ?></td><?php
                                            }
                                            print "</tr>";
                                        }
                                        
                                        if($design->ctg_id == '29'){
//                                            print "<tr><td>";
//                                            echo $design->remarks."DEMO";
//                                            print "</td></tr>";
//                                        }
                                        ?>
                        <tr><th>Requirements</th><td><?php echo $design->remarks; ?></td></tr>
                                        <?php } ?>
                        </tbody>
                    </table>
                    <div id="status_<?php echo $row_no; ?>"></div>
                    <?php
            if ($this->currStore->inactive == 1) { 
                ?> <input class="blueglassbutton" type="button" value="UPDATE DESIGN" onclick="add_toCart()"/>  
            <?php } else { ?>
            <input class="blueglassbutton" type="submit" value="UPDATE DESIGN"/><?php }?>
                    <input class="blueglassbutton" type="button" onclick="deleteCartItem('order_<?php echo $row_no; ?>');" value="REMOVE ITEM"></input>
                </div> <!-- end class=grid_10 -->
            </form>
        </div> <!-- end class="block" -->
    </div> <!-- end class="box" -->
    <div class="clear"></div>
            <?php
            } // end foreach allDesigns
            //$query = "SELECT d.image,d.design_no, d.ctg_id, d.prod_type,o.remarks, sum(o.order_qty) as total_qty from it_ck_designs d, it_ck_orderitems o where o.ctg_id='oth' and o.design_no=d.design_no and o.order_id=$cart->id group by o.design_no order by d.design_no";
            //$allDesigns = $db->fetchObjectArray($query); 
            //foreach ($allDesigns as $design) { $row_no++; ?>
    <!--<div class="box">
        <h2>Product : <?php //echo $design->design_no." [ MRP: ".$design->prod_type." ]"; ?></h2>
        <div class="block" id="<?php //echo $divid; ?>">
             <a href="images/stock/<?php //echo $design->image; ?>" rel="prettyPhoto"><img class="grid_2" align="left" src="images/stock/<?php //echo $design->image; ?>" width="170" /></a>
                <div class="block grid_10" style="margin-top:30px;">
                    <?php// if ($design->total_qty) { ?><label style="font-weight: bold;">Order Quantity : </label><label type="text" name="qty" style="width:7%;"> <?php  //print $design->total_qty; } ?>
                    <?php// if ($design->remarks) { ?><label style="font-weight: bold; margin-left:15px;">Requirements : </label><label type="text" name="remarks" style="width:35%;" ><?php //print $design->remarks; ?></label><?php// } ?>
                </div>
        </div>     
        <div class="clear"></div>
    </div>-->
    <div class="clear"></div>
    
<?php // } 
if ($row_no == 0) { ?>
	<div class="error">YOUR CART IS EMPTY. PLEASE BROWSE THROUGH THE CATALOG TO ADD ITEMS TO YOUR CART.</div>
<?php }
            ?>
</div>
    <?php
    }
}
?>
