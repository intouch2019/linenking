<?php
require_once("../it_config.php");
require_once("session_check.php");
require_once("lib/logger/clsLogger.php");
$logger = new clsLogger();
$logger->logInfo("Logout:".getCurrUser()->username);
session_destroy();
header("Location: ".DEF_SITEURL);
exit();
?>
