<?php
//require_once "lib/core/Constants.php";
require_once "lib/orders/clsOrders.php";

//require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "session_check.php";
//require_once "lib/items/clsItems.php";

$store_id=getCurrUserId();
$clsOrders = new clsOrders();
$cart = $clsOrders->getCart($store_id);
$cartinfo = $clsOrders->getCartInfo($store_id);
$cartinfoStr = $clsOrders->printCartInfo($cartinfo);
$currUri = $_SERVER["REQUEST_URI"];
$link=false; $linkName=false;
$is_inactive=0;

$picking_complete_amt=0;
$picking_amt=0;
$active_amt=0;
$total_orderamt_pickcomplete=0;
$difference=0;
$min_stock=0;
$max_stock=0;

$store_stock=0;
$curr_stock_val =0;
$intransit_stock_value_new=0;

$mslflag = 0;$fncall="";$expctd_order_val=0;$order_tot_val=0;
//before placing order do MSL chk if the MSL is set against the store
$db = new DBConn();
//$msl = $db->fetchObject("select * from it_codes where id = $store_id ");
$msl = $db->fetchObject("select min_stock_level,max_stock_level,inactive from it_codes where id = $store_id ");
//print_r($msl);
$db->closeConnection();
if(isset($msl) && trim($msl->min_stock_level)!=""){
    //step 1: fetch current order's tot val
    $db = new DBConn();
    $order_val = $db->fetchObject("select sum(order_qty * MRP) as tot_amt from it_ck_orderitems where order_id=$cart->id ");
    $db->closeConnection();
    if(isset($order_val) && trim($order_val->tot_amt) !=""){ $order_tot_val = $order_val->tot_amt; }else{ $order_tot_val = 0; }
    //step 2: fetch store current stock value
    $db = new DBConn();
    $store_stock = $db->fetchObject("select sum(c.quantity * i.MRP) as curr_stock_value from it_current_stock c , it_items i where c.store_id = $store_id  and c.barcode = i.barcode and i.ctg_id not in (53,54,64,62,63,41,56,52,51,61,46,42,43)");
    $db->closeConnection();
    if(isset($store_stock) && trim($store_stock->curr_stock_value) !=""){ $curr_stock_val = $store_stock->curr_stock_value; }else{ $curr_stock_val = 0; }
    //step 3: fetch store's stock in transit
    $db = new DBConn();
    $stock_intransit = $db->fetchObject("select sum(invoice_amt) as intransit_stock_value from it_sp_invoices where  invoice_type in ( 0 , 6 ) and store_id = $store_id and is_procsdForRetail = 0 ");
    $db->closeConnection();
    
    $db = new DBConn();
    $stock_intransit_new = $db->fetchObject("select sum(i.MRP*oi.quantity) as intransit_stock_value_new from it_sp_invoices o , it_sp_invoice_items oi , it_items i where oi.invoice_id = o.id and o.invoice_type in ( 0 , 6 ) and o.store_id =$store_id  and o.is_procsdForRetail = 0 and oi.barcode = i.barcode");
    $db->closeConnection();
    
    if(isset($stock_intransit) && trim($stock_intransit->intransit_stock_value) !=""){ $intransit_stock_value = $stock_intransit->intransit_stock_value; }else{ $intransit_stock_value = 0; }
    //step 4: check if Store Stock+Transit Stock+Current order val < MSL
    $tot_stk_val = $curr_stock_val+$intransit_stock_value+$order_tot_val;
    
    if(isset($stock_intransit_new) && trim($stock_intransit_new->intransit_stock_value_new) !=""){ $intransit_stock_value_new = $stock_intransit_new->intransit_stock_value_new; }else{ $intransit_stock_value_new = 0; }
     
    if(($tot_stk_val < $msl->min_stock_level) && ($cart->msl_ack==0)){
        $expctd_order_val = $msl->min_stock_level - ($curr_stock_val + $intransit_stock_value);
      //  $mslflag=1;
    }
    
    
    //step: check active ammount from order
    $db = new DBConn();
    $active_amount = $db->fetchObject("select sum(order_amount) as active_amount from it_ck_orders where status=1 and store_id=$store_id");
    $db->closeConnection();
    if(isset($active_amount) && trim($active_amount->active_amount)!=""){
        $active_amt=$active_amount->active_amount;
    }
    else{
        $active_amt=0;
    }
    
    
    
    //step: check picking ammount from order
    $db = new DBConn();
    $picking_amount = $db->fetchObject("select sum(order_amount) as picking_amount  from it_ck_orders where status=2 and store_id=$store_id");
    $db->closeConnection();
    if(isset($picking_amount) && trim($picking_amount->picking_amount)!=""){
        $picking_amt=$picking_amount->picking_amount;
    }
    else{
        $picking_amt=0;
    }
      //step: check picking complete  ammount from order
    
    $db = new DBConn();
    $picking_complete_amount = $db->fetchObject("select sum(order_amount) as picking_complete_amount  from it_ck_orders where status=5 and store_id=$store_id");
    $db->closeConnection();
    if(isset($picking_complete_amount) && trim($picking_complete_amount->picking_complete_amount)!=""){
        $picking_complete_amt=$picking_complete_amount->picking_complete_amount;
    }
    else{
        $picking_complete_amt=0;
    }
    
    
}
if(isset($msl))
{  
    $is_inactive=$msl->inactive;
    $min_stock=$msl->min_stock_level;
    $max_stock=$msl->max_stock_level;
} 
if (strpos($currUri,"/store/designs") !== false) { $link = "store/viewcart"; $linkName="Preview Order"; }
else
if ($cartinfo->quantity > 0 && strpos($currUri,"/store/viewcart") !== false) { if($is_inactive==0){ $link = "store/checkout"; $linkName="Confirm Order";}else{$link = "store/viewcart"; $linkName="Confirm Order";$fncall="onclick=add_toCart()";} }
else    

