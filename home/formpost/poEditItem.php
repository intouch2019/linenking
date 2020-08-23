<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";

extract($_POST);
$user = getCurrUser();
if ($user->usertype != UserType::Admin && $user->usertype != UserType::GoodsInward) { print "You are not authorized to add a User"; return; }

$querytrans = "";
$errors=array();
$success=array();
try {
	$_SESSION['form_post'] = $_POST;
	$db = new DBConn();
        $poid = isset($poid) ? intval($poid) : false;
	if ($poid <= 0) { $errors['poid'] = "Not able to get PO number"; }
        $potype = isset($potype) ? intval($potype) : false;
        if ($potype < 0) { $errors['potype'] = "Not able to get PO Type"; }
        $polineid = isset($polineid) ? intval($polineid) : false;
	if ($polineid <= 0) { $errors['polineid'] = "Not able to get PO Line"; }
        $supId = isset($supId) ? intval($supId) : false;
        if($supId <= 0) { $errors['supId'] = "Not able to get Supplier";  }

        $sdesign = isset($sdesign) && trim($sdesign) != "" ? $db->safe($sdesign) : false;
        if(!$sdesign) { $errors['sdesign'] = "Please enter supplier design"; }

        $ckdesign = isset($ckdesign) && trim($ckdesign) != "" ? $db->safe($ckdesign) : false;
        if(!$ckdesign) { $errors['ckdesign'] = "Please enter ck design"; }

        $qty = isset($qty) && trim($qty) != "" ? $db->safe($qty) : false;
        if(!$qty) { $errors['qty'] = "Please enter quantity"; }

        $uom = isset($uom) && trim($uom) != "" ? $db->safe($uom) : false;
        if(!$uom) { $errors['uom'] = "Please enter Unit of measure"; }
        
        $rate = isset($rate) && trim($rate) != "" ? $db->safe($rate) : false;
        if(!$rate) { $errors['rate'] = "Rate cannont be empty"; }
        
        $value = isset($value) && trim($value) != "" ? $db->safe($value) : false;
        if(!$value) { $errors['value'] = "Please enter value"; }
        
        $exdate = isset($exdate) && trim($exdate) != "" ? $db->safe($exdate) : false;
        if(!$exdate) { $errors['exdate'] = "Please enter expected date"; }
        

        $fabtype = isset($fabtype) && trim($fabtype) != "" ? $db->safe($fabtype) : false;
        if($potype == PoType::Fabric){
            if(!$fabtype) { $errors['fabtype'] = "Please Enter Fabric Type"; }
        }

        $color = isset($color) && trim($color) != "" ? $db->safe($color) : false;
        if($potype == PoType::Fabric){
            if(!$color) { $errors['color'] = "Please enter color"; }
        }

        $nproduct = isset($nproduct) && trim($nproduct) != "" ? $db->safe($nproduct) : false;
        if($potype == PoType::Fabric){                
            if(!$nproduct) { $errors['nproduct'] = "Please Select Product"; }
        }

        $nproducttype = isset($nproducttype) && trim($nproducttype) != "" ? $db->safe($nproducttype) : false;
        if($potype == PoType::Fabric){                
            if(!$nproducttype) { $errors['nproducttype'] = "Please Select Product Type"; }
        }
        
        if (count($errors) == 0) {
            $objftype = $db->fetchObject("select id from it_itemtype where name=$fabtype");
            if($objftype->id == null){
                $fabtypeId = $db->execInsert("insert into it_itemtype set name=$fabtype");
            }else{
                $fabtypeId = $objftype->id;
            }
            
            $objcolor = $db->fetchObject("select id from it_color where name=$color");
            if($objcolor->id == null){
                $colorId = $db->execInsert("insert into it_color set name=$color");
            }else{
                $colorId = $objcolor->id;
            }
            
            $objproduct = $db->fetchObject("select id from it_product where name=$nproduct");
            if($objproduct->id == null){
                $productId = $db->execInsert("insert into it_product set name=$nproduct");
            }else{
                $productId = $objproduct->id;
            }
            
            $objproducttype = $db->fetchObject("select id from it_productiontype where name=$nproducttype");
            if($objproducttype->id == null){
                $producttypeId = $db->execInsert("insert into it_productiontype set name=$nproducttype");
            }else{
                $producttypeId = $objproducttype->id;
            }
            
            $objckdesign = $db->fetchObject("select id from it_ckdesign where designno = $ckdesign");
            if($objckdesign != NULL){
                $ckdesign_id = $objckdesign->id;
                $db->execUpdate("update it_ckdesign set itemtype_id=$fabtypeId, color_id=$colorId, product_id=$productId, productiontype_id=$producttypeId, updatetime=now() where id = $ckdesign_id");
            }else{
                $ckdesign_id = $db->execInsert("insert into it_ckdesign set designno=$ckdesign, itemtype_id = $fabtypeId, color_id = $colorId, product_id = $productId, productiontype_id = $producttypeId");
            }
            
            $objsdesign = $db->fetchObject("select id from it_supplierdesign where supplier_id = $supId and designno = $sdesign");
            if($objsdesign != null){
                $supDesignId = $objsdesign->id;
                $db->execUpdate("update it_supplierdesign set supplier_id=$supId, designno=$sdesign, ckdesign_id=$ckdesign_id, updatetime=now() where id=$supDesignId");
            }else{
                $supDesignId = $db->execInsert("insert into it_supplierdesign set supplier_id = $supId, designno = $sdesign, ckdesign_id = $ckdesign_id");
            }
            
            $query = "update it_polines set suppdesign_id=$supDesignId, ckdesign=$ckdesign_id, qty=$qty, uom=$uom, rate=$rate, expected_date=$exdate, updatetime=now() where id = $polineid";
            $db->execUpdate($query);
	}
} catch (Exception $xcp) {
	$clsLogger = new clsLogger();
	$clsLogger->logError("Failed to do gate entry:".$xcp->getMessage());
	$errors['status']="There was a problem processing your request. Please try again later";
}
if (count($errors) > 0) {
	$_SESSION['form_errors'] = $errors;
	$redirect = "po/edititems/id=$polineid/";
} else {
	unset($_SESSION['form_errors']);
//        $_SESSION['form_success'] = $success;
	$redirect = "po/additems/id=$poid/";	 
}
session_write_close();
header("Location: ".DEF_SITEURL.$redirect);
exit;

?>
