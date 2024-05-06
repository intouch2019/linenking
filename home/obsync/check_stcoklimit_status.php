<?php

include "checkAccess.php";
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
//extract($_POST);
try {
    $store_id = $gCodeId;
//    $store_id = 182;
//    $store_id = 263;
    $intransit_stock_value_new = 0;
    $curr_stock_val = 0;
    $store_stocklimit_val = 0;
    $diff = 0;
    $db = new DBConn();
    $stock_intransit_new = $db->fetchObject("select sum(i.MRP*oi.quantity) as intransit_stock_value_new from it_sp_invoices o , it_sp_invoice_items oi , it_items i where oi.invoice_id = o.id and o.invoice_type in ( 0 , 6 ) and o.store_id =$store_id   and o.is_procsdForRetail = 0 and oi.barcode = i.barcode");

    if (isset($stock_intransit_new) && trim($stock_intransit_new->intransit_stock_value_new) != "") {
        $intransit_stock_value_new = $stock_intransit_new->intransit_stock_value_new;
    } else {
        $intransit_stock_value_new = 0;
    }

    $store_stock = $db->fetchObject("select sum(c.quantity * i.MRP) as curr_stock_value from it_current_stock c , it_items i where c.store_id = $store_id  and c.barcode = i.barcode and i.ctg_id not in (53,54,64,62,63,41,56,52,51,61,46,42,43)");

    if (isset($store_stock) && trim($store_stock->curr_stock_value) != "") {
        $curr_stock_val = $store_stock->curr_stock_value;
    } else {
        $curr_stock_val = 0;
    }


    $dealer_disc = $db->fetchObject("select dealer_discount from it_ck_storediscount where store_id=$store_id");

    if (isset($dealer_disc) && trim($dealer_disc->dealer_discount) != "") {
        $del_disc = $dealer_disc->dealer_discount;
    } else {
        $del_disc = 0;
    }


    $store_stocklimit = $db->fetchObject("select min_stock_level from it_codes where id= $store_id");

    if (isset($store_stocklimit) && trim($store_stocklimit->min_stock_level) != "") {
        $store_stocklimit_val = $store_stocklimit->min_stock_level;
    } else {
        $store_stocklimit_val = 0;
    }

    //   echo "transit stock:".$intransit_stock_value_new." Current stock:".$curr_stock_val." Stcoklimit:".$store_stocklimit_val;

    if ($del_disc == '20' || $del_disc == '20.8') {

        echo "2::";
    } else {
        if ($intransit_stock_value_new + $curr_stock_val > $store_stocklimit_val) {
            $diff = $intransit_stock_value_new + $curr_stock_val - $store_stocklimit_val;
            echo "0::" . $diff;
        } else {
            echo "1::Failed";
        }
    }



//                if($intransit_stock_value_new+$curr_stock_val >$store_stocklimit_val)
//                {
//                    $diff=$intransit_stock_value_new+$curr_stock_val-$store_stocklimit_val;
//                    echo "0::".$diff;
//                }
//                else 
//                {
//                    echo "1::Failed";
//                }


    $db->closeConnection();
} catch (Exception $ex) {
    
}