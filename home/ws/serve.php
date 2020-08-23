<?php
require_once "../../it_config.php";
require_once "lib/sms/clsSMSServe.php";

$storeid = isset($_GET['storeid']) ? $_GET['storeid'] : false;
$clsSMSServe = new clsSMSServe();
if (isset($_GET['sentid'])) {
        $clsSMSServe->processed($_GET['sentid']);
} else {
        $entry = $clsSMSServe->getEntry($storeid);
        if ($entry) { print $entry; }
        else { print "0"; }
}
