<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");

class cls_barcode_batches extends cls_renderer{

		var $currUser;
		var $userid;
		
		function __construct($params=null) {
		//parent::__construct(array(UserType::Admin, UserType::CKAdmin));
		$this->currUser = getCurrUser();
		$this->userid = $this->currUser->id;
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
			$formResult = $this->getFormResult();
?>
<div class="grid_10">
	<?php $_SESSION['form_post'] = array(); ?>
	<?php
	
	$display="none";
	$num = 0;
	$db = new DBConn();
	$batches = $db->fetchObjectArray("select b.id, b.createtime, m.name as mfg_by, c.name as category, b.design_no, b.MRP from it_barcode_batches b left outer join it_mfg_by m on b.mfg_by_id = m.id left outer join it_categories c on b.category_id = c.id order by b.id desc");
	?>
	<div class="box" style="clear:both;">
	<div class="grid_12" style="text-align:right;">
	<a href="barcode/newbatch"><button>New Batch</button></a>
	</div>
	<fieldset class="login">
	<legend>Barcode Batches</legend>
				<table align="center" border="1">
					<tr>
						<th>Batch No</th>
						<th>Manufactured By</th>
						<th>Category</th>
						<th>Design</th>
						<th>MRP</th>
						<th>Date Created</th>
						<th></th>
					</tr>
					<?php foreach($batches as $obj) { ?>
					<tr>
						<td><?php echo $obj->id; ?></td>
						<td><?php echo $obj->mfg_by; ?></td>
						<td><?php echo $obj->category; ?></td>
						<td><?php echo $obj->design_no; ?></td>
						<td><?php echo $obj->MRP; ?></td>
						<td><?php echo $obj->createtime; ?></td>
						<td><a href="barcode/batch/id=<?php echo $obj->id; ?>/">View Batch</a></td>
					</tr>
					<?php }?>
		</table>
	</fieldset>
	</div> <!-- class=box -->
</div>

<?php
	}
}
?>
