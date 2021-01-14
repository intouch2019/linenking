<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");

class cls_barcode_batch extends cls_renderer{

		var $currUser;
		var $userid;
		var $batch=false;
		
		function __construct($params=null) {
		//parent::__construct(array(UserType::Admin, UserType::CKAdmin));
		$this->currUser = getCurrUser();
		$this->userid = $this->currUser->id;
		if (isset($params['id'])) {
			$db = new DBConn();
			$this->batch = $db->fetchObject("select * from it_barcode_batches where id=".$params['id']);
			$db->closeConnection();
		}
		}

	function extraHeaders() {
		?>

<script type="text/javascript">

</script>
		
		<?php
		}

		public function pageContent() {
			$menuitem = "bbatches";
			include "sidemenu.".$this->currUser->usertype.".php";
?>
<div class="grid_10">
	<?php
	if (!$this->batch) { print "<h2>Batch not found</h2>"; return; }
	else {
	
        $formResult = $this->getFormResult();
	$display="none";
	$num = 0;
	$db = new DBConn();
	$barcodes = $db->fetchObjectArray("select i.id, i.barcode, m.name as mfg_by, c.name as category, i.design_no, i.MRP, b.name as brand, st.name as style, si.name as size, p.name as prod_type, mt.name as material, f.name as fabric_type from it_items i left outer join it_mfg_by m on i.mfg_id = m.id left outer join it_categories c on i.ctg_id = c.id left outer join it_brands b on i.brand_id = b.id left outer join it_styles st on i.style_id = st.id left outer join it_sizes si on i.size_id = si.id left outer join it_prod_types p on i.prod_type_id = p.id left outer join it_materials mt on i.material_id = mt.id left outer join it_fabric_types f on i.fabric_type_id = f.id where i.batch_id=".$this->batch->id." order by st.name, si.name");
	?>
	<div class="box" style="clear:both;">
	<div class="grid_12" style="text-align:right;">
	<a href="barcode/newbatch"><button>New Batch</button></a>
	</div>
	<fieldset class="login">
        <form action="formpost/printBatch.php" method="post">
	<legend>Barcodes</legend>
				<table align="center" border="1">
					<tr>
						<th>Barcode</th>
						<th>Manufactured By</th>
						<th>Category</th>
						<th>Design</th>
						<th>MRP</th>
						<th>Brand</th>
						<th>Style</th>
						<th>Size</th>
						<th>Production Type</th>
						<th>Material</th>
						<th>Fabric Type</th>
						<th>Print Qty</th>
					</tr>
					<?php foreach($barcodes as $obj) { ?>
					<tr>
						<td><?php echo $obj->barcode; ?></td>
						<td><?php echo $obj->mfg_by; ?></td>
						<td><?php echo $obj->category; ?></td>
						<td><?php echo $obj->design_no; ?></td>
						<td><?php echo $obj->MRP; ?></td>
						<td><?php echo $obj->brand; ?></td>
						<td><?php echo $obj->style; ?></td>
						<td><?php echo $obj->size; ?></td>
						<td><?php echo $obj->prod_type; ?></td>
						<td><?php echo $obj->material; ?></td>
						<td><?php echo $obj->fabric_type; ?></td>
						<td><input name="qty_<?php echo $obj->id; ?>" type="text" style="width:30px;" value="<?php echo $this->getFieldValue('qty_'.$obj->id); ?>"/></td>
					</tr>
					<?php }?>
		</table>
<?php
$pack_dt = $this->getFieldValue('pack_dt');
if (!$pack_dt) $pack_dt = strtoupper(date("M Y"));
$line = $this->getFieldValue('line');
?>
<?php
$material = $this->getFieldValue('material');
?>
Pack Dt: <input type="text" name="pack_dt" style="width:80px;" value="<?php echo $pack_dt; ?>" /><br /><br />
Material: <input type="text" name="material" style="width:80px;" value="<?php echo $material; ?>" /><br />
<input type="hidden" name="batch_id" value="<?php echo $this->batch->id; ?>" />
</br>

<label>Select Barcode Line</label></br>
<input type="radio" name="line" value="1" <?php if ($line == 1) { ?>checked <?php } ?> required/>Single Line
<input type="radio" name="line" value="2" <?php if ($line == 2) { ?>checked <?php } ?> required />Double Line
</br>
</br>

<input name="submitPrint" type="submit" value="Print" />
<?php if ($formResult) { ?>
<p>
<span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
</p>
<?php } ?>
	</form>
	</fieldset>
	</div> <!-- class=box -->
<?php } ?>
</div>

<?php
	}
}
?>
