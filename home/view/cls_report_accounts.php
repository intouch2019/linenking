<?php
require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once "session_check.php";

class cls_report_accounts extends cls_renderer {
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
        $menuitem = "areportaccounts";
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
		$dQuery = " and date(o.shipped_time) = '$sdate'";
	} else if (count($dtarr) == 2) {
		list($dd,$mm,$yy) = explode("-",$dtarr[0]);
		$sdate = "$yy-$mm-$dd";
		list($dd,$mm,$yy) = explode("-",$dtarr[1]);
		$edate = "$yy-$mm-$dd";
		$dQuery = " and date(o.shipped_time) >= '$sdate' and date(o.shipped_time) <= '$edate'";
	} else {
		$dQuery = "";
	}
        
        if ($this->storeid=="All Stores") {
            $sQuery = "";
        } else {
            $sQuery = " and o.storeid=$this->storeid";
        }

	$query = "select c.store_name, o.invoice_no,o.shipped_time,o.cheque_amt,o.cheque_dtl,o.remark from it_ck_pickgroup o, it_codes c where o.storeid=c.id and o.invoice_no is not null $dQuery $sQuery order by o.shipped_time desc";
	$orders = $db->fetchObjectArray($query);
        $allStores = $db->fetchObjectArray("select * from it_codes where usertype=4");
	?>
    <div class="box">
        <h2>Accounts</h2><br>
<span style="font-weight:bold;">Date Filter : </span> <input type="text" id="dateselect" name="dateselect" value="<?php echo $this->dtrange; ?>" /> (Click in the box to see date options)
<span style="font-weight:bold; margin-left:40px;">Select Store : </span><select id="storeselect" name="storeselect" style="width:180px; text-align: center;"><option >All Stores</option><?php foreach ($allStores as $store) { ?><option value="<?php echo $store->id;?>" <?php if ($this->storeid==$store->id) {?> selected="selected" <?php } ?>><?php echo $store->store_name; ?></option><?php } ?></select><br /><br />
 <div style="margin-top: 0px; margin-right: 0px; margin-bottom: 0px; margin-left: 0px; position: static; overflow:auto; ">
            <div style="display:inline-block;" class="block">
                <a href="formpost/generalexport.php?var=accounts"><button>Export To Excel (ALL FIELDS)</button></a>
                <a href="formpost/generalexport.php?var=accounts3">&nbsp;&nbsp;&nbsp;&nbsp;<button>Export To Excel (BANK SLIP) : AXIS BANK</button></a>
                <a href="formpost/generalexport.php?var=accounts2">&nbsp;&nbsp;&nbsp;&nbsp;<button>Export To Excel (BANK SLIP) : OTHER BANKS</button></a></div><br/><br/>
                    <?php $table = "<table>
                        <tr>
                            <th>Sr. No</th>
                            <th>Store Name</th>                                                       
                            <th>Invoice Number</th>
                            <th>Shipped Date</th>
                            <th style='text-align:right;'>Cheque Amount</th>
                            <th>Cheque Details</th>
                            <th>Remarks</th>
                            
                        </tr>"; $currdate = date('d-m-Y',time());
                        $table2="<table>
                        <tr><th colspan='5'>Please Credit the following cheques to:</th><th>$currdate<th></tr>
                        <tr><th colspan='6'>Cottonking Pvt Ltd, A/c No: 135010200008778 - AXIS BANK LTD - BARAMATI Branch</th><tr>
                        <tr><th colspan='6'> </th></tr>                        
                        <tr>
                            <th>Sr. No</th>
                            <th>Date</th>
                            <th>Bank</th>                            
                            <th>Cheque Number</th>
                            <th style='text-align:right;'>Cheque Amount</th>
                        </tr>";
                        $table3="<table>
                        <tr><th colspan='5'>Please Credit the following cheques to:</th><th>$currdate<th></tr>
                        <tr><th colspan='6'>Cottonking Pvt Ltd, A/c No: 135010200008778 - AXIS BANK LTD - BARAMATI Branch</th><tr>
                        <tr><th colspan='6'> </th></tr>                        
                        <tr>
                            <th>Sr. No</th>
                            <th>Date</th>
                            <th>Bank</th>                            
                            <th>Cheque Number</th>
                            <th style='text-align:right;'>Cheque Amount</th>
                        </tr>";
			$total = 0;$total2 = 0; $total3= 0;
			$count = 0;$count2 = 0; $count3= 0;
			foreach ($orders as $order) {
				$count++;
				$total += $order->cheque_amt; 
                        $table .="<tr>
                            <td>$count</td>
                            <td>$order->store_name</td>
                            <td>$order->invoice_no</td>";
                            $date = mmddyy($order->shipped_time); $table .= "<td>$date</td>"; 
                            $table .= "<td>$order->cheque_amt</td>                                                       
                            <td>$order->cheque_dtl</td>";                                   
                            $table .= "<td style='width:10%;'>$order->remark</td>
                        </tr>";
                            $date2 = date("d/m/Y", strtotime($order->shipped_time));
                            $chqarr = explode(",",$order->cheque_dtl);
                           $bankarr = array(); $chq_noarr = array();
                           foreach($chqarr as $key => $value){
                               $avalue = explode("::",$value);
                               array_push($bankarr, $avalue[0]);
                               array_push($chq_noarr, $avalue[1]);
                           }
//                            $arr = explode("::",$order->cheque_dtl);
//                            $bank= $arr[0];
//                            $chq_no = $arr[1];
                           $bank = implode(",", $bankarr);
                           $chq_no = implode(",",$chq_noarr);
                            $match= array();
                            
                            if((stripos(trim($bank),"AXIS") !== false)){                          
                                $count3++;
				$total3 += $order->cheque_amt; 
                                $table3 .= "<tr><td>$count3</td><td>$currdate</td><td>$bank</td><td>$chq_no</td><td>$order->cheque_amt</td></tr>";
                            } else {
                                $count2++;
				$total2 += $order->cheque_amt; 
                                $table2 .= "<tr><td>$count2</td><td>$currdate</td><td>$bank</td><td>$chq_no</td><td>$order->cheque_dtl</td><td>$order->cheque_amt</td></tr>";
                            }
                        }
                        $table .= "<tr style='font-weight:bold;'>
                            <td>TOTAL</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td style='text-align:right;'>$total</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>                                                       
                        </tr>
                    </table>";
                        $table2 .= "<tr style='font-weight:bold;'>
                            <td>TOTAL</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td style='text-align:right;'>$total2</td>
                        </tr></table>";
                        $table3 .= "<tr style='font-weight:bold;'>
                            <td>TOTAL</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td style='text-align:right;'>$total3</td>
                        </tr></table>";echo $table; $_SESSION['accounts']=$table; $_SESSION['accounts2']=$table2; $_SESSION['accounts3']=$table3;?>
        </div>                
    </div>
</div>
    <?php
    }
}
?>
