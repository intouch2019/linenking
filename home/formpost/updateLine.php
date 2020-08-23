<?php
require_once "../../it_config.php";
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php"; 
require_once "lib/serverChanges/clsServerChanges.php";
require_once 'lib/users/clsUsers.php';
//print_r ($_POST);
extract($_POST);
$store = getCurrUser();
$db = new DBConn();
$userpage = new clsUsers();
$pagecode = $db->safe($_SESSION['pagecode']);
$page = $db->fetchObject("select * from it_pages where pagecode = $pagecode");
if($page){
    $allowed = $userpage->isAuthorized($store->id, $page->pagecode);
    if (!$allowed) { header("Location: ".DEF_SITEURL."unauthorized"); return; }
}else{ header("Location:".DEF_SITEURL."nopagefound"); return; }


$errors=array();
$success=array();
try {
	$db = new DBConn();
        $serverCh = new clsServerChanges();
        $exist = $db->fetchObjectArray("select * from it_ck_designs where id=$designid");
	if (!$exist) {$errors['design']="selected design was not found"; }
        else {
            $lineno = $db->safe($lineno);
            $rackno = $db->safe($rackno);
            foreach ($exist as $des) {
             $update = $db->execUpdate("update it_ck_designs set lineno=$lineno, rackno=$rackno , updatetime = now() where id=$des->id");
             $obj = $db->fetchObject("select * from it_ck_designs where id = $des->id ");
             if($obj){
                $server_ch = "[".json_encode($obj)."]";
               // $ser_type = changeType::design_line_rack_updated;
                $store_id = DEF_WAREHOUSE_ID;
                $ser_type = changeType::ck_designs;               
                $serverCh->save($ser_type, $server_ch, $store_id,$obj->id);
                $ck_warehouse_id = DEF_CK_WAREHOUSE_ID;
                $serverCh->save($ser_type, $server_ch, $ck_warehouse_id,$obj->id);
             }
            }
            if ($update != "0") { $success = "The line and rack number for design updated"; }
        }
} catch (Exception $xcp) {
	$clsLogger = new clsLogger();
	$clsLogger->logError("Failed to add $fullname:".$xcp->getMessage());
	$errors['status']="There was a problem processing your request. Please try again later";
}

$dno = $exist[0]->design_no;
if (count($errors) > 0) {
	$_SESSION['form_errors'] = $errors;
        if ($designctg=="oth") { $redirect= "admin/designline/dno=other"; }
	else $redirect = "admin/designline/dno=$dno";
} else {
	unset($_SESSION['form_errors']);
        $_SESSION['form_success'] = $success;
	if ($designctg=="oth") { $redirect= "admin/designline/dno=other"; }
	else $redirect = "admin/designline/dno=$dno";
}
session_write_close();
header("Location: ".DEF_SITEURL.$redirect);
exit;

?>
