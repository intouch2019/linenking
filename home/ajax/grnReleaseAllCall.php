<?php
//@set_magic_quotes_runtime(false);
//ini_set('magic_quotes_runtime', 0);
print "here";


require_once "../../it_config.php";

require_once "lib/db/DBConn.php";

require_once "lib/logger/clsLogger.php";

require_once "lib/core/Constants.php";

require_once 'lib/users/clsUsers.php';

require_once "session_check.php";

require_once "lib/logger/clsLogger.php";

require_once "lib/grnPDFClass/GeneratePDF.php";

extract($_GET);
//return;
//exit;
print_r($_GET);
//return;
//exit;

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
$items = array();
$items_with_balance = array();
if(! isset($_SESSION['items_with_balance'])){
    $_SESSION['items_with_balance'] = array();
}

if(!isset($_SESSION['store_orders'])){
    $_SESSION['store_orders'] = array();
}

foreach ($_GET as $name => $value) {
    if ($value && startsWith($name, "item_")) {
        $arr = explode("_", $name);
        $items[] = (object) array(
            "item_id" => $arr[1],
            "grn_qty" => $arr[2],
            "to_release_qty" => $value
        );
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
//print "<br>ITEMS ARR: <br>";
//print_r($items);

$ctg_id = isset($_GET['ctg_id']) ? $_GET['ctg_id'] : false;
$design_id = isset($_GET['design_id']) ? $_GET['design_id'] : false;
$design_active = isset($_GET['design_active'])  ? $_GET['design_active'] : false;
$mrp = isset($_GET['mrp']) ? $_GET['mrp'] : false;
$cdesp = isset($_GET['cdesp']) ? $_GET['cdesp'] : false;
$_SESSION['totForms'] = $_GET['totForms'];
$form_no = $_GET['form_no'];
$place_ord = isset($_GET['placeOrd']) ? $_GET['placeOrd'] : false;
print "<br>SEESION ARR: <br>";
print_r($_SESSION);

try {   
    $clsLogger = new clsLogger();
    // fetch all auto refill stores
    $squery = "select * from  it_codes where usertype = ".UserType::Dealer." and is_autorefill = 1 and is_closed = 0 and inactive = 0 and sbstock_active = 1 and sequence is not null and sequence > 0 order by sequence ";
//   $sresults = $db->execQuery($squery);
    print "<br>Store QRY: $squery ";
    $storeobjs = $db->fetchObjectArray($squery);
    print_r($storeobjs);
//    if(mysql_num_rows($sresult)==0){ $msg .= "Store Sequence not set so no orders placed.";}
    
    if(isset($cdesp) && trim($cdesp) != "" && trim($cdesp)!= "-1"){
        $cdesp_db = $db->safe(trim($cdesp));
        $sqry = "select * from it_grn_ctg_desp where ctg_id = $ctg_id and design_id = $design_id ";
        $sgobj = $db->fetchObject($sqry);
        if($sgobj){
           $uqry = "update it_grn_ctg_desp set cdesp = $cdesp_db where id = $sgobj->id "; 
           $db->execUpdate($uqry);
        }else{
            $insqry =  "insert into it_grn_ctg_desp set ctg_id = $ctg_id , design_id = $design_id ,cdesp = $cdesp_db, createtime = now() ";
//            print "<br> C DESP QRY: $insqry ";
            $db->execInsert($insqry);
        }
    }
    
    if(trim($design_active)==0){ // means design is inactive so activate it.
       //so activate it
        $duqry = "update it_ck_designs set active = 1 where id = $design_id ";
        $db->execUpdate($duqry);
    }
    
    foreach ($items as $item) {  
        $multiple_grp_flag = 0;
        $currCheck = "";
        $item_id = $item->item_id;
        $grn_qty = $item->grn_qty;
        $to_release_qty = $item->to_release_qty;
        $release_bal_qty = $to_release_qty;
        if($grn_qty > 0 && $to_release_qty > 0 && $to_release_qty <= $grn_qty){
            $iqry = "select * from it_items where id = $item->item_id ";
            print "<br>ITEMS DETAILS: $iqry ";
            $iobj = $db->fetchObject($iqry);
            print "<br>";
            print_r($iobj);
            if(isset($iobj)){
                    $barcode_db = $db->safe(trim($iobj->barcode));
                    if($iobj->grn_qty < $to_release_qty){
                        $multiple_grp_flag = 1; // means release qty is sum of same group barcodes grn_qty
                        grnItemsBal($iobj,$to_release_qty);
                        $currCheck = ""; 
                    }else{
                       $multiple_grp_flag = 0; // means individual item in the same barcode grp has grn_qty , others grn_qty is not released
                       $query = "update it_items set grn_qty = grn_qty - $to_release_qty , curr_qty = curr_qty + $to_release_qty where id = $item_id ";                       
                       print "<br>UPDATE ITM QRY ELSE CASE: $query ";
                       $currCheck = " && $iobj->curr_qty > 0 ";
                       print "<br> CURR CHK: ".$currCheck;
//                      error_log("grnr qry: $query",3,"tmp.txt");
                       print "<br>$query<br>";
                        //--> code to log it_items update track
                       $ipaddr =  $_SERVER['REMOTE_ADDR'];
                       $pg_name = __FILE__;                
                       $clsLogger->logInfo($query,false, $pg_name,$ipaddr);
                       //--> log code ends here
                       $db->execUpdate($query);
                       $msg = " GRN Released";
                    }   


            if(trim($place_ord)==1){
                    foreach($storeobjs as $sobj){
                       if(isset($sobj)){
                           // step 1 : fetch store items details
                           $iquery = "select sum(quantity) as quantity from  it_current_stock where store_id = $sobj->id  and ctg_id = $iobj->ctg_id and design_id = $iobj->design_id and style_id = $iobj->style_id and size_id = $iobj->size_id ";
                           print "<br><br>STORE CURR ITM QRY: $iquery<br><br>";
                           $siobj = $db->fetchObject($iquery);
                           if(isset($siobj->quantity)){
                               if(trim($siobj->quantity)==""){ $store_item_curr_qty = 0;  }else{ $store_item_curr_qty = $siobj->quantity; }                             
                           }else{
                              $store_item_curr_qty = 0;  
                           }
                           
                           $intransit_stock_value=0;
                           $tquery2 = "select sum(oi.quantity) as intransit_stock_value from it_invoices o , it_invoice_items oi , it_items i where oi.invoice_id = o.id and o.invoice_type in ( 0 , 6 ) and o.store_id = $sobj->id and o.is_procsdForRetail = 0 and oi.item_code = i.barcode and oi.item_code in (select barcode from it_items where ctg_id = $iobj->ctg_id and design_id = $iobj->design_id and style_id = $iobj->style_id and size_id = $iobj->size_id  ) "; //= i.barcode and i.barcode = $barcode_db ";
                                 print "<br>".$tquery2;
                           $obs = $db->fetchObject($tquery2);                           

                            if(isset($obs)){
                               if(trim($obs->intransit_stock_value)==""){ $intransit_stock_value = 0 ;}else{ 
                                $intransit_stock_value = $obs->intransit_stock_value;
                               }
                            }
                           //fetch store's standing stock ratio againts item
                          // $squery = "select * from it_store_ratios where store_id = $sobj->id and ctg_id = $iobj->ctg_id and design_id = $iobj->design_id and style_id = $iobj->style_id and size_id = $iobj->size_id and mrp = $iobj->MRP and ratio_type = ".RatioType::Standing ;
                           $squery = "select * from it_store_ratios where store_id = $sobj->id and ctg_id = $iobj->ctg_id and $iobj->design_id = $design_id and style_id = $iobj->style_id and size_id = $iobj->size_id and ratio_type = ".RatioType::Standing ;
                           print "<br> STORE STANDING STOCK QRY: $squery";
                           $stkobj = $db->fetchObject($squery);
                           
                           if(!isset($stkobj)){
                                $squery = "select * from it_store_ratios where store_id = $sobj->id and ctg_id = $iobj->ctg_id "
                                        . "and $iobj->design_id = -1 and style_id = $iobj->style_id and size_id = $iobj->size_id and ratio_type = ".RatioType::Standing ;
                                print "<br> STORE STANDING STOCK QRY: $squery";
                                $stkobj = $db->fetchObject($squery);
                           }
                           
                           $sum_qty = $store_item_curr_qty + $intransit_stock_value;
                                   
                           if(isset($stkobj) &&  $release_bal_qty > 0  && isset($siobj) .$currCheck ){ 
       //                                        print "<br>STK RATIO: ".$stkobj->ratio;
//                               if(trim($siobj->quantity) < 0){
//                                   $store_current_qty = 0;
//                               }else{
//                                   $store_current_qty = $siobj->quantity;
//                               }
                            //error_log("\nSTORE ITM QTY: ".$store_item_curr_qty,3,"tmp.txt");
                           // $store_item_curr_qty = $siobj->quantity;
                            //if($store_item_curr_qty<=0){
                            if($sum_qty<=0){   
                               $qty = $stkobj->ratio  ; 
                            }else{
                                //if($store_item_curr_qty < $stkobj->ratio){
                                if($sum_qty < $stkobj->ratio){
                                   //$qty = $stkobj->ratio - $store_item_curr_qty; 
                                    $qty = $stkobj->ratio - $sum_qty; 
                                }else{
                                    $qty = 0; // no need to place the order for this item
                                }
                            }
                               
                               
                            if( $qty >= $release_bal_qty  ){
                                $qty = $release_bal_qty;
                            }
                            
//                            else{
//
//                                $qty = $stkobj->ratio;
//                            }
       //                                        print "<br>RELEASE QTY 1st: $qty";
       //                                        print "<br>CURR QTY: $iobj->curr_qty";
//                            if($qty > $iobj->curr_qty){
//                                $qty = $iobj->curr_qty;
//                            }
       //                                        print "<br>QTY to fn: $qty";
                            if($qty>0){
                                /*if(array_key_exists($sobj->id, $store_orders)){
                                    
                                    $order_id = $store_orders[$sobj->id];
        //                                            print "<br><br>ORDER_ID: ".$order_id;
                                    $release_bal_qty = insertItems($sobj,$qty,$iobj,$order_id, $release_bal_qty);
                                    //print "release_bal_qty<br/>";
                                }else{
                                    //print "place order<br/>";
                                    $release_bal_qty = orderCreate($sobj,$qty,$store_orders,$iobj,$release_bal_qty);
                                }*/
                                
                                if(array_key_exists($sobj->id, $_SESSION['store_orders'])){
                                    
                                    //$order_id = $store_orders[$sobj->id];
                                    $order_id = $_SESSION['store_orders'][$sobj->id];
                                    
        //                                            print "<br><br>ORDER_ID: ".$order_id;
                                    $release_bal_qty = insertItems($sobj,$qty,$iobj,$order_id, $release_bal_qty);
                                    //print "release_bal_qty<br/>";
                                }else{
                                    //print "place order<br/>";
                                    $release_bal_qty = orderCreate($sobj,$qty,$store_orders,$iobj,$release_bal_qty);
                                }
                                
                            }
       //                                        print "<br>RELEASE QTY RESP: $release_bal_qty";
                           }
                       }
                       if($release_bal_qty == 0){
                           break;
                       }
                   }//foreach store loop ends
            }
                   //if release qty > 0 means qty remained even after order is placed
                   //$items_with_balance[$item->item_id] = $release_bal_qty;
                   if($release_bal_qty > 0){
                        array_push($items_with_balance,$item->item_id );
                        $_SESSION['items_with_balance'] = $items_with_balance;
                        //array_push($_SESSION['$items_with_balance'],$item->item_id );
                   }
                    print "<br>AFTER SESSION ARR: <br>";
                    print_r($_SESSION);
        }
        }
    }// items loop end
    
    $cnt=0;
       //set orders tot_qty , tot_amt , num_designs

    //code added for store order placing   
    if(trim($place_ord)==1){    
//        echo json_encode(array("error"=>"0","message" => "here place order = 1"));
           if($_SESSION['totForms'] == trim($form_no)){ // means last form is called        
  //             echo json_encode(array("error"=>"0","message" => "Total Form Check"));
                foreach($_SESSION['store_orders'] as $store_id => $order_id){
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
                unset($_SESSION['store_orders']);                
           }
    }

    //added code end

    
    // if grn release balance is there then call pdf creation code
       if(!empty($items_with_balance)){
//           print "<br>IN ITM BAL 1 b";
           if($_SESSION['totForms'] == trim($form_no)){ // means last form is called
//           print "<br>IN ITM BAL 1";
           // $clsGRNPDF = new GeneratePDF(); -- yuvaraj commented

               
            
             // $clsGRNPDF->genUnreleasedPDF($_SESSION['items_with_balance']); -- yuvaraj commented
            unset($_SESSION['totForms']);
            unset($_SESSION['items_with_balance']);
           }
       }
       
//       print "<br>AFTER SESSION ARR: <br>";
//       print_r($_SESSION);
       
$db->closeConnection();

} catch (Exception $xcp) {
    echo json_encode(array("error"=>"1","message" => "problem in grn release"));

}
//echo "Message : ".$msg;

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
//        print "<br><br>ORDER QRY: $q";
        $order_id = $db->execInsert($q);
        $store_orders[$sobj->id] = $order_id;
        
        $_SESSION['store_orders'][$sobj->id] = $order_id;
        
        //insert item lines
        $design_no_db = $db->safe(trim($iobj->design_no));
        
        $iquery = "insert into it_ck_orderitems set order_id = $order_id , store_id = $sobj->id , item_id = $iobj->id , design_no = $design_no_db, MRP = $iobj->MRP , order_qty = $qty  , createtime = now()  ";
//        print "<br><br>INSERT ORDER ITM: $iquery";
        $db->execInsert($iquery);
        
        //update it-items curr_qty
        $query = "update it_items set updatetime=now(),curr_qty=curr_qty - ".$qty." where id=$iobj->id";
//        print "<br><br>UPDATE ITEM QTY: $query";
         //--> code to log it_items update track
                $ipaddr =  $_SERVER['REMOTE_ADDR'];
                $pg_name = __FILE__;                
                $clsLogger->logInfo($query,false, $pg_name,$ipaddr);
         //--> log code ends here
        $db->execUpdate($query);
        
        $release_bal_qty = $release_bal_qty - $qty;
        
//        print "<br><br>IN FN SARR: ";
//        print_r($store_orders);
        $db->closeConnection();
        return $release_bal_qty;
    }
    
    return $release_bal_qty;
   
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
    $db->closeConnection();
    return $release_bal_qty;
}


function grnItemsBal($iobj,$to_release_qty){
   print "<br>IN GRN ITEM BAL: <br>";
   $db = new DBConn(); 
   $clsLogger = new clsLogger();
   
   $query = "select * from it_items where ctg_id = $iobj->ctg_id and design_id = $iobj->design_id and style_id = $iobj->style_id and size_id = $iobj->size_id and grn_qty > 0 ";
   print "<br>ITM QRY: ".$query;
   $objs = $db->fetchObjectArray($query);
   $release_balance = $to_release_qty;
   foreach($objs as $obj){
       if($release_balance > 0){
            if($obj->grn_qty <= $release_balance){
                $qty = $obj->grn_qty;
            }else{
                $qty = $release_balance;
            }
            
            $query = "update it_items set grn_qty = grn_qty - $qty , curr_qty = curr_qty + $qty where id = $obj->id ";                                   
//               error_log("grnr qry: $query",3,"tmp.txt");
//            print "<br>UPDATE QRY: $query<br>";
             //--> code to log it_items update track
            $ipaddr =  $_SERVER['REMOTE_ADDR'];
            $pg_name = __FILE__;                
            $clsLogger->logInfo($query,false, $pg_name,$ipaddr);
            //--> log code ends here
            $db->execUpdate($query);
           
            $release_balance = $release_balance-$qty;
            
       }else{
           break;
       }
       
   }
   $db->closeConnection();
}

