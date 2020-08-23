<?php
require_once "view/cls_renderer.php";
require_once "lib/items/clsItems.php";
require_once "lib/supplier/clsSupplier.php";
class cls_inventory_reorderreport extends cls_renderer {
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
<script type="text/javascript" src="<?php echo DEF_SITEURL; ?>js/main.js"></script>
<script language="javascript">
	function getItemToReorderBySupplier(id) {
		var params="supId="+id;
		makeSecureRequest("<?php echo DEF_SITEURL;?>ajax/getReorder.php",params,getAjaxResp);
	}
	
	function getAjaxResp(response) {
		if (response.indexOf("error:") == 0) {
                        document.getElementById("displayReport").setAttribute("class", "error");
                        document.getElementById("displayReport").style.display="block";
                        document.getElementById("displayReport").innerHTML=response.substring(6);
                        return;
                } else {
                        document.getElementById("displayReport").setAttribute("class", "");
			document.getElementById("displayReport").style.display = "block";
			document.getElementById("displayReport").innerHTML = response;
		}
	}

</script>
<?php
	} // extraHeaders

	public function pageContent() {
?>
<?php
		if ($this->currStore) {
			$formResult = $this->getFormResult();
			$menuitem="reoderlevel";
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
} else {?>
<h3>Re-Order Report</h3>
<?php
$clsSupplier = new clsSupplier();
$sup = $clsSupplier->getAllSuppliers($this->storeid);
?>
<div class="clsDiv">Show Report By Supplier</div>
<div class="clsText">
	<select id="supplierid" name="supplierid" onchange="getItemToReorderBySupplier(this.value)">
		<option value="">Select</option>
<?php
	foreach($sup as $obj) {
?>
		<option value="<?php echo $obj->id; ?>"><?php echo $obj->suppliername; ?></option>
<?php
	}
?>
	</select>
</div>
<div id="displayReport" name="displayReport" style="diaplay:none;"></div>
<?php
} //else
?>
</div> <!-- colTwo -->
<?php
	} //pageContent
}//class
?>
