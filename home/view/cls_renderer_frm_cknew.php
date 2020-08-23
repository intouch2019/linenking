<?php

abstract class cls_renderer {

	var $currUser;

	function __construct($allowedUserTypes) {
		$this->currUser = getCurrUser();
		if ($allowedUserTypes && count($allowedUserTypes) > 0 && (!$this->currUser || !in_array($this->currUser->usertype, $allowedUserTypes))) {
			header("Location: ".DEF_SITEURL."unauthorized");
		}
	}

	function baseUrl() {
		return DEF_SITEURL;
	}

	function pageTitle() {
		return DEF_PAGE_TITLE;
	}

	function pageKeywords() {
		return DEF_PAGE_KEYWORDS;
	}

	function pageDescription() {
		return DEF_PAGE_DESCRIPTION;
	}

	function sideMenu() {
		return "";
	}

	function extraHeaders() {
		return "";
	}

	function getSessionVal($var,$retain_value=false) {
		$val=null;
		if (isset($_SESSION[$var])) {
			$val = $_SESSION[$var];
		}
		if (!$retain_value) {
			unset($_SESSION[$var]);
		}
		return $val;
	}

	function getFormResult() {
		$result=array();
		$form_id = $this->getSessionVal('form_id');
		$form_errors = $this->getSessionVal('form_errors');
		$form_success = $this->getSessionVal('form_success');
		$result['form_id'] = $form_id;
		if ($form_errors && count($form_errors) > 0) {
			$result['status']=implode("<br />", $form_errors);
			$result['showhide']="block";
			$result['cssClass']="error";
		} else if ($form_success != null) {
			$success_disp="block";
			$result['status']=$form_success;
			$result['showhide']="block";
			$result['cssClass']="success";
		} else {
			$result['status']="";
			$result['showhide']="none";
			$result['cssClass']="none";
		}
		return (object)$result;
	}

	function successResult($msg) {
		$result = array();
		$result['status']=$msg;
		$result['showhide']="block";
		$result['cssClass']="success";
		return (object)$result;
	}

	function getFieldValue($fieldname,$default=false) {
		$val = "";
		if (isset($_SESSION['form_post'])) {
			$post = $_SESSION['form_post'];
			if (isset($post[$fieldname])) { $val = $post[$fieldname]; }
		}
		if ($default == "0") { return $default; }
		if ($default && $val == "") { return $default; }
		return $val;
	}

	function errorResult($msg) {
		$result = array();
		$result['status']=$msg;
		$result['showhide']="block";
		$result['cssClass']="error";
		return (object)$result;
	}

}

?>
