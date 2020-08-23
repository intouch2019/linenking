<?php
require_once("../it_config.php");
require_once("session_check.php");
require_once("lib/users/clsUsers.php");
require_once("lib/core/Constants.php");
require_once("lib/core/clsProperties.php");
require_once("lib/logger/clsLogger.php");

$logger = new clsLogger();
extract($_POST);
$errors=array();
try {
	$username=trim($username);
	$_SESSION['form_username']=$username;
	$password=urldecode($password);
	if (!$username) { $errors['username']='Enter your Username'; }
	if (!$password) { $errors['password']='Enter your Password'; }
	if (count($errors) == 0) {
		$clsUsers = new clsUsers();
		$userInfo = $clsUsers->isAuthentic($username, $password);
		if (!$userInfo) {
			$errors['password']='Incorrect Username or Password';
			$logger->logInfo("Login Failed:$username");
		} else if ($userInfo->inactive &&  $userInfo->usertype != 4) {
//			header("Location: ".DEF_SITEURL."user/disabled");
//			exit;
                        $_SESSION['currUser'] = $userInfo;
			header("Location: ".DEF_SITEURL."store/disabled");                                               
			exit;
		}else if($userInfo->is_closed){
                        header("Location: ".DEF_SITEURL."loginsdisabled");
			exit;
                } else {
			$_SESSION['currUser'] = $userInfo;
			$logger->logInfo("Login Success:$username");
		}
	}
} catch (Exception $xcp) {
	$logger->logError("Failed to login $username:".$xcp->getMessage());
	$errors['status']="There was a problem processing your request. Please try again later";
}
if (count($errors) > 0) {
	$_SESSION['form_errors'] = $errors;
	$redirect = "";
} else {
	unset($_SESSION['form_errors']);
	if ($userInfo->usertype == 0 || $userInfo->usertype==1) { // admin
                $redirect = "barcode/newbatch";
	} else if ($userInfo->usertype == 4) { // store
                //$redirect = "store/featured";
                $redirect = "barcode/search/revised";
                //$redirect = "store/viewcart";
        } else if ($userInfo->usertype == 3) { //manager .. general admin
                $redirect = "admin/orders/active";
        } else if ($userInfo->usertype == 2) { // dispatcher
                $redirect = "dispatch/orders/active";
        }else if ($userInfo->usertype == 5) { // picker
                $redirect = "lk/sbinvoices";
        } else if ($userInfo->usertype == 6) { // accounts manager
                $redirect = "report/accounts";
        }  else if ($userInfo->usertype == 7) {  // BHM accounts manager
        $redirect = "report/ssales";
        } else {
		$redirect = "home";
	}
}
session_write_close();
header("Location: ".DEF_SITEURL.$redirect);
exit;

?>
