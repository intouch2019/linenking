<?php
require_once("../../../it_config.php");

$incfile="lib/sms/clsSMSHelper.php";
$incfile="t3.php";
if (@include($incfile)) { require_once($incfile); print "found\n"; }
print "done\n";

?>
