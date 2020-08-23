<?php
require_once "view/cls_renderer.php";
require_once "lib/core/SimpleDateTime.class.php";
require_once "lib/db/DBConn.php";

class cls_stats_daily extends cls_renderer {

	var $params;
	var $currStore;
	var $minDate, $maxDate, $rangeMin, $rangeMax;

	function __construct($params=null) {
		$this->currStore = getCurrStore();
		$this->params = $params;
		if (!$this->currStore) { return; }
		$this->db = new DBConn();
		$storeid = $this->currStore->id;
		$query = "select min(date(bill_datetime)) as mindate, max(date(bill_datetime)) as maxdate from it_orders where storeid=$storeid and status>0 and inactive=0";
		$obj = $this->db->fetchObject($query);
		if ($obj) {
			$this->rangeMin = $obj->mindate;
			$this->rangeMax = $obj->maxdate;
		}
		if (isset($_SESSION['daterange'])) {
			list($this->minDate,$this->maxDate) = explode(",",$_SESSION['daterange']);
		} else {
			$this->minDate = $obj->mindate;
			$this->maxDate = $obj->maxdate;
		}
	}

	function extraHeaders() {
if (!$this->currStore) { return; }
?>

<link rel="stylesheet" href="../jqueryui/themes/base/jquery.ui.all.css">
<script src="../jqueryui/js/jquery-1.4.2.min.js"></script>
<script src="../jqueryui/ui/jquery.ui.core.js"></script>
<script src="../jqueryui/ui/jquery.ui.widget.js"></script>
<script src="../jqueryui/ui/jquery.ui.mouse.js"></script>
<script src="../jqueryui/ui/jquery.ui.draggable.js"></script>
<script src="../jqueryui/ui/jquery.ui.position.js"></script>
<script src="../jqueryui/ui/jquery.ui.resizable.js"></script>
<script src="../jqueryui/ui/jquery.ui.dialog.js"></script>
<script src="../jqueryui/ui/jquery.ui.datepicker.js"></script>
<script src="../js/common.js"></script>
<script src="../js/jsDate.js"></script>
<style>
	#demo-frame > div.demo { padding: 10px !important; };
</style>
<script>
var gType=1; // 1 = numOrders, 2=byAmount

$(function() {
	$("#dialog").dialog({
		modal: true,
		autoOpen: false,
		minHeight: 500,
		maxHeight: 500
	});
	var dates = $( "#from, #to" ).datepicker({
		changeMonth: true,
		numberOfMonths: 1,
		dateFormat: 'yy-mm-dd',
<?php if ($this->rangeMin && $this->rangeMax) { ?>
		minDate: '<?php echo $this->rangeMin; ?>',
		maxDate: '<?php echo $this->rangeMax; ?>',
<?php } ?>
		onSelect: function( selectedDate ) {
			var option = this.id == "from" ? "minDate" : "maxDate",
			instance = $( this ).data( "datepicker" ),
			date = $.datepicker.parseDate(
				instance.settings.dateFormat ||
				$.datepicker._defaults.dateFormat,
				selectedDate, instance.settings );
			dates.not( this ).datepicker( "option", option, date );
		}
	});
});
</script>

<script type="text/javascript" src="../ofc/js/swfobject.js"></script>
<script type="text/javascript">
var minDate = "<?php echo $this->minDate; ?>";
var maxDate = "<?php echo $this->maxDate; ?>";
function loadChart(divName, url,width, height) {
fromElem=document.getElementById("from");
toElem=document.getElementById("to");
if (fromElem != null) minDate = fromElem.value;
if (toElem != null) maxDate = toElem.value;
if (minDate != null && maxDate != null) {
url+="&d1="+minDate;
url+="&d2="+maxDate;
encodedUrl = urlencode(url);
swfobject.embedSWF(
  "../ofc/open-flash-chart.swf", divName, width, height,
  "9.0.0", "expressInstall.swf",
  {"data-file":encodedUrl},
  {wmode:"transparent"}
  );
}
}
function loadAllCharts() {
loadChart("my_chart","chartdata/daily_avg_order.php?type="+gType,"650","300");
}
function loadByOrders() {
gType=1;
loadAllCharts();
}
function loadByRevenue() {
gType=2;
loadAllCharts();
}
function daily_orders(bdate) {
loadChart("bar_daily_orders","chartdata/bar_daily_orders.php?bdate="+bdate+"&type="+gType,"650","300");
}
function showReceipt(orderid,showparsed) {
	$.ajax({
		url: "/getorderinfo.php?id="+orderid+"&parsed="+showparsed,
		success: function(data) {
			$("#dialog").html(data);
			$("#dialog").dialog('open');
		}
	});
}

loadAllCharts();
</script>
<?php
	}

	public function pageContent() {
if (!$this->currStore) { return; }
$menuitem="daily";
include "storemenu.php";
?>
</div> <!-- div=colOne -->
<div id="colTwo">
		<h2>Daily Stats</h2>
<div style="clear:both;padding:10px;border:3px solid #a0a0a0;;width:100%;margin-bottom:10px;">
<label>Date range:</label>
<label for="from">From</label>
<input type="text" id="from" name="from" class="datepick" value="<?php echo $this->minDate; ?>" />
<label for="to">to</label>
<input type="text" id="to" name="to" class="datepick" value="<?php echo $this->maxDate; ?>" /> <span class="help">Change the date range and click on the "Reload" button</span>
<input type="button" onclick='loadAllCharts();' value="Reload" />
<hr>
<div style="clear:both;margin-bottom:5px;">
<form name="chartTypeForm">
Show <input type="radio" name="chartType" checked onclick="loadByOrders();">By Sales</input>
<input type="radio" name="chartType" onclick="loadByRevenue();">By Value</input> <span class="help">Choose to toggle the display between Units and Value</span>
</form>
</div>
</div>

<div id="my_chart" style="clear:both;"></div>
<div id="bar_daily_orders" style="clear:both;">Click on the chart above to view store receipts for a particular day</div>
<div id="dialog" title="Store Receipt">
</div>

<?php
	} // pageContent()

}

?>
