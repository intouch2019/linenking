<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/serverChanges/clsServerChanges.php";
require_once "lib/logger/clsLogger.php";
require_once "lib/core/strutil.php";

extract($_GET);
//print_r(($_GET) );

//Array ( [storeids] => 76 [from] => 2019-04-01 [to] => 2019-06-30 [user_id] => 101 [sman] => 0.5 [sincentive] => 0.5 ) 

//print 'store incentive-'.$sincentive;
//exit;//

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
    //echo 'errot6y';
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


//
//echo "</br>";
//echo "startdate".$stdate;
//echo "</br>";
//echo "enddate".$edate;


if(!$storeids)
{
    $errors['storec'] = "storeids";
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

if(isset($storeids) && trim($storeids)!="-1"){
    $sClause = "id in ($storeids) ";
}else{
    $sClause = "";
}

if(!$storeids || !$user_id || !$from || !$to  )//|| !$sman || !$sincentive)
{
    $errors['storec'] = "Please enter value for all required field";
    $_SESSION['form_errors'] = $errors;
    $redirect = "sales/incentive";
       
}
else {
         $smans = $db->safe($sman);
         $sincentives = $db->safe($sincentive);
         $storeidscheck = "select id from it_codes where  $sClause";
         //  print $storeidscheck;
         $storeid = $db->fetchObjectArray($storeidscheck);
         $cname = "select store_name from it_codes where id = $user_id";
         $createdbyname = $db->fetchObject($cname);
         $qt1 = $db->safe($qt1);
         $stdate = $db->safe($stdate);
         $edate = $db->safe($edate);
         $storenames="";
         $store_name="";
         foreach ($storeid as $stid){
                $checkinc = "select id from it_sales_incentive where store_id =$stid->id and start_date=$stdate and end_date=$edate";
                // print $checkinc;
                $checkincentive = $db->fetchObject($checkinc);
                if(isset($checkincentive))
                 {
                 $cname = "select store_name from it_codes where id = $stid->id";
                 $storenames1 = $db->fetchObject($cname);
                 $storenames.=$storenames1->store_name.",";
                 $errors['storec'] =$storenames;

                 }
          }
   
         if (count($errors) > 0) {
             $store_name=substr($storenames, 0, -1);
             //$errors['storec'] = "This Store Records Already Inserted for \r\n $store_name.\r\n Do not insert record for this quarter";
           //  $errors['storec'] = "Records Already Inserted for [$store_name.] \n Do not insert record for this quarter";
           $errors['storec'] = "Incentive Has Been Set For This Quarter, For The Stores [$store_name] ";
             $_SESSION['form_errors'] = $errors;
          //	$redirect = "sales/incentive";

         }else{

                foreach ($storeid as $stid){

                     $sname = "select store_name from it_codes where id = $stid->id";
                     $store_name = $db->fetchObject($sname);
                     $newentryinc = "insert into it_sales_incentive set store_id= $stid->id, store_name= '$store_name->store_name' , quarter= $qt1, "
                     . "salesman_incentive=$smans, store_incentive=$sincentives, start_date=$stdate, end_date= $edate, "
                     . "createdby_id=$user_id, createdby_name='$createdbyname->store_name',remark='$remark'";
                      //print $newentryinc;
                     //echo '</br>';
                     $newentryincentive = $db->execInsert($newentryinc);
                }
        }
 }

///////Actual coding Finish/////////////////
//echo $redirect;
 //exit();
if (count($errors) > 0) {	

        $_SESSION['form_errors'] = $errors;
	$redirect = "sales/incentive";
    
}
 else {
          $success="Records Inserted Succesfully";//
	unset($_SESSION['form_errors']);
        $_SESSION['form_success'] = $success;
	$redirect = "sales/incentive";	 
}
///echo '$redirect';
//exit;
session_write_close();
header("Location: ".DEF_SITEURL.$redirect);
//exit;