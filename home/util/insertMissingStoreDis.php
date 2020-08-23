<?php
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
require_once 'lib/core/Constants.php';

$db = new DBConn();
$cnt = 0;
try{
    $query = " select id from it_codes where usertype = ".UserType::Dealer." and id not in (select store_id from it_ck_storediscount)";
    $objs = $db->fetchObjectArray($query);    
    foreach($objs as $obj){
        $disq = "insert into it_ck_storediscount set store_id = $obj->id , dealer_discount = 0";
        $inserted=$db->execInsert($disq);
        if($inserted){ $cnt++;}
    }
}catch(Exception $xcp){
    print $xcp->getMessage();
}
print "Tot_inserted_rows: ".$cnt;
?>
