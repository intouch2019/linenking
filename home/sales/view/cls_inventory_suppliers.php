<?php
require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "lib/supplier/clsSupplier.php";
class cls_inventory_suppliers extends cls_renderer {
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
	} // extraHeaders

	public function pageContent() {
?>
<?php
		if ($this->currStore) {
			$formResult = $this->getFormResult();
			$menuitem="supplier";
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
<form method="post" action="postSupplier.php">
	<h3>Add Supplier</h3>
	<div class="clsDiv">Supplier's Name</div>
	<div class="clsText"><input type="text" id="supliername" name="suppliername"/></div>
	<div class="clsDiv">Address</div>
	<div class="clsText"><input type="text" id="address" name="address"/></div>
	<div class="clsDiv">Mobile</div>
	<div class="clsText"><input type="text" id="mobile" name="mobile"/></div>
	<div class="clsDiv">Alternate Phone</div>
	<div class="clsText"><input type="text" id="phone" name="phone"/></div>
	<div class="clsDiv">Email</div>
	<div class="clsText"><input type="text" id="email" name="email"/></div>
	<div class="clsText"><input type="submit" id="submit" name="submit" value="Add Supplier"></div>
	<input type="hidden" name="form_id" value="1" />
	<span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
</form>
<?php
	$clsSup = new clsSupplier();
	$supObj = $clsSup->getAllSuppliers($this->storeid);
if($supObj) {
?>
	<h3>Supplier's Details</h3>
<table class="report" width="630px">
	<tr>
		<th width="30%">Name</th>
		<th>Address</th>
		<th width="15%">Phone</th>
		<th width="30%">Email</th>
	</tr>
</table>
<?php
$style="";
if (count($supObj) > 5) {
$style="height:200px;overflow:auto;";
}
?>
<div style="<?php echo $style; ?>" width="650px">
<table class="report" width="630px">
	<tr>
		<th width="30%"></th>
		<th></th>
		<th width="15%"></th>
		<th width="30%"></th>
	</tr>
	<tr>
<?php
foreach($supObj as $obj) {
?>
		<td width="30%"><?php echo $obj->suppliername; ?></td>
		<td><?php echo $obj->address; ?></td>
		<td width="15%"><?php echo $obj->phone; ?></td>
		<td width="30%"><?php echo $obj->email; ?></td>
	</tr>
<?php
}
?>
</table>
</div>
<?php
}
}//else
?>
</div> <!-- colTwo -->
<?php
	} //pageContent
}//class
?>
