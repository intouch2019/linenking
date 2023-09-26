<?php

require_once("../../it_config.php");
require_once("session_check.php");
require_once "../lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once 'lib/users/clsUsers.php';
require_once "lib/core/strutil.php";

$user = getCurrUser();
extract($_GET);
$db = new DBConn();

if (isset($dbusr) || trim($dbusr) == "") {

    $DBUSR = DB_USR;
}

if (isset($dbpass) || trim($dbpass) == "") {

    $DBPASS = DB_PWD;
}

if (!isset($dbtables) || trim($dbtables) == "") {

    $DBTABLES = "";
} else {
    $DBTABLES = $dbtables;
}

$DBNAME = DB_NME;

$filename = "lk_portal" . date("d-m-Y") . ".sql.gz";
$mime = "application/x-gzip";

header("Content-Type: " . $mime);
header('Content-Disposition: attachment; filename="' . $filename . '"');

$cmd = "mysqldump -h 172.31.21.91 -u $DBUSR --password=$DBPASS $DBNAME $DBTABLES | gzip --best";

passthru($cmd);

exit(0);