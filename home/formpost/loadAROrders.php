<?php

require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once 'lib/users/clsUsers.php';
require_once "lib/logger/clsLogger.php";

$db = new DBConn();
$currStore = getCurrUser();
$res = array();
global $storeid;

$userpage = new clsUsers();
$pagecode = $db->safe($_SESSION['pagecode']);
$page = $db->fetchObject("select * from it_pages where pagecode = $pagecode");
if($page){
    $allowed = $userpage->isAuthorized($currStore->id, $page->pagecode);
    if (!$allowed) { header("Location: ".DEF_SITEURL."unauthorized"); return; }
}else{ header("Location:".DEF_SITEURL."nopagefound"); return; }

extract($_POST);

/*
 * Only IT Admin or CK Admin should be allowed to do this. If some other user then throw an error.
 */

$storeid = $storesel;

try {
    $_SESSION['form_id'] = $form_id;
    $items = array();
    $itmcnt = 0;
    //step 1 : check if for that store sbstock feature is enabled
    $query = "select * from it_codes where id = $storeid";
//    error_log("\n LAR STORE CHK: $query ",3,"tmp.txt");
    $storeobj = $db->fetchObject($query);
    if(isset($storeobj)){
        if($storeobj->sbstock_active == 0){ // means old procedure
            $query = "Select oi.item_id as item_id,oi.barcode as barcode ,sum(oi.quantity) as qty from it_orders o,it_order_items oi where o.id = oi.order_id and o.store_id = oi.store_id and o.store_id = ".$storeid." and o.ck_order_id is null group by oi.item_id";
            $objs = $db->fetchObjectArray($query);

            foreach ($objs as $obj) {
                $itemid = $obj->item_id;
                 $qty = $obj->qty;
                            if (!isset($items[$itemid]))
                                $items[$itemid] = 0;
                                $items[$itemid] += $qty;
                                $itmcnt++;
                            }


            $res = saveOrder($storeid, $items);
            
        }else if($storeobj->sbstock_active == 1){ // means enabled standing/base stock feature
            //step 1 : check if base stock ratio is set against the store
            $query = "select * from it_store_ratios where store_id = $storeid ";
            //error_log("\nLAR : ENQUERY: $query ",3,"tmp.txt");
            $sobj = $db->fetchObject($query);
            if(! isset($sobj)){
              $errors[] = "Base Stock Ratio not placed ";  
            }

            if(count($errors) == 0){   
                //$query = "Select oi.item_id as item_id,oi.barcode as barcode ,sum(oi.quantity) as qty from it_orders o,it_order_items oi where o.id = oi.order_id and o.store_id = oi.store_id and o.store_id = ".$storeid." and o.ck_order_id is null group by oi.item_id";
                $query = "Select oi.item_id as item_id,oi.barcode as barcode,sum(oi.quantity) as qty from it_orders o,it_order_items oi,it_items i where o.id = oi.order_id and o.store_id = oi.store_id and o.store_id = $storeid and o.ck_order_id is null and oi.item_id = i.id group by  i.ctg_id,i.design_id,i.style_id,i.size_id "; //oi.item_id";
//                error_log("LAR ITM QRY: $query ",3,"tmp.txt");
                $objs = $db->fetchObjectArray($query);

                foreach ($objs as $obj) {
                    $itemid = $obj->item_id;
                    //$qty = $obj->qty;
                    // qty shld be difference between curr stock and base stock ratio against that item
                    $barcode_db = $db->safe(trim($obj->barcode));
                    $qry = "select c.*,i.* from it_current_stock c , it_items i where c.barcode = i.barcode and c.store_id = $storeid and c.barcode = $barcode_db ";
//                    error_log("\n LAR CURR ITMQUERY: $qry ",3,"tmp.txt");
                    $bobj = $db->fetchObject($qry);

                    if(isset($bobj)){
                        //fetch stores curr stock sum for same ctg,design,style,size
                        $q = "select sum(quantity) as quantity from it_current_stock where store_id = $storeid and ctg_id = $bobj->ctg_id and design_id = $bobj->design_id and style_id = $bobj->style_id and size_id = $bobj->size_id ";
                        //echo $q."<br>";
                        $scurrobj = $db->fetchObject($q);
                        if($scurrobj){
                            if($scurrobj->quantity == null || trim($scurrobj->quantity)==""){ $store_item_curr_qty = 0 ; }else{
                             $store_item_curr_qty = $scurrobj->quantity;
                            }
                        }else{
                            $store_item_curr_qty = 0;
                        }
                        //echo $q."<br>";
                        //echo $obj->barcode."==>".$store_item_curr_qty."<br>";
                         $intransit_stock_value=0;
                         //fetch base stock ratio against that item
                         //$bsquery = "select * from it_store_ratios where store_id = $storeid and ctg_id = $bobj->ctg_id and design_id = $bobj->design_id and style_id = $bobj->style_id and size_id = $bobj->size_id and mrp = $bobj->MRP and ratio_type = ".RatioType::Base;
                         $bsquery = "select * from it_store_ratios where store_id = $storeid and ctg_id = $bobj->ctg_id and design_id = $bobj->design_id and style_id = $bobj->style_id and size_id = $bobj->size_id  and ratio_type = ".RatioType::Base;
//                         error_log("\nBASIC TSK ITMQUERY: $bsquery ",3,"tmp.txt");
                       //  $tquery2 = "select sum(oi.quantity) as intransit_stock_value from it_invoices o , it_invoice_items oi , it_items i where oi.invoice_id = o.id and o.invoice_type in ( 0 , 6 ) and o.store_id = $storeid and o.is_procsdForRetail = 0 and oi.item_code = i.barcode and oi.item_code in (select barcode from it_items where ctg_id = $bobj->ctg_id and design_id = $bobj->design_id and style_id = $bobj->style_id and size_id = $bobj->size_id ) "; //= i.barcode and i.barcode = $barcode_db ";
                          $tquery2 = "select sum(oi.quantity) as intransit_stock_value from it_sp_invoices o , it_sp_invoice_items oi , it_items i where oi.invoice_id = o.id and o.invoice_type in ( 0 , 6 ) and o.store_id = $storeid and o.is_procsdForRetail = 0 and oi.barcode = i.barcode and i.ctg_id = $bobj->ctg_id and i.design_id = $bobj->design_id and i.style_id = $bobj->style_id and i.size_id = $bobj->size_id  "; //= i.barcode and i.barcode = $barcode_db ";
                         //echo $tquery2."<br>";
                          $obs = $db->fetchObject($tquery2);
                          if(isset($obs)){
                              if($obs->intransit_stock_value != null && trim($obs->intransit_stock_value) != ""){
                                $intransit_stock_value = $obs->intransit_stock_value;
                              }else{
                                  $intransit_stock_value = 0;
                              }
                          }
                          $bsobj = $db->fetchObject($bsquery);
                          if(!isset($bsobj)){
                                $bsquery = "select * from it_store_ratios where store_id = $storeid and ctg_id = $bobj->ctg_id "
                                        . "and design_id = -1 and style_id = $bobj->style_id and size_id = $bobj->size_id  and ratio_type = ".RatioType::Base;                              
                                //echo $bsquery."<br>";        
                                $bsobj = $db->fetchObject($bsquery);                                
                          }
                         if(isset($bsobj)){
                             
                            //$store_item_curr_qty = $bobj->quantity;
//                            error_log("\nSTORE ITM QTY: ".$store_item_curr_qty,3,"tmp.txt");
                            if($store_item_curr_qty<=0){
                                //echo "stock 0<br>";
                               //echo $obj->barcode."<br>";
                               $qty = $bsobj->ratio - $intransit_stock_value; 
                               //echo "Store qty <= 0 Ratio = ".$bsobj->ratio." In Transit = ".$intransit_stock_value." ".$obj->barcode."=".$qty."<br>";
                            }else{
                                if($store_item_curr_qty < $bsobj->ratio){
                                  //  echo $obj->barcode." stock < ratio <br>";
                                   //$qty = $bsobj->ratio - $store_item_curr_qty; 
                                    //echo $obj->barcode."==>". $store_item_curr_qty ." ". $intransit_stock_value."<br>";
                                    $qty = $bsobj->ratio - ($store_item_curr_qty + $intransit_stock_value);  
                                   // echo "Store qty < base ratio Ratio = ".  $bsobj->ratio ." Store_Item_qty = ".$store_item_curr_qty." and intransit = ".$intransit_stock_value." ".$obj->barcode."=".$qty."<br>";
                                }else{
                                    $qty = 0; // no need to place the order for this item
                                   // echo "Store qty > base ratio ".$obj->barcode."=".$qty."<br>";
                                }
                            }
//                            error_log("\nQTY: ".$qty,3,"tmp.txt");
                           if($qty > 0){ 
                                if (!isset($items[$itemid]))
                                    $items[$itemid] = 0;
                                if ($qty > 0) {
                                    $items[$itemid] += $qty;
                                    $itmcnt++;
                                }
                           }
                         } /*else{
                             echo $obj->barcode." no ratio found<br>";
                         }  */
                     }        
                }
                $res = saveOrder($storeid, $items);
            } 
       }
    }
    
        
} catch (Exception $xcp) {
    print $xcp->getMessage();
}

