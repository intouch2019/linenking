<?php
require_once "view/cls_renderer.php";
require_once "lib/core/SimpleDateTime.class.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/strutil.php";

class cls_admin_graph_stores extends cls_renderer {

	var $params;
	var $currStore;
	var $minDate, $maxDate, $rangeMin, $rangeMax;

	function __construct($params=null) {
//error_reporting(E_ALL);
//ini_set('display_errors', '1');
        //parent::__construct(array(UserType::Admin, UserType::CKAdmin));
        $this->currStore = getCurrUser();
        $this->params = $params;
        $this->db = new DBConn();
	$query = "select DATE_FORMAT(NOW() ,'%Y-%m-01') as mindate, date(now()) as maxdate";
		$obj = $this->db->fetchObject($query);
		if ($obj) {
			$this->rangeMin = ddmmyy($obj->mindate);
			$this->rangeMax = ddmmyy($obj->maxdate);
                        //print_r ($this->rangeMin);
		}
		if (isset($_SESSION['daterange'])) {
			list($this->minDate,$this->maxDate) = explode(",",$_SESSION['daterange']);
		} else {
			$this->minDate = ddmmyy($obj->mindate);
			$this->maxDate = ddmmyy($obj->maxdate);
                        $_SESSION['daterange'] = $this->minDate.",".$this->maxDate;
		}
        
        if (isset($_SESSION['storeid'])) { $this->storeid = $_SESSION['storeid']; }	
        else { $this->storeid = "All Stores"; }
	}

	function extraHeaders() {
if (!$this->currStore) { return; }
?>
        
        <script src="jqueryui/ui/jquery.ui.datepicker.js"></script>
        <link rel="stylesheet" href="css/redmond/jquery-ui-1.7.1.custom.css" type="text/css" title="ui-theme" />
        <script type="text/javascript" src="ofc/js/swfobject.js"></script>
        <script src="js/common.js"></script>
    <!--<script type="text/javascript" src="js/daterangepicker.jQuery.js"></script>
    <script type="text/javascript" src="jqueryui/js/jquery-ui-1.7.1.custom.min.js"></script>
    <link rel="stylesheet" href="css/ui.daterangepicker.css" type="text/css" />
    <link rel="stylesheet" href="jqueryui/themes/base/jquery.ui.all.css">
    <script src="jqueryui/ui/jquery.ui.core.js"></script>
    <script src="jqueryui/ui/jquery.ui.widget.js"></script>
    <script src="jqueryui/ui/jquery.ui.mouse.js"></script>
    <script src="js/jsDate.js"></script>
    <link rel="stylesheet" href="css/ui.daterangepicker.css" type="text/css" />
    <link rel="stylesheet" href="css/redmond/jquery-ui-1.7.1.custom.css" type="text/css" title="ui-theme" />-->

<script>
var gType=1; // 1 = numOrders, 2=byAmount

$(function() {
        <?php if ($this->rangeMin && $this->rangeMax) { ?>
		var minDate = '<?php echo $this->rangeMin; ?>';
		var maxDate = '<?php echo $this->rangeMax; ?>';
        <?php } ?>
	var dates = $( "#from, #to" ).datepicker({
		changeMonth: true,
		numberOfMonths: 1,
		dateFormat: 'dd-mm-yy',
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

<script type="text/javascript">

function loadChart(divName, url,width, height) {
    var minDate = "<?php echo $this->minDate; ?>";
    var maxDate = "<?php echo $this->maxDate; ?>";
    var mnDate, mxDate;
    fromElem=document.getElementById("from");
    toElem=document.getElementById("to");
    if (fromElem != null) minDate = fromElem.value;
    if (toElem != null) maxDate = toElem.value;
    if (minDate != null && maxDate != null) {
        var ajaxUrl = "ajax/changedate.php?mindate="+minDate+"&maxdate="+maxDate;
        $.getJSON(ajaxUrl, function(data){
             mnDate=data.mindate;
             mxDate=data.maxdate;
            url+="&d1="+mnDate;
            url+="&d2="+mxDate;
            encodedUrl = urlencode(url);
            //alert(url);
            swfobject.embedSWF(
              "ofc/open-flash-chart.swf", divName, width, height,
              "9.0.0", "expressInstall.swf",
              {"data-file":encodedUrl},
              {wmode:"transparent"}
              );
        });
    }
}

function loadAllCharts() {
loadChart("bar_hourly","chartdata/bar_store_order.php?type="+gType,"705","300");
$('#bar_eachstore_orders').hide();
}
function loadByOrders() {
gType=1;
loadAllCharts();
}
function loadByRevenue() {
gType=2;
loadAllCharts();
}

function store_orders(storeid) {
    //loadChart("bar_eachstore_orders","chartdata/daily_avg_order.php?storeid="+storeid+"&type="+gType,"705","300");
}

function store_category(storeid) {
    loadChart("bar_eachstore_orders","chartdata/store_category.php?storeid="+storeid+"&type="+gType,"705","400");
}

function daily_orders(bdate,storeid) {
    //loadChart("bar_daily_orders","chartdata/bar_daily_orders.php?bdate="+bdate+"&storeid="+storeid+"&type="+gType,"705","300");
}
function loadpage(pid) {
    window.location.href="dispatch/vieworder/pid="+pid;
}
loadAllCharts();
</script>
<?php
	}

	public function pageContent() {         
            $menuitem = "storegraph";
            include "sidemenu.".$this->currStore->usertype.".php";
            //if ($this->currStore->usertype == UserType::Admin || $this->currStore->usertype == UserType::CKAdmin) {} else { print "Unauthorized Access"; return; }
?>
<!-- div=colOne -->
<div class="grid_10">
		<h2>Store Analysis</h2>
<div class="grid_12">
    <div class="box">
        <label>Date range:</label>
        <label for="from">From</label>
        <input type="text" id="from" name="from" class="datepick" value="<?php echo $this->minDate; ?>" />
        <label for="to">to</label>
        <input type="text" id="to" name="to" class="datepick" value="<?php echo $this->maxDate; ?>" /> <span class="help">Change the date range and click on the "Reload" button</span>
        <input type="button" onclick='loadAllCharts();' value="Reload" />
        <hr>
        <div style="clear:both;margin-bottom:5px;">
        <form name="chartTypeForm">
        Show <input type="radio" name="chartType" checked onclick="loadByOrders();">By Orders</input>
        <input type="radio" name="chartType" onclick="loadByRevenue();">By Amount</input> <span class="help" style="font-style:italic; font-weight: bold; padding-left: 10px;">Choose to toggle the display between Units and Value</span>
        </form>
        </div>
    </div>
    

    <div id="bar_hourly"></div>
    <div id="bar_eachstore_orders"></div>
    <div id="bar_daily_orders"></div>
</div>



<?php
	} // pageContent()

}

?>