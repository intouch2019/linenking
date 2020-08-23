<?php
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";

$db= new DBConn();
$query= "select * from it_current_stock where style_id is null and size_id is null and ctg_id is null and design_id is null "; //and createtime > '2016-01-01 00:00:00'"; //barcode = '8902001935843'";
print "\n".$query;
$resultset= $db->execQuery($query);
$cnt=0;
$skipped_cnt = 0;
$skipped_array = array();
while ($obj=$resultset->fetch_object()){
print "\n".$cnt++;    
$barcode= $db->safe(trim( $obj->barcode ));   
$id=$obj->id;
$query= "select * from it_items where  barcode = $barcode ";
print "\n $query \n";
$itemobj= $db->fetchObject($query);   
if(isset($itemobj)){
    $updQuery="update it_current_stock set design_id = $itemobj->design_id, ctg_id = $itemobj->ctg_id , style_id = $itemobj->style_id , size_id = $itemobj->size_id where id = $id";
    $db->execUpdate($updQuery);    
}
else{
    $skipped_cnt++;
    array_push($skipped_array, $obj->barcode);
    print"<br> barcode not found <br>";
}
}

print "\nTot no skipped: $skipped_cnt \n";
print_r($skipped_array);