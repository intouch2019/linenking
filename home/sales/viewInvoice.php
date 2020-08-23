<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once("lib/supplier/clsSupplier.php");
require_once("lib/reorder/clsReorder.php");
extract($_POST);
$errors=array();
try {
	$clsReorder = new clsReorder();
	if(isset($_REQUEST['reorderid'])) { 
		$reorderId= $_REQUEST['reorderid']; 
	}
	$obj = $clsReorder->getReorderInfoById($reorderId);
	$arr = explode(" ",$obj->createtime);
	$date = $arr[0];
	$itemObj = $clsReorder->getItemsByReorderId($reorderId);
?>
<link href="<?php echo DEF_SITEURL; ?>css/default.css" rel="stylesheet" type="text/css" />
<h2 align="center">Intouch Consumer Care Solution Pvt. Ltd.</h2>
<h3 align="center">Purchase Order Details</h3>
<div style="float:right;"><input type="button" value="Print Purchase Order" onClick="window.print()" /></div>
<div style="clear:both;width:250px;float:left;">Supplier's Name</div>
<div style="width:250px;float:left;">:&nbsp;<?php echo $obj->suppliername; ?></div>
<div style="clear:both;width:250px;float:left;">Purchase Order Date</div>
<div style="width:250px;float:left;">:&nbsp;<?php echo $date; ?></div>
<table style="clear:both;" width=700px border="1px" border-style="dotted">
<tr>
	<th>Item Name</th>
	<th width="25%">Order Quantity</th>
</tr>
<?php
foreach($itemObj as $ob) {
?>
<tr>
<td><?php echo $ob->itemname; ?></td>
<td width="25%"><?php echo $ob->orderquantity; ?></td>
</tr>
<?php
}
?>
</table>
<?php
} catch (Exception $xcp) {
	$clsLogger = new clsLogger();
	$clsLogger->logError("Failed to Add Supplier Info:".$xcp->getMessage());
	$errors['status']="There was a problem processing your request. Please try again later";
}
?>
