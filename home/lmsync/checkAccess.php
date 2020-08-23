<?php
require_once "authentication.php";
$auth = new authentication();
if(!$auth->loggedIn) {
$auth->authenticate();
exit();
}

$gCodeId = $auth->codeId;
$gCodeInfo = $auth->codeInfo;
?>
