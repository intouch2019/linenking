<?php
require_once "../../it_config.php";
require_once "lib/logger/clsLogger.php";
require_once "lib/db/DBConn.php";

$records="C141500001<>0<>1396366904000<>72841.0<>102.0<>140430.0<>67406.4<>3651.18<>3468.6210000000005<>Axis::RTGS<>Manager<==>164<==>8900000172597<>SLIM SHIRT<>1395.0<>1.0<>1395.0<++>8900000099825<>SLIM SHIRT<>1495.0<>1.0<>1495.0<++>8900000130078<>SLIM SHIRT<>1295.0<>1.0<>1295.0<++>8900000172566<>SLIM SHIRT<>1395.0<>1.0<>1395.0<++>8900000207510<>FORMAL SHIRT<>1695.0<>1.0<>1695.0<++>8900000215751<>SLIM SHIRT<>1495.0<>1.0<>1495.0<++>8900000217762<>SLIM SHIRT<>1595.0<>1.0<>1595.0<++>8900000205226<>SLIM SHIRT<>1795.0<>1.0<>1795.0<++>8900000217748<>SLIM SHIRT<>1595.0<>1.0<>1595.0<++>8900000164738<>SLIM SHIRT<>1495.0<>1.0<>1495.0<++>8900000214556<>FORMAL SHIRT<>1695.0<>1.0<>1695.0<++>8900000223046<>FORMAL SHIRT<>595.0<>1.0<>595.0<++>8900000227099<>FORMAL SHIRT<>595.0<>1.0<>595.0<++>8900000221189<>SLIM SHIRT<>1795.0<>1.0<>1795.0<++>8900000165254<>SLIM SHIRT<>1795.0<>1.0<>1795.0<++>8900000255474<>FORMAL SHIRT<>1295.0<>1.0<>1295.0<++>8900000177776<>SLIM SHIRT<>1795.0<>1.0<>1795.0<++>8900000230044<>SLIM SHIRT<>1595.0<>1.0<>1595.0<++>8900000221417<>SLIM SHIRT<>1795.0<>1.0<>1795.0<++>8900000224241<>SLIM SHIRT<>1395.0<>1.0<>1395.0<++>8900000230051<>SLIM SHIRT<>1595.0<>1.0<>1595.0<++>8900000164752<>SLIM SHIRT<>1495.0<>1.0<>1495.0<++>8900000130122<>FORMAL SHIRT<>1295.0<>1.0<>1295.0<++>8900000224265<>SLIM SHIRT<>1395.0<>1.0<>1395.0<++>8900000101429<>FORMAL SHIRT<>595.0<>1.0<>595.0<++>8900000217403<>SLIM SHIRT<>1595.0<>2.0<>3190.0<++>8900000208425<>FORMAL SHIRT<>1695.0<>1.0<>1695.0<++>8900000223787<>FORMAL SHIRT<>645.0<>1.0<>645.0<++>8900000170340<>FORMAL SHIRT<>1595.0<>1.0<>1595.0<++>8900000170524<>SLIM SHIRT<>1595.0<>1.0<>1595.0<++>8900000118953<>FORMAL SHIRT<>1395.0<>1.0<>1395.0<++>8900000214587<>FORMAL SHIRT<>695.0<>1.0<>695.0<++>8900000099825<>SLIM SHIRT<>1495.0<>2.0<>2990.0<++>8900000094189<>SHORT KURTA<>1625.0<>1.0<>1625.0<++>8900000106486<>FORMAL SHIRT<>1695.0<>1.0<>1695.0<++>8900000231034<>SLIM SHIRT<>1695.0<>1.0<>1695.0<++>8900000099818<>SLIM SHIRT<>1495.0<>3.0<>4485.0<++>8900000213160<>FORMAL SHIRT<>1695.0<>1.0<>1695.0<++>8900000209415<>FORMAL SHIRT<>1595.0<>1.0<>1595.0<++>8900000171910<>SLIM SHIRT<>1595.0<>1.0<>1595.0<++>8900000224685<>FORMAL SHIRT<>1495.0<>1.0<>1495.0<++>8900000153251<>FORMAL SHIRT<>1595.0<>1.0<>1595.0<++>8900000172559<>SLIM SHIRT<>1395.0<>1.0<>1395.0<++>8900000183333<>TROUSER<>1395.0<>1.0<>1395.0<++>8900000255276<>TROUSER<>895.0<>1.0<>895.0<++>8900000228805<>TROUSER<>795.0<>1.0<>795.0<++>8900000252923<>TROUSER<>895.0<>1.0<>895.0<++>8900000252916<>TROUSER<>895.0<>1.0<>895.0<++>8900000050871<>TROUSER<>1195.0<>1.0<>1195.0<++>8900000252428<>TROUSER<>895.0<>1.0<>895.0<++>8900000241248<>TROUSER<>995.0<>1.0<>995.0<++>8900000252350<>TROUSER<>895.0<>1.0<>895.0<++>8900000252367<>TROUSER<>895.0<>1.0<>895.0<++>8900000242177<>TROUSER<>995.0<>1.0<>995.0<++>8900000242153<>TROUSER<>995.0<>1.0<>995.0<++>8900000254811<>TROUSER<>895.0<>1.0<>895.0<++>8900000253340<>TROUSER<>995.0<>1.0<>995.0<++>8900000235520<>TROUSER<>895.0<>1.0<>895.0<++>8900000252718<>FORMAL SHIRT<>1695.0<>1.0<>1695.0<++>8900000252688<>FORMAL SHIRT<>1695.0<>1.0<>1695.0<++>8900000163656<>SLIM SHIRT<>1495.0<>2.0<>2990.0<++>8900000163663<>SLIM SHIRT<>1495.0<>1.0<>1495.0<++>8900000227921<>SLIM SHIRT<>1695.0<>1.0<>1695.0<++>8900000225644<>SLIM SHIRT<>1395.0<>1.0<>1395.0<++>8900000169122<>SLIM SHIRT<>1795.0<>1.0<>1795.0<++>8900000116454<>SLIM SHIRT<>1295.0<>1.0<>1295.0<++>8900000225330<>SLIM SHIRT<>1395.0<>1.0<>1395.0<++>8900000165025<>SLIM SHIRT<>995.0<>1.0<>995.0<++>8900000118861<>SLIM SHIRT<>1495.0<>1.0<>1495.0<++>8900000116447<>SLIM SHIRT<>1295.0<>1.0<>1295.0<++>8900000110735<>FORMAL SHIRT<>1295.0<>1.0<>1295.0<++>8900000228126<>FORMAL SHIRT<>645.0<>1.0<>645.0<++>8900000118830<>SLIM SHIRT<>1495.0<>1.0<>1495.0<++>8900000216109<>T-Shirt<>595.0<>1.0<>595.0<++>8900000033713<>FORMAL SHIRT<>595.0<>1.0<>595.0<++>8900000157532<>SLIM SHIRT<>2250.0<>1.0<>2250.0<++>8900000236107<>TROUSER<>795.0<>1.0<>795.0<++>8900000218097<>TROUSER<>1895.0<>1.0<>1895.0<++>8900000105922<>TROUSER<>1895.0<>1.0<>1895.0<++>8900000208586<>TROUSER<>1295.0<>1.0<>1295.0<++>8900000226849<>TROUSER<>895.0<>1.0<>895.0<++>8900000239092<>TROUSER<>2050.0<>1.0<>2050.0<++>8900000184729<>TROUSER<>1895.0<>1.0<>1895.0<++>8900000179312<>TROUSER<>1595.0<>1.0<>1595.0<++>8900000233670<>TROUSER<>1895.0<>1.0<>1895.0<++>8900000233687<>TROUSER<>1895.0<>1.0<>1895.0<++>8900000237807<>TROUSER<>1595.0<>1.0<>1595.0<++>8900000167579<>TROUSER<>1895.0<>1.0<>1895.0<++>8900000167593<>TROUSER<>1895.0<>1.0<>1895.0<++>8900000241033<>TROUSER<>995.0<>1.0<>995.0<++>8900000167586<>TROUSER<>1895.0<>1.0<>1895.0<++>8900000226528<>TROUSER<>995.0<>1.0<>995.0<++>8900000246281<>TROUSER<>895.0<>1.0<>895.0<++>8900000252343<>TROUSER<>895.0<>1.0<>895.0<++>8900000255115<>JEANS<>1295.0<>1.0<>1295.0<++>8900000255122<>JEANS<>1295.0<>1.0<>1295.0<++>8900000255191<>JEANS<>1395.0<>1.0<>1395.0<++><==>1199|||||";
print "$records\n";
//$records=urldecode($records);
try {
    $db = new DBConn();
    $arr = explode("|||||",$records);
          
    foreach ($arr as $record) {
            $invoice_id=false; 
            $store_id="";
            $tot_qty=0;
            $tot_amt=0;
            $items=array(); 
            if (trim($record) == "") { continue; }
            $invRecord = explode("<==>", $record);
            if (count($invRecord) == 0) { continue; }
            $invHeader = $invRecord[0];
            $invfields = explode("<>",$invHeader);
            if ($invfields) {
                    $invoice_no = $db->safe($invfields[0]);
                    $exists = $db->fetchObject("select * from it_invoices where invoice_no=$invoice_no");
                    if ($exists) break; // stop if invoice_no already exists
                    $invoice_type = $db->safe($invfields[1]);
//                    $invoice_dt = $db->safe($invfields[2]);
                    $dt = $invfields[2];
                    $dt /= 1000;
                    $invoice_dt = $db->safe(date("Y-m-d H:i:s", $dt));
                    $invoice_amt = floatval($invfields[3]);		
                    $invoice_qty = intval($invfields[4]);
                    $inv_store_id = $invfields[5];
                    $challan_count = $db->safe($invfields[6]);
                    $challans = $db->safe($invfields[7]);
                    $challan_nos = $challan_count."<>".$challans;
                    //challan =>9
                    $query = "insert into it_invoices set invoice_no=$invoice_no, invoice_dt=$invoice_dt, invoice_type=$invoice_type, invoice_amt=$invoice_amt, invoice_qty=$invoice_qty,store_id = $inv_store_id ,challan_nos = $challan_nos ";
print "$query\n";
//@@                    $invoice_id = $db->execInsert($query);
                    if (!$invoice_id) { break; }
            }
            $store_id = $invRecord[1];
            $query = " update it_invoices set store_id = $store_id where id = $invoice_id ";
//@@            $db->execUpdate($query);
            $itemlines = explode("<++>", $invRecord[2]);
           
            foreach ($itemlines as $currlineitem) {
                        $currlineitem=trim($currlineitem);
                        if ($currlineitem == "") { continue; }
                        $fields = explode("<>", $currlineitem );
                        $ck_code = $db->safe($fields[0]);
    //		$sp_code = $db->safe($fields[1]);
                        $price = floatval($fields[2]);
                        $quantity = intval($fields[3]);
                        $tot_qty += $quantity;
                        $tot_amt += ($price * $quantity);
                        $query = "insert into it_invoice_items set invoice_id=$invoice_id, item_code=$ck_code,  price=$price, quantity=$quantity";
print "$query\n";
//@@                       $inserted = $db->execInsert($query);
//                        if (!$inserted) { break; }
                        $items[$ck_code] = $quantity;
           }
           $pickingId = $invRecord[3];
           
           // stock balance code below
           if(trim($pickingId) != ""){
               $query = "select order_nos from it_ck_pickgroup where id = $pickingId";
               $orderObj = $db->fetchObject($query);    
               //$order_nos = explode(",", $orders);
               $order_nos = explode(",", $orderObj->order_nos);
                foreach($order_nos as $o_no){ // adding orderred qty to it_items
                    $orderNo = $db->safe($o_no);
                    $query = "select * from it_ck_orders where order_no = $orderNo ";
                    $obj = $db->fetchObject($query);
                    $query1 = "select * from it_ck_orderitems where order_id = $obj->id and store_id = $store_id ";
                    $orderItemsObj = $db->fetchObjectArray($query1);
                    foreach($orderItemsObj as $orderItem){
                        $query2 = "update it_items set curr_qty = curr_qty + $orderItem->order_qty where id = $orderItem->item_id ";
print "$query2\n";
//@@                        $db->execUpdate($query2);
                    }
                }
           }
//           print_r($items);
           foreach($items as $key => $value){  // sub inv_items qty from it_items
              // $barcode = $db->safe($key);
               $query3 = " update it_items set curr_qty = curr_qty - $value where barcode = $key ";
//              print($query3);
//@@               $db->execUpdate("update it_items set curr_qty = curr_qty - $value where barcode = $key");
                 print($query3);
               
           }
           unset($items);
           if ($invoice_id) {
//@@            $db->execUpdate("update it_invoices set invoice_amt=$invoice_amt, invoice_qty=$invoice_qty where id=$invoice_id");
           }
    }
    
//    $db->closeConnection();
    print "0::Success";
} catch (Exception $ex) {
	print "1::Error-".$ex->getMessage();
}
//}
?>
