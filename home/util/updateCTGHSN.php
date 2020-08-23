<?php
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
require_once 'lib/core/Constants.php';
require_once "lib/serverChanges/clsServerChanges.php";

$db = new DBConn();
$serverCh = new clsServerChanges();
print_r($argv);
$cnt = 0;
$commit = 0;
$commit = isset($argv[2]) && trim($argv[2])!="" ? trim($argv[2]) : "";

try{
   
    $fh = fopen($argv[1],"r");
    $first = 1;
    while(($data=fgetcsv($fh)) !== FALSE) {
      if(trim($first)==1){ $first= 2 ; continue; }  
      $id = $data[0];
      $name = $data[1];
      $active = $data[2];
      $hsncode = $data[3];
      
      if(trim($id)!="" && trim($name)!="" && trim($active)!="" && trim($hsncode)!=""){
         $hsncode_db = $db->safe(trim($hsncode)); 
         $query = "update it_categories set it_hsncode = $hsncode_db where id = $id ";
         if(trim($commit)==1){
             $db->execUpdate($query);
             //push to server changes
             $query = "select * from it_categories where id = $id ";
             $obj = $db->fetchObject($query);
             if(isset($obj) && !empty($obj) && $obj != null){
                    $server = json_encode($obj);
                    $server_ch = "[".$server."]"; // converting n storing in obj format so that easy retrival at pos side                    );
                    $ser_type = changeType::categories;                                   
//                    $store_id = DEF_WAREHOUSE_ID;
//                    $serverCh->save($ser_type, $server_ch,$store_id,$obj->id);  
                    $serverCh->insert($ser_type, $server_ch,$obj->id);
             }
         }
         $cnt++;
         
      }
    }
    
}catch(Execption $xcp){
   print $xcp->getMessage(); 
}

if(trim($commit)==1){
  print "\n Changes committed !!";
  print "\n Tot ctg updated: $cnt ";
    
}else{
  print "\n Tot ctg that will get updated: $cnt ";  
}
