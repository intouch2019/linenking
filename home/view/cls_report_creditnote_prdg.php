<?php
require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once "session_check.php";



class cls_report_creditnote_prdg extends cls_renderer {
    var $params;
    var $dtrange;
    var $id;
    var $currStore;
    var $storeid;
    function __construct($params=null) {
//	parent::__construct(array(UserType::Admin, UserType::CKAdmin, UserType::Manager));//
        $this->params = $params;
        $this->currStore = getCurrUser();
	if (isset($_SESSION['account_dtrange'])) { $this->dtrange = $_SESSION['account_dtrange']; }
	else { $this->dtrange = date("d-m-Y"); }
        
        if (isset($_SESSION['id'])) { $this->id = $_SESSION['id']; }
	else { $this->id = "1"; }
    
      if (isset($_SESSION['storeid'])) { $this->storeid = $_SESSION['storeid']; }
	else { $this->storeid = "-1"; }
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
    <!--//--><![CDATA[//>
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


function selectstore()
{
var storeid = $("#store").val();
//alert(storeid);

$.ajax({
			url: "savesession.php?name=storeid&value="+storeid,
			success: function(data) {
				window.location.reload();
			}
		});
        }
	function reload() { 
		var dtrange = $("#dateselect").val();
                //var id = $("#cn").val();
//                alert("id");
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
        $menuitem = "defectgarment";
        include "sidemenu.".$this->currStore->usertype.".php";
        $formResult = $this->getFormResult();
        $write_htm = true;
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
                           $newfname1 = "DefectiveGarmentCN_Reports_".$sdate.".csv";
                    $dQuery = " date(createtime) = '$sdate'";
                }else if($this->id==2){
                    $newfname = "PurchaseReturnCN_Reports_".$sdate.".csv";  
                   
                   $dQuery = "and i.invoice_dt >= '$sdate 00:00:00' and i.invoice_dt <= '$sdate 23:59:59' ";
               }
                	
	} else if (count($dtarr) == 2) {
		list($dd,$mm,$yy) = explode("-",$dtarr[0]);
		$sdate = "$yy-$mm-$dd";
		list($dd,$mm,$yy) = explode("-",$dtarr[1]);
		$edate = "$yy-$mm-$dd";
		
                
                if($this->id==1)
                {
                   
                     $newfname1 = "DefectiveGarmentCN_Reports_".$sdate."_".$edate.".csv"; 
                    $dQuery = " date(createtime) >= '$sdate' and date(createtime) <= '$edate'";
                    
                }else if($this->id==2){
                    $newfname = "PurchaseReturnCN_Reports_".$sdate."_".$edate.".csv";  
                    
                     $dQuery = " and i.invoice_dt >= '$sdate 00:00:00' and i.invoice_dt <= '$edate 23:59:59' ";
               }
                
	} else {
		$dQuery = "";
	}
        //print "$this->id";
        //print "$dQuery";
       $sQuery ="";
        if ($this->storeid=="-1") {
             if($this->id==2){
            $storeClause = " c.usertype = ".UserType::Dealer ;
             }else{
             $sQuery = "";}
        } else {
               if($this->id==1)
                {
            $sQuery = " and store_id=$this->storeid";
                }else   if($this->id==2)
                {
                  $storeClause = " i.store_id in ( $this->storeid ) "; 
                }
        }
        
//        echo $sQuery."</br>".$this->storeid;
	?>
    <div class="box">
        <h2>Credit Note</h2><br>

<div style="margin-top: 0px; margin-right: 0px; margin-bottom: 0px; margin-left: 0px; position: static; overflow:auto; ">
    <div class="grid_12">
                <?php if($this->currStore->usertype != UserType::Dealer ){ ?>    
		<div class="grid_4">
                    <b>Select Store*:</b><br/>
        	<select name="store" id="store" data-placeholder="Choose Store" class="chzn-select" onchange="selectstore()" style="width:100%;">
                <?php if( $this->storeid == -1 ){
                                   $defaultSel = "selected";
                             }else{ $defaultSel = ""; } ?>
                <option value="-1" <?php echo $defaultSel;?>>All Stores</option> 
<?php
$objs = $db->fetchObjectArray("select * from it_codes where usertype=4 order by store_name");
foreach ($objs as $obj) {        
	$selected="";
//	if (isset($this->storeidreport) && $obj->id==$this->storeidreport) { $selected = "selected"; }
        if ($this->storeid != -1){
                if($obj->id==$this->storeid) 
                { $selected = "selected"; }
            
        }
?>
          <option value="<?php echo $obj->id; ?>" <?php echo $selected; ?>><?php echo $obj->store_name; ?></option> 
<?php } ?>
		</select>
		</div>
        <?php } ?>
        </br>
        <span style="font-weight:bold;">Date Filter : </span> <input type="text" id="dateselect" name="dateselect" value="<?php echo $this->dtrange; ?>" /> (Click in the box to see date options)
    

