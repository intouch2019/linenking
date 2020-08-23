<?php
require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
//require_once "session_check.php";


class cls_barcode_newbatch extends cls_renderer{

        var $currUser;
        var $userid;
        var $worker_order;
        var $styles;
        var $sizes;
        var $data;
        var $material_id;
        function __construct($params=null) {
		parent::__construct(array(UserType::Admin, UserType::CKAdmin, UserType::Manager));
		$this->currUser = getCurrUser();
		$this->userid = $this->currUser->id;
                  if (isset($params) && isset($params['wo_order'])) {
            $this->worker_order = $params['wo_order'];
        } else {
            $this->worker_order = "";
        }
                
        if (isset($params) && isset($params['data'])) { $this->data=$params['data'];}
        
        if (isset($params) && isset($params['styles'])) { $this->styles = $params['styles'];}
        
        if (isset($params) && isset($params['sizes'])) { $this->sizes = $params['sizes']; }
        
        if (isset($params) && isset($params['material_id'])) { $this->material_id = $params['material_id']; }
       
                
        }

	function extraHeaders() {
        ?>

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
<link rel="stylesheet" href="css/bigbox.css" type="text/css" />
<script type="text/javascript">
function enterPressed() {
    var key = window.event.keyCode;
    // If the user has pressed enter
    if (key == 13) {
        alert("Enter detected");
        return false;
    } else {
        return true;
    }
}



 function reseteWorkOrderData()
            {
             window.location.href="barcode/newbatch";
            }            
            function getWorkOrderData() {
               // localStorage.setItem("new_way", "yes");
                //alert(localStorage.getItem("new_way"));
                

                var worderid = "";
                worderid = document.getElementById("worder").value;
                //console.log(worderid);

                if (worderid == "" || worderid.length < 0) {
                    alert("Please Enter Work order number to fetch Data");
                } else {
                    //console.log(worderid);
                   // $(".old_form").hide();
                   // $(".new_form").show();
                    var uri = "ajax/getWorkOrderData.php";
                    var stl;
                   // alert(uri);
                    $.ajax({
                        url: uri,
                        data: ({'order_id': worderid}),
                        dataType: 'json',
                        type: 'POST',
                             // error:function(){//alert("error occur");},
                        success: function (res) {
                            //alert("response occur");
                            if (res.error == "0") {
                                $msg=res.message;
                                $dat = $msg.split("<>");
                                if ($dat[0] === "1::category new") {
                                    if (confirm("Category [ " + $dat[1] + " ] new Appeared on Portal,Click OK to Add Category")) {
                                        var cat_workordr = $dat[1] + "<>" + worderid;
                                        window.location.href = "barcode/attributes/cat_id=" + cat_workordr;
                                    } else {

                                    }
                                }
                                else
                                {
                                window.location.href="barcode/newbatch/data="+res.message+"/styles="+res.styles+"/sizes="+res.sizes+"/material_id="+res.material_id;
                            }
                            } else {
                                alert("No work order of this number exist.Continue regular barcode creation without work order number");
                                    document.getElementById("worder").value="";                  
                                    }
                        }
                    });
          
                }

            }


</script>
        
        <?php
        }

