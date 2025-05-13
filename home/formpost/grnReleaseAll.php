<?php

require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once 'lib/users/clsUsers.php';
require_once "lib/logger/clsLogger.php";
require_once "lib/grnPDFClass/GeneratePDF.php";
require_once "lib/orders/clsOrders.php";
require_once "lib/grnPDFClass/EmailHelper.php";

extract($_POST);
//print_r($_POST);
$db = new DBConn();
//$clsLogger = new clsLogger();
$clsOrders = new clsOrders();

$place_ord = isset($_POST['stand_ord']) ? $_POST['stand_ord'] : false;
$Release_time = isset($_POST['Release']) ? $_POST['Release'] : false;
$id = isset($_POST['id']) ? $_POST['id'] : false;

$errors = array();
$store_orders = array(); //store orders information
$items_with_balance = array();
$dno = "";
$success = "GRN Released successfully";

if (trim($place_ord) == "") {
    $errors[] = "Please select whether to place standing order(s) or not.";
    foreach ($designid as $darrobj) {
        $dno .= $darrobj['design_id'] . ",";
    }
}



if (count($errors) == 0) {
    try {
        //step 1 : fetch all auto refill stores
        $squery = "select id,store_number,min_stock_level,max_stock_level,inactive from  it_codes where usertype = " . UserType::Dealer . " and is_autorefill = 1 and is_closed = 0 and inactive = 0 and sbstock_active = 1 and sequence is not null and sequence > 0 order by sequence ";
//   $sresults = $db->execQuery($squery);

        $storeobjs = $db->fetchObjectArray($squery);
      //  print "<br>Store QRY: $squery <br><br><br>";
//  print_r($storeobjs);
//    print_r($_POST['designid']);
        $sa = "select Rel_sent_temp from release_orders where id=$id and Rel_sent_temp=1";
//        print_r($sa);
        $sc = $db->fetchObject($sa);

        if (isset($sc->Rel_sent_temp) == 1 || $Release_time == '00:00:00') {

                  if (isset($sc) && trim($sc->Rel_sent_temp) != "") {
                      
                  
            $du = "update release_orders set Rel_sent_temp=0 where id=$id and Rel_sent_temp=1";
            $db->execUpdate($du);
            
                  }

            foreach ($designid as $darrobj) {
//        print "<br><br>Design OBJ begins: ";
//        print_r($darrobj);
                $dno .= $darrobj['design_id'] . ",";

                //print "<br><br>DNO=$dno<br><br>";
                //fetch design' details
                $ctg_id = isset($darrobj['ctg_id']) ? $darrobj['ctg_id'] : false;
                $design_id = isset($darrobj['design_id']) ? $darrobj['design_id'] : false;
                $design_no = isset($darrobj['design_no']) ? $darrobj['design_no'] : false;
                $design_active = isset($darrobj['design_active']) ? $darrobj['design_active'] : false;
                $mrp = isset($darrobj['mrp']) ? $darrobj['mrp'] : false;
                $cdesp = isset($darrobj['cdesp']) ? $darrobj['cdesp'] : false;

//        print "<br> CTG DESP: $cdesp ";
                //ctg_description
                if (isset($cdesp) && trim($cdesp) != "" && trim($cdesp) != "-1") {
                    $cdesp_db = $db->safe(trim($cdesp));
                    $sqry = "select id from it_grn_ctg_desp where ctg_id = $ctg_id and design_id = $design_id ";
                    $sgobj = $db->fetchObject($sqry);
                    if ($sgobj) {
                        $uqry = "update it_grn_ctg_desp set cdesp = $cdesp_db where id = $sgobj->id ";
                        $db->execUpdate($uqry);
                    } else {
                        $insqry = "insert into it_grn_ctg_desp set ctg_id = $ctg_id , design_id = $design_id ,cdesp = $cdesp_db, createtime = now() ";
//                print "<br> C DESP QRY: $insqry ";
                        $db->execInsert($insqry);
                    }
                }


                //check design active/inactive
                // if(trim($design_active)==0){ // means design is inactive so activate it.
                if (trim($design_no) != "" && trim($ctg_id) != "") {
                    //so activate it
                    // $duqry = "update it_ck_designs set active = 1 where id = $design_id ";
                    //$duqry = "update it_items set is_design_mrp_active = 1 where design_id = $design_id ";
                    $design_nodb = $db->safe(trim($design_no));
                    $duqry = "update it_items set is_design_mrp_active = 1 where design_no = $design_nodb and ctg_id = $ctg_id "; //design_id = $design_id ";
                    $db->execUpdate($duqry);
                }


//        print "<br><br> ITS ITEMS: ";
//        print_r($darrobj['item']);
//        print "<br><br>";
                $itemarr = $darrobj['item'];
                foreach ($itemarr as $item_key => $item_value) {
                    $multiple_grp_flag = 0;
                    $currCheck = "";
                    if (trim($item_value) == "") {
                        continue;
                    }

                    //if(trim($item_value) > 0){ // means to_release qty shld be > 0
                    $iarr = explode("_", $item_key);
                    $item_id = $iarr[1];
                    $grn_qty = $iarr[2];
                    $to_release_qty = $item_value;
                    $release_bal_qty = $to_release_qty;
                    $multiple_items_released = array();

//                 print "<br>ITEM KEY: $item_key <br> ITEM VALUE: $item_value ";
//                  print "<br> INITIAL ITEM ID: $item_id <br>";
                    if ($grn_qty > 0 && $to_release_qty > 0 && $to_release_qty <= $grn_qty) {
                        $iqry = "select barcode,grn_qty,curr_qty,ctg_id,design_id,style_id,size_id,design_no,id,MRP from it_items where id = $item_id ";
//                    print "<br>ITEMS DETAILS: $iqry ";
                        $iobj = $db->fetchObject($iqry);
//                    print "<br>";
//                    print_r($iobj);
                        //STEP A : GRN QTY RELEASE FROM IT_ITEMS PROCEDURE
                        if (isset($iobj)) {
                            $barcode_db = $db->safe(trim($iobj->barcode));
                            $item_grn_qty = $iobj->grn_qty;
                            if ($iobj->grn_qty < $to_release_qty) {
                                $multiple_grp_flag = 1; // means release qty is sum of same group barcodes grn_qty
                                
                                //deactivate design for manual order
                                $query = "update it_items set is_avail_manual_order=0 where id = $item_id ";
           
                                //--> code to log it_items update track
//                                $ipaddr = $_SERVER['REMOTE_ADDR'];
//                                $pg_name = __FILE__;
//                                $clsLogger->logInfo($query, false, $pg_name, $ipaddr);
                                //--> log code ends here
                                $db->execUpdate($query);
                                
                                $multiple_items_released = grnItemsBal($iobj, $to_release_qty);
//                                print "ITS ARR: <br>";
//                                print_r($multiple_items_released);
//                                print "<br>";
                                $currCheck = "";
                            } else {
                                $multiple_grp_flag = 0; // means individual item in the same barcode grp has grn_qty , others grn_qty is not released
                                
                                 //deactivate design for manual order
                                $query = "update it_items set is_avail_manual_order=0 where id = $item_id ";
           
                                //--> code to log it_items update track
//                                $ipaddr = $_SERVER['REMOTE_ADDR'];
//                                $pg_name = __FILE__;
//                                $clsLogger->logInfo($query, false, $pg_name, $ipaddr);
                                //--> log code ends here
                                $db->execUpdate($query);
                                
                                $query = "update it_items set grn_qty = grn_qty - $to_release_qty , curr_qty = curr_qty + $to_release_qty where id = $item_id ";
//                               print "<br>UPDATE ITM QRY ELSE CASE: $query ";
                                $currCheck = " && $iobj->curr_qty > 0 ";

                                //                       print "<br> CURR CHK: ".$currCheck;
                                //                      error_log("grnr qry: $query",3,"tmp.txt");
                                //                       print "<br>$query<br>";
                                //--> code to log it_items update track
//                                $ipaddr = $_SERVER['REMOTE_ADDR'];
//                                $pg_name = __FILE__;
//                                $clsLogger->logInfo($query, false, $pg_name, $ipaddr);
                                //--> log code ends here
                                $db->execUpdate($query);
                            }

                            //STEP B: STANDING ORDER PLACING PROCEDURE BEGINS    
                            if (trim($place_ord) == 1) {
                                foreach ($storeobjs as $sobj) {
                                    if (isset($sobj)) {
                                        // step 1 : fetch store items details
                                        $iquery = "select sum(quantity) as quantity from  it_current_stock where store_id = $sobj->id  and ctg_id = $iobj->ctg_id and design_id = $iobj->design_id and style_id = $iobj->style_id and size_id = $iobj->size_id ";
//                                           print "<br><br>STORE CURR ITM QRY: $iquery<br><br>";
                                        $siobj = $db->fetchObject($iquery);
                                        if (isset($siobj->quantity)) {
                                            if (trim($siobj->quantity) == "") {
                                                $store_item_curr_qty = 0;
                                            } else {
                                                $store_item_curr_qty = $siobj->quantity;
                                            }
                                        } else {
                                            $store_item_curr_qty = 0;
                                        }

                                        $intransit_stock_value = 0;
                                        //$tquery2 = "select sum(oi.quantity) as intransit_stock_value from it_invoices o , it_invoice_items oi , it_items i where oi.invoice_id = o.id and o.invoice_type in ( 0 , 6 ) and o.store_id = $sobj->id and o.is_procsdForRetail = 0 and oi.item_code = i.barcode and oi.item_code in (select barcode from it_items where ctg_id = $iobj->ctg_id and design_id = $iobj->design_id and style_id = $iobj->style_id and size_id = $iobj->size_id  ) "; //= i.barcode and i.barcode = $barcode_db ";
                                        $tquery2 = "select sum(oi.quantity) as intransit_stock_value from it_sp_invoices o , it_sp_invoice_items oi , it_items i where oi.invoice_id = o.id and o.invoice_type in ( 0 , 6 ) and o.store_id = $sobj->id and o.is_procsdForRetail = 0 and oi.barcode = i.barcode and i.ctg_id =  $iobj->ctg_id and i.design_id = $iobj->design_id and i.style_id = $iobj->style_id and i.size_id = $iobj->size_id  "; //= i.barcode and i.barcode = $barcode_db ";
//                                                 print "<br>IN TRANSIT QRY: ".$tquery2;

                                        $obs = $db->fetchObject($tquery2);
//                                           print_r($obs);

                                        if (isset($obs)) {
                                            if (trim($obs->intransit_stock_value) == "") {
                                                $intransit_stock_value = 0;
                                            } else {
                                                $intransit_stock_value = $obs->intransit_stock_value;
                                            }
                                        }
//                                            print "<br>INSTRANSIT : $intransit_stock_value ";
                                        //fetch store's standing stock ratio againts item                                          
                                        $checkcore = "select core from it_ck_designs where id= $design_id";

                                        $core = $db->fetchObject($checkcore);

                                        $squery = "select ratio from it_store_ratios where store_id = $sobj->id and ctg_id = $iobj->ctg_id and design_id = $design_id and style_id = $iobj->style_id and size_id = $iobj->size_id and ratio_type = " . RatioType::Standing . " and is_exceptional = 1 and is_exceptional_active = 1 and core=$core->core";
                                        // print "<br> STORE STANDING STOCK QRY: $squery";
                                        $stkobj = $db->fetchObject($squery);

                                        if (!isset($stkobj)) {
//                                            $checkcore = "select core from it_ck_designs where id= $design_id";
//
//                                            $core = $db->fetchObject($checkcore);

                                            $squery = "select ratio from it_store_ratios where store_id = $sobj->id and ctg_id = $iobj->ctg_id "
                                                    . "and design_id = -1 and style_id = $iobj->style_id and size_id = $iobj->size_id and ratio_type = " . RatioType::Standing . " and core=$core->core";
                                            //  print "<br> STORE STANDING STOCK QRY FOR ALL DESIGNS: $squery";
                                            $stkobj = $db->fetchObject($squery);
                                        }

                                        $sum_qty = $store_item_curr_qty + $intransit_stock_value;

//                                           print "<br>SUM QTY: $sum_qty";


                                        if (isset($stkobj) && $release_bal_qty > 0 && isset($siobj) . $currCheck) {

                                            if ($sum_qty <= 0) {
                                                //  print_r(" ratio=". $stkobj->ratio);
                                                $qty = $stkobj->ratio;
                                            } else {
                                                if ($sum_qty < $stkobj->ratio) {
                                                    $qty = $stkobj->ratio - $sum_qty;
                                                } else {
                                                    $qty = 0; // no need to place the order for this item
                                                }
                                            }


                                            if ($qty >= $release_bal_qty) {
                                                $qty = $release_bal_qty;
                                            }

//                                            $msl = $db->fetchObject("select min_stock_level,max_stock_level,inactive from it_codes where id = $sobj->id");

                                            if (!isset($csv[$sobj->id])) {

                                                $store_stock = $db->fetchObject("select sum(c.quantity * i.MRP) as curr_stock_value from it_current_stock c , it_items i where c.store_id = $sobj->id  and c.barcode = i.barcode and i.ctg_id not in (53,54,64,62,63,41,56,52,51,61,46,42,43,65)");

                                                if (isset($store_stock) && trim($store_stock->curr_stock_value) != "") {
                                                    $curr_stock_val = $store_stock->curr_stock_value;
                                                } else {
                                                    $curr_stock_val = 0;
                                                }
                                                $csv[$sobj->id] = $curr_stock_val;
                                            } else {

                                                $curr_stock_val = $csv[$sobj->id];
                                            }

                                            if (!isset($isv[$sobj->id])) {

                                                 $stock_intransit_new = $db->fetchObject("select sum(i.MRP*oi.quantity) as intransit_stock_value_new from it_sp_invoices o , it_sp_invoice_items oi , it_items i where oi.invoice_id = o.id and o.invoice_type in ( 0 , 6 ) and o.store_id =$sobj->id   and o.is_procsdForRetail = 0 and oi.barcode = i.barcode");
                                                 

                                                if (isset($stock_intransit_new) && trim($stock_intransit_new->intransit_stock_value_new) != "") {
                                                    $intransit_stock_value_new = $stock_intransit_new->intransit_stock_value_new;
                                                } else {
                                                    $intransit_stock_value_new = 0;
                                                }
                                                $isv[$sobj->id] = $intransit_stock_value_new;
                                            } else {
                                                $intransit_stock_value_new = $isv[$sobj->id];
                                            }
                                            
                                            //Fetch active,picking,picking_complete,cart amount
                                            $query = "SELECT SUM(CASE WHEN status = 1 THEN order_amount ELSE 0 END) AS active_amount, SUM(CASE WHEN status = 2 THEN order_amount ELSE 0 END) AS picking_amount, SUM(CASE WHEN status = 5 THEN order_amount ELSE 0 END) AS picking_complete_amount, SUM(CASE WHEN status = 0 THEN order_amount ELSE 0 END) AS cart_amt FROM it_ck_orders WHERE store_id = $sobj->id and status in (0,1,2,5)";
                                            $result = $db->fetchObject($query);

                                            // Access the amounts
                                            $active_amt = isset($result->active_amount) ? $result->active_amount : 0;
                                            $picking_amt = isset($result->picking_amount) ? $result->picking_amount : 0;
                                            $picking_complete_amt = isset($result->picking_complete_amount) ? $result->picking_complete_amount : 0;
                                            $cart_amount = isset($result->cart_amt) ? $result->cart_amt : 0;

//                                            if (!isset($aa[$sobj->id])) {
//
//                                                $active_amount = $db->fetchObject("select sum(order_amount) as active_amount from it_ck_orders where status=1 and store_id=$sobj->id");
//
//                                                if (isset($active_amount) && trim($active_amount->active_amount) != "") {
//                                                    $active_amt = $active_amount->active_amount;
//                                                } else {
//                                                    $active_amt = 0;
//                                                }
//                                                $aa[$sobj->id] = $active_amt;
//                                            } else {
//                                                $active_amt = $aa[$sobj->id];
//                                            }

//                                            if (!isset($pa[$sobj->id])) {
//
//                                                $picking_amount = $db->fetchObject("select sum(order_amount) as picking_amount  from it_ck_orders where status=2 and store_id=$sobj->id");
//
//                                                if (isset($picking_amount) && trim($picking_amount->picking_amount) != "") {
//                                                    $picking_amt = $picking_amount->picking_amount;
//                                                } else {
//                                                    $picking_amt = 0;
//                                                }
//                                                $pa[$sobj->id] = $picking_amt;
//                                            } else {
//                                                $picking_amt = $pa[$sobj->id];
//                                            }

//                                            if (!isset($pca[$sobj->id])) {
//
//                                                $picking_complete_amount = $db->fetchObject("select sum(order_amount) as picking_complete_amount  from it_ck_orders where status=5 and store_id=$sobj->id");
//
//                                                if (isset($picking_complete_amount) && trim($picking_complete_amount->picking_complete_amount) != "") {
//                                                    $picking_complete_amt = $picking_complete_amount->picking_complete_amount;
//                                                } else {
//                                                    $picking_complete_amt = 0;
//                                                }
//                                                $pca[$sobj->id] = $picking_complete_amt;
//                                            } else {
//                                                $picking_complete_amt = $pca[$sobj->id];
//                                            }

//                                            if (!isset($ca[$sobj->id])) {
//
//                                                $cartinfoo = $db->fetchObject("select  sum(order_amount) as cart_amt from it_ck_orders where store_id=$sobj->id and status=0");
//
//                                                if (isset($cartinfoo) && trim($cartinfoo->cart_amt) != "") {
//                                                    $cart_amount = $cartinfoo->cart_amt;
//                                                } else {
//                                                    $cart_amount = 0;
//                                                }
//                                                $ca[$sobj->id] = $cart_amount;
//                                            } else {
//
//                                                $cart_amount = $ca[$sobj->id];
//                                            }




                                            if (isset($sobj) && trim($sobj->max_stock_level) != NULL && trim($sobj->max_stock_level) != 0) {
                                                if (array_key_exists($sobj->id, $store_orders)) {
                                                    $order_id = $store_orders[$sobj->id];

                                                    $pre_amt = $db->fetchObject("select sum(order_qty*MRP) as previous_amount from it_ck_orderitems where store_id=$sobj->id and order_id=$order_id ");
                                                    if (isset($pre_amt) && trim($pre_amt->previous_amount) != "") {
                                                        $pre_amount = $pre_amt->previous_amount;
                                                    } else {
                                                        $pre_amount = 0;
                                                    }

                                                    $ttt = $pre_amount + $curr_stock_val + $intransit_stock_value_new + $active_amt + $picking_amt + $picking_complete_amt + $cart_amount;

                                                    if ($ttt > $sobj->max_stock_level) {
                                                        continue;
                                                    }
                                                } else {

                                                    $tt = $curr_stock_val + $intransit_stock_value_new + $active_amt + $picking_amt + $picking_complete_amt + $cart_amount;

                                                    if ($tt > $sobj->max_stock_level) {
                                                        continue;
                                                    }
                                                }
                                            }
//                                            print "<br>QTY: $qty ";
//                                            print "<br> QTY TO PLACE ORDER: $qty ";

                                            if ($qty > 0) {

                                                if (array_key_exists($sobj->id, $store_orders)) {
                                                    $order_id = $store_orders[$sobj->id];
//                                                  print "<br><br>ORDER_ID: ".$order_id;
                                                    $release_bal_qty = insertItems($sobj, $qty, $iobj, $order_id, $release_bal_qty, $item_grn_qty, $multiple_items_released);
//                                                    print "release_bal_qty<br/>";
                                                } else {
//                                                    print "place order<br/>";
                                                    $release_bal_qty = orderCreate($sobj, $qty, $store_orders, $iobj, $release_bal_qty, $item_grn_qty, $multiple_items_released);
                                                }
                                            }


//                                                               print "<br>RELEASE QTY RESP: $release_bal_qty";
                                        }
                                    }
                                    if ($release_bal_qty == 0) {
                                        break;
                                    }
                                }//foreach store loop ends
                            }// place standing order chk ends 
                            //if release qty > 0 means qty remained even after order is placed                           
                            if ($release_bal_qty > 0) {
                                array_push($items_with_balance, $item_id);
                                //$_SESSION['items_with_balance'] = $items_with_balance;
                                //array_push($_SESSION['$items_with_balance'],$item->item_id );
                            }
                        }// item objs isset check ends here
                    } // grn n release qty check ends here
                    //activate design for manual order
                                $query = "update it_items set is_avail_manual_order=1 where id = $item_id ";
           
                                //--> code to log it_items update track
//                                $ipaddr = $_SERVER['REMOTE_ADDR'];
//                                $pg_name = __FILE__;
//                                $clsLogger->logInfo($query, false, $pg_name, $ipaddr);
                                //--> log code ends here
                                $db->execUpdate($query);
                } //items loop within design ends here
            }// all design loop ends here
        } else {


            $str = serialize($_POST);

            $Rtime_qry = "insert into release_orders set designid='$str',Release_time='$Release_time',Rel_sent=0,createtime = now()";
            $db->execInsert($Rtime_qry);
        }

        $cnt = 0;
        //code added for store order placing   
        if (trim($place_ord) == 1) {
            //step 1  : create a store sequence wise sorted arr
            //step 2  : then update their order's active time randomly n sequence
//        print "<br>PREVIOUS ORDERS ARR: <br>";
//        print_r($store_orders);

            $seq_sorted_arr = array();
            foreach ($storeobjs as $storeobj) {

                if (array_key_exists($storeobj->id, $store_orders)) {
                    $seq_sorted_arr[$storeobj->id] = $store_orders[$storeobj->id];
                }
            }

            // print "<br>SORTED ORDERS ARR: <br>";
            //fetching random datetime by fluctuating seconds
            $dt = date('Y-m-d H:i:s');

            //foreach($store_orders as $store_id => $order_id){
            foreach ($seq_sorted_arr as $store_id => $order_id) {

                $cartinfo = $clsOrders->getCartInfo($store_id);
                $min_stock = 0;
                $store_stock = 0;
                $curr_stock_val = 0;
                $intransit_stock_value_new = 0;
                $picking_complete_amt = 0;
                $picking_amt = 0;
                $active_amt = 0;
                $total_orderamt_pickcomplete = 0;
                $difference = 0;
                $is_inactive = 0;
                $order_tot_val = 0;
                $db = new DBConn();
                $msl = $db->fetchObject("select min_stock_level,max_stock_level from it_codes where id = $store_id ");
//                $db->closeConnection();
                if (isset($msl) && trim($msl->min_stock_level) != "") {
                    //step 1: fetch current order's tot val
//                    $db = new DBConn();
                    $order_val = $db->fetchObject("select sum(order_qty * MRP) as tot_amt from it_ck_orderitems where order_id=$order_id ");
//                    $db->closeConnection();
                    if (isset($order_val) && trim($order_val->tot_amt) != "") {
                        $order_tot_val = $order_val->tot_amt;
                    } else {
                        $order_tot_val = 0;
                    }
                    //step 2: fetch store current stock value
//                    $db = new DBConn();
                    $store_stock = $db->fetchObject("select sum(c.quantity * i.MRP) as curr_stock_value from it_current_stock c , it_items i where c.store_id = $store_id  and c.barcode = i.barcode and i.ctg_id not in (53,54,64,62,63,41,56,52,51,61,46,42,43,65)");
//                    $db->closeConnection();
                    if (isset($store_stock) && trim($store_stock->curr_stock_value) != "") {
                        $curr_stock_val = $store_stock->curr_stock_value;
                    } else {
                        $curr_stock_val = 0;
                    }
                    //step 3: fetch store's stock in transit

//                    $db = new DBConn();
                    //$stock_intransit_new = $db->fetchObject("select sum(i.MRP*oi.quantity) as intransit_stock_value_new from it_sp_invoices o , it_sp_invoice_items oi , it_items i where oi.invoice_id = o.id and o.invoice_type in ( 0 , 6 ) and o.store_id =$store_id  and o.is_procsdForRetail = 0 and oi.item_code = i.barcode");
                      $stock_intransit_new = $db->fetchObject("select sum(i.MRP*oi.quantity) as intransit_stock_value_new from it_sp_invoices o , it_sp_invoice_items oi , it_items i where oi.invoice_id = o.id and o.invoice_type in ( 0 , 6 ) and o.store_id =$store_id  and o.is_procsdForRetail = 0 and oi.barcode = i.barcode");
//                    $db->closeConnection();

                    if (isset($stock_intransit_new) && trim($stock_intransit_new->intransit_stock_value_new) != "") {
                        $intransit_stock_value_new = $stock_intransit_new->intransit_stock_value_new;
                    } else {
                        $intransit_stock_value_new = 0;
                    }

                    
                    //Fetch active_amount,picking_amount,picking_completed_amout
                    // Merge the queries into one
                    $query = "SELECT SUM(CASE WHEN status = 1 THEN order_amount ELSE 0 END) AS active_amount, SUM(CASE WHEN status = 2 THEN order_amount ELSE 0 END) AS picking_amount, SUM(CASE WHEN status = 5 THEN order_amount ELSE 0 END) AS picking_complete_amount FROM it_ck_orders WHERE store_id = $store_id and status in (1,2,5)";
                    $result = $db->fetchObject($query);

                    // Check if each amount is set, otherwise assign 0
                    $active_amt = isset($result->active_amount) ? $result->active_amount : 0;
                    $picking_amt = isset($result->picking_amount) ? $result->picking_amount : 0;
                    $picking_complete_amt = isset($result->picking_complete_amount) ? $result->picking_complete_amount : 0;

                    
                    //step: check active ammount from order
//                    $db = new DBConn();
//                    $active_amount = $db->fetchObject("select sum(order_amount) as active_amount from it_ck_orders where status=1 and store_id=$store_id");
////                    $db->closeConnection();
//                    if (isset($active_amount) && trim($active_amount->active_amount) != "") {
//                        $active_amt = $active_amount->active_amount;
//                    } else {
//                        $active_amt = 0;
//                    }



                    //step: check picking ammount from order
//                    $db = new DBConn();
//                    $picking_amount = $db->fetchObject("select sum(order_amount) as picking_amount  from it_ck_orders where status=2 and store_id=$store_id");
////                    $db->closeConnection();
//                    if (isset($picking_amount) && trim($picking_amount->picking_amount) != "") {
//                        $picking_amt = $picking_amount->picking_amount;
//                    } else {
//                        $picking_amt = 0;
//                    }
                    //step: check picking complete  ammount from order

//                    $db = new DBConn();
//                    $picking_complete_amount = $db->fetchObject("select sum(order_amount) as picking_complete_amount  from it_ck_orders where status=5 and store_id=$store_id");
////                    $db->closeConnection();
//                    if (isset($picking_complete_amount) && trim($picking_complete_amount->picking_complete_amount) != "") {
//                        $picking_complete_amt = $picking_complete_amount->picking_complete_amount;
//                    } else {
//                        $picking_complete_amt = 0;
//                    }

                    if (isset($msl)) {
                        $min_stock = $msl->min_stock_level;
                        $max_stock = $msl->max_stock_level;
                    }
                    $total_orderamt_pickcomplete = $active_amt + $picking_amt + $picking_complete_amt + $order_tot_val;
                    $total_stockamt=$curr_stock_val + $cartinfo->amount + $intransit_stock_value_new + $total_orderamt_pickcomplete;
                    if ($total_stockamt >= $min_stock) {

                        if (isset($msl) && trim($msl->max_stock_level) != NULL && trim($msl->max_stock_level) != 0) {
                            if ($total_stockamt >= $max_stock) {

                                $query = "select sum(oi.order_qty) as tot_qty, sum(oi.order_qty * oi.MRP) as tot_amt, count(distinct(oi.design_no)) as num_designs from it_ck_orderitems oi, it_items i where oi.order_id=$order_id and oi.item_id = i.id and i.ctg_id != 21";
//               print "<br> FINAL ITM UPDATE SEL: $query <br>";                 
                                $orderobj = $db->fetchObject($query);
//               print_r($orderobj);
                                //below code created random active time
                                $date = new DateTime($dt);
                                $date->add(new DateInterval('P0Y0M0DT0H0M' . mt_rand(1, 5) . 'S'));
                                $activedt_new = $date->format('Y-m-d H:i:s');

                                if ($orderobj && $orderobj->tot_qty && $orderobj->tot_amt && $orderobj->num_designs) {
                                    $query = "update it_ck_orders set order_qty=$orderobj->tot_qty, order_amount=$orderobj->tot_amt, num_designs=$orderobj->num_designs ,  active_time = '$activedt_new' where id=$order_id";
//                   print "<br> UPDATE PROPER: $query";
                                    $db->execUpdate($query);
                                } else {
                                    $query = "update it_ck_orders set order_qty=0, order_amount=0, num_designs=$orderobj->num_designs  , active_time = '$activedt_new' where id=$order_id and store_id=$store_id";
//                           print "<br> UPDATE IM PROPER: $query";
                                    $db->execUpdate($query);
                                }
                                //cancel this order 
                                cancelOrder($order_id);
                            } else {
                                $query = "select sum(oi.order_qty) as tot_qty, sum(oi.order_qty * oi.MRP) as tot_amt, count(distinct(oi.design_no)) as num_designs from it_ck_orderitems oi, it_items i where oi.order_id=$order_id and oi.item_id = i.id and i.ctg_id != 21";
//               print "<br> FINAL ITM UPDATE SEL: $query <br>";                 
                                $orderobj = $db->fetchObject($query);
//               print_r($orderobj);
                                //below code created random active time
                                $date = new DateTime($dt);
                                $date->add(new DateInterval('P0Y0M0DT0H0M' . mt_rand(1, 5) . 'S'));
                                $activedt_new = $date->format('Y-m-d H:i:s');

                                if ($orderobj && $orderobj->tot_qty && $orderobj->tot_amt && $orderobj->num_designs) {
                                    $query = "update it_ck_orders set order_qty=$orderobj->tot_qty, order_amount=$orderobj->tot_amt, num_designs=$orderobj->num_designs , status=" . OrderStatus::Active . " , active_time = '$activedt_new' where id=$order_id";
//                   print "<br> UPDATE PROPER: $query";
                                    $db->execUpdate($query);
                                } else {
                                    $query = "update it_ck_orders set order_qty=0, order_amount=0, num_designs=$orderobj->num_designs  , active_time = '$activedt_new' where id=$order_id and store_id=$store_id";
//                           print "<br> UPDATE IM PROPER: $query";
                                    $db->execUpdate($query);
                                }
                                $cnt++;
                                $dt = $activedt_new;
                            }
                        } else {

                            $query = "select sum(oi.order_qty) as tot_qty, sum(oi.order_qty * oi.MRP) as tot_amt, count(distinct(oi.design_no)) as num_designs from it_ck_orderitems oi, it_items i where oi.order_id=$order_id and oi.item_id = i.id and i.ctg_id != 21";
//               print "<br> FINAL ITM UPDATE SEL: $query <br>";                 
                            $orderobj = $db->fetchObject($query);
//               print_r($orderobj);
                            //below code created random active time
                            $date = new DateTime($dt);
                            $date->add(new DateInterval('P0Y0M0DT0H0M' . mt_rand(1, 5) . 'S'));
                            $activedt_new = $date->format('Y-m-d H:i:s');

                            if ($orderobj && $orderobj->tot_qty && $orderobj->tot_amt && $orderobj->num_designs) {
                                $query = "update it_ck_orders set order_qty=$orderobj->tot_qty, order_amount=$orderobj->tot_amt, num_designs=$orderobj->num_designs , status=" . OrderStatus::Active . " , active_time = '$activedt_new' where id=$order_id";
//                   print "<br> UPDATE PROPER: $query";
                                $db->execUpdate($query);
                            } else {
                                $query = "update it_ck_orders set order_qty=0, order_amount=0, num_designs=$orderobj->num_designs  , active_time = '$activedt_new' where id=$order_id and store_id=$store_id";
//                           print "<br> UPDATE IM PROPER: $query";
                                $db->execUpdate($query);
                            }
                            $cnt++;
                            $dt = $activedt_new;
                        }
                    } else {
                        $query = "select sum(oi.order_qty) as tot_qty, sum(oi.order_qty * oi.MRP) as tot_amt, count(distinct(oi.design_no)) as num_designs from it_ck_orderitems oi, it_items i where oi.order_id=$order_id and oi.item_id = i.id and i.ctg_id != 21";
//               print "<br> FINAL ITM UPDATE SEL: $query <br>";                 
                        $orderobj = $db->fetchObject($query);
//               print_r($orderobj);
                        //below code created random active time
                        $date = new DateTime($dt);
                        $date->add(new DateInterval('P0Y0M0DT0H0M' . mt_rand(1, 5) . 'S'));
                        $activedt_new = $date->format('Y-m-d H:i:s');

                        if ($orderobj && $orderobj->tot_qty && $orderobj->tot_amt && $orderobj->num_designs) {
                            $query = "update it_ck_orders set order_qty=$orderobj->tot_qty, order_amount=$orderobj->tot_amt, num_designs=$orderobj->num_designs ,  active_time = '$activedt_new' where id=$order_id";
//                   print "<br> UPDATE PROPER: $query";
                            $db->execUpdate($query);
                        } else {
                            $query = "update it_ck_orders set order_qty=0, order_amount=0, num_designs=$orderobj->num_designs  , active_time = '$activedt_new' where id=$order_id and store_id=$store_id";
//                           print "<br> UPDATE IM PROPER: $query";
                            $db->execUpdate($query);
                        }
                        //cancel this order 
                        cancelOrder($order_id);
                    }
                } else {
                    $query = "select sum(oi.order_qty) as tot_qty, sum(oi.order_qty * oi.MRP) as tot_amt, count(distinct(oi.design_no)) as num_designs from it_ck_orderitems oi, it_items i where oi.order_id=$order_id and oi.item_id = i.id and i.ctg_id != 21";
//               print "<br> FINAL ITM UPDATE SEL: $query <br>";                 
                    $orderobj = $db->fetchObject($query);
//               print_r($orderobj);
                    //below code created random active time
                    $date = new DateTime($dt);
                    $date->add(new DateInterval('P0Y0M0DT0H0M' . mt_rand(1, 5) . 'S'));
                    $activedt_new = $date->format('Y-m-d H:i:s');

                    if ($orderobj && $orderobj->tot_qty && $orderobj->tot_amt && $orderobj->num_designs) {
                        $query = "update it_ck_orders set order_qty=$orderobj->tot_qty, order_amount=$orderobj->tot_amt, num_designs=$orderobj->num_designs , status=" . OrderStatus::Active . " , active_time = '$activedt_new' where id=$order_id";
//                   print "<br> UPDATE PROPER: $query";
                        $db->execUpdate($query);
                    } else {
                        $query = "update it_ck_orders set order_qty=0, order_amount=0, num_designs=$orderobj->num_designs  , active_time = '$activedt_new' where id=$order_id and store_id=$store_id";
//                           print "<br> UPDATE IM PROPER: $query";
                        $db->execUpdate($query);
                    }
                    $cnt++;
                    $dt = $activedt_new;
                }
            }
            $success .= ". \nTotal $cnt new order(s) created";
            unset($store_orders);
        }


        // pdf creation and email code block
        // if grn release balance is there then call pdf creation code
        if (!empty($items_with_balance)) {
            // print "<br>IN ITM BAL 1 b";
            //print "<br>IN LAST FORM: ";
            if (trim($place_ord) == 1) {
                $clsGRNPDF = new GeneratePDF();
                $clsGRNPDF->genUnreleasedPDF($items_with_balance);
            }
        }else{
             //no balance qty available 
        $EmailArray = array(); // Initialize array
        $errorfpatharr = array(); // Initialize another array
        $EmailArray = array(); // Reset the array
        array_push($EmailArray, "shchaudhari@intouchrewards.com");
        array_push($EmailArray, "ranjeet.mundekar@kinglifestyle.com");
        array_push($EmailArray, "kunal.marathe@kinglifestyle.com");
        array_push($EmailArray, "rghule@intouchrewards.com");
        array_push($EmailArray, "harshada.marathe@kinglifestyle.com");
//        array_push($EmailArray, "ahatwar@intouchrewards.com");
        array_push($EmailArray, "koushik.marathe@kinglifestyle.com");
        array_push($EmailArray, "rohan.phalke@kinglifestyle.com");
        array_push($EmailArray, "prashant.mane@kinglifestyle.com");
        $errorsubject = "No balance qty available from todays design release for Linenking";
        $errorbody = "<br>Dear All,<br><br><br>";
        $errorbody .= "<p>$cnt Standing Orders placed sucessfully. and no balance qty available after standing order placed. </p>";
        $errorbody .= "<p>So, No pdf will be generated. </p>";
        $errorbody .= "<b>Note : This is auto generated email, please do not reply this email.</b><br/><br>";
        $errorbody .= "<b><br>From</b><br/>";
        $errorbody .= "<b>Linenking Portal</b><br/>";
        $errorbody .= "<b></b>";
        $errorbody .= "<br/>";
        $emailHelper = new EmailHelper();
        $errormsg = $emailHelper->send($EmailArray, $errorsubject, $errorbody, $errorfpatharr);
        }
        $EmailArray = array(); // Initialize array
        $errorfpatharr = array(); // Initialize another array
        $EmailArray = array(); // Reset the array
        array_push($EmailArray, "shchaudhari@intouchrewards.com");
        array_push($EmailArray, "ranjeet.mundekar@kinglifestyle.com");
        array_push($EmailArray, "kunal.marathe@kinglifestyle.com");
        array_push($EmailArray, "rghule@intouchrewards.com");
        array_push($EmailArray, "harshada.marathe@kinglifestyle.com");
//        array_push($EmailArray, "ahatwar@intouchrewards.com");
        array_push($EmailArray, "koushik.marathe@kinglifestyle.com");
        array_push($EmailArray, "rohan.phalke@kinglifestyle.com");
        array_push($EmailArray, "prashant.mane@kinglifestyle.com");
        $errorsubject = "Standing Order Placed for Todays Design Release of Linenking";
        $errorbody = "<br>Dear All,<br><br><br>";
        $errorbody .= "<p>$cnt Standing Orders placed sucessfully. </p>";
        $errorbody .= "<b>Note : This is auto generated email, please do not reply this email.</b><br/><br>";
        $errorbody .= "<b><br>From</b><br/>";
        $errorbody .= "<b>Linenking Portal</b><br/>";
        $errorbody .= "<b></b>";
        $errorbody .= "<br/>";
        $emailHelper = new EmailHelper();
        $errormsg = $emailHelper->send($EmailArray, $errorsubject, $errorbody, $errorfpatharr);
    } catch (Exception $xcp) {
        print $xcp->getMessage();
    }
}
$sdno = substr($dno, 0, -1);

