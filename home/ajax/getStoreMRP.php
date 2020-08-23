<?php 
//echo "hello";
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
require_once("session_check.php");

$store_id = isset($_GET['storeid']) ? ($_GET['storeid']) : false;
if (!$store_id) { return error("missing parameters"); }

try {
    $mrplist = array();
    //$count=0;
    $db = new DBConn();
    $sClause="";
    if($store_id == -1){
       $sClause = "" ;
    }else{
       $sClause = " where store_id in ( $store_id ) " ;
    }
    
    $query = "select distinct price as MRP from it_order_items $sClause ";
    $mrpobjs = $db->fetchObjectArray($query);
//    error_log("\nSTORE MRP: $query \n",3,"tmp.txt");
     
    foreach ($mrpobjs as $mrpob) {
        $mrplist[] = $mrpob->MRP;
    }
    
    if ($mrplist) { //error_log("\nIn succes of storemrp\n",3,"tmp.txt"); 
    success($mrplist); }
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