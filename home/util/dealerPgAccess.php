<?php
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
require_once 'lib/core/Constants.php';

$db = new DBConn();
$countuser=0;
$countpages=0;
try{
  $query = " select id,code,usertype from it_codes where usertype = (4)";
  $allDealerUsers = $db->fetchObjectArray($query);
  
  foreach($allDealerUsers as $dealer){
        $user_id = $dealer->id;
        $countuser++;      
        $insertquery = "insert into it_user_pages set user_id = $user_id , page_id = 88 ";
//        echo "<br/>Insert query:".$insertquery."<br/>";
        $inserted=$db->execInsert($insertquery);
        if($inserted){ $countpages++;}
//        $countpages++;
    
  }
  print "\nInserted pages for ".$countuser." user(s)\n";
  print "\nTotal Inserted pages :".$countpages."\n";
}catch(Exception $xcp){
    print $xcp->getMessage();
}


?>
