<?php

//require_once("/var/www/limelight_new/it_config.php");
require_once ("/var/www/html/linenking/it_config.php");
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");
require_once ("lib/logger/clsLogger.php");
require_once ("lib/core/clsProperties.php");

$db = new DBConn();
$clsLogger = new clsLogger();
$res = "";
$error = "";
$ordercount = 0;
$totalorderQty = 0;
$storeid_list = "";
$pg_name = "";
$ipaddr = "";

try {

    //rg testing
    $dbProperties = new dbProperties();
    if ($dbProperties->getBoolean(Properties::DisableUserLogins)) {
        print_r("All Stores are Disabled.");
        exit();
    }

    //step 1 : check if for that store sbstock feature is enabled
//     $query = "select c.id,c.sbstock_active,c.autorefil_dttm,c.store_name as store,sum(o.quantity) as qty from it_codes c,it_orders o where c.is_autorefill=1 and c.inactive = 0  and c.is_closed = 0 and c.id=o.store_id and o.ck_order_id is null and o.quantity > 0 and o.bill_datetime >= case when ISNULL(c.autorefil_dttm) then o.bill_datetime else c.autorefil_dttm end group by o.store_id order by sum(o.quantity) desc";
    $query = "select c.id,c.sbstock_active,c.autorefil_dttm,c.store_name as store,sum(o.quantity) as qty from it_codes c,it_orders o where c.is_autorefill=1 and c.inactive = 0  and c.is_closed = 0 and c.id=o.store_id and o.ck_order_id is null and o.quantity > 0 and o.bill_datetime >= case when ISNULL(c.autorefil_dttm) then o.bill_datetime else c.autorefil_dttm end group by o.store_id order by c.sequence";
    $objs = $db->fetchObjectArray($query);
    foreach ($objs as $storeobj) {
        $items = array();
        $total_order_qty = 0;
        $storeid = $storeobj->id;
        if ($storeobj->sbstock_active == 0) { // means old procedure
            ////check in it_codes whethere autorefill_dttm is set
            //if yes then put dat chk clause
            if (trim($storeobj->autorefil_dttm) != "") {
                $bClause = "and o.bill_datetime >= c.autorefil_dttm";
                $tabClause = " ,it_codes c ";
                $cClause = " and c.id=$storeid";
            } else {
                $tabClause = "";
                $bClause = "";
                $cClause = "";
            }
            $query = "Select oi.item_id as item_id,oi.barcode as barcode ,sum(oi.quantity) as qty from it_orders o,it_order_items oi $tabClause where o.id = oi.order_id and o.store_id = oi.store_id and o.store_id = " . $storeid . " $cClause $bClause and o.ck_order_id is null group by oi.item_id order by null";
            $objs = $db->fetchObjectArray($query);
            foreach ($objs as $obj) {
                $itemid = $obj->item_id;
                $qty = $obj->qty;
                if (!isset($items[$itemid]))
                    $items[$itemid] = 0;
                $items[$itemid] += $qty;
                $total_order_qty += $qty;
//                                $itmcnt++;
            }

            if ($total_order_qty > 100) {  //60
                $ress = saveOrder($storeid, $items);
                $arr = explode(":", $ress);
                if ($arr[0] == 1) {
                    $ordercount++;
                    $totalorderQty += $arr[1];
                    $storeid_list .= $storeid . ",";
                }
                if ($arr[0] == 0) {
                    $error .= $ress;
                }
            }
        } else if ($storeobj->sbstock_active == 1) { // means enabled standing/base stock feature
            //step 1 : check if base stock ratio is set against the store
            $query = "select * from it_store_ratios where store_id = $storeid ";
            $errors = array();
            $sobj = $db->fetchObject($query);
            if (!isset($sobj)) {
                $errors[] = "Base Stock Ratio not placed ";
                $error .= "Base Stock Ratio not placed ";
            }

            if (count($errors) == 0) {
                if (trim($storeobj->autorefil_dttm) != "") {
                    $tabClause = " ,it_codes c ";
                    $bClause = "and o.bill_datetime >= c.autorefil_dttm";
                    $cClause = " and c.id=$storeid";
                } else {
                    $tabClause = "";
                    $cClause = "";
                    $bClause = "";
                }
                $query = "Select oi.item_id as item_id,oi.barcode as barcode,sum(oi.quantity) as qty from it_orders o,it_order_items oi,it_items i $tabClause where o.id = oi.order_id and o.store_id = oi.store_id and o.store_id = $storeid  $cClause and o.ck_order_id is null and oi.item_id = i.id $bClause group by  i.ctg_id,i.design_id,i.style_id,i.size_id order by null"; //oi.item_id";
                $objs = $db->fetchObjectArray($query);

                foreach ($objs as $obj) {
                    $itemid = $obj->item_id;
                    // qty shld be difference between curr stock and base stock ratio against that item
                    $barcode_db = $db->safe(trim($obj->barcode));
                    $qry = "select c.*,i.* from it_current_stock c , it_items i where c.barcode = i.barcode and c.store_id = $storeid and c.barcode = $barcode_db ";
                    $bobj = $db->fetchObject($qry);

                    if (isset($bobj)) {
                        //fetch stores curr stock sum for same ctg,design,style,size
                        $q = "select sum(quantity) as quantity from it_current_stock where store_id = $storeid and ctg_id = $bobj->ctg_id and design_id = $bobj->design_id and style_id = $bobj->style_id and size_id = $bobj->size_id ";
                        //echo $q."<br>";
                        $scurrobj = $db->fetchObject($q);
                        if ($scurrobj) {
                            if ($scurrobj->quantity == null || trim($scurrobj->quantity) == "") {
                                $store_item_curr_qty = 0;
                            } else {
                                $store_item_curr_qty = $scurrobj->quantity;
                            }
                        } else {
                            $store_item_curr_qty = 0;
                        }
                        $intransit_stock_value = 0;
                        //fetch base stock ratio against that item
                        //$bsquery = "select * from it_store_ratios where store_id = $storeid and ctg_id = $bobj->ctg_id and design_id = $bobj->design_id and style_id = $bobj->style_id and size_id = $bobj->size_id and mrp = $bobj->MRP and ratio_type = ".RatioType::Base;
                        $bsquery = "select ratio from it_store_ratios where store_id = $storeid and ctg_id = $bobj->ctg_id and design_id = $bobj->design_id and style_id = $bobj->style_id and size_id = $bobj->size_id  and ratio_type = " . RatioType::Base;
                        $tquery2 = "select sum(oi.quantity) as intransit_stock_value from it_invoices o , it_invoice_items oi , it_items i where oi.invoice_id = o.id and o.invoice_type in ( 0 , 6 ) and o.store_id = $storeid and o.is_procsdForRetail = 0 and oi.item_code = i.barcode and i.ctg_id = $bobj->ctg_id and i.design_id = $bobj->design_id and i.style_id = $bobj->style_id and i.size_id = $bobj->size_id  "; //= i.barcode and i.barcode = $barcode_db ";
                        //echo $tquery2."<br>";
                        $obs = $db->fetchObject($tquery2);
                        if (isset($obs)) {
                            if ($obs->intransit_stock_value != null && trim($obs->intransit_stock_value) != "") {
                                $intransit_stock_value = $obs->intransit_stock_value;
                            } else {
                                $intransit_stock_value = 0;
                            }
                        }
                        $bsobj = $db->fetchObject($bsquery);
                        if (!isset($bsobj)) {
                            $bsquery = "select ratio from it_store_ratios where store_id = $storeid and ctg_id = $bobj->ctg_id "
                                    . "and design_id = -1 and style_id = $bobj->style_id and size_id = $bobj->size_id  and ratio_type = " . RatioType::Base;
                            //echo $bsquery."<br>";        
                            $bsobj = $db->fetchObject($bsquery);
                        }
                        if (isset($bsobj)) {
                            if ($store_item_curr_qty <= 0) {
                                $qty = $bsobj->ratio - $intransit_stock_value;
                            } else if ($store_item_curr_qty < $bsobj->ratio) {
                                $qty = $bsobj->ratio - ($store_item_curr_qty + $intransit_stock_value);
                            } else {
                                $qty = 0; // no need to place the order for this item
                            }

                            if ($qty > 0) {
                                if (!isset($items[$itemid]))
                                    $items[$itemid] = 0;
                                if ($qty > 0) {
                                    $items[$itemid] += $qty;
                                    $total_order_qty += $qty;
//                                    $itmcnt++;
                                }
                            }
                        }
                    }
                }
                if ($total_order_qty > 100) {   //60
                    $ress = saveOrder($storeid, $items);
                    $arr = explode(":", $ress);
                    if ($arr[0] == 1) {
                        $ordercount++;
                        $totalorderQty += $arr[1];
                        $storeid_list .= $storeid . ",";
                    }
                    if ($arr[0] == 0) {
                        $error .= $ress;
                    }
                }

//                $res = saveOrder($storeid, $items);
            }
        }
    }
    //--> code to log it_items update track
//                $ipaddr =  $_SERVER['REMOTE_ADDR'];
    $ipaddr = 0;
    $pg_name = __FILE__;

    if ($error != "") {
        $clsLogger->logInfo1($error, false, $pg_name, $ipaddr);
    }
    if ($totalorderQty >= 0) {
        $clsLogger->logInfo1(" Order placed for :" . $storeid_list . " and total order quantity:" . $totalorderQty, false, $pg_name, $ipaddr);
    }
    //--> log code ends here   
} catch (Exception $xcp) {

    $clsLogger->logInfo1($xcp->getMessage(), false, $pg_name, $ipaddr);
//    print $xcp->getMessage();
}

