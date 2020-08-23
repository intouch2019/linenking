<?php
if ($gCurrStore) {
	// show login form only if not currently logged in
} else {
$form_errors = $_SESSION['form_errors'];
if ($form_errors && count($form_errors) > 0) {
$form_errors = implode("<br />", $form_errors);
}
?>
<div id="content">
	<span class="formtitle">Store Login</span>
	<ul class="pageitem">
<form method="post" name="storeloginform" action="postLogin.php">
<li class="menu" style="height:80px;"><span class="fieldlabel">Your Store Code:</span><input post="1" class="eform" name="storecode" type="text" value="<?php echo $_SESSION['form_storecode'] ?>" onfocus="if (this.value == '') {this.value = ''}" onblur="if (this.value == '') {this.value=''}"/></li>
<li class="menu" style="height:80px;"><span class="fieldlabel">Password:</span><input post="1" class="eform" name="password" type="password" value="" onfocus="if (this.value == '') {this.value = ''}" onblur="if (this.value == '') {this.value=''}"/></li>
<li class="textbox"><span class="error" id="slf_status"><?php echo $form_errors ?></span></li>
<li class="form">
<input name="Submit input" type="submit" value="Login" />
</li>
<input type="hidden" name="formId" value="slf" post="1"/><span class="error" id="slf_error_formId"></span>
</form>
	</ul>
</div> <!-- end div=content -->
<?php
}
?>
