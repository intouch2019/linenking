<?php
require_once("../../it_config.php");
require_once("session_check.php");
session_destroy();
header("Location: ".DEF_SITEURL."store");
exit();
?>
