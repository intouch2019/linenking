<?php
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
require_once 'lib/core/Constants.php';
// no user as account type in  limelight so this script not needed here
$db = new DBConn();
$countuser=0;
$countpages=0;
try{
  $query = " select id,code,usertype from it_codes where usertype in (6) and id != 170 ";
  $allAccountUsers = $db->fetchObjectArray($query);
  $query2 = "select page_id from it_user_pages where user_id = 170 ";
  $allAccountPages = $db->fetchObjectArray($query2);

  foreach($allAccountUsers as $account){
      $user_id = $account->id;
      $countuser++;
      foreach($allAccountPages as $page){
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
