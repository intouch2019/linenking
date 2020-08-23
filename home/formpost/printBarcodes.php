<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once 'lib/users/clsUsers.php';

extract($_POST);
$user = getCurrUser();
$db = new DBConn();
$userpage = new clsUsers();
$pagecode = $db->safe($_SESSION['pagecode']);
$page = $db->fetchObject("select * from it_pages where pagecode = $pagecode");
if($page){
    $allowed = $userpage->isAuthorized($user->id, $page->pagecode);
    if (!$allowed) { header("Location: ".DEF_SITEURL."unauthorized"); return; }
}else{ header("Location:".DEF_SITEURL."nopagefound"); return; }


$errors=array();
$success=array();
try {
	$_SESSION['form_post'] = $_POST;
	$print_ids = array(); $total = 0;
	foreach ($_POST as $name=>$value) {
		if (!startsWith($name,"qty_")) { continue; }
		if (!$value || trim($value) == "") { continue; }
		if (!isNumber($value)) { $errors[$name] = "Incorrect Number '$value'"; continue; }
		$item_id = substr($name,4);
		$print_ids[$item_id] = intval($value);
		$total += intval($value);
	}
	if (count($errors) == 0 && count($print_ids) > 0) {
		$db = new DBConn();
		$length = 50*$total;
		$html = "";
		$html2pdf = new HTML2PDF('P', array(45,$length), 'en', true, 'UTF-8', array(0,0,0,0));
		foreach ($print_ids as $item_id => $num_copies) {
			$item = $db->fetchObject("select * from it_items where id=$item_id");
			for ($i=0; $i<$num_copies; $i++) {
				$html .= "<p>".print_r($item,true)."</p>";
			}
		}
		$html2pdf->writeHTML($html);
		$html2pdf->Output("abc.pdf", "F");
		$db->closeConnection();
	}
} catch (Exception $xcp) {
	$clsLogger = new clsLogger();
	$clsLogger->logError("Failed to add $atype:".$xcp->getMessage());
	$errors['status']="There was a problem processing your request. Please try again later";
}
if (count($errors) > 0) {
	$_SESSION['form_errors'] = $errors;
} else {
	unset($_SESSION['form_errors']);
        $_SESSION['form_success'] = $success;
}
session_write_close();
header("Location: ".DEF_SITEURL."barcode/search");
exit;

?>