if ($cartinfo->quantity > 0 && strpos($currUri,"/store/checkout") !== false){
    $total_orderamt_pickcomplete=$active_amt+$picking_amt+$picking_complete_amt;
    $difference=$min_stock-$curr_stock_val-$intransit_stock_value_new-$total_orderamt_pickcomplete;
    
//   print 'intransit value :'.$intransit_stock_value_new.'</br>';
//   print 'cart value :'.$cartinfo->amount.'</br>';
//    print 'current stock value :'.$curr_stock_val.'</br>';
////
// $a= $curr_stock_val+$cartinfo->amount+$intransit_stock_value_new;
//  echo 'total order is value'.$a.'</br>';
//  echo 'minimum stock:'.$min_stock.'</br>';
//     
//     echo 'active order value'.$active_amt;
//    echo '<br>';
//     echo 'picking order value'.$picking_amt;
//    echo '<br>';
//    
//     echo 'picking complete order value'.$picking_complete_amt;
//    echo '<br>';
//     echo 'total order with picking complete value'.$total_orderamt_pickcomplete;
//    echo '<br>';
    
 if($curr_stock_val+$cartinfo->amount+$intransit_stock_value_new+$total_orderamt_pickcomplete>=$min_stock)
    {
//          echo 'minimum value is crossed'.'</br>';
//      echo 'maximum'.$max_stock.'<br>';
     if($max_stock!=null && $max_stock!="" )
     {
        if($curr_stock_val+$cartinfo->amount+$intransit_stock_value_new + $active_amt + $picking_amt + $picking_complete_amt >=$max_stock)
        {
          //echo '<br>';
          //  echo 'not place to due to max stock value '.'<br>';
          $link = "store/checkout";
          $fncall ="onclick=showmaxstock(".$max_stock.");";
          $linkName="Place Order"; 
        }
     else
     { 
         //echo '<br>';
         //echo 'order is place because value between max and min';
         $link = "ajax/finalOrder.php";
         $linkName="Place Order"; 
     } 
    
    }
    else{
       //   echo 'order is place because  max is not set ';
         $link = "ajax/finalOrder.php";
          $linkName="Place Order"; 
    }
   
 }
    
 else{
       //echo 'false';
       // echo 'minimum value is not  crossed';
      $link = "store/checkout";
      $fncall ="onclick=show(".$order_tot_val.",".$min_stock.",".$difference.",".$cart->id.");";
      $linkName="Place Order"; 
  }
  
  
}

      $this->storeinfo = getCurrUser();
        $dbProperties = new dbProperties();
//        echo $this->storeinfo->inactive;
    
?>


<div class="box" style="height:30px; border:2px solid;">
	<h3>
	<div style="float:left" id="carttop"><?php echo $cartinfoStr; ?></div>
<?php if ($link) { ?>
	<div style="float:right;margin-top:-5px;">
                         
            <?php if (isset($this->storeinfo->inactive) && $this->storeinfo->inactive == 0 && isset($this->storeinfo->usertype) && $this->storeinfo->usertype == 4) {
             if (($dbProperties->getBoolean(Properties::DisableUserLogins) == 0) && isset($this->storeinfo->usertype) && $this->storeinfo->usertype == 4) { ?>
             
            <img src="images/cart.png" style="vertical-align:bottom;"/>&nbsp;
            <?php  if ($link!="ajax/finalOrder.php"){?>
            <a <?php echo $fncall; ?> href="<?php echo $link; ?>"><button><?php echo $linkName; ?></button></a>
            <?php  }else{ ?>            
            <input type="button" value="<?php echo $linkName; ?>"  onclick="show1();">
            <?php  } 
              }} ?>
        </div>
        <ul id="demo_menu1" >
<?php if ($cartinfo->order_no) { ?>
            <div id="order_no">NO: <?php echo $cartinfo->order_no; ?></div>
<?php } ?>
            <div id="side_qty">QTY: <?php echo $cartinfo->quantity; ?></div>
            <div id="side_price">AMT: <?php echo $cartinfo->amount; ?></div>
            
             <?php if (isset($this->storeinfo->inactive) && $this->storeinfo->inactive == 0 && isset($this->storeinfo->usertype) && $this->storeinfo->usertype == 4) {
             if (($dbProperties->getBoolean(Properties::DisableUserLogins) == 0) && isset($this->storeinfo->usertype) && $this->storeinfo->usertype == 4) { ?>

            <li>
              <?php  if ($link!="ajax/finalOrder.php"){?>
            <!--<a <?php // echo $fncall; ?> href="<?php // echo $link; ?>" onclick="show1();"><button><?php // echo $linkName; ?></button></a>-->
            <a <?php echo $fncall; ?> href="<?php echo $link; ?>"><button><?php echo $linkName; ?></button></a>
            <?php  }else{ ?>            
            <input type="button" value="<?php echo $linkName; ?>"  onclick="show1();">
            <?php  } ?>
            
<!--               <a <?php //echo $fncall; ?> href="<?php// echo $link; ?>" ><img src="images/cart.png" style="vertical-align:bottom;"/><button><?php // echo $linkName; ?></button></a>
            
            -->
            </li>
            <?php }} ?>
        </ul>
<?php } ?>
	</h3>
</div>


<div class="clear" style="margin-bottom:10px;"></div>