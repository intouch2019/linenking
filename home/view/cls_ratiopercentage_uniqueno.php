<?php
require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once ("lib/core/Constants.php");
require_once "lib/core/strutil.php";
require_once "session_check.php";

class cls_ratiopercentage_uniqueno extends cls_renderer {
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
<script type="text/javascript" src="js/custom.js"></script>
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


function genExcelRatioRep()
 {

      var ret = dateRange2();
      if(ret==1)
          return;
   
   var store_ids = $("#storeselect").val();
   var dtrange = $("#dateselect").val();
    
 window.location.href="formpost/genRatioPercentageExcel.php?storeid="+store_ids+"&dtrange="+dtrange;
    
}
</script>
    <?php
    }
    //extra-headers close

    public function pageContent() {
        $menuitem = "reportratiopercentage";
        include "sidemenu.".$this->currUser->usertype.".php";
        ?>


<div class="grid_10">
	<?php
	$db = new DBConn();
	$store_id = getCurrUserId();
	$dtarr = explode(" - ", $this->dtrange);
	if (count($dtarr) == 1) {
		list($dd,$mm,$yy) = explode("-",$dtarr[0]);
		$sdate = "$yy-$mm-$dd";
//		$dQuery = " and co.active_time like '%$sdate%'";
                $dQuery = " and o.bill_datetime >= '$sdate 00:00:00' and o.bill_datetime <= '$sdate 23:59:59' ";
                //$dQuery = " and co.active_time >= '$sdate 00:00:00' and co.active_time <= '$sdate 23:59:59'";
	} else if (count($dtarr) == 2) {
		list($dd,$mm,$yy) = explode("-",$dtarr[0]);
		$sdate = "$yy-$mm-$dd";
		list($dd,$mm,$yy) = explode("-",$dtarr[1]);
		$edate = "$yy-$mm-$dd";
//		$dQuery = " and date(co.active_time) >= '$sdate%' and date(co.active_time) <= '$edate%'";
               // $dQuery = " and co.active_time >= '$sdate 00:00:00' and co.active_time <= '$edate 23:59:59'";
                $dQuery = " and o.bill_datetime >= '$sdate 00:00:00' and o.bill_datetime <= '$edate 23:59:59' ";
	} else {
		$dQuery = "";
	}
        
        if ($this->storeid=="All Stores") {
            $sQuery = "";
        } else {
            $sQuery = " and o.store_id=$this->storeid";
        }
       
        
//        $query = "select sum(p.order_qty) as ordersum, sum(p.shipped_qty) as shippedsum from it_ck_pickgroup p,it_codes c where p.storeid=c.id and p.order_qty >0  $dQuery $sQuery group by c.store_name order by c.store_name";
//        $orders2 = $db->fetchObjectArray($query);
//        $numorders = count($orders);
        $allStores = $db->fetchObjectArray("select id,store_name from it_codes where usertype=4");
	?>
    <div class="box">
        <h2>Customer data Percentage and Duplicate no entry Report</h2><br>
         <form action="" method="POST">
<span style="font-weight:bold;">Date Filter : </span> <input type="text" id="dateselect" name="dateselect" value="<?php echo $this->dtrange; ?>" /> (Click in the box to see date options)
<span style="font-weight:bold; margin-left:40px;">Select Store : </span><select id="storeselect" name="storeselect" style="width:180px;"><option >All Stores</option><?php foreach ($allStores as $store) { ?><option value="<?php echo $store->id;?>" <?php if ($this->storeid==$store->id) {?> selected="selected" <?php } ?>><?php echo $store->store_name; ?></option><?php } ?></select><br /><br />
<input type="button" name="genexcel" value="Export to Excel" onclick="javascript:genExcelRatioRep();">
<br>
</form>

    </div>
</div>
    <?php
    }
}

?>
