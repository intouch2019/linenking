<?php
require_once "../../it_config.php";
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once 'lib/users/clsUsers.php';
require_once 'lib/core/Constants.php';

extract($_POST);
//print_r($_POST);
$redirect="";
$errors = array();
$success = array();
$user = getCurrUser();
$db = new DBConn();

if(!isset($_POST['sel_cat']) || $_POST['sel_cat']==0){
    $errors[] = "Please select category";
}else{
    $redirect = "/ctgid=".$_POST['sel_cat'];
}
if(!isset($_POST['exdesigns']) || count($_POST['exdesigns'])==0){
   $errors[] = "Please select 1 or many designs " ;
}
try{
    if(count($errors)==0){
        $ctg_id = $_POST['sel_cat'];
        foreach($exdesigns as $key => $design_id){
           $query = "update it_store_ratios set is_exceptional = 0 , is_exceptional_active = 0 where ctg_id = $ctg_id and design_id = $design_id " ;
           $db->execUpdate($query);
           $success = " Design(s) removed from execptional list successfully ";
        }
    }
    
}catch(Exception $xcp){
   print $xcp->getMessage();
}
;
if (count($errors) > 0) {
    $_SESSION['form_errors'] = $errors;
} else {
    
    $_SESSION['form_success'] = $success;
}
//print_r($errors);

//print "Location: " . DEF_SITEURL . "exceptional/designs".$redirect;

header("Location: " . DEF_SITEURL . "exceptional/designs".$redirect);
exit;
