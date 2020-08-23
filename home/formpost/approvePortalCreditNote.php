<?php
 require_once("../../it_config.php");
require_once("session_check.php");
require_once "../lib/db/DBConn.php";

extract($_POST);
 $creditnoteid=0;
$errors=array(); 
if(isset($user_id) && $user_id !="")
{
    $userid=$user_id;
}else{$errors['userid'] = "Invalid user id";}

if(isset($creditnote_id) && $creditnote_id !="")
{ 
    $creditnoteid=$creditnote_id;
}else{$errors['creditnoteid'] = "Invalid creditnote id";}

try{
    if (count($errors) <= 0) {
    $db = new DBConn();
    $dquery="update it_portalinv_creditnote set is_approved=1,approve_dt=now() ,approve_by=$userid  where id=$creditnoteid ";
//    echo $dquery;    exit();
     $db->execUpdate($dquery);} 
    
} catch (Exception $ex) {

}
$path="";
if (count($errors) > 0) {
	$_SESSION['form_errors'] = $errors;
        $path="dg/creditnote/id=".$creditnoteid;
} else {
    unset($_SESSION['form_errors']);
    $path="approve/dgcreditnotes";
}
session_write_close();
header("Location: ".DEF_SITEURL.$path);
exit;