//print "<br><br>SDNO: $sdno";
if (count($errors) > 0) {
    $_SESSION['form_errors'] = $errors;
    $redirect = "unreleased/catalog/dno=" . $sdno;
    //print "REDIRECT: ".$redirect;
} else {
    unset($_SESSION['form_errors']);
    $_SESSION['form_success'] = $success;
    $redirect = "unreleased/catalog/dno=" . $sdno;
    // print "REDIRECT: ".$redirect;
}

//print "<br><br>SDNO: $sdno";
// print "REDIRECT: ".$redirect;

//session_write_close();
header("Location: " . DEF_SITEURL . $redirect);
exit;

//print "<br><br>RESULT<br><br>";
//print "<br>ERR: ";
//print_r($errors);
//print "<br>SUCCESS MSG: ".$success;


function cancelOrder($order_id) {
    $db = new DBConn();
//    $clsLogger = new clsLogger();

    $updates = array();
    $count = 0;
    $objs = $db->fetchObjectArray("select item_id,order_qty from it_ck_orderitems where order_id = $order_id");
    foreach ($objs as $oi) {
//        $query = "select  from it_ck_items where ctg_id='$oi->ctg_id' and style_id='$oi->style_id' and size_id='$oi->size_id' and design_no='$oi->design_no' and MRP=$oi->MRP";
        $query = "select barcode,grn_qty,curr_qty,ctg_id,design_id,style_id,size_id,design_no,id,MRP from it_items where id = $oi->item_id ";
        $obj = $db->fetchObject($query);
        if (!$obj) {
            continue;
        }
        if (!isset($updates[$obj->id])) {
            $updates[$obj->id] = 0;
        }
        $updates[$obj->id] += $oi->order_qty;
        $count++;
    }
//            print "Update items\n";
    foreach ($updates as $itemid => $quantity) {
        $query = "update it_items set curr_qty = curr_qty + $quantity where id=$itemid";

        //--> code to log it_items update track
//        $ipaddr = $_SERVER['REMOTE_ADDR'];
//        $pg_name = __FILE__;
//        $clsLogger->logInfo($query, false, $pg_name, $ipaddr);
        //--> log code ends here
        $db->execUpdate($query);
    }
    $query = "update it_ck_orders set status=" . OrderStatus::Cancelled . " where id=$order_id";
    $db->execUpdate($query);
    $db->closeConnection();
}

