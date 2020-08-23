<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");

class cls_barcode_search_revised extends cls_renderer{

        var $currUser;
        var $userid;
        
        function __construct($params=null) {
		//parent::__construct(array(UserType::Admin, UserType::CKAdmin));
		$this->currUser = getCurrUser();
		$this->userid = $this->currUser->id;
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
<link rel="stylesheet" href="css/prettyPhoto.css" type="text/css" media="screen" title="prettyPhoto main stylesheet" charset="utf-8" />
<script src="js/prettyPhoto/jquery.prettyPhoto.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript">
    $(function() {
        //pretty photo pop up
	$("a[rel^='prettyPhoto']").prettyPhoto({animation_speed:'fast',slideshow:3000, hideflash: true});
    });
</script>
        <?php
        }

        public function pageContent() {
            $currUser = getCurrUser();
            $menuitem = "bsearch_rev";
            include "sidemenu.".$currUser->usertype.".php";
            $formResult = $this->getFormResult();
             if ($this->currUser->inactive == 1) {
            $reason = "You can not place Order Because, ".$currUser->inactivating_reason;
            echo "<script> alert('$reason');</script>";
        }
            
?>
<div class="grid_10">
    <?php
    
    $display="none";
    $num = 0;
    ?>
    <div class="box" style="clear:both;">
	<fieldset class="login">
	<legend>Search Barcodes</legend>
	<p>Select values for the various fields below. Some fields allow you to pick only a single value, others allow you to pick multiple values. You may leave one or more fields blank.<br />For <key>Design No</key> and <key>MRP</key> you can specify multiple values by using commas.</p>
        <form action="formpost/bSearchRev.php" method="post">
		<div class="grid_12" style="margin-bottom:20px;">
		<div class="grid_4">
		Category:<br />
        	<select name="categories[]" data-placeholder="Choose Category..." class="chzn-select" multiple style="width:200px;">
          <option value=""></option> 
<?php
$form_value=$this->getFieldValue('categories');
$db = new DBConn();
$objs = $db->fetchObjectArray("select * from it_categories order by name");
$db->closeConnection();
foreach ($objs as $obj) {
	$selected="";
	if (in_array($obj->id,$form_value)) { $selected = "selected"; }
?>
          <option value="<?php echo $obj->id; ?>" <?php echo $selected; ?>><?php echo $obj->name; ?></option> 
<?php } ?>
		</select>
		</div>
		<div class="grid_4">
                Design No (a,b,c):<br /><input type="text" name="design_no" style="width:130px;height:23px;font-size:14px;" value="<?php echo $this->getFieldValue('design_no'); ?>"/>
		</div>
		<div class="grid_4">
                MRP (10,11,12):<br /><input type="text" name="mrp" style="width:130px;height:23px;font-size:14px;" value="<?php echo $this->getFieldValue('mrp'); ?>"/>
		</div>
		</div>
<hr>
		<div class="grid_12">
		<div class="grid_8">
                Barcodes (890...,8901...): Note: If barcodes are entered, the above values will be ignored.<br /><input type="text" name="barcodes" style="width:100%;height:23px;font-size:14px;" value="<?php echo $this->getFieldValue('barcodes'); ?>"/>
		</div>
		</div> <!-- grid_12 -->
		<div class="grid_12" style="padding:10px;margin-top:20px;">
                <input type="submit" name="submitSearch" value="Search" style="background-color:#34de63;"/>
                
                       <?php if ($formResult && $this->getFieldValue('submitSearch')) { ?>
                <p>
                    <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
                </p>
                        <?php } ?>
		</div>
	</fieldset>
<?php if ($this->getFieldValue('success')) {
//unset($_SESSION['form_post']['success'];
$mfg_by = $this->getFieldValue('mfg_by');
if (is_array($mfg_by) && count($mfg_by) > 0) { $mfg_by_str = join(",",$mfg_by); }
else { $mfg_by_str = false; }
$categories = $this->getFieldValue('categories');
if (is_array($categories) && count($categories) > 0) { $categories_str = join(",",$categories); }
else { $categories_str = false; }
$design_no = $this->getFieldValue('design_no');
if (isset($design_no) && trim($design_no) != "") {
$arr = explode(",",$design_no);
for ($i=0; $i<count($arr); $i++) { $arr[$i] = $db->safe(strtoupper($arr[$i])); }
$design_no_str = join(",",$arr);
}
else { $design_no_str = false; }
$mrp = $this->getFieldValue('mrp');
if (isset($mrp) && trim($mrp) != "") {
$mrp_str = $mrp;
} else { $mrp_str = false; }
$barcodes = $this->getFieldValue('barcodes');
if (isset($barcodes) && trim($barcodes) != "") {
$tokens = explode(",",$barcodes);
$barray = array();
foreach ($tokens as $token) { $barray[] = $db->safe(trim($token)); }
$barcodes_str = join(",",$barray);
} else { $barcodes_str = false; }
$brands = $this->getFieldValue('brands');
if (is_array($brands) && count($brands) > 0) { $brands_str = join(",",$brands); }
else { $brands_str = false; }
$styles = $this->getFieldValue('styles');
if (is_array($styles) && count($styles) > 0) { $styles_str = join(",",$styles); }
else { $styles_str = false; }
$sizes = $this->getFieldValue('sizes');
if (is_array($sizes) && count($sizes) > 0) { $sizes_str = join(",",$sizes); }
else { $sizes_str = false; }
$prod_types = $this->getFieldValue('prod_types');
if (is_array($prod_types) && count($prod_types) > 0) { $prod_types_str = join(",",$prod_types); }
else { $prod_types_str = false; }
$materials = $this->getFieldValue('materials');
if (is_array($materials) && count($materials) > 0) { $materials_str = join(",",$materials); }
else { $materials_str = false; }
$fabric_types = $this->getFieldValue('fabric_types');
if (is_array($fabric_types) && count($fabric_types) > 0) { $fabric_types_str = join(",",$fabric_types); }
else { $fabric_types_str = false; }

//$query = "select * from it_items where ";
$query = "select i.id, i.batch_id, i.barcode, m.name as mfg_by, c.name as category, i.design_no, i.MRP, b.name as brand, st.name as style, si.name as size, p.name as prod_type, mt.name as material, f.name as fabric_type, d.image as design_image from it_items i left outer join it_mfg_by m on i.mfg_id = m.id left outer join it_categories c on i.ctg_id = c.id left outer join it_brands b on i.brand_id = b.id left outer join it_styles st on i.style_id = st.id left outer join it_sizes si on i.size_id = si.id left outer join it_prod_types p on i.prod_type_id = p.id left outer join it_materials mt on i.material_id = mt.id left outer join it_fabric_types f on i.fabric_type_id = f.id left outer join it_ck_designs d on i.design_id = d.id where ";
$where = array();
if ($barcodes_str) { $where[] = "i.barcode in ($barcodes_str)"; }
else {
if ($mfg_by_str) { $where[] = "i.mfg_id in ($mfg_by_str)"; }
if ($categories_str) { $where[] = "i.ctg_id in ($categories_str)"; }
if ($design_no_str) { $where[] = "i.design_no in ($design_no_str)"; }
if ($mrp_str) { $where[] = "i.MRP in ($mrp_str)"; }
if ($brands_str) { $where[] = "i.brand_id in ($brands_str)"; }
if ($styles_str) { $where[] = "i.style_id in ($styles_str)"; }
if ($sizes_str) { $where[] = "i.size_id in ($sizes_str)"; }
if ($prod_types_str) { $where[] = "i.prod_type_id in ($prod_types_str)"; }
if ($materials_str) { $where[] = "i.material_id in ($materials_str)"; }
if ($fabric_types_str) { $where[] = "i.fabric_type_id in ($fabric_types_str)"; }
}
$itemObjs = array();
if (count($where) > 0) {
	$query .= join(" and ", $where);
	$query .= " order by i.batch_id";
        $db = new DBConn();
	$itemObjs = $db->fetchObjectArray($query);
        $db->closeConnection();
}
?>
	<fieldset class="login">
	<legend>Search Barcodes</legend>
<table align="center" border="1">
<tr>
<th>Barcode</th>
<!--<th>Manufactured By</th>-->
<th>Category</th>
<th>Design</th>
<th>MRP</th>
<!--<th>Brand</th>-->
<th>Style</th>
<th>Size</th>
<th>Production Type</th>
<!--<th>Material</th>-->
<!--<th>Fabric Type</th>-->
<!--<th>Print Qty</th>-->
</tr>
<?php foreach($itemObjs as $obj) { ?>
<tr>
<td><?php echo $obj->barcode; ?><?php if (isset($obj->batch_id)) { ?> [ <a href="barcode/batch/id=<?php echo $obj->batch_id; ?>">Batch <?php echo $obj->batch_id; ?></a> ]<?php } ?></td>
<!--<td><?php // echo $obj->mfg_by; ?></td>-->
<td><?php echo $obj->category; ?></td>
<td><?php echo $obj->design_no; ?>
<?php if (isset($obj->design_image)) { ?> 
[ <a href="images/stock/<?php echo $obj->design_image; ?>" rel="prettyPhoto">Image</a> ]
<?php } ?>
</td>
<td><?php echo $obj->MRP; ?></td>
<!--<td><?php // echo $obj->brand; ?></td>-->
<td><?php echo $obj->style; ?></td>
<td><?php echo $obj->size; ?></td>
<td><?php echo $obj->prod_type; ?></td>
<!--<td><?php // echo $obj->material; ?></td>-->
<!--<td><?php // echo $obj->fabric_type; ?></td>-->
<!--<td><input name="qty_<?php // echo $obj->id; ?>" type="text" style="width:30px;" value="<?php // echo $this->getFieldValue('qty_'.$obj->id); ?>"/></td>-->
</tr>
<?php }?>
</table>
<?php
//$pack_dt = $this->getFieldValue('pack_dt');
//if (!$pack_dt) $pack_dt = strtoupper(date("M Y"));
?>
<!--Pack Dt: <input type="text" name="pack_dt" style="width:80px;" value="<?php // echo $pack_dt; ?>" /><br />-->
<!--<input name="submitPrint" type="submit" value="Print" />-->
<?php // if ($formResult && $this->getFieldValue('submitPrint')) { ?>
<!--<p>
<span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
</p>-->
<?php // } ?>
	</fieldset>
<?php } ?>
</form>
    </div> <!-- class=box -->
</div>
<script src="js/chosen/chosen.jquery.js" type="text/javascript"></script>
<script type="text/javascript"> $(".chzn-select").chosen(); $(".chzn-select-deselect").chosen({allow_single_deselect:true}); </script>

<?php
	}
}
?>
