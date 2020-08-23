<?php
require_once "../it_config.php";
require_once "session_check.php";

if (isset($_GET['name']) && isset($_GET['value'])) {
$name = $_GET['name'];
$value = $_GET['value'];
if ($value == "0") {
unset($_SESSION[$name]);
} else {
$_SESSION[$name] = $value;
}
}