        public function pageContent() {
            $currUser = getCurrUser();
            $menuitem = "bnewbatch";
            include "sidemenu.".$currUser->usertype.".php";
            
            $formResult = $this->getFormResult();
           // print_r($currUser->usertype);
            $db = new DBConn();
?>
<div class="grid_10">
    <?php
    
    $display="none";
    $num = 0;
    ?>
    <div class="box" style="clear:both;">
	<fieldset class="login">
	<legend>Add New Barcode Batch</legend>
        <p>Enter work order number to create barcode</p>
        <div > 
  <?php  if(isset($this->data)) { $this->data=str_replace(' ', '', $this->data); $this->data=str_replace('"', '', $this->data);$this->data=str_replace('}', '', $this->data); $this->data=str_replace('{', '', $this->data); $this->data=str_replace(']', '', $this->data);
  //echo $this->data;
  //wo_id:20104,category:jeans,designno:27935,mrp:1,production_type:denim,product_id:106,
          //label_name:jeansset,category_id:23,production_type_id:14,mfg_by_name:cottonkingpvtltd,mfg_by_id:3 
  $data1=explode(',',$this->data); } ?>
                        
                    
                        
            <label>Work Order : </label><input type="text" id="worder" name="workorder" value="<?php if(isset($this->data)){$w_ord=explode(':',$data1[0]);echo $w_ord[1];} ?>" placeholder="Enter your work order here..."/> <button onclick="getWorkOrderData();">Fetch Data</button> <?php if(isset($this->data)){?> <button onclick="reseteWorkOrderData();" >Reset All<?php }?>                         
                    </div><hr/>
	<form action="formpost/addBarcodeBatch.php" method="post">
            <p>Select values for the various fields below. Some fields allow you to pick only a single value, others allow you to pick multiple values.</p>
        
		<div class="grid_12">
		<div class="grid_4">
		Manufactured By*:<br />
        	<select name="mfg_by" data-placeholder="Choose Manufacturers..." class="chzn-select" single style="width:100%;">
          <option value=""></option> 
<?php
$form_value=$this->getFieldValue('mfg_by');
$objs = $db->fetchObjectArray("select * from it_mfg_by order by name");
foreach ($objs as $obj) {
	$selected="";
	if ($obj->id == $form_value) { $selected = "selected"; }
?>
          <option value="<?php echo $obj->id; ?>"<?php echo $selected; if(isset($this->data) && $selected==""){ $mf_id=explode(':',$data1[10]); if($mf_id[1]==$obj->id){ ?>selected="selected" <?php }}?> ><?php echo $obj->name; ?></option> 
<?php } ?>
		</select>
		</div>
		<div class="grid_4">
		Category*:<br />
        	<select name="category" data-placeholder="Choose Category..." class="chzn-select" single style="width:100%;">
          <option value=""></option> 
<?php
$form_value=$this->getFieldValue('category');
$objs = $db->fetchObjectArray("select * from it_categories order by name");
foreach ($objs as $obj) {
	$selected="";
	if ($obj->id == $form_value) { $selected = "selected"; }
?>
          <option value="<?php echo $obj->id; ?>"<?php  if(isset($this->data)&& $selected=="" ){ $cat_id=explode(':',$data1[7]); if($cat_id[1]==$obj->id){ ?>selected="selected" <?php }}else {echo $selected;}?> ><?php echo $obj->name; ?></option> 
<?php } ?>
		</select>
		</div>
		<div class="grid_2">
                Design No*:<br /><input type="text" name="design_no" style="width:100%;height:23px;font-size:14px;" value="<?php if(isset($this->data)){ $design=explode(':',$data1[2]); echo $design[1];} else {echo $this->getFieldValue('design_no');}?>"/>
		</div>
		<div class="grid_1">
                MRP*:<br /><input type="text" name="mrp" style="width:100%;height:23px;font-size:14px;" value="<?php if(isset($this->data)){ $mrp=explode(':',$data1[3]); echo $mrp[1];} else { echo $this->getFieldValue('mrp');}?>"/>
		</div>
		<div class="grid_1">
                Units*:<br /><input type="text" name="num_units" style="width:100%;height:23px;font-size:14px;" <?php if(isset($this->data)){?> value="1"<?php }else { ?>value="<?php echo $this->getFieldValue('num_units');}?>"/>
		</div>
		</div>
		<div class="grid_12">
		<div class="grid_4">
		Brands*:<br />
        	<select name="brands[]" data-placeholder="Choose Brands..." class="chzn-select" multiple style="width:100%;">
          <option value=""></option> 
<?php
$form_value=$this->getFieldValue('brands');
$objs = $db->fetchObjectArray("select * from it_brands order by name");
foreach ($objs as $obj) {
	$selected="";
	if (isset($form_value) && in_array($obj->id,$form_value)) { $selected = "selected"; }
?>
          <option value="<?php echo $obj->id; ?>"  <?php if(isset($this->data)&& $selected==""){if($obj->id=="5"){ ?>selected="selected" <?php }}else{echo $selected;}?>><?php echo $obj->name; ?></option> 
<?php } ?>
		</select>
		</div>
		<div class="grid_4">
		Styles*:<br />
        	<select name="styles[]" data-placeholder="Choose Styles..." class="chzn-select" multiple style="width:100%;">
          <option value=""></option> 
<?php
$form_value=$this->getFieldValue('styles');
$objs = $db->fetchObjectArray("select * from it_styles order by name");
foreach ($objs as $obj) {
	$selected="";
	if (isset($form_value) && in_array($obj->id,$form_value)) { $selected = "selected"; }
?>
          <option value="<?php echo $obj->id; ?>"  <?php if(isset($this->styles) ){if(substr_count(",".$this->styles.",", ",".$obj->id.",")>0) {?>selected<?php }}else{echo $selected;} ?>><?php echo $obj->name; ?></option> 
<?php } ?>
		</select>
		</div>
		<div class="grid_4">
		Sizes*:<br />
        	<select name="sizes[]" data-placeholder="Choose Sizes..." class="chzn-select" multiple style="width:100%;">
          <option value=""></option> 
<?php
$form_value=$this->getFieldValue('sizes');
$objs = $db->fetchObjectArray("select * from it_sizes order by name");
foreach ($objs as $obj) {
	$selected="";
	if (isset($form_value) && in_array($obj->id,$form_value)) { $selected = "selected"; }
?>
          <option value="<?php echo $obj->id; ?>" <?php if(isset($this->sizes)){if(substr_count(",".$this->sizes.",", ",".$obj->id.",")>0) {?>selected<?php }}else{echo $selected;}  ?>><?php echo $obj->name; ?></option> 
<?php } ?>
		</select>
		</div>
                </div>
		<div class="grid_12">
		<div class="grid_4">
		Production Types*:<br />
        	<select name="prod_types[]" data-placeholder="Choose Production Types..." class="chzn-select" multiple style="width:100%;">
          <option value=""></option> 
<?php
$form_value=$this->getFieldValue('prod_types');
$objs = $db->fetchObjectArray("select * from it_prod_types order by name");
foreach ($objs as $obj) {
          $selected="";
	if (isset($form_value) && in_array($obj->id,$form_value)) { $selected = "selected"; }  ?>
          <option value="<?php echo $obj->id; ?>" <?php if(isset($this->data)){ $prod_type=explode(':',$data1[8]); if($prod_type[1]==$obj->id){ ?>selected="selected" <?php }} else{echo $selected;}?>><?php echo $obj->name; ?></option> 
<?php } ?>
		</select>
		</div>
		<div class="grid_4">
		Materials*:<br />
        	<select name="materials[]" data-placeholder="Choose Materials..." class="chzn-select" multiple style="width:100%;">
          <option value=""></option> 
<?php
$form_value=$this->getFieldValue('materials');
$objs = $db->fetchObjectArray("select * from it_materials order by name");
foreach ($objs as $obj)  {
         $selected="";
	if (isset($form_value) && in_array($obj->id,$form_value)) { $selected = "selected"; } ?>
    { ?>
          
          <option value="<?php echo $obj->id; ?>"<?php if(isset($this->material_id)){if($this->material_id==$obj->id) {?>selected<?php }}else{echo $selected;} ?>><?php echo $obj->name; ?></option> 
<?php } ?>
		</select>
		</div>
		<div class="grid_4">
		Fabric Types*:<br />
        	<select name="fabric_types[]" data-placeholder="Choose Fabric Types..." class="chzn-select" multiple style="width:100%;">
          <option value=""></option> 
<?php
$objs = $db->fetchObjectArray("select * from it_fabric_types order by name");
foreach ($objs as $obj) { ?>
          <option value="<?php echo $obj->id; ?>"><?php echo $obj->name; ?></option> 
<?php } ?>
		</select>
                </div>
		</div> <!-- grid_12 -->
                 <div class="grid_12">
                    <div class="grid_4">
                        Hsncode.*<br>
                <select name="hsncode" data-placeholder="Choose HSN Code..." class="chzn-select"  style="width:100%;">
                <option value=""></option> 
      <?php
      $objs = $db->fetchObjectArray("select * from it_hsns order by hsncode");
      foreach ($objs as $obj) { ?>
                <option value="<?php echo $obj->id; ?>"><?php echo $obj->hsncode; ?></option> 
      <?php } ?>
		</select>
                        <!--<input type="text" name="hsncode" id="hsncode" value="">-->
                    </div>
                    <div class="grid_8">
                        <label>Select Stock Type*:<br></label>
                        <div class="grid_2">
                             <input type ="radio" name="stocktype" id="stocktype"   value="<?php echo StockType::NormalStock;?>" required> <?php echo trim(StockType::getName(StockType::NormalStock));?>
                        </div>
                        <div class="grid_2">
                             <input type ="radio" name="stocktype" id="stocktype"   value="<?php echo StockType::Stock50percent;?>" required> <?php echo trim(StockType::getName(StockType::Stock50percent));?>
                        </div>
                    </div>
                </div>
<!--                 <div class="grid_12">
                    <div class="grid_2"><label>Select Stock Type*:</label></div>         
                    <div class="grid_2">
                         <input type ="radio" name="stocktype" id="stocktype" style="width:5%" value="<?php echo StockType::NormalStock;?>" required> <?php echo trim(StockType::getName(StockType::NormalStock));?>
                    </div>
                    <div class="grid_2">
                         <input type ="radio" name="stocktype" id="stocktype" style="width:5%" value="<?php echo StockType::Stock50percent;?>" required> <?php echo trim(StockType::getName(StockType::Stock50percent));?>
                    </div>
                </div>-->
		<div class="grid_12" style="padding:10px;">
                <input type="submit" name="add" id="add" value="Create Batch" style="background-color:#34de63;"/>
                
                       <?php if ($formResult) { ?>
                <p>
                    <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
                </p>
                        <?php } ?>
		</div>
            </form>
	</fieldset>
    </div> <!-- class=box -->
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js" type="text/javascript"></script>
<script src="js/chosen/chosen.jquery.js" type="text/javascript"></script>
<script type="text/javascript"> $(".chzn-select").chosen(); $(".chzn-select-deselect").chosen({allow_single_deselect:true}); </script>

<?php
	}
}
?>