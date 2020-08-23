<?php
require_once "view/cls_renderer.php";

class cls_inward_workorder_edit extends cls_renderer {

	function __construct($params=null) {
	}

	function extraHeaders() {
	}

	public function pageContent() {
		$currUser = getCurrUser();
		$menuitem = "editwo";
		include "sidemenu.".$currUser->usertype.".php";
?>
<div class="grid_10">
<?php
		print "You have 5 pending tasks";
?>
</div>
<?php
	}
}
?>
