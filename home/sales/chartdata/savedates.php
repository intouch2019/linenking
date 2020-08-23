<?php

include "/var/www/intouch/home/store/appConfig.php";
require_once "store/session_check.php";

if (isset($_GET['today'])) {
$today = date("Y-m-d");
$_SESSION['daterange']="$today,$today";
} else if (isset($_GET['d1']) && isset($_GET['d2'])) {
$_SESSION['daterange']=$_GET['d1'].",".$_GET['d2'];
}
print "done";
?>
