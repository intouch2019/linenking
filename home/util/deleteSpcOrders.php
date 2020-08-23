<?php
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
//require_once "lib/grnPDFClass/GeneratePDF.php";
//print "here";



$db = new DBConn();
//$fh = fopen($argv[1],"r");
$store_id = null;
$store_id = isset($argv[1]) ? $argv[1] : null;
$commit = 0;
$commit = isset($argv[2]) ? $argv[2] : 0;
$cnt=0;
$skipped_cnt = 0;
$skipped = array();
//$s
if (trim($store_id)=="") { print "Please provide valid storeid \n"; return; }


try{
       $db = new DBConn();
       //fetch stores orders after Tuesday i.e. (08-11-2016)
       $query = "select * from it_orders where store_id = $store_id and bill_datetime > '2016-11-08 00:00:00'";
       print "\n$query\n";
       $result = $db->execQuery($query);
       
       while($obj = $result->fetch_object()){
           if(isset($obj) && !empty($obj) && $obj != null){
                $cnt++;
                //fetch & delete items
                $dquery1 = "delete from it_order_items where store_id = $store_id and order_id = $obj->id "; 
                print "\n$dquery1";
                //print "\n$dquery2";
                $dquery2 = "delete from it_orders where id = $obj->id ";
		print "\n$dquery2";
                if(trim($commit)==1){
                    $db->execQuery($dquery1);
                    //now delete orders
                    $db->execQuery($dquery2);
                }
           }
       }
       
}catch(Exception $xcp){
    print $xcp->getMessage();
}

if(trim($commit)==1){
  print "\nChanges Committed \n"  ;
  print "\nTot orders deleted $cnt";  
  
}else{  
   print "\nTot orders that will get deleted $cnt";  
}
