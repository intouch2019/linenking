<?php
require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "lib/orders/clsOrders.php";
require_once "session_check.php";

class cls_store_checkout extends cls_renderer {

    var $params;
    var $currStore;

    function __construct($params=null) {
        $this->currStore = getCurrUser();
    }

    function extraHeaders() {
        if (!$this->currStore) {
            return;
        }
        ?>
<link rel="stylesheet" type="text/css" href="css/dark-glass/sidebar.css" />
<link rel="stylesheet" type="text/css" href="css/lightbox.css" />
<script type="text/javascript" src="js/sidebar/jquery.sidebar.js"></script>
<script type="text/javascript">
     
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

function lightbox_open(){
    window.scrollTo(0,0);
    document.getElementById('light').style.display='block';
    document.getElementById('fade').style.display='block'; 
}

function mslmsg(order_val,exp_order_val,order_id){    
    var msg="<b>Your Current Order Value is Rs."+order_val+"<br/> Expected Order Value should be Rs."+exp_order_val+"</b>";
    $("#order_id").val(order_id);
    $("#mslmsgpost").val(msg);
    document.getElementById("mslmsg").innerHTML = msg;
    lightbox_open();
}



function show(order_val,min_stock,difference,order_id){
   // alert(order_val);//
   
     var msg="Your Are Not Allowed To Place Order Because Current Order Value is Rs."+order_val+" ,Expected Order Value Should Be Rs."+difference+" ,To Reach Upto Your Store Stock Limit Is Rs."+min_stock+"";
    alert(msg);
}
function showmaxstock(maxsl){
//    alert(maxsl);
    var msg="Your Order Exceeding The Maximum Stock Level, So Kindly Adjust The Order Value According To Your Maximum Stock level Is Rs."+maxsl+".";
//    window.location.href="store/viewcart/";//

        alert(msg);
        
}


function show1(){
             // alert('hiiiiiii');
        
                var uri = "ajax/finalOrder_Check.php";
                $.ajax({
                          url: uri,
                          success: function(res)
                                 {

                                       if(res){
                                       $msg=res;
                                       $dat = $msg.split("[");
                                       $case=$dat[0];
                                      // alert($case);
                                       var msg;
                                       switch ($case)  //$case is for to show msg which is coming from ajax responce
                                       {
                                            case '1':
                                              msg = "आपले ऑर्डर यशस्वीरित्या करण्यात अयशस्वी. \n\nFailed To Process Your Orders. ";
                                              alert(msg);
                                              window.location.href="store/viewcart/";
                                            break;
                                            
                                            case '2':
                                                   // this section for Failed To Process Orders Because  Following Designs Are Dropped 
                                              msg = "दुसर्‍या स्टोअरने आपल्या आधी यशस्वीरित्या ऑर्डर दिल्याने खालील डिझाईन क्रमांक . ("+$dat[1]+") मधील संपूर्ण क्वांन्टिटी  ऑर्डर केली गेली आहे. लेफ्ट ओव्हर यादी तपासा आणि उपलब्ध असल्यास पुन्हा ऑर्डर द्या. \n\nThe Entire Qty In The Following Design No.("+$dat[1]+") Has Been Dropped As Another Store Has Successfully Placed Order Before You. Check For Left Over Inventory And Place Order Again If Available.";
                                              alert(msg);
                                              window.location.href="store/designs/ctg=8";
                                            break;
                                           
                                            case '3':
                                                   //this section for Order Placed Succesfully
                                              msg = "ऑर्डर यशस्वीपणे दिली. \n\nOrder Placed Succesfully. ";
                                              alert(msg);
                                              window.location.href="store/orders/active";
                                            break;
                                            case '4':
                                                //this section for Order Placed Succesfully but following design drop.
                                              msg = "दुसर्‍या स्टोअरने आपल्या आधी यशस्वीरित्या ऑर्डर दिल्याने खालील डिझाईन क्रमांक . ("+$dat[1]+") मधील संपूर्ण क्वांन्टिटी  ऑर्डर केली गेली आहे. लेफ्ट ओव्हर यादी तपासा आणि उपलब्ध असल्यास पुन्हा ऑर्डर द्या. \n\nThe Entire Qty In The Following Design No.("+$dat[1]+") Has Been Dropped As Another Store Has Successfully Placed Order Before You. Check For Left Over Inventory And Place Order Again If Available.";
                                              alert(msg);
                                              window.location.href="store/orders/active";
                                            break;
                                            
                                            default: 
                                                //     this section for Failed To Process Orders
                                              msg = "आपले ऑर्डर यशस्वीरित्या करण्यात अयशस्वी. \n\nFailed To Process Your Orders. ";
                                              alert(msg);
                                              window.location.href="store/viewcart/";
                                              
                                        }
                                    }
                                  }       
                 });
}




</script>

    <?php
        ?>

    <?php
    }

