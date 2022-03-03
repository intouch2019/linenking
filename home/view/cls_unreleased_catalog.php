<?php
require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "session_check.php";
require_once "lib/orders/clsOrders.php";
require_once "lib/items/clsItems.php";

class cls_unreleased_catalog extends cls_renderer {

    var $params;
    var $currStore;
    var $ctg;
    var $result;
    var $des_code="";
    var $MRP;
    var $size;
    var $size_group;
    var $brand="";

    function __construct($params=null) {
        $this->currStore = getCurrUser();
        $this->params = $params;
        if (isset($params['ctg']))
            { $this->ctg = $params['ctg']; }
        else 
            { $this->ctg = null; }
        if (isset($params['dno']))
            $this->des_code = $params['dno'];
        if (isset($params['mrp']))
            $this->MRP = $params['mrp'];
        if (isset($params['size']))
            $this->size = $params['size'];
        if (isset($params['brand']))
            $this->brand = $params['brand'];
    }

    function extraHeaders() {
        if (!$this->currStore) {
            ?>
<h2>Session Expired</h2>
Your session has expired. Click <a href="">here</a> to login.
            <?php
            return;
        }
        ?>
<script type="text/javascript" src="js/expand.js"></script>
<link rel="stylesheet" type="text/css" href="css/dark-glass/sidebar.css" />
<script type="text/javascript" src="js/sidebar/jquery.sidebar.js"></script>
<link rel="stylesheet" href="css/prettyPhoto.css" type="text/css" media="screen" title="prettyPhoto main stylesheet" charset="utf-8" />
<script src="js/prettyPhoto/jquery.prettyPhoto.js" type="text/javascript" charset="utf-8"></script>
<link rel="stylesheet" href="js/chosen/chosen.css" />
<script src="js/chosen/chosen.jquery.js" type="text/javascript"></script>
<script type="text/javascript">
   
    $(function() {
        $("#results").hide();
        //accodian dropdown
        // --- Using the default options:
        //$("h2.expand").toggler();
        // --- Other options:
        //$("h2.expand").toggler({method: "toggle", speed: "slow"});
        //$("h2.expand").toggler({method: "toggle"});
        //$("h2.expand").toggler({speed: "fast"});
        //$("h2.expand").toggler({method: "fadeToggle"});
        $("h2.expand").toggler({method: "slideFadeToggle"});
        $("#content").expandAll({trigger: "h2.expand"});
 
        //pretty photo pop up
	$("a[rel^='prettyPhoto']").prettyPhoto({animation_speed:'fast',slideshow:3000, hideflash: true});
        

        
        $(".chzn-select").chosen(); $(".chzn-select-deselect").chosen({allow_single_deselect:true});
    });
    
      window.onload = function() {
		var frm = document.getElementById("allDesignsGRNR");
		frm.onsubmit = function() {
			document.getElementById("btnSubmit").disabled = true;
			
		}
	}
  
    function releaseAllDivCellValues(){
        var flg = 0                               
        var params = $("#allDesignsGRNR").serializeArray();
        //alert(params);
        var sz = getLength(params);
        var element_nm="";
        var element_val="";        
            for(var k=0;k<sz;k++){ // iterate within single form elements   
                var str = params[k];               
                element_nm = str.name;
               // alert("ELEMENT NAME: "+element_nm);
                var s ="[item][item_";                   
               // if(element_nm.match("^".s)){
               //if (str.indexOf("Yes") >= 0)
               if(element_nm.indexOf(s) >= 0){
                    //alert("INSIDE IF");
                    element_val = str.value;   
                    // alert("ELEMENT VAL: "+element_val);
                     var arr1 = element_nm.split("]");                   
                        var arr2 = arr1[2].split("_");
                        var grn_qty = arr2[2];                    
                        if(grn_qty > 0){                                            
                          document.getElementById(element_nm).value = grn_qty;
                          flg = 1;    
                       }else{flg =0 ;}                     
                }

            }
        if(flg == 0){
            alert("Where GRN Release QTY is 0 , it wont get populated in the corresponding boxes");
        }
    }
    
    function resetAllDivCellValues(){
        var flg = 0                               
        var params = $("#allDesignsGRNR").serializeArray();
        var sz = getLength(params);
        var element_nm="";
        var element_val="";        
            for(var k=0;k<sz;k++){ // iterate within single form elements   
                var str = params[k];               
                element_nm = str.name;                
                var s ="[item][item_";                               
                //if(element_nm.match("^".s)){
                 if(element_nm.indexOf(s) >= 0){
                    element_val = str.value;                   
                     var arr1 = element_nm.split("]");
                     var arr2 = arr1[2].split("_");
                     var grn_qty = arr2[2];                    
                     if(grn_qty > 0){                                            
                       document.getElementById(element_nm).value = "";
                       flg = 1;    
                    }else{flg =0 ;} 
                }

            }
        if(flg == 0){
            alert("Where GRN Release QTY is 0 , it wont get populated in the corresponding boxes");
        }
    }
    
    
    function releaseDivCellValues(designid){
        var flg = 0                               
        var params = $("#allDesignsGRNR").serializeArray();
        var sz = getLength(params);
        var element_nm="";
        var element_val="";        
            for(var k=0;k<sz;k++){ // iterate within single form elements   
                var str = params[k];               
                element_nm = str.name;                
                var s ="designid["+designid+"][item][item_";                               
                if(element_nm.startsWith(s)){
                    element_val = str.value;                   
                     var arr1 = element_nm.split("]");
                     var arr2 = arr1[2].split("_");
                     var grn_qty = arr2[2];                    
                     if(grn_qty > 0){                                            
                       document.getElementById(element_nm).value = grn_qty;
                       flg = 1;    
                    }else{flg =0 ;} 
                }

            }
        if(flg == 0){
            alert("Where GRN Release QTY is 0 , it wont get populated in the corresponding boxes");
        }
    }
    
    
    function resetDivCellValues(designid){
        var flg = 0               
        var params = $("#allDesignsGRNR").serializeArray();
        var sz = getLength(params);
        var element_nm="";
        var element_val="";        
            for(var k=0;k<sz;k++){ // iterate within single form elements   
                var str = params[k];               
                element_nm = str.name;                
                var s ="designid["+designid+"][item][item_";               
                if(element_nm.startsWith(s)){
                    element_val = str.value;                   
                     var arr1 = element_nm.split("]");
                     var arr2 = arr1[2].split("_");
                     var grn_qty = arr2[2];                    
                     if(grn_qty > 0){                                           
                       document.getElementById(element_nm).value = "";
                        flg = 1;    
                    }else{flg =0 ;} 
                }

            }

        if(flg == 0){
            alert("Where GRN Release QTY is 0 , it wont get populated in the corresponding boxes");
        }
    }
      
    var getLength = function(obj) {
    var i = 0, key;
    for (key in obj) {
        if (obj.hasOwnProperty(key)){
            i++;
        }
    }
    return i;
}
    
function grnGrid(){
    var designids = $("#designos").val();
//    alert(designids);
    window.location.href = "unreleased/catalog/dno="+designids;
}

function txtValChk(t_id){
   var entered_qty = parseFloat(document.getElementById(t_id).value);
   //alert("TXTBOX ID: "+t_id+" , VAL: "+entered_qty); 
   var b = isNaN(entered_qty);    
   if(b){
      //either space or delete button pressed   
   }else{
      //fetch grn allowed 
      var arr1 = t_id.split("]");
      var arr = arr1[2].split("_");
      var grn_limit = arr[2];
      if(entered_qty < 0){
        alert("Release quantity cannot be negative");
        document.getElementById(t_id).value = "";
        }else if(entered_qty > grn_limit){
            alert("Release quantity cannot be greater then Grn Qty");
            document.getElementById(t_id).value = "";
        }
   }
}
</script>
<!--<link rel="stylesheet" href="js/chosen/chosen.css" />-->
<!--<link rel="stylesheet" href="<?php // CdnUrl('css/bigbox.css'); ?>" type="text/css" />-->
    <?php
    }

