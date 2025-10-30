<?php

require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once 'Classes/PHPExcel/IOFactory.php';
require_once "lib/logger/clsLogger.php";


$commit = false;
$errors = array();
$success = "";
$err = "";

extract($_GET);

if (!isset($filename) && trim($filename) == "") {
    $errors['file'] = "File not found";
} else {
    $commit = true;
}

if (count($errors) == 0) {
    $db = new DBConn();
    $success .= createitems($filename);
}

if (count($errors) > 0) {
    unset($_SESSION['form_success']);
    unset($_SESSION['fpath']);
    $_SESSION['form_errors'] = $errors;
} else {
    unset($_SESSION['form_errors']);
    unset($_SESSION['fpath']);
    $_SESSION['form_success'] = $success;
    $_SESSION['orderplace'] = "done";
}

session_write_close();
header("Location: " . DEF_SITEURL . "admin/strorders");
exit;

function createitems($newdir) {
    $db = new DBConn();
    $itemfound = "";
    $objPHPExcel = PHPExcel_IOFactory::load($newdir);
    $objWorksheet = $objPHPExcel->getActiveSheet();
    $return = "";
    $first = 1;
    $code = '';
    foreach ($objWorksheet->getRowIterator() as $row) {
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false);
        $colno = 0;
        foreach ($cellIterator as $cell) {
            $value = strval($cell->getValue());
            if ($colno == 0 && !is_numeric($value)) {
                if ($first != 1) {
                    $return .= "<br/>" . saveOrder($storename, $items);
                }
                $storename = $value;
                $items = array();
                $itmcnt = 0;
                $first = 2;
            } else {
                if ($colno == 0) {
                    $code = $value;
                } else if (intval($value) > 0) {
                    $items[$itmcnt] = array('barcode' => $code, 'qty' => $value);
                    $itmcnt++;
                }
            }
            $colno++;
        }
    }
    $return = $return . "<br />" . saveOrder($storename, $items);
    return $return;
}

