<?php
require_once "../it_config.php";
require_once "session_check.php";
//print_r($_GET);
// print_r($_SESSION);
if (isset($_GET['identifier']) ) {
  $identifier = $_GET['identifier'];
  $rowno = $_SESSION[$identifier]  ;
  //print $rowno;
 //echo json_encode(array("rowno" => $rowno)
  echo $rowno;
//         );
} else {
//$_SESSION[$name] = $value;
}

