<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/serverChanges/clsServerChanges.php";
require_once 'lib/users/clsUsers.php';  

extract($_POST);
$user = getCurrUser();
$db = new DBConn();
$userpage = new clsUsers();
$pagecode = $db->safe($_SESSION['pagecode']);
$page = $db->fetchObject("select * from it_pages where pagecode = $pagecode");
if($page){
    $allowed = $userpage->isAuthorized($user->id, $page->pagecode);
    if (!$allowed) { header("Location: ".DEF_SITEURL."unauthorized"); return; }
}else{ header("Location:".DEF_SITEURL."nopagefound"); return; }


$errors=array();
$success=array();
try {
	$_SESSION['form_post'] = $_POST;
	
        $addquery = "";
        $serverCh = new clsServerChanges();
	if (isset($atype) && trim($atype) != "" && isset($avalue) && trim($avalue) != "") {
            //error_log("\n attr :- ".$atype."\n",3,"tmp.txt");
                if($atype == "categories"){                    
//                    if(!$vat_type && trim($vat_type) == "" || !$cst_type && trim($cst_type) == ""){ 
//                        $errors['vat'] = "Please select vat and/or cst type";                        
//                    }else{
//                        $addquery .= " , vat_id = $vat_type , cst_id = $cst_type ";
//                    }   
                     $addquery .= " , vat_id = 0 , cst_id = 0";
                    if(!$hsncode && trim($hsncode) == "" ){ 
                        $errors['hsn'] = "Please enter hsncode";                        
                    }else{
                        $hsncode_db = $db->safe(trim($hsncode));
                        $addquery .= " , it_hsncode = $hsncode_db ";
                    } 
                    
                    
                    
                    if(!$margin && trim($margin) == "" ){ 
                        $errors['margin'] = "Please select margin";                        
                    }else{
                        $addquery .= " ,margin=$margin ";
                    }
                    
                    if (isset($othercat)) {
                        $addquery .= " , setotheractive = $othercat ";
                    } else {
                        $errors['otr'] = "Please  Set For Other";
                    }

                    if (isset($accesorycat)) {
                        $addquery .= " , setaccesories = $accesorycat ";
                    } else {
                        $errors['acr'] = "Please  Set For Accesories";
                    }
            
            
                }
                if(count($errors) == 0){
                    $avalue=$db->safe($avalue);
                    $obj = $db->fetchObject("select * from it_$atype where name=$avalue");
                    if ($obj) { $errors['status'] = "Attribute $avalue already exists"; }
                    else {
                            $query = "insert into it_$atype set name=$avalue $addquery";
                            //error_log("\nATTR QRY:- ".$query."\n",3,"../ajax/tmp.txt");
                            $inserted = $db->execInsert ($query);
                            if($inserted){
                                $obj = $db->fetchObject("select * from it_$atype where id = $inserted ");
                             if(isset($obj)){
                                $server = json_encode($obj);
                                $server_ch = "[".$server."]"; // converting n storing in obj format so that easy retrival at pos side
                                //$typename = "new_".$atype;
                                //$ser_type = constant("changeType::$typename");
                                $ser_type = constant("changeType::$atype");   
//                                if($atype == "categories"){        
//                                    $store_id = DEF_WAREHOUSE_ID;
//                                    $serverCh->save($ser_type, $server_ch,$store_id,$obj->id); 
//                                }else{
                                  $serverCh->insert($ser_type, $server_ch,$obj->id);
                                //}
                             }
                                if($atype == "categories"){
                                     if ($othercat == "0" && $accesorycat == "0") {
                                        createCatalogPg($inserted);
                                        createReleasedCatalogPg($inserted);
                                    }
//                        
//                                     createCatalogPg($inserted);
//                                     createReleasedCatalogPg($inserted);
                                 }
                            }
                            $success = "$avalue has been added";
                            unset($_SESSION['avalue']);
                    }
                }
        }else{
            $errors['attr'] = " Please enter ".$atype."'s name ";
        }
} catch (Exception $xcp) {
	$clsLogger = new clsLogger();
	$clsLogger->logError("Failed to add $atype:".$xcp->getMessage());
	$errors['status']="There was a problem processing your request. Please try again later";
}
if (count($errors) > 0) {
	$_SESSION['form_errors'] = $errors;
} else {
	unset($_SESSION['form_errors']);
        unset($_SESSION['form_post']);
        $_SESSION['form_success'] = $success;
}
session_write_close();
header("Location: ".DEF_SITEURL."barcode/attributes");
exit;

function createCatalogPg($inserted){
    $db = new DBConn();
    //fetch details of new category
    $query = "select * from it_categories where id = $inserted ";
    $catedb = $db->fetchObject($query);
    //fetch details of last catlog pg added
    $qry = "select * from it_pages where menuhead = 'Catalog' and sequence != 0 order by id desc limit 1";
    $catlogdb = $db->fetchObject($qry);
    $qry3 = "select max(submenu_seq) as subsequence_num from it_pages where menuhead='Catalog' and sequence!=0 and pagecode not in('other_cat','Accessories');";
    $subsequence = $db->fetchObject($qry3);
    //$submenu_seq = $catlogdb->submenu_seq + 1;
    

    $submenu_seq = $subsequence->subsequence_num + 1;
    $cquery = "insert into it_pages set pagecode = '$catedb->id',pagename='$catedb->name',pageuri='store/designs/ctg=$catedb->id',menuhead='Catalog',sequence=$catlogdb->sequence,submenu_seq = $submenu_seq";
   
    $pg_id = $db->execInsert($cquery);
    if($pg_id){
        dealerPgAccess($pg_id);
    }
    
}


function createReleasedCatalogPg($inserted){
    $db = new DBConn();
    //fetch details of new category
    $query = "select * from it_categories where id = $inserted ";
    $catedb = $db->fetchObject($query);
    //fetch details of last catlog pg added
    $qry = "select * from it_pages where menuhead = 'Released Catalog' and sequence != 0 order by id desc limit 1";
    $catlogdb = $db->fetchObject($qry);
    $submenu_seq = $catlogdb->submenu_seq + 1;
    $cquery = "insert into it_pages set pagecode = 'r_$catedb->id',pagename='$catedb->name',pageuri='rstore/designs/ctg=$catedb->id',menuhead='Released Catalog',sequence=$catlogdb->sequence,submenu_seq = $submenu_seq";
    $pg_id = $db->execInsert($cquery);
    if($pg_id){
      //  dealerPgAccess($pg_id);
    }
    
}

function dealerPgAccess($pg_id){
   $db = new DBConn();
   $query = " select id,code,usertype from it_codes where usertype = ".UserType::Dealer;
   $allDealerUsers = $db->fetchObjectArray($query);
   foreach($allDealerUsers as $dealer){
        $user_id = $dealer->id;
        $insertquery = "insert into it_user_pages set user_id = $user_id , page_id = $pg_id ";
        $db->execInsert($insertquery);
   }     
}
?>
