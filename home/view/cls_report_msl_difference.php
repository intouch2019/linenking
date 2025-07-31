<?php
require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once "session_check.php";

class cls_report_msl_difference extends cls_renderer {
    var $params;
    var $dtrange;
    var $storeid;
    var $currStore;
    function __construct($params=null) {
//	parent::__construct(array(UserType::Admin, UserType::CKAdmin, UserType::Manager));
        $this->params = $params;
        $this->currStore = getCurrUser();
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
        $menuitem = "reportmsldiff";
        include "sidemenu.".$this->currStore->usertype.".php";
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
                $dQuery = " createtime >= '$sdate 00:00:00' and createtime <= '$sdate 23:59:59'";
	} else if (count($dtarr) == 2) {
		list($dd,$mm,$yy) = explode("-",$dtarr[0]);
		$sdate = "$yy-$mm-$dd";
		list($dd,$mm,$yy) = explode("-",$dtarr[1]);
		$edate = "$yy-$mm-$dd";
		$dQuery = " createtime >= '$sdate 00:00:00' and createtime <= '$edate 23:59:59'";
	} else {
		$dQuery = "";
	}
        
        if ($this->storeid=="All Stores") {
            $sQuery = "";
        } else {
            $sQuery = " and store_id=$this->storeid";
        }
        
        $query = "SELECT id, store_id, store_name, min_stock_level, max_stock_level, current_stock_value, intransit_stock_value, active_order_amount, total_stock_value, difference, createtime FROM it_stores_below_msl WHERE $dQuery $sQuery ORDER BY store_id";
//        echo $query; exit();
//	$query = "select c.store_name, o.invoice_no,o.shipped_time,o.cheque_amt,o.cheque_dtl,o.remark from it_ck_pickgroup o, it_codes c where o.storeid=c.id and o.invoice_no is not null $dQuery $sQuery order by o.shipped_time desc";
	$objs = $db->fetchObjectArray($query);
        $allStores = $db->fetchObjectArray("select id,store_name from it_codes where usertype=4");
	?>
    <div class="box">
        <h2>Salesman</h2><br>
<span style="font-weight:bold;">Date Filter : </span> <input type="text" id="dateselect" name="dateselect" value="<?php echo $this->dtrange; ?>" /> (Click in the box to see date options)
<span style="font-weight:bold; margin-left:40px;">Select Store : </span><select id="storeselect" name="storeselect" style="width:180px; text-align: center;"><option >All Stores</option><?php foreach ($allStores as $store) { ?><option value="<?php echo $store->id;?>" <?php if ($this->storeid==$store->id) {?> selected="selected" <?php } ?>><?php echo $store->store_name; ?></option><?php } ?></select><br /><br />
 <div style="margin-top: 0px; margin-right: 0px; margin-bottom: 0px; margin-left: 0px; position: static; overflow:auto; ">
            <div style="display:inline-block;" class="block">
                <a href="formpost/generalexport.php?var=salesman"><button>Export To Excel (ALL FIELDS)</button></a><br/><br/>
                    <?php $table = "<table>
                        <tr>
                            <th>Sr. No</th>
                            <th>Store ID</th>
                            <th>Store Name</th>                                                       
                            <th>Min. Stock Level</th>
                            <th>Max. Stock Level</th>
                            <th>Current Stock Value</th>
                            <th>Intransit Stock Value</th>
                            <th>Active Order Amount</th>
                            <th>Total Stock Value</th>
                            <th>Difference</th>
                            <th>Date</th>
                            
                        </tr>";
                    
			$count = 0;
                        if(!empty($objs)){
			foreach ($objs as $obj) {
                            $count++;
                        $table .="<tr>
                            <td>$count</td>
                            <td>$obj->store_id</td>
                            <td>$obj->store_name</td>
                            <td>$obj->min_stock_level</td>
                            <td>$obj->max_stock_level</td>
                            <td>$obj->current_stock_value</td>
                            <td>$obj->intransit_stock_value</td>
                            <td>$obj->active_order_amount</td>
                            <td>$obj->total_stock_value</td>
                            <td>$obj->difference</td>";
                           
                            $date = date("d/m/Y", strtotime($obj->createtime));
                            
                            $table .= "<td>$date</td></tr>";
                        }
                        } else {
                            $table .="<tr>
                                <td style='color: red;'>No Negative Stock Found</td>";
                            $table .= "</tr>";
                        }
                        
                        $table .= "</table>";

                        echo $table;
                        
                        $_SESSION['salesman']=$table; 
//                        $_SESSION['accounts2']=$table2; $_SESSION['accounts3']=$table3;?>
        </div>                
    </div>
</div>
    <?php
    }
}
?>
