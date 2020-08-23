<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once "lib/core/Constants.php";


class cls_suppliers_delete extends cls_renderer {
    function __construct($params=null) {
        // set page permissions
        parent::__construct(array(UserType::Admin, UserType::CKAdmin));
        
        /*$currUser = getCurrUser();
	if ($currUser->usertype != UserType::Admin && $currUser->usertype != UserType::CKAdmin) {
		print "Unauthorized access."; return;
	}*/
	if (isset($params['supplierid'])) { $id = $params['supplierid']; }
	else { print "Nothing to delete"; return; }
	$id = trim($id);
	$db = new DBConn();
	$db->execUpdate("update it_suppliers set inactive=1 where id=$id");
	header("Location: ".DEF_SITEURL."suppliers");
	exit;
    }

    function extraHeaders() {
    } //end of extra headers

    public function pageContent() {
    }
}
?>