    //extra-headers close

    public function pageContent() {
//        print_r($this->params);
        $menuitem = "unreleasedCatalog";
        include "sidemenu.".$this->currStore->usertype.".php";
//        $_SESSION['rowno'] = 0;
         if(isset($this->des_code) && trim($this->des_code)!=""){
         $designarr = explode(",",$this->des_code);
        }else{
            $designarr = array();
        }
        //print_r($designarr);
        $formResult = $this->getFormResult();
        ?>

<div class="grid_10">
     <!--select s1.*,s2.name from it_ck_sizes s1,it_sizes s2, it_items i  where s1.size_id = s2.id and s2.id = i.size_id and i.ctg_id = s1.ctg_id and i.mrp =  650 and s1.ctg_id='10' group by s2.id order by s1.sequence-->
            <?php            
            $db = new DBConn();
            $clsItems = new clsItems();
            $storeid = getCurrUserId();
            $mTab = "";
            $mrpClause = "";
            $styleClause = "";
            $sizeClause = "";

            $category = $clsItems->getCategoryNameFromId($this->ctg);
            $ctg_id = $db->safe($this->ctg);
            ?>
    <div class="grid_3">&nbsp;</div>
    <div class="grid_5">
        <fieldset class="login">
            <legend>Un Released Catalog <?php //echo $category->name; ?></legend>
            <?php 
//                $query = "select distinct(i.brand_id),b.name as brand from it_items i,it_brands b where i.ctg_id='$this->ctg' and i.brand_id=b.id group by brand_id";
//                $allbrands = $db->fetchObjectArray($query);
                
            ?>
         
        <h5>Note: Designs with missing image won't show up in below selection box</h5>
        <fieldset>
        <form name="designSel" id="designSel" style="display:inline;">
            <h5>Select Design(s) : </h5>
            <select id="designos" name="designnos" style="width:75%" class="chzn-select" multiple > 
                <option value="-1">Select</option>
                <?php
                    // $query="select d.id as design_id,d.design_no,c.name,c.id from it_ck_designs d , it_categories c where d.ctg_id = c.id  and image is not null"; //and d.active = 1
                   $query = "select d.id as design_id,d.design_no,c.name,c.id from it_ck_designs d , it_categories c , it_items i  where d.ctg_id = c.id and i.design_id = d.id and i.grn_qty >  0 and i.barcode like '89%'  and d.image is not null and d.image != '' group by d.id " ;
                    //print $query;
                   $objs = $db->fetchObjectArray($query);
                   foreach($objs as $obj){
                       $selected = "";
                       if(! empty($designarr)){
                           if(in_array($obj->design_id, $designarr)){ 
                               $selected = "selected";}
                               else{ $selected = ""; }
                       }
                       $option_value = $obj->design_no." ( ".$obj->name." ) ";
                ?>
                <option value="<?php echo $obj->design_id; ?>" <?php echo $selected; ?>><?php echo $option_value; ?></option>
                <?php } ?>
            </select>
            <br/><br/>
            <input type="button" onclick="javascript:grnGrid()" name="search" id="search" value="Search">
        </form>
        </fieldset>
        <!--</div>-->
        </fieldset>
    </div>
    <div  class="grid_4" id="results" name="results" style="background:#DBECFF; margin-left:10px; width:60%;">Processing. Please wait... <img src="images/loading.gif" /></div>
            <?php
            if(isset($this->des_code) && trim($this->des_code)!="" && trim($this->des_code)!= "-1"){
            // choose high and low prices from filter table for each design category.
            $code = $db->safe($this->des_code);
//            if ($this->des_code) {
//                echo "select i.id,i.MRP,i.brand_id,br.name as brandname, i.prod_type_id, pt.name as prodtype, i.material_id, mt.name as material,  d.image,d.design_no,sum(i.curr_qty) as tot_qty from it_items i,it_ck_designs d,it_brands br,it_prod_types pt,it_materials mt  where i.design_no=$code and d.active=1 and i.design_no=d.design_no and i.ctg_id=$ctg_id and i.brand_id=br.id and i.prod_type_id=pt.id and i.material_id=mt.id  and i.ctg_id=d.ctg_id group by i.design_no,MRP";
            //$qry = "select i.id,i.barcode,i.ctg_id,i.MRP,i.brand_id,br.name as brandname, i.prod_type_id, pt.name as prodtype, i.material_id, mt.name as material,  d.image,d.design_no,sum(i.curr_qty) as tot_qty from it_items i,it_ck_designs d,it_brands br,it_prod_types pt,it_materials mt  where i.design_id=$code and i.design_no=d.design_no  and i.barcode like '89%' and i.brand_id=br.id and i.prod_type_id=pt.id and i.material_id=mt.id  and i.ctg_id=d.ctg_id group by i.design_no,MRP"; //and i.curr_qty > 0
            $qry = "select i.id,i.ctg_id,i.MRP,i.design_no,i.barcode,i.style_id,s.name as style,i.size_id,sz.name as size,"
                    . "i.grn_qty ,i.design_id,d.active,i.design_no,c.name as category, "
                    . "i.brand_id,br.name as brandname, i.prod_type_id, pt.name as prodtype,"
                    . "i.material_id, mt.name as material, d.image,d.design_no,sum(i.grn_qty) as tot_grn_qty"
                    . " from it_items i,it_styles s ,it_sizes sz,it_categories c ,"
                    . "it_ck_designs d,it_brands br,it_prod_types pt,it_materials mt where "
                    . "i.style_id = s.id and i.size_id = sz.id and  i.ctg_id = c.id and "
                    . "i.design_id in ( $this->des_code ) and i.design_no=d.design_no "
                    . "and i.barcode like '89%' and i.brand_id=br.id and i.prod_type_id=pt.id and i.material_id=mt.id"
                    . " and i.ctg_id=d.ctg_id group by i.ctg_id,i.design_no order by i.ctg_id "; //,i.MRP ";
           // $qry = "select i.id,i.ctg_id,i.MRP,i.design_no,i.barcode,i.style_id,s.name as style,i.size_id,sz.name as size,i.mrp,i.grn_qty ,i.design_id,i.design_no,c.name as category, i.brand_id,br.name as brandname, i.prod_type_id, pt.name as prodtype, i.material_id, mt.name as material,  d.image,d.design_no from it_items i,it_styles s ,it_sizes sz,it_categories c ,it_ck_designs d,it_brands br,it_prod_types pt,it_materials mt where i.style_id = s.id and i.size_id = sz.id  and  i.ctg_id = c.id and i.design_id in ( $this->des_code ) and i.design_no=d.design_no  and i.brand_id=br.id and i.prod_type_id=pt.id and i.material_id=mt.id  and i.ctg_id=d.ctg_id and i.grn_qty > 0 ";
//            print $qry;

            //echo $qry;
            $allDesigns = $db->fetchObjectArray($qry); //and i.ctg_id=$ctg_id i.fabric_type_id, ft.name as fabric,and i.fabric_type_id=ft.id ,it_fabric_types ft $brandquery
           
            $row_no = 0;
//            $styleClause = " and s2.id = i.style_id and i.ctg_id = s1.ctg_id and i.mrp = $this->MRP group by s2.id ";
//            $sqry = "select s1.style_id,s2.name as style_name from it_ck_styles s1,it_styles s2 $mTab where s1.ctg_id=$ctg_id and s2.is_active = 1 and s1.style_id=s2.id $styleClause order by s1.sequence"; 
//            print "<br> Style query: ".$sqry." <br/>";
//            $styleobj = $db->fetchObjectArray($sqry);
////            echo "<br/>select s1.style_id,s2.name as style_name from it_ck_styles s1,it_styles s2 where s1.ctg_id=$ctg_id and s2.is_active = 1 and s1.style_id=s2.id order by s1.sequence<br/>";
//            $no_styles = count($styleobj);
//            print_r($styleobj);
////            $sizeClause = " and s2.id = i.size_id and i.ctg_id = s1.ctg_id and i.mrp = $this->MRP group by s2.id ";
//            $szqry = "select s1.size_id, s2.name as size_name from it_ck_sizes s1,it_sizes s2 $mTab where s1.ctg_id=$ctg_id and s1.size_id=s2.id $sizeClause order by s1.sequence";
//            print "<br>Size Query: $szqry <br/>";
//            $sizeobj = $db->fetchObjectArray($szqry);
////            echo "<br/>select s1.size_id, s2.name as size_name from it_ck_sizes s1,it_sizes s2 where s1.ctg_id=$ctg_id and s1.size_id=s2.id order by s1.sequence<br/>";
//            print_r($sizeobj);
//            $no_sizes = count($sizeobj);
            ?> 
        <div class="clear"></div>
<form name="allDesignsGRNR" id="allDesignsGRNR" method="POST" action="formpost/grnReleaseAll.php">        
        <div class="grid_10">
            <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
        </div>
        <div class="box" >
            <h5>Place Standing Orders? </h5>
            <input type="radio" name="stand_ord" id="stand_ord_Y" style="width:5%" value=1>Yes  &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
            <input type="radio" name="stand_ord" id="stand_ord_N" style="width:5%" value=0>No   
        </div>
    <!--<form name="allDesignsGRNR" id="allDesignsGRNR" method="POST" action="formpost/grnReleaseAll.php">-->
    <?php
            foreach ($allDesigns as $design) {
                $row_no++;
//                $_SESSION['rowno'] = $row_no;
                $design_no = $db->safe($design->design_no);
                $ctg_id = $design->ctg_id;
                $sqry = "select s1.style_id,s2.name as style_name from it_ck_styles s1,it_styles s2 $mTab where s1.ctg_id=$ctg_id and s2.is_active = 1 and s1.style_id=s2.id $styleClause order by s1.sequence"; 
//                print "<br> Style query: ".$sqry." <br/>";
                $styleobj = $db->fetchObjectArray($sqry);
    //            echo "<br/>select s1.style_id,s2.name as style_name from it_ck_styles s1,it_styles s2 where s1.ctg_id=$ctg_id and s2.is_active = 1 and s1.style_id=s2.id order by s1.sequence<br/>";
                $no_styles = count($styleobj);
//                print_r($styleobj);
    //            $sizeClause = " and s2.id = i.size_id and i.ctg_id = s1.ctg_id and i.mrp = $this->MRP group by s2.id ";
                $szqry = "select s1.size_id, s2.name as size_name from it_ck_sizes s1,it_sizes s2 $mTab where s1.ctg_id=$ctg_id and s1.size_id=s2.id $sizeClause order by s1.sequence";
//                print "<br>Size Query: $szqry <br/>";
                $sizeobj = $db->fetchObjectArray($szqry);
    //            echo "<br/>select s1.size_id, s2.name as size_name from it_ck_sizes s1,it_sizes s2 where s1.ctg_id=$ctg_id and s1.size_id=s2.id order by s1.sequence<br/>";
//                print_r($sizeobj);
                $no_sizes = count($sizeobj);
                $divid = "accordion-" . $row_no;
                ?>
    <div class="clear"></div>
    <div class="box">
        <h2 class="expand">Category : <?php echo $design->category; ?> | Design No: <?php echo $design->design_no." | Brand: $design->brandname"." | Material: $design->material"." | "; if ($design->prodtype) { echo " [ Production Type: ".$design->prodtype." ]"; } echo " [ MRP: ALL ] [ TOT GRN QTY : $design->tot_grn_qty ]"; ?></h2>  <!--  Fabric Type: $design->fabric-->
        <div class="collapse" id="<?php echo $divid; ?>">           
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Category Description :</b>
                <textarea name="designid[<?php echo $design->design_id; ?>][cdesp]" id="cdesp" rows="1" cols="25"></textarea>
                <input type="hidden" name="designid[<?php echo $design->design_id; ?>][mrp] " value="<?php echo $design->MRP; ?>" />
                <input type="hidden" name="designid[<?php echo $design->design_id; ?>][design_no]" value="<?php echo $design->design_no; ?>"/>
                <input type="hidden" name="designid[<?php echo $design->design_id; ?>][design_id]" value="<?php echo $design->design_id; ?>"/>
                <input type="hidden" name="designid[<?php echo $design->design_id; ?>][design_active]" value="<?php echo $design->active; ?>"/>
                <input type="hidden" name="designid[<?php echo $design->design_id; ?>][ctg_id]" value="<?php echo $design->ctg_id; ?>"/>
                <div class="grid_2" id="imagebackground">
                <a href="images/stock/<?php echo $design->image; ?>" rel="prettyPhoto"><img id="orderimage" align="left" src="images/stock/<?php echo $design->image; ?>" width="130" /></a>
                </div>
                <div class="block grid_10" style="width:78%;">
                    <table>
                        <thead>
                            <tr><th></th>
                                            <?php
                                            $width = intval(100/($no_sizes+1));
                                            for ($i = 0; $i < $no_sizes; $i++) {
                                                print '<th style="text-align:left;" width="'.$width.'%">';
                                                echo $sizeobj[$i]->size_name;
                                                print "</th>";  //print sizes
                                            }
                                            ?>
                            </tr>
                        </thead>
                        <tbody>
                                        <?php
                                        //for each unique style print style
                                        for ($k = 0; $k < $no_styles; $k++) {
                                        //print style names
                                            print "<tr id='styles::".$design->id."::".$styleobj[$k]->style_id."'><th>";
                                            echo $styleobj[$k]->style_name;
                                            print"</th>";
                                            //store style id in $stylecod
                                            $stylcod = $styleobj[$k]->style_id;
                                            for ($i = 0; $i < $no_sizes; $i++) {
                                                ?><td><?php
                                                        //to get the quantity and stock id of specific item
                                                        $sizeid = $sizeobj[$i]->size_id;
							//$query = "select id,sum(grn_qty) as qty from it_items where design_no = $design_no  and ctg_id=$ctg_id and style_id = '$stylcod' and size_id = '$sizeid'  and barcode like '89%' order by grn_qty desc";   // and MRP=$design->MRP and curr_qty > 0                                                     
                                                        $query = "select id,sum(grn_qty) as qty from it_items where design_no = $design_no  and ctg_id=$ctg_id and style_id = '$stylcod' and size_id = '$sizeid'  and barcode like '89%'  and grn_qty > 0 order by grn_qty desc";   // and MRP=$design->MRP and curr_qty > 0                                                     
                                                        //echo "<br/>$query<br/>";
                                                        $getitm = $db->fetchObject($query);
                                                        //check to see if specific item exists in order, if exist -> stores qty in order_qty  
                                                        if (isset($getitm)) {
//                                                            $query = "select sum(order_qty) as order_qty from it_ck_orderitems where order_id=$cart->id and store_id=$storeid and item_id=$getitm->id";
//                                                           echo $query;
//                                                                $exist = $db->fetchObject($query);
                                                            if ($getitm->id) { $id = $getitm->id; } else { $id="0"; }
                                                            if ($getitm->qty) { $qty=$getitm->qty; } else { $qty="0"; }
                                                            if(trim($qty==0)){ $readonly = "readonly"; }else{ $readonly = ""; }
                                                            ?><input type="text" style="width: 30px;" name="designid[<?php echo $design->design_id ;?>][item][item_<?php echo $id."_".$qty; ?>]" id="designid[<?php echo $design->design_id ;?>][item][item_<?php echo $id."_".$qty; ?>]" <?php
//                                                                if ($exist) {
                                                                    print "value='";
//                                                                    echo $exist->order_qty;
                                                                    print "'";
//                                                                }
                                                                    echo $readonly;
                                                                ?> onkeyup="txtValChk(this.id);"/><br>[ <?php echo $getitm->qty; ?> ]<?php
                                                        }
                                                ?></td><?php
                                            }
                                            print "</tr>";
                                        }
                                        ?>
                        </tbody>
                    </table>
                    <div id="status_<?php echo $row_no; ?>"></div>
<!--                    <input class="blueglassbutton" type="submit" value="ADD TO CART"/>
                    <input class="blueglassbutton" type="reset" value="RESET"/>-->
                    <input class="blueglassbutton" name = "divReleasebtn" id = "divReleasebtn" type="button" value="RELEASE ALL" onclick="releaseDivCellValues(<?php echo $design->design_id; ?>);"/>
                    <input class="blueglassbutton" name = "divResetbtn" id = "divResetbtn" type="button" value="RESET" onclick="resetDivCellValues(<?php echo $design->design_id; ?>);" />
                </div> <!-- end class=grid_10 -->
          
            <br>
        </div> <!-- end class="block" -->
    </div> <!-- end class="box" -->  
            <?php } ?>
     <label for="Release"><h5>Release Time:<h5></label>
                
                <select id="Release" name="Release">
                  <option value="00:00:00">Release now</option>
                  <option value="13:30:00">Release at 1.30 pm</option>
                  <option value="20:30:00">Release at 8.30 pm</option>
                </select>
                  <br><br>
    <input class="blueglassbutton" type="submit" name="btnSubmit" id="btnSubmit" value="SAVE" />    
    <input class="blueglassbutton" type="button" name="releaseAllBtn" id="releaseAllBtn" value="RELEASE ALL" onclick="releaseAllDivCellValues();"/>    
    <input class="blueglassbutton" type="button" name="resetAllBtn" id="resetAllBtn" value="RESET ALL" onclick="resetAllDivCellValues();"/>    
    </form>
<!--    <input class="blueglassbutton" type="button" value="SAVE" onclick="releaseAllDIVValues();"/>    
    <input class="blueglassbutton" type="button" value="RELEASE ALL" onclick="releaseAllTxtValues();"/>    -->
    <script src="js/chosen/chosen.jquery.js" type="text/javascript"></script>
    <script type="text/javascript"> $(".chzn-select").chosen(); $(".chzn-select-deselect").chosen({allow_single_deselect:true}); </script>
<div class="clear"></div>
            <?php
                }
            ?>
</div>
    <?php
    }
}
?>
