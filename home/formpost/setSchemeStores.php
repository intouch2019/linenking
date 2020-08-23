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
    extract($_POST);
    $db = new DBConn();
    $serverCh = new clsServerChanges();
    $ruleObj = $db->fetchObject("select * from it_rules where id=$scheme_id");

    if (!isset($scheme_id)) { $errors['scheme_id'] = 'Scheme ID is missing'; }
    
    if (count($errors) == 0) {
	$active = explode(",", $left_stores);
	foreach ($active as $store_id) {
	    $changeObj = null;
	    $store_id=trim($store_id);
	    if ($store_id == "") continue;
	    $obj = $db->fetchObject("select * from it_rule_stores where RULE_ID=$scheme_id and STORE_ID=$store_id");
	    if ($obj) {
		if (!$obj->IS_ACTIVE) {
		    $db->execUpdate("update it_rule_stores set IS_ACTIVE=1 where id=$obj->ID");
		    $changeObj = $ruleObj; $changeObj->IS_ACTIVE=1;
		}
	    } else {
		$db->execInsert("insert into it_rule_stores set RULE_ID=$scheme_id, STORE_ID=$store_id, IS_ACTIVE=1");
		$changeObj = $ruleObj; $changeObj->IS_ACTIVE=1;
	    }
	    if ($changeObj) {
	        //$data = "[".json_encode($changeObj)."]";
                $data = json_encode($changeObj);
		$serverCh->save(changeType::rules, $data, $store_id , $scheme_id);
	    }
	}
	$inactive = explode(",", $right_stores);
	foreach ($inactive as $store_id) {
	    $changeObj = null;
	    $store_id=trim($store_id);
	    if ($store_id == "") continue;
	    $obj = $db->fetchObject("select * from it_rule_stores where RULE_ID=$scheme_id and STORE_ID=$store_id");
	    if ($obj && $obj->IS_ACTIVE == 1) {
		$query = "update it_rule_stores set IS_ACTIVE=0 where id=$obj->ID";
		$db->execUpdate($query);
		$changeObj = $ruleObj; $changeObj->IS_ACTIVE=0;
	        //$data = "[".json_encode($changeObj)."]";
                $data = json_encode($changeObj);
		$serverCh->save(changeType::rules, $data, $store_id , $scheme_id);
	    }
	}
	$success = "Update Successful";
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

header("Location: ".DEF_SITEURL."scheme/stores/id=$scheme_id/");
exit;
