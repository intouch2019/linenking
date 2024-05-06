<?php

require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
require_once("session_check.php");
require_once "lib/core/Constants.php";
require_once 'lib/users/clsUsers.php';
//require_once "lib/logger/clsLogger.php";

extract($_POST);
try {
    $store = getCurrUser();
    $db = new DBConn();
    $errors = array();
    $userpage = new clsUsers();
//    $clsLogger = new clsLogger();
    $pagecode = $db->safe($_SESSION['pagecode']);
    $page = $db->fetchObject("select pagecode from it_pages where pagecode = $pagecode");
    if ($page) {
        $allowed = $userpage->isAuthorized($store->id, $page->pagecode);
        if (!$allowed) {
            header("Location: " . DEF_SITEURL . "unauthorized");
            return;
        }
    } else {
        header("Location:" . DEF_SITEURL . "nopagefound");
        return;
    }

    $items = array();
    $itmcnt = 0;
    //step 1 : check if sbstock feature is enabled for the store
    $query = "select sbstock_active from it_codes where id = $sid ";
    $storeobj = $db->fetchObject($query);

    if ($storeobj->sbstock_active == 0) { //means old feature
        $query = "Select oi.item_id as item_id,oi.barcode as barcode,sum(oi.quantity) as qty from it_orders o,it_order_items oi where o.id = oi.order_id and o.store_id = oi.store_id and o.store_id = $sid and o.ck_order_id is null group by oi.item_id";
        $objs = $db->fetchObjectArray($query);

        foreach ($objs as $obj) {
            $itemid = $obj->item_id;
            $qty = $obj->qty;
            if (!isset($items[$itemid]))
                $items[$itemid] = 0;
            if ($qty > 0) {
                $items[$itemid] += $qty;
                $itmcnt++;
            }
        }
    } else if ($storeobj->sbstock_active == 1) { //means enabled standing/base stock feature
        //step 1 : check if base stock ratio is set against the store
        $query = "select id from it_store_ratios where store_id = $sid ";
        // print "<br>".$query;
//                error_log("\nIN SBSEC ENQUERY: $query ",3,"tmp.txt");
        $sobj = $db->fetchObject($query);
        if (!isset($sobj)) {
//                     error_log("\nIN SBOBJ ",3,"tmp.txt");
            $errors[] = "Base Stock Ratio not placed ";
        }

        if (count($errors) == 0) {
//                        error_log("\n In count 0 sec ",3,"tmp.txt");
            $query = "Select oi.item_id as item_id,oi.barcode as barcode,sum(oi.quantity) as qty from it_orders o,it_order_items oi,it_items i where o.id = oi.order_id and o.store_id = oi.store_id and o.store_id = $sid and o.ck_order_id is null and oi.item_id = i.id group by  i.ctg_id,i.design_id,i.style_id,i.size_id "; //oi.item_id";
            //  print "<br>".$query;
//                        error_log("\nITMQUERY: $query ",3,"tmp.txt");
            $objs = $db->fetchObjectArray($query);
            $itemcnt = 0;
            $missedSBItem = 0;
            foreach ($objs as $obj) {
//                             print "<br>";
//                             print_r($obj);
                $itemcnt++;
                $itemid = $obj->item_id;
                //  $qty = $obj->qty;
                // qty shld be difference between curr stock and base stock ratio against that item
                $barcode_db = $db->safe(trim($obj->barcode));
//                             $qry = "select c.*,i.* from it_current_stock c , it_items i where c.barcode = i.barcode and c.store_id = $sid and c.barcode = $barcode_db ";
                $qry = "select c.ctg_id,c.design_id,c.style_id,c.size_id from it_current_stock c , it_items i where c.barcode = i.barcode and c.store_id = $sid and c.barcode = $barcode_db ";
//                             print "<br>".$qry;
//                             error_log("\n CURR ITMQUERY: $qry ",3,"tmp.txt");
                $bobj = $db->fetchObject($qry);
                if (isset($bobj)) {
                    //fetch stores curr stock sum for same ctg,design,style,size
                    $q = "select sum(quantity) as quantity from it_current_stock where store_id = $sid and ctg_id = $bobj->ctg_id and design_id = $bobj->design_id and style_id = $bobj->style_id and size_id = $bobj->size_id ";
//                                 print "<br>".$q;
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
                    // $bsquery = "select * from it_store_ratios where store_id = $sid and ctg_id = $bobj->ctg_id and design_id = $bobj->design_id and style_id = $bobj->style_id and size_id = $bobj->size_id and mrp = $bobj->MRP and ratio_type = ".RatioType::Base;
                    $bsquery = "select ratio from it_store_ratios where store_id = $sid and ctg_id = $bobj->ctg_id and "
                            . "design_id = $bobj->design_id and style_id = $bobj->style_id and size_id = $bobj->size_id  and ratio_type = " . RatioType::Base;
                    //print "<br>".$bsquery;
//                                 error_log("\nBASIC TSK ITMQUERY: $bsquery ",3,"tmp.txt");
                    // $tquery2 = "select sum(oi.quantity) as intransit_stock_value from it_invoices o , it_invoice_items oi , it_items i where oi.invoice_id = o.id and o.invoice_type in ( 0 , 6 ) and o.store_id = $sid and o.is_procsdForRetail = 0 and oi.item_code = i.barcode and oi.item_code in (select barcode from it_items where ctg_id = $bobj->ctg_id and design_id = $bobj->design_id and style_id = $bobj->style_id and size_id = $bobj->size_id ) "; //= i.barcode and i.barcode = $barcode_db ";
                    $tquery2 = "select sum(oi.quantity) as intransit_stock_value from it_sp_invoices o , it_sp_invoice_items oi , it_items i where oi.invoice_id = o.id and o.invoice_type in ( 0 , 6 ) and o.store_id = $sid and o.is_procsdForRetail = 0 and oi.barcode = i.barcode and i.ctg_id = $bobj->ctg_id and i.design_id = $bobj->design_id and i.style_id = $bobj->style_id and i.size_id = $bobj->size_id  "; //= i.barcode and i.barcode = $barcode_db ";
//                                 print "<br>".$tquery2;
                    $obs = $db->fetchObject($tquery2);
                    //$intransit_stock_value = $obs->intransit_stock_value;

                    if (isset($obs)) {
                        if ($obs->intransit_stock_value != null && trim($obs->intransit_stock_value) != "") {
                            $intransit_stock_value = $obs->intransit_stock_value;
                        } else {
                            $intransit_stock_value = 0;
                        }
                    }
                    $bsobj = $db->fetchObject($bsquery);
                    if (!isset($bsobj)) {
                        $bsquery = "select ratio from it_store_ratios where store_id = $sid and ctg_id = $bobj->ctg_id "
                                . "and design_id = -1 and style_id = $bobj->style_id and size_id = $bobj->size_id  and ratio_type = " . RatioType::Base;
                        //print "<br>".$bsquery;        
                        $bsobj = $db->fetchObject($bsquery);
                    }

                    if (isset($bsobj)) {
                        //$store_item_curr_qty = $bobj->quantity;
//                                        error_log("\nSTORE ITM QTY: ".$store_item_curr_qty,3,"tmp.txt");
                        if ($store_item_curr_qty <= 0) {
                            $qty = $bsobj->ratio - $intransit_stock_value;
                        } else {
                            if ($store_item_curr_qty < $bsobj->ratio) {
                                //$qty = $bsobj->ratio - $store_item_curr_qty  ; 
                                $qty = $bsobj->ratio - ($store_item_curr_qty + $intransit_stock_value);
                            } else {
                                $qty = 0; // no need to place the order for this item
                            }
                        }
//                                        error_log("\nQTY: ".$qty,3,"tmp.txt");
                        if ($qty > 0) {
                            if (!isset($items[$itemid]))
                                $items[$itemid] = 0;
                            if ($qty > 0) {
                                $items[$itemid] += $qty;
                                $itmcnt++;
                            }
                        }
//                                       print "<br>ITEMS ARR: <br>";
//                                       print_r($items);
                    } else {
                        $missedSBItem++;
//                                    error_log("\n Item count = ".$itemcnt." MISSED ITM = ".$missedSBItem,3,"tmp.txt"); 
                    }
                }
            }
        } else {
//                   error_log("\n In count else sec p1",3,"tmp.txt"); 
            echo json_encode(array("error" => "1",
                "msg" => "Order cannot be placed as base stock ratios is not set against selected store.")
            );
//                     error_log("\n In count else sec p2",3,"tmp.txt"); 
        }
    }


    if (count($errors) == 0) {
        $num_item_codes = 0;
        $ordered_qty = 0;
        $avail_qty = 0;
        $orderqty = 0;
        $totamt = 0;
        $recentqty = 0;
        $amt = 0;
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
            $ordered_qty += $qty;
            // $dbobj = $db->fetchObject("select i.ctg_id, i.curr_qty from it_items i, it_ck_designs d where i.id=$item_id and i.ctg_id = d.ctg_id and i.design_no = d.design_no and d.active=1 and i.curr_qty > 0");
            $dbobj = $db->fetchObject("select i.ctg_id, i.curr_qty from it_items i, it_ck_designs d where i.id=$item_id and i.ctg_id = d.ctg_id and i.design_no = d.design_no and i.is_design_mrp_active=1 and i.curr_qty > 0");
            if (!$dbobj) {
                continue;
            }
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
        
         //-------min stock store level
    $msl = $db->fetchObject("select min_stock_level,max_stock_level from it_codes where id = $sid");
    if (isset($msl) && trim($msl->min_stock_level) != "") {

        $min_stock = $msl->min_stock_level;
    } else {
        $min_stock = 0;
    }
    
    //-------------------store curr stock

    $store_stock = $db->fetchObject("select sum(c.quantity * i.MRP) as curr_stock_value from it_current_stock c , it_items i where c.store_id = $sid  and c.barcode = i.barcode and i.ctg_id not in (53,54,64,62,63,41,56,52,51,61,46,42,43)");

    $db->closeConnection();
    if (isset($store_stock) && trim($store_stock->curr_stock_value) != "") {
        $curr_stock_val = $store_stock->curr_stock_value;
    } else {
        $curr_stock_val = 0;
    }

     
    //----------------------------------intransit stock

    $stock_intransit_new = $db->fetchObject("select sum(i.MRP*oi.quantity) as intransit_stock_value_new from it_sp_invoices o , it_sp_invoice_items oi , it_items i where oi.invoice_id = o.id and o.invoice_type in ( 0 , 6 ) and o.store_id =$sid  and o.is_procsdForRetail = 0 and oi.barcode = i.barcode");
    $db->closeConnection();

    if (isset($stock_intransit_new) && trim($stock_intransit_new->intransit_stock_value_new) != "") {
        $intransit_stock_value_new = $stock_intransit_new->intransit_stock_value_new;
    } else {
        $intransit_stock_value_new = 0;
    }


       //------------------------------------check active ammount from order

    $active_amount = $db->fetchObject("select sum(order_amount) as active_amount from it_ck_orders where status=1 and store_id=$sid");
    $db->closeConnection();
    if (isset($active_amount) && trim($active_amount->active_amount) != "") {
        $active_amt = $active_amount->active_amount;
    } else {
        $active_amt = 0;
    }
    
   
    
      //--------------------check picking ammount from order

    $picking_amount = $db->fetchObject("select sum(order_amount) as picking_amount  from it_ck_orders where status=2 and store_id=$sid");
    $db->closeConnection();
    if (isset($picking_amount) && trim($picking_amount->picking_amount) != "") {
        $picking_amt = $picking_amount->picking_amount;
    } else {
        $picking_amt = 0;
    }
    
    
     //--------------------------check picking complete  ammount from order

    $picking_complete_amount = $db->fetchObject("select sum(order_amount) as picking_complete_amount  from it_ck_orders where status=5 and store_id=$sid");
    $db->closeConnection();
    if (isset($picking_complete_amount) && trim($picking_complete_amount->picking_complete_amount) != "") {
        $picking_complete_amt = $picking_complete_amount->picking_complete_amount;
    } else {
        $picking_complete_amt = 0;
    }

     
    //------------------------------cart

    $cartinfoo = $db->fetchObject("select  sum(order_amount) as cart_amt from it_ck_orders where status=0 and store_id=$sid");

    if (isset($cartinfoo) && trim($cartinfoo->cart_amt) != "") {
        $cart_amount = $cartinfoo->cart_amt;
    } else {
        $cart_amount = 0;
    }
      
$totalamt = $tamt + $curr_stock_val + $intransit_stock_value_new + $active_amt + $picking_amt + $picking_complete_amt + $cart_amount;

if ($totalamt >= $min_stock) {

        $lastrecord = $db->fetchObject("select createtime from it_orders where store_id = $sid order by id desc limit 1");

        if ($ordered_qty == 0) {
//                 error_log("\n Item count = ".$itemcnt." MISSED ITM = ".$missedSBItem,3,"tmp.txt"); 
            $msg = "Order cannot be placed as stock qty is equal or greater than base stock ratios .";
            if ($itemcnt == $missedSBItem) {
                $msg = " Order cannot be placed as base stock ratio is not set for any items within the orders";
            }
            echo json_encode(array("error" => "1",
                "msg" => $msg)
            );
        } else {
            echo json_encode(array("error" => "0",
                "num_item" => $num_item_codes,
                "orderqty" => $ordered_qty,
                "availstock" => $avail_qty,
                "lasttime" => $lastrecord->createtime)
            );
        }
          }else{
             $requ=$min_stock-$totalamt;
        $msg = "Your AT order value does not meet to the minimum store stock level Please consider adding additional items to your manual order to meet the minimum requirement of minimum amount RS- .$requ";
        
         echo json_encode(array("error" => "1",
                "msg" => $msg)
            );
        }
    }
} catch (Exception $xcp) {
    echo json_encode(array("error" => "1", "message" => "Exception:" . $xcp->getMessage()));
}
