<?php
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/serverChanges/clsServerChanges.php";

$db = new DBConn();
$serverCh = new clsServerChanges();
$count = 0;

try{    
    $query = "select c.id as store_id,c.code,c.store_name,c.tally_name,c.accountinfo,c.owner,c.address,c.city,c.zipcode,c.phone,c.phone2,c.email,c.email2,c.vat,c.store_number,c.usertype,c.tax_type,c.server_change_id,c.username,c.a1hash,c.trust,c.password,c.createtime,c.inactive,d.dealer_discount,d.additional_discount,d.transport,d.octroi,d.cash,d.nonclaim from it_codes c left outer join it_ck_storediscount d on c.id = d.store_id where c.usertype = ".UserType::Dealer." group by c.id ";
    $objs = $db->fetchObjectArray($query);
    foreach($objs as $obj){
        $count++;
       // echo "<br/>Inserted:-".$count."store".$obj->id."<br/>";
        $server_ch = "[".json_encode($obj)."]";
        $data_id = $obj->store_id;
        $ser_type = changeType::store;
        $store_id = DEF_WAREHOUSE_ID;
        $serverCh->save($ser_type, $server_ch, $store_id,$data_id);
        $store_id = $obj->store_id;
        $serverCh->save($ser_type, $server_ch, $store_id,$data_id);
    }
    
}catch(Exception $xcp){
    print $xcp;
}
print "0::success";
print "\n Total no stores:- ".$count;
?>
