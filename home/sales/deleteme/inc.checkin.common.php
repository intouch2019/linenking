<?php
$currStore = getCurrStore();
if (!$currStore) {
	// donot show checkin form if not logged in
} else {
$msgClass="success";
$statusMsg="";
if (isset($_SESSION['form_response'])) {
list($isError, $statusMsg) = explode(",", $_SESSION['form_response'], 2);
if ($isError) { $msgClass="error"; }
}
//print $statusMsg;

?>
<div id="content">
	<span class="formtitle">Customer Checkin</span>
	<ul class="pageitem">
<form method="post" name="checkinform" action="postCheckin.php">
<li class="menu" style="height:80px;"><span class="fieldlabel">InTouch Number (or Phone Number):</span><input post="1" class="eform" name="intouchno" type="text" value="<?php echo $_SESSION['form_intouchno'] ?>" /></li>
<li class="menu" style="height:80px;"><span class="fieldlabel">Server Name:</span><input post="1" class="eform" name="server_name" type="text" value="<?php echo $_SESSION['form_server_name'] ?>" /></li>
<li class="textbox"><span class="<?php echo $msgClass ?>" id="cif_status"><?php echo $statusMsg ?></span></li>
<?php
print $form_buttons;
?>
</form>
	</ul>
</div> <!-- end div=content -->
<?php
}
?>
