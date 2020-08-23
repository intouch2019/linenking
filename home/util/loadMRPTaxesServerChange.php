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

		$query = "select * from it_mrp_taxes";
		$obj_mrp = $db->fetchObjectArray($query);
		  foreach($obj_mrp as $obj){
			if(isset($obj) && !empty($obj) && $obj != null){
				   $server = json_encode($obj);
				   $server_ch = "[".$server."]"; // converting n storing in obj format so that easy retrival at pos side                    );
				   $ser_type = changeType::mrptaxes;                                                         
				   //$serverCh->save($ser_type, $server_ch,$store_id,$obj->id);  
				   $serverCh->insert($ser_type, $server_ch ,$obj->id);
			}
		  }

              $cnt++;
   
    
}catch(Exception $xcp){
   $xcp->getMessage();  
}