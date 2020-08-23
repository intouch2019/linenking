<?php
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
require_once "lib/logger/clsLogger.php";
require_once "lib/core/Constants.php";
require_once 'lib/users/clsUsers.php';
require_once ("session_check.php");
require_once "lib/logger/clsLogger.php";

extract($_GET);
$cnt = 1;
$ocnt=0;
$errors=array();
$success=array();
$db = new DBConn();
$msg="";

$user = getCurrUser();   
$user_id = $user->id;

$userpage = new clsUsers();
$pagecode = $db->safe($_SESSION['pagecode']);
$page = $db->fetchObject("select * from it_pages where pagecode = $pagecode");
if($page){
    $allowed = $userpage->isAuthorized($user_id, $page->pagecode);
    if (!$allowed) { header("Location: ".DEF_SITEURL."unauthorized"); return; }
}else{ header("Location:".DEF_SITEURL."nopagefound"); return; }
        

$items_sel_chk = array();
$items_grn_qty = array(); // grn release qtys
$store_orders = array(); //store orders information
foreach ($_GET as $name => $value) {
//    print "<br>NM:$name=>value:$value";
    if (startsWith($name, "Sel_")) {
        $arr = explode("_", $name);
        //arr 1st position is item id
        //value contains selected status
        $items_sel_chk[$arr[1]] = $value;
    }
    
    if (startsWith($name, "grn_")) {
//        print "<br>inside grn";
        $arr2 = explode("_", $name);
        //arr 1st position is item id
        //arr 2nd position contains grn_qty
        //value contains release qty
        $items_grn_qty[$arr2[1]] = $arr2[2]."::".$value;
    }
    
}




