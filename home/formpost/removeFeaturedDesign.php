<?php
require_once "../../it_config.php";
require_once("session_check.php");
require_once "lib/db/DBConn.php";

//print_r ($_POST);
extract($_POST);
$errors=array();
$success=array();


try {
//    validatePost();
    global $success, $errors;
    $db = new DBConn();

    $category=$db->safe(trim($category));
    $design_no = $db->safe(trim($design_no));
    
    
    if (!$design_no) { $errors['design_no']='Design Number General Error'; }
    else {
        $exist = $db->fetchObject ("select * from it_ck_featureddesigns where ctg_id=$category and design_no=$design_no");
        if (!$exist) { $errors['exist']="the design was not found in the featured list"; }
    }
   
    if (count($errors) == 0) {
        $del="delete from it_ck_featureddesigns where design_no='$exist->design_no' and ctg_id='$exist->ctg_id'";
        $db->execQuery($del);
        $success="The design has been removed from the featured list";
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
