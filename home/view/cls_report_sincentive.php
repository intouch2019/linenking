<?php
require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once "session_check.php";

class cls_report_sincentive extends cls_renderer{

        var $currUser;
        var $userid;
        var $dtrange;
        var $storeidreport = null;
        var $params;
        var $itemctg; var $itemmrp; 
        var $scode; var $totqty;
        var $amt;
                  var $incentive;
                     var $bill_no;
        var $storeloggedin = -1;
        var $fields = array(); 
          var $atv;
        var $upt;
       
        function __construct($params=null) {
 //parent::__construct(array(UserType::Admin, UserType::CKAdmin,  UserType::Manager));                
		$this->currUser = getCurrUser();
		$this->userid = $this->currUser->id;
                if (isset($_SESSION['account_dtrange'])) { $this->dtrange = $_SESSION['account_dtrange']; } else { $this->dtrange = date("d-m-Y"); }
                if (isset($params['str'])) $this->storeidreport = $params['str']; else $this->storeidreport=null;
                if (isset($params['store'])) { $this->fields['store']=$params['store']; $this->store = $params['store']; } else $this->fields['store']="0";               
                if (isset($params['salesmainid'])) { $this->fields['salesmainid']=$params['salesmainid']; $this->salesmainid = $params['salesmainid']; } else $this->fields['salesmainid']="-";                                                            
                if (isset($params['totqty'])) { $this->fields['totqty']=$params['totqty']; $this->totqty = $params['totqty']; } else $this->fields['totqty']="0";                                
                if (isset($params['amt'])) { $this->fields['amt']=$params['amt']; $this->amt = $params['amt']; } else $this->fields['amt']="0";
                if (isset($params['atv'])) { $this->fields['atv']=$params['atv']; $this->atv = $params['atv']; } else $this->fields['atv']="0";
                if (isset($params['upt'])) { $this->fields['upt']=$params['upt']; $this->upt = $params['upt']; } else $this->fields['upt']="0";
                
                if (isset($params['billno'])) { $this->fields['billno']=$params['billno']; $this->billno = $params['billno']; } else $this->fields['billno']="0";
                if (isset($params['nettotal'])) { $this->fields['nettotal']=$params['nettotal']; $this->nettotal = $params['nettotal']; } else $this->fields['nettotal']="0";
                if (isset($params['returntotal'])) { $this->fields['returntotal']=$params['returntotal']; $this->returntotal = $params['returntotal']; } else $this->fields['returntotal']="0";
                if (isset($params['incentive'])) { $this->fields['incentive']=$params['incentive']; $this->incentive = $params['incentive']; } else $this->fields['incentive']="0";
                 if (isset($params['bill_no'])) { $this->fields['bill_no']=$params['bill_no']; $this->bill_no = $params['bill_no']; } else $this->fields['bill_no']="-";
               
                if (isset($params['ctg'])) { $this->fields['ctg']=$params['ctg']; $this->ctg = $params['ctg']; } else $this->fields['ctg']="0";
                
                 
                     
                 if($this->currUser->usertype == UserType::Dealer){ 
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
    
var storeid = '<?php echo $this->storeidreport; ?>';  
var storeloggedin = '<?php echo $this->storeloggedin; ?>';
//alert("STORE ID: "+storeid);
//alert("STORE LOGGED IN: "+storeloggedin);
 $(function(){
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
    });
    
    function reloadreport() {
    var reporttype=$('input[name=report]:radio:checked').val();       
       $('#selectRight option').attr('selected', 'selected');
       if(storeloggedin == '-1'){
           storeid = $('#store').val();
          //alert("SID:"+storeid);
       }
       //var storeid = $('#store').val();  
       //alert(storeid);
       if (storeid!=null && storeid!='') {
                var multiplevalues = $('#selectRight').val();
                var append='';
                var sequence=1;
                for (var i=0;i<multiplevalues.length;i++) {
                        append += "/"+multiplevalues[i]+"="+sequence;
                        sequence++;
                }
                window.location.href="report/sincentive/str="+storeid+append;
       } else {
           alert("please select a store to genereate a report");
       }
}

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
    if(listRight.options.length==0){
      alert('You have already moved all fields to Left');
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
            $menuitem = "sIncentiveRep";
            include "sidemenu.".$this->currUser->usertype.".php";
            $formResult = $this->getFormResult();
            $db = new DBConn();
?>
<div class="grid_10">
    <?php
    
    $display="none";
    $num = 0;
    ?>
    <div class="box" style="clear:both;">
	<fieldset class="login">
	<legend>Generate Salesman Incentive Report</legend>	
        <form action="" method="" onsubmit="reloadreport(); return false;">
		<div class="grid_12">
            <?php if($this->currUser->usertype != UserType::Dealer ){ ?>    
                <p>Select store(s) below to view their current stock.</p>    
		<div class="grid_4">
                    <b>Select Store*:</b><br/>
        	<select name="store" id="store" data-placeholder="Choose Store" class="chzn-select" multiple style="width:100%;">
         <?php if( $this->storeidreport == -1 ){
                                   $defaultSel = "selected";
                             }else{ $defaultSel = ""; } ?>
                    <option value="-1" <?php echo $defaultSel;?>>All Stores</option> 
<?php
$objs = $db->fetchObjectArray("select * from it_codes where usertype=".UserType::Dealer." order by store_name");

if($this->storeidreport == "-1"){
    $storeid = array();
    $allstoreArrays=$db->fetchObjectArray("select id from it_codes where usertype = ".UserType::Dealer);
    foreach($allstoreArrays as $storeArray){
        foreach($storeArray as $store){
            array_push($storeid,$store);
        }
    }
}else{
  $storeid = explode(",",$this->storeidreport);  
}
;
foreach ($objs as $obj) {        
	$selected="";
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
                <div class="grid_4">
                    <span style="font-weight:bold;">Date Filter : </span></br> <input size="20" type="text" id="dateselect" name="dateselect" value="<?php echo $this->dtrange; ?>" /> (Click to see date options)
		</div>
		</div>
             <?php }else{ ?>  
                <div class="grid_12">
                    <span style="font-weight:bold;">Date Filter : </span></br> <input size="20" type="text" id="dateselect" name="dateselect" value="<?php echo $this->dtrange; ?>" /> (Click to see date options)
		</div>
              <?php } ?>
            <div class="clear"></div>
            <br>
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
<!--                                          <option value="itemctg">Item Category</option>
                                          <option value="itemmrp">Item MRP</option>                                              -->
                                    <option value="ctg" selected >Category</option>
                                      <option value="bill_no" >Bill No</option> 
                                    </select>
                                </label></td>
                                <td colspan="1" rowspan="3" style="vertical-align:middle;">
                                        <input name="btnRight" type="button" id="btnRight" value="&gt;&gt;" onClick="javaScript:moveToRightOrLeft(1);">
                                    <br/><br/>
                                    <input name="btnLeft" type="button" id="btnLeft" value="&lt;&lt;" onClick="javaScript:moveToRightOrLeft(2);">
                                </td>
                                    <td rowspan="3" colspan="2" align="left">
                                        <select name="selectRight" multiple size="10" style="width:200px;" id="selectRight">
<!--                                        <option value="scode" selected>Salesman Code</option>
                                        <option value="totqty" selected>Total Quantity</option>                                                                                                                      
                                        <option value="amt" selected>Amount</option>-->
                                          <option value="store">Store Name</option>
                                            <option value="salesmainid" >Salesman Id</option>
                                             <option value="incentive" >Incentive</option> 
                                              <option value="totqty"  >Sale Quantity</option>
                                             <option value="amt" >Net Sale </option>
                                               <option value="atv" >ATV</option>
                                              <option value="upt" >UPT</option>
                                        </select>
                                    </td>
                                </tr>
                        </table>
                    </div>
                </div>
            
            
            <div class="grid_12" id="generalselection" style="display:none;">
		<div class="grid_12" >
		Fields in the report:<br />               
                (Store Name/MRP/Multiplier)
		</div>
            </div>
		<div class="grid_12" id="submitbutton" style="padding:10px;">
                <input type="submit" name="add" id="add" value="Generate Report" style="background-color:white;"/>
                
                       <?php if ($formResult) { ?>
                <p>
                    <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
                </p>
                        <?php } ?>
		</div>
            </form>
	</fieldset>
    </div> <!-- class=box -->
    <?php if (isset($this->storeidreport))  { //12 fields ?>
    <div class="box grid_12" style="margin-left:0px; overflow:auto; height:500px;">
        <?php 
            $queryfields = "";
            $tableheaders = "";
            $group_by = array();$gClause="";
            $storeClause="";
            $dtarr = explode(" - ", $this->dtrange);
            $_SESSION['storeid'] = $this->storeidreport;
            $dQuery = "";
            if (count($dtarr) == 1) {
                    list($dd,$mm,$yy) = explode("-",$dtarr[0]);
                    $sdate = "$yy-$mm-$dd";		
                    $dQuery = " and s.bill_datetime >= '$sdate 00:00:00' and s.bill_datetime <= '$sdate 23:59:59' ";
            } else if (count($dtarr) == 2) {
                    list($dd,$mm,$yy) = explode("-",$dtarr[0]);
                    $sdate = "$yy-$mm-$dd";
                    list($dd,$mm,$yy) = explode("-",$dtarr[1]);
                    $edate = "$yy-$mm-$dd";		
                    $dQuery = " and s.bill_datetime >= '$sdate 00:00:00' and s.bill_datetime <= '$edate 23:59:59' ";
            } else {
                    $dQuery = "";
            }
            
            
            if($this->storeidreport == "-1"){
                 $storeClause = " c.usertype = ".UserType::Dealer ;
//              $storeClause = " select id from it_codes c where usertype = ".UserType::Dealer ;
            }else{
                 $storeClause ="s.store_id in( $this->storeidreport)";
//                $storeClause = $this->storeidreport;
            }
//            $tableheaders = "Store:Barcode:Current Quantity";
            for ($x=1;$x<14;$x++) {
                foreach ($this->fields as $field => $seq) {
                    if ($seq==$x) {   
                         if ($field=="store") {$tableheaders.="Store Name:"; $queryfields .= "c.store_name as storename,"; $group_by[] = "c.store_name";}                       
                      //  if ($field=="billno") {$tableheaders.="Ticket No:"; $queryfields .= "s.bill_no as bill_no,";$group_by[] = "s.bill_no";}
                        if ($field=="ctg") {$tableheaders.="Category:"; $queryfields .= "s.catg_name as category,";$group_by[] = "s.catg_name";}
                        if ($field=="salesmainid") {$tableheaders.="Salesman Id:"; $queryfields .= "s.salesman_no as salesmanid,";$group_by[] = "s.salesman_no";}                        
                        if ($field=="totqty") {$tableheaders.="Total Qty:"; $queryfields .= " sum(s.qty) as totalqty,";}                                                                        
                        
                       // if ($field=="nettotal") {$tableheaders.="Net Total:"; $queryfields .= "round(sum(s.net_total),2) as nettotal ,";}                        
                        //return_total
                        //  if ($field=="returntotal") {$tableheaders.="Return Value:"; $queryfields .= "round(sum(s.return_total),2) as returntotal ,";} 
                       // if ($field=="amt") {$tableheaders.="Net Sale :"; $queryfields .= "round(sum(s.incentive_amount),2) as isamt ,";}                                                                       
                         //if ($field=="amt") {$tableheaders.="Net Sale :"; $queryfields .= "round(sum(s.incentive_amount),2) as isamt ,";}                                                                       
                        if ($field=="amt") {$tableheaders.="Net Sale :"; $queryfields .= "round(sum(s.net_total)-sum(s.return_total),2) as isamt ,";}                                                                       
                        if ($field=="atv") {$tableheaders.="ATV:"; $queryfields .= "round(((sum(s.net_total)-sum(s.return_total))/count(DISTINCT s.bill_no)),2) as atv  ,";}                                                                       
                        if ($field=="upt") {$tableheaders.="UPT:"; $queryfields .= "round(((sum(s.qty))/count(DISTINCT s.bill_no)) ,2) as upt  ,";}                                                                       
                         
                       // if ($field=="incentive") {$tableheaders.="Incentive :"; $queryfields .= " round(sum(s.incentive_amount),2) as Incentive ,";}   //22092020                                                                    
                          // if ($field=="incentive") {$tableheaders.="Incentive :"; $queryfields .= " round(sum(s.incentive_amount),2)*.01 as Incentive ,";}                                                                       
                           if ($field=="incentive") {$tableheaders.="Incentive :"; $queryfields .= " round(sum(s.net_total)-sum(s.return_total),2)*.01 as Incentive ,";}                                                                       
                       
                         if ($field=="bill_no") {$tableheaders.="Bill No :"; $queryfields .= " s.bill_no as billno ,";$group_by[] = "s.bill_no";}                                                                       
                        
                        
                        //incentive
                    }
                }
            }
            $queryfields = substr($queryfields, 0, -1);
            if(!empty($group_by)){ 
                $gClause = " group by ".implode(",", $group_by);
             //$groupby = substr($groupby, 0, -1);
            }else if($this->currUser->usertype==UserType::Dealer && empty($group_by)){
                //$queryfields .= "c.store_name,sum(cs.quantity) as quantity,sum(i.MRP * cs.quantity) as totalvalue";                
                $queryfields .= "c.store_name";                
            }
            //$query2 = "select c.store_name , cs.barcode, sum(cs.quantity) as quantity from it_codes c , it_current_stock cs where c.id = cs.store_id and cs.store_id in ( $storeClause ) group by cs.barcode ";
            //echo $query2;
            $query = "select $queryfields";
            //$query .= " from it_codes c,it_orders o,it_order_items oi, it_items i, it_categories ctg, it_sincentive_multipliers sm  where oi.order_id = o.id and o.store_id = c.id  and oi.item_id = i.id and i.ctg_id = ctg.id and i.ctg_id = sm.ctg_id and o.salesman_code is not null and o.store_id in ( $storeClause ) and case when sm.mrp != -1 then sm.mrp = i.mrp else 1=1 end $dQuery $gClause ";
            $query .= " from it_codes c,it_salesmanreport s where  c.id=s.store_id and  $storeClause $dQuery $gClause ";
//           echo $query;
            $result = $db->execQuery($query);

?>
        <br /><div style='margin-left:40px; padding-left:15px; height:24px;width:130px;border: solid gray 1px;background:#F5F5F5;padding-top:4px;'>
        <a href='tmp/SalesmanIncentive.csv' title='Export table to CSV'><img src='images/excel.png' width='20' hspace='3' style='margin-bottom:-6px;' /> Export To Excel</a>
        </div><br />
        
<?php 
    $totqty =0 ; $totTotalVal=0;
    if (isset($result)) { 
        $fp = fopen('tmp/SalesmanIncentive.csv', 'w');
        $fp2 = fopen ('tmp/salesmanincentive.htm', 'w');
        if ($fp) {
            $trow = array(); $tcell = array(); 
            //write header info
            fwrite($fp2,"<table width='100%' style='overflow:auto;'><thead><tr>");
            $headerarr = explode(":", $tableheaders); 
            foreach ($headerarr as $harr) {
                if ($harr != "") {
                    $tcell[] .= $harr;
                    fwrite($fp2,"<th>$harr</th>");
                }
            }
            fputcsv($fp, $tcell,',',chr(0));
            fwrite($fp2,"</tr></thead><tbody>");
            //write body
            while ($reportrows = $result->fetch_object()) {
                $tcell = null; 
                fwrite($fp2,"<tr>");
                foreach ($reportrows as $field => $value) {
                   if($field == "quantity"){$totqty += $value;}
                   if($field=="iamt"){$totTotalVal += $value;}
                   $tcell[] .= $value;
                   fwrite($fp2,"<td>$value</td>");
                }
                fputcsv($fp, $tcell,',',chr(0));
                fwrite($fp2,"</tr>");
            } 
            //fwrite($fp2,"<tr><td><b>Total</b></td><td><b>$totqty</b></td><td><b>$totTotalVal</b></td></tr>");
            fwrite ($fp2,"</tbody></table>");
            fclose ($fp); fclose ($fp2); 
            $table = file_get_contents("tmp/salesmanincentive.htm");
            echo $table;
        } else {
            echo "<br/>Unable to create file. Contact Intouch.";
        }
    }
?>
    </div>
    <?php } ?>
</div>
<script src="js/chosen/chosen.jquery.js" type="text/javascript"></script>

<?php
	}
}
?>
