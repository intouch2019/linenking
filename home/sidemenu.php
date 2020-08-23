<?php
require_once "../it_config.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once 'session_check.php';

$db = new DBConn();
$menu = array();
$currUser = getCurrUser();

try{   
if($currUser->usertype == UserType::Dealer){$sClause = " order by menuhead,sequence desc ";}else{$sClause ="";}
$query = "select distinct menuhead, sequence from it_pages where id in (select page_id from it_user_pages where user_id = $currUser->id ) and sequence > 0 $sClause";
//error_log("\nsidemenu qry:\n".$query,3,"ajax/tmp.txt");
$objs = $db->fetchObjectArray($query);
foreach($objs as $obj){
    $menuheading = $obj->menuhead;
    $obj->menuhead = array();
    $qry = " select p.* from it_pages p , it_user_pages u where p.menuhead = '$menuheading' and p.sequence = $obj->sequence  and p.id = u.page_id and u.user_id = $currUser->id order by p.submenu_seq asc"; //and p.sequence > 0 ";
   // echo "<br/>menuitem qry:".$qry."<br/>";
//    error_log("\nsubmenu qry:\n".$qry,3,"ajax/tmp.txt");
    $submenuobj = $db->fetchObjectArray($qry);
    foreach($submenuobj as $submenu){
        $obj->menuhead[$submenu->pagecode] = array($submenu->pagename,$submenu->pageuri);
    }
    $menu[$menuheading]=$obj->menuhead;
    
}    
//print_r($menu);
    return $menu;
}catch(Exception $xcp){
    print $xcp->getMessage();
}

?>
