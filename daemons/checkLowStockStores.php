<?php
include '/var/www/html/linenking/it_config.php'; //live
//include '/var/www/html/linenking/it_config.php'; //local ubuntu
//include '../it_config.php'; //local windows
require_once 'lib/db/DBConn.php';
require_once 'lib/core/Constants.php';
require_once "Classes/PHPExcel.php";
require_once 'Classes/PHPExcel/Writer/Excel2007.php';
require_once "lib/email/EmailHelper.php";

$start_date = date('Y-m-d H:i:s');
echo "<br>Execution start...<br> datetime: " . $start_date . "<br>";
//Cron Frequency daily 11.15 PM
//exit();
try {
    $db = new DBConn();
    $cnt = 0;

    $alldealersobj = $db->fetchObjectArray("select id,store_name,min_stock_level  from it_codes where usertype = ".UserType::Dealer."  and is_closed = 0 and id not in (70,147,162,168) " );  // and inactive = 0 
    
    foreach($alldealersobj as $dealerobj){ 
        if(trim($dealerobj->min_stock_level)!=""){
            //step 1: fetch current stock value
            $query = "select sum(c.quantity * i.MRP) as curr_stock_value from it_current_stock c , it_items i where c.store_id = $dealerobj->id  and c.barcode = i.barcode and i.ctg_id not in (53,54,64,62,63,41,56,52,51,61,46,42,43,65) ";
            $cobj = $db->fetchObject($query);
            if (isset($cobj) && trim($cobj->curr_stock_value) != "") {
                $store_stock_val = round($cobj->curr_stock_value);
            } else {
                $store_stock_val = 0;
            }

            //step 2: fetch intransit stock value
            $query2 = "select sum(invoice_amt) as intransit_stock_value from it_sp_invoices where  invoice_type in (0,6) and store_id = $dealerobj->id and is_procsdForRetail = 0 and i.ctg_id not in (53,54,64,62,63,41,56,52,51,61,46,42,43,65)";
            $tobj = $db->fetchObject($query2);
            if (isset($tobj) && trim($tobj->intransit_stock_value) != "") {
                $intransit_stock_val = round($tobj->intransit_stock_value);
            } else {
                $intransit_stock_val = 0;
            }
            
            //step 3: fetch active stock value
            $query3 = "select sum(order_amount) as active_amount from it_ck_orders where status in (1,2,5,6,7) and store_id=$dealerobj->id";
            $aobj = $db->fetchObject($query3);
            if (isset($aobj) && trim($aobj->active_amount) != "") {
                $active_amount = round($aobj->active_amount);
            } else {
                $active_amount = 0;
            }
            
            if (isset($dealerobj->max_stock_level) && trim($dealerobj->max_stock_level) != "") {
                $max_stock_level = $dealerobj->max_stock_level;
            } else {
                $max_stock_level = 0;
            }

            $total_stock_value = round($store_stock_val + $intransit_stock_val + $active_amount);
            
            if ($total_stock_value < $dealerobj->min_stock_level) {
                $difference = $dealerobj->min_stock_level - $total_stock_value;
//            Insert into it_stores_below_msl 
                $insertQuery = "INSERT INTO it_stores_below_msl SET store_id = $dealerobj->id, store_name = '$dealerobj->store_name', min_stock_level = $dealerobj->min_stock_level, max_stock_level = $max_stock_level, current_stock_value = $store_stock_val, intransit_stock_value = $intransit_stock_val, active_order_amount = $active_amount, total_stock_value = $total_stock_value, difference = $difference";
//                echo $insertQuery;
//                echo '<br>';
            $db->execInsert($insertQuery);
                $cnt++;
            }
        }
    }

    $end_date = date('Y-m-d H:i:s');
    echo "Execution end.<br> datetime: " . $end_date;
    echo '<br>';
} catch (Exception $xcp) {
    print $xcp->getMessage();
}

print "Total rows inserted: " . $cnt;