        <br><br>
        <input type="radio" id="cn" name="cn"   value="1"  <?php if ($this->id == 1) { ?>checked <?php } ?> onchange="reload()" ><b>DG Credit Note</b>
        <input type="radio" id="cn1" name="cn" value="2"   <?php if ($this->id == 2) { ?>checked <?php } ?>  onchange="reload()"><b>Purchase Return Credit Note</b>
    <br><br>
            
    
    
            
                    <?php 
                    
        if($this->id==1)
        {
            $table = "<table>
                
                        <tr>
                            <th>Sr. No</th>
                            <th>CreditNote No.</th>
                            
                            <th>Irn No.</th>
                            <th>Ack No.</th>
                            <th>Ack Date</th>
 
                  
                            <th>Store Tally Name</th> 
                            <th>Dated</th>
                           <th>MRP Total</th>
                           <th>Qty</th>
                            <th>CN Taxable Value</th> 
                            
                            
                            <th>Tax Total</th>
                            <th>Total Invoice Value</th>
                             <th>Created By</th>
                              <th>Approve By</th>
                               <th>Status</th>
                            <th>Action</th>
                            
                        </tr>";
          $query = "select * from it_portalinv_creditnote where $dQuery $sQuery"; 
        
//         print "$query";
          $orders1 = $db->fetchObjectArray($query);
          
          if($orders1==null)
          {
              $table .='<tr>
                            <td colspan=12>No Defective Garment Credit Note available For Selected Date Range</td></tr>';
          }
        $allStores = $db->fetchObjectArray("select * from it_codes where usertype=4");
                    $currdate = date('d-m-Y',time());
                     
			$total = 0;$total2 = 0; $total3= 0;
			$count = 0;$count2 = 0; $count3= 0;
                        $created_by="";
                        $approve_by="";
                        $status="";
			foreach ($orders1 as $order) {
                           
                            
                     
                            
                            
                                
                            
				$count++;
				$total = $order->total_mrp; 
                                $tallynamequery="select tally_name,gstin_no from it_codes where id=$order->store_id";
                                $tallyobj=$db->fetchObject($tallynamequery);
                                
                                
                                
                                $query123 = "select irn,AckNo,AckDate from it_irncreditnote where CreditNote_ID=$order->id";//new change
                               $q123=$db->fetchObject($query123);
                               
                               $irn="";
                               $AckNo="";
                               $Ackdate="";
                               if(isset($q123))
                               {
                                 $irn=$q123->irn;
                               $AckNo=$q123->AckNo;
                               $Ackdate=$q123->AckDate;   
                                   
                               }
                               
                                
                                $table .="<tr>
                                <td>$count</td>
                                <td>$order->invoice_no</td>
                                    
                                <td>$irn</td>
                                <td>$AckNo</td>
                                <td>$Ackdate</td>
                                    
                                
                               
                  
                                <td>$tallyobj->tally_name</td>
                               
                                ";
                        
                            $date = mmddyy($order->approve_dt); 
                            $table .= "<td>$date</td>"; 
                            $table .= "<td>$order->total_mrp</td>";
                            $table .= "<td>$order->invoice_qty</td>";
                            $table .= "<td>".round($order->total_taxable_value,2)."</td>";
//                            if($order->igst_total!=0) 
//                               {
//                                  $table.= ""
//                                   ."<td>-</td>"
//                                   ."<td>-</td>"
//                                    ."<td>".round($order->igst_total,2)."</td>";
//                               }
//                            else {
//                                $table.= ""
//                                   ."<td>".round($order->cgst_total,2)."</td>"
//                                   ."<td>".round($order->cgst_total,2)."</td>"
//                                        . "<td>-</td>";
//                            } 
                            $gst=round(($order->igst_total+($order->cgst_total*2)),2);
                            $total=round(($order->total_taxable_value+$gst),2);
                            
                               $createdby="select store_name from it_codes where id=$order->created_by";
                               $createdby1=$db->fetchObject($createdby);
                               if(isset($created_by)){
                               $created_by=$createdby1->store_name;
                               }
                            
                               $approveby="select store_name from it_codes where id=$order->approve_by ";//and is_approved=1
//                               echo $approveby;                               exit();
                               $approve_by1=$db->fetchObject($approveby);
                               if(isset($approve_by1)){
                               $approve_by=$approve_by1->store_name;
                               }
                             
                            
//                              $status
                               if($order->is_approved==0){
                                   $status='Not Approved';
                               }
                               else{
                                   $status='Approved';
                               }
                            
                           $action="-";
                           if($order->is_approved==1){
                           $action="<a href='formpost/generateDGpdfs.php?invid=$order->id' target=_blank >Download</a>";}
                           
                            $table .= "<td style='width:10%;'>$gst</td><td style='width:10%;'>$total</td>"
                              ." <td>$created_by</td>"
                                   . "<td>$approve_by</td>"
                                    .  "<td>$status</td>"
                                    . "<td>$action</td></tr>";
                        
                            
                           
                        }
                        
                        $table .= "
          </table>";
                       echo $table; $_SESSION['accounts']=$table;
          
        }
        
                   
        if($this->id==2)
        {
            $table = "<table>
                
                        <tr>
                            <th>Invoice Id</th>
                            <th>Store Name</th>
                            <th>CreditNote No</th>
                            
                            
                            <th>Irn No</th>
                            <th>Ack No</th>
                            <th>Ack Date</th>
                            
                             <th>MRP Total</th>
                             <th>Invoice Amount</th>
                             <th>Quantity</th>
                             <th>Date</th>
 

                  
                            
                            
                        </tr>";
          $query = "select i.id as ids,c.store_name as stores,i.invoice_no as invno,irn_no as irn,ack_no as ack_no,ack_date as ack_date, i.total_mrp as mrp,i.invoice_amt as invamt,i.invoice_qty as quantity, i.invoice_dt as date from it_codes c,it_invoices_creditnote i"; 
          $query .=" where $storeClause $dQuery and c.id=i.store_id and i.invoice_type in(5) and i.createtime > '2019-04-01 00:00:00' group by i.id,i.store_name,i.invoice_no,i.invoice_qty,i.invoice_dt";
//         print "$query </br>";
//         exit();
        
          $orders = $db->fetchObjectArray($query);
          
          if($orders==null)
          {
              $iscomposite=0;
//              echo $this->storeid."</br>";
              if ($this->storeid !="-1") {
                $qcmpst="select composite_billing_opted from it_codes where id=$this->storeid";
//                echo $qcmpst;
                               $cmp_obj=$db->fetchObject($qcmpst);
                               if(isset($cmp_obj)){
                              $iscomposite= $cmp_obj->composite_billing_opted;
                               }  
                  
              }
              if($iscomposite ==0 && $this->storeid !="-1"){
                         $table .='<tr>
                            <td colspan=12>This is Non-Compsite Store having no Purchase Return Credit Note</td></tr>';
              }else{
              $table .='<tr>
                            <td colspan=12>No Purchase Return Credit Note available for selected Date Range</td></tr>';
              }
          }
          foreach ($orders as $order) {
              $table .="<tr><td>".$order->ids."</td>";
              $table .="<td>".$order->stores."</td>";
              $table .="<td>".$order->invno."</td>";
              $table .="<td>".$order->irn."</td>";
              $table .="<td>".$order->ack_no."</td>";
              $table .="<td>".$order->ack_date."</td>";
              $table .="<td>".$order->mrp."</td>";
              $table .="<td>".$order->invamt."</td>";
              $table .="<td>".$order->quantity."</td>";
              $table .="<td>".$order->date."</td></tr>";
              
          }
          
          
                        
                        $table .= "
          </table>";
                       echo $table; $_SESSION['accounts']=$table;
          
        }
	
