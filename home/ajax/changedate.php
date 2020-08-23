<?php 
require_once "../../it_config.php";
require_once "lib/core/strutil.php";
require_once "lib/db/DBConn.php";
require_once("session_check.php");

$mindate = isset($_GET['mindate']) ? ($_GET['mindate']) : false;
$maxdate = isset($_GET['maxdate']) ? ($_GET['maxdate']) : false;
if (!$mindate || !$maxdate) { return error("missing parameters"); }

try {
        $mnd = strtotime($mindate);
        $mndate = date("Y-m-d", $mnd);
        
        $mxd = strtotime($maxdate);
        $mxdate = date("Y-m-d", $mxd);
    //print $mndate; print $mxdate;
    if ($mndate && $mxdate) { success($mndate,$mxdate); }
    else { error("MRPs Not Found"); }
} catch(Exception $xcp){
    echo "error:There was a problem processing your request. Please try again later.";
 //   return;
}

function error($msg) {
    print json_encode(array(
            "error" => "1",
            "message" => $msg
            ));
}

function success($mndate1,$mxdate1) {
    print json_encode(array(
            "mindate" => $mndate1,
            "maxdate" => $mxdate1
            ));
}
?>