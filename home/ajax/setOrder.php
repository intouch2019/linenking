<?php
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
require_once "lib/orders/clsOrders.php";
require_once ("session_check.php");
require_once "formpost/MatersStockQtyCalc.php";

extract($_GET);
$db=new DBConn();
$store_id = getCurrUserId();
$errors="";
$clsOrders = new clsOrders();
$cart = $clsOrders->getCartt($store_id);
$eligibleStores = getMasterStackEligibleStores(); //Initially, only a limited number of stores are eligible to place orders within the stack capacity.
$items = array();
foreach ($_GET as $name => $value) {
    if ($value && startsWith($name, "item_")) {
        $arr = explode("_", $name);
        $items[] = (object) array(
            "item_id" => $arr[1],
            "avl_qty" => $arr[2],
            "req_qty" => $value
        );
//        print_r($eligibleStores);exit();
        //        	 <-------  Master stock limit code start------------>
        if(in_array($store_id, $eligibleStores)){ // Applying only for one store remove when making it live for all stores
            $added_to_cart_qry="select ctg_id, style_id, size_id from it_items where id=$arr[1]"; // This is ordered product details/ product added in cart
            $added_to_cart=$db->fetchObject($added_to_cart_qry);
            if(!empty($added_to_cart)){
                // Get master and store stock for item currently added in cart
                $curr_store_master_stock_ctg_style_size_wise=getMaterStoreStock($store_id, $added_to_cart->ctg_id, $added_to_cart->style_id, $added_to_cart->size_id);
                
                $curr_store_stock_ctg_style_size_wise= getCurrentStoreStock($store_id, $added_to_cart->ctg_id, $added_to_cart->style_id, $added_to_cart->size_id);
                
                $eligible_categories = checkEligibleCategories($added_to_cart->ctg_id);
                
                $buffer = getLastThreeMonthSale($store_id, $added_to_cart->ctg_id, $added_to_cart->style_id, $added_to_cart->size_id);

//                print_r($cart->id);exit();
                // Get quantity for items that are already in cart
                $items_in_cart=$clsOrders->getCartItems($cart->id);
                
                // For each item in cart check if currently added cart item exceeds the master stock quantity
                foreach ($items_in_cart as $item_in_cart) {
                    $cart_item_details=$db->fetchObject("select ctg_id, style_id, size_id from it_items where id=$item_in_cart->item_id");
                    if ($cart_item_details->ctg_id == $added_to_cart->ctg_id &&
                        $cart_item_details->style_id == $added_to_cart->style_id &&
                        $cart_item_details->size_id == $added_to_cart->size_id) {
                        
                        $curr_store_stock_ctg_style_size_wise += $item_in_cart->order_qty; // $item_in_cart->order_qty = It is the qty of item already in cart
                    }                                                                               
                }
                    $curr_store_stock_ctg_style_size_wise += $value; // $value = current cart qty
                // Return error if curr_stock is greater or equal to buffer + master stacking capacity including cart items
                if($eligible_categories && $curr_store_stock_ctg_style_size_wise> ($curr_store_master_stock_ctg_style_size_wise + $buffer)){
                    
                    $errors .="Your stock including cart items is greater or equal to master stacking capacity please check!";
                    return error($errors);
                }
            }
        }
        //        	 <-------  Master stock limit code ends------------>
    }
    else if ((!$value || $value==0) && startsWith($name, "item_")) {
        $arr = explode("_",$name);
        if ($arr[1]!="0") {
            $remove[] = (object)array(
                "item_id" => $arr[1],
            );
        }
    }
    else {$remove="";}
}


