<?php
include "../../it_config.php";
require_once "store/session_check.php";
require_once "lib/core/strutil.php";

if (isset($_GET['today'])) {
$today = date("d-m-Y");
$_SESSION['daterange']="$today,$today";
} else if (isset($_GET['d1']) && isset($_GET['d2'])) {
$_SESSION['daterange']=ddmmyy($_GET['d1']).",".ddmmyy($_GET['d2']);
}
print "done";
?>
