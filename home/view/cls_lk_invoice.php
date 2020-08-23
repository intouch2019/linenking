<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");
require_once ("lib/core/strutil.php");

class cls_lk_invoice extends cls_renderer{

	var $currUser;
	var $userid;
	var $id=-1;
		
	function __construct($params=null) {
		parent::__construct(array(UserType::Admin, UserType::CKAdmin));
		$this->currUser = getCurrUser();
		$this->userid = $this->currUser->id;
		if (isset($params['id'])) { $this->id = $params['id']; }
	}

	function extraHeaders() {
		?>

<script type="text/javascript">

</script>
		
		<?php
		}

		public function pageContent() {
			$menuitem = "ckinvoices";
			include "sidemenu.".$this->currUser->usertype.".php";
			$formResult = $this->getFormResult();
?>
<div class="grid_10">
	<div class="box" style="clear:both;">
	<?php $_SESSION['form_post'] = array(); ?>
	<?php
	
	$display="none";
	$num = 0;
	$db = new DBConn();
	$invoice = $db->fetchObject("select * from it_invoices where id=$this->id");
	if ($invoice) {
	$items = $db->fetchObjectArray("select * from it_invoice_items where invoice_id = $invoice->id");
	?>
	<fieldset class="login">
	<legend style="font-size:14px;">CottonKing Invoice: <?php echo $invoice->invoice_no; ?> | 
	<label>Amount:</label> <?php echo $invoice->invoice_amt; ?> | 
	<label>Quantity:</label> <?php echo $invoice->invoice_qty; ?> | 
	<label>Date:</label> <?php echo $invoice->invoice_dt; ?>
<?php if ($invoice->sp_invoice_id) { ?>
	<br /><label>SP Invoice:</label> <a href="sp/invoice/id=<?php echo $invoice->sp_invoice_id; ?>/">View</a>
<?php } ?>
	</legend>
				<table align="center" border="1">
					<tr>
						<th>Barcode</th>
						<!--<th>Intouch Barcode</th>-->
						<th style="text-align:right;">Price</th>
						<th style="text-align:right;">Quantity</th>
					</tr>
					<?php foreach($items as $obj) { ?>
					<tr>
						<td><?php echo $obj->item_code; ?></td>
						<!--<td><?php // echo $obj->barcode; ?></td>-->
						<td style="text-align:right;"><?php echo sprintf("%0.02f", $obj->price); ?></td>
						<td style="text-align:right;"><?php echo $obj->quantity; ?></td>
					</tr>
					<?php }?>
		</table>
	</fieldset>
<?php } else { ?>
	<h2>Invoice not found</h2>
<?php } ?>
	</div> <!-- class=box -->
</div>

<?php
	}
}
?>
