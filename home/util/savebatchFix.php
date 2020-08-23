<?php
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";

//Note: SKIP the order_ids => 221346,221460
// As its order_items hav same item_id twice

$db = new DBConn();
$query = "select id,store_id,bill_no,orderinfo from it_orders where createtime > '2015-08-14 23:59:59' and id not in (221346,221460) "; // and id = 221862 ";
//print "<br>".$query."<br>";
$result = $db->getConnection()->query($query);
//$result = $db->fetchObjectArray($query);

//print_r($result);
$cnt=0;
$r=0;
while ($obj = $result->fetch_object()){
//foreach ($result as $obj){
    $r++;
    print "\n".$r;
//    print "<br>";
//    print_r($obj);
    $record="";$store_id="";
    $record = $obj->orderinfo;
    $store_id = $obj->store_id;
    
    if(trim($record)!="" && trim($store_id)!=""){
        $arr = explode("|||||", $record);
        foreach ($arr as $orderinfo) {
            $records = explode("<==>", $orderinfo);
            if (count($records) == 0) { continue; }
            
            //step 1: fetch order header n update qty
            list($billtype,$bill_no, $timeInMillis, $amount, $quantity, $discount_val, $discount_pct, $voucher_amt, $tax) = explode("<>", $records[0]);
            $query = "update it_orders set quantity = $quantity , updatetime = now() where id = $obj->id ";
//            print "<br>UPDATE ORDER HEADER QRY: $query<br>";
            $db->execUpdate($query);
            
            //step 2: fetch order items n update qty
            $itemlines = explode("<++>", $records[2]);
            $items=array();
            foreach ($itemlines as $currlineitem) {
                    $currlineitem=trim($currlineitem);
                    if ($currlineitem == "") { continue; }
                    list($item_id, $barcode, $price, $quantity, $discount_val, $discount_pct) = explode("<>", $currlineitem);
//                    print "\n".$item_id." ".$barcode." ".$price." ".$quantity." ".$discount_val." ".$discount_pct;
                    $query = "update it_order_items set  ";
//                    if ($item_id && trim($item_id) != "") { $query .= ", item_id=$item_id"; }
//                    if ($barcode && trim($barcode) != "") { $query .= ", barcode='$barcode'"; }
//                    if ($price && trim($price) != "") { $query .= ", price=$price"; }
                    if ($quantity && trim($quantity) != "") { $query .= " quantity=$quantity"; }
//                    if ($discount_val && trim($discount_val) != "") { $query .= ", discount_val=$discount_val"; }
//                    if ($discount_pct && trim($discount_pct) != "") { $query .= ", discount_pct=$discount_pct"; }    
                    $query .= " where order_id = $obj->id and item_id = $item_id ";
//                    print "<br>UPDATE ITEMS QRY : $query<br>";
                    $db->execUpdate($query);
            }  
            $cnt++;
        }
        
    }
}

print "\nTotal orders updated: $cnt \n";


