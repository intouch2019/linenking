<?php 
//include "checkAccess.php";
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
        $points_used=$db->safe($fields[4]);
         
        $update_query="update it_store_redeem_points set is_reddeme =1 where id=$server_id and store_id =$store_id";
                $insert_query="insert into it_store_redeem_points_partial set it_store_redeem_points_id=$server_id, invoice_no=$invoice_no, points_redeemdate=$redeem_date, points_used=$points_used";

//        echo $update_query."<br>";
        $db->execUpdate($update_query);
        $db->execInsert($insert_query);
           print "0::Successfull";
  }
}catch(Exception $ex){
    print "1::Error".$ex->getMessage();
}