<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once 'lib/users/clsUsers.php';
require_once "lib/logger/clsLogger.php";

$clsLogger = new clsLogger();
extract($_POST);
$db = new DBConn();
$user = getCurrUser();
$userpage = new clsUsers();
$pagecode = $db->safe($_SESSION['pagecode']);
$page = $db->fetchObject("select * from it_pages where pagecode = $pagecode");
if($page){
    $allowed = $userpage->isAuthorized($user->id, $page->pagecode);
    if (!$allowed) { header("Location: ".DEF_SITEURL."unauthorized"); return; }
}else{ header("Location:".DEF_SITEURL."nopagefound"); return; }

$errors = array();
$success="";

try{
    if(!$store_id){ $errors['Store'] = "Please select Store";}
    if(!$order_id){ $errors['Orderno'] = "Please select Order no";}
    
    if(count($errors) == 0){
        $objs = $db->fetchObjectArray("select * from it_ck_orderitems where order_id = $order_id");
        $updates=array();
        $count=0;
        foreach ($objs as $oi) {
//        $query = "select * from it_ck_items where ctg_id='$oi->ctg_id' and style_id='$oi->style_id' and size_id='$oi->size_id' and design_no='$oi->design_no' and MRP=$oi->MRP";
        $query = "select * from it_items where id = $oi->item_id ";
        $obj = $db->fetchObject($query);
        if (!$obj) { $errors['item'] = "Item not found :$query\n"; continue; }
        if (!isset($updates[$obj->id])) { $updates[$obj->id] = 0; }
        $updates[$obj->id] += $oi->order_qty;
        $count++;
        }
//            print "Update items\n";
            foreach ($updates as $itemid => $quantity) {
                    $query = "update it_items set curr_qty = curr_qty + $quantity where id=$itemid";
//                    print "$query\n";
                      //if ($commit)
                    //--> code to log it_items update track
                    $ipaddr =  $_SERVER['REMOTE_ADDR'];
                    $pg_name = __FILE__;                
                    $clsLogger->logInfo($query,false, $pg_name,$ipaddr);
                    //--> log code ends here
                    $db->execUpdate($query);
            }
            $query = "update it_ck_orders set status=".OrderStatus::Cancelled." where id=$order_id";
//            print "$query\n";
//            if ($commit) 
                $db->execUpdate($query);
                $success .= "Order Cancelled Successfully";
//            if ($commit) print "DATABASE UPDATED\n";
    }
    
}catch(Exception $xcp){
    print $xcp->getMessage();
}

if( count($errors) > 0){
    $_SESSION['form_errors'] = $errors;
    $redirect = "admin/cancel/order";
} else {
    unset($_SESSION['form_errors']);
    $_SESSION['form_success'] = $success;
    $redirect = "admin/cancel/order";	 
}
session_write_close();
header("Location: ".DEF_SITEURL.$redirect);
exit;
?>
