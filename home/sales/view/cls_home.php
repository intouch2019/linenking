<?php
require_once "view/cls_renderer.php";

class cls_home extends cls_renderer {

	public function pageContent() {
		$currUser = getCurrUser();
		if ($currStore) {
			require_once "inc.main.php";
		} else {
			header("Location:".DEF_SITEURL);
			exit;
		}
	}
}

?>
