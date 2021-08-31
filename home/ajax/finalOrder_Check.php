<?php
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
require_once "lib/orders/clsOrders.php";
require_once ("session_check.php");
require_once "lib/logger/clsLogger.php";

$db=new DBConn();

//$str = getCurrUser();
$store_id = getCurrUserId();       
       
$clsOrders = new clsOrders();
$clsLogger = new clsLogger();
$cart = $clsOrders->getCartt($store_id);
//print("Cart=".print_r($cart,true));
//return; //hi
           
//$redirect="store/orders/active";
$cnt=0;
if ($cart) {   
//$query="select * from it_ck_orderitems where order_id=$cart->id";
//$orderitems=$db->fetchObjectArray("select * from it_ck_orderitems where order_id=$cart->id");
$orderitems=$db->fetchObjectArray("select id,item_id,order_qty,store_id from it_ck_orderitems where order_id=$cart->id");
if (count($orderitems) == 0) { 
   // print "Failed - no orderitems found [$query]"; 
    print "1";
    return;
    
}
 
$design_no1="";
$design_no="";
foreach ($orderitems as $ord)
    {
//    $query11="select * from it_items where curr_qty>=$ord->order_qty and id=$ord->item_id and ctg_id!=29";
    $query11="select id,curr_qty,ctg_id from it_items where curr_qty>=$ord->order_qty and id=$ord->item_id and ctg_id!=29";
        // print $query;
        //error_log("\n|JSON it_items:$query11 ",3,"tmp.txt");
    $chk=$db->fetchObject($query11);
    if (!isset($chk)) {
         $query="select c.name as ctg_name,i.design_no,i.MRP,st.name as style_name,sz.name as size_name from it_items i,it_categories c,it_styles st,it_sizes sz where c.id=i.ctg_id and st.id=i.style_id and sz.id=i.size_id and i.id=$ord->item_id  and i.ctg_id != 29";
        $result1=$db->fetchObject($query);
        if (isset($result1)) 
        {
            
            $design_no.=$result1->design_no.",";
            
           // $design_no=$result1->design_no;
            $orderid=$ord->id;
            $store_id=$ord->store_id; 
            $item_id=$ord->item_id;
            
            
          //  deletecart($orderid,$store_id,$item_id);
            
              $db=new DBConn();
              $query = "delete from it_ck_orderitems where id=$orderid and store_id=$store_id and item_id=$item_id";
             // error_log("\n|JSON delete Query for drop is:$query ",3,"tmp.txt");
              $db->execQuery($query);
            
              //$clsOrders->updateCartTotals($cart->id); //update cart
              
                $query = "select sum(order_qty) as tot_qty, sum(order_qty * MRP) as tot_amt, count(distinct(design_no)) as num_designs from it_ck_orderitems where order_id=$cart->id";
		//error_log("\n|JSON select Query After  delete items:$query ",3,"tmp.txt");
                $obj = $db->fetchObject($query);
		$tot_qty = 0; $tot_amt = 0; $num_designs = 0;
		if ($obj && $obj->tot_qty && $obj->tot_amt && $obj->num_designs) {
			$tot_qty = $obj->tot_qty;
			$tot_amt = $obj->tot_amt;
			$num_designs = $obj->num_designs;
		}
		$query = "update it_ck_orders set order_qty=$tot_qty, order_amount=$tot_amt, num_designs=$num_designs where id=$cart->id";
		//error_log("\n|JSON update after drop is:$query ",3,"tmp.txt");
                $db->execUpdate($query);
                
              $cnt++;
              
        }
        
       
       }
    
    }
    
$design_no1=substr($design_no, 0, -1);

//error_log("\n|JSON design no:$design_no1 ",3,"tmp.txt"); ///var/www/tyzer_new_y/home/ajax/tmp.txt 
//error_log("\n|JSON count is:$cnt ",3,"tmp.txt"); ///var/www/tyzer_new_y/home/ajax/tmp.txt 
   
//$orderitems_check=$db->fetchObjectArray("select * from it_ck_orderitems where order_id=$cart->id");
$orderitems_check=$db->fetchObjectArray("select order_qty,item_id from it_ck_orderitems where order_id=$cart->id");
//$query_check="select * from it_ck_orderitems where order_id=$cart->id";
if (count($orderitems_check) == 0) {
    //print "Failed - no orderitems found [$query]"; return; 
    //print "Failed - no orderitems found,Your order are not process because  Following Designs are Dropped  Before Confirming the Orders [$design_no"; 
    
    print "2[$design_no1";
    return; 
}

// error_log("\n|JSON last Query to check:$query_check ",3,"tmp_1.txt");

foreach ($orderitems_check as $ord)
{
       
               $query = "update it_items set updatetime=now(),curr_qty=curr_qty - ".$ord->order_qty." where id=$ord->item_id";
               $ipaddr =  $_SERVER['REMOTE_ADDR'];
                $pg_name = __FILE__;                
                $clsLogger->logInfo($query,false, $pg_name,$ipaddr);
                //--> log code ends here.//
                $db->execUpdate($query);

}   
//$query = "select sum(order_qty) as tot_qty, sum(order_qty * MRP) as tot_amt, count(distinct(design_no)) as num_designs from it_ck_orderitems where order_id=$cart->id";
$query = "select sum(oi.order_qty) as tot_qty, sum(oi.order_qty * oi.MRP) as tot_amt, count(distinct(oi.design_no)) as num_designs from it_ck_orderitems oi, it_items i where oi.order_id=$cart->id and oi.item_id = i.id and i.ctg_id != 29";
$obj = $db->fetchObject($query);
$cartinfo = "";
if ($obj) {
$cartinfo = ", order_qty=$obj->tot_qty, order_amount=$obj->tot_amt, num_designs=$obj->num_designs";
}
$query="update it_ck_orders set status=1,active_time=now() $cartinfo where id=$cart->id";
//print $query."<br/>";
$db->execUpdate($query);

} 



if( $cnt==0){
    ////print "Order Placed Succesfully";
     print "3[$design_no1";
}
else{//   msg = "The entire Qty in the following Design No. ("+$dat[1]+") has been dropped as another Store has successfully placed order before you. Check for left over inventory and place order again if available";
                                         
    $ack_msg_drop_design="The entire Qty in the following Design No.($design_no) has been dropped as another Store has successfully placed order before you. Check for left over inventory and place order again if available";
   // print "Your order placed succesfully But Following Designs are Dropped  Before Confirming the Orders [$design_no]";////
    $query = "update it_ck_orders set  msl_ack_text = '$ack_msg_drop_design' where id=$cart->id ";
  //    print $query;
    $db->execUpdate($query);
    print "4[$design_no1";
    
}






?>