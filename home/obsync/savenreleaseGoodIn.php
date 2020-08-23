<?php
include "checkAccess.php";
ini_set('max_execution_time',300);
require_once "../../it_config.php";
//require_once "lib/logger/clsLogger.php";
require_once "../lib/db/DBConn.php";
require_once "../lib/core/Constants.php"; 
require_once "../lib/serverChanges/clsServerChanges.php";
require_once "../lib/logger/clsLogger.php";

extract($_POST);
if (!isset($records) || trim($records) == "") {
//	$logger->logError("Missing parameter [records]:".print_r($_POST, true));
	print "1::Missing parameter";
	return;
}
$db = new DBConn();
$serverCh = new clsServerChanges();
$clsLogger = new clsLogger();
$arr = explode("||",$records);
$design_no=false;$ctg_id=false;
$lineno=false; $rackno=false;
foreach ($arr as $record) {
	if (trim($record) == "") { continue; }
	$fields = explode("<>",$record);
        $receipt_no = $db->safe($fields[0]);
        $lineno = trim($fields[1]); 
        $rackno = trim($fields[2]); 
        $barcode = $db->safe($fields[3]);
        $item_qty = doubleval($fields[4]);
        $dt = $fields[5];
        $dt /= 1000;
        $date = $db->safe(date("Y-m-d H:i:s", $dt));
        $obj = $db->fetchObject("select * from it_items where barcode = $barcode");
        $item_id = 0;
        if (isset($obj)){ 
              $query = "update it_items set  curr_qty = curr_qty + $item_qty, updatetime=now() where id=$obj->id";
              
              $db->execUpdate($query); 

              $track_query = "insert into it_savenrelase_log set barcode=$barcode,release_qty=$item_qty,release_date=now()";
              
              $db->execInsert($track_query);
              
        }
            //--> code to log it_items update track
                $ipaddr =  $_SERVER['REMOTE_ADDR'];
                $pg_name = __FILE__;                       
                $clsLogger->logInfo($query,false, $pg_name,$ipaddr);
            //--> log code ends here
            $item_id = $obj->id;
            $design_no = $obj->design_no;
            $ctg_id = $obj->ctg_id;
        }
        $query = "insert into it_goodsreceipt set receipt_no = $receipt_no, item_id=$item_id, item_code=$barcode , item_qty=$item_qty , date = $date ";
        $db->execInsert($query);
        if ($design_no !== false && $ctg_id !== false && ($lineno || $rackno)) {
                $q = array();
                if ($lineno) { $q[] = "lineno = $lineno"; }
                if ($rackno) { $q[] = "rackno = $rackno"; }
                $query = "update it_ck_designs set ".implode(",",$q)." , updatetime = now() where ctg_id=$ctg_id and design_no='$design_no'";                
                $db->execUpdate($query);
//                $obj = $db->fetchObject("select * from it_ck_designs where ctg_id = $ctg_id and design_no='$design_no' ");
//                $server_ch = "[".json_encode($obj)."]";
//               // $ser_type = changeType::design_line_rack_updated;
//                 $ser_type = changeType::ck_designs;
//                $serverCh->insert($ser_type, $server_ch);
        }
//}
$db->closeConnection();
print "0::Success";
?>