try {   
    $clsLogger = new clsLogger();
    // fetch all auto refill stores
    $squery = "select * from  it_codes where usertype = ".UserType::Dealer." and is_autorefill = 1 and is_closed = 0 and inactive = 0 and sbstock_active = 1 and sequence is not null order by sequence ";
//    $sresults = $db->execQuery($squery);
    $storeobjs = $db->fetchObjectArray($squery);
//    if(mysql_num_rows($sresult)==0){ $msg .= "Store Sequence not set so no orders placed.";}
    if(! empty($items_grn_qty)){
       //to update grn release effect
       foreach($items_grn_qty as $item_id => $value){
           //step 1 : check if release qty +ve
//           if($value > 0){
              //step 2 : check if its select is checked
               if(array_key_exists($item_id, $items_sel_chk)){
                  $sel_status = $items_sel_chk[$item_id]; 
                  //step 2.1 : if chk selected then do updates
                  if($sel_status){
                      //do updates
                      $varr = explode("::",$value);
                      $grn_qty = $varr[0];
                      $to_release_qty = $varr[1];
                      $release_bal_qty = $to_release_qty;
                      if($grn_qty > 0 && $to_release_qty > 0 && $to_release_qty <= $grn_qty){
                            //step 3: Deduct release qty from grn_qty n add release qty to curr_qty
                            $query = "update it_items set grn_qty = grn_qty - $to_release_qty , curr_qty = curr_qty + $to_release_qty where id = $item_id ";
//                            error_log("grnr qry: $query",3,"tmp.txt");
                            //print "<br>$query<br>";
                             //--> code to log it_items update track
                                $ipaddr =  $_SERVER['REMOTE_ADDR'];
                                $pg_name = __FILE__;                
                                $clsLogger->logInfo($query,false, $pg_name,$ipaddr);
                                //--> log code ends here
                            $db->execUpdate($query);
                            $msg = " GRN Released";
                          
                                              //stores orders
                   // while($sobj = $sresults->fetch_object()){
                            foreach($storeobjs as $sobj){
                                if(isset($sobj)){
                                    // step 1 : fetch items details
                                    $iquery = "select * from it_items where id = $item_id";
                                   // print "<br><br>ITM LOOP QRY: $iquery<br><br>";
                                    $iobj = $db->fetchObject($iquery);

                                    //fetch store's standing stock ratio againts item
                                   // $squery = "select * from it_store_ratios where store_id = $sobj->id and ctg_id = $iobj->ctg_id and design_id = $iobj->design_id and style_id = $iobj->style_id and size_id = $iobj->size_id and mrp = $iobj->MRP and ratio_type = ".RatioType::Standing ;
                                     $squery = "select * from it_store_ratios where store_id = $sobj->id and ctg_id = $iobj->ctg_id and style_id = $iobj->style_id and size_id = $iobj->size_id and ratio_type = ".RatioType::Standing ;
//                                     print "<br>$squery";
                                    $stkobj = $db->fetchObject($squery);
                                    if(isset($stkobj) &&  $release_bal_qty > 0 && $iobj->curr_qty > 0){ 
//                                        print "<br>STK RATIO: ".$stkobj->ratio;
                                        if( $stkobj->ratio >= $release_bal_qty  ){
                                            $qty = $release_bal_qty;
                                        }else{

                                            $qty = $stkobj->ratio;
                                        }
//                                        print "<br>RELEASE QTY 1st: $qty";
//                                        print "<br>CURR QTY: $iobj->curr_qty";
                                        if($qty > $iobj->curr_qty){
                                            $qty = $iobj->curr_qty;
                                        }
//                                        print "<br>QTY to fn: $qty";
                                        if(array_key_exists($sobj->id, $store_orders)){
                                            $order_id = $store_orders[$sobj->id];
//                                            print "<br><br>ORDER_ID: ".$order_id;
                                            $release_bal_qty = insertItems($sobj,$qty,$iobj,$order_id, $release_bal_qty);
                                        }else{
                                            $release_bal_qty = orderCreate($sobj,$qty,$store_orders,$iobj,$release_bal_qty);
                                        }
//                                        print "<br>RELEASE QTY RESP: $release_bal_qty";
                                    }
                                }
                                if($release_bal_qty == 0){
                                    break;
                                }
                            }

                      }
                  }
                 
               }

           //}
//           {
//               $msg = "GRN will not be release in case where qty is 0";
//           }
       } 
       $cnt=0;
       //set orders tot_qty , tot_amt , num_designs
        foreach($store_orders as $store_id => $order_id){
                $query = "select sum(oi.order_qty) as tot_qty, sum(oi.order_qty * oi.MRP) as tot_amt, count(distinct(oi.design_no)) as num_designs from it_ck_orderitems oi, it_items i where oi.order_id=$order_id and oi.item_id = i.id and i.ctg_id != 21";
                //return error($query);
                $orderobj = $db->fetchObject($query);
                if ($orderobj && $orderobj->tot_qty && $orderobj->tot_amt && $orderobj->num_designs) {
                    $query="update it_ck_orders set order_qty=$orderobj->tot_qty, order_amount=$orderobj->tot_amt, num_designs=$orderobj->num_designs where id=$order_id";
                    $db->execUpdate($query);

                } else {
                    $query="update it_ck_orders set order_qty=0, order_amount=0, num_designs=$orderobj->num_designs where id=$order_id and store_id=$store_id";
                    $db->execUpdate($query);

                }
                $cnt++;
        }
       $msg .= "\nTotal $cnt new order(s) created";
    }else{
        $msg = "No barcode Selected. Please select and then click save";
    } 
} catch (Exception $xcp) {
    echo json_encode(array("error"=>"1","message" => "problem in grn release"));

}

echo json_encode(array("error"=>"0","message" => $msg));

function startsWith($haystack, $needle)
{
    $length = strlen($needle);
    return (substr($haystack, 0, $length) === $needle);
}

