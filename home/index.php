<?php
require_once "../it_config.php";
require_once "session_check.php";
require_once("UriHandler.class.php");
$uri = null;
if (isset($_GET['uri'])) { $uri = $_GET['uri']; }
$uriHandler = new UriHandler($uri);

$uriHandler->displayContent();
?>
