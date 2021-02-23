<?php
require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once "session_check.php";

class cls_report_creditnotetd extends cls_renderer {
    var $params;
    var $dtrange;
    var $id;
    var $currStore;
    function __construct($params=null) {
//	parent::__construct(array(UserType::Admin, UserType::CKAdmin, UserType::Manager));
        $this->params = $params;
        $this->currStore = getCurrUser();
	if (isset($_SESSION['account_dtrange'])) { $this->dtrange = $_SESSION['account_dtrange']; }
	else { $this->dtrange = date("d-m-Y"); }
        
        if (isset($_SESSION['id'])) { $this->id = $_SESSION['id']; }
	else { $this->id = "1"; }
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
                //var id = $("#cn").val();
                //alert(id);
		$.ajax({
			url: "savesession.php?name=account_dtrange&value="+dtrange,
			success: function(data) {
				window.location.reload();
			}
		});
	}
        
        $(function(){
            $("#cn").change(function () {
                var id= $("#cn").val();
                //alert();
                
                $.ajax({
			url: "savesession.php?name=id&value="+id,
			success: function(data) {
				window.location.reload();
                                
			}
		});
               
            });
            $("#cn1").change(function () {
                var id= $("#cn1").val();
                
                $.ajax({
			url: "savesession.php?name=id&value="+id,
			success: function(data) {
                                //alert(data);
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
        $menuitem = "reportcreditnotetd";
        include "sidemenu.".$this->currStore->usertype.".php";
        ?>


<div class="grid_10">
	<?php
	$db = new DBConn();
	$store_id = getCurrUserId();
	$dtarr = explode(" - ", $this->dtrange);
	if (count($dtarr) == 1) {
		list($dd,$mm,$yy) = explode("-",$dtarr[0]);
		$sdate = "$yy-$mm-$dd";
                if($this->id==1)
                {
                    $dQuery = " date(from_datetime) = '$sdate'";
                }
                else {
                    
                    $dQuery = " date(createtime) = '$sdate'";
                }
		
	} else if (count($dtarr) == 2) {
		list($dd,$mm,$yy) = explode("-",$dtarr[0]);
		$sdate = "$yy-$mm-$dd";
		list($dd,$mm,$yy) = explode("-",$dtarr[1]);
		$edate = "$yy-$mm-$dd";
		
                
                if($this->id==1)
                {
                    $dQuery = " date(from_datetime) >= '$sdate' and date(to_datetime) <= '$edate'";
                }
                else {
                    
                    $dQuery = " date(createtime) >= '$sdate' and date(createtime) <= '$edate'";
                    
                }
                
	} else {
		$dQuery = "";
	}
        //print "$this->id";
        //print "$dQuery";
       $sQuery ="";
//        if ($this->storeid=="All Stores") {
//            $sQuery = "";
//        } else {
//            $sQuery = " and o.storeid=$this->storeid";
//        }
        
	?>
    <div class="box">
        <h2>Credit Note</h2><br>

<div style="margin-top: 0px; margin-right: 0px; margin-bottom: 0px; margin-left: 0px; position: static; overflow:auto; ">
    <input type="radio" id="cn" name="cn" value="1" <?php if ($this->id == 1) { ?>checked <?php } ?> onchange="reload()">Turn Over Discount
    <input type="radio" id="cn1" name="cn" value="2" <?php if ($this->id == 2) { ?>checked <?php } ?> onchange="reload()">Discount Scheme
    <lable id="cntype"><lable>
            
    <br/>
    <span style="font-weight:bold;">Date Filter : </span> <input type="text" id="dateselect" name="dateselect" value="<?php echo $this->dtrange; ?>" /> (Click in the box to see date options)

            
                    <?php 
                    
        if($this->id==1)
        {
            $table = "<table>
                <tr><th colspan=13> Turnover Discount CreditNote</th></tr>
                        <tr>
                            <th>Sr. No</th>
                            <th>CreditNote. No</th>
                            <th>Store Tally Name</th>                                                       
                            <th>Refrence Number</th>
                            <th>HSNCODE</th>
                            <th>Dated</th>
                            <th>GST TurnOver Discount  Net 12%</th>
                            <th>IGST TurnOver Discount Paid 12%</th>
                            <th>SGST TurnOver Discount Paid 6%</th>
                            <th>CGST TurnOver Discount Paid 6%</th>
                            <th>GST TurnOver Discount Total</th>
                            <th>Total Sale</th>
                            <th>Remark</th>
                            
                            
                        </tr>";
          $query = "select * from it_creditnote_td where $dQuery $sQuery"; 
          $orders = $db->fetchObjectArray($query);
        $allStores = $db->fetchObjectArray("select * from it_codes where usertype=4");
                    $currdate = date('d-m-Y',time());
                     
			$total = 0;$total2 = 0; $total3= 0;
			$count = 0;$count2 = 0; $count3= 0;
			foreach ($orders as $order) {
                            $strhsn="-";
                            $invoice_id_query="select id from it_invoices where invoice_no=$order->ref_no";
                            $invobj=$db->fetchObject($invoice_id_query);
                            if(isset($invobj)){
                            
                            $hsnquery="select item_code from it_invoice_items where invoice_id=$invobj->id order by id desc limit 1";
                            $hsnobj=$db->fetchObjectArray($hsnquery);
                            //print_r($hsnobj);
                            if(isset($hsnobj)){
                            
                            $strhsn="(";
                            $cnt=1;
                            foreach($hsnobj as $hsnbb)
                            {
                                if($cnt==1)
                                {
                                    $strhsn.="$hsnbb->item_code";
                                }
                                else {
                                       $strhsn.=",$hsnbb->item_code";
                                 }
                                $cnt=0;
                            }
                            $strhsn.=")";
                            //print "$strhsn";
                            }
                            $finalQuery="select c.it_hsncode from it_items i left outer join it_categories c on i.ctg_id = c.id 
                                         where i.barcode in$strhsn group by c.it_hsncode";
                            //print "$finalQuery";
                            $hsnobj=$db->fetchObjectArray($finalQuery);
                            //print_r($hsnobj);
                            if(isset($hsnobj) && $hsnobj!=null){
                            
                                $strhsn="";
                                    $cnt=1;
                                    foreach($hsnobj as $hsnbb)
                                        {
                                            if($cnt==1)
                                                {
                                                    $strhsn.="$hsnbb->it_hsncode";
                                                }
                                                else {
                                                        $strhsn.=",$hsnbb->it_hsncode";
                                                    }
                                            $cnt=0;
                                        }
                                            $strhsn.="";
                            }
                            else
                            {
                                $strhsn="-";
                            }
                     }
                            
                            
                                
                            
				$count++;
				$total = $order->gst_total; 
                                $tallynamequery="select tally_name from it_codes where id=$order->store_id";
                                $tallyobj=$db->fetchObject($tallynamequery);
                                $table .="<tr>
                                <td>$count</td>
                                <td>CN-$order->cn_no</td>
                                    
                                <td>$tallyobj->tally_name</td>
                                <td>$order->ref_no</td>
                                <td>$strhsn</td>";
                        
//                            $date = mmddyy($order->to_datetime);
//                            $date = mmddyy($order->createtime);
                                  $date = mmddyy($order->createtime); 
                            $date2=explode(" ",$date);
                            $table .= "<td>$date2[0]</td>"; 
                            $table .= "<td>$date</td>"; 
                            $table .= "<td>$order->gst_net</td>";                                                      
                            if($order->igst_paid!=0) 
                               {
                                  $table.= "<td>$order->igst_paid</td>"
                                   ."<td>-</td>"
                                   ."<td>-</td>";
                               }
                            else {
                                $table.= "<td>-</td>"
                                   ."<td>$order->cgst_paid</td>"
                                   ."<td>$order->cgst_paid</td>";
                            } 
                            //gst=$order->igst_paid+($order->cgst_paid*2);
                            //$total=$total+$gst;
                            $table .= "<td style='width:10%;'>$total</td>
                                <td>$order->net_sale</td>"
                                    . "<td>$order->remark</td>"
                                    . "<td><a href='formpost/genCN_storewise.php?cn_no=$order->cn_no' >Download CreditNote</a></td></tr>";
                        
                            
                           
                        }
                        $table .= "
          </table>";
                       echo $table; $_SESSION['accounts']=$table;
          
        }
	else
        {
           $table = "<table>
                <tr><th colspan=13 align=\"center\"> Discount Scheme CreditNote</th></tr>
                        <tr>
                            <th>Sr. No</th>
                            <th>CreditNote. No</th>
                            <th>Store Tally Name</th>                                                       
                            <th>Refrence Number</th>
                            <th>HSNCODE</th>
                            <th>Dated</th>
                            <th>GST Discount  Scheme Net</th>
                            <th>IGST Discount  Scheme Paid</th>
                            <th>SGST Discount  Scheme Paid</th>
                            <th>CGST Discount  Scheme Paid</th>
                            <th>GST  Discount  Scheme Total</th>
                            <th>Remark</th>    
                        </tr>"; 
           $query = "select * from it_creditnote_ds where $dQuery $sQuery";
           //print "$query";
           $orders = $db->fetchObjectArray($query);
           //print_r($orders);
           $allStores = $db->fetchObjectArray("select * from it_codes where usertype=4");
                    $currdate = date('d-m-Y',time());
                     
			$total = 0;$total2 = 0; $total3= 0;
			$count = 0;$count2 = 0; $count3= 0;
			foreach ($orders as $order) {
                            
                            
                                
                            
				$count++;
				$total = $order->taxable_amt; 
                                $tallynamequery="select tally_name from it_codes where id=$order->store_id";
                                $tallyobj=$db->fetchObject($tallynamequery);
                                $table .="<tr>
                                <td>$count</td>
                                <td>CN-$order->cn_no</td>
                                    
                                <td>$order->tally_name</td>
                                <td>$order->ref_no</td>";
                                
                                if(isset($order->hsncode))
                                {
                                    $table .="<td>$order->hsncode</td>";
                                }
                                else
                                {
                                    $table .="<td>-</td>";
                                }
                                
                        
                            $date = mmddyy($order->createtime); $table .= "<td>$date</td>"; 
                            $table .= "<td>$total<br/>(@$order->taxpct %)</td>";                                                      
                            if($order->igst_paid!=0) 
                               {
                                  $table.= "<td>$order->igst_paid <br/>(@$order->taxpct %)</td>"
                                   ."<td>-</td>"
                                   ."<td>-</td>";
                               }
                            else {
                                $table.= "<td>-</td>"
                                   ."<td>$order->cgst_paid <br/>(@$order->taxpct %)</td>"
                                   ."<td>$order->cgst_paid <br/>(@$order->taxpct %)</td>";
                            } 
                            $gst=$order->igst_paid+($order->cgst_paid*2);
                            $total=$total+$gst;
                            $table .= "<td style='width:10%;'>$total</td>
                                <td>$order->ds_remark</td>"
                                    . "<td><a href='formpost/genCN_storewiseDS.php?cn_no=$order->cn_no' >Download DSCreditNote</a></td></tr>";
                        
                            ///var/www/limelight_new/home/genCN_storewiseDS.php
                            
                           
                        }
                        $table .= "
          </table>";
                       echo $table; 
                       $_SESSION['accounts']=$table;
                       $_SESSION['dtrng']=$this->dtrange;
        }
        //print "$query";
	?>
    
        </div> 
            <div style="display:inline-block;" class="block">
                <?php 
                if($this->id==1)
                {
                    
                    ?>
                <a href="formpost/generalexportcn.php?var=accounts"><button>Export To Excel (ALL FIELDS)</button></a>
                <a href="formpost/genCreditnoteTDXML.php?var=<?php echo $this->dtrange; ?>"><button>Download TD Creditnote XML</button></a>
                <?php
                }
                else {
                   ?>
                <a href="formpost/generalexportcn1.php?var=accounts"><button>Export To Excel (ALL FIELDS)</button></a>
                <a href="formpost/genCreditnoteDSXML.php?var=<?php echo $this->dtrange; ?>"><button>Download DS Creditnote XML</button></a>
                <?php  
                }
                ?>
                
            </div><br/><br/>
    </div>
    
</div>
    <?php
    }
}
?>
