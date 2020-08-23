<?php
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
require_once 'lib/core/Constants.php';

$db = new DBConn();
$countuser=0;
$countpages=0;
try{
  $query = " select id,code,usertype from it_codes where usertype in (3) and id != 71 ";
  $allManagerUsers = $db->fetchObjectArray($query);
  $query2 = "select page_id from it_user_pages where user_id = 71 ";
  $allManagerPages = $db->fetchObjectArray($query2);

  foreach($allManagerUsers as $manager){
      $user_id = $manager->id;
      $countuser++;
      foreach($allManagerPages as $page){
        $insertquery = "insert into it_user_pages set user_id = $user_id , page_id = $page->page_id ";
        //echo "<br/>Insert query:".$insertquery."<br/>";
        $inserted=$db->execInsert($insertquery);
        if($inserted){ $countpages++;}
        //$countpages++;
      }
  }
  print "\nInserted pages for ".$countuser." user(s)\n";
  print "\nTotal Inserted pages :".$countpages."\n";
}catch(Exception $xcp){
    print $xcp->getMessage();
}

?>
