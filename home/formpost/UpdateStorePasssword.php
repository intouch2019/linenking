<?php
ini_set('max_execution_time', 60);
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once 'lib/users/clsUsers.php';
require_once 'lib/core/Constants.php';
require_once "lib/serverChanges/clsServerChanges.php";

extract($_POST);
$password="";
$RePassword='';
$role='';
$user = getCurrUser();
$db = new DBConn();
$serverCh = new clsServerChanges();
$userpage = new clsUsers();
$enpass=null;
$errors=array();
if($_SERVER["REQUEST_METHOD"]=="POST"){
    if(empty($_POST["Password"])){
        $password="";
    }else{
        $password=$_POST["Password"];
        print_r("password=>".$password."<br>");
    }
    
    if(empty($_POST["RePassword"])){
        $RePassword="";
    }else{
        $RePassword=$_POST["RePassword"];
        print_r("rePassword=>".$RePassword."<br>");
    }
    
    if(empty($_POST["role"])){
        $errors["role"]='Role Not Selected';
    }

    if(empty($_POST["store"])){
        $errors["store"]='Store Not Selected';
    }
}
function InsertInPasswordsAndServerChanges_allStores($enpass, $selectedOption_in, $selectedrole_in  ) {
    $db = new DBConn();
    $serverCh = new clsServerChanges();
    $query = "SELECT EXISTS(SELECT 1 FROM it_stores_passwords  WHERE store_id=$selectedOption_in and role_id='$selectedrole_in') AS mycheck";
    $flag=$db->fetchObject($query);
    if($flag->mycheck){
           $query = "update it_stores_passwords set AppPassword='$enpass', update_time=now() where store_id=$selectedOption_in and role_id='$selectedrole_in' ";		
           $updated_row=$db->execUpdate($query);
    }else{
           $query = "insert into it_stores_passwords set store_id=$selectedOption_in, role_id='$selectedrole_in', AppPassword='$enpass'";
           $insertid = $db->execInsert($query);
    }
     
}
function InsertInPasswordsAndServerChanges($enpass, $selectedOption_in, $selectedrole_in  ) {
    $db = new DBConn();
    $serverCh = new clsServerChanges();
    $query = "SELECT EXISTS(SELECT 1 FROM it_stores_passwords  WHERE store_id=$selectedOption_in and role_id='$selectedrole_in') AS mycheck";
    $flag=$db->fetchObject($query);
    if($flag->mycheck){
           $query = "update it_stores_passwords set AppPassword='$enpass', update_time=now() where store_id=$selectedOption_in and role_id='$selectedrole_in' ";		
           $updated_row=$db->execUpdate($query);
           print"In Update";
    }else{
           $query = "insert into it_stores_passwords set store_id=$selectedOption_in, role_id='$selectedrole_in', AppPassword='$enpass'";
           $insertid = $db->execInsert($query);
           print "In Insert";
    }
     $query = "SELECT * FROM it_stores_passwords  WHERE store_id=$selectedOption_in and role_id='$selectedrole_in'";
     $obj = $db->fetchObject($query);
     $pass_id = $obj->ID;
     $server_ch = json_encode($obj);
     $ser_type = changeType::password;
     $serverCh->save($ser_type, $server_ch,$selectedOption_in,$pass_id);
}
$pagecode = $db->safe($_SESSION['pagecode']);
$page = $db->fetchObject("select * from it_pages where pagecode = $pagecode");
if($page){
    $allowed = $userpage->isAuthorized($user->id, $page->pagecode);
    if (!$allowed) { header("Location: ".DEF_SITEURL."unauthorized"); return; }
}else{ header("Location:".DEF_SITEURL."nopagefound"); return; }


$success=array();
try {
       	$db = new DBConn(); 
        $currUser = getCurrUser();
        $userid = $currUser->id;
	$userp=trim($password);
	if (trim($password) == "" || trim($RePassword) == "") {
            $errors['password']='Password cannot be empty';
	} else
         if ($userp != $RePassword) {
            $errors['password']='Passwords do not match';
            print_r("wrong password");
 	} else {
            $enpass= base64_encode ($password);
            $depass= base64_decode($enpass);
            if($_POST){
                foreach ($_POST['role'] as $selectedrole){ 
                  foreach ($_POST['store'] as $selectedOption){ 
                    if($selectedOption==-1&&$selectedrole==-1){
                       $query = "select * from it_store_roles order by id";
                       $allRoles = $db->fetchObjectArray($query);
                       foreach($allRoles as $role){
                            $query = "select * from it_codes where usertype = ".UserType::Dealer." and inactive = 0";
                            $allStores = $db->fetchObjectArray($query);
                            foreach($allStores as $store){
                              InsertInPasswordsAndServerChanges_allStores($enpass, $store->id,$role->id );
                            }
                            $query = "SELECT * FROM it_stores_passwords  WHERE role_id=$role->id limit 1";
                            $obj = $db->fetchObject($query);
                            $pass_id = $obj->ID;
                            $server_ch = json_encode($obj);
                            $ser_type = changeType::password;
                            $serverCh->insert($ser_type, $server_ch,$pass_id);
                       }
                       
                       break;
                    }else if($selectedOption==-1){
                        foreach ($_POST['role'] as $selectedrole_in){
                             $query = "select * from it_codes where usertype = ".UserType::Dealer." and inactive = 0 ";
                             $allStores = $db->fetchObjectArray($query);
                             foreach($allStores as $store){
                               InsertInPasswordsAndServerChanges_allStores($enpass, $store->id,$selectedrole_in );
                             }
                            $query = "SELECT * FROM it_stores_passwords  WHERE role_id=$selectedrole_in limit 1";
                            $obj = $db->fetchObject($query);
                            $pass_id = $obj->ID;
                            $server_ch = json_encode($obj);
                            $ser_type = changeType::password;
                            $serverCh->insert($ser_type, $server_ch,$pass_id);
                         }
                       
                       break;
                    }else if($selectedrole==-1){
                        $query = "select * from it_store_roles order by id";
                        $allRoles = $db->fetchObjectArray($query);
                        foreach($allRoles as $role){
                            foreach ($_POST['store'] as $selectedOption_in){
                                InsertInPasswordsAndServerChanges($enpass, $selectedOption_in, $role->id  );
                             }
                        }
                       break;
                    }else{
                       foreach ($_POST['role'] as $selectedrole_in){
                            foreach ($_POST['store'] as $selectedOption_in){
                                InsertInPasswordsAndServerChanges($enpass, $selectedOption_in, $selectedrole_in  );
                            }
                        }
                        break;
                    }
                  
                }
                 break; 
              }
            }
              $success = 'Password changed';
        }

}
catch (Exception $xcp) {
	$clsLogger = new clsLogger();
	$clsLogger->logError("Failed to change password:$userid:".$xcp->getMessage());
	$errors['password']="There was a problem processing your request. Please try again later";
}
if (count($errors) > 0) {
	$_SESSION['form_errors'] = $errors;
	$redirect = "admin/passwordchange";
} else {
	unset($_SESSION['form_errors']);
        $_SESSION['form_success'] = $success;
	$redirect = "admin/passwordchange";
}
session_write_close();
header("Location: ".DEF_SITEURL.$redirect);
exit;

?>
