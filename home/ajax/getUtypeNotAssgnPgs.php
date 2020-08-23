<?php 
//echo "hello";
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
require_once("session_check.php");

$utype = isset($_GET['user_type']) ? ($_GET['user_type']) : false;
if (!$utype) { return error("missing parameters"); }

try {
    $pagelist = array();
    //$count=0;
    $db = new DBConn();   
    //note : page sequence -1 means page is not in use anymore
    $query=" select id, menuhead, pagename from it_pages where id not in (select page_id from it_usertype_pages where usertype = $utype) and sequence != -1 group by menuhead,pagename";
    //print $query;
    //error_log("\nPG QRY: $query\n",3,"tmp.txt");
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