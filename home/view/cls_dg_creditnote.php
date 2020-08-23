<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");
require_once ("lib/core/strutil.php");

class cls_dg_creditnote extends cls_renderer{

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
			$menuitem = "approve_creditnote";
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
	 $invoice = $db->fetchObject("select i.* from it_portalinv_creditnote i  where  i.id=$this->id ");
     // print "select i.* from it_portalinv_creditnote i  where  i.id=$this->id ";
         if (isset($invoice)) {
//            $items = $db->fetchObjectArray("select * from it_portalinv_items_creditnote where invoice_id = $invoice->id");
	?>
	<fieldset class="login">
	<legend style="font-size:14px;">CottonKing CreditNote: <?php echo $invoice->invoice_no; ?> | 
	<label>Amount:</label> <?php echo $invoice->invoice_amt; ?> | 
	<label>Quantity:</label> <?php echo $invoice->invoice_qty; ?> | 
	<label>Date:</label> <?php echo $invoice->approve_dt; ?><br/> 
           <label>Store Name:</label><?php echo $invoice->store_name;?>
       </legend>
		<div class="grid_12" style="overflow-y: scroll;height: 300px">
        <table style="width:100%" >
            <tr>
                <th colspan="18" align="center">Credit Note Details</th>
            </tr>
            <tr><th colspan="18"></th></tr>
                        <tr>
                            
                            <th>Sr.No.:</th>
                            <th>Description of Goods</th>
                            <th>HSN</th>
                            <th>Qty.</th>
                            <th>MRP(per item)</th>
                            <th>Discount(per item)</th>
                            <th>Rate(per item)</th>
                            <th>MRP Total</th>
                            <th>Total Discount</th>
                            <th>Txable Value</th>
                            <th>CGST Rate</th>
                            <th>CGST Amount</th>
                            <th>SGST Rate</th>
                            <th>SGST Amount</th>
                            <th>IGST Rate</th>
                            <th>IGST Amoun</th>
                             <th>Date</th>
                            
<!--                            <th><table style="width:100%" border="0"><tr><th>CGST</th></tr><tr><th>Rate</th><th>Amount</th></tr></table><tbody  style="overflow-y: auto;height: 20px;overflow-x: hidden"></th>
                            <th><table style="width:100%"><tr><th>SGST</th></tr><tr><th>Rate</th><th>Amount</th></tr></table></th>
                            <th><table style="width:100%"><tr><th>IGST</th></tr><tr><th>Rate</th><th>Amount</th></tr></table></th>-->
                            </tr>
                            
                                <?php
                                $i=1;
                                $total_qty=0;
                                $total_mrp=0; 
                                $total_disc=0.0;
                                $taxable_total=0.0;
                                $cgst_total=0.0;
                                $sgst_total=0.0;
                                $igst_total=0.0; 
                                $taxtype=0;
                                $iquery="select i.id,i.desc_defects as dsc,c.it_hsncode as hsn,i.price as mrp,i.quantity as qty,i.disc_peritem as disc_peritm,i.rate as rate_peritm,i.price*i.quantity as  total_mrp,i.discount_val as total_disc,i.tax_rate*100 as tax,i.cgst as cgst,i.sgst as sgst,i.igst as igst,cs.tax_type as taxtype,i.createtime as dttime from it_portalinv_items_creditnote i,it_categories c,it_codes cs where i.ctg_id=c.id and i.store_id=cs.id and invoice_id = $invoice->id";
                                 $items=$db->fetchObjectArray($iquery);
                                     foreach($items as $item){
                                       $total_qty +=$item->qty;  
                                        $total_mrp +=$item->total_mrp;
                                        $total_disc +=$item->total_disc;
                                        $taxable_total +=$item->rate_peritm*$item->qty;
                                        $cgst_total +=$item->cgst;
                                        $sgst_total +=$item->sgst;
                                        $igst_total +=$item->igst;
                                        $taxtype=$item->taxtype;
                                ?>
                            <tr>
                             <td><?php echo $i;?></td>
                            <td><?php echo $item->dsc;?></td>
                            <td><?php echo $item->hsn;?></td>
                            <td><?php echo $item->qty;?></td>
                            <td><?php echo $item->mrp;?></td>
                            <td><?php echo $item->disc_peritm;?></td>
                            <td><?php echo $item->rate_peritm;?></td>
                            <td><?php echo $item->total_mrp;?></td>
                            <td><?php echo $item->total_disc;?></td>
                             <td><?php echo $item->rate_peritm*$item->qty;?></td>
                            <td><?php if($item->taxtype==1){ $cgst_rate=$item->tax/2; echo $cgst_rate."%";}else{ echo "-";}?></td>
                            <td><?php if($item->taxtype==1){echo $item->cgst;}else{ echo "-";}?></td>
                            <td><?php if($item->taxtype==1){ $sgst_rate=$item->tax/2; echo $sgst_rate."%";}else{ echo "-";}?></td>
                            <td><?php if($item->taxtype==1){echo $item->sgst;}else{ echo "-";}?></td>
                            <td><?php if($item->taxtype !=1){ $igst_rate=$item->tax/2; echo $igst_rate."%";}else{ echo "-";}?></td>
                            <td><?php if($item->taxtype!=1){echo $item->igst;}else{ echo "-";}?></td>
                            <td><?php echo $item->dttime;?></td>
                           
                                </tr>
                                     <?php $i++; }?>    
                                
                            <tr><th></th> <th>Total</th> <th></th>
                            <th><?php echo $total_qty; ?></th>
                            <th></th> <th></th> <th></th>
                            <th><?php echo $total_mrp; ?></th>
                            <th><?php echo $total_disc; ?></th>
                            <th><?php echo $taxable_total; ?></th>
                            <th></th>
                            <th><?php if($taxtype==1){echo $cgst_total;}else{echo "-";} ?></th>
                            <th></th>
                            <th><?php if($taxtype==1){echo $sgst_total;}else{echo "-";} ?></th>
                            <th></th>
                            <th><?php if($taxtype!=1){echo $igst_total;}else{echo "-";} ?></th>
                             <th></th>
                            <th></th> 
                               </tr>    
                            <tbody id="scrl" style="overflow-y: auto;height: 20px;overflow-x: hidden">
                    
        </table>
                </div>    <div class="grid_12" style="overflow-y: scroll;height: 50px">
           <form action="formpost/approvePortalCreditNote.php" method="POST">
               <input type="hidden" name="creditnote_id" value='<?php echo $this->id;?>'>
               <input type="hidden" name="user_id" value='<?php echo $this->currUser->id;?>'>
               
               <?php
                  if (($invoice-> is_approved==0)) {  ?>
                 <input type="submit" value="Approve">
                  <?php }?>
                 
      <?php if ($formResult) { ?>
                <p>
                    <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
                </p>
                        <?php } ?>
              </form>  
       </div>
	</fieldset>
<?php } else { ?>
	<h2>CreditNote not found</h2>
<?php } ?>
	</div> <!-- class=box -->
</div>

<?php
	}
}
?>
