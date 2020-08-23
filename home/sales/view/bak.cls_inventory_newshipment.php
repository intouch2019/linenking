<?php
require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "lib/items/clsItems.php";
class cls_inventory_newshipment extends cls_renderer {
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
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js" type="text/javascript"></script>
<script type="text/javascript" src="<?php echo DEF_SITEURL; ?>js/main.js"></script>
<script type="text/javascript" src="<?php echo DEF_SITEURL; ?>util/jquery-ui-1.8.12.custom/js/jquery-1.5.1.min.js"></script>
<script type="text/javascript" src="<?php echo DEF_SITEURL; ?>util/jquery-ui-1.8.12.custom/js/jquery-ui-1.8.12.custom.min.js"></script>
<link type="text/css" href="<?php echo DEF_SITEURL; ?>util/jquery-ui-1.8.12.custom/css/ui-lightness/jquery-ui-1.8.12.custom.css" rel="stylesheet" />   

<!--DatePicker-->
<link rel="stylesheet" type="text/css" href="<?php echo DEF_SITEURL; ?>util/calendar/datepicker/jsDatePick_ltr.min.css" />
<script type="text/javascript" src="<?php echo DEF_SITEURL; ?>util/calendar/datepicker/jsDatePick.min.1.3.js"></script>
<script language="javascript">
	var gShipmentId=null;
	$(document).ready(function(){
<?php
if (isset($this->params['sid'])) {
?>
		viewStockDetails(<?php echo $this->params['sid']; ?>);
<?php
}
?>
        });

	function dump(obj) {
	    var out = '';
	    for (var i in obj) {
	        //out += i + ": " + obj[i] + "\n";
	        out = obj[i];
	    }
	    return out;
	}


	function arrivaldatePicker() {
                new JsDatePick({
                        useMode:2,
                        target:"arrival_date",
                        dateFormat:"%Y-%m-%d"
                });
        }

	function stockeddatePicker() {
                new JsDatePick({
                        useMode:2,
                        target:"stocked_date",
                        dateFormat:"%Y-%m-%d"
                });
        }
	
	function createShipment() {
		document.getElementById("shipmentDiv").style.display="block";
	}
	
	function viewStockDetails(id) {
		document.getElementById("stockFormDiv").style.display="none";
		document.getElementById("shipmentInfo").style.display="block";
		gShipmentId = id;
		var params = "shid="+id;
		params += "&storeid="+<?php echo $this->storeid; ?>;
		makeSecureRequest("<?php echo DEF_SITEURL;?>ajax/getStockByShipmentId.php",params,getAjaxResp);
	}

	function getAjaxResp(response) {
		if (response.indexOf("error:") == 0) {
			document.getElementById("itemsInfo").style.display="none";
                        document.getElementById("msg").setAttribute("class", "error");     
                        document.getElementById("msg").style.display="block";      
                        document.getElementById("msg").innerHTML=response.substring(6);
                        return;
		} else {
			document.getElementById("itemsInfo").style.display="block";
                        document.getElementById("msg").style.display="none";      
			document.getElementById("itemsInfo").innerHTML=response;
	        }	
	}

