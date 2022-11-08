<?php

require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once 'lib/users/clsUsers.php';

/* $currUser = getCurrUser();
  if ($currUser->usertype != UserType::Admin && $currUser->usertype != UserType::CKAdmin) {
  print "Unauthorized access."; return;
  } */
//print_r($_GET['id']);
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $id = trim($id);
} else {
    print "Nothing to delete";
    return;
}

$db = new DBConn();
//echo "update companydetails set inactive=1 where id=$id";
//exit();
if (isset($_GET['id'])) {
    $db->execUpdate("update contactdetails set inactive=1 where id=$id");
}
header("Location: " . DEF_SITEURL . "companydetails");
exit;
