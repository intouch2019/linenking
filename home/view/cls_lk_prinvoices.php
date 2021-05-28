<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");
require_once ("lib/core/strutil.php");

class cls_lk_prinvoices extends cls_renderer{

	var $currUser;
	var $userid;
	var $id=-1;
		
	function __construct($params=null) {
		//parent::__construct(array(UserType::Admin, UserType::CKAdmin));
		$this->currUser = getCurrUser();
		$this->userid = $this->currUser->id;
		if (isset($params['id'])) { $this->id = $params['id']; }
               // print $params['id'];
                return;
	}

	function extraHeaders() {
		?>

<script type="text/javascript">

</script>
		
		<?php
		}

		public function pageContent() {
			$menuitem = "lkprinvoices";
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
	//$invoice = $db->fetchObject("select i.*,c.store_name from it_invoices i , it_codes c  where i.store_id = c.id and i.id=$this->id ");
      
        $return = $db->fetchObject("select i.* from it_store_returns i  where  i.id=$this->id and i.createtime >'2019-04-01 00:00:00' ");
        
        $invoice = $db->fetchObject("select i.* from it_store_returnitems i  where  i.return_id=$this->id and i.createtime >'2019-04-01 00:00:00' ");
        

	if ($invoice) {
       
	$items = $db->fetchObjectArray("select * from it_store_returnitems where return_id= $return->id and createtime >'2019-04-01 00:00:00'");
	?>
	<fieldset class="login">
	<legend style="font-size:14px;">LinenKing Purchase Return Invoice: <?php echo $return->return_no; ?> | 
	<label>Amount:</label> <?php if(isset($return->amount)){$value= sprintf('%.2f',$return->amount);echo $value;}?> | 
	<label>Quantity:</label> <?php echo $return->quantity; ?> | 
	<label>Date:</label> <?php echo $return->createtime; ?><br/>
       
       <?php
           $obj1 = $db->fetchObject("select store_name from it_codes where id = $return->store_id ");
           ?>
        <label>Store Name:</label><?php echo $obj1->store_name;?>
	</legend>
	    <table align="center" border="1">
		<tr>
		 <th>Barcode</th>
                 <th>Price</th>
		 <th>Quantity</th>
		</tr>
		<?php foreach($items as $items) { ?>
		<tr>
		 <td><?php echo $items->item_code; ?></td>
                 <td><?php echo sprintf("%0.02f", $items->price); ?></td>
		 <td><?php echo $items->quantity; ?></td>
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
