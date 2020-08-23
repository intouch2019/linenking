<?php
require_once "view/cls_renderer.php";

class cls_loginsdisabled extends cls_renderer {

	function __construct($params=null) {
	}

	public function pageContent() {
		session_destroy();
?>
		<div class="grid_9">
			<div class="error" style="font-size:1.5em;">Store logins are currently disabled. Please <a href="<?php echo DEF_SITEURL; ?>home/login">TRY AGAIN</a> later. Thank you for your patience.</div>
		</div>
<?php
	}

}
