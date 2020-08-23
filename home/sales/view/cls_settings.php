<?php
require_once "view/cls_renderer.php";
require_once "lib/core/SimpleDateTime.class.php";
require_once "lib/codes/clsCodes.php";
require_once "lib/codes/CodeProps.php";

class cls_settings extends cls_renderer {

	var $params;
	var $currStore, $storeid;
	var $minDate, $maxDate, $rangeMin, $rangeMax;

	function __construct($params=null) {
		$this->currStore = getCurrStore();
		$this->params = $params;
	}

	function extraHeaders() {
if (!$this->currStore) { return; }
?>

<style type="text/css" title="currentStyle">
	@import "../js/datatables/media/css/demo_table.css";
</style>
<link rel="stylesheet" href="../jqueryui/themes/base/jquery.ui.all.css">
<script src="../jqueryui/js/jquery-1.4.2.min.js"></script>
<script src="../jqueryui/ui/jquery-ui-1.8.6.custom.js"></script>
<script src="../jqueryui/ui/jquery.ui.core.js"></script>
<script src="../jqueryui/ui/jquery.ui.widget.js"></script>
<script src="../jqueryui/ui/jquery.ui.mouse.js"></script>
<script src="../js/common.js"></script>
<script src="../js/jsDate.js"></script>
<link rel="stylesheet" type="text/css" media="all" href="../util/niceforms/niceforms-default.css" />
<style>
	#demo-frame > div.demo { padding: 10px !important; };
</style>
<script type="text/javascript">
var gCurrTabIndex=0;

$(function() {
	$( "#tabs" ).tabs();
	$("#tabs").bind("tabsselect", function(event, ui) {
		gCurrTabIndex = ui.index;
	});
});

function saveCodeProps(theForm) {
var params = "<?php echo CodeProps::AmountSlabs; ?>" + "=" + theForm.<?php echo CodeProps::AmountSlabs; ?>.value;
params += "&" + "<?php echo CodeProps::HourOfDaySlabs; ?>" + "=" + theForm.<?php echo CodeProps::HourOfDaySlabs; ?>.value;
$("#form_success").hide();
$("#form_error").hide();
$.ajax({
	type: "POST",
	url: "ajax/postSaveCodeProps.php",
	data: params,
	success: function(data) {
		var arr = data.split("||",2);
		if (arr[0] == "success") {
			$("#form_success").show();
			$("#form_success").html(arr[1]);
		} else {
			$("#form_error").show();
			$("#form_error").html(arr[1]);
		}
	}
});
}

</script>
<?php
	}

	public function pageContent() {
if (!$this->currStore) {
?>
</div>
<div id="colTwo">
<h2>Session Expired</h2>
Your session has expired. Click <a href="/store">here</a> to login.
</div>
<?php
return;
}
$this->storeid = $this->currStore->id;
$menuitem="settings";
include "storemenu.php";
?>
</div> <!-- div=colOne -->
<div id="colTwo">
		<h2>Settings</h2>
<div id="border" style="clear:both;padding:10px;border:3px solid #a0a0a0;;width:100%;margin-bottom:10px;">
<div id="tabs">
	<ul>
		<li><a href="#tabs-1">Analytics</a></li>
		<li><a href="#tabs-2">Alerts</a></li>
		<li><a href="#tabs-3">Change Password</a></li>
	</ul>
<div id="tabs-1">
<?php
		$storeid = $this->storeid;
		$clsCodes = new clsCodes();
		$propValues = $clsCodes->getCodeProps($storeid);
		$amountSlabs = isset($propValues[CodeProps::AmountSlabs]) ? $propValues[CodeProps::AmountSlabs] : "";
		$hourofdaySlabs = isset($propValues[CodeProps::HourOfDaySlabs]) ? $propValues[CodeProps::HourOfDaySlabs] : "";
?>
<form name="saveCodePropsForm" action="javascript:{};" onsubmit="saveCodeProps(this);return false;">
<fieldset>
<dl>
<dt><label for="<?php echo CodeProps::AmountSlabs; ?>">Amount Slabs:</label></dt>
<dd><input type="text" name="<?php echo CodeProps::AmountSlabs; ?>" id="<?php echo CodeProps::AmountSlabs; ?>" size="32" maxlength="128" value="<?php echo $amountSlabs; ?>"/> (e.g. 100,500,1000)</dd>
</dl>
<dl>
<dt><label for="<?php echo CodeProps::HourOfDaySlabs; ?>">Hour of Day Slabs:</label></dt>
<dd><input type="text" name="<?php echo CodeProps::HourOfDaySlabs; ?>" id="<?php echo CodeProps::HourOfDaySlabs; ?>" size="32" maxlength="128" value="<?php echo $hourofdaySlabs; ?>"/> (e.g. enter 10,14,18,20 for 10am,2pm,6pm,8pm)</dd>
</dl>
<dl>
<div class="error" id="form_error" style="display:none;"></div>
<div class="success" id="form_success" style="display:none;"></div>
</dl>
</fieldset>
<fieldset class="action">
<input type="hidden" name="storeid" value="<?php echo $this->storeid; ?>" />
<input type="submit" name="submit" id="submit" value="Submit" />
</fieldset>
</form>
</div>
<div id="tabs-2">
COMING SOON...
</div>
<div id="tabs-3">
COMING SOON...
</div>
</div> <!-- div id=tabs -->
</div> <!-- border -->
</div> <!-- colTwo -->

<?php
	} // pageContent()

}

?>
