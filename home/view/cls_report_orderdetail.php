<?php
require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once ("lib/core/Constants.php");
require_once "lib/core/strutil.php";
require_once "session_check.php";

class cls_report_orderdetail extends cls_renderer {
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
        $menuitem = "reportorderdetail";
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
		$dQuery = " and co.active_time like '%$sdate%'";
	} else if (count($dtarr) == 2) {
		list($dd,$mm,$yy) = explode("-",$dtarr[0]);
		$sdate = "$yy-$mm-$dd";
		list($dd,$mm,$yy) = explode("-",$dtarr[1]);
		$edate = "$yy-$mm-$dd";
		$dQuery = " and date(co.active_time) >= '$sdate%' and date(co.active_time) <= '$edate%'";
	} else {
		$dQuery = "";
	}
        
        if ($this->storeid=="All Stores") {
            $sQuery = "";
        } else {
            $sQuery = " and co.store_id=$this->storeid";
        }
       
        
//        $query = "select sum(p.order_qty) as ordersum, sum(p.shipped_qty) as shippedsum from it_ck_pickgroup p,it_codes c where p.storeid=c.id and p.order_qty >0  $dQuery $sQuery group by c.store_name order by c.store_name";
//        $orders2 = $db->fetchObjectArray($query);
//        $numorders = count($orders);
        $allStores = $db->fetchObjectArray("select * from it_codes where usertype=4");
	?>
    <div class="box">
        <h2>Order Detail Report</h2><br>
        <form action="" method="POST">
<span style="font-weight:bold;">Date Filter : </span> <input type="text" id="dateselect" name="dateselect" value="<?php echo $this->dtrange; ?>" /> (Click in the box to see date options)
<span style="font-weight:bold; margin-left:40px;">Select Store : </span><select id="storeselect" name="storeselect" style="width:180px;"><option >All Stores</option><?php foreach ($allStores as $store) { ?><option value="<?php echo $store->id;?>" <?php if ($this->storeid==$store->id) {?> selected="selected" <?php } ?>><?php echo $store->store_name; ?></option><?php } ?></select><br /><br />
<b>Design No:</b><input type="text" name="design_no" placeholder="Design No"style="width:130px;height:23px;font-size:14px;" value="<?php echo $this->getFieldValue('design_no'); ?>" required=""/>
<input type="submit" value="submit" name="submit" />
</form>
    <?php 
     //$errors=array();
    if(empty($_POST['design_no'])){
        //print_r("here".$_POST['design_no']);
        echo '<span style="color:#F00;text-align:center;"><b>Please Enter Design No</b></span>';
    }
    if(!empty($_POST['design_no'])){
        $form_value=$_POST['design_no'];
        
        $query = "select c.store_name,co.status,co.order_no,oi.design_no,oi.mrp,oi.order_qty,co.active_time as time from it_ck_orders co,it_ck_orderitems oi,it_codes c where  c.id=co.store_id and co.id=oi.order_id and oi.design_no='$form_value' $sQuery $dQuery order by time desc";
     	$orders = $db->fetchObjectArray($query);
        $numorders = count($orders);
        ?>
        <div style="margin-top: 0px; margin-right: 0px; margin-bottom: 0px; margin-left: 0px; position: static; overflow:auto; ">
                    <table>
                        <tr>
                            <th>Store Name</th>
                            <th>STATUS</th>
                            <th>ORDER NO</th>
                            <th>DESIGN NO</th>
                            <th>MRP</th>
                            <th>ORDER QTY</th>
                            <th>TIME</th>
                            
                        </tr>
                        <?php //foreach ($orders as $order) { 
                             $name=new OrderStatus();
                            for ($i=0;$i<count($orders);$i++) {
                           // $pct= sprintf("%0.2f",((($orders2[$i]->ordersum/$orders[$i]->totorders)-($orders2[$i]->shippedsum/$orders[$i]->totorders))/($orders2[$i]->ordersum/$orders[$i]->totorders))*100);?>
                        <tr>
                            <td><?php echo $orders[$i]->store_name; ?></td>
                            <td><?php echo $name->getName($orders[$i]->status); ?></td>
                            <td><?php echo $orders[$i]->order_no;?></td>
                            <td><?php echo $orders[$i]->design_no ;?></td>
                            <td><?php echo $orders[$i]->mrp; ?></td>
                            <td><?php echo $orders[$i]->order_qty; ?></td>
                            <td><?php echo $orders[$i]->time; ?></td>
                        </tr>
                         <?php }  } ?>
                    </table>
        </div>
    </div>
</div>
    <?php
    }
}

?>
