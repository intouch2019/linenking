<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";

extract($_GET);
$user = getCurrUser();


$errors=array();
$success=array();


try{
    	$db = new DBConn();
        $supId = isset($supId) ? intval($supId) : false;
        if($supId <= 0) { $errors['supId'] = "Not able to get Supplier";  }
        $sdesign = isset($sdesign) && trim($sdesign) != "" ? $db->safe($sdesign) : false;
        if(!$sdesign) { 
            $objckdesign = $db->fetchObject("select max(designno) as ckdesign from it_ckdesign");
            if($objckdesign == null){
                $ckdesign = 1;
            }else{
                $ckdesign = $objckdesign->ckdesign + 1;
                echo $ckdesign;
            }
        }

        if (count($errors) == 0) {
            $objdesign = $db->fetchObject("select ckdesign_id from it_supplierdesign where supplier_id = $supId and designno = $sdesign");
            if($objdesign != null){
                if($objdesign->ckdesign_id != null){
                    $ckdesign_id = $objdesign->ckdesign_id;
                    $obj_ckdesign = $db->fetchObject("select designno from it_ckdesign where id = $ckdesign_id");
                    echo $obj_ckdesign->designno;
                }else{
                    $objckdesign = $db->fetchObject("select max(designno) as ckdesign from it_ckdesign");
                    if($objckdesign == null){
                        $ckdesign = 1;
                    }else{
                        $ckdesign = $objckdesign->ckdesign + 1;
                        echo $ckdesign;
                    }
                }
            }else{
                $objckdesign = $db->fetchObject("select max(designno) as ckdesign from it_ckdesign");
                if($objckdesign == null){
                    $ckdesign = 1;
                }else{
                    $ckdesign = $objckdesign->ckdesign + 1;
                    echo $ckdesign;
                }
            }
	}
        
}catch(Exception $xcp){
    
}

?>
