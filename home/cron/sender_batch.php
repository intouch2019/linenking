<?php
ini_set('memory_limit', '-1');
ini_set('max_execution_time', '-1');
require_once("/var/www/html/linenking/it_config.php");
//require_once("/../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/email/EmailHelper.php";


   $db = new DBConn();
   $batchh = "select file_name,batch_id from it_new_barcode_batch where is_sent =0";        
   $f_name = $db->fetchObjectArray($batchh);

   foreach ($f_name as $f_nam){
if(isset($f_nam)){
 
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://bu-fashionking-wc1wyu.truevuecloud.com/api/v1/productUpload?businessUnitId=61830fe6-e3b5-4553-bda1-cc91944cea19&apikey=eeYNNlKSGf42Aon9pLLN8cZZaw9GE8ub&delimiter=,',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => array('file'=> new CURLFILE('/var/www/html/linenking/home/cron/b_batch/'.$f_nam->file_name)),
  
  //CURLOPT_POSTFIELDS => array('file'=> new CURLFILE('C:/xampp/htdocs/ck_new_y/home/formpost/b_batch/'.$f_nam->file_name.'.csv')),
   // CURLOPT_POSTFIELDS => array('file'=> new CURLFILE('C:/xampp/htdocs/ck_new_y/home/formpost/b_batch/items190062.csv')),
  CURLOPT_HTTPHEADER => array(
    'accept: */*'
  ),
));
   
   
$response = curl_exec($curl);
   
$var = json_decode($response,true);
//echo "<pre>";
//print_r($var);
//echo "</pre>";

$stats=$var['status'];
$errar=$var['totalRecordsWithError'];
$datee1=$var['startDate'];

if($stats="UPLOADED"){

          $db = new DBConn();
  $db->execUpdate("update it_new_barcode_batch set response='$response',status='$stats',is_sent=1,updatetime=now() where batch_id='$f_nam->batch_id'");

}else{
    
      /////////////////Email send code starts here /////////

         $emailHelper = new EmailHelper();

         $toArray = array();
         $ccArray = array();     


         array_push($toArray,"djagtap@intouchrewards.com"); 
$body="";
            if(!empty($toArray)){

                $subject = "Barcode batch sending issues "; 
                $body .= "During the barcode batch uploaded issue found:";
                $body.="<h4>Batch no:$f_nam->batch_id</h4>";
                $body .= "<p>Date :$datee1<br>";
                $body .= "Status : $stats</p>";
                $body .= "response : $response</p>";
                $body .= "<p>**** This is a system generated email, Do not reply to this email. ****   </p>";

                $errormsg = $emailHelper->send($toArray, $subject, $body ,array(), $ccArray);
               // print "<br>EMAIL SENT RESP:".$errormsg;
            }
/////////////////Email send code ends here /////////
    
    
}
  
curl_close($curl);
   
echo $response;
   }
  }