function saveOrder($store, $items) {
    global $commit;
    $msg = "";
    $db = new DBConn();
    $store = $db->safe($store);
    $storeinfo = $db->fetchObject("select id,store_number,inactive,is_closed,is_natch_required from it_codes where code =$store");
    if (!$storeinfo) {
        return "ERROR:Store $store not found";
    } else {
         if($storeinfo->inactive == 1){ //means store is disabled
//            return "ERROR:Store $store is disabled so order against it cannot be placed ";  
            return;
        }
        if($storeinfo->is_closed == 1){ //means store is closed
//            return "<br> ERROis_closedR:Store $store is closed so order against it cannot be placed ";  
            return;
        }
        //check if at least 1 qty exist for the items.
        $sum = 0;
        foreach ($items as $item) {
            $itemcode = $db->safe($item['barcode']);
            //echo "item".$itemcode."</br>";
//            $totalqty = $db->fetchObject("select i.ctg_id, i.curr_qty from it_items i, it_ck_designs d where i.barcode=$itemcode and i.ctg_id = d.ctg_id and i.design_no = d.design_no and d.active=1 and i.curr_qty > 0");
           // $totalqty = $db->fetchObject("select i.ctg_id, i.curr_qty, i.design_no,d.active from it_items i, it_ck_designs d where i.barcode=$itemcode and i.ctg_id = d.ctg_id and i.design_no = d.design_no  and i.curr_qty > 0");
           $totalqty = $db->fetchObject("select i.ctg_id, i.curr_qty, i.design_no,i.is_design_mrp_active from it_items i, it_ck_designs d where i.barcode=$itemcode and i.ctg_id = d.ctg_id and i.design_no = d.design_no  and i.curr_qty > 0");
            if (!$totalqty) {
                continue;
            }
           // if (!$totalqty->active) {
//              $msg .= "Design [$totalqty->design_no] is inactive \n";

            //}
            $sum += $totalqty->curr_qty;
        }
        //echo "sum".$sum."</br>";
        //if at least 1 qty exist ->continue and  create a new order in it_ck_orders
        if ($sum > 0) {
            $storeid = $storeinfo->id;
            $store_number = $storeinfo->store_number;
            //insert new order. 
            if (!$store_number) {
                return "ERROR:Store number missing for store $store.";
            }
            $obj = $db->fetchObject("select order_no from it_ck_orders where store_id=$storeid order by id desc limit 1");
            $new_order_no = 1;
            if ($obj) {
                $new_order_no = intval(substr($obj->order_no, -3)) + 1;
            }
            if ($new_order_no == 1000) {
                $new_order_no = 1;
            }
            $order_no = $db->safe(sprintf("MT%03d%03d", $store_number, $new_order_no));
            //echo "orderno".$order_no."</br>";
            $query = "insert into it_ck_orders set store_id=$storeid, status=" . OrderStatus::Active . ", order_no=$order_no, order_qty=0";
            if ($commit) {
                $storequery = "select c.id as store_id,c.code,c.store_name,c.tally_name,c.accountinfo,c.owner,c.address,c.city,c.zipcode,c.phone,c.phone2,c.email,c.email2,c.vat,c.store_number,c.usertype,c.tax_type,c.server_change_id,c.username,c.a1hash,c.trust,c.password,c.createtime,c.inactive,d.dealer_discount,d.additional_discount,d.transport,d.octroi,d.cash,d.nonclaim from it_codes c left outer join it_ck_storediscount d on c.id = d.store_id where c.id = ".$storeid;
                $storeobj = $db->fetchObject($storequery);
                $json_str = $db->safe(json_encode($storeobj));
                 
                $order_id = $db->execInsert("insert into it_ck_orders set store_id=$storeid, status=" . OrderStatus::Active . ", order_no=$order_no, order_qty=0 , store_info = $json_str ");
           
            } else {
                $order_id = "-1";
//			print "$query<br />";
            }
            //echo "orderid".$order_id."</br>";
            //
              foreach ($items as $item) {
                $itemcode = $db->safe($item['barcode']);
                $orderqty = $item['qty'];
//                  $itemdbinfo= $db->fetchObject("select i.ctg_id,i.ctg_name,i.style_id,i.style_name,i.size_id,i.size_name,i.design_no,i.curr_qty,i.MRP,d.active from it_ck_items i, it_ck_designs d where i.item_code=$itemcode and i.ctg_id=d.ctg_id and i.design_no=d.design_no and i.curr_qty > 0");
                $itemdbinfo = $db->fetchObject("select i.id as itemid, i.ctg_id,c.name as ctg_name ,c.skip_Place_Order as skipCheck , i.style_id,i.size_id,i.design_no,i.curr_qty,i.MRP,i.is_design_mrp_active from it_items i, it_ck_designs d , it_categories c where i.barcode=$itemcode and i.ctg_id=d.ctg_id and i.ctg_id = c.id and i.design_no=d.design_no and i.curr_qty > 0");
                if (!$itemdbinfo) {
                    continue;
                }
                //if (!$itemdbinfo->active) {
                    //$msg .= "Design [$itemdbinfo->ctg_name::$itemdbinfo->design_no] is inactive<br />";
//                    continue;
                //}

                if ($itemdbinfo->skipCheck == 1) {  // skip items from placing order against them
                    continue;
                }
                if ($itemdbinfo->curr_qty < $orderqty) {
                    $orderqty = $itemdbinfo->curr_qty;
                    $newstock = 0;
                } else {
                    $newstock = $itemdbinfo->curr_qty - $orderqty;
                }

                $design_no = $db->safe($itemdbinfo->design_no);
                $item_id = $itemdbinfo->itemid;
                $query = "insert into it_ck_orderitems set order_id=$order_id,store_id=$storeid,item_id = $item_id, design_no = $design_no, order_qty = $orderqty, MRP = $itemdbinfo->MRP";
                if ($commit) {
//                  $orderitem_id = $db->execInsert("insert into it_ck_orderitems set order_id=$order_id,store_id=$storeid, ctg_id=$ctg_id, ctg_name=$ctg_name,style_id=$style_id, style_name = $style_name, size_id=$size_id, size_name=$size_name, design_no = $design_no, order_qty = $orderqty, MRP = $itemdbinfo->MRP");
                    $orderitem_id = $db->execInsert($query);
                } else {
                    $orderitem_id = -1;
//		print "$query<br />";
                }
                //remove orderqty from curr_qty. 
                $query = "update it_items set curr_qty=$newstock where barcode=$itemcode";
                if ($commit) {
                    $updateitem = $db->execUpdate($query);
                    //--> code to log it_items update track
                    $clsLogger = new clsLogger();
                    $ipaddr =  $_SERVER['REMOTE_ADDR'];
                    $pg_name = __FILE__;                       
                    $clsLogger->logInfo($query,false, $pg_name,$ipaddr);
                    //--> log code ends here
                } else {
//		print "$query<br />";
                }
            }
        } else {
            return "ERROR:Either No stock available for any of the items in your order or all the items belong to inactive designs - store:$store";
        }
    }
    //get total summary from it_ck_orderitems to update it_ck_orders and it_ck_pickgroup.
    $summary = $db->fetchObject("select sum(order_qty) as tot_qty,sum(order_qty*MRP) as tot_sum,count(distinct(design_no)) as num_designs from it_ck_orderitems where order_id=$order_id and store_id=$storeid");
    //update it_ck_orders
    $query = "update it_ck_orders set order_qty =$summary->tot_qty, order_amount=$summary->tot_sum,num_designs=$summary->num_designs,active_time=now() where id=$order_id";
    if ($commit) {
        $updateord = $db->execUpdate("update it_ck_orders set order_qty =$summary->tot_qty, order_amount=$summary->tot_sum,num_designs=$summary->num_designs,active_time=now() where id=$order_id");
    } else {
//	print "$query<br />";
    }
    //insert into it_ck_pickgroup
    $query = "insert into it_ck_pickgroup set storeid=$storeid, order_ids=$order_id,order_nos=$order_no, order_qty=$summary->tot_qty, order_amount=$summary->tot_sum,num_designs=$summary->num_designs,active_time=now()";
    if ($commit) {
        $inspickgr = $db->execInsert("insert into it_ck_pickgroup set storeid=$storeid, order_ids=$order_id,order_nos=$order_no, order_qty=$summary->tot_qty, order_amount=$summary->tot_sum,num_designs=$summary->num_designs,active_time=now()");
    } else {
//	print "$query<br />";
    }
    return "Order $order_no placed for qty:$summary->tot_qty, amount:$summary->tot_sum. for store $store <br />$msg";
}