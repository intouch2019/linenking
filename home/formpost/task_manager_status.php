<?php

require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once 'lib/users/clsUsers.php';

 $currUser = getCurrUser();

//print_r($_GET['id']);
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $id = trim($id);
} else {
    print "";
    return;
}

$db = new DBConn();
//echo "update companydetails set inactive=1 where id=$id";
//exit();
if (isset($_GET['id'])) {
         $record = $db->fetchObject("select progress from it_task_manager where id = $id");
    
         if(isset($record)&& $record->progress=='0' ){
             $query = "update it_task_manager set progress =50,startdate=now() where id=$id"; 
             $db->execUpdate($query);
         }else if(isset($record)&& $record->progress=='50'){
             $query = "update it_task_manager set progress =100,status=3,finisheddate=now() where id=$id"; 
                                $db->execUpdate($query);
         }
    
    
     
}
header("Location: " . DEF_SITEURL . "report/task/manager");
exit;