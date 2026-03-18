<?php
ini_set('max_execution_time', 60);
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once 'lib/users/clsUsers.php';
require_once 'lib/core/Constants.php';
//require_once "lib/serverChanges/clsServerChanges.php";

extract($_POST);
//print_r($_POST);exit();
       	$db = new DBConn(); 
$a = $_POST['new_store'];
$b = $_POST['old_store'];
$errors=array();


$old_stores = implode(",", $b);

//print_r($old_stores);exit();
if($a == "-1"){
     $errors["new_store"]='Select New Store';
}
else if($b == "-1"){
         $errors["old_store"]='Select Old Store';
}
 else {
            $query = "update it_codes set old_id = '$old_stores' where id = $a";
//            print_r($query);exit();
            $is_updated = $db->execUpdate($query);
                $success = 'Status changed';
                }
                if (count($errors) > 0) {
	$_SESSION['form_errors'] = $errors;
	$redirect = "old/store/map";
} else {
	unset($_SESSION['form_errors']);
        $_SESSION['form_success'] = $success;
	$redirect = "old/store/map";
}
session_write_close();
header("Location: ".DEF_SITEURL.$redirect);
    
    