<?php
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
require_once 'lib/core/Constants.php';

$db = new DBConn();
$count=0;$cnt=0;

try{
  $query = " select * from it_codes where usertype = ".UserType::Dealer; // and inactive = 0 and is_closed = 0";
  $objs = $db->fetchObjectArray($query);
  

  foreach($objs as $obj){
      //assign Store curr stock report pg
      $qry = "insert into it_user_pages set page_id = 23 , user_id = $obj->id ,createtime = now() ";
     // print "\n$qry";
      $inserted = $db->execInsert($qry);
      if($inserted > 0){
          $count++;
      }
      //assign Store sales pg
      $qry = "insert into it_user_pages set page_id = 106 , user_id = $obj->id ,createtime = now() ";
     // print "\n$qry";
      $inserted = $db->execInsert($qry);
      if($inserted){
          $count++;
      }
      $cnt++;
  }
  print "\nTot ".$count." pages assigned to $cnt user(s)\n";
  
}catch(Exception $xcp){
    print $xcp->getMessage();
}

?>