function orderCreate($sobj, $qty, &$store_orders, $iobj, $release_bal_qty, $item_grn_qty, $multiple_items_released) {
    $db = new DBConn();
//    $clsLogger = new clsLogger();

    $store_number = $sobj->store_number;
    $order_qty_bal = $qty;
    //insert new order. 
    if (isset($store_number) && trim($store_number) != "") {
        $obj = $db->fetchObject("select order_no from it_ck_orders where store_id=$sobj->id order by id desc limit 1");
        $new_order_no = 1;
        if ($obj) {
            $new_order_no = intval(substr($obj->order_no, -3)) + 1;
        }
        if ($new_order_no == 1000) {
            $new_order_no = 1;
        }
        $order_no = $db->safe(sprintf("ST%03d%03d", $store_number, $new_order_no));
        $storequery = "select c.id as store_id,c.code,c.store_name,c.tally_name,c.accountinfo,c.owner,c.address,c.city,c.zipcode,c.phone,c.phone2,c.email,c.email2,c.vat,c.store_number,c.usertype,c.tax_type,c.server_change_id,c.username,c.a1hash,c.trust,c.password,c.createtime,c.inactive,d.dealer_discount,d.additional_discount,d.transport,d.octroi,d.cash,d.nonclaim from it_codes c left outer join it_ck_storediscount d on c.id = d.store_id where c.id = " . $sobj->id;
//        print "<br>STore QRY: $storequery ";
        $storeobj = $db->fetchObject($storequery);
        $json_str = $db->safe(json_encode($storeobj));
        // $q = "insert into it_ck_orders set store_id=$sobj->id, status=" . OrderStatus::Active . ", order_no=$order_no, order_qty=0 , store_info = $json_str , active_time = now() ";
        $q = "insert into it_ck_orders set store_id=$sobj->id, status=" . OrderStatus::StandingOrder . ", order_no=$order_no, order_qty=0 , store_info = $json_str , active_time = now() ";
//        print "<br><br>ORDER QRY: $q";
        $order_id = $db->execInsert($q);
        $store_orders[$sobj->id] = $order_id;

//        print "<br>ITEMS GRN QTY : $item_grn_qty and to order qty: $qty <br>";
        if ($qty <= $item_grn_qty) { //means a single barcode satisfies released qty units
            //insert item lines
            $design_no_db = $db->safe(trim($iobj->design_no));

            $iquery = "insert into it_ck_orderitems set order_id = $order_id , store_id = $sobj->id , item_id = $iobj->id , design_no = $design_no_db, MRP = $iobj->MRP , order_qty = $qty  , createtime = now()  ";
            //        print "<br><br>INSERT ORDER ITM: $iquery";
            $db->execInsert($iquery);

            //update it-items curr_qty
            $query = "update it_items set updatetime=now(),curr_qty=curr_qty - " . $qty . " where id=$iobj->id";
            //        print "<br><br>UPDATE ITEM QTY: $query";
            //--> code to log it_items update track
//            $ipaddr = $_SERVER['REMOTE_ADDR'];
//            $pg_name = __FILE__;
//            $clsLogger->logInfo($query, false, $pg_name, $ipaddr);
            //--> log code ends here
            $db->execUpdate($query);

            $release_bal_qty = $release_bal_qty - $qty;
        } else {
//            print "<br> INSIDE ELSE OF ORDER CREATE ARR AS BELOW: ";
//            print_r($multiple_items_released);
//            print "<br>";
            foreach ($multiple_items_released as $item_id => $value) {
                //value should be in a string as "<ctg_id>::<design_no>::<MRP>::<qty>"
//                print "<br> IOBJ ITM ID: $iobj->id and ARR ITM ID: $item_id ";

                $arr = explode("::", $value);
                $ctg_id = $arr[0];
                $design_no = $arr[1];
                $design_no_db = $db->safe(trim($design_no));
                $MRP = trim($arr[2]);
                $items_released_qty = trim($arr[3]);

                if (($iobj->ctg_id == $ctg_id) && strcmp($design_no, $iobj->design_no) == 0) {
//                     print "<br>  MATCHES SO IN IF";
                    if ($order_qty_bal > 0) {
                        if ($items_released_qty <= $qty) {
                            $to_order_qty = $items_released_qty;
                        } else {
                            $to_order_qty = $qty;
                        }

                        $iquery = "insert into it_ck_orderitems set order_id = $order_id , store_id = $sobj->id , item_id = $item_id , design_no = $design_no_db, MRP = $MRP , order_qty = $to_order_qty  , createtime = now()  ";
//                print "<br><br>INSERT ORDER ITM IN ELSE : $iquery";
                        $db->execInsert($iquery);

                        //update it-items curr_qty
                        $query = "update it_items set updatetime=now(),curr_qty=curr_qty - " . $to_order_qty . " where id=$item_id";
//                        print "<br><br>UPDATE ITEM QTY IN ELSE : $query";
                        //--> code to log it_items update track
//                        $ipaddr = $_SERVER['REMOTE_ADDR'];
//                        $pg_name = __FILE__;
//                        $clsLogger->logInfo($query, false, $pg_name, $ipaddr);
                        //--> log code ends here
                        $db->execUpdate($query);
                        $release_bal_qty = $release_bal_qty - $to_order_qty;
                        $order_qty_bal = $order_qty_bal - $to_order_qty;
                    }
                }
            }
        }

//        print "<br><br>IN FN SARR: ";
//        print_r($store_orders);
        $db->closeConnection();
        return $release_bal_qty;
    }

    return $release_bal_qty;

    $db->closeConnection();
}

