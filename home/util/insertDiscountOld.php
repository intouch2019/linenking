<?php

//
//require_once("../../it_config.php");
//require_once("session_check.php");
//require_once "lib/db/DBConn.php";
//require_once "lib/core/Constants.php";
//require_once "lib/serverChanges/clsServerChanges.php";
//require_once "lib/logger/clsLogger.php";
//    
//
//$db = new DBConn();
//$serverCh = new clsServerChanges();
//if ($db) {
//    //print 'Connected To Database SuccessFully...';
//    echo '<br>';
//    echo '<br>';
//} else {
//    print 'Not Connected<br>';
//}
////for lk
////set_time_limit(10000);///
//$file = fopen("lkstorediscountnew.csv", "r"); //custphone.csv
////storediscountnew.csv
////$file = fopen("storediscountnew.csv", "r");
//if($file){
//$upcnt = 0;
//$icnt = 0;
//while (($row = fgetcsv($file, 0, ",")) !== FALSE) {
//
//    $id = trim($row[0]);
//    $is_claim = trim($row[1]);
//    $is_cash = trim($row[2]);
//    $discountset = trim($row[3]);
//    $dealer_discount = trim($row[4]);
//    $cash = trim($row[5]);
//    $nonclaim = trim($row[6]);
//
//    if ($id != '') {
//        $query = "update it_codes set is_claim=$is_claim, is_cash=$is_cash, discountset=$discountset where id= $id";  //, tally_name=$tallyname
////        echo '<br>';
////        print 'UPDATING IT_CODES: '.'<br>'.$query;
////        echo '<br>';
//        $db->execUpdate($query);
//        $discquery = " update it_ck_storediscount set  dealer_discount = $dealer_discount ,additional_discount=$dealer_discount ,cash=$cash,nonclaim=$nonclaim  where store_id = $id ";
////          echo '<br>';
////        print 'UPDATING it_ck_storediscount: '.'<br>'.$discquery;
////        echo '<br>';
//        $db->execUpdate($discquery);
//        $query = "select c.id as store_id,c.code,c.store_name,c.tally_name,c.accountinfo,c.owner,c.address,c.city,c.zipcode,c.phone,c.phone2,c.email,c.email2,c.vat,c.gstin_no,c.store_number,c.usertype,c.pancard_no,c.tax_type,c.server_change_id,c.username,c.a1hash,c.trust,c.password,c.createtime,c.inactive,c.is_closed,c.is_natch_required,d.dealer_discount,c.distance,c.state_id,c.composite_billing_opted ,c.region_id,IF(c.store_type =3, 1,0) as is_companystore from it_codes c left outer join it_ck_storediscount d on c.id = d.store_id where c.id = ".$id;
//                    
//      // echo '<br>';
//        //print 'FETCH IT_CODES: '.'<br>'.$query;
////       echo '<br>';
//        $obj = $db->fetchObject($query);
//        if($obj){
//        $server_ch = "[" . json_encode($obj) . "]";
//        $ser_type = changeType::store;
//        $store_id = 84;
//        //here obj->store_id is  the id of table it_codes so it will become the data_id.
//        $serverCh->save($ser_type, $server_ch, $store_id, $obj->store_id);
//        $store_id = $id; // data for that store specifically
//        $serverCh->save($ser_type, $server_ch, $store_id, $obj->store_id);
//
//        $icnt++;
//        }
//        
//        }
//    //exit;
//}
//}
//fclose($file);
//$db->closeConnection();
//
//if($icnt != 0){
//echo '<br>';
//echo 'Successfully update it_server_changes table : ' . $icnt;
//echo '<br>';
//}
?>


