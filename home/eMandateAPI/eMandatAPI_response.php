<?php
require_once("../../it_config.php");
require_once "lib/db/DBConn.php";
$db = new DBConn();
$data="<-- ||";
foreach ($_POST as $key => $value) {
    $data .=$key ."=> ".$value. " || ";
}
$data .="-->";
  $sql='insert into it_eMandateAPI_logs set message="'.$data.'"';
$db->execInsert($sql);

//echo $data;


$checksumval=$_POST['CheckSumVal'];

$insquery='insert into emandate_response set Checksum = "'.$checksumval.'"';
$MandateRespDoc=$_POST['MandateRespDoc'];
$MandateRespDoc=str_replace("'","\"",$MandateRespDoc);

$errors=array();
$MandateRespDoc_obj=  json_decode($MandateRespDoc);
$status="";
$error_msg="";
foreach ($MandateRespDoc_obj  as $key => $value) {
   if(strnatcasecmp($key,"Status")==0){
   $insquery .=', Status="'.$value.'"'; 
   $status=$value;
   
   }
   if(strnatcasecmp($key,"MsgId")==0){
   $insquery .=', MsgId="'.$value.'"'; }
   if(strnatcasecmp($key,"RefId")==0){
   $insquery .=', RefId="'.$value.'"'; }
   if(strnatcasecmp($key,"Errors")==0){
    $errors=$value;
    foreach ($errors[0]  as $key1=>$value1) {
     if(strnatcasecmp($key1,"Error_Code")==0){
      $insquery .=', Error_Code="'.$value1.'"';
       }
     if(strnatcasecmp($key1,"Error_Message")==0){
         
     $insquery .=', Error_Message="'.$value1.'"';
     $error_msg=$value1;
       }
      }
    }
   if(strnatcasecmp($key,"Filler1")==0){
   $insquery .=', Filler1="'.$value.'"'; }
   if(strnatcasecmp($key,"Filler2")==0){
   $insquery .=', Filler2="'.$value.'"'; }
   if(strnatcasecmp($key,"Filler3")==0){
   $insquery .=', Filler3="'.$value.'"'; }
   if(strnatcasecmp($key,"Filler4")==0){
   $insquery .=', Filler4="'.$value.'"'; }
   if(strnatcasecmp($key,"Filler5")==0){
   $insquery .=', Filler5="'.$value.'"'; }
   if(strnatcasecmp($key,"Filler6")==0){
   $insquery .=', Filler6="'.$value.'"'; }
   if(strnatcasecmp($key,"Filler7")==0){
   $insquery .=', Filler7="'.$value.'"'; }
   if(strnatcasecmp($key,"Filler8")==0){
   $insquery .=', Filler8="'.$value.'"'; }
   if(strnatcasecmp($key,"Filler9")==0){
   $insquery .=', Filler9="'.$value.'"'; }
   if(strnatcasecmp($key,"Filler10")==0){
   $insquery .=', Filler10="'.$value.'"'; }
   
   
}

$insquery .=", createtime =now()";
$db->execInsert($insquery);

$redirect="admin/emandateapi";
if(strnatcasecmp($status,"Success")!=0){
	$redirect  .="/status=1/message=".$error_msg;
	
} else {
        $redirect  .="/status=0/message=RegistrationSuccessfull";
}
session_write_close();
header("Location: ".DEF_SITEURL.$redirect);
//echo "Location: ".DEF_SITEURL.$redirect;
exit;