        //print "$query";
	?>
    
        </div> 
     </div>
    
    <?php 
    if($this->id==2)
    {
      
    
    if (isset($orders)) {
       
           
        $fp = fopen('tmp/StorePurchaseReturn.csv', 'w');
           
        if($write_htm){
         $fp2 = fopen ('tmp/StorePurchaseReturn.htm', 'w');
        } 
        if ($fp) {
            $trow = array(); $tcell = array(); 
            //write header info   
            if($write_htm){
             fwrite($fp2,"<table width='100%' style='overflow:auto;'><thead><tr>");
            } 
            
            $tableheaders="Invoice Id:Store Name:CreditNote No:Irn No:Ack No:Ack Date:Total MRP:Invoice Ammount:Quantity:Date";
        
            $headerarr = explode(":", $tableheaders); 
            foreach ($headerarr as $harr) {
                if ($harr != "") {
                    $tcell[] .= $harr;
                    if($write_htm){
                     fwrite($fp2,"<th>$harr</th>");
                    } 
                    } 
                }
                
                
            
            fputcsv($fp, $tcell,',',chr(0));
            if($write_htm){
              fwrite($fp2,"</tr></thead><tbody>");
            }  
            //write body
            foreach ($orders as $order) {
                $tcell = null; 
               if($write_htm){ 
                fwrite($fp2,"<tr>");
               } 
                foreach ($order as $field => $value) {
                    
                    
                    
                   if ($field=="tax") {
                       $value = sprintf('%.2f',$value);
                   } else if($field == "date"){                                              
                       $t_str = ddmmyy2($value);
                       $value = $t_str;
                   }
                   
                   
                   
                   $tcell[] .= trim($value);
                   if($write_htm){
                    fwrite($fp2,"<td>".trim($value)."</td>");
                   }
                  
                }
                fputcsv($fp, $tcell,',',chr(0));
                if($write_htm){
                fwrite($fp2,"</tr>");
                }
                
            } 
//            if($this->gen==1){
//                $totTotalValue=$totAmt;
        //    }
            if($write_htm){
               // fwrite($fp2,"<tr><td><b></b></td><td><b></b></td></tr>");
                fwrite($fp2,"");
                fwrite ($fp2,"</tbody></table>");
                fclose ($fp2); 
            }
            fclose ($fp); 
            if($write_htm){
                $table = file_get_contents("tmp/StorePurchaseReturn.htm");
//                echo $table;
            }
        } else {
            echo "<br/>Unable to create file. Contact Intouch.";
        }
    }
    ?>
        <div id="dwnloadbtn" style='margin-left:15px;  height:24px;width:130px;border: solid gray 1px;background:#F5F5F5;padding-top:4px;'>
    <a href='<?php echo "tmp/store_purchasereturn.php?output=$newfname" ;?>' title='Export table to CSV'><img src="images/excel.png" width='20' hspace='3' style='margin-bottom:-6px;' /> Export To Excel</a>
        </div>  
    <?php }?>        
           
    
    
    
    <?php 
    if($this->id==1)
    {
      
    
    if (isset($orders1)) {
       
           
        $fp = fopen('tmp1/DgCreditNote.csv', 'w');
           
        if($write_htm){
         $fp2 = fopen ('tmp1/DgCreditNote.htm', 'w');
        } 
        if ($fp) {
            $trow = array(); $tcell = array(); 
            //write header info   
            if($write_htm){
             fwrite($fp2,"<table width='100%' style='overflow:auto;'><thead><tr>");
            } 
            
            $tableheaders="Sr No:CreditNote No:Irn No:Ack No:Ack Date:Store Tally Name:Dated:MRP Total:Qty:CN Taxable Value:Tax Total:Total Invoice Value:Created By:Approve By:Status";
        
            $headerarr = explode(":", $tableheaders); 
            foreach ($headerarr as $harr) {
                if ($harr != "") {
                    $tcell[] .= $harr;
                    if($write_htm){
                     fwrite($fp2,"<th>$harr</th>");
                    } 
                    } 
                }
                
                
            
            fputcsv($fp, $tcell,',',chr(0));
            if($write_htm){
              fwrite($fp2,"</tr></thead><tbody>");
            }  
            //write body
            $count=0;
            foreach ($orders1 as $order) {
                $tcell = null; 
               if($write_htm){ 
                fwrite($fp2,"<tr>");
               } 
//                foreach ($order as $field => $value) {
                    $count++;
				$total = $order->total_mrp; 
                                $tallynamequery="select tally_name,gstin_no from it_codes where id=$order->store_id";
//                                echo $tallynamequery;
                                $tallyobj=$db->fetchObject($tallynamequery);
                                $query123 = "select irn,AckNo,AckDate from it_irncreditnote where CreditNote_ID=$order->id";//new change
                               $q123=$db->fetchObject($query123);
//                               echo $query123;
                               $irn="-";
                               $AckNo="-";
                               $Ackdate="-";
                               if(isset($q123))
                               {
                                 $irn=$q123->irn;
                               $AckNo=$q123->AckNo;
                               $Ackdate=$q123->AckDate;   
                                   
                               }
                               
                               
                               $gst=round(($order->igst_total+($order->cgst_total*2)),2);
                            $total=round(($order->total_taxable_value+$gst),2);
                            
                               $createdby="select store_name from it_codes where id=$order->created_by";
                               $createdby1=$db->fetchObject($createdby);
                               $created_by="-";
                               if(isset($createdby1)){
                               $created_by=$createdby1->store_name;
                               }
                            
                               $approveby="select store_name from it_codes where id=$order->approve_by ";//and is_approved=1
//                               echo $approveby;                               exit();
                               $approve_by1=$db->fetchObject($approveby);
                               $approve_by="-";
                               if(isset($approve_by1)){
                               $approve_by=$approve_by1->store_name;
                               }
                             
                            
//                              $status
                               if($order->is_approved==0){
                                   $status='Not Approved';
                               }
                               else{
                                   $status='Approved';
                               }
                    $value="";
//                   if ($field=="Sr No") {
                       $tcell[] .= trim($count);
//                   } else if($field == "CreditNote No"){ 
                       $tcell[] .= trim($order->invoice_no);
//                   } else if($field == "Irn No"){  
                       $tcell[] .= trim($irn);
//                   } else if($field == "Ack No"){  
                       $tcell[] .= trim($AckNo);
//                   } else if($field == "Ack Date"){  
                       $tcell[] .= trim($Ackdate);
//                   }else if($field == "Store Tally Name"){                                              
                       $tallyname="";
                       if(isset($tallyobj))
                       {
                        $tallyname  =   $tallyobj->tally_name;
                       }
                       $tcell[] .= trim($tallyname);
//                   } else if($field == "Dated"){                                              
                        $date = mmddyy($order->approve_dt); 
                       $tcell[] .= trim($date);
//                   }else if($field == "MRP Total"){  
                       $tcell[] .= trim($order->total_mrp);
//                   } else if($field == "Qty"){ 
                       $tcell[] .= trim($order->invoice_qty);
//                   } else if($field == "CN Taxable Value"){  
                       $tcell[] .= trim(round($order->total_taxable_value,2));
//                   }  else if($field == "Tax Total"){  
                       $tcell[] .= trim($gst);
//                   } else if($field == "Total Invoice Value"){   
                       $tcell[] .= trim($total);
//                   } else if($field == "Created By"){                                              
                       
                       $tcell[] .= trim($created_by);
//                   }else if($field == "Approve By"){                                              
                       
                       $tcell[] .= trim($approve_by);
//                   }else if($field == "Status"){                                              
                       
                       $tcell[] .= trim($status);
//                   }
                   
                   
                   
                   $tcell[] .= trim($value);
                   if($write_htm){
                    fwrite($fp2,"<td>".trim($count)."</td>");
                    fwrite($fp2,"<td>".trim($order->invoice_no)."</td>");
                    fwrite($fp2,"<td>".trim($irn)."</td>");
                    fwrite($fp2,"<td>".trim($AckNo)."</td>");
                    fwrite($fp2,"<td>".trim($Ackdate)."</td>");
                    fwrite($fp2,"<td>".trim($tallyname)."</td>");
                    fwrite($fp2,"<td>".trim($date)."</td>");
                    fwrite($fp2,"<td>".trim($order->total_mrp)."</td>");
                    fwrite($fp2,"<td>".trim($order->invoice_qty)."</td>");
                    fwrite($fp2,"<td>".trim(round($order->total_taxable_value,2))."</td>");
                    fwrite($fp2,"<td>".trim($gst)."</td>");
                    fwrite($fp2,"<td>".trim($total)."</td>");
                    fwrite($fp2,"<td>".trim($created_by)."</td>");
                    fwrite($fp2,"<td>".trim($approve_by)."</td>");
                    fwrite($fp2,"<td>".trim($status)."</td>");
                    
                   //fwrite($fp2,"<td>viewdata</td>");
                   }
                  
//                }
                fputcsv($fp, $tcell,',',chr(0));
                if($write_htm){
                 fwrite($fp2,"</tr>");
                }
                
            } 
//            if($this->gen==1){
//                $totTotalValue=$totAmt;
        //    }
            if($write_htm){
               // fwrite($fp2,"<tr><td><b></b></td><td><b></b></td></tr>");
                fwrite($fp2,"");
                fwrite ($fp2,"</tbody></table>");
                fclose ($fp2); 
            }
            fclose ($fp); 
            if($write_htm){
                $table = file_get_contents("tmp1/DgCreditNote.htm");
//                echo $table;
            }
        } else {
            echo "<br/>Unable to create file. Contact Intouch.";
        }
    }
    ?><div id="dwnloadbtn" style='margin-left:15px;  height:24px;width:130px;border: solid gray 1px;background:#F5F5F5;padding-top:4px;'>
    <a href='<?php echo "tmp1/store_dgcreditnote.php?output=$newfname1" ;?>' title='Export table to CSV'><img src="images/excel.png" width='20' hspace='3' style='margin-bottom:-6px;' /> Export To Excel</a>
    </div>      
    <?php }?> 
    
    
        <br/><br/>
<br/><br/>
  <?php if ($formResult) { ?>
                <p>
                    <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
                </p>
                        <?php } ?>
    </div>
    
</div>
    <?php
    }
}
?>