exit;

function saveOrder($storeid, $items) {

    $msg = "";
    $order_id = false;
    $db = new DBConn();

    $storeinfo = $db->fetchObject("select store_name,store_number from it_codes where id =$storeid");

    if (!$storeinfo) {
        $res = "0:ERROR:Store $storeinfo->store_name not found";
        return $res;
    }


    $num_item_codes = 0;
    $ordered_qty = 0;
    $avail_qty = 0;
    $recentqty = 0;
    $orderqty = 0;
    $sum = 0;
    $tamt = 0;
    $totalamt = 0;
    foreach ($items as $itemid => $qty) {
        $item_id = $itemid;
        $orderqty = $qty;
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
        
        $itemdbinfo = $db->fetchObject("select i.id as item_id, i.ctg_id,c.name as ctg_name ,c.skip_Place_Order as skipCheck , i.design_no,i.curr_qty,i.MRP,i.is_design_mrp_active from it_items i, it_ck_designs d , it_categories c where i.id=$item_id and i.ctg_id=d.ctg_id and i.ctg_id = c.id and i.design_no=d.design_no and i.curr_qty > 0");
        if (!$itemdbinfo) {

            continue;
        }
        if (!$itemdbinfo->is_design_mrp_active) {
            $msg .= "Design [$itemdbinfo->ctg_name::$itemdbinfo->design_no::$itemdbinfo->MRP] is inactive<br />";
            //  print "<br> skipp for inactive products......2";
            continue;
        }
        if ($itemdbinfo->skipCheck == 1) { // skip for socks and handkerchiefs 
            // print "<br> skip for skip_place_order=1";
            continue;
        }
        if ($itemdbinfo->curr_qty < $orderqty) {
            $orderqty = $itemdbinfo->curr_qty;
        }
        $sum = $orderqty * $itemdbinfo->MRP;
        $tamt += $sum;
        $recentqty += $orderqty;
    }
    $store_number = $storeinfo->store_number;
    
     //-------min stock store level
    $msl = $db->fetchObject("select min_stock_level,max_stock_level from it_codes where id = $storeid");
    if (isset($msl) && trim($msl->min_stock_level) != "") {

        $min_stock = $msl->min_stock_level;
    } else {
        $min_stock = 0;
    }
    
     //-------------------store curr stock

    $store_stock = $db->fetchObject("select sum(c.quantity * i.MRP) as curr_stock_value from it_current_stock c , it_items i where c.store_id = $storeid  and c.barcode = i.barcode");

    $db->closeConnection();
    if (isset($store_stock) && trim($store_stock->curr_stock_value) != "") {
        $curr_stock_val = $store_stock->curr_stock_value;
    } else {
        $curr_stock_val = 0;
    }
    
    
    //---------------intransit_stock_value_new
    
    $stock_intransit_new = $db->fetchObject("select sum(i.MRP*oi.quantity) as intransit_stock_value_new from it_sp_invoices o , it_sp_invoice_items oi , it_items i where oi.invoice_id = o.id and o.invoice_type in ( 0 , 6 ) and o.store_id =$store_id  and o.is_procsdForRetail = 0 and oi.item_code = i.barcode");
                    $db->closeConnection();

                    if (isset($stock_intransit_new) && trim($stock_intransit_new->intransit_stock_value_new) != "") {
                        $intransit_stock_value_new = $stock_intransit_new->intransit_stock_value_new;
                    } else {
                        $intransit_stock_value_new = 0;
                    }
    
    
    
    //------------------------------------check active ammount from order

    $active_amount = $db->fetchObject("select sum(order_amount) as active_amount from it_ck_orders where status=1 and store_id=$storeid");
    $db->closeConnection();
    if (isset($active_amount) && trim($active_amount->active_amount) != "") {
        $active_amt = $active_amount->active_amount;
    } else {
        $active_amt = 0;
    }
    
      //--------------------check picking ammount from order

    $picking_amount = $db->fetchObject("select sum(order_amount) as picking_amount  from it_ck_orders where status=2 and store_id=$storeid");
    $db->closeConnection();
    if (isset($picking_amount) && trim($picking_amount->picking_amount) != "") {
        $picking_amt = $picking_amount->picking_amount;
    } else {
        $picking_amt = 0;
    }
    
      //--------------------------check picking complete  ammount from order

    $picking_complete_amount = $db->fetchObject("select sum(order_amount) as picking_complete_amount  from it_ck_orders where status=5 and store_id=$storeid");
    $db->closeConnection();
    if (isset($picking_complete_amount) && trim($picking_complete_amount->picking_complete_amount) != "") {
        $picking_complete_amt = $picking_complete_amount->picking_complete_amount;
    } else {
        $picking_complete_amt = 0;
    }
    
     //------------------------------cart

    $cartinfoo = $db->fetchObject("select  sum(order_amount) as cart_amt from it_ck_orders where status=0 and store_id=$storeid");

    if (isset($cartinfoo) && trim($cartinfoo->cart_amt) != "") {
        $cart_amount = $cartinfoo->cart_amt;
    } else {
        $cart_amount = 0;
    }
    
     $totalamt = $tamt + $curr_stock_val + $intransit_stock_value_new + $active_amt + $picking_amt + $picking_complete_amt + $cart_amount;
    
    
    if ($totalamt >= $min_stock) {

    //if at least 1 qty exist ->continue and  create a new order in it_ck_orders
    if ($avail_qty > 0) {

        $store_number = $storeinfo->store_number;

        //insert new order. 
        if (!$store_number) {
            $res = "0:ERROR:Store number missing for store $storeinfo->store_name.";
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
        $order_no = $db->safe(sprintf("FAT%03d%03d", $store_number, $new_order_no));
        $storequery = "select c.id as store_id,c.code,c.store_name,c.tally_name,c.accountinfo,c.owner,c.address,c.city,c.zipcode,c.phone,c.phone2,c.email,c.email2,c.vat,c.store_number,c.usertype,c.tax_type,c.server_change_id,c.username,c.a1hash,c.trust,c.password,c.createtime,c.inactive,d.dealer_discount,d.additional_discount,d.transport,d.octroi,d.cash,d.nonclaim from it_codes c left outer join it_ck_storediscount d on c.id = d.store_id where c.id = " . $storeid;
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
            $itemdbinfo = $db->fetchObject("select i.id as item_id, i.ctg_id,c.name as ctg_name ,c.skip_Place_Order as skipCheck , i.design_no,i.curr_qty,i.MRP,i.is_design_mrp_active from it_items i, it_ck_designs d , it_categories c where i.id=$item_id and i.ctg_id=d.ctg_id and i.ctg_id = c.id and i.design_no=d.design_no and i.curr_qty > 0");
            if (!$itemdbinfo) {
                continue;
            }
            if (!$itemdbinfo->is_design_mrp_active) {
                $msg .= "Design [$itemdbinfo->ctg_name::$itemdbinfo->design_no::$itemdbinfo->MRP] is inactive<br />";
                continue;
            }

            if ($itemdbinfo->skipCheck == 1) { // skip for socks and handkerchiefs 
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

            $updateitem = $db->execUpdate($query);
        }
        //get total summary from it_ck_orderitems to update it_ck_orders and it_ck_pickgroup.
        $summary = $db->fetchObject("select sum(order_qty) as tot_qty,sum(order_qty*MRP) as tot_sum,count(distinct(design_no)) as num_designs from it_ck_orderitems where order_id=$order_id and store_id=$storeid");
        //update it_ck_orders
        $updateord = $db->execUpdate("update it_ck_orders set order_qty =$summary->tot_qty, order_amount=$summary->tot_sum,num_designs=$summary->num_designs,active_time=now() where id=$order_id");

        //  $inspickgr = $db->execInsert("insert into it_ck_pickgroup set storeid=$storeid, order_ids=$order_id,order_nos=$order_no, order_qty=$summary->tot_qty, order_amount=$summary->tot_sum,num_designs=$summary->num_designs,active_time=now()");
//        echo "Order $order_no placed for qty:$summary->tot_qty, amount:$summary->tot_sum, store:$storeinfo->store_name.<br /><br />";

        $res1 = "1:" . $summary->tot_qty;
    } else {
        $res1 = "0:ERROR:No stock available for any of the items in your order - store:$storeinfo->store_name";
    }
      }else{
       
     $res1 = "order not placed bec of FAT order does not meet the store stock level amount -store:$storeinfo->store_name.";
}

    $query = "update it_orders set ck_order_id = $order_id , updatetime = now() where store_id = $storeid and ck_order_id is null";
    $db->execUpdate($query);
    return $res1;
}
