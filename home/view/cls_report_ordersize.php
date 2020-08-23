<?php
require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once "session_check.php";

class cls_report_ordersize extends cls_renderer {
    var $params;
    var $dtrange;
    var $storeid;
    var $currUser;
    function __construct($params=null) {
	//parent::__construct(array(UserType::Admin, UserType::CKAdmin, UserType::Manager));
        $this->params = $params;
        $this->currUser = getCurrUser();
	if (isset($_SESSION['account_dtrange'])) { $this->dtrange = $_SESSION['account_dtrange']; }
	else { $this->dtrange = date("d-m-Y"); }
        
        if (isset($_SESSION['storeid'])) { $this->storeid = $_SESSION['storeid']; }
	else { $this->storeid = "All Stores"; }
    }
    function extraHeaders() {
?>
		<script type="text/javascript" src="jqueryui/js/jquery-ui-1.7.1.custom.min.js"></script>
		<script type="text/javascript" src="js/daterangepicker.jQuery.js"></script>
		<link rel="stylesheet" href="css/ui.daterangepicker.css" type="text/css" />
		<link rel="stylesheet" href="css/redmond/jquery-ui-1.7.1.custom.css" type="text/css" title="ui-theme" />
                <script type="text/javascript"> 
                </script>
<link rel="stylesheet" href="css/prettyPhoto.css" type="text/css" media="screen" title="prettyPhoto main stylesheet" charset="utf-8" />
<script src="js/prettyPhoto/jquery.prettyPhoto.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript">
    <!--//--><![CDATA[//><!--
    $(function(){
	var isOpen=false;
        $("a[rel^='prettyPhoto']").prettyPhoto({animation_speed:'fast',slideshow:3000, hideflash: true});
	$('#dateselect').daterangepicker({
	 	dateFormat: 'dd-mm-yy',
		arrows:false,
		closeOnSelect:true,
		onOpen: function() { isOpen=true; },
		onClose: function() { isOpen=false; },
		onChange: function() {
		if (isOpen) { return; }
		var dtrange = $("#dateselect").val();
		$.ajax({
			url: "savesession.php?name=account_dtrange&value="+dtrange,
			success: function(data) {
				window.location.reload();
			}
		});
		}
	});
    });

	function reload() {
		var dtrange = $("#dateselect").val();
		$.ajax({
			url: "savesession.php?name=account_dtrange&value="+dtrange,
			success: function(data) {
				window.location.reload();
			}
		});
	}
        
        $(function(){
            $("#storeselect").change(function () {
                var storeid= $('select option:selected').val();
                $.ajax({
			url: "savesession.php?name=storeid&value="+storeid,
			success: function(data) {
				window.location.reload();
			}
		});
            });
        });

</script>
    <?php
    }
    //extra-headers close

    public function pageContent() {
        $menuitem = "reportordersize";
        include "sidemenu.".$this->currUser->usertype.".php";
        ?>

<div id="orderinfo"></div>
<div class="grid_10">
	<?php
	$db = new DBConn();
	$store_id = getCurrUserId();
	$dtarr = explode(" - ", $this->dtrange);
	if (count($dtarr) == 1) {
		list($dd,$mm,$yy) = explode("-",$dtarr[0]);
		$sdate = "$yy-$mm-$dd";
		$dQuery = " and date(p.shipped_time) = '$sdate'";
	} else if (count($dtarr) == 2) {
		list($dd,$mm,$yy) = explode("-",$dtarr[0]);
		$sdate = "$yy-$mm-$dd";
		list($dd,$mm,$yy) = explode("-",$dtarr[1]);
		$edate = "$yy-$mm-$dd";
		$dQuery = " and date(p.shipped_time) >= '$sdate' and date(p.shipped_time) <= '$edate'";
	} else {
		$dQuery = "";
	}
        
        if ($this->storeid=="All Stores") {
            $sQuery = "";
        } else {
            $sQuery = " and p.storeid=$this->storeid";
        }

	$query = "select count(*) as totorders, c.store_name from it_codes c,it_ck_orders o,it_ck_pickgroup p where o.pickgroup=p.id and p.storeid=c.id $dQuery $sQuery group by c.store_name order by c.store_name";
	$orders = $db->fetchObjectArray($query);
        $query = "select sum(p.order_qty) as ordersum, sum(p.shipped_qty) as shippedsum from it_ck_pickgroup p,it_codes c where p.storeid=c.id and p.order_qty >0  $dQuery $sQuery group by c.store_name order by c.store_name";
        $orders2 = $db->fetchObjectArray($query);
        $numorders = count($orders);
        $allStores = $db->fetchObjectArray("select * from it_codes where usertype=4");
	?>
    <div class="box">
        <h2>Order Size Report</h2><br>
<span style="font-weight:bold;">Date Filter : </span> <input type="text" id="dateselect" name="dateselect" value="<?php echo $this->dtrange; ?>" /> (Click in the box to see date options)
<span style="font-weight:bold; margin-left:40px;">Select Store : </span><select id="storeselect" name="storeselect" style="width:180px;"><option >All Stores</option><?php foreach ($allStores as $store) { ?><option value="<?php echo $store->id;?>" <?php if ($this->storeid==$store->id) {?> selected="selected" <?php } ?>><?php echo $store->store_name; ?></option><?php } ?></select><br /><br />
        <div style="margin-top: 0px; margin-right: 0px; margin-bottom: 0px; margin-left: 0px; position: static; overflow:auto; ">
                    <table>
                        <tr>
                            <th>Store</th>
                            <th>No of Orders Placed</th>
                            <th>Average Placed Order Size</th>
                            <th>Average Shipped Order Size</th>
                            <th>Average Drop Percentage (%)</th>
                        </tr>
                        <?php //foreach ($orders as $order) { 
                            for ($i=0;$i<count($orders);$i++) {
                            $pct= sprintf("%0.2f",((($orders2[$i]->ordersum/$orders[$i]->totorders)-($orders2[$i]->shippedsum/$orders[$i]->totorders))/($orders2[$i]->ordersum/$orders[$i]->totorders))*100);?>
                        <tr>
                            <td><?php echo $orders[$i]->store_name; ?></td>
                            <td><?php echo $orders[$i]->totorders; ?></td>
                            <td><?php if ($orders[$i]->totorders > 0) echo intval($orders2[$i]->ordersum/$orders[$i]->totorders); else echo "0";?></td>
                            <td><?php if ($orders[$i]->totorders > 0) echo intval($orders2[$i]->shippedsum/$orders[$i]->totorders); else echo "0";?></td>
                            <td><?php echo $pct; ?></td>
                        </tr>
                         <?php } ?>
                    </table>
        </div>
    </div>
</div>
    <?php
    }
}
?>
