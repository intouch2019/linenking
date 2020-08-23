<?php
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
require_once "lib/orders/clsOrders.php";
require_once("session_check.php");

//print_r ($_GET);
extract($_GET);
$db=new DBConn();
$items = array();
if(!$quantity || !$remarks){ return error("Please enter quantity and requirements"); }
if (!$ctg_id || !$design_no ) { return error("General Error - please report this"); }
$store_id =  getCurrUserId(); //getCurrStoreId();
// verify avail-quantity
$errors="";
$design_no = $db->safe($design_no);
$clsOrders = new clsOrders();
$cart = $clsOrders->getCartt($store_id);
if (!$cart) { return error("Cart not found:$store_id. Please report this problem."); }
$queries = array();

if ($ctg_id==29) {
//    if (!$mrp) { $mQuery = "";} else {$mQuery=", MRP=$mrp"; }
    if (!$remarks) { $remarkQ = "";} else { $remarks = $db->safe($remarks); $remarkQ=", remarks=$remarks"; }
    
    $exist=$db->fetchObject("select o.id  from it_ck_orderitems o , it_items i where o.item_id = i.id and o.order_id=$cart->id and o.design_no=$design_no and i.id=$item_id");
    $query = "select o.id,i.curr_qty from it_ck_orderitems o, it_items i  where o.item_id = i.id and o.order_id=$cart->id and o.design_no=$design_no";
    $stock = $db->fetchObject("select i.curr_qty as avlqty from it_items i where i.id = $item_id");
    $query2 ="select i.curr_qty from it_items i where i.id = $item_id";
        if( $quantity < 0){
            $errors .= "Value cannot be negative for quantity. <br/>";
            return error($errors);
        }else{}
  
        try{
            $query = "insert into it_ck_orderitems set order_id=$cart->id,store_id = $store_id,item_id = $item_id,design_no=$design_no ,MRP = $mrp, order_qty = $quantity $remarkQ";
            if ($exist && !$remarks) { 
                $query = "delete from it_ck_orderitems where id=$exist->id"; 
                $db->execQuery($query);
            }else if ($exist){ 
                $query = "update it_ck_orderitems set order_qty = $quantity $remarkQ, updatetime=now() where id=$exist->id";
                $db->execQuery($query);
            }else{             
                $query = "insert into it_ck_orderitems set order_id=$cart->id,store_id = $store_id,item_id = $item_id,design_no=$design_no ,MRP = $mrp, order_qty = $quantity $remarkQ"; 
                $db->execInsert($query);
            }
        }catch(Exception $xcp){
            $errors = $xcp->getMessage();
        }
} 
//return error(join("<br />", $queries));
//foreach ($queries as $query) {
//    try{
//        $db->execQuery($query);
//    }catch(Exception $xcp){
//        $errors = $xcp->getMessage();
//    }
//}

if ($errors != "")
{ return error($errors); }
// update the cart with the totals
$query = "select sum(oi.order_qty) as tot_qty, sum(oi.order_qty * oi.MRP) as tot_amt, count(distinct(oi.design_no)) as num_designs from it_ck_orderitems oi, it_items i where oi.order_id=$cart->id and oi.item_id = i.id and i.ctg_id != 29";

$cartinfo = $db->fetchObject($query);
if ($cartinfo && $cartinfo->tot_qty  && $cartinfo->num_designs) {
$cartinfoStr = "Order No: $cart->order_no | Quantity: $cartinfo->tot_qty | Amount: $cartinfo->tot_amt";
$tot_qty = $cartinfo->tot_qty;
$tot_amt = $cartinfo->tot_amt;
} else {
$cartinfoStr = "Order No: $cart->order_no | Quantity: 0 | Amount: 0";
}

if ($remarks || (isset($quantity) && ($quantity> 0))) { return success("Items in cart updated", $cartinfoStr,$tot_qty,$tot_amt); }
else { return success("All items removed", $cartinfoStr,$tot_qty,$tot_amt); }

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
?>
