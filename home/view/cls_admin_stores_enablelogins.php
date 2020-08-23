<?php
require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/clsProperties.php";
require_once "session_check.php";

class cls_admin_stores_enablelogins extends cls_renderer {
	function __construct($params=null) {
		//parent::__construct(array(UserType::Admin, UserType::CKAdmin));
		$dbProperties = new dbProperties();
		$dbProperties->setBoolean(Properties::DisableUserLogins, false);
		header("Location: ".DEF_SITEURL."admin/stores");
	}

	function extraHeaders() {
	}

	public function pageContent() {
	}
}
