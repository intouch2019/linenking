<?php
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
require_once 'lib/core/Constants.php';
require_once "lib/serverChanges/clsServerChanges.php";


try{
   $db = new DBConn();
   $serverCh = new clsServerChanges();
   $cnt = 0;
   $commit = 0;
   $commit = isset($argv[1]) && trim($argv[1])!="" ? trim($argv[1]) : "";
   $duplicatecnt = 0 ;
   $query = "select distinct mrp from it_items ";
   
   $objs = $db->fetchObjectArray($query);
   
   foreach($objs as $obj){
      if(isset($obj) && !empty($obj) && $obj!= null){
          $query = "select * from it_mrp_taxes where mrp = $obj->mrp ";
          $eobj = $db->fetchObject($query);
          if(isset($eobj) && !empty($eobj) && $eobj!= null){
             $duplicatecnt ++; 
          }else{
              //insert 
              if(trim($obj->mrp) <= "1050"){
                    $tax_name = "GST 5%";
                    $tax_percent = "5";
                    $tax_rate = 0.05;
                    $validfrom = "2017-07-01 00:00:00";
              }else{
                    $tax_name = "GST 12%";
                    $tax_percent = "12";
                    $tax_rate = 0.12;
                    $validfrom = "2017-07-01 00:00:00";
              }
              
              $query = "insert into it_mrp_taxes set mrp = $obj->mrp , tax_name = '$tax_name' , tax_percent = $tax_percent , tax_rate = $tax_rate , validfrom = '$validfrom' , createtime = now() ";
              print "\n".$query;
              if(trim($commit)==1){
                $inserted_id = $db->execInsert($query);  
                //push to server changes
                $query = "select * from it_mrp_taxes where id = $inserted_id ";
                $obj = $db->fetchObject($query);
                if(isset($obj) && !empty($obj) && $obj != null){
                       $server = json_encode($obj);
                       $server_ch = "[".$server."]"; // converting n storing in obj format so that easy retrival at pos side                    );
                       $ser_type = changeType::mrptaxes;                                                         
                       //$serverCh->save($ser_type, $server_ch,$store_id,$obj->id);  
                       $serverCh->insert($ser_type, $server_ch ,$obj->id);
                }
              }
              $cnt++;
              
          }
      } 
   }
   
    
}catch(Exception $xcp){
   $xcp->getMessage();  
}

if(trim($commit)==1){
  print "\n Changes committed !!";
  print "\n Tot rows inserted: $cnt ";
  print "\n Tot duplicate cnt: $duplicatecnt ";
    
}else{
  print "\n Tot rows that will get inserted: $cnt ";
  print "\n Tot duplicate cnt: $duplicatecnt ";
}

