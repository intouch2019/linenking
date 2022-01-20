<?php

require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/serverChanges/clsServerChanges.php";
require_once "lib/logger/clsLogger.php";
require_once "lib/core/strutil.php";
extract($_POST);
//print_r($_POST);


//echo 'direcor login op';

$db = new DBConn();

$errors = array();
$success = array();


$startdate = date('Y-m-d', strtotime($from));
$enddate = date('Y-m-d', strtotime($to));
$qt1date = yymmdd($from);
$qt2date = yymmdd($to);
$parts = explode('-', $qt1date);
$yr = $parts[0];
$qtrquery = "SELECT QUARTER('$qt1date') as qt1";
$qtr2query = "SELECT QUARTER('$qt2date') as qt2";
$qt1obj = $db->fetchObject($qtrquery);
$qt2obj = $db->fetchObject($qtr2query);
$qt1 = $qt1obj->qt1;
$qt2 = $qt2obj->qt2;


if ($qt1 != $qt2) {
    $errors['nodata'] = "date range selected belongs to diff qtr,Kindly select date range from same qtr ";
       $_SESSION['form_errors'] = $errors;
	$redirect = "sales/incentive";
       //exit();
}

if ($qt1 == 1) {
   // $refdatequery = "and invoice_dt>='" . ($yr - 1) . "-11-01' and invoice_dt<='$yr-03-31'";
    $qt1=4;
  
    $stdate="$yr-01-01";
    $edate="$yr-03-31 23:59:59";
    //$enddate="31-03-$yr";
} else if ($qt1 == 2) {
   // $refdatequery = "and invoice_dt>='$yr-01-01' and invoice_dt<='$yr-06-30'";
    $qt1=1;
  
    $stdate="$yr-04-01";
    $edate="$yr-06-30 23:59:59";
    
    
} else if ($qt1 == 3) {
  //  $refdatequery = "and invoice_dt>='$yr-03-01' and invoice_dt<='$yr-09-30'";
    $qt1=2;
    
    $stdate="$yr-07-01";
    $edate="$yr-09-30 23:59:59";
    
   
} else {
   // $refdatequery = "and invoice_dt>='$yr-06-01' and invoice_dt<='$yr-12-31'";
    $qt1=3;
    $stdate="$yr-10-01";
    $edate="$yr-12-31 23:59:59";
    
   
}





if(!$store)
{
    $errors['storec'] = "store";
    $_SESSION['form_errors'] = $errors;
	$redirect = "sales/incentive";
}
if(!$user_id)
{
    $errors['storec'] = "userid";
    $_SESSION['form_errors'] = $errors;
	$redirect = "sales/incentive";
}
if(!$from)
{
    $errors['storec'] = "from";
    $_SESSION['form_errors'] = $errors;
	$redirect = "sales/incentive";
}
if(!$to)
{
    $errors['storec'] = "to";
    $_SESSION['form_errors'] = $errors;
	$redirect = "sales/incentive";
}


 $count=0;

if(!$store || !$user_id || !$from || !$to)// || !$sman || !$sincentive)
{
    $errors['storec'] = "Please enter value for all required field";
    $_SESSION['form_errors'] = $errors;
	$redirect = "sales/incentive";
        //exit(); ///
}
 else {
     $strname = "select store_name from it_codes where id = $store";
     $storename = $db->fetchObject($strname);
     $cname = "select store_name from it_codes where id = $user_id";
     $createdbyname = $db->fetchObject($cname);
     //it_sales_incentive
     $qt1 = $db->safe($qt1);
     $stdate = $db->safe($stdate);
     $edate = $db->safe($edate);
     
    $ipaddr1 = $_SERVER['REMOTE_ADDR'];
    $query1 = "select  salesman_incentive,store_incentive,createdby_id from it_sales_incentive  where store_id= $store and start_date=$stdate and end_date=$edate";
    $check = $db->fetchObject($query1);
    if(isset($check)){
    $old_salesman_incentive = "" . $check->salesman_incentive;
    $old_store_incentive= "" .$check->store_incentive;
   $oldcreatedbyid="".$check->createdby_id;

        if ($old_salesman_incentive != $sman || $old_store_incentive !=$sincentive ) {
        $query = "insert into it_sales_incentive_log (modified_by,old_salesmanincentive,new_salesmanincentive,old_storeincentive,new_storeincentive,storeid,IpAddress,user_id,quarter,old_createdby_id,start_date,end_date) values('$createdbyname->store_name',$old_salesman_incentive,$sman,$old_store_incentive,$sincentive,$store,'$ipaddr1',$user_id,$qt1,$oldcreatedbyid,$stdate,$edate)";

       // print ">>>>$query>>>";
       //exit;
        $db->execInsert($query);
        }
    }
     
     $checkinc = "select id from it_sales_incentive where store_id= $store and start_date=$stdate and end_date=$edate";
     $checkincentive = $db->fetchObject($checkinc);
    
     if(isset($checkincentive))
     {
         
         $newentryinc = "update it_sales_incentive set salesman_incentive=$sman, store_incentive= $sincentive, "
                 . "updatedby_id=$user_id, updatedby_name='$createdbyname->store_name' ,remark='$remark' ,updatetime= now()"
                 . "where store_id= $store and start_date=$stdate and end_date=$edate";
         $newentryincentive = $db->execUpdate($newentryinc);
         
     }
     else {
        
         $newentryinc = "insert into it_sales_incentive set store_id= $store, store_name= '$storename->store_name' , quarter= $qt1, "
                 . "salesman_incentive=$sman, store_incentive= $sincentive,remark='$remark' , start_date=$stdate, end_date= $edate, "
                 . "createdby_id=$user_id, createdby_name='$createdbyname->store_name'";
         $newentryincentive = $db->execInsert($newentryinc);
         if($newentryincentive){
             
             $count++;
         }
         
     }
 }

///////Actual coding Finish./////////////////

if (count($errors) > 0) {	

        $_SESSION['form_errors'] = $errors;
	$redirect = "sales/incentive";
    
}
 else {
        if($count==0){
        $success="Record Updated Succesfully";
        
        }else{
             $success="Record Inserted Succesfully";
        }
        
	unset($_SESSION['form_errors']);
        $_SESSION['form_success'] = $success;
	$redirect = "sales/incentive";	 
}
echo '$redirect';
//exit;
session_write_close();
header("Location: ".DEF_SITEURL.$redirect);//	$redirect = "sales/incentive";
//exit;