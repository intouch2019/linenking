<?php
require_once "view/cls_renderer.php";
require_once "lib/core/SimpleDateTime.class.php";
require_once "lib/db/DBConn.php";

class cls_products_overview extends cls_renderer {

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
<script src="../jqueryui/ui/jquery.ui.dialog.js"></script>
<script src="../jqueryui/ui/jquery.ui.datepicker.js"></script>
<script src="../js/common.js"></script>
<script src="../js/jsDate.js"></script>
<style>
	#demo-frame > div.demo { padding: 10px !important; };
</style>
<script>
var gFieldName="linequantity";
var gBarFrom = 0;
var gBarNum = 50;

$(function() {
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
if (divName == "bar_allitems") {
//clickEvent(url);
}
swfobject.embedSWF(
  "../ofc/open-flash-chart.swf", divName, width, height,
  "9.0.0", "expressInstall.swf",
  {"data-file":encodedUrl}
  );
}
}
function loadAllCharts() {
	gBarFrom=0;
	loadTopLow();
	loadAllItems();
}
function loadTopLow() {
loadChart("pie_topN","chartdata/products/pie_toplow.php?order=desc&num=10&field="+gFieldName+"&title=10 Most Selling Items","305","200");
loadChart("pie_lowN","chartdata/products/pie_toplow.php?order=&num=10&field="+gFieldName+"&title=10 Least Selling Items","305","200");
}
function loadAllItems() {
loadChart("bar_allitems","chartdata/products/bar_allitems.php?field="+gFieldName+"&barFrom="+gBarFrom+"&barNum="+gBarNum+"&title=All Items","615","300");
}
function loadByOrders() {
gFieldName="linequantity";
loadAllCharts();
}
function loadByRevenue() {
gFieldName="linetotal";
loadAllCharts();
}
function prevItems() {
gBarFrom -= gBarNum;
loadAllItems();
}
function nextItems() {
gBarFrom += gBarNum;
loadAllItems();
}
function clickEvent(msg) {
msg = unescape(msg);
document.getElementById("msgbar").innerHTML = msg;
}

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
<?php
return;
}
$menuitem="poverview";
include "storemenu.php";
?>
</div> <!-- div=colOne -->
<div id="colTwo">
		<h2>Products Overview</h2>
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
<div id="pie_topN"></div>
<div id="pie_lowN"></div>
<div id="msgbar" style="clear:both;height:40px;"></div>
<div id="bar_allitems"></div>

<script type="text/javascript">
loadAllCharts();
</script>

<?php
	} // pageContent()

}

?>