	function getAddStockForm() {
                var availableItems = new Array();
                var arr = new Array();
                var gAvailItems = new Array();
                storeid = document.getElementById("storeid").value;
                var params="storeid="+storeid;
                makeSecureRequest("<?php echo DEF_SITEURL;?>ajax/getItems.php",params,function(response){
                        arr = response.split("<>");  
                	var arr1 = new Array();
			for(i=0;i<arr.length;i++) {
                        	arr1 = arr[i].split("||");
				availableItems[i]=arr1[0];
				gAvailItems[arr1[0]]=arr1[1];
			}  
                   //AutoComplete
                        $( "#rawitemname" ).autocomplete({
                              source: availableItems,
			      minLength: 2,
			      change: function(event, ui) { 
					var item = dump(ui.item); 
					//alert(gAvailItems[item]);
                        		document.getElementById("rawitemid").value=gAvailItems[item];
				}
                        });
                });
		document.getElementById("stockFormDiv").style.display="block";
		document.getElementById("shipmentid").value=gShipmentId;
	}
	</script>

<?php
	} // extraHeaders

	public function pageContent() {
?>
<?php
		if ($this->currStore) {
		$formResult = $this->getFormResult();
		$menuitem="newshipment";
		include "storemenu.php";
		}
?>
</div> <!-- div=colOne -->
<div id="colTwo">
<?php if (!$this->currStore) { ?>
<h3>Session Expired</h3>
Your session has expired. Click <a href="">here</a> to login.
<?php } else { ?>
<h3>Shipments Information</h3>
<br />
<input type="hidden" id="storeid" name="storeid" value="<?php echo $this->storeid;?>"/>
<input type="button" onclick="createShipment();" value="New Shipment"></input>
<br /><br />
<div id="shipmentDiv" style="display:none">
	<form method="post" action="postShipment.php">
		<div style="margin-top:3px; margin-bottom:2px;"><b>Create New Shipment</b></div>
		<div class="clsDiv">Supplies Arrival Date</div>
		<div class="clsText"><input type="text" id="arrival_date" name="arrival_date" onclick="arrivaldatePicker();" readonly/></div>
		<div class="clsDiv">Supplies Stocked Date</div>
		<div class="clsText"><input type="text" id="stocked_date" name="stocked_date" onclick="stockeddatePicker();" readonly/></div>
		<input type="submit" id="submit" name="submit" value="Create"/>
		<input type="hidden" name="form_id" value="1"/>
	</form>
</div>
<?php if ($formResult->form_id == "1") { ?>
<span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
<?php 
} 
	$classItem = new clsItems();
	$itemInfo = $classItem->getAllShipmentInfo($this->storeid);
if($itemInfo) { 
?>
	<table class="report">
		<tr>
			<th width="100px">Shipment No</th>	
			<th width="150px">Shipment Arrival Date</th>	
			<th width="150px">Shipment Stocked Date</th>	
			<th width="75px"></th>	
		</tr>
	</table>
<?php
$style="";
if (count($itemInfo) > 5) {
$style="height:200px;overflow:auto;";
}
?>
<div style="<?php echo $style; ?>">
<table class="report">	
<?php
	foreach($itemInfo as $obj) {
?>
	<tr>
		<td width="100px"><?php echo $obj->id; ?></td>
		<td width="150px"><?php echo $obj->arrival_date; ?></td>
		<td width="150px"><?php echo $obj->stocked_date; ?></td>
		<td width="75px"><a href="javascript:{}" onclick="viewStockDetails(<?php echo $obj->id;?>)">View</a></td>
	</tr>
<?php
	}
?>
</table>
</div>
<div id="shipmentInfo" style="display:none;">
	<div id="msg" style="display:none;"></div>
	<div style="display:none;" class="report" id="itemsInfo"></div>
	<div><a href="javascript:{}" onclick="getAddStockForm();"><b>Add New Stock</b></a></div>
	<div id="stockFormDiv" style="display:none;">
		<form method="post" action="postAddStock.php">
        		<input type="hidden" id="storeid" name="storeid" value="<?php echo $this->storeid;?>"/>
       			<input type="hidden" id="shipmentid" name="shipmentid"/>
        		<div class="ui-widget">
                		<label for="rawitemname" class="clsDiv">Item Name</label>
                		<div class="clsText"><input id="rawitemname" name="rawitemname" size=30 /> (type a few characters to find matching items)</div>
        		</div>
       			<input type="hidden" id="rawitemid" name="rawitemid"/>
        		<div class="clsDiv">Item Quantity</div>
        		<div class="clsText"><input id="quantity" name="quantity" size=5 /></div>
        		<input type="submit" id="submit" name="submit" value="AddStock"/>
        		<input type="hidden" name="form_id" value="2"/>
		</form>
	</div> <!-- stockFormDiv -->
<?php if ($formResult->form_id == "2") { ?>
<span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
<?php } ?>
</div><!-- shipmentInfo -->
<?php
}
} // end if currStore
?>
</div> <!-- colTwo -->
<?php
	} //pageContent
}//class
?>
