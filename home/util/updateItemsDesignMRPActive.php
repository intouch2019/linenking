<?php
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
require_once 'lib/core/Constants.php';

$db = new DBConn();
$commit = 0;
$commit = isset($argv[1]) ? $argv[1] : 0;
$cnt=0;
$skipped_cnt = 0;
$skipped = array();
print_r($argv);

try{
    $query = "select * from it_ck_designs where active = 1";
    $result = $db->execQuery($query);
    
    while($obj = $result->fetch_object()){
        if(isset($obj) && $obj != null && ! empty($obj)){
            $design_no_db = $db->safe(trim($obj->design_no));
            $updateqry = "update it_items set is_design_mrp_active = $obj->active where ctg_id = $obj->ctg_id and design_no = $design_no_db  and design_id = $obj->id ";
            print "\n$updateqry";
            if(trim($commit)==1){
             $db->execUpdate($updateqry);
            }
            $cnt = $cnt+1;
        }
    }
}catch(Exception $xcp){
    print $xcp->getMessage();
}

if(trim($commit)==1){
    print "\nChanges Committed !!";
    print "\n Tot rows updated : $cnt";
}else{
    print "\n Tot rows that will get updated : $cnt";
}