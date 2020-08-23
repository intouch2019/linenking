<?php
require_once "view/cls_renderer.php";

class cls_home_login extends cls_renderer {

	function __construct($params=null) {
		$this->currUser = getCurrUser();
		if ($this->currUser) {
			header("Location: home");
			exit;
		}
	}

	public function pageContent() {
		$result = $this->getFormResult();
		?>
		<div class="grid_3">
			<div class="box">
				<form action="postLogin.php" method="post">
					<fieldset class="login">
						<legend>Login</legend>
						<p>
							<label>Username: </label>

							<input type="text" name="username" />
						</p>
						<p>
							<label>Password: </label>
							<input type="password" name="password" />
						</p>
						<?php if ($result) { ?>
						<p>
							<div class="<?php echo $result->cssClass; ?>"><?php echo $result->status; ?></div>
						</p>
						<?php } ?>
						<input class="login button" type="submit" value="Login" />
					</fieldset>

				</form>
			</div>
		</div>
		<div class="grid_9">
			<h4>We recommend one of the following browsers:</h4>
			<a href="http://www.mozilla.org/en-US/products/download.html"><img width="32px" src="images/firefox.png" /> Download Firefox</a>
			<a href="http://www.google.com/chrome"><img width="32px" src="images/chrome.png" /> Download Chrome</a>
		</div>

		<?php
	}

}
?>
