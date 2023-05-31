<?php 
include "checkAccess.php";
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
//$records='389<>711<>21220038<>2021-06-30 17:45:56.0||389<>732<>21220042<>2021-07-04 19:16:07.0||389<>735<>21220038<>2021-06-30 17:45:56.0||389<>737<>21220063<>2021-07-27 17:20:49.0||389<>738<>21220063<>2021-07-27 17:20:49.0||389<>2858<>21220060<>2021-07-26 16:35:52.0||144<>2859<>21220056<>2021-07-20 15:32:11.0||389<>2878<>21220060<>2021-07-26 16:35:52.0||389<>2906<>21220060<>2021-07-26 16:35:52.0||';

extract($_POST);
if (!isset($records) || trim($records) == "") {
    print "1::Missing parameter";
	return;
}
//echo $records;
//exit();
try
{
$db = new DBConn(); 
$arr = explode("||",$records); 
foreach ($arr as $record) {
	if (trim($record) == "") { continue; }
         
	$fields = explode("<>",$record);
        $store_id = trim($fields[0]);
        $server_id = trim($fields[1]);
        $invoice_no = $db->safe($fields[2]);
        $redeem_date = $db->safe($fields[3]);
        $points_used = $db->safe($fields[4]);
         
        if($invoice_no!=null && $points_used!=0 || $points_used!=""){
        $update_query=" update it_store_redeem_points set is_reddeme =1 where id=$server_id and store_id =$store_id";
        $db->execUpdate($update_query);
        $insert_query="insert into it_store_redeem_points_partial set it_store_redeem_points_id=$server_id, invoice_no=$invoice_no, points_redeemdate=$redeem_date, points_used=$points_used";
        $db->execInsert($insert_query);
        }
         $select_query = "select points_to_upload as ptu from it_store_redeem_points where id=$server_id and store_id=$store_id and is_completely_used=0";
        $points_to_upload = $db->fetchObject($select_query);

        $select_query_points_used = "select sum(rp.points_used) as tpoints from it_store_redeem_points r inner join it_store_redeem_points_partial rp on r.id=rp.it_store_redeem_points_id where r.id=$server_id and r.store_id=$store_id and r.is_completely_used=0";
        $total_points_used = $db->fetchObject($select_query_points_used);
        
        if(isset($points_to_upload) && isset($total_points_used) && trim($points_to_upload->ptu)!=null && trim($total_points_used->tpoints)!=null){
            if($points_to_upload->ptu==$total_points_used->tpoints){
                        $db->execUpdate("update it_store_redeem_points set is_completely_used=1 where store_id=$store_id and id=$server_id");

            }
        }
        
           print "0::Successfull";
  }
}catch(Exception $ex){
    print "1::Error".$ex->getMessage();
}