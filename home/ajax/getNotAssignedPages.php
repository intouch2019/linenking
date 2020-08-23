<?php 
//echo "hello";
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
require_once("session_check.php");

$user_id = isset($_GET['user_id']) ? ($_GET['user_id']) : false;
if (!$user_id) { return error("missing parameters"); }

try {
    $pagelist = array();
    //$count=0;
    $db = new DBConn(); 
    //note : page sequence -1 means page is not in use anymore
    $query=" select id, menuhead, pagename from it_pages where id not in (select distinct page_id from it_user_pages where user_id = $user_id) and sequence != -1 group by menuhead,pagename";
    //print $query;
//    error_log("\nPG QRY: $query\n",3,"tmp.txt");
    $pageobjs = $db->fetchObjectArray($query);
    
    foreach ($pageobjs as $pageob) {
        $pagelist[] = $pageob->id."::".$pageob->menuhead."::".$pageob->pagename;    
    }    
    if ($pagelist) { success($pagelist); }
    else { error("Page Not Found"); }
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

function success($pagelis) {
    print json_encode($pagelis);
}
?>