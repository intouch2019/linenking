<?php
include "checkAccess.php";
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
//note data is going to come all together in one batch
extract($_POST);
//print_r($record);
if (!isset($records) || trim($records) == "") {
	print "1::Missing parameter";
	return;
}
//step 1:- set quantity = 0 n sync_id = null for dat store curr stock
//step 2:- If barcode exists in table update the quantity with the one provided
//step 3:- if barcode is not present in table then insert against the store

try{
    $db = new DBConn();   
    $store_id = $gCodeId;    
    //$store_id = 75;
    $upqry = "update it_current_stock set quantity = 0 , sync_id = null   where  store_id = $store_id";
    $db->execUpdate($upqry);            
    $arr = explode("||",$records);
    
    foreach($arr as $rec){
        if ($rec == "") { continue; }
        $currrec = explode("<>",$rec);
//        print_r($currrec);               
        $barcode = $db->safe($currrec[0]);
        $quantity = $currrec[1];
        
        $chkQry = "select * from it_current_stock where barcode = $barcode and store_id = $store_id ";
        $check = $db->fetchObject($chkQry);
        if($check){
            $updtQry = "update it_current_stock set quantity = $quantity , updatetime = now()  where barcode = $barcode and store_id = $store_id ";
            $db->execQuery($updtQry);
        }else{
            $iqry = "select * from it_items where barcode = $barcode ";
            $iobj = $db->fetchObject($iqry);
            if(isset($iobj)){
                $ctg_id = $iobj->ctg_id;
                $design_id = $iobj->design_id;
                $style_id = $iobj->style_id;
                $size_id = $iobj->size_id;
            }else{
                $ctg_id = 0;
                $design_id = 0;
                $style_id = 0;
                $size_id = 0;
            }
            $insQry = " insert into it_current_stock set barcode = $barcode , store_id = $store_id ,ctg_id = $ctg_id, design_id = $design_id , style_id = $style_id , size_id = $size_id , quantity = $quantity , createtime = now() ";
            
            //$insQry = " insert into it_current_stock set barcode = $barcode , store_id = $store_id , quantity = $quantity , createtime = now() ";
            $db->execInsert($insQry);
        }
    
    }
    
    print "0::Success";
}catch(Exception $ex){
    print "1::Error-".$ex->getMessage();
}
?>
