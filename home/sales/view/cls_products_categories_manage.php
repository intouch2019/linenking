<?php
require_once "view/cls_renderer.php";
require_once "lib/items/clsItems.php";

class cls_products_categories_manage extends cls_renderer {

	var $params;
	var $currStore;

	function __construct($params=null) {
		$this->currStore = getCurrStore();
		$this->params = $params;
		if (!$this->currStore) { return; }
		$storeid = $this->currStore->id;
	}

	function extraHeaders() {
if (!$this->currStore) { return; }
?>

<script src="../jqueryui/js/jquery-1.4.2.min.js"></script>
<script src="../jqueryui/ui/jquery.ui.core.js"></script>
<script src="../js/common.js"></script>
<script type="text/javascript">

function searchItems() {
searchtext=escape(document.formAssignItems.searchbox.value);
	$.ajax({
		url: "/savesession.php?name=searchtext&value="+searchtext,
		success: function(data) {
			window.location.reload();
		}
	});
}

function resetItems() {
	$.ajax({
		url: "/savesession.php?name=searchtext&value=0",
		success: function(data) {
			window.location.reload();
		}
	});
}

function changeScenario() {
	var dropdown = document.scenarioSelectForm.scenarioSelect;
	var scenarioid = dropdown.options[dropdown.selectedIndex].value;
	$.ajax({
		url: "/savesession.php?name=scenarioid&value="+scenarioid,
		success: function(data) {
			window.location.reload();
		}
	});
}

function cancelAddScenario() {
	$.ajax({
		url: "/savesession.php?name=scenarioid&value=0",
		success: function(data) {
			window.location.reload();
		}
	});
}

$(function() {
});
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
		$formResult = $this->getFormResult();
$menuitem="pctgmanage";
include "storemenu.php";
$clsItems = new clsItems();
$scenarios = $clsItems->getScenarios($this->currStore->id);
$curr_scenarioid = 0;
if (isset($_SESSION['scenarioid'])) { $curr_scenarioid = intval($_SESSION['scenarioid']); }
?>
</div> <!-- div=colOne -->
<div id="colTwo">
	<h2>Product Segmentation</h2>
	<div class="box">
<?php if ($curr_scenarioid == -1 || count($scenarios) == 0) { ?>
		<h3>New Segmentation</h3>
		<h4>Create new product segmentation. Use this to perform scenario analysis.</h4>
	
		<form method="post" action="postAddScenario.php">
		<p class="bottom">
		<label for="tile">Title: </label>
		<input type="text" name="title" size=50 />
		<input type="Submit" value="Add" />
<?php if (count($scenarios) > 0) { ?>
		<input type="button" value="Cancel" onclick="cancelAddScenario();" />
<?php } ?>
		<input type="hidden" name="form_id" value="1" />
<?php if ($formResult->form_id == "1") { ?>
<span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
<?php } ?>
		</p>
		</form>
<?php } else { ?>
<form name="scenarioSelectForm">
<p class="bottom">
<select name="scenarioSelect" onchange="changeScenario();">
<option value="0">Select Product Segmentation</option>
<?php foreach ($scenarios as $scenario) {
$selected="";
if ($curr_scenarioid == $scenario->id) $selected = "selected";
?>
<option value="<?php echo $scenario->id; ?>" <?php echo $selected; ?>><?php echo $scenario->title; ?></option>
<?php } ?>
<option value=-1>>>> Add New <<<</option>
</select>
</p>
</form>
<?php } ?>
	</div>
<?php if ($curr_scenarioid > 0) { ?>
	<div id="assignctgs" class="box">
		<h3>Add New Category</h3>
		<h4>Use this to add new product categories.</h4>
	
		<form method="post" action="postAddCtg.php">
		<p class="bottom">
		<label for="ctgname">Category Name: </label>
		<input type="text" name="ctgname" size=30 />
		<input type="Submit" value="Add Category" />
		<input type="hidden" name="scenarioid" value="<?php echo $curr_scenarioid; ?>" />
		<input type="hidden" name="form_id" value="1" />
<?php if ($formResult->form_id == "1") { ?>
<span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
<?php } ?>
		</p>
		</form>
	</div>
	<div class="box">
		<h3>Assign Products to Categories</h3>
		<h4>In order to generate meaningful category based statistics, you need to ensure that all your products have been assigned to categories. Select the unassigned products, if any, from the list below and assign them to their respective categories.</h4>

		<form name="formAssignItems" method="post" action="postAssignItems.php">
		<div class="bottom" style="height:420px;">
<div style="float:left;width:300px;height:400px;overflow:auto;border:1px solid #256;padding:5px;">
<h4 style="font-weight:bold;">1. Select products from this list that belong to the same category</h4>
<input name="searchbox" type="text" size="18" />
<input type="button" value="Search" onclick="searchItems();" />
<input type="button" value="Reset" onclick="resetItems();" /><br />
<?php
$searchtext=false;
if (isset($_SESSION['searchtext'])) { 
$searchtext=$_SESSION['searchtext'];
print "Search term:$searchtext<br /";
}
print "<br />";
$clsItems = new clsItems();
$items = $clsItems->getUnassignedItems($curr_scenarioid, $this->currStore->id, $searchtext);
foreach ($items as $item) {
$style="";
if ($item->font) {
$style='style="font-family:'.$item->font.';font-size:2em;"';
}
?>
<input type="checkbox" name="selectItems[]" value="<?php echo $item->id; ?>"><span <?php echo $style; ?>><?php echo $item->itemname; ?></span></input><br />
<?php
}
?>
</div>
<div style="float:left;width:200px;border:1px solid #256;padding:5px;margin-left:10px;">
<h4 style="font-weight:bold;">2. Select the category you wish to add the products to</h4>
<select name="ctgSelect">
<option value="0">Select Category</option>
<?php
$clsItems = new clsItems();
$ctgs = $clsItems->getAllCategories($curr_scenarioid);
foreach ($ctgs as $ctg) { 
?>
<option value="<?php echo $ctg->id; ?>"><?php echo $ctg->name; ?></option>
<?php
}
?>
</select>
</div>
<div style="float:left;width:200px;border:1px solid #256;padding:5px;margin:10px 0 0 10px;">
<h4 style="font-weight:bold;">3. Press the "Assign" button helow to make it happen</h4>
<input type="submit" value="Assign" />
<input type="hidden" name="form_id" value="2" />
<input type="hidden" name="scenarioid" value="<?php echo $curr_scenarioid; ?>" />
<?php if ($formResult->form_id == "2") { ?>
<span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
<?php } ?>
</div>
		</div> <!-- class=bottom -->
	</div> <!-- class=box -->
		</form>
<?php } ?>
</div> <!-- colTwo -->

<script type="text/javascript">
</script>

<?php
	} // pageContent()

}

?>
