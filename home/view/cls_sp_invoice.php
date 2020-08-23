<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");
require_once ("lib/core/strutil.php");

class cls_sp_invoice extends cls_renderer{

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
			$menuitem = "spinvoices";
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
	$invoice = $db->fetchObject("select i.*, c.store_name from it_sp_invoices i, it_codes c where i.id=$this->id and i.store_id = c.id");
	if ($invoice) {
	$items = $db->fetchObjectArray("select * from it_sp_invoice_items where invoice_id = $invoice->id");
	?>
	<fieldset class="login">
	<legend style="font-size:14px;">SP Lifestyle Invoice: <?php echo $invoice->invoice_no; ?> | 
	<label>Amount:</label> <?php echo $invoice->invoice_amt; ?> | 
	<label>Quantity:</label> <?php echo $invoice->invoice_qty; ?> | 
	<label>Date:</label> <?php echo mmddyy($invoice->invoice_dt); ?>
	<br /><label>Store:</label> <?php echo $invoice->store_name; ?>
<?php if ($invoice->ck_invoice_id) { ?>
	<br /><label>LK Invoice:</label> <a href="lk/invoice/id=<?php echo $invoice->ck_invoice_id; ?>/">View</a>
<?php } ?>
	</legend>
				<table align="center" border="1">
					<tr>
						<th>Intouch Barcode</th>
						<th style="text-align:right;">Price</th>
						<th style="text-align:right;">Quantity</th>
					</tr>
					<?php foreach($items as $obj) { ?>
					<tr>
						<td><?php echo $obj->barcode; ?></td>
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
