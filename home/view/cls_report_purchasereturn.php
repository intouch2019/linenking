<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");
require_once "lib/core/strutil.php";
require_once "session_check.php";

class cls_report_purchaseReturn extends cls_renderer{
        var $currUser;
        var $userid;
        var $dtrange;
        var $params;
        var $date; var $store; var $billno; var $transaction;
        var $total_quantity;
        var $designno;
        var $barcode;
        var $mrpval; 
        var $total_mrp;
        var $inid; 
        var $taxableamt;
        var $tax; 
        var $style; 
        var $view;
        var $gen; var $shwdetails; var $itemvalue;
        var $storeidreport = null;var $a=0;
        var $storeloggedin = -1; 
        var $month=0;
        var $fields = array();
      
       
 
                 
        function __construct($params=null) {
//		parent::__construct(array(UserType::Admin, UserType::CKAdmin, UserType::Manager));
                ini_set('max_execution_time', 300);
		$this->currUser = getCurrUser();
		$this->userid = $this->currUser->id;
                if (isset($_SESSION['account_dtrange'])) { $this->dtrange = $_SESSION['account_dtrange']; }
                else { $this->dtrange = date("d-m-Y"); }
                if (isset($params['id'])) { $this->fields['id']=$params['id']; $this->store = $params['id']; } else $this->fields['id']="0";
                  
                if (isset($params['date'])) { $this->fields['date']=$params['date']; $this->date = $params['date']; } else $this->fields['date']="0";
                if (isset($params['store'])) { $this->fields['store']=$params['store']; $this->store = $params['store']; } else $this->fields['store']="0";
//                if (isset($params['billno'])) { $this->fields['billno']=$params['billno']; $this->billno = $params['billno']; } else $this->fields['billno']="0";
//                if (isset($params['view'])) { $this->fields['view']=$params['view']; $this->view = $params['view']; } else $this->fields['view']="0";
                if (isset($params['total_quantity'])) { $this->fields['total_quantity']=$params['total_quantity']; $this->total_quantity = $params['total_quantity']; } else $this->fields['total_quantity']="0";
//                if (isset($params['designno'])) { $this->fields['designno']=$params['designno']; $this->designno = $params['designno']; } else $this->fields['designno']="0";
//                if (isset($params['totalvalue'])) { $this->fields['totalvalue']=$params['totalvalue']; $this->totalvalue = $params['totalvalue']; } else $this->fields['totalvalue']="0";               
//                if (isset($params['shwdetails'])) { $this->fields['shwdetails']=$params['shwdetails']; $this->shwdetails = $params['shwdetails']; } else $this->fields['shwdetails']="0";
                if (isset($params['mrpval'])) { $this->fields['mrpval']=$params['mrpval']; $this->mrpval = $params['mrpval']; } else $this->fields['mrpval']="0";
                if (isset($params['total_mrp'])) { $this->fields['total_mrp']=$params['total_mrp']; $this->total_mrp = $params['total_mrp']; } else $this->fields['total_mrp']="0";
                if (isset($params['inid'])) { $this->fields['inid']=$params['inid']; $this->inid = $params['inid']; } else $this->fields['inid']="0";
                if (isset($params['gen'])) $this->gen = $params['gen']; else $this->gen="0";
                if (isset($params['str'])) $this->storeidreport = $params['str']; else $this->storeidreport=null;
                if(isset($params['a'])){ $this->a=$params['a'];}
                
                
                
                if($this->currUser->usertype==UserType::Dealer){ 
                   
                    $this->storeidreport = $this->currUser->id;
                    $this->storeloggedin = 1;                    
                }
        }

