<?php
require_once "view/cls_renderer.php";
require_once "lib/items/clsItems.php";
require_once "lib/core/Constants.php";
require_once "lib/db/DBConn.php";

class cls_scheme_schemes extends cls_renderer {
    var $params;
    var $currStore;
    var $storeid;
    function __construct($params=null) {
	$this->currStore = getCurrUser();
	$this->params = $params;
	if (!$this->currStore) { return; }
	$this->storeid = $this->currStore->id;
    }

    function extraHeaders() {
	if (!$this->currStore) {
	    return;
	}
?>
<script type="text/javascript" src="js/daterangepicker.jQuery.js"></script>
<link rel="stylesheet" href="css/ui.daterangepicker.css" type="text/css" />
<link rel="stylesheet" href="css/redmond/jquery-ui-1.7.1.custom.css" type="text/css" title="ui-theme" />
<script type="text/javascript">
    $(function(){
	var isOpen=false;
	$('#dateselect').daterangepicker({
	 	dateFormat: 'dd-mm-yy',
		arrows:false,
		closeOnSelect:true,
		onOpen: function() { isOpen=true; },
		onClose: function() { isOpen=false; },
		onChange: function() {
		if (isOpen) { return; }
		var dtrange = $("#dateselect").val();
		$.ajax({
			url: "savesession.php?name=scheme_dtrange&value="+dtrange,
		});
		}
	});
    });

	function reload() {
		var dtrange = $("#dateselect").val();
		$.ajax({
			url: "savesession.php?name=scheme_dtrange&value="+dtrange,
			success: function(data) {
				window.location.reload();
			}
		});
	}
        
    function ruletypeSelect(dropdown)
    {
	var idx = dropdown.selectedIndex;
	var value = dropdown.options[idx].value;
	$("#rulevalue1").hide();
	$("#rulevalue2").hide();
	$("#rulevalue3").hide();
        $("#rulevalue12").hide();
	if (value != null) {
	    $("#rulevalue"+value).show();
	}
    }

</script>
<?php
    } // extraHeaders

