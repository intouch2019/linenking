<?php

require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";

extract($_POST);
print_r($_POST);
//$designno=json_encode($designno);
//     print_r($designno);
// foreach ($designno as $designid) {
//     print_r($designid);
//     echo"<br>";
// }   
$user = getCurrUser();
if ($user->usertype != UserType::Admin && $user->usertype != UserType::CKAdmin) {
    print "You are not authorized to add a User";
    return;
}


$errors = array();
try {
    $_SESSION['form_post'] = $_POST;
    $db = new DBConn();

    if (count($errors) == 0) {
        print_r($designno[0]);
//            exit();
     if(isset($designno[0]) && $designno[0]!=""){
//        if ($designno[0] == -1) {
//            $query = "update it_ck_designs set core=$designtype where ctg_id=$category";
//            $db->execUpdate($query);
//            $success = "Design type updated sucessfully";
//        } else {

            foreach ($designno as $designid) {
//                exit();
                $query = "update it_ck_designs set core=$designtype where id= $designid";
//		$query .= " where id=$supplierid";

                $db->execUpdate($query);
            }
            $success = "Design type updated sucessfully";
            unset($_SESSION['form_post']);
//        }
     }
      
    }
} catch (Exception $xcp) {
    $clsLogger = new clsLogger();
    $clsLogger->logError("Failed to update $category:" . $xcp->getMessage());
    $errors['status'] = "There was a problem processing your request. Please try again later";
}
if (count($errors) > 0) {
    $_SESSION['form_errors'] = $errors;
    $redirect = "design/type";
} else {
    unset($_SESSION['form_errors']);
        $_SESSION['form_success'] = $success;
    $redirect = "design/type";
}
session_write_close();
header("Location: " . DEF_SITEURL . $redirect);
exit;
?>
