<?php
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
require_once("session_check.php");
require_once "lib/core/Constants.php";
require_once 'lib/users/clsUsers.php';
//require_once "lib/serverChanges/clsServerChanges.php";

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
//$serverCh = new clsServerChanges();
if (!$category || !$design_no ) { return error("General Error - please report this"); }
$store_id = getCurrUserId();
$category = $db->safe($category);
$design_no = $db->safe($design_no);

    $exist = $db->fetchObject("select * from it_ck_designs where ctg_id=$category and design_no=$design_no");
    if ($exist) {                 
        if($active == "ACTIVE"){
           // $changed = $db->execUpdate("update it_ck_designs set active=0 where ctg_id=$category and design_no=$design_no");            
            //success("design has been deactivated");
           $changed = $db->execUpdate("update it_items set is_design_mrp_active=0 where ctg_id=$category and design_no=$design_no and mrp = $mrp ");            
            $msg = "design has been deactivated";
        } else {
            //$changed = $db->execUpdate("update it_ck_designs set active=1 where ctg_id=$category and design_no=$design_no");
            $changed = $db->execUpdate("update it_items set is_design_mrp_active=1 where ctg_id=$category and design_no=$design_no and mrp = $mrp ");   
//            success("design has been activated");
            $msg = "design has been activated";
        }
        success($msg);
    }


function error($msg) {
    echo json_encode(array(
            "error" => "1",
            "message" => $msg
            ));
}

function success($msg) {
    echo json_encode(array(
            "error" => "0",
            "message" => $msg,
            ));
}

?>
