<?php
//include "checkAccess.php";
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";


extract($_POST);

if ((!isset($Records) || trim($Records) == "") ){
	print "1::Missing parameters";
	return;
}
$data= explode("<>", $Records);
try{
    $db = new DBConn();
    
    $status = $db->safe($data[1]);
    $reason = $db->safe($data[2]);
    
    $update_qry = "update it_workorder_units set is_sent_to_wh = $status, reject_reason = $reason where wo_no = $data[0] ";
//    print "<br><br>".$update_qry;
    $db->execUpdate($update_qry);
            
//    $qry = "update creditnote_no set cn_no=$Cn_Num ,active=0;";
//    $db->execUpdate($qry);
    print "0::$Records";
}catch(Exception $ex){
    print "1::Error".$ex->getMessage();
}
?>
