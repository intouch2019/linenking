<?php
require_once("lib/codes/clsCodes.php");

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
$clsCodes = new clsCodes();
$offers = $clsCodes->getOffers($currStore->id);
if (count($offers) == 0) {
	$msgClass="error";
	$statusMsg="No active offers setup with your store right now";
}
//print $statusMsg;

?>
<div id="content">
	<span class="formtitle">Redeem Offers</span>
	<ul class="pageitem">
<form method="post" name="redeemform" action="postRedeem.php">

<li class="menu"><span class="fieldlabel">Select an Offer to Redeem:</span>
<?php foreach ($offers as $offer) { ?>
<li class="form">
<span class="choice">
<span class="name"><?php echo $offer->offer_title ?></span>
<input name="offerid" type="radio" value="<?php echo $offer->id ?>" />
</span>
</li>
<?php } ?>
</li>
<li class="menu" style="height:80px;"><span class="fieldlabel">InTouch Number (or Phone Number):</span><input post="1" class="eform" name="intouchno" type="text" value="<?php echo $_SESSION['form_intouchno'] ?>" /></li>
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
