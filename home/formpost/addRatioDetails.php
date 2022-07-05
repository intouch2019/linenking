<?php

require_once "../../it_config.php";
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once 'lib/users/clsUsers.php';
require_once 'lib/core/Constants.php';

extract($_POST);
//print_r($_POST);
$errors = array();
$success = array();
$user = getCurrUser();
$db = new DBConn();
$designids = $_POST['designids'];
$category_id = $_POST['category'];
$stores = $_POST['sid'];
$store_ids = $_POST['sid'];
$ratio_type = $_POST['rtype'];
$userid = $_POST['userid'];
$core = $_POST['core'];
$level = $_POST['level'];
$level = $db->safe($level);
echo $level;
//$mrp = $_POST['mrp'];
//print $designids;

try {
    $Clause = "";
    $storeqry = "";
    if ($stores != -1) {
        $storeqry = "and id in ($stores) ";
    } else {
        $storeqry = " and usertype=4";   // for all stores selection   
    }
    $sids = $db->fetchObjectArray("select id,store_name from it_codes where level =$level $storeqry ");

    foreach ($sids as $store) {         //This section belongs only for passing error message for dealer login if ratio decreased by dealer.
        foreach ($_POST as $key => $value) {
                if (preg_match("/_/", $key)) {
                $arr = explode("_", $key);
 $design_id = $designids;
            if ($ratio_type == RatioType::Base && (trim($value) == null || (trim($value) != "" && trim($value) < 1))) {
                //error
                $errors['error'] = "Entered numeric value should be greater than zero";
            }
            //check if aleady exist for given store
            if (trim($value) != "" && is_numeric($value) && count($errors) == 0) { // && trim($value) > 0
                if (trim($design_id) == "-1") { // means all designs
                    $query = "select r.id,r.ratio,r.core from it_store_ratios r  where r.store_id=$store->id and r.ctg_id=$category_id and "
                            . "r.design_id = -1 and r.ratio_type=$ratio_type and r.style_id=$arr[0] "
                            . "and r.size_id=$arr[1] and r.core=$core";

                    $obj = $db->fetchObject($query);

                    //  exit();
                    if (isset($obj) && !empty($obj)) {
                        if ($user->usertype == UserType::Dealer && $obj->core == 1) {
                            if ($obj->ratio > $value) {
                                $errors['status'] = "Decreasing the standing ratio for the set value is not permitted.";
                            }
                        }
                    }
                } else { 
                    $design_arr = explode(",", $designids);
                    //print_r($design_arr);
                    for ($i = 0; $i < sizeof($design_arr); $i++) {
                        $query = "select r.id,r.ratio,r.core from it_store_ratios r  where r.store_id=$store->id and r.ctg_id=$category_id and "
                                . "r.design_id = $design_arr[$i] and r.ratio_type=$ratio_type and r.style_id=$arr[0] "
                                . "and r.size_id=$arr[1] and r.core=$core";

                        $obj = $db->fetchObject($query);
                        //print_r($obj);

                        if (isset($obj) && !empty($obj)) {
                            print("<br>lineno 205<br> $obj->ratio ==> $value <br>");
                            if ($user->usertype == UserType::Dealer && $obj->core == 1) {
                                if ($obj->ratio > $value) {
                                    $errors['status'] = "Decreasing the standing ratio for the set value is not permitted.";
                                }
                            }
                        }
                    
                    if ($ratio_type == RatioType::Standing) { // then same update/insert goes against standing ratio type i.e. for exceptional design
                        
                        $query = "select r.id,r.ratio,r.core from it_store_ratios r  where r.store_id=$store->id and r.ctg_id=$category_id and "
                                . "r.design_id = $design_arr[$i] and r.ratio_type=" . RatioType::Standing . " and r.style_id=$arr[0] "
                                . "and r.size_id=$arr[1] and r.core=$core";
//                        print_r($query);
                        $obj = $db->fetchObject($query);
                        if (isset($obj) && !empty($obj)) {
                            if ($user->usertype == UserType::Dealer && $obj->core == 1) {
                                if ($obj->ratio > $value) {
                                    $errors['status'] = "Decreasing the standing ratio for the set value is not permitted.";
                                }
                            }
                    }}
                    }
                }
            }
        }
        
       }
    }







            //Main logic start here for setting ratios for different designs 

    foreach ($sids as $store) {
        $Clause .= $store->store_name . ",";

//      exit();
        $obj = $db->fetchObject("select level from it_codes where id = $store->id");

        foreach ($_POST as $key => $value) {
            if (preg_match("/_/", $key)) {
                $arr = explode("_", $key);

                if ($ratio_type == RatioType::Base && (trim($value) == null || (trim($value) != "" && trim($value) < 1))) {
                    //error
                    $errors['error'] = "Entered numeric value should be greater than zero";
                }
                //check if aleady exist for given store
                if (trim($value) != "" && is_numeric($value) && count($errors) == 0) { // && trim($value) > 0
                    $design_id = $designids;
                    //step 1 : check if all designs is selected, if yes then insert record against all designs
                    if (trim($design_id) == "-1") { // means all designs
                        /* NEW CHANGES FOR ALL DESIGN NUMBER */
//                        $query = "select r.id,r.ratio,r.core from it_store_ratios r, it_ck_designs d  where r.store_id=$store->id and r.ctg_id=$category_id and "
//                                . "r.design_id = -1 and r.ratio_type=$ratio_type and r.style_id=$arr[0] "
//                                . "and r.size_id=$arr[1] and r.core=$core and r.core=d.core and d.id =r.design_id ";

                        $query = "select r.id,r.ratio,r.core from it_store_ratios r  where r.store_id=$store->id and r.ctg_id=$category_id and "
                                . "r.design_id = -1 and r.ratio_type=$ratio_type and r.style_id=$arr[0] "
                                . "and r.size_id=$arr[1] and r.core=$core";

                        $obj = $db->fetchObject($query);

                        //  exit();
                        if (isset($obj) && !empty($obj)) {
                            if ($user->usertype == UserType::Dealer && $obj->core == 1) {
                                if ($obj->ratio <= $value) {


//                                    $upt = "update it_store_ratios r, it_ck_designs d  set r.ratio=$value,r.updated_by=$userid,r.updatetime=now()"
//                                            . " where r.id = $obj->id and d.core=$core and d.id =r.design_id";

                                    $upt = "update it_store_ratios r  set r.ratio=$value,r.updated_by=$userid,r.updatetime=now()"
                                            . " ,r.is_exceptional=1,r.is_exceptional_active=1 where r.id = $obj->id and r.core=$core ";
//                                    print "<br>$upt";

                                    $db->execUpdate($upt);
                                    $success = " $Clause store ratios updated successfully <br>";
                                } else {
                                    // ERROR CODE HERE       

                                    $errors['status'] = "Decreasing the standing ratio for the set value is not permitted.";
                                    //Decreasing the standing ratio for the set value is not permitted.
                                }
                            } else {
//                                $upt = "update it_store_ratios r, it_ck_designs d  set r.ratio=$value,r.updated_by=$userid,r.updatetime=now()"
//                                        . " where r.id = $obj->id and d.core=$core and d.id =r.design_id";
//                                        
                                $upt = "update it_store_ratios r  set r.ratio=$value,r.updated_by=$userid,r.updatetime=now()"
                                        . " where r.id = $obj->id and r.core=$core ";

//                                print "<br>$upt";

                                $db->execUpdate($upt);
                                $success = " $Clause store ratios updated successfully <br>";
                            }
                        } else {
                            $ins = "insert into it_store_ratios set store_id=$store->id,ctg_id=$category_id,"
                                    . "design_id=$design_id,style_id=$arr[0],size_id=$arr[1],"
                                    . "ratio_type=$ratio_type,ratio=$value,core=$core,updated_by=$userid,is_exceptional=1,is_exceptional_active=1,createtime=now()";

                            $db->execInsert($ins);
                            $success = " $Clause store ratios added successfully";
                        }

                        /* NEW CHANGES ENDED */

                        /* $q = "select * from it_ck_designs where ctg_id = $category_id ";
                          //                    print "<br>$q";
                          $cdobjs = $db->fetchObjectArray($q);
                          if(! empty($cdobjs)){
                          foreach($cdobjs as $cdobj){
                          $query = "select c.id as ctg_id,c.name as category,c.active,d.design_no,d.image,"
                          . "d.lineno,d.rackno from it_categories c,it_ck_designs d,"
                          . "it_items i where c.id=d.ctg_id and d.id=i.design_id and "
                          . "c.id=i.ctg_id and c.id=$category_id and d.id=$cdobj->id group by d.design_no";
                          //echo $query."<br/>";
                          $iobj = $db->fetchObject($query);

                          $query = "select * from it_store_ratios where store_id=$store_id and ctg_id=$category_id and "
                          . "design_id = $cdobj->id and ratio_type=$ratio_type and style_id=$arr[0] "
                          . "and size_id=$arr[1]";
                          $obj = $db->fetchObject($query);
                          //                            print "<br>";
                          //                            print_r($obj);
                          if (isset($obj) && !empty($obj)) {
                          //$upt = "update it_store_ratios set ratio=$value,updated_by=$userid,updatetime=now() where store_id=$store_id and style_id=$arr[0] and size_id=$arr[1] and ratio_type=$ratio_type and ctg_id=$category_id and design_id=$design_id";
                          $upt = "update it_store_ratios set ratio=$value,updated_by=$userid,updatetime=now()"
                          . " where id = $obj->id ";
                          //                                print "<br>$upt";
                          $db->execUpdate($upt);
                          $success = " $Clause store ratios updated successfully";
                          }else{
                          $ins = "insert into it_store_ratios set store_id=$store_id,ctg_id=$category_id,"
                          . "design_id=$cdobj->id,style_id=$arr[0],size_id=$arr[1],"
                          . "ratio_type=$ratio_type,ratio=$value,updated_by=$userid,createtime=now()";
                          //                                print "<br>".$ins;
                          $db->execInsert($ins);
                          $success = " $Clause store ratios added successfully";
                          }
                          }
                          } */
                    } else { // means specific design selected
                        //$obj = $db->fetchObject("select * from it_store_ratios where store_id=$store_id and ctg_id=$category_id and design_id=$design_id and ratio_type=$ratio_type and style_id=$arr[0] and size_id=$arr[1] and mrp = $mrp ");
                        $design_arr = explode(",", $designids);
                        //print_r($design_arr);
                        for ($i = 0; $i < sizeof($design_arr); $i++) {
                            //echo $design_arr[$i];
//                            $query = "select r.id, r.ratio ,d.core from it_store_ratios r, it_ck_designs d  where r.store_id = $store->id and r.ctg_id = $category_id and "
//                                    . "r.ratio_type = $ratio_type and r.style_id = $arr[0] and r.size_id = $arr[1] and "
//                                    . "r.design_id = $design_arr[$i] and d.core=$core and d.id =r.design_id";


                            $query = "select r.id,r.ratio,r.core from it_store_ratios r  where r.store_id=$store->id and r.ctg_id=$category_id and "
                                    . "r.design_id = $design_arr[$i] and r.ratio_type=$ratio_type and r.style_id=$arr[0] "
                                    . "and r.size_id=$arr[1] and r.core=$core";

                            //        print $query ;


                            $obj = $db->fetchObject($query);
//                            print_r($obj);

                            if (isset($obj) && !empty($obj)) {
                                print("<br>lineno 205<br> $obj->ratio ==> $value <br>");
                                if ($user->usertype == UserType::Dealer && $obj->core == 1) {
                                    if ($obj->ratio <= $value) {
//                                        $query = "update it_store_ratios r, it_ck_designs d  set r.ratio=$value,r.updated_by=$userid,r.updatetime=now() "
//                                                . "where d.core=$core and d.id =r.design_id and r.id = $obj->id";

                                        $upt = "update it_store_ratios r  set r.ratio=$value,r.updated_by=$userid,r.updatetime=now()"
                                                . " where r.id = $obj->id and r.core=$core ";
                                        //   print_r($upt);

                                        $db->execUpdate($upt);
                                    } else {
                                        //ERROR CODE HERE

                                        $errors['status'] = "Decreasing the standing ratio for the set value is not permitted.";
                                    }
                                } else {
//                                $upt = "update it_store_ratios r, it_ck_designs d  set r.ratio=$value,r.updated_by=$userid,r.updatetime=now()"
//                                        . " where r.id = $obj->id and d.core=$core and d.id =r.design_id";

                                    $upt = "update it_store_ratios r  set r.ratio=$value,r.updated_by=$userid,r.updatetime=now()"
                                            . " where r.id = $obj->id and r.core=$core ";

                                    $db->execUpdate($upt);
                                    $success = " $Clause store ratios updated successfully <br>";
                                }
                            } else {
                                $query = "insert into it_store_ratios set store_id=$store->id,ctg_id=$category_id,"
                                        . "style_id=$arr[0],size_id=$arr[1],ratio_type=$ratio_type,"
                                        . "ratio=$value,design_id = $design_arr[$i],core=$core, updated_by=$userid,createtime=now()";

                                $db->execInsert($query);
                            }



                            if ($ratio_type == RatioType::Standing) { // then same update/insert goes against standing ratio type i.e. for exceptional design
//                                $query = "select r.id, r.ratio  ,d.core from it_store_ratios r, it_ck_designs d  where r.store_id = $store->id and r.ctg_id = $category_id and "
//                                        . "r.ratio_type = " . RatioType::Standing . " and r.style_id = $arr[0] and r.size_id = $arr[1] and "
//                                        . "r.design_id = $design_arr[$i] and d.core=$core and d.id =r.design_id";
                                $query = "select r.id,r.ratio,r.core from it_store_ratios r  where r.store_id=$store->id and r.ctg_id=$category_id and "
                                        . "r.design_id = $design_arr[$i] and r.ratio_type=" . RatioType::Standing . " and r.style_id=$arr[0] "
                                        . "and r.size_id=$arr[1] and r.core=$core";

                                $obj = $db->fetchObject($query);
                                if (isset($obj) && !empty($obj)) {
                                    if ($user->usertype == UserType::Dealer && $obj->core == 1) {
                                        if ($obj->ratio <= $value) {

//                                            $query = "update it_store_ratios r, it_ck_designs d  set r.ratio=$value,r.updated_by=$userid,r.updatetime=now()"
//                                                    . ",r.is_exceptional=1,r.is_exceptional_active=1 where d.core=$core and d.id =r.design_id and r.id = $obj->id";

                                            $upt = "update it_store_ratios r  set r.ratio=$value,r.updated_by=$userid,r.updatetime=now()"
                                                    . ",r.is_exceptional=1,r.is_exceptional_active=1 where r.id = $obj->id and r.core=$core ";

                                            $db->execUpdate($upt);
                                        } else {
                                            // ERROR CODE HERE  

                                            $errors['status'] = "Decreasing the standing ratio for the set value is not permitted.";
                                        }
                                    } else {
//                                $upt = "update it_store_ratios r, it_ck_designs d  set r.ratio=$value,r.updated_by=$userid,r.updatetime=now()"
//                                        . " where r.id = $obj->id and d.core=$core and d.id =r.design_id";

                                        $upt = "update it_store_ratios r  set r.ratio=$value,r.updated_by=$userid,r.updatetime=now() ,r.is_exceptional=1,r.is_exceptional_active=1"
                                                . " where r.id = $obj->id and r.core=$core ";
                                        $db->execUpdate($upt);
                                        $success = " $Clause store ratios updated successfully <br>";
                                    }
                                } else {
                                    $query = "insert into it_store_ratios set store_id=$store->id,ctg_id=$category_id,"
                                            . "style_id=$arr[0],size_id=$arr[1],ratio_type=" . RatioType::Standing . ","
                                            . "ratio=$value,design_id = $design_arr[$i],core=$core updated_by=$userid,is_exceptional=1,is_exceptional_active=1,createtime=now()";

                                    $db->execInsert($query);
                                }
                            }
                        }
                        $success = " $Clause store ratios added successfully";
                        /* $query = "select * from it_store_ratios where store_id=$store_id and ctg_id=$category_id  and ratio_type=$ratio_type and style_id=$arr[0] and size_id=$arr[1]";
                          $obj = $db->fetchObject($query);
                          if (isset($obj) && !empty($obj)) {
                          //$upt = "update it_store_ratios set ratio=$value,updated_by=$userid,updatetime=now() where store_id=$store_id and style_id=$arr[0] and size_id=$arr[1] and ratio_type=$ratio_type and ctg_id=$category_id and design_id=$design_id";
                          $upt = "update it_store_ratios set ratio=$value,updated_by=$userid,updatetime=now() where id = $obj->id ";
                          $db->execUpdate($upt);
                          $success = " $Clause store ratios updated successfully";
                          }else{
                          //$ins = "insert into it_store_ratios set store_id=$store_id,ctg_id=$category_id,design_id=$design_id,style_id=$arr[0],size_id=$arr[1],mrp=$mrp,ratio_type=$ratio_type,ratio=$value,updated_by=$userid,createtime=now()";
                          $ins = "insert into it_store_ratios set store_id=$store_id,ctg_id=$category_id,style_id=$arr[0],size_id=$arr[1],ratio_type=$ratio_type,ratio=$value,updated_by=$userid,createtime=now()";
                          $db->execInsert($ins);
                          $success = " $Clause store ratios added successfully";
                          } */
                    }
                }
            }
        }
    }
    $Clause = rtrim($Clause, ',');
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
?>
