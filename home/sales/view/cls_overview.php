<?php
require_once "view/cls_renderer.php";
require_once "lib/core/SimpleDateTime.class.php";
require_once "lib/db/DBConn.php";

class cls_overview extends cls_renderer {

	var $params;
	var $currStore;
	var $firstDate, $minDays, $rangeMin, $rangeMax;

	function __construct($params=null) {
		$this->currStore = getCurrStore();
		$this->params = $params;
		if (!$this->currStore) { return; }
		$this->db = new DBConn();
		$storeid = $this->currStore->id;
		$query = "select min(date(bill_datetime)) as mindate, to_days(min(date(bill_datetime))) as mindays, to_days(max(date(bill_datetime))) as maxdays from it_orders where storeid=$storeid and status>0 and inactive=0";
		$obj = $this->db->fetchObject($query);
		$sd = new SimpleDateTime();
    		$this->firstDate = $sd->Format("Y-m-d","Y-m-d",$obj->mindate);
		$minDays = intVal($obj->mindays);
		$maxDays = intVal($obj->maxdays);
		$this->minDays = $minDays;
		$this->rangeMin = 0;
		$this->rangeMax = $maxDays-$minDays;
	}

	function extraHeaders() {
if (!$this->currStore) { return; }
?>

<link rel="stylesheet" href="../jqueryui/themes/base/jquery.ui.all.css">
<script src="../jqueryui/js/jquery-1.4.2.min.js"></script>
<script src="../jqueryui/ui/jquery.ui.core.js"></script>
<script src="../jqueryui/ui/jquery.ui.widget.js"></script>
<script src="../jqueryui/ui/jquery.ui.mouse.js"></script>
<script src="../jqueryui/ui/jquery.ui.slider.js"></script>
<script src="../js/common.js"></script>
<script src="../js/jsDate.js"></script>
<style>
	#demo-frame > div.demo { padding: 10px !important; };
</style>
<script>
var firstDate = new Date("<?php echo $this->firstDate; ?>");
var minDays = <?php echo $this->minDays; ?>;
var d1 = <?php echo $this->rangeMin; ?>;
var d2 = <?php echo $this->rangeMax; ?>;

function showRange() {
date1 = DateAdd("d", d1, firstDate);
date2 = DateAdd("d", d2, firstDate);
return date1.toDateString() + " - " + date2.toDateString();
}

$(function() {
	$( "#slider-range" ).slider({
		range: true,
		min: <?php echo $this->rangeMin; ?>,
		max: <?php echo $this->rangeMax; ?>,
		values: [ <?php echo $this->rangeMin; ?>, <?php echo $this->rangeMax; ?> ],
		slide: function( event, ui ) {
			d1 = ui.values[0];
			d2 = ui.values[1];
			$( "#amount" ).html(showRange());
		}
	});
	$( "#amount" ).html(showRange());
});
</script>

<script type="text/javascript" src="../ofc/js/swfobject.js"></script>
<script type="text/javascript">
function loadChart(divName, url,width, height) {
url+="&d1="+(minDays+d1);
url+="&d2="+(minDays+d2);
encodedUrl = urlencode(url);
swfobject.embedSWF(
  "../ofc/open-flash-chart.swf", divName, width, height,
  "9.0.0", "expressInstall.swf",
  {"data-file":encodedUrl}
  );
}
function loadAllCharts() {
loadChart("bar_hourly","chartdata/bar_hourly.php?pg=overview","650","300");
loadChart("pie_daywise","chartdata/pie_daywise.php?pg=overview","305","200");
loadChart("pie_halfs_amount","chartdata/pie_halfs.php?t=amount","305","200");
loadChart("pie_amount_threshold","chartdata/pie_amount_threshold.php?amount=200","305","200");
}
loadAllCharts();
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
$menuitem="overview";
include "storemenu.php";
?>
</div> <!-- div=colOne -->
<div id="colTwo">
		<h2>Store Overview</h2>
<div style="clear:both;padding:10px;border:3px solid #a0a0a0;;width:100%;margin-bottom:10px;">
<label for="amount">Date range:</label>
<span id="amount" style="color:#f6931f; font-weight:bold;"></span>

<div id="slider-range"></div>
<br />
<input type="button" onclick='loadAllCharts();' value="Reload" />
</div>

<div id="pie_daywise"></div>
<div id="pie_halfs_amount"></div>
<div id="pie_amount_threshold"></div>
<div id="bar_hourly"></div>

<?php
	} // pageContent()

}

?>
