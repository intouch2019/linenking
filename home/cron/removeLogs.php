<?php

require_once("/var/www/html/linenking/it_config.php");
require_once "lib/db/DBConn.php";

//For Testing
//require_once("../../it_config.php");
//LK
$db = new DBConn();
$currentDate = new DateTime();
echo "currentdate=". date('Y-m-d')."<br>";
$currentDate->sub(new DateInterval('P1Y'));
$getdate = $currentDate->format('Y-m-d 00:00:00');
echo "Delete Data till date before :".$getdate;
//$getdate = '2014-05-05 00:00:00';
echo "<br>";

exit();
$sql = "select max(id) as maxid from it_logs where createtime < '$getdate' ";
$maxidobj = $db->fetchObject($sql);
if (isset($maxidobj->maxid) && !empty($maxidobj) && $maxidobj->maxid > 0) {
    $mxid = $maxidobj->maxid;
    do {
        echo "<br>";
        $delsql = "delete from it_logs where id <= $mxid limit 2000 ";
        echo $delsql;
        echo $db->execQuery($delsql);
        echo "<br>";
        $sql = "select count(id) as count from it_logs where id <= $mxid";
        echo $sql;
        $countlogs = $db->fetchObject($sql);
        print_r($countlogs);
    } while ($countlogs->count > 0);
} else {
    echo "No record found";
}