<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");
require_once ("lib/core/strutil.php");

class cls_lk_sbinvoice extends cls_renderer{

	var $currUser;
	var $userid;
	var $id=-1;
		
	function __construct($params=null) {
		//parent::__construct(array(UserType::Admin, UserType::CKAdmin));
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
			$menuitem = "lksbinvoices";
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
        $invoice = $db->fetchObject("select i.* from it_saleback_invoices i  where  i.id=$this->id ");
	if ($invoice) {
        $spObj = $db->fetchObject("select * from it_codes where id = ".DEF_SP_LIFESTYLE_ID); // S.P. Life Style ID    
        $itemobj = $db->fetchObject("select item_code from it_saleback_invoice_items where invoice_id = $invoice->id ");
	$items = $db->fetchObjectArray("select * from it_saleback_invoice_items where invoice_id = $invoice->id");
	?>
	<fieldset class="login">
	<legend style="font-size:14px;">LinenKing SaleBack Invoice: <?php echo $invoice->invoice_no; ?> | 
	<label>Amount:</label> <?php echo $invoice->invoice_amt; ?> | 
	<label>Quantity:</label> <?php echo $invoice->invoice_qty; ?> | 
	<label>Date:</label> <?php echo $invoice->invoice_dt; ?><br/>
       <?php 
           $obj1 = $db->fetchObject("select store_name from it_codes where id = $invoice->store_id ");
           ?>
         <label>Store Name:</label><?php echo $obj1->store_name;?>
	</legend>
	    <table align="center" border="1">
		<tr>
		 <th>Barcode</th>
                 <th>Price</th>
		 <th>Quantity</th>
		</tr>
		<?php foreach($items as $obj) { ?>
		<tr>
		 <td><?php echo $obj->item_code; ?></td>
                 <td><?php echo sprintf("%0.02f", $obj->price); ?></td>
		 <td><?php echo $obj->quantity; ?></td>
		</tr>
		<?php }?>
            </table>
	</fieldset>
<?php } else { ?>
	<h2>Invoice not found</h2>
<?php } ?>
	</div> 
</div>

<?php
	}
}
?>
