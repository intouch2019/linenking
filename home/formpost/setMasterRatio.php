<?php
require_once("../../it_config.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once 'lib/users/clsUsers.php';
require_once "session_check.php";

//extract($_GET);
$user = getCurrUser();
$userpage = new clsUsers();
$db = new DBConn();
//$pagecode = $db->safe($_SESSION['pagecode']);
$page = $db->fetchObject("select * from it_pages where pagecode ='sbratio'");
if($page){
    $allowed = $userpage->isAuthorized($user->id, $page->pagecode);
    if (!$allowed) { header("Location: ".DEF_SITEURL."unauthorized"); return; }
}//else{ header("Location:".DEF_SITEURL."nopagefound"); return; }
//print_r($user);
if (isset($_GET['sid']))
	$store_id = $_GET['sid'];
if (isset($_GET['cat_id']))
	$cat_id = $_GET['cat_id'];
if (isset($_GET['r_type']))
	$r_type = $_GET['r_type'];
if (isset($_GET['user_id']))
	$user_id = $_GET['user_id'];
if (isset($_GET['cat_name']))
	$cat_name = $_GET['cat_name'];
$store_name="";
$success="";
$errors = array();
$success = array();

try
{
//         $dsobj = $db->fetchObjectArray("select id from it_ck_designs where ctg_id=$cat_id  order by design_no ");
//          $no_designs = count($dsobj);
    
    $st_id = $db->fetchObject("select store_name from it_codes where id=$store_id");
    if (isset($st_id)) {
        $store_name = $st_id->store_name;
    } else {
        $store_name = "";
    }
    
    
    if($r_type==2)
    {
              
      $ratiobjs=$db->fetchObjectArray("select * from it_store_ratios where store_id = $store_id and ctg_id = $cat_id and ratio_type = $r_type");  
        foreach ($ratiobjs as $ratiobj) {
    $query="select id from it_store_ratios_bk where store_id = $ratiobj->store_id and ctg_id = $ratiobj->ctg_id and "
                                . "ratio_type = $ratiobj->ratio_type and style_id = $ratiobj->style_id and size_id = $ratiobj->size_id and "
                                . "design_id = $ratiobj->design_id";
//                        print $query.";<br/>";
                        $obj = $db->fetchObject($query);
                        if(isset($obj) && !empty($obj)){
//                            echo'updated';
                            $query = "update it_store_ratios_bk set ratio=$ratiobj->ratio,updated_by=$ratiobj->updated_by,updatetime=now() where id = $obj->id";
                            $db->execUpdate($query);
                        }else{
                            $query = "insert into it_store_ratios_bk set store_id=$ratiobj->store_id,ctg_id=$ratiobj->ctg_id,"
                                    . "style_id=$ratiobj->style_id,size_id=$ratiobj->size_id,ratio_type=$ratiobj->ratio_type,"
                                    . "ratio=$ratiobj->ratio ,design_id = $ratiobj->design_id, updated_by=$ratiobj->updated_by,createtime='".$ratiobj->createtime."',updatetime='".$ratiobj->updatetime."'";
                            $db->execInsert($query);
//                            echo 'query:'.$query;
                            }
    
        }
        
      $db->execQuery("delete from it_store_ratios where store_id = $store_id and ctg_id = $cat_id and ratio_type = $r_type ");
      
    }
          $styleobj = $db->fetchObjectArray("select s1.style_id,s2.name as style_name from it_ck_styles s1,it_styles s2 where s1.ctg_id=$cat_id and s1.style_id=s2.id  and s2.is_active = 1 order by s1.sequence");
                $no_styles = count($styleobj);
//                  print_r($styleobj);
                $sizeobj = $db->fetchObjectArray("select s1.size_id, s2.name as size_name from it_ck_sizes s1,it_sizes s2 where s1.ctg_id=$cat_id and s1.size_id=s2.id order by s1.sequence");
//                  print_r($sizeobj);
                $no_sizes = count($sizeobj);
//                    for ($i = 0; $i < $no_designs; $i++) {
//                        $design_id=$dsobj[$i]->id;
                    for ($j = 0; $j < $no_styles; $j++) {
                        $style_id=$styleobj[$j]->style_id;
                    for ($k = 0; $k < $no_sizes; $k++) {
                        $size_id=$sizeobj[$k]->size_id;
                         $query="select id from it_store_ratios where store_id = $store_id and ctg_id = $cat_id and "
                                . "ratio_type = $r_type and style_id = $style_id and size_id = $size_id and "
                                . "design_id = -1";
//                        print $query.";<br/>";
                        $obj = $db->fetchObject($query);
                        if(isset($obj) && !empty($obj)){
                            $query = "update it_store_ratios set ratio=1,updated_by=$user_id,updatetime=now() where id = $obj->id";
                            $db->execUpdate($query);
                        }else{
                            $query = "insert into it_store_ratios set store_id=$store_id,ctg_id=$cat_id,"
                                    . "style_id=$style_id,size_id=$size_id,ratio_type=$r_type,"
                                    . "ratio=1,design_id = -1, updated_by=$user_id,createtime=now()";
                            $db->execInsert($query);
                        }
                        
                    }
                  }
                //}

                 $success =  $store_name.": Default ratio (i.e. 1) set Successfully for Category:".$cat_name;
                
    

}
 catch (Exception $xcp) {
    $errors['status'] = "There was a problem processing your request. Please try again later";
}


if (count($errors) > 0) {
    $_SESSION['form_errors'] = $errors;
} else {
    $_SESSION['form_success'] = $success;
}

header("Location: " . DEF_SITEURL . "store/ratio");
exit;


?>