function orderCreate($sobj,$qty,&$store_orders,$iobj,$release_bal_qty){
    $db = new DBConn();
    $clsLogger = new clsLogger();
    
    $store_number = $sobj->store_number;
    //insert new order. 
    if (isset($store_number) && trim($store_number)!="") {      
        $obj = $db->fetchObject("select order_no from it_ck_orders where store_id=$sobj->id order by id desc limit 1");
        $new_order_no = 1;
        if ($obj) {
            $new_order_no = intval(substr($obj->order_no, -3)) + 1;
        }
        if ($new_order_no == 1000) {
            $new_order_no = 1;
        }
        $order_no = $db->safe(sprintf("AT%03d%03d", $store_number, $new_order_no));
        $storequery = "select c.id as store_id,c.code,c.store_name,c.tally_name,c.accountinfo,c.owner,c.address,c.city,c.zipcode,c.phone,c.phone2,c.email,c.email2,c.vat,c.store_number,c.usertype,c.tax_type,c.server_change_id,c.username,c.a1hash,c.trust,c.password,c.createtime,c.inactive,d.dealer_discount,d.additional_discount,d.transport,d.octroi,d.cash,d.nonclaim from it_codes c left outer join it_ck_storediscount d on c.id = d.store_id where c.id = ".$sobj->id;
        $storeobj = $db->fetchObject($storequery);
        $json_str = $db->safe(json_encode($storeobj));
        $q = "insert into it_ck_orders set store_id=$sobj->id, status=" . OrderStatus::Active . ", order_no=$order_no, order_qty=0 , store_info = $json_str , active_time = now() ";
        //print "<br><br>ORDER QRY: $q";
        $order_id = $db->execInsert($q);
        $store_orders[$sobj->id] = $order_id;
        
        //insert item lines
        $design_no_db = $db->safe(trim($iobj->design_no));
        
        $iquery = "insert into it_ck_orderitems set order_id = $order_id , store_id = $sobj->id , item_id = $iobj->id , design_no = $design_no_db, MRP = $iobj->MRP , order_qty = $qty  , createtime = now()  ";
        //print "<br><br>INSERT ORDER ITM: $iquery";
        $db->execInsert($iquery);
        
        //update it-items curr_qty
        $query = "update it_items set updatetime=now(),curr_qty=curr_qty - ".$qty." where id=$iobj->id";
        //print "<br><br>UPDATE ITEM QTY: $query";
         //--> code to log it_items update track
                $ipaddr =  $_SERVER['REMOTE_ADDR'];
                $pg_name = __FILE__;                
                $clsLogger->logInfo($query,false, $pg_name,$ipaddr);
         //--> log code ends here
        $db->execUpdate($query);
        
        $release_bal_qty = $release_bal_qty - $qty;
        
//        print "<br><br>IN FN SARR: ";
//        print_r($store_orders);
        
        return $release_bal_qty;
    }
   
    $db->closeConnection();
}


function insertItems($sobj,$qty,$iobj,$order_id,$release_bal_qty){
    $db = new DBConn();
    $clsLogger = new clsLogger();
    
    //step 1 fetch order details
    $oquery = "select * from it_ck_orders where id = $order_id ";
    $oobj = $oquery;
//    $order_no_db = $db->safe(trim($oobj->order_no));
    
    //insert item lines
    $design_no_db = $db->safe(trim($iobj->design_no));
    $iquery = "insert into it_ck_orderitems set order_id = $order_id , store_id = $sobj->id , item_id = $iobj->id , design_no = $design_no_db, MRP = $iobj->MRP , order_qty = $qty , createtime = now() ";
//    print "<br><br>INSERT ITM CASE 2 : $iquery <br>";
    $db->execInsert($iquery);
    
    //update it-items curr_qty
    $query = "update it_items set updatetime=now(),curr_qty=curr_qty - ".$qty." where id=$iobj->id";
//    print "<br><br>UPDATE ITM CASE 2 : $query <br>";
     //--> code to log it_items update track
                $ipaddr =  $_SERVER['REMOTE_ADDR'];
                $pg_name = __FILE__;                
                $clsLogger->logInfo($query,false, $pg_name,$ipaddr);
     //--> log code ends here
    $db->execUpdate($query);
    
    $release_bal_qty = $release_bal_qty - $qty;
    return $release_bal_qty;
}