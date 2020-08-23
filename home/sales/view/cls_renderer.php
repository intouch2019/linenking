<?php

abstract class cls_renderer {

	function baseUrl() {
		return DEF_BASEURL;
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

	function errorResult($msg) {
		$result = array();
		$result['status']=$msg;
		$result['showhide']="block";
		$result['cssClass']="error";
		return (object)$result;
	}

}

?>