	function extraHeaders() {
        ?>

<script type="text/javascript" src="jqueryui/js/jquery-ui-1.7.1.custom.min.js"></script>
<script type="text/javascript" src="js/daterangepicker.jQuery.js"></script>
<link rel="stylesheet" href="css/ui.daterangepicker.css" type="text/css" />
<link rel="stylesheet" href="css/redmond/jquery-ui-1.7.1.custom.css" type="text/css" title="ui-theme" />



 <style type="text/css" title="currentStyle">
    @import "js/datatables/media/css/demo_page.css";
    @import "js/datatables/media/css/demo_table.css";
</style>
<script src="js/datatables/media/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="js/chosen/chosen.css" />
<script type="text/javascript" src="js/ajax.js"></script>
<script type="text/javascript" src="js/ajax-dynamic-list.js">
    
	/************************************************************************************************************
	(C) www.dhtmlgoodies.com, April 2006
	
	This is a script from www.dhtmlgoodies.com. You will find this and a lot of other scripts at our website.	
	
	Terms of use:
	You are free to use this script as long as the copyright message is kept intact. However, you may not
	redistribute, sell or repost it without our permission.
	
	Thank you!
	
	www.dhtmlgoodies.com
	Alf Magne Kalleland
	
	************************************************************************************************************/	

</script>
<script type="text/javaScript">  
 function moveToRightOrLeft(side){
  var listLeft=document.getElementById('selectLeft');
  var listRight=document.getElementById('selectRight');

  if(side==1){
    if(listLeft.options.length==0){ 
    alert('You have already moved all fields to Right');
    return false;
    }else{
      var selectedCountry=listLeft.options.selectedIndex;

      move(listRight,listLeft.options[selectedCountry].value,listLeft.options[selectedCountry].text);
      listLeft.remove(selectedCountry); 

      if(listLeft.options.length>0){
      listLeft.options[selectedCountry].selected=true;
      } 
    } 
  } else if(side==2){
    var selectedCountry=listRight.options.selectedIndex;  
    var sval = listRight.options[selectedCountry].value;  
    if(sval=='shwdetails' || sval=='totalvalue'){
       
       alert('You cannot move default fields to Left'); 
      return false;
    }else{
      var selectedCountry=listRight.options.selectedIndex; 

      move(listLeft,listRight.options[selectedCountry].value,listRight.options[selectedCountry].text);
      listRight.remove(selectedCountry);

      if(listRight.options.length>0){
        listRight.options[selectedCountry].selected=true;
      }
    }
  }
}

function move(listBoxTo,optionValue,optionDisplayText){  
  var newOption = document.createElement("option"); 
  newOption.value = optionValue; 
  newOption.text = optionDisplayText;   
  newOption.selected = true;
  listBoxTo.add(newOption, null);  
  return true; 
} 
</script>

<link rel="stylesheet" href="css/bigbox.css" type="text/css" />

        
        <?php
        }