function insertItems($sobj, $qty, $iobj, $order_id, $release_bal_qty, $item_grn_qty, $multiple_items_released) {
    $db = new DBConn();
//    $clsLogger = new clsLogger();
    $order_qty_bal = $qty;
    //step 1 fetch order details
//    $oquery = "select  from it_ck_orders where id = $order_id ";
//    $oobj = $oquery;
//    $order_no_db = $db->safe(trim($oobj->order_no));
//        print "<br> IN INSERT ITEMS: ITEMS GRN QTY : $item_grn_qty and to order qty: $qty <br>";
    if ($qty <= $item_grn_qty) { //means a single barcode satisfies released qty units
        //insert item lines
        $design_no_db = $db->safe(trim($iobj->design_no));
        $iquery = "insert into it_ck_orderitems set order_id = $order_id , store_id = $sobj->id , item_id = $iobj->id , design_no = $design_no_db, MRP = $iobj->MRP , order_qty = $qty , createtime = now() ";
        //    print "<br><br>INSERT ITM CASE 2 : $iquery <br>";
        $db->execInsert($iquery);

        //update it-items curr_qty
        $query = "update it_items set updatetime=now(),curr_qty=curr_qty - " . $qty . " where id=$iobj->id";
        //    print "<br><br>UPDATE ITM CASE 2 : $query <br>";
        //--> code to log it_items update track
//        $ipaddr = $_SERVER['REMOTE_ADDR'];
//        $pg_name = __FILE__;
//        $clsLogger->logInfo($query, false, $pg_name, $ipaddr);
        //--> log code ends here
        $db->execUpdate($query);

        $release_bal_qty = $release_bal_qty - $qty;
    } else {
//            print "<br> INSIDE ELSE OF INSERT ITEMS ARR AS BELOW: ";
//            print_r($multiple_items_released);
//            print "<br>";
        foreach ($multiple_items_released as $item_id => $value) {
            //value should be in a string as "<ctg_id>::<design_no>::<MRP>::<qty>"
//                print "<br> IOBJ ITM ID: $iobj->id and ARR ITM ID: $item_id ";


            $arr = explode("::", $value);
            $ctg_id = $arr[0];
            $design_no = $arr[1];
            $design_no_db = $db->safe(trim($design_no));
            $MRP = trim($arr[2]);
            $items_released_qty = trim($arr[3]);

            if (($iobj->ctg_id == $ctg_id) && strcmp($design_no, $iobj->design_no) == 0) {
//                        print "<br>  MATCHES SO IN IF";
                if ($order_qty_bal > 0) {
                    if ($items_released_qty <= $qty) {
                        $to_order_qty = $items_released_qty;
                    } else {
                        $to_order_qty = $qty;
                    }

                    $iquery = "insert into it_ck_orderitems set order_id = $order_id , store_id = $sobj->id , item_id = $item_id , design_no = $design_no_db, MRP = $MRP , order_qty = $to_order_qty  , createtime = now()  ";
//                    print "<br><br>INSERT ORDER ITM: $iquery";
                    $db->execInsert($iquery);

                    //update it-items curr_qty
                    $query = "update it_items set updatetime=now(),curr_qty=curr_qty - " . $to_order_qty . " where id=$item_id";
//                            print "<br><br>UPDATE ITEM QTY: $query";
                    //--> code to log it_items update track
//                    $ipaddr = $_SERVER['REMOTE_ADDR'];
//                    $pg_name = __FILE__;
//                    $clsLogger->logInfo($query, false, $pg_name, $ipaddr);
                    //--> log code ends here
                    $db->execUpdate($query);
                    $release_bal_qty = $release_bal_qty - $to_order_qty;
                    $order_qty_bal = $order_qty_bal - $to_order_qty;
                }
            }
        }
    }
    $db->closeConnection();
    return $release_bal_qty;
}

