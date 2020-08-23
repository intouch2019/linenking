<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/serverChanges/clsServerChanges.php";
require_once 'lib/users/clsUsers.php';



extract($_POST);
$user = getCurrUser();
$db = new DBConn();
//print_r($_POST);


$errors=array();
$success=array();

try {
                    $_SESSION['form_post'] = $_POST;
                    
                    $serverCh = new clsServerChanges();
                    
                    $cat_name=$db->safe($avalue);
                    $tax_name="GST ".$vat_type."%";
                    $tax_pct=$vat_type;
                    $tax_rate=($tax_pct/100);
                    $valid_from=$db->safe($fromdate);
                    //select id from it_categories where name like '%Short SHIRT%';"";
                    $obj = $db->fetchObject("select * from it_categories where name=$cat_name");
                    //print "$obj->id";
                    $cat_id=$obj->id;

                    $obj1=$db->fetchObject("select * from it_category_taxes where category_id=$cat_id");
                    if ($obj1) {

                                $query = "update it_category_taxes set tax_name='$tax_name',tax_rate=$tax_rate,category_name=$cat_name,tax_percent=$tax_pct,validfrom=$valid_from where category_id=$cat_id";          
                                print "$query";
                                $updated= $db->execUpdate ($query);
                                print ">>>>>>updated$updated";
                                if($updated)
                                {
                                    $obj = $db->fetchObject("select * from it_category_taxes where category_id= $cat_id ");
                                    print_r($obj);
                                }
                                  
                                
                               if(isset($obj)){
                                $server = json_encode($obj);
                                print_r($server);
                                $server_ch = "[".$server."]"; // converting n storing in obj format so that easy retrival at pos side
                                //$typename = "new_".$atype;
                                //$ser_type = constant("changeType::$typename");
                                $ser_type =26;   
                                        
                                    $store_id ="";
                                    $serverCh->insert($ser_type, $server_ch,$obj->id); 
                                
                             }
                    }
                    else {
                             $query = "insert into it_category_taxes set category_id=$cat_id,tax_name='$tax_name',tax_rate=$tax_rate,category_name=$cat_name,tax_percent=$tax_pct,validfrom=$valid_from";
                               print "$query";
                               $inserted = $db->execInsert ($query);
                               print ">>>>>>>>>insert$inserted";
                               $obj = $db->fetchObject("select * from it_category_taxes where id = $inserted ");
                                
                               if(isset($obj)){
                                $server = json_encode($obj);
                                print_r($server);
                                $server_ch = "[".$server."]"; // converting n storing in obj format so that easy retrival at pos side
                                //$typename = "new_".$atype;
                                //$ser_type = constant("changeType::$typename");
                                $ser_type =26;   
                                        
                                    $store_id ="";
                                    $serverCh->insert($ser_type, $server_ch,$obj->id); 
                                
                             }
                                   
                               
                               
                    }
                                    $success = "Tax for the category $avalue has been recorded";
                                    unset($_SESSION['avalue']);
                                   
} catch (Exception $xcp) {
	$clsLogger = new clsLogger();
	$clsLogger->logError("Failed to add $avalue:".$xcp->getMessage());
	$errors['status']="There was a problem processing your request. Please try again later";
}
if (count($errors) > 0) {
	$_SESSION['form_errors'] = $errors;
} else {
	unset($_SESSION['form_errors']);
        unset($_SESSION['form_post']);
        $_SESSION['form_success'] = $success;
}
                    //category_id,tax_name,tax_percent,tax_rate,validfrom

                    session_write_close();
                    header("Location: ".DEF_SITEURL."category/taxes");
                    exit;