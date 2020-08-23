<?php
require_once "../../it_config.php";
//include "checkAccess.php";--> no need for checkAccess for this sync
require_once "lib/db/DBConn.php";
//this sync is called to check and activate the license
extract($_POST);
//extract($_GET);

if ((!isset($license) || trim($license) == "") && (!isset($android_id) || trim($android_id) == "")) {
	print "1::Missing parameter";
	return;
}
//$mytime = time();
//if ($mytime > $t) { $diff = $mytime - $t; }
//else { $diff = $t - $mytime; }
//if ($diff > 300) { // more than 5 minutes
//	print "1::Authentication failure4:$t:$mytime"; exit;
//}
try{
    $db = new DBConn();
    $licensedb = $db->safe(trim($license));
    $android_id_db = $db->safe(trim($android_id));
    $query = "select * from it_pickup_instances where license = $licensedb";
    $obj = $db->fetchObject($query);
    if($obj){
        if($obj->is_active==1){ // means license is already activated throw an error
            print "1::license already activated";
        }else{ // activate the license
            $query = "update it_pickup_instances set is_active = 1 , android_id = $android_id_db  where license = $licensedb";
            $updated=$db->execUpdate($query);
            $json_obj = array();
            $qry = "select * from it_pickup_instances where license = $licensedb ";
            //$qry = "select pi.*,su.*,u.* from it_pos_instances pi , it_store_users su ,it_users u  where pi.store_id = su.store_id and su.user_id = u.id and pi.license = $licensedb";
            $inst_obj = $db->fetchObject($qry);
            $json_obj['instance'] = $inst_obj;
            
            //push all the users
           /* $query = "select id, code as username, store_name as name, password, usertype from it_codes where usertype = 2 and inactive = 0";
            $obj_users = $db->fetchObjectArray($query);
            if(isset($obj_users)){
                $json_obj['users'] = $obj_users;
            }
            
            $query = "select id, name, is_active from it_styles where is_active = 1";
            $obj_styles = $db->fetchObjectArray($query);
            if(isset($obj_styles)){
                $json_obj['styles'] = $obj_styles;
            }
            
            $query = "select id, name from it_sizes";
            $obj_sizes = $db->fetchObjectArray($query);
            if(isset($obj_sizes)){
                $json_obj['sizes'] = $obj_sizes;
            }
            
            $query = "select id, ctg_id, sequence, size_id from it_ck_sizes";
            $obj_ck_sizes = $db->fetchObjectArray($query);
            if(isset($obj_ck_sizes)){
                $json_obj['cksizes'] = $obj_ck_sizes;
            }
            
            $query = "select id, ctg_id, sequence, style_id from it_ck_styles";
            $obj_ck_styles = $db->fetchObjectArray($query);
            if(isset($obj_ck_styles)){
                $json_obj['ckstyles'] = $obj_ck_styles;
            }*/
            
            $json_str = json_encode($json_obj);
            print "0::$json_str";
        }
    }else{
        print "1::license not found in database";return;
    }
    
}catch(Exception $xcp){
    print "1::".$xcp->getMessage();
}
?>
