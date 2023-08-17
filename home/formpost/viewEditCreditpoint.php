<?php
 require_once("../../it_config.php");
require_once("session_check.php");
require_once "../lib/db/DBConn.php";
require_once "lib/serverChanges/clsServerChanges.php";
require_once "lib/core/Constants.php";
extract($_POST);
//print_r($_POST);

$errors=array(); 
//echo $id;
//echo "<br>";
//echo $store_id;

if(isset($id) && $id !="")
{
    $id=$id;
}else{$errors['itemid'] = "Invalid item id";}
if(isset($store_id) && $store_id !="")
{
    $store_id=$store_id;
}else{$errors['store'] = "Store not selected properly";}
try{
    $db = new DBConn();
   // $dquery="delete from it_store_redeem_points where id=$itemid and store_id=$store_id";
   $dquery=" update it_store_redeem_points set active=0, is_completely_used=1 where id=$id and store_id=$store_id";
//    echo $dquery;
    $db->execUpdate($dquery);
    
     $serverCh = new clsServerChanges(); 
    $objj1 = $db->fetchObjectArray("select id, store_id,points_to_upload from it_store_redeem_points where is_reddeme =0 and active=0");
//      $db->closeConnection();
       $credit_point = array();
       $credit_points =array();
       $item=array();
       $workorderno=0;
                foreach ($objj1 as $obj1){
                    $item['server_id']=intval($obj1->id);
                    $item['store_id']=intval($obj1->store_id);
                    $item['points_to_upload'] = intval($obj1->points_to_upload);
                    $credit_point[] = json_encode($item);
                   
                 
                }
//                $wip_stockdata['work_order_no']=$workorderno;
                $credit_points['items']=json_encode($credit_point);
                 $server_ch = json_encode($credit_points);
//                echo $server_ch;
                             $CKWHStoreid = DEF_CK_WAREHOUSE_ID;
                             $ser_type = changeType::removeCrditPoints;   
                             $serverCh->save($ser_type, $server_ch,$CKWHStoreid,$workorderno);
                             
//                             $sql="update it_store_redeem_points set is_sent=1";
//                             $db->execUpdate($sql);
    
    
    
    $db->closeConnection();
    
   
    
    
    
} catch (Exception $ex) {

}
if (count($errors) > 0) {
	$_SESSION['form_errors'] = $errors;
} else {
    unset($_SESSION['form_errors']);}
session_write_close();
header("Location: ".DEF_SITEURL."viewedit/creditpoint/sid=$store_id");
exit;///