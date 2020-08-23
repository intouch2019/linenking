<?php
include "checkAccess.php";
require_once "../../it_config.php";
require_once "lib/logger/clsLogger.php";


require_once "lib/db/DBConn.php";
//require_once "lib/core/Constants.php"; 
require_once "lib/serverChanges/clsServerChanges.php";
//require_once "lib/logger/clsLogger.php";
//echo 'hii';
extract($_POST);
//extract($_GET);
//$logger = new clsLogger();
//$logger->logInfo("record=$record");
//
//$record='[{"NAME":"abhijhama"," E":"aba111","LASTNAME":"bhua","PHONE":"8787888787","FEEDBACK":"tomh in"}]';

//print_r($record);
//return;

if (!isset($record) || trim($record) == "") {
	//$logger->logError("Missing parameter [record]:".print_r($_POST, true));
	print "1:: Missing  parameter";
	return;
}


try{
//$gCodeId= 86;
///$record=str_replace(' ', '', $record);
$record=str_replace('"', '', $record);
$record=str_replace('}', '', $record);
$record=str_replace('{', '', $record);
$record=str_replace(']', '', $record);
                
$db = new DBConn();
$serverCh = new clsServerChanges();
$clsLogger = new clsLogger();
$arr = explode(",",$record);


//echo  "record".$record;


	
        $fields = explode(":",$arr[0]);
        $name=$fields[1];
        $fields = explode(":",$arr[1]);
        $fname=$fields[1];
        $fields = explode(":",$arr[2]);
        $lname=$fields[1];
        $fields = explode(":",$arr[3]);
        $mobileno=$fields[1];
        $fields = explode(":",$arr[4]);
        $feedback=$fields[1];
        
     
        
    $checkmobileno = "select * from customerwithoutbill where cust_phone='".$mobileno."'";
       
    
    $exists=  $db->fetchObject($checkmobileno);
    
    
    if (empty($exists)) {
        
        
     $query = "insert into customerwithoutbill set store_id =$gCodeId ,cust_name='".$name."',cust_fname='".$fname."',cust_lname ='".$lname."',cust_phone='".$mobileno."',feedback='".$feedback."'";
         $db->execInsert($query);
        
        
        
        }
        
        else {
            
             $db->execUpdate("update customerwithoutbill set  store_id =$gCodeId ,cust_name='".$name."',cust_fname='".$fname."',cust_lname ='".$lname."',feedback='".$feedback."' ,createtime=now() where cust_phone='".$mobileno."' ");
            
            
        }
    
     
    
//        $query = "insert into customerwithoutbill set store_id =$gCodeId ,cust_name='".$name."',cust_fname='".$fname."',cust_lname ='".$lname."',cust_phone='".$mobileno."',feedback='".$feedback."'";
//        $db->execInsert($query);
       // echo "Query:".$query;
        
        
        
        
$db->closeConnection();
print "0::Success";


} catch (Exception $ex)
{
	print "1::Error-".$ex->getMessage();
}




?>

