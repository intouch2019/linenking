<?php
require_once "../../it_config.php";
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once 'lib/users/clsUsers.php';
require_once 'lib/core/Constants.php';

extract($_POST);
//print_r($_POST);
//print_r($_POST);
$errors = array();
$success = array();
$user = getCurrUser();
$db = new DBConn();
$Clause="";

try{
 foreach($designid as $darrobj){
        //print "<br><br>Design OBJ begins: ";
        //print_r($darrobj);
        $store_id =  $darrobj['sid'];
        $design_id = $darrobj['designid'];
        $ctg_id = $darrobj['category'];
        $ratio_type = $darrobj['rtype'];
        $userid = $darrobj['userid'];
        $items = $darrobj['item'];
        if(trim($Clause)!=""){
            $sid = $db->fetchObject("select * from it_codes where id=$store_id");
            if (isset($sid)) {
                $Clause = $sid->store_name;
            } else {
                $Clause = "";
            }
        }
       // print "<br><br>";
        foreach($items as $key => $value){
           if(trim($value)!=""){
                $karry = explode("_", $key);
                $item_id = $karry[1];
                $style_id = $karry[2];
                $size_id = $karry[3];
                $query = "select * from it_store_ratios where store_id=$store_id and ctg_id=$ctg_id and "
                        . "design_id = $design_id and ratio_type=$ratio_type and style_id=$style_id "
                        . "and size_id=$size_id";
                //print "<br>$query<br>";
                $obj = $db->fetchObject($query);
                if (isset($obj) && !empty($obj)) {
                    //$upt = "update it_store_ratios set ratio=$value,updated_by=$userid,updatetime=now() where store_id=$store_id and style_id=$arr[0] and size_id=$arr[1] and ratio_type=$ratio_type and ctg_id=$category_id and design_id=$design_id";
                    $upt = "update it_store_ratios set ratio=$value,updated_by=$userid,updatetime=now()"
                            . " where id = $obj->id ";
    //                                print "<br>$upt";
                    //print "<br>$upt<br>";
                    $db->execUpdate($upt);
                    $success = " $Clause store ratios updated successfully";
                }else{
                    $ins = "insert into it_store_ratios set store_id=$store_id,ctg_id=$ctg_id,"
                            . "design_id=$design_id,style_id=$style_id,size_id=$size_id,"
                            . "ratio_type=$ratio_type,ratio=$value,updated_by=$userid,createtime=now()";
    //                                print "<br>".$ins;
                    //print "<br>$ins<br>";
                    $db->execInsert($ins);
                    $success = " $Clause store ratios added successfully";
                }  
            }
        }
     
 }
} catch (Exception $xcp) {

    $errors['status'] = "There was a problem processing your request. Please try again later";
}
if (count($errors) > 0) {
    $_SESSION['form_errors'] = $errors;
} else {
    $_SESSION['form_success'] = $success;
}

header("Location: " . DEF_SITEURL . "store/ratio");
exit;