        public function pageContent() {
            //$currUser = getCurrUser();
//            $menuitem = "bnewbatch";
            $menuitem = "purchasereturninvc";
            include "sidemenu.".$this->currUser->usertype.".php";
            $formResult = $this->getFormResult();
            $db = new DBConn(); $write_htm = true;
            $categories = array(); $sizes = array(); $styles = array();
            $mfg_by = array(); $brands = array(); $prod_typs = array();
            $fabric_types = array();$materials = array();
            $sdate="";$edate="";
?>
<div class="grid_10">
    <?php
    
    $display="none";
    $num = 0;
    //create categories array
 ?>
    <div class="box" style="clear:both;">
	<fieldset class="login">
	<legend>Generate Purchase Return Report</legend>
	<p>Select values for the various fields below. Some fields allow you to pick only a single value, others allow you to pick multiple values.</p>
        <form action="" method="" onsubmit="reloadreport(); return false;">
		<div class="grid_12">
                <?php if($this->currUser->usertype != UserType::Dealer ){ ?>    
		<div class="grid_4">
                    <b>Select Store*:</b><br/>
        	<select name="store" id="store" data-placeholder="Choose Store" class="chzn-select" multiple style="width:100%;">
                <?php if( $this->storeidreport == -1 ){
                                   $defaultSel = "selected";
                             }else{ $defaultSel = ""; } ?>
                <option value="-1" <?php echo $defaultSel;?>>All Stores</option> 
<?php
$objs = $db->fetchObjectArray("select * from it_codes where usertype=4 and composite_billing_opted =1 order by store_name");
print_r($objs);
if($this->storeidreport == "-1"){
    $storeid = array(); 
    if($this->a==0){ //means 'all stores report is req only in excel'
     $write_htm = false;   
    }
    $allstoreArrays=$db->fetchObjectArray("select id from it_codes where usertype = 4");
    foreach($allstoreArrays as $storeArray){
        foreach($storeArray as $store){
            array_push($storeid,$store);
        }
    }
}else{
  $storeid = explode(",",$this->storeidreport);  
}

foreach ($objs as $obj) {        
	$selected="";
//	if (isset($this->storeidreport) && $obj->id==$this->storeidreport) { $selected = "selected"; }
        if ($this->storeidreport != -1){
            foreach($storeid as $sid){
                if($obj->id==$sid) 
                { $selected = "selected"; }
            }
        }
?>
          <option value="<?php echo $obj->id; ?>" <?php echo $selected; ?>><?php echo $obj->store_name; ?></option> 
<?php } ?>
		</select>
		</div>
        <?php } ?>    
		<div class="grid_4">
                <span style="font-weight:bold;">Date Filter : </span></br> <input size="17" type="text" id="dateselect" name="dateselect" value="<?php echo $this->dtrange; ?>" /> (Click to see date options)
		</div>
                <div class="grid_4"  style="display:none">
		Report type*:<br />
<!--        	<input type="radio" name="report" value="billwise" <?php if ($this->gen==1) echo "checked"; ?> onclick="showgeneral();">General bill wise sale summary<br>-->
                <input type="radio" name="report" value="itemwise" <?php if ($this->gen==0) echo "checked"; ?> onclick="showitemwise();">Group By
		</div>
		</div>
            
		<div class="grid_12" id="itemselection">
<!--		<div class="grid_12" >-->
                    <div class="grid_7">
                            <table border="0" colspan="4">
                                <tr>
                                    <td colspan="5">Custom Report Field Selection:</td>
                                </tr>
                                <tr>
                                    <td colspan="2">Available Fields </td>
                                    <td colspan="1">&nbsp;</td>
                                    <td colspan="2">Your Selection </td>
                                </tr>
                                <tr>
                                <td rowspan="3" colspan="2" align="right"><label>
                                    <select name="selectLeft" size="10" width="100%" style="width:200px;" id="selectLeft"> 
                                        
                                        <option value="inid">Return No</option> 
                                        <option value="store">Store Name</option> 
                                        
                                          <option value="total_mrp">Mrp Total</option>
                                        <option value="mrpval">Invoice Amount</option>
                                        <option value="total_quantity">Quantity</option>
                                        <option value="date">Date</option>
                                            
                                        
                                            
                                            <?php 
                                            if($this->currUser->usertype == UserType::Admin || $this->currUser->usertype == UserType::CKAdmin){?>

                                        <?php } ?>
                                    </select>
                                </label></td>
                                <td colspan="1" rowspan="3" style="vertical-align:middle;">
                                        <input name="btnRight" type="button" id="btnRight" value="&gt;&gt;" onClick="javaScript:moveToRightOrLeft(1);">
                                    <br/><br/>
                                    <input name="btnLeft" type="button" id="btnLeft" value="&lt;&lt;" onClick="javaScript:moveToRightOrLeft(2);">
                                </td>
                                    <td rowspan="3" colspan="2" align="left">
                                        <select name="selectRight" multiple size="10" style="width:200px;" id="selectRight">
                                            <option  value="id">Id</option>
<!--                                            <option value="shwdetails">Show details</option>-->
     <!--                                        <option value="shwdetails">Item Quantity</option>                                       -->
<!--                                        <option value="totalvalue">Total Value</option>-->
                                        </select>
                                    </td>
                                </tr>
                        </table>
                    </div>
                </div>
            
      
           
		<div class="grid_12" id="submitbutton" style="padding:10px;">
                <input type="submit" name="add" id="add" value="Generate Report" style="background-color:white;"/>
                
                       <?php if ($formResult) {                            
                           ?>
                <p>
                    <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
                </p>
                        <?php } ?>
		</div>
            </form>
	</fieldset>
    </div> <!-- class=box -->
    <?php if (isset($this->storeidreport)) { //22 fields ?>
    <div class="box grid_12" style="margin-left:0px; overflow:auto; height:500px;">
        <?php 
        $queryfields = "";
        $tableheaders = "";
        $total_td = "";
        $str="";
        $dtarr = explode(" - ", $this->dtrange);
            $_SESSION['storeid'] = $this->storeidreport;
	if (count($dtarr) == 1) {
		list($dd,$mm,$yy) = explode("-",$dtarr[0]);
		$sdate = "$yy-$mm-$dd";		
                $dQuery = " and i.date >= '$sdate 00:00:00' and i.date <= '$sdate 23:59:59' ";
	} else if (count($dtarr) == 2) {
		list($dd,$mm,$yy) = explode("-",$dtarr[0]);
		$sdate = "$yy-$mm-$dd";
		list($dd,$mm,$yy) = explode("-",$dtarr[1]);
		$edate = "$yy-$mm-$dd";		
                    $dQuery = " and i.date >= '$sdate 00:00:00' and i.date <= '$edate 23:59:59' ";
	} else {
		$dQuery = "";
	}
        
       //store purchase return report excel name
                $filenameas_storename = "stores_";
            if ($this->currUser->usertype == UserType::Dealer) {
                $filenameas_storename = $this->currUser->store_name . "_";
            } else {
                $ids = explode(',', $this->storeidreport);
                if (count($ids) > 1) {
                    $filenameas_storename = "Multistores_";
                } else if (count($ids) == 1) {
                    if ($this->storeidreport == -1) {
                        $filenameas_storename = "Allstores_";
                    } else {
                        $storename = $db->fetchObject("select store_name from it_codes where id=$this->storeidreport");
                        $filenameas_storename = $storename->store_name . "_";
                    }
                }
            }
            
        if ($this->gen!=1) {
                        $totTotalValue=0;$totAmt="";
            $newfname = $filenameas_storename."PurchaseReturnReports_".$sdate."_".$edate.".csv";           
	    $group_by = array(); $total_td = "";$gClause="";
            for ($x=1;$x<24;$x++) {
                foreach ($this->fields as $field => $seq) {
                    if ($seq==$x) {
                        if ($field=="id") {$tableheaders.="Id:"; $queryfields .= " i.id as id,"; $group_by[] = "i.id"; $total_td .= "<td></td>";} 
                                                                    
                        if ($field=="date") {$tableheaders.="Date:"; $queryfields .= " i.date as date,"; $group_by[] = "i.date"; $total_td .= "<td></td>";} // DATE_FORMAT(o.bill_datetime,'%d/%m/%Y')
                        //if ($field=="billno") {$tableheaders.="Invoice No:";  $queryfields .= "i.invoice_no as invno,"; $group_by[] = "i.invoice_no"; $total_td .= "<td></td>";}
                        if ($field=="inid") {$tableheaders.="Return No:"; $queryfields .= "i.return_no as rid,";;$group_by[] = "i.return_no";}
                        if ($field=="store") {$tableheaders.="Store Name:"; $queryfields .= "c.store_name as stores,";$group_by[] = "i.store_id"; $total_td .= "<td></td>";}
                        if ($field=="total_quantity") {$tableheaders.="Quantity:"; $queryfields .= "i.quantity as quantity,";$group_by[] = "i.quantity"; $total_td .= "<td></td>";}
                        if ($field=="mrpval") {$tableheaders.="Invoice Amount:"; $queryfields .= "i.amount as invamt,";}
                        if($field=="total_mrp"){$tableheaders.="Total MRP:"; $queryfields.="i.total_mrp as mrp,";}
    
                    }
                }
            }
            
            
            if(!empty($group_by)){              
                $gClause = " group by ".implode(",", $group_by);
            }
//            if (strpos($queryfields,"o.tickettype") === false){
//               $queryfields .= "o.tickettype,"; 
//            }
        //$tableheaders.="show_details:"; 
             
           //$tableheaders.="Quantity:"; $queryfields .= "sum(case when (i.invoice_type in (6,7) ) then i.invoice_type else 0 end) as quantity,";
            //$tableheaders.="Total Value:"; $queryfields .= "sum(case when (o.discount_pct is not NULL) then ((((100-o.discount_pct)/100)*oi.price) * (case when (o.tickettype in (0,1,6)) then (oi.quantity) else 0 end )) else oi.price*(case when (o.tickettype in (0,1,6)) then (oi.quantity) else 0 end ) end) as totalvalue,";           
            $queryfields = substr($queryfields, 0, -1);
            $storeClause="";
            if($this->storeidreport == "-1"){               
                $storeClause = " c.usertype = ".UserType::Dealer ;
            }else{              
                $storeClause = " i.store_id in ( $this->storeidreport ) ";
            }
            
            $query = "select $queryfields";
            //   $query .= " from it_saleback_invoices o,it_saleback_invoice_items oi, it_items i, it_codes c,it_categories ics where $storeClause $dQuery and oi.invoice_id=o.id and i.barcode = oi.item_code and  o.store_id = c.id and i.ctg_id=ics.id".$gClause;
        
      $query.=" from it_codes c,it_store_returns i where $storeClause $dQuery and c.id=i.store_id  and i.createtime > '2019-04-01 00:00:00' ".$gClause;
         //print $query; //and c.id in ( $storeClause)
	    //error_log("1:$query\n",3, "../ajax/tmp.txt");
            $result = $db->execQuery($query);
          
            
        } else if ($this->gen==1) {

        }
     
?>
        <br /><div id="dwnloadbtn" style='margin-left:40px; padding-left:15px; height:24px;width:130px;border: solid gray 1px;background:#F5F5F5;padding-top:4px;'>
            <a href='<?php echo "tmp/store_purchasereturn.php?output=$newfname" ;?>' title='Export table to CSV'><img src="images/excel.png" width='20' hspace='3' style='margin-bottom:-6px;' /> Export To Excel</a>
        </div><br />
        
<?php 
   $totqty=0;$totsp=0;
    if (isset($result)) {
       
           
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
            
            //echo $tableheaders;
        
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
            while ($reportrows = $result->fetch_object()) {
                $tcell = null; 
               if($write_htm){ 
                fwrite($fp2,"<tr>");
               } 
                foreach ($reportrows as $field => $value) {
                  
                     if($field=="invamt"){
                        $value=sprintf('%.2f',$value);
                    }else if ($field=="tax") {
                       $value = sprintf('%.2f',$value);
                   } else if($field == "date"){                                              
                       $t_str = ddmmyy2($value);
                       $value = $t_str;
                   }
                   
                   
                   
                   $tcell[] .= trim($value);
                   if($write_htm){
                    fwrite($fp2,"<td>".trim($value)."</td>");
                    
                   //fwrite($fp2,"<td>viewdata</td>");
                   }
                  
                }
                fputcsv($fp, $tcell,',',chr(0));
                if($write_htm){
                   // print_r($reportrows);
                   //print_r($reportrows);
               
                    $invid  = $reportrows->id;
                    //print $invid;style="color: #cc0000"
                    
                  fwrite($fp2,"<td><a href='lk/prinvoices/id=$invid 'style='color:#cc0000'  >Show Details</a></td>");
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
                echo $table;
            }
        } else {
            echo "<br/>Unable to create file. Contact Intouch.";
        }
    }
?>
    </div>
    <?php } ?>
</div>

<script src="js/chosen/chosen.jquery.js"  type="text/javascript"></script>
<script type="text/javascript"> </script>
<script type="text/javascript">
var storeid = '<?php echo $this->storeidreport; ?>';  
var storeloggedin = '<?php echo $this->storeloggedin; ?>';
//alert("STORE ID: "+storeid);
//alert("STORE LOGGED IN: "+storeloggedin);
    $(function(){
         //var url = "ajax/tb_Sbinvoices.php";
         
        $(".chzn-select").chosen(); $(".chzn-select-deselect").chosen({allow_single_deselect:true});
        var isOpen=false;
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
				//window.location.reload();
			}
		});
		}
	});
        
        var radio = $('input[name=report]:checked').val()
        if (radio=='billwise') {
            showgeneral();
        } else {
            showitemwise();
        }
