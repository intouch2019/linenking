<?php
ini_set('memory_limit', '-1');
ini_set('max_execution_time', '-1');
require_once("/var/www/html/linenking/it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/email/EmailHelper.php";


$db = new DBConn();
$batchh = "select file_name,batch_id from it_new_barcode_batch where is_sent =1";
$f_name = $db->fetchObjectArray($batchh);

foreach ($f_name as $f_nam) {
    if(isset($f_nam)){

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://bu-fashionking-wc1wyu.truevuecloud.com//api/v1/productUpload?businessUnitId=61830fe6-e3b5-4553-bda1-cc91944cea19&apikey=eeYNNlKSGf42Aon9pLLN8cZZaw9GE8ub',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
    ));

    $response = curl_exec($curl);
    $var = json_decode($response, true);

    $stats = $var['content'];

    echo "<pre>";
    print_r($var);
    echo "</pre>";

    $stas = "";
    $fname = "";
    $toterror = "";
    $datte = "";

    $db = new DBConn();

    foreach ($stats as $stat) {
        foreach ($stat as $key => $value) {
            if ($key == "status") { //file
                //        print_r($key . "=>" . $value);
                $stas = $value;
            }
            if ($key == "totalRecordsWithError") { //file
                 //       print_r($key . "=>" . $value);
                $toterror = $value;
            }
            if ($key == "startDate") { //file
                 //       print_r($key . "=>" . $value);
                $datte = $value;
            }

            if ($key == "originalFilename") {
                $fname = $value;
                $sp = preg_split("#/#", $value);
                foreach ($sp as $key => $value) {
                    if ($key == "8") {
                        //     print_r($key . "=>" . $value);
                        $fname = $value;
                    }
                }
            }
            
            if($toterror==0){
            if ($stas == "COMPLETE") {
                $db->execUpdate("update it_new_barcode_batch set status='$stas',is_sent=2,updatetime=now() where file_name='$fname'");
            }else{
                $db->execUpdate("update it_new_barcode_batch set status='$stas',updatetime=now() where file_name='$fname'");
            }
            }else{
                
                /////////////////Email send code starts here /////////

         $emailHelper = new EmailHelper();

         $toArray = array();
         $ccArray = array();     


         array_push($toArray,"djagtap@intouchrewards.com"); 
$body="";
            if(!empty($toArray)){

                $subject = "(Linenking)--Barcode batch Receiving status issues "; 
                $body .= "During the barcode batch Receiving issue found:";
                $body.="<h4>Batch no:$fname</h4>";
                $body .= "<p>Date :$datte<br>";
                $body .= "Status : $stas</p>";
                $body .= "<p>**** This is a system generated email, Do not reply to this email. ****   </p>";

                $errormsg = $emailHelper->send($toArray, $subject, $body ,array(), $ccArray);
                //print "<br>EMAIL SENT RESP:".$errormsg;
            }
/////////////////Email send code ends here /////////
            }
        }
    }

    curl_close($curl);
//print_r ($response);
}
}