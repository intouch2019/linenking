<?php
//ini_set('max_execution_time', 300);
include "../../it_config.php";
require_once "session_check.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
//require_once "lib/logger/clsLogger.php";
require_once ("lib/core/strutil.php");


extract($_GET);
//print_r($_GET);

$currStore = getCurrUser();
if (!$currStore) {
    print "User session timedout. Please login again";
    return;
}


  $db = new DBConn();

   
//// Check connection
//if ($db->connect_error) {
//    die("Connection failed: " . $db->connect_error);
//}

// Retrieve data from AJAX request
  if($_GET['utr']=== '0'){
      print "Please Fill Utr ";
    return;
  }
  if($_GET['remark']===''){
      print "Please Fill  Remark";
    return;
  }
$utrval = $_GET['utr'];
$remarkval = $_GET['remark'];
$invid = $_GET['invid'];


if(isset($utrval) && isset($remarkval) && isset($invid)){
    // Construct the SQL query
$query = "UPDATE it_saleback_invoices SET utr = '$utrval', remark = '$remarkval', is_sb_transit_complete = 2 WHERE id = $invid";
//echo $query;
$db->execUpdate($query);
// Execute the query
}





// Close the database connection

?>