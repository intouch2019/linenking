<?php
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
require_once("session_check.php");

$ctg_id = isset($_GET['ctg_id']) ? ($_GET['ctg_id']) : false;
$design_no = isset($_GET['design_no']) ? ($_GET['design_no']) : false;
//$cat = isset($_GET['cat']) ? ($_GET['cat']) : false;

if (!$ctg_id || !$design_no) { return error("missing parameters"); }

try{
    $db = new DBConn();
    $db_ctg_id = $db->safe($ctg_id);
    $db_design_no = $db->safe($design_no);
   // $cat=$db->safe($cat);
    //$exist=$db->fetchObjectArray("select * from it_items i,it_ck_designs d where d.ctg_id=$db_ctg_id and d.design_no=$db_design_no and d.active=1 and i.design_no=d.design_no and i.ctg_id = d.ctg_id");
    $exist=$db->fetchObjectArray("select * from it_items i,it_ck_designs d where d.ctg_id=$db_ctg_id and d.design_no=$db_design_no and i.is_design_mrp_active = 1  and i.design_no=d.design_no and i.ctg_id = d.ctg_id");
    
    if ($exist) {
        $redirect="store/designs/otherctg/ctg=$ctg_id/dno=$design_no";
        success("Design Found", $redirect); /*return;*/
    } else {  return error("Design Not Found"); }
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

function success($msg, $redirect) {
    print json_encode(array(
            "error" => "0",
            "redirect" => $redirect,
            "message" => $msg
            ));
}
?>
