<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");
require_once "lib/core/strutil.php";
require_once "session_check.php";

class cls_report_storesales extends cls_renderer{

        var $currUser;
        var $userid;
        var $dtrange;
        var $params;
        var $date; var $store; var $billno; var $transaction;
        var $itemctg; var $designno; var $itemmrp; var $barcode;
        var $linediscountper; var $linediscountval; var $ticketdiscountper;
        var $ticketdiscountval; var $totaldiscount; var $tax; var $brand;
        var $category; var $style; var $size; var $fabric; var $material;
        var $prodtype; var $manuf; var $gen; var $itemqty; var $itemvalue; var $totalvalue;
        var $storeidreport;
        var $fields = array();
       
        function __construct($params=null) {
//		parent::__construct(array(UserType::Admin, UserType::CKAdmin, UserType::Manager));
                ini_set('max_execution_time', 300);
		$this->currUser = getCurrUser();
		$this->userid = $this->currUser->id;
                if (isset($_SESSION['account_dtrange'])) { $this->dtrange = $_SESSION['account_dtrange']; }
                else { $this->dtrange = date("d-m-Y"); }
                //date,store,billno,transaction,itemctg,designno,itemmrp,barcode,linediscountper,linediscountval,ticketdiscountper,
                //ticketdiscountval, totaldiscount, tax, brand, category, style, size, fabric, material, prodtgype, manuf.
                if (isset($params['date'])) { $this->fields['date']=$params['date']; $this->date = $params['date']; } else $this->fields['date']="0";
                if (isset($params['store'])) { $this->fields['store']=$params['store']; $this->store = $params['store']; } else $this->fields['store']="0";
                if (isset($params['billno'])) { $this->fields['billno']=$params['billno']; $this->billno = $params['billno']; } else $this->fields['billno']="0";
                if (isset($params['billtype'])) { $this->fields['billtype']=$params['billtype']; $this->billtype = $params['billtype']; } else $this->fields['billtype']="0";
//                if (isset($params['transaction'])) { $this->fields['transaction']=$params['transaction']; $this->transaction = $params['transaction']; } else $this->fields['transaction']="0";
                if (isset($params['itemctg'])) { $this->fields['itemctg']=$params['itemctg']; $this->itemctg = $params['itemctg']; } else $this->fields['itemctg']="0";
                if (isset($params['designno'])) { $this->fields['designno']=$params['designno']; $this->designno = $params['designno']; } else $this->fields['designno']="0";
                if (isset($params['itemmrp'])) { $this->fields['itemmrp']=$params['itemmrp']; $this->itemmrp = $params['itemmrp']; } else $this->fields['itemmrp']="0";
                if (isset($params['itemvalue'])) { $this->fields['itemvalue']=$params['itemvalue']; $this->itemvalue = $params['itemvalue']; } else $this->fields['itemvalue']="0";
                if (isset($params['totalvalue'])) { $this->fields['totalvalue']=$params['totalvalue']; $this->totalvalue = $params['totalvalue']; } else $this->fields['totalvalue']="0";               
                if (isset($params['itemqty'])) { $this->fields['itemqty']=$params['itemqty']; $this->itemqty = $params['itemqty']; } else $this->fields['itemqty']="0";
                if (isset($params['barcode'])) { $this->fields['barcode']=$params['barcode']; $this->barcode = $params['barcode']; } else $this->fields['barcode']="0";
                if (isset($params['linediscountper'])) { $this->fields['linediscountper']=$params['linediscountper']; $this->linediscountper = $params['linediscountper']; } else $this->fields['linediscountper']="0";
                if (isset($params['linediscountval'])) { $this->fields['linediscountval']=$params['linediscountval']; $this->linediscountval = $params['linediscountval']; } else $this->fields['linediscountval']="0";
                if (isset($params['ticketdiscountper'])) { $this->fields['ticketdiscountper']=$params['ticketdiscountper']; $this->ticketdiscountper = $params['ticketdiscountper']; } else $this->fields['ticketdiscountper']="0";
                if (isset($params['ticketdiscountval'])) { $this->fields['ticketdiscountval']=$params['ticketdiscountval']; $this->ticketdiscountval = $params['ticketdiscountval']; } else $this->fields['ticketdiscountval']="0";
//                if (isset($params['totaldiscount'])) { $this->fields['totaldiscount']=$params['totaldiscount']; $this->totaldiscount = $params['totaldiscount']; } else $this->fields['totaldiscount']="0";
                if (isset($params['tax'])) { $this->fields['tax']=$params['tax']; $this->tax = $params['tax']; } else $this->fields['tax']="0";
                if (isset($params['brand'])) { $this->fields['brand']=$params['brand']; $this->brand = $params['brand']; } else $this->fields['brand']="0";
//                if (isset($params['category'])) { $this->fields['category']=$params['category']; $this->category = $params['category']; } else $this->fields['category']="0";
                if (isset($params['style'])) { $this->fields['style']=$params['style']; $this->style = $params['style']; } else $this->fields['style']="0";
                if (isset($params['size'])) { $this->fields['size']=$params['size']; $this->size = $params['size']; } else $this->fields['size']="0";
                if (isset($params['fabric'])) { $this->fields['fabric']=$params['fabric']; $this->fabric = $params['fabric']; } else $this->fields['fabric']="0";
                if (isset($params['material'])) { $this->fields['material']=$params['material']; $this->material = $params['material']; } else $this->fields['material']="0";
                if (isset($params['prodtype'])) { $this->fields['prodtype']=$params['prodtype']; $this->prodtype = $params['prodtype']; } else $this->fields['prodtype']="0";
                if (isset($params['manuf'])) { $this->fields['manuf']=$params['manuf']; $this->manuf = $params['manuf']; } else $this->fields['manuf']="0";
                if (isset($params['gen'])) $this->gen = $params['gen']; else $this->gen="0";
                if (isset($params['str'])) $this->storeidreport = $params['str']; else $this->storeidreport=null;
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
            $currUser = getCurrUser();
//            $menuitem = "bnewbatch";
            $menuitem = "storesales";
            include "sidemenu.".$currUser->usertype.".php";
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
    $query = "select * from it_categories ";
    $objs= $db->fetchObjectArray($query);
    if($objs){ foreach($objs as $obj){ $categories[$obj->id] = $obj->name;}}
    //create brands array
    $query = "select * from it_brands ";
    $objs= $db->fetchObjectArray($query);
    if($objs){ foreach($objs as $obj){ $brands[$obj->id] = $obj->name;}}
    //create style array
    $query = "select * from it_styles ";
    $objs= $db->fetchObjectArray($query);
    if($objs){ foreach($objs as $obj){ $styles[$obj->id] = $obj->name;}}
    //create size array
    $query = "select * from it_sizes ";
    $objs= $db->fetchObjectArray($query);
    if($objs){ foreach($objs as $obj){ $sizes[$obj->id] = $obj->name;}}
    //create mfg by array
    $query = "select * from it_mfg_by ";
    $objs= $db->fetchObjectArray($query);
    if($objs){ foreach($objs as $obj){ $mfg_by[$obj->id] = $obj->name;}}
    //create prod_type array
    $query = "select * from it_prod_types ";
    $objs= $db->fetchObjectArray($query);
    if($objs){ foreach($objs as $obj){ $prod_typs[$obj->id] = $obj->name;}}
    //create fabric_type array
    $query = "select * from it_fabric_types ";
    $objs= $db->fetchObjectArray($query);
    if($objs){ foreach($objs as $obj){ $fabric_types[$obj->id] = $obj->name;}}
    //create material array
    $query = "select * from it_materials ";
    $objs= $db->fetchObjectArray($query);
    if($objs){ foreach($objs as $obj){ $materials[$obj->id] = $obj->name;}}
    ?>
    <div class="box" style="clear:both;">
	<fieldset class="login">
	<legend>Generate Sales Report</legend>
	<p>Select values for the various fields below. Some fields allow you to pick only a single value, others allow you to pick multiple values.</p>
        <form action="" method="" onsubmit="reloadreport(); return false;">
		<div class="grid_12">
		<div class="grid_4">
                    <b>Select Store*:</b><br/>
        	<select name="store" id="store" data-placeholder="Choose Store" class="chzn-select" multiple style="width:100%;">
                <?php if( $this->storeidreport == -1 ){
                                   $defaultSel = "selected";
                             }else{ $defaultSel = ""; } ?>
                <option value="-1" <?php echo $defaultSel;?>>All Stores</option> 
<?php
$objs = $db->fetchObjectArray("select * from it_codes where usertype=4 order by store_name");

if($this->storeidreport == "-1"){
    $storeid = array(); $write_htm = false;   
    $allstoreArrays=$db->fetchObjectArray("select id from it_codes where usertype = 4");
    foreach($allstoreArrays as $storeArray){
        foreach($storeArray as $store){
            array_push($storeid,$store);
        }
    }
}else{
  $storeid = explode(",",$this->storeidreport);  
}
//print_r($allst);
//print_r($storeid);
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
		<div class="grid_4">
                <span style="font-weight:bold;">Date Filter : </span></br> <input size="17" type="text" id="dateselect" name="dateselect" value="<?php echo $this->dtrange; ?>" /> (Click to see date options)
		</div>
                <div class="grid_4">
		Report type*:<br />
        	<input type="radio" name="report" value="billwise" <?php if ($this->gen==1) echo "checked"; ?> onclick="showgeneral();">General bill wise sale summary<br>
                <input type="radio" name="report" value="itemwise" <?php if ($this->gen==0) echo "checked"; ?> onclick="showitemwise();">Bill+item wise sale summary (customize)
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
                                          <option value="store">Store Name</option> 
<!--                                          <option value="transaction">Transaction Type</option>-->
                                          <option value="itemctg">Item Category</option>
                                          <option value="designno">Item Design no</option>
                                          <option value="itemmrp">Item Price</option>
                                          <option value="barcode">Barcode</option>
                                          <option value="linediscountper">Line discount %</option>
                                          <option value="linediscountval">Line discount value</option>
                                          <option value="ticketdiscountper">Ticket discount %</option>
                                          <option value="ticketdiscountval">Ticket discount value</option>
<!--                                          <option value="totaldiscount">Total discount</option>-->
                                          <option value="tax">Tax Incl</option>
                                          <option value="brand">Brand</option>
<!--                                          <option value="category">Category</option> -->
                                          <option value="style">Style</option>
                                          <option value="size">Size</option> 
                                          <option value="fabric">Fabric Type</option> 
                                          <option value="material">Material</option> 
                                          <option value="prodtype">Production Type</option> 
                                          <option value="manuf">Manufactured By</option> 
                                    </select>
                                </label></td>
                                <td colspan="1" rowspan="3" style="vertical-align:middle;">
<!--                                        <td colspan="2" align="center"><label>-->
                                        <input name="btnRight" type="button" id="btnRight" value="&gt;&gt;" onClick="javaScript:moveToRightOrLeft(1);">
<!--                                        </label></td>-->
<!--                                        <td colspan="2" align="left"><label>-->
                                    <br/><br/>
                                    <input name="btnLeft" type="button" id="btnLeft" value="&lt;&lt;" onClick="javaScript:moveToRightOrLeft(2);">
<!--                                        </label></td>-->
                                </td>
                                    <td rowspan="3" colspan="2" align="left">
                                        <select name="selectRight" multiple size="10" style="width:200px;" id="selectRight">
                                        <option value="date" selected>Date</option>
                                        <option value="billno" selected>Bill no</option>
                                        <option value="billtype" selected>Bill Type</option>
                                        <option value="itemqty">Item Quantity</option>
                                        <option value="itemvalue">Sold Price</option>
                                        <option value="totalvalue">Total Value</option>
                                        </select>
                                    </td>
                                </tr>
                        </table>
                    </div>
<!--		</div>-->
                </div>
            
            <div class="grid_12" id="generalselection" style="display:none;">
		<div class="grid_12" >
		Fields in the report:<br />
                (Date/Bill no/Bill quantity/Bill amount/Tax/Bill discount value/Bill discount %//Voucher/Store name)
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
    <?php if (isset($this->storeidreport)) { //22 fields ?>
    <div class="box grid_12" style="margin-left:0px; overflow:auto; height:500px;">
        <?php 
        $queryfields = "";
        $tableheaders = "";
        $dtarr = explode(" - ", $this->dtrange);
        $_SESSION['storeid'] = $this->storeidreport;
	if (count($dtarr) == 1) {
		list($dd,$mm,$yy) = explode("-",$dtarr[0]);
		$sdate = "$yy-$mm-$dd";
		//$dQuery = " and date(o.bill_datetime) = '$sdate' ";
                $dQuery = " and o.bill_datetime >= '$sdate 00:00:00' and o.bill_datetime <= '$sdate 23:59:59' ";
	} else if (count($dtarr) == 2) {
		list($dd,$mm,$yy) = explode("-",$dtarr[0]);
		$sdate = "$yy-$mm-$dd";
		list($dd,$mm,$yy) = explode("-",$dtarr[1]);
		$edate = "$yy-$mm-$dd";
		//$dQuery = " and date(o.bill_datetime) >= '$sdate' and date(o.bill_datetime) <= '$edate' ";
                $dQuery = " and o.bill_datetime >= '$sdate 00:00:00' and o.bill_datetime <= '$edate 23:59:59' ";
	} else {
		$dQuery = "";
	}
        if ($this->gen!=1) {
            $totTotalValue=0;$totAmt="";
            $newfname = "Bill_itemwise_".$sdate."_".$edate.".csv";           
            $tableheaders = "Bill Type:"; $queryfields .= " o.tickettype,";
            for ($x=1;$x<23;$x++) {
                foreach ($this->fields as $field => $seq) {
                    if ($seq==$x) {
                        if ($field=="date") {$tableheaders.="Date:"; $queryfields .= " o.bill_datetime as date,";} // DATE_FORMAT(o.bill_datetime,'%d/%m/%Y')
                        if ($field=="billno") {$tableheaders.="Bill No.:";  $queryfields .= "o.bill_no,";}
                        if ($field=="store") {$tableheaders.="Store Name:"; $queryfields .= "c.store_name,";}
                        if ($field=="itemctg") {$tableheaders.="Category:"; $queryfields .= "i.ctg_id as itemctg,";}
                        if ($field=="designno") {$tableheaders.="Design No.:"; $queryfields .= "i.design_no,";}
                        if ($field=="itemmrp") {$tableheaders.="MRP (Rs):"; $queryfields .= "i.MRP,";}
                        if ($field=="itemvalue") { $tableheaders .="Sold Price:"; $queryfields .="(case when (o.discount_pct is not NULL) then (((100-o.discount_pct)/100)*oi.price) else oi.price end) as itemvalue,"; }
//                        if ($field=="itemqty") {$tableheaders.="Quantity:"; $queryfields .= "sum(oi.quantity) as quantity,";}
                        if ($field=="itemqty") {$tableheaders.="Quantity:"; $queryfields .= "(case when (o.tickettype = 0 ) then sum(oi.quantity) else 0 end) as quantity,";}                        
                        //if($field=="totalvalue"){$tableheaders.="Total:"; $queryfields .= "(case when (o.discount_pct is not NULL) then ((((100-o.discount_pct)/100)*oi.price) * (case when (o.tickettype = 0) then oi.quantity else 0 end )) else oi.price*(case when (o.tickettype = 0) then oi.quantity else 0 end ) end) as totalvalue,";}
                        if($field=="totalvalue"){$tableheaders.="Total:"; $queryfields .= "(case when (o.discount_pct is not NULL) then ((((100-o.discount_pct)/100)*oi.price) * (case when (o.tickettype = 0) then sum(oi.quantity) else 0 end )) else oi.price*(case when (o.tickettype = 0) then sum(oi.quantity) else 0 end ) end) as totalvalue,";}
                        if ($field=="barcode") {$tableheaders.="Barcode:"; $queryfields .= "oi.barcode,";}
                        if ($field=="linediscountper") {$tableheaders.="Line Discount %:"; $queryfields .= "oi.discount_pct as itmdiscp,";}
                        if ($field=="linediscountval") {$tableheaders.="Line Discount Value:"; $queryfields .= "oi.discount_val as itmdiscv,";}
                        if ($field=="ticketdiscountper") {$tableheaders.="Ticket Discount %:"; $queryfields .= "o.discount_pct as totdiscp,";}
                        if ($field=="ticketdiscountval") {$tableheaders.="Ticket Discount Value:"; $queryfields .= "o.discount_val as totdiscv,";}
                        if ($field=="tax") {$tableheaders.="Tax on Bill Amt:"; $queryfields .= "o.tax,";}
                        if ($field=="brand") {$tableheaders.="Brand:"; $queryfields .= "i.brand_id as brand,";}
                        if ($field=="style") {$tableheaders.="Style:"; $queryfields .= "i.style_id as style,";}
                        if ($field=="size") {$tableheaders.="Size:"; $queryfields .= "i.size_id as size,";}
                        if ($field=="fabric") {$tableheaders.="Fabric:"; $queryfields .= "i.fabric_type_id as fabric,";}
                        if ($field=="material") {$tableheaders.="Material:"; $queryfields .= "i.material_id as material,";}
                        if ($field=="prodtype") {$tableheaders.="Production Type:"; $queryfields .= "i.prod_type_id as prodtype,";}
                        if ($field=="manuf") {$tableheaders.="Mfg By:"; $queryfields .= "i.mfg_id as manuf,";}
                    }
                }
            }
            $queryfields = substr($queryfields, 0, -1);
            $storeClause="";
            if($this->storeidreport == "-1"){               
             // $storeClause = " select id from it_codes  where usertype = ".UserType::Dealer ;
                $storeClause = " c.usertype = ".UserType::Dealer ;
            }else{              
               //$storeClause = $this->storeidreport;
                $storeClause = " o.store_id in ( $this->storeidreport ) ";
            }
            $query = "select $queryfields";
          //  $query .= " from it_orders o,it_order_items oi, it_items i, it_categories ctg, it_brands br, it_styles st, it_sizes si, it_fabric_types fb, it_materials mt, it_prod_types pr, it_mfg_by mfg , it_codes c where o.store_id in ( $storeClause ) $dQuery and oi.order_id=o.id and i.id = oi.item_id and  ctg.id=i.ctg_id and br.id=i.brand_id and st.id=i.style_id and si.id=i.size_id and pr.id=i.prod_type_id and mt.id=i.material_id and fb.id=i.fabric_type_id and mfg.id=i.mfg_id and o.store_id = c.id  group by $gClause o.bill_no,oi.item_id order by o.bill_datetime";
              $query .= " from it_orders o,it_order_items oi, it_items i, it_codes c where $storeClause $dQuery and oi.order_id=o.id and i.id = oi.item_id and  o.store_id = c.id  group by o.store_id, o.bill_no,oi.item_id order by o.bill_datetime";
            //print $query; //and c.id in ( $storeClause)
            $result = $db->execQuery($query);
        } else if ($this->gen==1) {
            $newfname = "Billwise_".$sdate."_".$edate.".csv";  
            $totTotalValue="";$totAmt=0;
            $storeClause="";
            if($this->storeidreport == "-1"){                
                $storeClause = " c.usertype = ".UserType::Dealer ;
//              $storeClause = " select id from it_codes c where usertype = ".UserType::Dealer ;
            }else{                
                $storeClause = " o.store_id in ( $this->storeidreport ) ";
            }
            $tableheaders = "Date:Bill No:Bill Type:Bill Quantity:Bill Amount:Tax:Bill Discount Value:Bill Discount %:Voucher:Store Name";
            //$query2 = "select DATE_FORMAT(o.bill_datetime,'%d/%m/%Y') as bill_datetime,o.bill_no,o.tickettype,o.quantity,o.amount,o.tax,o.discount_val,o.discount_pct,o.voucher_amt from it_orders o where o.store_id in ( $storeClause ) $dQuery group by $gClause o.bill_no order by bill_datetime";
            $query2 = "select o.bill_datetime ,o.bill_no,o.tickettype,o.quantity,o.amount,o.tax,o.discount_val,o.discount_pct,o.voucher_amt,c.store_name from it_orders o , it_codes c where $storeClause  and  o.store_id = c.id  $dQuery group by o.store_id, o.bill_no order by bill_datetime";
            //echo $query2;
            $result = $db->execQuery($query2);
        }
?>
        <br /><div style='margin-left:40px; padding-left:15px; height:24px;width:130px;border: solid gray 1px;background:#F5F5F5;padding-top:4px;'>
            <a href='<?php echo "tmp/storesales.php?output=$newfname" ;?>' title='Export table to CSV'><img src='images/excel.png' width='20' hspace='3' style='margin-bottom:-6px;' /> Export To Excel</a>
        </div><br />
        
<?php 
   $totqty=0;$totsp=0;
    if (isset($result)) {        
        $fp = fopen('tmp/StoreSales.csv', 'w');
        if($write_htm){
         $fp2 = fopen ('tmp/storesales.htm', 'w');
        } 
        if ($fp) {
            $trow = array(); $tcell = array(); 
            //write header info
            if($write_htm){
             fwrite($fp2,"<table width='100%' style='overflow:auto;'><thead><tr>");
            } 
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
                   if ($field=="tax") {
                       $value = sprintf('%.2f',$value);
                   } else if ($field =="tickettype" ) {
                       if ($value=='3') {
                           $value='Cancelled';
                       } else if ($reportrows->quantity < 0) {
                           $value='Return';
                       } else {
                           $value='Sale';
                       }
                   }else if($field == "date"){                                              
                       $t_str = ddmmyy2($value);
                       $value = $t_str;
                   }else if($field == "quantity"){
                       $totqty += $value;
                   }else if($field == "itemvalue"){
                       $totsp += $value;
                   }else if($field == "totalvalue"){
                       $totTotalValue += $value;
                   }else if($field == "amount"){
                       $totAmt += $value;
                   }else if($field == "itemctg"){
                       $t_str = $categories[$value];
                       $value = $t_str;
                   }else if($field == "brand"){
                       $t_str = $brands[$value];
                       $value = $t_str;
                   }else if($field == "style"){
                       $t_str = $styles[$value];
                       $value = $t_str;
                   }else if($field == "size"){
                       $t_str = $sizes[$value];
                       $value = $t_str;
                   }else if($field == "fabric"){
                       $t_str = $fabric_types[$value];
                       $value = $t_str;
                   }else if($field == "material"){
                       $t_str = $materials[$value];
                       $value = $t_str;
                   }else if($field == "prodtype"){
                       $t_str = $prod_typs[$value];
                       $value = $t_str;
                   }else if($field == "manuf"){
                       $t_str = $mfg_by[$value];
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
            if($write_htm){
                fwrite($fp2,"<tr><td><b>Total</b></td><td></td><td></td><td><b>$totqty</b></td><td><b>$totAmt</b></td><td><b>$totTotalValue</b></td></tr>");
                fwrite ($fp2,"</tbody></table>");
                fclose ($fp2); 
            }
            fclose ($fp); 
            if($write_htm){
                $table = file_get_contents("tmp/storesales.htm");
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
<script src="js/chosen/chosen.jquery.js" type="text/javascript"></script>
<script type="text/javascript"> </script>
<script type="text/javascript">
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
        
        var radio = $('input[name=report]:checked').val()
        if (radio=='billwise') {
            showgeneral();
        } else {
            showitemwise();
        }
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
//        var storeid = $('#store').val();
//        alert(storeid);
//       // $('select.foo option:selected').val(); commented
       var reporttype=$('input[name=report]:radio:checked').val();
       //alert(reporttype);commented
       $('#selectRight option').attr('selected', 'selected');
       var storeid = $('#store').val();
       //alert(storeid);commented
       if (storeid!="") {
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
                window.location.href="report/storesales/str="+storeid+append;
           } else {
                window.location.href="report/storesales/str="+storeid+"/gen=1";
           }
       } else {
           alert("please select a store to genereate a report");
       }
    }
</script>
<?php
	}
}
?>
