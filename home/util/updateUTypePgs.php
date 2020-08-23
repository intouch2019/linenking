<?php
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
require_once 'lib/core/Constants.php';

$db = new DBConn();
$cnt = 0;
try{
    $query = "select distinct page_id from it_user_pages where user_id = 70 and page_id not in (select distinct page_id from it_usertype_pages where usertype = ".UserType::Dealer.")";
    $objs = $db->fetchObjectArray($query);
    foreach($objs as $obj){
        $insqry = "insert into it_usertype_pages set page_id = $obj->page_id , usertype = ".UserType::Dealer." , createtime = now()";
        print "\n$insqry";
        $inserted = $db->execInsert($insqry);
        if($inserted){
            $cnt++;
        }
    }
}catch(Exception $xcp){
 print $xcp->getMessage();   
}
print "\nToT_pg ins $cnt\n";