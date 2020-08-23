<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once 'lib/users/clsUsers.php';

extract($_GET);
$db = new DBConn();
$user = getCurrUser();
$userpage = new clsUsers();
$pagecode = $db->safe($_SESSION['pagecode']);
$page = $db->fetchObject("select * from it_pages where pagecode = $pagecode");
$errors = array();
$cnt = 0;
$success = "";
if($page){
    $allowed = $userpage->isAuthorized($user->id, $page->pagecode);
    if (!$allowed) { header("Location: ".DEF_SITEURL."unauthorized"); return; }
}else{ header("Location:".DEF_SITEURL."nopagefound"); return; }

//To dos:
//step 1: Chk which pages were removed from the selected usertype
//step 2: Delete existing pages & Add new pages to the selected usertype
//step 3: Delete removed pages & Add new pages to the all the users under 
//        selected usertype
 $remove_pgs = array();
 $allpgs = explode(",",$pgs); 
try{
    //step 1: CHK which pgs were removed
    //   1.1: fetch existing alloted pages to the selected usertype
    $qry = "select page_id from it_usertype_pages where usertype = $usertype";
    $epobjs = $db->fetchObjectArray($qry);
    //step 1.2  fetch pgs to remove
    foreach($epobjs as $epobj){ 
        if(!in_array($epobj->page_id, $allpgs)){
            array_push($remove_pgs, $epobj->page_id);            
        }        
    }
    
    //step 2: Delete existing pages & Add new pages to the selected usertype
    $delqry = "delete from it_usertype_pages where usertype = $usertype";    
    $db->execQuery($delqry);
    foreach($allpgs as $pg){        
        if(trim($pg)!=""){
            $iq = "insert into it_usertype_pages set page_id = $pg , usertype = $usertype , createtime = now()";            
            $insert_id = $db->execInsert($iq);
            if($insert_id){
                $cnt++;
            }
        }
       
    }
    
    //step 3: Delete removed pages from all the users under selected user type
    //    3.1: fetch all users under the selected usertype
    $uqry = "select * from it_codes where usertype = $usertype";
    $uobjs = $db->fetchObjectArray($uqry);
    //step 3.2: For all users first delete the removed pages
    foreach($uobjs as $uobj){
        foreach($remove_pgs as $key => $r_pg_id){
            $rqry = "delete from it_user_pages where user_id = $uobj->id and page_id = $r_pg_id";
            $db->execQuery($rqry);
        }        
    }
    //step 3.3 : Add new pgs to all the users under selected usertype
    foreach($uobjs as $uobj){
      foreach($allpgs as $pg){
          $pqry = "select * from it_user_pages where user_id = $uobj->id and page_id = $pg";
          $pexists = $db->fetchObject($pqry);
          if(!isset($pexists)){
              //insert the new pg
              $ipqry = "insert into it_user_pages set user_id = $uobj->id ,page_id = $pg ";
              $db->execInsert($ipqry);
          }
      }  
    }
}catch(Exception $xcp){
   $errors['xcp'] = $xcp->getMessage();
}
if($cnt > 0){
  $success = "Page(s) assigned successfully ";
}else{
  $errors['pg'] =  "Error during assigning pages. Contact Intouch";
}  
if (count($errors)>0) {
        unset($_SESSION['form_success']);       
        $_SESSION['form_errors'] = $errors;
  } else {
        unset($_SESSION['form_errors']);
        $_SESSION['form_success'] = $success;        
  }
  header("Location: ".DEF_SITEURL."admin/usertype/assignpages");
  exit;
?>
