<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/serverChanges/clsServerChanges.php";

extract($_POST);
$errors=array();
$success=array();

function debugLog($msg) {
	$d = date("m/d/y H:i:s.u");
	//error_log($msg.":".$d."\n", 3, "/var/www/cottonking/logs/ck-debug.log");
}

//debugLog("1");

try {
//    validatePost();
    global $success, $errors, $_SESSION;
    $_SESSION['form_post']=$_POST;
    extract($_POST);
    $db = new DBConn();
    $serverCh = new clsServerChanges();
    $_SESSION['form_name']=$name;
    $name=$db->safe(trim($name));

    if (!$name) { $errors['name']='Please enter a name for the Exception list'; }

    $path = $_FILES['xcp_file']['name'];
    $ext = pathinfo($path, PATHINFO_EXTENSION);
    if ($ext != "txt" && $ext != "TXT") {
        $errors['xcp_file']="Please enter a text file with an extension of .txt [$ext]";
    } else if ($_FILES["xcp_file"]["error"] != "0") {
        if ($_FILES["xcp_file"]["error"]==4)
            $errors['xcp_file']='Please enter a text file to upload';
        else
            $errors['xcp_file'] = "File error : ".$_FILES["xcp_file"]["error"];
    }

    if (count($errors) == 0) {
        $barcodes = file_get_contents($_FILES['xcp_file']['tmp_name']);	
	//$barcodes = $db->safe($barcodes);
        //print $barcodes;
        $arr = explode("\n", $barcodes);
        $unique_arr = array_unique($arr);
        $sarr = implode(",", $unique_arr);
        //print "<br><br>ARRAY<br><br>";
        //print_r($arr);
       // $sarr = implode(",", $arr);
        //print "<br><br>P ARRAY<br><br>";
        //print $sarr;
        $barcodes_db = $db->safe(trim($sarr));
	$db->execInsert("insert into it_rule_exceptions set name=$name, barcodes=$barcodes_db");
        $success = "The list $name has been created.";
    }

} catch (Exception $xcp) {
    $clsLogger = new clsLogger();
    $clsLogger->logError("Failed to add exception list:".$xcp->getMessage());
    $errors['status']="There was a problem processing your request. Please try again later";
}
if (count($errors) > 0) {
    $_SESSION['form_errors'] = $errors;
} else {
    $_SESSION['form_success'] = $success;
}

header("Location: ".DEF_SITEURL."scheme/exceptions");
exit;

