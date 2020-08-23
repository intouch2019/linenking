<?php 
//echo "hello";
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
require_once("session_check.php");

$ctg_id = isset($_GET['cat']) ? ($_GET['cat']) : false;
if (!$ctg_id) { return error("missing parameters"); }

try {
    $mrplist = array();
    //$count=0;
    $db = new DBConn();
    $ctg_id = $db->safe($ctg_id);
   // $query="select distinct(i.MRP) from it_items i,it_ck_designs d where i.ctg_id=$ctg_id and i.design_no=d.design_no and i.ctg_id=d.ctg_id and d.active=1 group by MRP order by MRP desc";
    $query="select distinct(i.MRP) from it_items i,it_ck_designs d where i.ctg_id=$ctg_id and i.design_no=d.design_no and i.ctg_id=d.ctg_id and i.is_design_mrp_active=1 group by MRP order by MRP desc";
    //print $query;
    $mrpobjs = $db->fetchObjectArray($query);
    
    foreach ($mrpobjs as $mrpob) {
        $mrplist[] = $mrpob->MRP;
       // $count++;
    }
    //print $mrplist;
    //$mrplist = substr($mrplist, 0, -1);
    if ($mrplist) { success($mrplist); }
    else { error("MRPs Not Found"); }
} catch(Exception $xcp){
    echo "error:There was a problem processing your request. Please try again later.";
 //   return;
}

function error($msg) {
    print json_encode(array(
            "error" => "1",
            "message" => $msg
            ));
}

function success($mrplis) {
    print json_encode($mrplis);
}
?>