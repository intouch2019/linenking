<?php
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
require_once 'lib/core/Constants.php';

$db = new DBConn();
$countuser=0;

try{
  $query = " select * from it_user_pages where page_id = 22";
  $objs = $db->fetchObjectArray($query);
  

  foreach($objs as $obj){
      $qry = "insert into it_user_pages set page_id = 106 , user_id = $obj->user_id ,createtime = now() ";
     // print "\n$qry";
      $inserted = $db->execInsert($qry);
      if($inserted){
          $countuser++;
      }
  }
  print "\nInserted pages for ".$countuser." user(s)\n";
  
}catch(Exception $xcp){
    print $xcp->getMessage();
}

?>
