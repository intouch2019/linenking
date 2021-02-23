<?php
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
require_once 'lib/core/Constants.php';

$conn = new DBConn();
if ($conn) {
    //print 'Connected To Database SuccessFully...';
    echo '<br>';
    echo '<br>';
   
      
}else{
    print 'Not Connected<br>';
} 

//set_time_limit(10000);///
//$file = fopen("ckdefectedfile.csv","r");//for ck
$file = fopen("lkdgbarcode.csv","r");//for lk

$upcnt=0;
//$icnt=0;
//$cnno="DCN-20210039";
while (($row = fgetcsv($file, 0, ",")) !== FALSE) {
    
     $dbid=trim($row[0]);
     $invoiceno=trim($row[1]);
     $sql2="select * from it_portalinv_creditnote  where  id=$dbid";
////         echo $sql2;
     $found  = $conn->fetchObject($sql2);
     if($found){
//         update it_portalinv_creditnote set invoice_no=
              $updatequery = "update it_portalinv_creditnote set invoice_no ='$invoiceno' where id=$found->id";
       $updateds=$conn->execUpdate($updatequery);
      // echo "<br/>update query:".$updatequery."<br/>";
         if($updateds){
         $upcnt++;
         }
     }
}

 
fclose($file);     
$conn->closeConnection();   
//
echo '<br>';
echo 'NO OF RECORDS ARE UPDATE ARE:'.$upcnt;
echo '<br>';

//echo '<br>';
//echo 'NO OF RECORDS ARE INSERTED ARE:'.$icnt;

?>