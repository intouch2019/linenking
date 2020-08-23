<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/serverChanges/clsServerChanges.php";

extract($_POST);
$errors=array();
$success=array();

try {
    global $success, $errors, $_SESSION;
    $_SESSION['form_post']=$_POST;
    extract($_POST);
    $db = new DBConn();
    $serverCh = new clsServerChanges();
    $_SESSION['form_name']=$name;
    $_SESSION['form_dateselect']=$dateselect;

    if (!$name) { $errors['name']='Please enter a name for the Scheme'; }
    $name=$db->safe(trim($name));
    $exists = $db->fetchObject("select * from it_rules where rule_text=$name");
    if ($exists) { $errors['name'] = 'Rule by this name already exists'; }
    if (!$dateselect) { $errors['dateselect']='Please enter a Date Range for the Scheme'; }
    list($stdt,$endt) = explode(" - ", $dateselect);
    if (!$stdt || !$endt || $stdt > $endt) {
        $errors['dateselect'] = 'Incorrect Date Range';
    }
    $rvalue = false;
    if (isset($ruletype)) {
        $_SESSION['form_ruletype']=$ruletype;
        switch($ruletype) {
	   case "1": if (!isset($discount1)) { $errors['discount1'] = 'Enter a value for the discount'; }
		     else $rvalue=$discount1;
		     break;
	   case "2": if (!isset($qtyM2) || !isset($qtyN2)) { $errors['qtyM2'] = 'Enter both the quantities'; }
		     else $rvalue="$qtyM2,$qtyN2";
		     break;
	   case "3": if (!isset($discount3) || !isset($categories3)) { $errors['discount3'] = "Enter the discount and the category id's"; }
		     else $rvalue="$discount3,$categories3";
		     break;
           case "12": if (!isset($discount4) || !isset($qtyM2r4) || !isset($qtyN2r4)) { $errors['discount4'] = "Enter the discount and both the quantities"; }
		     else $rvalue="100/$discount4";
		     break;          
	   default:  break;
        }
    }
    if (!$rvalue) { $errors['ruletype'] = 'Rule Values not set'; }

    if (!$exception_id) { $errors['exception_id'] = 'You have to select an Exception list'; }
    else $_SESSION['form_exception_id'] = $exception_id;
    
    if (count($errors) == 0) {
	$rvalue = $db->safe($rvalue);
	$query = "insert into it_rules set RTYPE=$ruletype, RULE_TEXT=$name, ST_DTTM=str_to_date('$stdt', '%d-%m-%Y'), ED_DTTM=str_to_date('$endt', '%d-%m-%Y'), RULE_VALUES=$rvalue, EXCEPTION_ID=$exception_id";
	$db->execInsert($query);
	$success = "The Scheme $name has been created.";
	unset($_SESSION['form_post']);
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

header("Location: ".DEF_SITEURL."scheme/schemes");
exit;
