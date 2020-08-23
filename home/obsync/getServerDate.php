<?php
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
//this sync for both store n warehouse
//Used for taking curr date of server at time invoice pull at retail POS
try{
    $db = new DBConn();
    $query = "select now() as currDt";
    $obj = $db->fetchObject($query);
//    $server_date = date('Y-m-d H:i:s', strtotime($obj->currDt));
   // $time = strtotime($obj->currDt);
//    $time = strtotime(date("Y-m-d H:i:s"));
//    $dt = date('Y-m-d',$time);
//    $h =  date('h', $time);
//    $i =  date('i', $time);
//    $s = date('s', $time);
//   echo "<br/>PHP dt:-".date("Y-m-d H:i:s")."<br/>";
////  echo "<br/>".."<br/>";
//    echo "obj date: ".$obj->currDt."<br/>";
////    echo "<br/> DT:- ".$dt."<br/> HR:- ".$h."<br/>min:-".$i."<br/>sec:-".$s."<br/>";
//    $server_date = iso2ts($dt,$h,$i,$s);
//    echo "aftr convt: ".$server_date."<br/>";
//   $sdt = $db->safe(date("Y-m-d H:i:s", $server_date));
//   echo "<br/> again bk dt:- ".$sdt."<br/>";
//    print "0::".$server_date;
     print "0::".$obj->currDt;
    
}catch(Exception $xcp){
    print $xcp->getMessage();
}

function iso2ts ($iso, $hour=12, $min=0, $sec=0) 
  {
    $d = substr($iso, 8, 2);
    $m = substr($iso, 5, 2);
    $y = substr($iso, 0, 4);
    return mktime($hour, $min, $sec, $m, $d, $y);
  }
  

?>
