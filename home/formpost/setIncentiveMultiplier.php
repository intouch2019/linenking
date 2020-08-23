<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/serverChanges/clsServerChanges.php";

extract($_POST);

print_r($_POST);

$errors = array();
$success = "";
$cnt = 0;

if(trim($sel_cat)==0){
    $errors[] = "Please Select Category ";
}

if(!isset($cmrp) || count($cmrp) == 0){
    $errors[] = "Please Select MRP ";
}else{
    sort($cmrp);
    $cnt = count($cmrp);    
}

if(!isset($multiplier) || trim($multiplier) == ""){
    $errors[] = "Please Enter Multiplier ";
}
$all_mrp_consideration = 0;
if($cnt > 1){
    $all_mrp_consideration = 0;
}else if($cnt == 1){
    $all_mrp_consideration = 1;
}
//print $cnt;
//print_r($errors);

try{
    $serverCh = new clsServerChanges();
    $db = new DBConn();
    
    if(count($errors)==0){
        foreach($cmrp as $key => $mrp){
           if($mrp=="-1" && $all_mrp_consideration==0){ // means all option selected along with other mrps, so ignore all option
              continue; 
           } 
           $insert=0;
           
           $query = "select * from it_sincentive_multipliers where ctg_id = $sel_cat and mrp = $mrp ";
//           echo "select * from it_sincentive_multipliers where ctg_id = $sel_cat and mrp = $mrp";
//           exit();
           $obj = $db->fetchObject($query);
           $insert =$obj->id ;
           if(isset($obj)){
             //update  
               $query = "update it_sincentive_multipliers set  multiplier = $multiplier where id = $obj->id ";
//               echo "update it_sincentive_multipliers set  multiplier = $multiplier where id = $obj->id";
//               exit();
               $db->execUpdate($query);
                $insert =$obj->id ;
           }else{  
             //insert  
                $query = "insert into it_sincentive_multipliers set ctg_id = $sel_cat , mrp = $mrp , multiplier = $multiplier , createtime = now()  ";
//                echo"insert into it_sincentive_multipliers set ctg_id = $sel_cat , mrp = $mrp , multiplier = $multiplier , createtime = now()";
//                exit();
                $insert=$db->execInsert($query);//incentive 
                
           }
            $query = "select id as server_id,ctg_id,mrp,multiplier from it_sincentive_multipliers where id=$insert ";
//            echo "select id as server_id,ctg_id,mrp,multiplier from it_sincentive_multipliers where id=$insert ";
//            exit();
          
           $obj = $db->fetchObject($query);
                if(isset($obj) && !empty($obj) && $obj != null){
                       $server = json_encode($obj);
                       $server_ch = "[".$server."]"; // converting n storing in obj format so that easy retrival at pos side                    );
                       $ser_type = changeType::incentive; 
                       $serverCh->insert($ser_type, $server_ch ,$obj->server_id);
                   
                }
        }
    }
    
}catch(Exception $xcp){
   print $xcp->getMessage(); 
}

if (count($errors) > 0) {
	$_SESSION['form_errors'] = $errors;
	$redirect="sincentive/multiplier/ctgid=".$sel_cat;
} else {
        $success = "Incentive Multiplier Set/Updated successfully";
	unset($_SESSION['form_errors']);
        $_SESSION['form_success'] = $success;
        $redirect="sincentive/multiplier/ctgid=".$sel_cat;
}
session_write_close();
header("Location: ".DEF_SITEURL.$redirect);
exit;