<?php
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
require_once("session_check.php");
require_once "lib/core/Constants.php";
require_once 'lib/users/clsUsers.php';

extract($_GET);
$db=new DBConn();
$store = getCurrUser();
$db = new DBConn();
$userpage = new clsUsers();
$pagecode = $db->safe($_SESSION['pagecode']);
$page = $db->fetchObject("select * from it_pages where pagecode = $pagecode");
if($page){
    $allowed = $userpage->isAuthorized($store->id, $page->pagecode);
    if (!$allowed) { header("Location: ".DEF_SITEURL."unauthorized"); return; }
}else{ header("Location:".DEF_SITEURL."nopagefound"); return; }
$msg="";
$query = "update it_codes set macaddress = null where id = $id";
$updated=$db->execUpdate($query);
if($updated>0){
   $msg = "License enable successfully"; 
}

echo $msg;
?>
