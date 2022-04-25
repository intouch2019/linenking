<?php

require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
require_once 'lib/core/Constants.php';
require_once "lib/serverChanges/clsServerChanges.php";


try{
   $db = new DBConn();
   $serverCh = new clsServerChanges();
   $cnt = 0;

//   $query = "select * from it_mrp_taxes where mrp=1545 and id=4896";
//                $obj = $db->fetchObject($query);
//                if(isset($obj) && !empty($obj) && $obj != null){
//                       $server = json_encode($obj);
//                       $server_ch = "[".$server."]"; // converting n storing in obj format so that easy retrival at pos side                    );
//                       $ser_type = changeType::mrptaxes; 
//                       $cnt++;
//                       
//                       $serverCh->insert($ser_type, $server_ch ,$obj->id);
//                }
//    
    $objs = $db->fetchObjectArray("select * from it_ck_designs where design_no='J38260'");

    if(isset($objs) && !empty($objs) && $objs != null){
       foreach ($objs as $obj){
                    $server = json_encode($obj);
                    $server_ch = "[".$server."]"; // converting n storing in obj format so that easy retrival at pos side
                    $ser_type = changeType::ck_designs;
                    $serverCh->insert($ser_type, $server_ch,$obj->id);
                    $cnt++;
       }
    }
   
    
      $objj1 = $db->fetchObjectArray("select * from it_items where barcode in('8900001181550','8900001181567','8900001181574','8900001181581')");
      $db->closeConnection();
                //$server_ch = json_encode($obj1);
                foreach ($objj1 as $obj1){
                    $server = json_encode($obj1);
                    $server_ch = "[".$server."]"; // converting n storing in obj format so that easy retrival at pos side
                    $ser_type = changeType::items;
                    $serverCh->insert($ser_type, $server_ch,$obj1->id);
                    $cnt++;
                }
   
   
   
   
   
   
}catch(Exception $xcp){
    print $xcp->getMessage();
}
print "Tot_inserted_rows: ".$cnt;
