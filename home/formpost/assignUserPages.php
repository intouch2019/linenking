<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once 'lib/users/clsUsers.php';

extract($_GET);
$db = new DBConn();
$user = getCurrUser();
$userpage = new clsUsers();
$pagecode = $db->safe($_SESSION['pagecode']);
$page = $db->fetchObject("select * from it_pages where pagecode = $pagecode");
$errors = array();
$cnt = 0;
$success = "";
if($page){
    $allowed = $userpage->isAuthorized($user->id, $page->pagecode);
    if (!$allowed) { header("Location: ".DEF_SITEURL."unauthorized"); return; }
}else{ header("Location:".DEF_SITEURL."nopagefound"); return; }

 $allpgs = explode(",",$pgs);
// print_r($allpgs);
// print "<br/>USER:".$userid."<br/>";
try{ 
    $delqry = "delete from it_user_pages where user_id = $userid";
    //echo "<br/>".$delqry;
    $db->execQuery($delqry);
    foreach($allpgs as $pg){
        if(trim($pg)!=""){
            $iq = "insert into it_user_pages set page_id = $pg , user_id = $userid";
            $insert_id = $db->execInsert($iq);
            if($insert_id){
                $cnt++;
            }
       }
    }
}catch(Exception $xcp){
   $errors['xcp'] = $xcp->getMessage();
}
if($cnt > 0){
  $success = "Page(s) assigned successfully ";
}else{
  $errors['pg'] =  "Error during assigning pages. Contact Intouch";
}  
if (count($errors)>0) {
        unset($_SESSION['form_success']);       
        $_SESSION['form_errors'] = $errors;
  } else {
        unset($_SESSION['form_errors']);
        $_SESSION['form_success'] = $success;        
  }
  header("Location: ".DEF_SITEURL."admin/users/assignpages");
  exit;
?>
