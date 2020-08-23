<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/logger/clsLogger.php";



$errors = array();
$success = "";
$commit=false;

extract($_GET);
if (!isset($filename) && trim($filename) == "") {
    $errors['file'] = "File not found";
} else {
    $commit = true;
}
if (count($errors) == 0) {
    $db = new DBConn();
    $success .= uploadstock($filename,$commit);
}

if (count($errors) > 0) {
    unset($_SESSION['form_success']);
    unset($_SESSION['fpath']);
    $_SESSION['form_errors'] = $errors;
} else {
    unset($_SESSION['form_errors']);
    unset($_SESSION['fpath']);
    $_SESSION['form_success'] = $success;
    $_SESSION['stockuploaded'] = "done";
}

session_write_close();
header("Location: " . DEF_SITEURL . "admin/stock/upload");
exit;

function uploadstock($stkfile,$commit){
    $fresp = "<br/>Stock Upload done<br/>";
    $fh = fopen($stkfile, "r");
    if (!$fh) { $fresp .= "File not found\n"; return; }

    $db=new DBConn();

    $rowno=0;$tot_qty=0;$skip_qty=0;
    if ($commit) $db->execUpdate("update it_items set curr_qty = 0, updatetime=now()");
    $error="Error:";

    while(($row=fgetcsv($fh,0,"\t")) !== FALSE) {
        $colno=0;
        $item_code=false; 
        $item_code=trim($row[0]);
        $curr_qty=intval(trim($row[1]));
                $item = $db->fetchObject("select * from it_items where barcode='$item_code'");
                if ($item) {
                        if ($commit) $db->execUpdate("update it_items set curr_qty=$curr_qty, updatetime=now() where id=$item->id");
                        $tot_qty += $curr_qty;
                        //--> code to log it_items update track
                        $clsLogger = new clsLogger();
                        $ipaddr =  $_SERVER['REMOTE_ADDR'];
                        $pg_name = __FILE__;   
                        $logquery = "update it_items set curr_qty=$curr_qty, updatetime=now() where id=$item->id";
                        $clsLogger->logInfo($logquery,false, $pg_name,$ipaddr);
                        //--> log code ends here
                } else {
                        $fresp .= "Item not found:$item_code so no stock uploaded for it. <br/>";
                        $skip_qty += $curr_qty;
                }
        $rowno++;
    }
    $fresp .= "<br/>Total Qty=$tot_qty<br/>Skipped Qty=$skip_qty";
    fclose($fh);
    $db->closeConnection();
    return $fresp;
}
?>
