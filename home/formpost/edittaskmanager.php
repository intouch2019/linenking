<?php

require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once 'lib/users/clsUsers.php';

extract($_POST);
//print_r($_POST);
//exit;
$db = new DBConn();

$errors = array();
//print_r($errors);exit();
$success = array();
//print_r($errors);exit();
//print_r($_POST);
//echo "<br>";
if (isset($_POST['id']) && $_POST['id'] !== "") {
    $id = $_POST['id'];
}

if (isset($_POST['department']) && $_POST['department'] !== "") {
    $dept = $_POST['department'];
}else {
    $errors[] = "Please select a department.";
}


if (isset($_POST['usertype']) && $_POST['usertype'] !== "") {
    $utype = $_POST['usertype'];
}else {
    $errors[] = "Please select a user type.";
}

if (isset($_POST['username']) && $_POST['username'] !== "") {
    $uname = $_POST['username'];
}else {
    $errors[] = "Please enter the username.";
}

//if (isset($_POST['department']) && $_POST['department'] !== "") {
//    $dept = implode(", ", $_POST['department']);
//}

//if (isset($_POST['department']) && $_POST['department'] !== "") {
//    $dept = $_POST['department'];
//    foreach($dept as $value){
//        $db->execInsert("insert into it_task_manager set s_department='$s_deptdata->roles',image='$image',r_department='$value',s_name='$s_deptdata->store_name',subject='$sub',task_info ='$desc',status=1,received=2, createtime=now()");
//    }
//}


if (isset($_POST['subject']) && $_POST['subject'] !== "") {
    $sub = $_POST['subject'];
}else {
    $errors["subject"] = "Please enter the subject.";
}

if (isset($_POST['description']) && $_POST['description'] !== "") {
    $desc = $_POST['description'];
}else {
    $errors[] = "Please enter the description.";
}

   $file=$_FILES['file']["type"];
//   print_r($file); exit();
//  $filename=$_FILES['file']['name'];
if (isset($_FILES['file']) && $_FILES['file'] !== "") {
//if (isset($_FILES['file'])) {
//if (isset($_FILES['file']) && $_FILES['file'] !== "" && $_FILES['size'] !=0 && $_FILES['name'] != "") {
if ((($_FILES["file"]["type"] == "") || ($_FILES["file"]["type"] == "image/gif") || ($_FILES["file"]["type"] == "image/jpeg") || ($_FILES["file"]["type"] == "image/png")) && ($_FILES["file"]["size"] < 51240000)) {
    $image_name = ($_FILES['file']['name']);
} else {
//    $errors["file"] = 'Please enter an image file(gif/jpeg/png) and enter within 500kb size';
    $errors["file"] = 'Please select an image file(gif/jpeg/png) and select file size within 500kb';
}

//if ($_FILES["file"]["error"] != "0") {
//    if ($_FILES["file"]["error"] == 4) {
//        $errors['file'] = 'Please enter an image file to upload';
//    } else {
//
//        $errors['file'] = "Image error : " . $_FILES["file"]["error"];
//    }
//}
}
//echo "errors";
//print_r($errors);
//exit();

$user = getCurrUser();
$record = "";

if (count($errors) == 0) {
try {
    $db = new DBConn();
    if (isset($id) && $id !== "") {
        $record = $db->fetchObject("select * from it_task_manager where id = $id");
    }
//    print_r($record); exit();
    $s_deptdata = $db->fetchObject("select code,store_name,roles from it_codes where usertype= $user->usertype and code='$user->code'");
//    print_r($s_deptdata); exit();
    if (isset($_FILES['file']) && $_FILES['file'] !== "") {
       $extndiv = explode("/", $_FILES["file"]["type"]);
        $iname = explode("/", $_FILES["file"]["name"]);
        
         $extndiv = explode("/", $_FILES["file"]["type"]);
        $iname = explode("/", $_FILES["file"]["name"]);

        $filename_without_ext = preg_replace('/\\.[^.\\s]{3,4}$/', '', $iname);
        $extn = $extndiv[1];
        $str = implode(",", $filename_without_ext);
        $image = "$str.$extn";
    }else{
        $image = "";
    }
    
    

    if (isset($record) && $record !== "" && isset($id) && $id !== "") {
        
      
        if($image != ""){
          if (move_uploaded_file($_FILES['file']['tmp_name'], "../images/task/$image")) {
              
              $imgqry = ", image='$image'";
             }else{
              $imgqry = "";
        }}else{
            $imgqry = "";
        }
               foreach($dept as $value){
                    $db->execUpdate("update it_task_manager set s_department='$s_deptdata->roles' $imgqry,r_department='$value',s_name='$s_deptdata->store_name',subject='$sub',task_info ='$desc',receivername='$uname',status=1,progress=0,updatetime=now() where id=$id");
//                    $db->execInsert("insert into it_reassign_task_image set image = '$image',task_id = $id");
                    if($image != "" && $imgqry != ""){
                        $db->execInsert("insert into it_reassign_task_image set task_id = $id $imgqry");
                    }
              
               }
//              $db->execInsert("insert into it_reassign_task_image set image='$image',task_id=$id");
//               }
//              }
    } else {
//        echo "hiiiii";
//        print_r($image);exit();
        if($image != ""){
     if (move_uploaded_file($_FILES['file']['tmp_name'], "../images/task/$image")) {
//            // $image = $db->safe($image);
         $imgqry = ", image='$image'";
     }else{
         $imgqry = "";
        }}else{
            $imgqry = "";
        }

             foreach($dept as $value){
                $insertid = $db->execInsert("insert into it_task_manager set s_department='$s_deptdata->roles' $imgqry,r_department='$value',s_name='$s_deptdata->store_name',subject='$sub',task_info ='$desc',receivername='$uname',status=1,received=2, createtime=now()");
//                $db->execInsert("insert into it_reassign_task_image set image = '$image',task_id = $insertid");
                if($image != "" && $imgqry != ""){
                    $db->execInsert("insert into it_reassign_task_image set task_id = $insertid $imgqry");
                }
//            $db->execInsert("insert into it_reassign_task_image set image='$image',r_department='$value',task_id=$id");
             }
//             $db->execInsert("insert into it_reassign_task_image set image='$image',task_id=$id");

//             }
    }
} catch (Exception $xcp) {
    $clsLogger = new clsLogger();
    $clsLogger->logError("Failed to change password:$userid:" . $xcp->getMessage());
    $errors['password'] = "There was a problem processing your request. Please try again later";
}
}
if (count($errors) > 0) { if (isset($_POST['id']) && $_POST['id'] !== "") {
    
    $_SESSION['form_errors'] = $errors;
    $redirect = "reassigntaskmanager/ids=$id";
}else{
    $_SESSION['form_errors'] = $errors;
    $redirect = "edittaskmanager";
}
} else {
    unset($_SESSION['form_errors']);
    $_SESSION['form_success'] = $success;
    $redirect = "report/task/manager";
}
session_write_close();
//echo $redirect;
header("Location: ".DEF_SITEURL.$redirect);
exit;
?>