    //extra-headers close

    public function pageContent() {
	$menuitem="";
        include "sidemenu.".$this->currStore->usertype.".php";
	$clsOrders = new clsOrders();
	$cart = $clsOrders->getCart($this->currStore->id);
        ?>
<div class="grid_10">
            <?php
            $checkout_page=true; 

            $db = new DBConn();
            $store_id = getCurrUserId();
            $query = "SELECT i.design_no, i.ctg_id, ctg.name, i.MRP, sum(o.order_qty) as total_qty from it_items i,it_categories ctg, it_ck_orderitems o where o.store_id=$store_id and o.order_id=$cart->id and o.item_id=i.id and i.ctg_id=ctg.id group by i.ctg_id, i.design_no order by i.ctg_id,i.design_no";
            $allDesigns = $db->fetchObjectArray($query);

            $code =$db->safe($this->currStore->code);
//            $storeDetails=$db->fetchObject("select * from it_codes where code=$code");
            $storeDetails=$db->fetchObject("select store_name,address,city,owner,phone,email,vat from it_codes where code=$code");
            if ($storeDetails) {
            include "cartinfo.php";
            }
            ?>
        <div class="grid_1">&nbsp;</div>
        <div class="grid_5">
            <div class="box">
                <h2>Store Details</h2>
                <?php
                if ($storeDetails)
                {  ?>
                <h5>Dealer Name : <?php echo $storeDetails->store_name; ?></h5>
                <h5>Address : <?php echo $storeDetails->address; ?></h5>
                <h5>City : <?php echo $storeDetails->city; ?></h5>
                <h5>Contact Name : <?php echo $storeDetails->owner; ?></h5>
                <h5>Contact Number : <?php echo $storeDetails->phone; ?></h5>
                <h5>Email : <?php echo $storeDetails->email; ?></h5>
                <h5>VAT/TIN number : <?php echo $storeDetails->vat; ?></h5>
                <?php } else { ?>
                <h5> Please enter store details in settings page </h5>
                <h4><a href="user/settings"><button>Proceed to fill details</button></a></h4>
                <?php } ?>
            </div>
        </div>
            <?php
            
            $row_no = 0;
            $prevctg="";
            foreach ($allDesigns as $design) {
		if ($design->ctg_id == 29) { continue; }
                $row_no++;
                $design_no = $db->safe($design->design_no);
                $ctg_id = $db->safe($design->ctg_id);
                
                $divid = "accordion-" . $row_no;
            if ($design->ctg_id !=$prevctg) {
                $prevctg=$design->ctg_id;
                $ctg=$db->safe($design->ctg_id);
                $topinfo = $db->fetchObject ("select count(distinct o.design_no) as num_des,sum(o.order_qty) as tot_qty, sum(o.order_qty * o.MRP) as tot_mrp from it_ck_orderitems o,it_items i where o.item_id=i.id and i.ctg_id=$ctg and order_id=$cart->id");
                ?>

    <div class="clear"></div>    
    <div class="box">
        <h2>CATEGORY: <?php echo $design->name;?> | NO. OF DESIGNS : <?php echo $topinfo->num_des;?> | TOT QTY : <?php echo $topinfo->tot_qty; ?> | TOT MRP: <?php echo $topinfo->tot_mrp;?></h2>
        <div class="block" id="<?php echo $divid; ?>">
            <form name="order_<?php echo $row_no; ?>" method="post" action="" onsubmit="addToCart(this); return false;">
                <input type="hidden" name="ctg_id" value="<?php echo $design->ctg_id; ?>" />
                <input type="hidden" name="number " value="<?php echo $design->design_no; ?>" />
                <input type="hidden" name="mrp" value="<?php echo $design->MRP; ?>" />
                <div class="block grid_12">
                    <table align="center">
                                    <?php
                                    $styleobj = $db->fetchObjectArray("select s1.style_id,s2.name as style_name from it_ck_styles s1,it_styles s2 where s1.style_id=s2.id and s1.ctg_id=$ctg order by s1.sequence");
                                    $no_styles = count($styleobj);
                                    $sizeobj = $db->fetchObjectArray("select s1.size_id, s2.name as size_name from it_ck_sizes s1,it_sizes s2 where s1.size_id=s2.id and s1.ctg_id=$ctg order by s1.sequence");
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
                                            //store style id in $stylecod
                                            $stylcod = $styleobj[$k]->style_id;
                                            //for each unique size check if size for that particular style is available. if available->show input box. if in order->show qty.
                                            for ($i = 0; $i < $no_sizes; $i++) {
                                                ?><td><?php
                                                        $sizecod= $sizeobj[$i]->size_id;
                                                        //to get the all quantities of item with specific ctg_id,size, and style.
                                                        $qty =$db->fetchObject("select sum(o.order_qty) as order_qt from it_ck_orderitems o,it_items i where o.item_id=i.id and o.order_id=$cart->id and o.store_id=$store_id and i.ctg_id=$ctg and i.style_id=$stylcod and i.size_id=$sizecod group by ctg_id");
                                                                if ($qty) {
                                                                    echo $qty->order_qt;
                                                                }
                                                ?></td><?php
                                            }
                                            print "</tr>";
                                        }
                                        ?>
                        </tbody>
                    </table>
                    <div id="status_<?php echo $row_no; ?>"></div>
                </div> <!-- end class=grid_10 -->
            </form>
        </div> <!-- end class="block" -->
    </div> <!-- end class="box" -->
    <div class="clear"></div>
    <?php
             }
            }
//            $others = $db->fetchObjectArray("select * from it_ck_orderitems o, it_items i where o.item_id = i.id and i.ctg_id=29 and o.store_id=$store_id and o.order_id=$cart->id order by i.design_no");
            $others = $db->fetchObjectArray("select i.design_no,o.order_qty,o.remarks from it_ck_orderitems o, it_items i where o.item_id = i.id and i.ctg_id=29 and o.store_id=$store_id and o.order_id=$cart->id order by i.design_no");
           if (count($others)>0) {
             $topinfo = $db->fetchObject ("select sum(o.order_qty) as tot_qty, sum(o.order_qty * o.mrp) as tot_mrp from it_ck_orderitems o, it_items i where o.item_id = i.id and i.ctg_id=29 and o.order_id=$cart->id");
            ?>
    <div class="clear"></div>
    <div class="box">
        <h2>CATEGORY: OTHERS | TOT QTY : <?php echo $topinfo->tot_qty; ?> | TOT MRP: <?php echo $topinfo->tot_mrp;?></h2>
        <div class="block" id="<?php echo $divid; ?>">
                <div class="block grid_12">
                    <table align="center" width="100%">
                        <thead>
                            <tr><th width="20%"></th>
                                <th width="20%">Total Quantity</th>
                                <th width="60%">Requirements</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($others as $other) { ?>
                            <tr>
                            <th><?php echo $other->design_no; ?></th>
                            <td><?php if ($other->order_qty) echo $other->order_qty; ?></td>
                            <td><?php echo $other->remarks; ?></td>
                            <tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div> <!-- end class=grid_10 -->
        </div> <!-- end class="block" -->
	<div class="clear"></div>
    </div>  <!-- end class="box" -->
    <div class="clear"></div>
</div>
    <?php } ?>
<div id="light" class="light">
        <form id="mslAck" name="mslAck" method="post" action="formpost/ackMSLMsg.php">
            <input type="hidden" id="order_id" name="order_id" value="">
            <label id="mslmsg" name="mslmsg"></label>
            <input type="hidden" id="mslmsgpost" name="mslmsgpost" value="">
            <br/><br/><br/><br/>
            <input type="submit" value="Acknowledge">
        </form>
    </div>
<div id="fade" class="fade"></div> 
<?php
    }
}
?>
