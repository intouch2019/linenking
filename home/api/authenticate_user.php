<?php
require_once "../../it_config.php";
//include "checkAccess.php";
require_once "lib/db/DBConn.php";
extract($_POST);
//extract($_GET);

if ((!isset($username) || trim($username) == "") && (!isset($password) || trim($password) == "")) {
	print "1::Missing parameter";
	return;
}
try{
    $db = new DBConn();
            
    //push all the users
    $username = $db->safe($username);
    $password = $db->safe(md5($password));
    $query = "select id, code as username, store_name as name, password, usertype from it_codes where usertype = 5 and inactive = 0 and code = $username and password = $password";
    $obj_user = $db->fetchObject($query);
    if(isset($obj_user)){
        //$json_obj['users'] = $obj_users;
        $json_str = json_encode($obj_user);
        print "0::$json_str";
    }else{
        //print "1::$query";
        print "1::User not found";
    }


    /*$json_str = json_encode($json_obj);
    print "0::$json_str";*/
    
}catch(Exception $xcp){
    print "1::".$xcp->getMessage();
}
?>