function grnItemsBal($iobj, $to_release_qty) {
//   print "<br>IN GRN ITEM BAL: <br>";
    $db = new DBConn();
//    $clsLogger = new clsLogger();
    $multiple_items_released = array();
    $query = "select ctg_id,design_no,id,MRP,grn_qty from it_items where ctg_id = $iobj->ctg_id and design_id = $iobj->design_id and style_id = $iobj->style_id and size_id = $iobj->size_id and grn_qty > 0 order by grn_qty desc ";
//   print "<br>ITM QRY: ".$query;
    $objs = $db->fetchObjectArray($query);
    $release_balance = $to_release_qty;
    foreach ($objs as $obj) {
        if ($release_balance > 0) {
            if ($obj->grn_qty <= $release_balance) {
                $qty = $obj->grn_qty;
            } else {
                $qty = $release_balance;
            }

            $query = "update it_items set grn_qty = grn_qty - $qty , curr_qty = curr_qty + $qty where id = $obj->id ";
//               error_log("grnr qry: $query",3,"tmp.txt");
//            print "<br>UPDATE QRY: $query<br>";
            //--> code to log it_items update track
//            $ipaddr = $_SERVER['REMOTE_ADDR'];
//            $pg_name = __FILE__;
//            $clsLogger->logInfo($query, false, $pg_name, $ipaddr);
            //--> log code ends here
            $db->execUpdate($query);

            //push details in array
            //value should be in a string as "<ctg_id>::<design_no>::<MRP>::<qty>"
            $multiple_items_released[$obj->id] = $obj->ctg_id . "::" . $obj->design_no . "::" . $obj->MRP . "::" . $qty;

            $release_balance = $release_balance - $qty;
        } else {
            break;
        }
    }
    $db->closeConnection();

    return $multiple_items_released;
}
