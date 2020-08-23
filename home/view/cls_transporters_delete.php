<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once "lib/core/Constants.php";


class cls_transporters_delete extends cls_renderer {
    function __construct($params=null) {
        // set page permissions
        parent::__construct(array(UserType::Admin, UserType::CKAdmin));
        
        /*$currUser = getCurrUser();
	if ($currUser->usertype != UserType::Admin && $currUser->usertype != UserType::CKAdmin) {
		print "Unauthorized access."; return;
	}*/
	if (isset($params['transporterid'])) { $id = $params['transporterid']; }
	else { print "Nothing to delete"; return; }
	$id = trim($id);
	$db = new DBConn();
	$db->execUpdate("update it_transporters set inactive=1 where id=$id");
	header("Location: ".DEF_SITEURL."transporters");
	exit;
    }

    function extraHeaders() {
    } //end of extra headers

    public function pageContent() {
    }
}
?>
