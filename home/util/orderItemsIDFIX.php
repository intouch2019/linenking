<?php
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
require_once 'lib/core/Constants.php';
require_once "lib/serverChanges/clsServerChanges.php";
$db = new DBConn();
$count=0;
try{
    // here 7938 is the min id of it_items table
    
    $query = "select * from it_order_items where item_id < 7938";
    $allOrderItems =  $db->fetchObjectArray($query);
    foreach($allOrderItems as $orderitem){
        $itemidquery = "select id from it_items where barcode = '$orderitem->barcode'";
        $itemIDObj =  $db->fetchObject($itemidquery);
        if($itemIDObj){
          $count++;  
          $updatequery = "update it_order_items set item_id = $itemIDObj->id where id = $orderitem->id  ";
//          print "\n".$updatequery."\n";
          $db->execUpdate($updatequery);
        }        
    }
}catch(Exception $xcp){
    print $xcp->getMessage();
}
print "\n tot_updated rows = ".$count."\n";
?>
