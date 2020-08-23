<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once "lib/core/Constants.php";


class cls_admin_stores_enable extends cls_renderer {
    function __construct($params=null) {
	//parent::__construct(array(UserType::Admin, UserType::CKAdmin));
	if (isset($params['id'])) { $id = $params['id']; }
	else { print "Nothing to enable"; return; }
	$id = trim($id);
	$db = new DBConn();
	$db->execUpdate("update it_codes set inactive=0 where id=$id");
	header("Location: ".DEF_SITEURL."admin/stores");
	exit;
    }

    function extraHeaders() {
    } //end of extra headers

    public function pageContent() {
    }
}
