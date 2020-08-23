<?php
require_once "view/cls_renderer.php";
require_once "lib/items/clsItems.php";
require_once "lib/supplier/clsSupplier.php";
class cls_inventory_itemmaster extends cls_renderer {
        var $params;
        var $currStore;
	var $storeid;
        function __construct($params=null) {
		$this->currStore = getCurrStore();
                $this->params = $params;
                if (!$this->currStore) { return; }
                $this->storeid = $this->currStore->id;
	}
	
	function extraHeaders() {
		if (!$this->currStore) { return; }
?>
<script language="javascript">
	function getForm(id,name,qty,relevel,supid) {
		if(!qty) qty="&nbsp;";
		document.getElementById("updateItemForm").style.display="block";
		document.getElementById("itemid").value=id;
		document.getElementById("itemname").innerHTML=name;
		document.getElementById("quantity").innerHTML=qty;
		var dropdown = document.getElementById("supplierid");
		setSelectedIndex(dropdown,supid);
		document.getElementById("reorderlevel").value=relevel;
		document.getElementById("statusMsg").innerHTML="";
		document.getElementById("statusMsg").setAttribute("class", "");
	}

	function setSelectedIndex(s, v) {
    		for ( var i = 0; i < s.options.length; i++ ) {
        		if ( s.options[i].value == v ) {
        		    s.options[i].selected = true;
        		    return;
        		}
    		}
	}
</script>
<?php
	} // extraHeaders

	public function pageContent() {
		if ($this->currStore) {
			$formResult = $this->getFormResult();
			$menuitem="itemmaster";
			include "storemenu.php";
		}
?>
</div> <!-- div=colOne -->
<div id="colTwo">
<?php
if (!$this->currStore) {
?>
<h3>Session Expired</h3>
Your session has expired. Click <a href="">here</a> to login.
<?php
} else {
?>
<h3>Item Master</h3>
<?php
$clsItems = new clsItems();
$clsSupplier = new clsSupplier();
$itemObj = $clsItems->getAllRawItems($this->storeid);
if($itemObj) {
?>
<table class="report" width="630px">
<tr>
	<th width="35%">Item Name</th>
	<th width="15%">Curr Qty</th>
	<th width="20%">Reorder Level</th>
	<th width="20%">Supplier</th>
	<th width="10%"></th>
</tr>
</table>
<?php   
$style="";
if (count($itemObj) > 5) {
$style="height:200px;overflow:auto;";
}
?>
<div style="<?php echo $style; ?>width:650px;">
<table class="report" width="630px">
<tr>
	<th width="35%"></th>
	<th width="15%"></th>
	<th width="20%"></th>
	<th width="20%"></th>
	<th width="10%"></th>
</tr>
<?php
foreach($itemObj as $obj){
	$qty = $obj->curr_quantity;
	if($obj->supplierid=="" || $obj->supplierid==null) { $supplierid=0; } else { $supplierid=$obj->supplierid; }
	$sObj = $clsSupplier->getSupplierById($supplierid);
	if(!$sObj) {
		$suppliername = null;
	} else {
		$suppliername = $sObj->suppliername;
	}
?>
<tr>
	<td width="35%"><?php echo $obj->itemname; ?></td>
	<td width="15%"><?php echo $qty; ?></td>
	<td width="20%"><?php echo $obj->reorderlevel; ?></td>
	<td width="20%"><?php echo $suppliername; ?></td>
	<td width="10%"><a href="javascript:{}" onclick="getForm(<?php echo $obj->id; ?>,'<?php echo $obj->itemname; ?>',<?php if(!$qty) {?>null<?php } else { echo $qty; } ?>,<?php echo $obj->reorderlevel; ?>,<?php echo $supplierid; ?>);">Update</a></td>
</tr>
<?php
}
?>
</table>
</div>
<div id="updateItemForm" style="display:none;">
<h3>Item Details</h3>
	<form method="post" action="postUpdateItem.php">
        	<input type="hidden" id="storeid" name="storeid" value="<?php echo $this->storeid;?>"/>
        	<input type="hidden" id="itemid" name="itemid"/>
        	<div class="clsDiv">Item Name</div>
        	<div class="clsText" id="itemname"></div>
        	<div class="clsDiv">Item Quantity</div>
        	<div class="clsText" id="quantity"></div>
        	<div class="clsDiv">Reorder Level</div>
        	<div class="clsText"><input type="text" id="reorderlevel" name="reorderlevel"/></div>
        	<div class="clsDiv">Supplier</div>
<?php
	$supObj = $clsSupplier->getAllSuppliers($this->storeid);
?>
                        <div class="clsText">
				<select id="supplierid" name="supplierid"/>
					<option value="0">Select</option>
<?php
	foreach($supObj as $ob) {
?>			
					<option value="<?php echo $ob->id; ?>"><?php echo $ob->suppliername;?></option>
<?php
	}
?>	
				</select>
			</div>
                        <input type="submit" id="submit" name="submit" value="Update"/>
                        <input type="hidden" name="form_id" value="1"/>
       </form>
</div><!-- updateItemForm -->
<span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
<?php 
}
}//else
?>
</div> <!-- colTwo -->
<?php
	} //pageContent
}//class
?>
