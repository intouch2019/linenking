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
    $objs = $db->fetchObjectArray("select * from it_ck_designs where design_no='K38185'");

    if(isset($objs) && !empty($objs) && $objs != null){
       foreach ($objs as $obj){
                    $server = json_encode($obj);
                    $server_ch = "[".$server."]"; // converting n storing in obj format so that easy retrival at pos side
                    $ser_type = changeType::ck_designs;
                    $serverCh->insert($ser_type, $server_ch,$obj->id);
                    $cnt++;
       }
    }
   
    
      $objj1 = $db->fetchObjectArray("select * from it_items where barcode in('8900001157647','8900001157654','8900001157661','8900001157678','8900001157838','8900001157821','8900001157814','8900001157807','8900001157630','8900001157371','8900001157326','8900001157340','8900001157333','8900001157890','8900001157876','8900001157883','8900001157869','8900001157906','8900001156404','8900001156398','8900001156428','8900001156411','8900001156770','8900001157029','8900001157012','8900001157005','8900001156756','8900001156992','8900001156749','8900001156985','8900001157791','8900001156732','8900001156763')");
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
