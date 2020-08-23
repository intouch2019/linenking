<?php
require_once "view/cls_renderer.php";
require_once "lib/core/SimpleDateTime.class.php";
require_once "lib/db/DBConn.php";

class cls_corporate extends cls_renderer {

	var $params;
	var $currStore;
	var $minDate, $maxDate, $rangeMin, $rangeMax;

	function __construct($params=null) {
		$this->currStore = getCurrStore();
		$this->params = $params;
		if (!$this->currStore) { return; }
		$storeid = $this->currStore->id;
		$this->rangeMin = "2011-06-01";
		$this->rangeMax = "2011-08-10";
		if (isset($_SESSION['daterange'])) {
			//print "session=".$_SESSION['daterange'];
			list($this->minDate,$this->maxDate) = explode(",",$_SESSION['daterange']);
		} else {
			$this->minDate = "2011-06-01";
			$this->maxDate = "2011-08-10";
			$_SESSION['daterange'] = $this->minDate.",".$this->maxDate;
		}
	}

	function extraHeaders() {
if (!$this->currStore) { return; }
?>

<style type="text/css" title="currentStyle">
	@import "../js/datatables/media/css/demo_page.css";
	@import "../js/datatables/media/css/demo_table.css";
</style>
<link rel="stylesheet" href="../jqueryui/themes/base/jquery.ui.all.css">
<script src="../jqueryui/js/jquery-1.4.2.min.js"></script>
<script src="../jqueryui/ui/jquery-ui-1.8.6.custom.js"></script>
<script src="../jqueryui/ui/jquery.ui.core.js"></script>
<script src="../jqueryui/ui/jquery.ui.widget.js"></script>
<script src="../jqueryui/ui/jquery.ui.mouse.js"></script>
<script src="../jqueryui/ui/jquery.ui.datepicker.js"></script>
<script src="../js/datatables/media/js/jquery.dataTables.js"></script>
<script src="../js/common.js"></script>
<script src="../js/jsDate.js"></script>
<style>
	#demo-frame > div.demo { padding: 10px !important; };
</style>
<script>
var gType=1; // 1 = numOrders, 2=byAmount

function toDays(dt) {
var dd = DateDiff("d", new Date("0000-01-01"), new Date(dt));
}

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
	$( "#tabs" ).tabs();
	loadStores();
});
function loadStores() {
	var url = addDates("chartdata/tb_storeSales.php");
	sTable = $('#tb_storeSales').dataTable( {
		"bProcessing": true,
		"bServerSide": true,
		"bDestroy": true,
		"sAjaxSource": url
	} );
/*
	if ( sTable.length > 0 ) {
		sTable.fnAdjustColumnSizing();
	}
*/
	// search on pressing Enter key only
	$('.dataTables_filter input').unbind('keyup').bind('keyup', function(e){
		if (e.which == 13){
			sTable.fnFilter($(this).val(), null, false, true);
		}
	});
}

function reload(func) {
saveDatesUrl = addDates("chartdata/savedates.php");
$.ajax({
  url: saveDatesUrl,
  success: function(data) {
    eval(func);
  }
});
}

function today(func) {
saveDatesUrl = "chartdata/savedates.php?today=1";
$.ajax({
  url: saveDatesUrl,
  success: function(data) {
    eval(func);
  }
});
}

function addDates(url) {
fromElem=document.getElementById("from");
toElem=document.getElementById("to");
var minDate=null, maxDate=null;
if (fromElem != null) minDate = fromElem.value;
if (toElem != null) maxDate = toElem.value;
if (minDate != null && maxDate != null) {
if (url.indexOf("?") == -1) { url+="?"; }
else { url+="&";}
url+="d1="+minDate;
url+="&d2="+maxDate;
}
return url;
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
</div>
<?php
return;
}
$menuitem="home";
include "storemenu.php";
?>
</div> <!-- div=colOne -->
<div id="colTwo">
		<h2>Dashboard</h2>
<div style="clear:both;padding:10px;border:3px solid #a0a0a0;;width:100%;margin-bottom:10px;">
<label>Date range:</label>
<label for="from">From</label>
<input type="text" id="from" name="from" class="datepick" value="<?php echo $this->minDate; ?>" />
<label for="to">to</label>
<input type="text" id="to" name="to" class="datepick" value="<?php echo $this->maxDate; ?>" /> <span class="help">Change the date range and click on the "Reload" button</span>
<br />
<input type="button" onclick="reload('window.location.reload()');" value="Reload" />
<input type="button" onclick="today('window.location.reload()');" value="Today" />
<hr>
<div style="clear:both;margin-bottom:5px;">
</div>
<div id="tabs">
	<ul>
		<li><a href="#tabs-1">Overview</a></li>
		<li><a href="#tabs-2">Store Summary</a></li>
	</ul>
<div id="tabs-1">
<?php
		list($minDate,$maxDate) = explode(",",$_SESSION['daterange']);
		$db = new DBConn();
		$storeid = $this->currStore->id;
		$dClause = " and date(bill_datetime) >= ".$db->safe($minDate)." and date(bill_datetime) <= ".$db->safe($maxDate);
		$query = "select sum(bill_amount) as totamount, sum(bill_quantity) as totitems, count(*) as totreceipts from it_orders where storeid in(34,35,36) and status>0 and inactive=0 $dClause";
		$summary = $db->fetchObject($query);
?>
<h3>Sales Summary from <?php echo $minDate; ?> to <?php echo $maxDate; ?></h3>
<label>Number of stores:</label> 3<br />
<label>Total Receipts:</label> <?php echo $summary->totreceipts; ?><br />
<label>Total Amount:</label> <?php echo intval($summary->totamount); ?><br />
<label>Total Items:</label> <?php echo $summary->totitems; ?><br />
</div>
<div id="tabs-2">
<table cellpadding="0" cellspacing="0" border="0" class="display" id="tb_storeSales">
	<thead>
		<tr>
			<th width="30%">Store Name</th>
			<th>Total Receipts</th>
			<th>Total Amount</th>
			<th>Total Items</th>
			<th>Average Price</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td colspan="5" class="dataTables_empty">Loading data from server</td>
		</tr>
	</tbody>
</table>
</div>
</div> <!-- div id=tabs -->
</div> <!-- border -->
</div> <!-- colTwo -->

<?php
	} // pageContent()

}

?>
