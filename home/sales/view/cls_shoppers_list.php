<?php
require_once "view/cls_renderer.php";
require_once "lib/core/SimpleDateTime.class.php";
require_once "lib/db/DBConn.php";

class cls_shoppers_list extends cls_renderer {

	var $params;
	var $currStore;

	function __construct($params=null) {
		$this->currStore = getCurrStore();
		$this->params = $params;
		if (!$this->currStore) { return; }
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

$(function() {
	$( "#tabs" ).tabs();
	loadShoppers();
});
function loadStores() {
	var url = "chartdata/shoppers/tb_shoppers.php";
	sTable = $('#tb_shoppers').dataTable( {
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
<div id="tabs">
	<ul>
		<li><a href="#tabs-1">Overview</a></li>
		<li><a href="#tabs-2">Shoppers List</a></li>
	</ul>
<div id="tabs-1">
<?php
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
<table cellpadding="0" cellspacing="0" border="0" class="display" id="tb_shoppers">
	<thead>
		<tr>
			<th>Customre Name</th>
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
