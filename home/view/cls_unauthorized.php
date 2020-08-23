<?php
require_once "view/cls_renderer.php";

class cls_unauthorized extends cls_renderer {

	function __construct($params=null) {
	}

	public function pageContent() {
		session_destroy();
?>
		<div class="grid_9">
			<div class="error" style="font-size:1.5em;">You are not authorized to perform this action. Please <a href="<?php echo DEF_SITEURL; ?>home/login">Login</a> again.</div>
		</div>
<?php
	}

}
?>