//        $('#dwnloadbtn').hide();
    });
    
    function showgeneral(){
        $('#itemselection').hide();
        $('#generalselection').show();
    }
    
    function showitemwise(){
        $('#generalselection').hide();
        $('#itemselection').show();
    }
    
    var fieldlist = new Array();
    
    function reloadreport() {        
       if(storeloggedin == '-1'){
           storeid = $('#store').val();
          //alert("SID:"+storeid);
       }
      //alert("1: "+storeid);
      
      function showInvoiceDetails( invid){
  // window.location.href = "lk/sbinvoice/id="+invid;
    
    
}
      
        var aclause='';
        if(storeid=='-1'){
          resp = confirm("Do you want all stores report visible on portal?"); 
          if(resp){
              aclause='/a=1';
          }
        }
        //alert("a:"+aclause);
//       // $('select.foo option:selected').val(); commented
       var reporttype=$('input[name=report]:radio:checked').val();
       //alert(reporttype);commented
       $('#selectRight option').attr('selected', 'selected');
      // var storeid = $('#store').val();      
       //alert("2: "+storeid);//commented
       if (storeid!="" && storeid != null) {
           if (reporttype=="itemwise") {
                var multiplevalues = $('#selectRight').val();
                //var values = $('#itemfields').attr('name'); commented
                //alert(values);commented
                //alert(multiplevalues);commented
                var append='';
                var sequence=1;
                for (var i=0;i<multiplevalues.length;i++) {
                        append += "/"+multiplevalues[i]+"="+sequence;
                        sequence++;
                }
             window.location.href="report/purchaseReturn/str="+storeid+append+aclause;
                // window.location.href="ck/sbinvoice/str="+storeid+append+aclause;
             
           } else {
                window.location.href="report/purchaseReturn/str="+storeid+"/gen=1"+aclause;
           }
           $('#dwnloadbtn').show();
       } else {
           alert("please select store(s) to genereate a report");
       }
    }
</script>
<?php
	}
}
?>