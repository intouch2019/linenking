<?php
require_once "../../it_config.php";
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once 'lib/users/clsUsers.php';

extract($_POST);
$errors=array();
$success=array();
$user = getCurrUser();
$db = new DBConn();
$userpage = new clsUsers();
$pagecode = $db->safe($_SESSION['pagecode']);
$page = $db->fetchObject("select * from it_pages where pagecode = $pagecode");
if($page){
    $allowed = $userpage->isAuthorized($user->id, $page->pagecode);
    if (!$allowed) { header("Location: ".DEF_SITEURL."unauthorized"); return; }
}else{ header("Location:".DEF_SITEURL."nopagefound"); return; }


try {
//    validatePost();
    global $success, $errors, $_SESSION;
    $_SESSION['form_post']=$_POST;
    

    $category=$db->safe(trim($category));
    $_SESSION['form_design_no']=$design_no;
    $design_no = $db->safe(trim($design_no));
    
    
    if (!$design_no) { $errors['design_no']='Please specify an design number'; }
    else {
        $exist = $db->fetchObject("select * from it_ck_designs where ctg_id=$category and design_no=$design_no");
        if (!$exist) { $errors['dexist']= "the design does not exist for that number"; }
        $existfeat = $db->fetchObject ("select * from it_ck_featureddesigns where ctg_id=$category and design_no=$design_no");
        if ($existfeat) { $errors['exist']="the design is already present in the featured list"; }
    }
   
    if (count($errors) == 0) {
        $ins="insert into it_ck_featureddesigns set design_no='$exist->design_no', ctg_id='$exist->ctg_id', image='$exist->image', extension='$exist->extension'";
        $db->execInsert($ins);
        if ($ins != "-1") { $success="The design has been added to the featured list"; }
    }

} catch (Exception $xcp) {
    $clsLogger = new clsLogger();
    $clsLogger->logError("Failed to add design:".$xcp- getMessage());
    $errors['status']="There was a problem processing your request. Please try again later";
}
if (count($errors) > 0) {
    $_SESSION['form_errors'] = $errors;
}
else
{
    $_SESSION['form_success'] = $success;
}

header("Location: ".DEF_SITEURL."admin/featureddesign");
exit;
?>
