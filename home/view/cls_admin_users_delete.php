<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once "lib/core/Constants.php";


class cls_admin_users_delete extends cls_renderer {
    function __construct($params=null) {
        $currUser = getCurrUser();
//	if ($currUser->usertype != UserType::Admin && $currUser->usertype != UserType::CKAdmin) {
//		print "Unauthorized access."; return;
//	}
	if (isset($params['userid'])) { $id = $params['userid']; }
	else { print "Nothing to delete"; return; }
	$id = trim($id);
	$db = new DBConn();
//	$db->execUpdate("update it_users set inactive=1 where id=$id");
        $db->execUpdate("update it_codes set inactive=1 where id=$id");
	header("Location: ".DEF_SITEURL."admin/users");
	exit;
    }

    function extraHeaders() {
    } //end of extra headers

    public function pageContent() {
    }
}
?>