    public function pageContent() {
	$db = new DBConn();
	$formResult = $this->getFormResult();
        $menuitem = "schemeList";
	include "sidemenu.".$this->currStore->usertype.".php";
	?>
<div class="grid_10">

    <div class="grid_3">&nbsp;</div>
    <div class="grid_6">
	<fieldset>
	    <legend>Create New Scheme</legend>
    <form id="ruleform" name="ruleform" method="post" action="formpost/postAddRule.php">
	<div class="clsDiv">Scheme Name (e.g. 20% off Diwali Offer)</div>
	<div class="clsText"><input id="name" name="name" size="40" value="<?php echo $this->getFieldValue('name'); ?>"/></div>
<br />
	<div class="clsDiv">Date Range</div>
	<div class="clsText">
<input type="text" id="dateselect" name="dateselect" value="<?php echo $this->getFieldValue('dateselect'); ?>" /> (Click in the box to see date options)
	</div>
<br />
	<div class="clsDiv">Rule Type</div>
	<div class="clsText">
	    <select name="ruletype" onchange="ruletypeSelect(this);">
		<option value="">Please Select</option>
			<?php
			$allRuleTypes = RuleType::getAll();
			$display = "block";
			foreach ($allRuleTypes as $ruletype => $typename) {
			    if ($ruletype == $this->getFieldValue('ruletype')) {
				$selected = "selected";
			    }
			    else { $selected = ""; }
			?>
		<option value="<?php echo $ruletype; ?>" <?php echo $selected; ?>><?php echo $typename; ?></option>
		  <?php } ?>
	    </select>
	</div>
<?php if ($this->getFieldValue('ruletype') == "1") $display = "block"; else $display = "none"; ?>
<div id="rulevalue1" style="display:<?php echo $display; ?>;margin-top:10px;">
	<div class="clsDiv">Rule Value</div>
	<div class="clsText"><input id="discount1" name="discount1" style="width:24px;" value="<?php echo $this->getFieldValue('discount1'); ?>"/> % off on select Items</div>
</div>
<?php if ($this->getFieldValue('ruletype') == "2") $display = "block"; else $display = "none"; ?>
<div id="rulevalue2" style="display:<?php echo $display; ?>;margin-top:10px;">
	<div class="clsDiv">Rule Value</div>
	<div class="clsText">Buy <input id="qtyM2" name="qtyM2" style="width:24px;" value="<?php echo $this->getFieldValue('qtyM2'); ?>"/> and get
	<input id="qtyN2" name="qtyN2" style="width:24px;" value="<?php echo $this->getFieldValue('qtyN2'); ?>"/> FREE of lower value
	</div>
</div>
<?php if ($this->getFieldValue('ruletype') == "3") $display = "block"; else $display = "none"; ?>
<div id="rulevalue3" style="display:<?php echo $display; ?>;margin-top:10px;">
	<div class="clsDiv">Rule Value</div>
	<div class="clsText"><input id="discount3" name="discount3" style="width:24px;" value="<?php echo $this->getFieldValue('discount3'); ?>"/> % off on select<br />Categories <input id="categories3" name="categories3" style="width:250px;" value="<?php echo $this->getFieldValue('categories3'); ?>" />(comma separated category ID's)</div>
</div>
        <?php if ($this->getFieldValue('ruletype') == "12") $display = "block"; else $display = "none"; ?>
<div id="rulevalue4" style="display:<?php echo $display; ?>;margin-top:10px;">
	<div class="clsDiv">Rule Value</div>
	<div class="clsText">Buy <input id="qtyM2r4" name="qtyM2r4" style="width:24px;" value="<?php echo $this->getFieldValue('qtyM2'); ?>"/> and get
	<input id="qtyN2r4" name="qtyN2r4" style="width:24px;" value="<?php echo $this->getFieldValue('qtyN2'); ?>"/> FREE of lower value
	<br>
        <div class="clsText"><input id="discount1" name="discount4" style="width:24px;" value="<?php echo $this->getFieldValue('discount1'); ?>"/> % off on select Items</div>
        </div>
</div>

<br />
	<div class="clsDiv">Exception List</div>
	<div class="clsText">
	    <select name="exception_id">
		<option value="">Please Select</option>
			<?php
			$allxcp = $db->fetchObjectArray("select * from it_rule_exceptions order by id");
			$display = "block";
			foreach ($allxcp as $xcp) {
			    if ($exception_id == $xcp->ID) {
				$selected = "selected";
			    }
			    else { $selected = ""; }
			?>
		<option value="<?php echo $xcp->ID; ?>" <?php echo $selected; ?>><?php echo $xcp->NAME; ?></option>
		  <?php } ?>
	    </select>
	</div>
<br />
	<input type="submit" value="Create Scheme"/>
	<input type="hidden" name="form_id" value="1"/>
	<span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
    </form>
	</fieldset>
    </div>
</div>
<div class="grid_10">
<div class="grid_2">&nbsp;</div>
<div class="grid_8">
<table>
<tr>
<th style="width:10%;">ID</th>
<th style="width:70%;">Name</th>
<th style="width:20%;">&nbsp;</th>
</tr>
<?php
$count=1;
$rules = $db->fetchObjectArray("select * from it_rules order by id");
foreach ($rules as $rule) { ?>
<tr>
<td><?php echo $rule->ID; ?></td>
<td><?php echo $rule->RULE_TEXT; ?></td>
<td><a href="<?php echo DEF_SITEURL.'scheme/stores/id='.$rule->ID.'/'; ?>">Scheme Stores</a></td>
</tr>
<? }
if (count($rules) == 0) { ?>
<tr><td colspan="3" style="text-align:center;">No data</td></tr>
<?php }
?>
</table>
</div>
</div>
    <?php
    } //pageContent
}//class