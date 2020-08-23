<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once "lib/core/Constants.php";


class cls_poline_delete extends cls_renderer {
    
    function __construct($params=null) {
        $currUser = getCurrUser();
	if ($currUser->usertype != UserType::Admin && $currUser->usertype != UserType::CKAdmin) {
		print "Unauthorized access."; return;
	}
        if (isset($params['poid'])) { $poid = $params['poid']; }
        else { print "PoId : Your request cannot be processed at this time"; return; }
	if (isset($params['lineid'])) { $polineid = $params['lineid']; }
	else { print "LineId : Your request cannot be processed at this time"; return; }
        $poid = trim($poid);
	$polineid = trim($polineid);
	$db = new DBConn();
	$db->execUpdate("delete from it_polines where id = $polineid");
        $redirect = "po/additems/id=".$poid;
	header("Location: ".DEF_SITEURL.$redirect);
	exit;
    }

    function extraHeaders() {
    } //end of extra headers

    public function pageContent() {
    }
}
?>
