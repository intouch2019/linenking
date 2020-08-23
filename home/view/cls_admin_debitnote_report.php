<?php
require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once "session_check.php";

class cls_admin_debitnote_report extends cls_renderer {
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
        $menuitem = "debitnotereport";
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
                  
                    
                    $dQuery = " date(createtime) = '$sdate'";
                }
		
	} else if (count($dtarr) == 2) {
		list($dd,$mm,$yy) = explode("-",$dtarr[0]);
		$sdate = "$yy-$mm-$dd";
		list($dd,$mm,$yy) = explode("-",$dtarr[1]);
		$edate = "$yy-$mm-$dd";
		
                
                if($this->id==1)
                {
                   
                    
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
        <h2>Debit Note</h2><br>

<div style="margin-top: 0px; margin-right: 0px; margin-bottom: 0px; margin-left: 0px; position: static; overflow:auto; ">
    
    <lable id="cntype"><lable>
            
    <br/>
    <span style="font-weight:bold;">Date Filter : </span> <input type="text" id="dateselect" name="dateselect" value="<?php echo $this->dtrange; ?>" /> (Click in the box to see date options)

            
                    <?php 
                    
        if($this->id==1)
        {
            $table = "<table>
                
                        <tr>
                            <th>Sr. No</th>
                            <th>Debit Note No</th>
                              <th>Received in Inv. No.</th>    //new change in report of debitnote
                  
                            <th>Store Tally Name</th> 
                            <th>Dated</th>
                            <th>Store GSTIN</th>
                            <th>DN Taxable Value</th>
                            <th>CGST</th>
                            <th>SGST</th>
                            <th>IGST</th>
                            
                            
                            <th>Tax Total</th>
                            <th>TotalValue</th>
                            
                            
                            
                        </tr>";
          $query = "select * from it_debit_advice where $dQuery $sQuery"; 
          $orders = $db->fetchObjectArray($query);
          
          if($orders==null)
          {
              $table .='<tr>
                            <td colspan=12>No Debit Note Generated For Selected Date Range</td></tr>';
          }
        $allStores = $db->fetchObjectArray("select * from it_codes where usertype=4");
                    $currdate = date('d-m-Y',time());
                     
			$total = 0;$total2 = 0; $total3= 0;
			$count = 0;$count2 = 0; $count3= 0;
			foreach ($orders as $order) {
                           
                            
                     
                            
                            
                                
                            
				$count++;
				$total = $order->debit_amt; 
                                $tallynamequery="select tally_name,gstin_no from it_codes where id=$order->store_id";
                                $tallyobj=$db->fetchObject($tallynamequery);
                                $table .="<tr>
                                <td>$count</td>
                                <td>DN-$order->debit_no</td>
                                   <td>$order->ref_no</td> //new change in report of debitnote
                  

                                <td>$tallyobj->tally_name</td>
                               
                                ";
                        
                            $date = mmddyy($order->createtime); $table .= "<td>$date</td>"; 
                            $table .= "<td>$tallyobj->gstin_no</td>"; 
                            $table .= "<td>".round($order->total_taxable_value,2)."</td>";
                            if($order->igst_total!=0) 
                               {
                                  $table.= ""
                                   ."<td>-</td>"
                                   ."<td>-</td>"
                                    ."<td>".round($order->igst_total,2)."</td>";
                               }
                            else {
                                $table.= ""
                                   ."<td>".round($order->cgst_total,2)."</td>"
                                   ."<td>".round($order->cgst_total,2)."</td>"
                                        . "<td>-</td>";
                            } 
                            $gst=round(($order->igst_total+($order->cgst_total*2)),2);
                            $total=round(($order->total_taxable_value+$gst),2);
                            $table .= "<td style='width:10%;'>$gst</td><td style='width:10%;'>$total</td>"
//                                <td>$order->net_sale</td>"
//                                    . "<td>$order->remark</td>"
                                    . "<td><a href='formpost/genDN_storewise.php?cn_no=$order->debit_no' >Download DebitNote</a></td></tr>";
                        
                            
                           
                        }
                        $table .= "
          </table>";
                       echo $table; $_SESSION['accounts']=$table;
          
        }
	
        //print "$query";
	?>
    
        </div> 
            <div style="display:inline-block;" class="block">
                <?php 
                if($this->id==1)
                {
                    
                    ?>
                <a href="formpost/generalexportdn.php?var=accounts"><button>Export To Excel (ALL FIELDS)</button></a>
                <a href="formpost/genDebitnoteXML.php?var=<?php echo $this->dtrange; ?>"><button>Download Debitnote XML</button></a>
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
