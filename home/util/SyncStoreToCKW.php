<?php
require_once "../../it_config.php";
require_once "lib/logger/clsLogger.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once "lib/serverChanges/clsServerChanges.php";
require_once "lib/logger/clsLogger.php";

$db = new DBConn();
$serverCh = new clsServerChanges();

//$query="select id from it_codes where usertype=4";
$query="select id from it_codes where usertype=4";
$objs=$db->fetchObjectArray($query);
foreach ($objs as $obj){
$storeids=$obj->id;    
$query = "select c.id as store_id,c.code,c.store_name,c.tally_name,c.accountinfo,c.owner,c.address,c.city,c.zipcode,c.phone,c.phone2,c.email,c.email2,c.vat,c.gstin_no,c.store_number,c.usertype,c.pancard_no,c.tax_type,c.server_change_id,c.username,c.a1hash,c.trust,c.password,c.createtime,c.inactive,d.dealer_discount,d.additional_discount,d.transport,d.octroi,d.cash,d.nonclaim from it_codes c left outer join it_ck_storediscount d on c.id = d.store_id where c.id = $storeids";
            $obj = $db->fetchObject($query);
            print_r($storeids);
            $server_ch = "[".json_encode($obj)."]";
            $ser_type = changeType::store;
            $store_id = DEF_CK_WAREHOUSE_ID;
           // here obj->store_id is  the id of table it_codes so it will become the data_id.
            $serverCh->save($ser_type, $server_ch,$store_id,$obj->store_id);
}
