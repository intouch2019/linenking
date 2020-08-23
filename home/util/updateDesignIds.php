<?php
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
require_once 'lib/core/Constants.php';

try{
    $db = new DBConn();
    $cnt = 0;
    $query = "select * from it_ck_designs";
    $result = $db->execQuery($query);
     if ($db->getConnection()->error) { throw new Exception($db->getConnection()->error); }
     if (!$result) { print "no results\n"; $db->closeConnection(); return; }
      while ($obj = $result->fetch_object()) {
        $design_no = $db->safe($obj->design_no);  
        $dqry = "update it_items set design_id = $obj->id where design_no = $design_no and ctg_id = $obj->ctg_id ";
        //echo "\n  QRY: ".$dqry."\n";
        $cnt+=$db->execUpdate($dqry);                
    }
    $result->close();
    $db->closeConnection();
    print "tot_updated_rows: ".$cnt;
}catch(Exception $xcp){
    $xcp->getMessage();
}
