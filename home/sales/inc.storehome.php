<?php
$form_errors = null;
if (isset($_SESSION['form_errors'])) { $form_errors = $_SESSION['form_errors']; }
if ($form_errors && count($form_errors) > 0) {
$form_errors = implode("<br />", $form_errors);
$disp="block";
} else {
$disp="none";
}
$form_storecode = ""; if (isset($_SESSION['form_storecode'])) $form_storecode = $_SESSION['form_storecode'];
?>
	<div class="box">
		<h3>Store Login</h3>
		<ul class="bottom">
<form method="post" name="storeloginform" action="postLogin.php">
		<div style="height:30px;"><div style="float:left;width:70px;">Username: </div><input type="text" size="15" style="float:left;" name="storecode" value="<?php echo $form_storecode; ?>" /></div>
		<div style="height:30px;"><div style="float:left;width:70px;">Password: </div><input type="password" size="15" style="float:left;" name="password" /></div>
<span class="error" id="slf_status" style="display:<?php echo $disp; ?>;"><?php echo $form_errors ?></span>
		<div style="height:30px;"><div style="float:left;width:70px;">&nbsp;</div><input type="submit" value="Login" style="float:left;" name="Submit input" /></div>
</form>
		</ul>
	</div>
</div>
<div id="colTwo">
	<div class="box">
		<h3>Intouch Store Management Portal</h3>
		<img src="images/shopping-cart.jpg" />
		<p class="bottom">Find out what your customers are putting in that shopping cart<br />
		* Top/Least Selling Items<br />
		* Top/Least Selling Categories<br />
		* Daily Statistics - View Individual Receipts<br />
		* View sales by the hour<br />
		* Setup suppliers, alerts, etc<br />
		</p>
	</div>
</div>
