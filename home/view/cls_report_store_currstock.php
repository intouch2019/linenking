<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");
require_once "lib/core/strutil.php";
require_once "session_check.php";

class cls_report_store_currstock extends cls_renderer{
        
        var $currUser;
        var $userid;
        var $storeidreport = null;
        var $params;
        var $itemctg; var $designno; var $itemmrp; var $barcode;
        var $category; var $style; var $size; var $fabric; var $material;
        var $prodtype; var $manuf; var $gen; var $itemqty; var $itemvalue; var $totalvalue;
        var $storeloggedin = -1;
        var $fields = array();
       
        function __construct($params=null) {
		//parent::__construct(array(UserType::Admin, UserType::CKAdmin,  UserType::Manager));
		$this->currUser = getCurrUser();
		$this->userid = $this->currUser->id;
                if (isset($params['str'])) $this->storeidreport = $params['str']; else $this->storeidreport=null;
                if (isset($params['store'])) { $this->fields['store']=$params['store']; $this->store = $params['store']; } else $this->fields['store']="0";               
                if (isset($params['itemctg'])) { $this->fields['itemctg']=$params['itemctg']; $this->itemctg = $params['itemctg']; } else $this->fields['itemctg']="0";
                if (isset($params['designno'])) { $this->fields['designno']=$params['designno']; $this->designno = $params['designno']; } else $this->fields['designno']="0";
                if (isset($params['barcode'])) { $this->fields['barcode']=$params['barcode']; $this->barcode = $params['barcode']; } else $this->fields['barcode']="0";                
                if (isset($params['style'])) { $this->fields['style']=$params['style']; $this->style = $params['style']; } else $this->fields['style']="0";
                if (isset($params['size'])) { $this->fields['size']=$params['size']; $this->size = $params['size']; } else $this->fields['size']="0";
                if (isset($params['fabric'])) { $this->fields['fabric']=$params['fabric']; $this->fabric = $params['fabric']; } else $this->fields['fabric']="0";
                if (isset($params['material'])) { $this->fields['material']=$params['material']; $this->material = $params['material']; } else $this->fields['material']="0";
                if (isset($params['prodtype'])) { $this->fields['prodtype']=$params['prodtype']; $this->prodtype = $params['prodtype']; } else $this->fields['prodtype']="0";
                if (isset($params['manuf'])) { $this->fields['manuf']=$params['manuf']; $this->manuf = $params['manuf']; } else $this->fields['manuf']="0";
                if (isset($params['itemqty'])) { $this->fields['itemqty']=$params['itemqty']; $this->itemqty = $params['itemqty']; } else $this->fields['itemqty']="0";
                if (isset($params['itemmrp'])) { $this->fields['itemmrp']=$params['itemmrp']; $this->itemmrp = $params['itemmrp']; } else $this->fields['itemmrp']="0";
                if (isset($params['totalvalue'])) { $this->fields['totalvalue']=$params['totalvalue']; $this->totalvalue = $params['totalvalue']; } else $this->fields['totalvalue']="0";  
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
                window.location.href="report/store/currstock/str="+storeid+append;
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
//            $currUser = getCurrUser();
            $menuitem = "storecurrStock";
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
	<legend>Generate Store Current Stock Report</legend>	
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
 $objs = array();
// if($this->currUser->usertype == UserType::BHMAcountant ) {
//$objs = $db->fetchObjectArray("select * from it_codes where usertype=".UserType::Dealer." and  (is_bhmtallyxml=1 or store_type=3) order by store_name");
// }else{
//     $objs = $db->fetchObjectArray("select * from it_codes where usertype=".UserType::Dealer." order by store_name");
// }
if($this->currUser->usertype == UserType::Dealer ) {
$objs = $db->fetchObjectArray("select * from it_codes where usertype=".UserType::Dealer." and  id= ".getCurrUser()->id." order by store_name");
 }else{
     $objs = $db->fetchObjectArray("select id,store_name from it_codes where usertype=4 and id in (select store_id from executive_assign where exe_id=".getCurrUser()->id." ) order by store_name");    
 }
if($this->storeidreport == "-1"){
    $storeid = array();
    $allstoreArrays=array();
//     if($this->currUser->usertype == UserType::BHMAcountant ) {
//     $allstoreArrays=$db->fetchObjectArray("select id from it_codes where usertype = ".UserType::Dealer." and  (is_bhmtallyxml=1 or store_type=3)") ;
//      }
//      else{
//        $allstoreArrays=$db->fetchObjectArray("select id from it_codes where usertype = ".UserType::Dealer);  
//      } 
    $allstoreArrays = $db->fetchObjectArray("select id from it_codes where usertype = " . UserType::Dealer ."and id in (select store_id from executive_assign where exe_id=".getCurrUser()->id." )");
                
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
		</div>
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
                                          <option value="itemctg">Item Category</option>
                                          <option value="designno">Item Design no</option>
                                           <option value="barcode" selected>Barcode</option>                                                                                  
                                          <option value="style">Style</option>
                                          <option value="size">Size</option> 
                                          <option value="fabric">Fabric Type</option> 
                                          <option value="material">Material</option> 
                                          <option value="prodtype">Production Type</option> 
                                          <option value="manuf">Manufactured By</option> 
                                          <option value="itemmrp">Item Price</option>
                                    </select>
                                </label></td>
                                <td colspan="1" rowspan="3" style="vertical-align:middle;">
                                        <input name="btnRight" type="button" id="btnRight" value="&gt;&gt;" onClick="javaScript:moveToRightOrLeft(1);">
                                    <br/><br/>
                                    <input name="btnLeft" type="button" id="btnLeft" value="&lt;&lt;" onClick="javaScript:moveToRightOrLeft(2);">
                                </td>
                                    <td rowspan="3" colspan="2" align="left">
                                        <select name="selectRight" multiple size="10" style="width:200px;" id="selectRight">
                                        <option value="store" selected>Store</option>                                                                              
                                        <option value="itemqty">Item Current Quantity</option>
                                        <option value="totalvalue">Total Value</option>
                                        </select>
                                    </td>
                                </tr>
                        </table>
                    </div>
                </div>
            
            
            <div class="grid_12" id="generalselection" style="display:none;">
		<div class="grid_12" >
		Fields in the report:<br />               
                (Store Name/Barcode/Quantity)
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
    <?php if (isset($this->storeidreport)) { //12 fields ?>
    <div class="box grid_12" style="margin-left:0px; overflow:auto; height:500px;">
        <?php 
            $queryfields = "";
            $tableheaders = "";
            $group_by = array();$gClause="";
            $storeClause="";
            if($this->storeidreport == "-1"){
             
              $storeClause = "select store_id from executive_assign where exe_id=".getCurrUser()->id." " ;
               
            }else{
                $storeClause = $this->storeidreport;
            }
//            $tableheaders = "Store:Barcode:Current Quantity";
            for ($x=1;$x<14;$x++) {
                foreach ($this->fields as $field => $seq) {
                    if ($seq==$x) {                        
                        if ($field=="store") {$tableheaders.="Store Name:"; $queryfields .= "c.store_name,"; $group_by[] = "c.store_name";}                       
                        if ($field=="itemctg") {$tableheaders.="Category:"; $queryfields .= "ctg.name as ctgname,";$group_by[] = "ctgname";}
                        if ($field=="designno") {$tableheaders.="Design No.:"; $queryfields .= "i.design_no,";$group_by[] = "i.design_no";}
                        if ($field=="barcode") {$tableheaders.="Barcode:"; $queryfields .= "cs.barcode,";$group_by[] = "cs.barcode";}                        
                        if ($field=="brand") {$tableheaders.="Brand:"; $queryfields .= "br.name as brand,";$group_by[] = "brand";}
                        if ($field=="style") {$tableheaders.="Style:"; $queryfields .= "st.name as style,";$group_by[] = "style";}
                        if ($field=="size") {$tableheaders.="Size:"; $queryfields .= "si.name as size,";$group_by[] = "size";}
                        if ($field=="fabric") {$tableheaders.="Fabric:"; $queryfields .= "fb.name as fabric,";$group_by[] = "fabric";}
                        if ($field=="material") {$tableheaders.="Material:"; $queryfields .= "mt.name as material,";$group_by[] = "material";}
                        if ($field=="prodtype") {$tableheaders.="Production Type:"; $queryfields .= "pr.name as prodtype,";$group_by[] = "prodtype";}
                        if ($field=="manuf") {$tableheaders.="Mfg By:"; $queryfields .= "mfg.name as manuf,";$group_by[] = "manuf";}
                        if ($field=="itemqty") {$tableheaders.="Quantity:"; $queryfields .= "sum(cs.quantity) as quantity,";} //$groupby .= "quantity,";
                        if ($field=="itemmrp") {$tableheaders.="MRP (Rs):"; $queryfields .= "i.MRP,";$group_by[] = "i.MRP";}                        
                        if($field=="totalvalue"){$tableheaders.="Total:"; $queryfields .= "sum(i.MRP * cs.quantity) as totalvalue,";  }
                    }
                }
            }
            $queryfields = substr($queryfields, 0, -1);
             if(!empty($group_by)){ 
                $gClause = " group by ".implode(",", $group_by);
             //$groupby = substr($groupby, 0, -1);
            }else if($this->currUser->usertype==UserType::Dealer && empty($group_by)){
                $queryfields .= "c.store_name,sum(cs.quantity) as quantity,sum(i.MRP * cs.quantity) as totalvalue";                
            }
            //$query2 = "select c.store_name , cs.barcode, sum(cs.quantity) as quantity from it_codes c , it_current_stock cs where c.id = cs.store_id and cs.store_id in ( $storeClause ) group by cs.barcode ";
            //echo $query2;
            $query = "select $queryfields";
            $query .= " from it_codes c,it_current_stock cs, it_items i, it_categories ctg, it_brands br, it_styles st, it_sizes si, it_fabric_types fb, it_materials mt, it_prod_types pr, it_mfg_by mfg  where c.id = cs.store_id and cs.store_id in ( $storeClause ) and cs.barcode = i.barcode and  ctg.id=i.ctg_id and br.id=i.brand_id and st.id=i.style_id and si.id=i.size_id and pr.id=i.prod_type_id and mt.id=i.material_id and fb.id=i.fabric_type_id and mfg.id=i.mfg_id and cs.store_id = c.id and cs.quantity !=0 and ctg.id!=65 $gClause ";
           // echo $query;
            
            $result = $db->execQuery($query);
                    

?>
        <br /><div style='margin-left:40px; padding-left:15px; height:24px;width:130px;border: solid gray 1px;background:#F5F5F5;padding-top:4px;'>
        <a href='tmp/StoreCurrStock.csv' title='Export table to CSV'><img src='images/excel.png' width='20' hspace='3' style='margin-bottom:-6px;' /> Export To Excel</a>
        </div><br />
        
<?php 
    $totqty =0 ; $totTotalVal=0;
    if (isset($result)) { 
        $fp = fopen('tmp/StoreCurrStock.csv', 'w');
        $fp2 = fopen ('tmp/storecurrstock.htm', 'w');
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
                   if($field=="totalvalue"){$totTotalVal += $value;}
                   $tcell[] .= $value;
                   fwrite($fp2,"<td>$value</td>");
                }
                fputcsv($fp, $tcell,',',chr(0));
                fwrite($fp2,"</tr>");
            } 
            fwrite($fp2,"<tr><td><b>Total</b></td><td><b>$totqty</b></td><td><b>$totTotalVal</b></td></tr>");
            fwrite ($fp2,"</tbody></table>");
            fclose ($fp); fclose ($fp2); 
            $table = file_get_contents("tmp/storecurrstock.htm");
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
<script type="text/javascript"> </script>
<script type="text/javascript">

</script>
<?php
	}
}
?>