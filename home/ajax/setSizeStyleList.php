<?php
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
require_once("session_check.php");
require_once "lib/core/Constants.php";
require_once 'lib/users/clsUsers.php';

extract($_GET);
$store = getCurrUser();
$utype = $store->usertype;
$db = new DBConn();
$userpage = new clsUsers();
$pagecode = $db->safe($_SESSION['pagecode']);
$page = $db->fetchObject("select * from it_pages where pagecode = $pagecode");
if($page){
    $allowed = $userpage->isAuthorized($store->id, $page->pagecode);
    if (!$allowed) { header("Location: ".DEF_SITEURL."unauthorized"); return; }
}else{ header("Location:".DEF_SITEURL."nopagefound"); return; }
//if ($utype != UserType::Admin && $utype != UserType::CKAdmin) {return error("unauthorised user"); }
if (!$type && !$catid && !$list) { return error("missing parameters"); }

try{
    $db = new DBConn();
    $seq=1;
    
    $array = explode(":", $list);
    if ($type=="size") {
        $rem = $db->execQuery("delete from it_ck_sizes where ctg_id=$catid");
        foreach ($array as $size) {
            //$sizequery .= " sequence=$seq, size_id=$size"
            $ins = $db->execInsert("insert into it_ck_sizes set ctg_id=$catid, sequence = $seq, size_id=$size");
            $seq++;
        }
    } else if ($type == "style") {
        $rem = $db->execQuery("delete from it_ck_styles where ctg_id=$catid");
        foreach ($array as $style) {
            //$sizequery .= " sequence=$seq, size_id=$size"
            $ins = $db->execInsert("insert into it_ck_styles set ctg_id=$catid, sequence = $seq, style_id=$style");
            $seq++;
        }
    }

    if ($ins != -1) {
        success("List has been saved successfully"); /*return;*/
    } else {  return error("Update not successful, contact Intouch"); }
}catch(Exception $xcp){
    echo "error:There was a problem processing your request. Please try again later.";
 //   return;
}

function error($msg) {
    print json_encode(array(
            "error" => "1",
            "message" => $msg
            ));
}

function success($msg) {
    print json_encode(array(
            "error" => "0",
            "message" => $msg
            ));
}
?>
