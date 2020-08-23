<?php
require_once "view/cls_renderer.php";
require_once "lib/items/clsItems.php";
class cls_addstock extends cls_renderer {
        var $params;
        var $currStore;
	var $storeid;
	var $shipmentid;
        function __construct($params=null) {
		$this->currStore = getCurrStore();
                $this->params = $params;
                if (!$this->currStore) { return; }
                $this->storeid = $this->currStore->id;
		$this->shipmentid = $params['shid'];
	}
	
	function extraHeaders() {
		if (!$this->currStore) { return; }
?>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js" type="text/javascript"></script>
<script type="text/javascript" src="<?php echo DEF_SITEURL; ?>js/main.js"></script>
<script type="text/javascript" src="<?php echo DEF_SITEURL; ?>util/jquery-ui-1.8.12.custom/js/jquery-1.5.1.min.js"></script>
<script type="text/javascript" src="<?php echo DEF_SITEURL; ?>util/jquery-ui-1.8.12.custom/js/jquery-ui-1.8.12.custom.min.js"></script>
<link type="text/css" href="<?php echo DEF_SITEURL; ?>util/jquery-ui-1.8.12.custom/css/ui-lightness/jquery-ui-1.8.12.custom.css" rel="stylesheet" />   

<script language="javascript">
	$(document).ready(function(){
		storeid = document.getElementById("storeid").value;
		var params="storeid="+storeid;
		var availableItems = new Array();
		var arr = new Array();
		makeSecureRequest("http://localhost/intouch/home/ajax/getItems.php",params,function(response){
		//makeSecureRequest("../../ajax/getItems.php",params,function(response){
			availableItems = response.split("<>"); 	
                   //AutoComplete
                	$( "#rawitemid" ).autocomplete({
                	      source: availableItems
                	});
		});
	});

	</script>
	<style type="text/css">
        #report { border-collapse:collapse;}
        #report h4 { margin:0px; padding:0px;}
        #report img { float:right;}
        #report ul { margin:10px 0 10px 40px; padding:0px;}
        #report th { background:#7CB8E2 url(../util/jExpand/header_bkg.png) repeat-x scroll center left; color:#fff; padding:7px 15px; text-align:left;}
        #report td { background:#C7DDEE none repeat-x scroll center left; color:#000; padding:7px 15px; }
        #report tr.odd td { background:#fff url(../util/jExpand/row_bkg.png) repeat-x scroll center left; cursor:pointer; }
        #report div.arrow { background:transparent url(../util/jExpand/arrows.png) no-repeat scroll 0px -16px; width:16px; height:16px; display:block;}
        #report div.up { background-position:0px 0px;}
	</style>

<?php
	} // extraHeaders

	public function pageContent() {
?>
<?php
		if (!$this->currStore) {
?>
<h2>Session Expired</h2>
Your session has expired. Click <a href="store/">here</a> to login.
<?php
			return;
		}
		$formResult = $this->getFormResult();
		$menuitem="newshipment";
		include "storemenu.php";
?>
</div> <!-- div=colOne -->
<div id="colTwo">
<h3>Shipments Information</h3>
<div>Shipment Id : <?php echo $this->shipmentid; ?></div>	
<form method="post" action="postAddStock.php">
	<input type="hidden" id="storeid" name="storeid" value="<?php echo $this->storeid;?>"/>
	<input type="hidden" id="shipmentid" name="shipmentid" value="<?php echo $this->shipmentid;?>"/>
	<div class="clsDiv">Item SKU</div>
	<div class="clsText"><input id="itemsku" name="itemsku"/></div>	
	<div class="ui-widget">
		<label for="rawitemid" class="clsDiv">Item Name</label>
		<div class="clsText"><input id="rawitemid" name="rawitemid"/></div>	
	</div>
	<div class="clsDiv">Item Quantity</div>
	<div class="clsText"><input id="quantity" name="quantity"/></div>	
	<input type="submit" id="submit" name="submit" value="AddStock"/>
	<input type="hidden" name="form_id" value="1"/>
	<span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
</form>
<?php
	$classItem = new clsItems();
	$itemInfo = $classItem->getInventoryByShipmentId($this->storeid,$this->shipmentid);
if($itemInfo) { 
?>
	<table id="report">
		<tr>
			<th width="150px">Item SKU</th>	
			<th width="200px">Item Name</th>	
			<th width="150px">Quantity</th>	
		</tr>
	</table>
<div style="overflow:auto;height:200px;">
<table id="report">	
<?php
	foreach($itemInfo as $obj) {
?>
	<tr>
		<td width="150px"><?php echo $obj->itemsku; ?></td>
		<td width="200px"><?php echo $obj->itemname;?></td>
		<td width="150px"><?php echo $obj->quantity; ?></td>
	</tr>
<?php
	}
?>
</table>
</div>
<?php
}
?>
</div> <!-- colTwo -->
<?php
	} //pageContent
}//class
?>
