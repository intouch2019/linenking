<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once "lib/core/Constants.php";


class cls_po_publish extends cls_renderer {
    function __construct($params=null) {
        $currUser = getCurrUser();
	if ($currUser->usertype != UserType::Admin && $currUser->usertype != UserType::CKAdmin) {
		print "Unauthorized access."; return;
	}
	if (isset($params['poid'])) { $poid = $params['poid']; }
	else { print "Nothing to publish"; return; }
	$poid = trim($poid);
	$db = new DBConn();
	$db->execUpdate("update it_purchaseorder set po_status=1, submittedtime=now() where id=$poid");
	header("Location: ".DEF_SITEURL."po/home");
	exit;
    }

    function extraHeaders() {
    } //end of extra headers

    public function pageContent() {
    }
}
?>