$design_no = $db->safe($design_no);
$store_id = getCurrUserId();
$errors="";
$clsOrders = new clsOrders();
$cart = $clsOrders->getCartt($store_id);
if (!$cart) { return error("Cart not found:$store_id. Please report this problem."); }
$queries = array();
foreach ($items as $item) {
    $dbitem=$db->fetchObject("select * from it_items where id=$item->item_id");
    $stock = $db->fetchObject("select sum(curr_qty) as qty,is_avail_manual_order from it_items where ctg_id=$dbitem->ctg_id and design_no='$dbitem->design_no' and MRP=$dbitem->MRP and style_id=$dbitem->style_id and size_id=$dbitem->size_id");
    $exist = $db->fetchObject ("select id,item_id,order_qty from it_ck_orderitems where order_id=$cart->id and item_id=$item->item_id");
    //$exist=$db->fetchObject("select id, ctg_id,design_no,MRP,style_id,size_id from it_ck_orderitems where order_id=$cart->id and ctg_id=$ctg_id and design_no=$design_no and MRP=$mrp and style_id=$style_id and size_id=$size_id");
    if ($item->req_qty < 0)
      {$errors .= "Value cannot be negative for quantity. <br/>"; }
    else if ($stock->qty <= 0 || $stock->is_avail_manual_order ==0)
      {$errors .= "Quantity is unavailable for that item. <br/>"; }
    else if ($item->req_qty > $stock->qty)
      { $errors .= "Entered ".$item->req_qty.", available Quantity ".$stock->qty."<br />"; }
    else if ($exist)
      { $queries[] = "update it_ck_orderitems set order_qty=$item->req_qty, updatetime=now() where id = $exist->id"; }
    else
      { $queries[] = "insert into it_ck_orderitems set order_id=$cart->id,store_id=$store_id,item_id=$item->item_id,design_no=$design_no, MRP=$mrp, order_qty=$item->req_qty"; }
}

if ($remove) {
   foreach ($remove as $remv) {
       $exist=$db->fetchObject("select id from it_ck_orderitems where order_id=$cart->id and store_id=$store_id and item_id=$remv->item_id");
       if ($exist) {
           $queries[]="delete from it_ck_orderitems where id=$exist->id";
       }
       //print_r($queries);print"<br>";
   }
}

if ($errors != "")
{ return error($errors); }

//return error(join("<br />", $queries));
foreach ($queries as $query) {
    $db->execQuery($query);
}

// update the cart with the totals
//unmesh - $query = "select sum(order_qty) as tot_qty, sum(order_qty * MRP) as tot_amt, count(distinct(design_no)) as num_designs from it_ck_orderitems where order_id=$cart->id and store_id=$store_id";
$query = "select sum(oi.order_qty) as tot_qty, sum(oi.order_qty * oi.MRP) as tot_amt, count(distinct(oi.design_no)) as num_designs from it_ck_orderitems oi, it_items i where oi.order_id=$cart->id and oi.item_id = i.id and i.ctg_id != 29";
//return error($query);
$cartinfo = $db->fetchObject($query);
$subQuery = "";
$tot_qty = 0; $tot_amt = 0;
if ($cartinfo && $cartinfo->tot_qty && $cartinfo->tot_amt && $cartinfo->num_designs) {
$query="update it_ck_orders set order_qty=$cartinfo->tot_qty, order_amount=$cartinfo->tot_amt, num_designs=$cartinfo->num_designs where id=$cart->id";
$db->execUpdate($query);
$cartinfoStr = "Order No: $cart->order_no | Quantity: $cartinfo->tot_qty | Amount: $cartinfo->tot_amt";
$tot_qty = $cartinfo->tot_qty;
$tot_amt = $cartinfo->tot_amt;
} else {
$query="update it_ck_orders set order_qty=0, order_amount=0, num_designs=$cartinfo->num_designs where id=$cart->id and store_id=$store_id";
$db->execUpdate($query);
$cartinfoStr = "Order No: $cart->order_no | Quantity: 0 | Amount: 0";
$tot_qty = "0";
$tot_amt = "0";
}

if (count($items) == 0) { return success("All items removed", $cartinfoStr,$tot_qty,$tot_amt); }
else return success("Items in cart updated", $cartinfoStr,$tot_qty,$tot_amt);

function error($msg) {
    echo json_encode(array(
            "error" => "1",
            "message" => $msg
            ));
}

function success($msg,$msg2,$totq,$totp) {
    echo json_encode(array(
            "error" => "0",
            "message" => $msg,
            "cartinfo" => $msg2,
            "totqt" => $totq,
            "totmrp" => $totp
            ));
}
function startsWith($haystack, $needle)
{
    $length = strlen($needle);
    return (substr($haystack, 0, $length) === $needle);
}
?>