if (isset($res['err'])) {

    $_SESSION['form_errors'] = $res;
} else {

    $_SESSION['form_success'] = $res;
}
session_write_close();

header("Location: " . DEF_SITEURL . "admin/autorefill");
exit;

function saveOrder($storeid, $items) {

    $msg = "";
    $order_id = false;
    $db = new DBConn();

    $storeinfo = $db->fetchObject("select store_name,store_number from it_codes where id =$storeid");

    if (!$storeinfo) {
        $res['err'] = "ERROR:Store $storeinfo->store_name not found";
        return $res;
    }


    $num_item_codes = 0;
    $ordered_qty = 0;
    $avail_qty = 0;
    foreach ($items as $itemid => $qty) {
        $item_id = $itemid;
        if ($qty <= 0) {
            continue;
        }

        $num_item_codes++;
       // $dbobj = $db->fetchObject("select i.ctg_id, i.curr_qty from it_items i, it_ck_designs d where i.id=$item_id and i.ctg_id = d.ctg_id and i.design_no = d.design_no and d.active=1 and i.curr_qty > 0");
         $dbobj = $db->fetchObject("select i.ctg_id, i.curr_qty from it_items i, it_ck_designs d where i.id=$item_id and i.ctg_id = d.ctg_id and i.design_no = d.design_no and i.is_design_mrp_active=1 and i.curr_qty > 0");

        if (!$dbobj) {
            continue;
        }
        
        $ordered_qty += $qty;
        $avail_qty += $dbobj->curr_qty;
    }

    //if at least 1 qty exist ->continue and  create a new order in it_ck_orders
    if ($avail_qty > 0) {

        $store_number = $storeinfo->store_number;

        //insert new order. 
        if (!$store_number) {
            $res['err'] = "ERROR:Store number missing for store $storeinfo->store_name.";
            return $res;
        }
        $obj = $db->fetchObject("select order_no from it_ck_orders where store_id=$storeid order by id desc limit 1");
        $new_order_no = 1;
        if ($obj) {
            $new_order_no = intval(substr($obj->order_no, -3)) + 1;
        }
        if ($new_order_no == 1000) {
            $new_order_no = 1;
        }
        $order_no = $db->safe(sprintf("AT%03d%03d", $store_number, $new_order_no));
        $storequery = "select c.id as store_id,c.code,c.store_name,c.tally_name,c.accountinfo,c.owner,c.address,c.city,c.zipcode,c.phone,c.phone2,c.email,c.email2,c.vat,c.store_number,c.usertype,c.tax_type,c.server_change_id,c.username,c.a1hash,c.trust,c.password,c.createtime,c.inactive,d.dealer_discount,d.additional_discount,d.transport,d.octroi,d.cash,d.nonclaim from it_codes c left outer join it_ck_storediscount d on c.id = d.store_id where c.id = ".$storeid;
        $storeobj = $db->fetchObject($storequery);
        $json_str = $db->safe(json_encode($storeobj));
        $order_id = $db->execInsert("insert into it_ck_orders set store_id=$storeid, status=" . OrderStatus::Active . ", order_no=$order_no, order_qty=0 , store_info = $json_str ");
        $clsLogger = new clsLogger();
        foreach ($items as $itemid => $qty) {
            $item_id = $itemid;
            $orderqty = $qty;
            if ($qty <= 0) {
                continue;
            }
//             $itemdbinfo = $db->fetchObject("select i.id as item_id,i.ctg_id,i.design_no,i.curr_qty,i.MRP,d.active from it_items i, it_ck_designs d where i.id=$item_id and i.ctg_id=d.ctg_id and i.design_no=d.design_no and i.curr_qty > 0");
//             $query ="select i.id as item_id,i.ctg_id,i.design_no,i.curr_qty,i.MRP,d.active from it_items i, it_ck_designs d where i.id=$item_id and i.ctg_id=d.ctg_id and i.design_no=d.design_no and i.curr_qty > 0";
//              $itemdbinfo = $db->fetchObject("select i.id as item_id, i.ctg_id,c.name as ctg_name ,c.skip_Place_Order as skipCheck , i.design_no,i.curr_qty,i.MRP,d.active from it_items i, it_ck_designs d , it_categories c where i.id=$item_id and i.ctg_id=d.ctg_id and i.ctg_id = c.id and i.design_no=d.design_no and i.curr_qty > 0");
//              $query = "select i.id as item_id, i.ctg_id,c.name as ctg_name ,c.skip_Place_Order as skipCheck , i.design_no,i.curr_qty,i.MRP,d.active from it_items i, it_ck_designs d , it_categories c where i.id=$item_id and i.ctg_id=d.ctg_id and i.ctg_id = c.id and i.design_no=d.design_no and i.curr_qty > 0";
              $itemdbinfo = $db->fetchObject("select i.id as item_id, i.ctg_id,c.name as ctg_name ,c.skip_Place_Order as skipCheck , i.design_no,i.curr_qty,i.MRP,i.is_design_mrp_active from it_items i, it_ck_designs d , it_categories c where i.id=$item_id and i.ctg_id=d.ctg_id and i.ctg_id = c.id and i.design_no=d.design_no and i.curr_qty > 0");  
//            error_log($query,3,"../formpost/tmp.txt");
             if (!$itemdbinfo) {
                continue;
            }
          //  if (!$itemdbinfo->active) {
            if (!$itemdbinfo->is_design_mrp_active) {
                $msg .= "Design [$itemdbinfo->ctg_name::$itemdbinfo->design_no::$itemdbinfo->MRP] is inactive<br />"; //::$itemdbinfo->MRP
                continue;
            }
            
            if($itemdbinfo->skipCheck == 1){ // skip for socks and handkerchiefs 
                continue;
            }

            if ($itemdbinfo->curr_qty < $orderqty) {
                $orderqty = $itemdbinfo->curr_qty;
                $newstock = 0;
            } else {
                $newstock = $itemdbinfo->curr_qty - $orderqty;
            }
            //id,order_id,store_id,ctg_id,ctg_name,style_id,style_name, size_id,size_name,design_no,order_qty,mrp
            $ctg_id = $db->safe($itemdbinfo->ctg_id);
//            $item_Id = $itemdbinfo->item_id;
            $design_no = $db->safe($itemdbinfo->design_no);
            //update orderitems table with the orderqty 
            $orderitem_id = $db->execInsert("insert into it_ck_orderitems set order_id=$order_id,store_id=$storeid,item_id=$item_id,design_no = $design_no,  MRP = $itemdbinfo->MRP ,order_qty = $orderqty");          
            $query = "update it_items set curr_qty=$newstock where id = $item_id"; 
            //--> code to log it_items update track
                $ipaddr =  $_SERVER['REMOTE_ADDR'];
                $pg_name = __FILE__;                
                $clsLogger->logInfo($query,false, $pg_name,$ipaddr);
            //--> log code ends here
            $updateitem = $db->execUpdate($query);
        }
        //get total summary from it_ck_orderitems to update it_ck_orders and it_ck_pickgroup.
        $summary = $db->fetchObject("select sum(order_qty) as tot_qty,sum(order_qty*MRP) as tot_sum,count(distinct(design_no)) as num_designs from it_ck_orderitems where order_id=$order_id and store_id=$storeid");
        //update it_ck_orders
        $updateord = $db->execUpdate("update it_ck_orders set order_qty =$summary->tot_qty, order_amount=$summary->tot_sum,num_designs=$summary->num_designs,active_time=now() where id=$order_id");

        //$inspickgr = $db->execInsert("insert into it_ck_pickgroup set storeid=$storeid, order_ids=$order_id,order_nos=$order_no, order_qty=$summary->tot_qty, order_amount=$summary->tot_sum,num_designs=$summary->num_designs,active_time=now()");

        $res = "Order $order_no placed for qty:$summary->tot_qty, amount:$summary->tot_sum.<br />";
    } else {
        $res['err'] = "ERROR:No stock available for any of the items in your order - store:$storeinfo->store_name";
    }

     $query = "update it_orders set ck_order_id = $order_id , updatetime = now() where store_id = $storeid and ck_order_id is null";
     $db->execUpdate($query);
     return $res;
}

