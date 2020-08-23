<?php
require_once "view/cls_renderer.php";
require_once "lib/reorder/clsReorder.php";
class cls_inventory_purchaseorders extends cls_renderer {
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
			$menuitem="reoderhistory";
			include "storemenu.php";
			$clsReorder = new clsReorder();
			$orderObj = $clsReorder->getAllReorder($this->currStore->id);
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
} else { ?>
<h3>Purchase Order History</h3>
<a href="inventory/reorderreport">New Order</a>
<?php
if($orderObj) {
?>
<table class="report" width="630px">
	<tr>
		<th width="100px">Id</th>
		<th>SupplierName</th>
		<th width="100px">Date</th>
		<th width="100px"></th>
	</tr>
</table>
<?php
$style="";
if (count($orderObj) > 5) {
$style="height:200px;overflow:auto;";
}
?>
<div style="<?php echo $style; ?>" width="650px">
<table class="report" width="630px">
	<tr>
		<th width="100px"></th>
		<th></th>
		<th width="100px"></th>
		<th width="100px"></th>
	</tr>
<?php
foreach($orderObj as $obj) {
	$arr=explode(" ",$obj->createtime );
	$date=$arr[0];
?>	
	<tr>
		<td width="100px"><?php echo $obj->id; ?></td>
		<td><?php echo $obj->suppliername; ?></td>
		<td width="100px"><?php echo $date;?></td>
		<td width="100px"><a href="javascript:{}" onClick="window.open('viewInvoice.php?reorderid=<?php echo $obj->id; ?>','invoicewindow','width=800,height=500')">View</a></td>
	</tr>	
<?php
}
?>
</table>
<?php
}
?>
</div>
<?php
}//else
?>
</div> <!-- colTwo -->
<?php
	} //pageContent
}//class
?>
