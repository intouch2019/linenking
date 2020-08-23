<?php
include "checkAccess.php";
require_once "../../ck_config.php";
require_once "lib/codes/clsCodes.php";
$clsCodes = new clsCodes();
$codeInfo = $clsCodes->getCodeInfoById($gCodeId);

print "date:$codeInfo->order_format,$codeInfo->accountinfo";
?>
