<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");
require_once ("lib/core/strutil.php");

class cls_store_saleback_status extends cls_renderer{

	var $currUser;
	var $userid;

	function __construct($params=null) {
		//parent::__construct(array(UserType::Admin, UserType::CKAdmin));
                //ini_set('max_execution_time', 300);
		$this->currUser = getCurrUser();
		$this->userid = $this->currUser->id;
	}


	function extraHeaders() {
		?>
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

 <style type="text/css" title="currentStyle">
    @import "js/datatables/media/css/demo_page.css";
    @import "js/datatables/media/css/demo_table.css";
</style>
<script src="js/datatables/media/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="js/chosen/chosen.css" />
<link rel="stylesheet" href="css/bigbox.css" type="text/css" />
<script type="text/javascript">
$(function() {
    var st_id=document.getElementById('st_id').value;
     var url = "ajax/tb_store_salebackstatus.php?st_id=" + encodeURIComponent(st_id);
//     alert(url);
      oTable = $('#tb_allinvoices').dataTable({
                    "bProcessing": true,
                    "bServerSide": true,
                    "aoColumns": [null,null, null, null, null,{"bSortable": false},null, {"bSortable": false},null,null],                     
                    "aaSorting": [[0,"desc"]],                    
                    "sAjaxSource": url,
                    "iDisplayLength": 50
                });
                //                oTable.fnSort([[0, 'desc']]);
                // search on pressing Enter key only
                $('.dataTables_filter input').unbind('keyup').bind('keyup', function(e) {
                    if (e.which == 13) {
                        oTable.fnFilter($(this).val(), null, false, true);
                    }
                });
});

function showInvoiceDetails( invid){
    window.location.href = "lk/sbinvoice/id="+invid;
}
</script>		
		<?php
		}

		public function pageContent() {


			$menuitem = "salebackstatus";
			include "sidemenu.".$this->currUser->usertype.".php";
			$formResult = $this->getFormResult();
?>
<div class="grid_10">
	<?php $_SESSION['form_post'] = array(); ?>
	<?php

	$display="none";
	$num = 0;
	$db = new DBConn();
       //$spObj = $db->fetchObject("select * from it_codes where id = ".DEF_SP_LIFESTYLE_ID); // S.P. Life Style ID
	//$invoices = $db->fetchObjectArray("select i.* , c.store_name from it_invoices i , it_codes c where i.store_id = c.id and i.invoice_type = 0 group by i.id order by i.id desc");
       // $invoices = $db->fetchObjectArray("select i.*  from it_invoices i  where  i.invoice_type = 0 group by i.id order by i.id desc");
	?>
	<!--<div class="box" style="clear:both;">-->
	<!--<fieldset class="login">-->	
         <div class="grid_12" id="tablebox" class="ui-widget-content ui-corner-bottom" style="overflow:auto;">
             <legend>LinenKing SaleBack Invoices</legend>
             <input type="hidden" value="<?php echo $this->userid; ?>" id="st_id" >
             <table align="center" border="1" cellpadding="0" cellspacing="0" border="0" class="display" id="tb_allinvoices">
					<thead>
                                            <tr>
                                                    <th>ID</th>
                                                    <th>Invoice No</th>
                                                    <th>Date</th>
                                                    <th style="text-align:right;">Amount</th>
                                                    <th style="text-align:right;">Quantity</th>
                                                    <th>Store Name</th>
                                                    <th>Transit Status</th>
                                                    <th>Invoice Details</th>
                                                    <th>Payments UTR</th>                                                    
                                                    <th>Remark/Payment Date</th>
                                                    <th></th>
                                                    <!--<th>Type</th>-->                                                    
                                                    <!--<th>SP Invoice</th>-->
                                            </tr>
                                        </thead>
					<?php //foreach($invoices as $obj) { ?>
<!--					<tr>
						<td><?php // echo $obj->invoice_no; ?> </td>
						<td><?php //echo $obj->invoice_dt; ?></td>
						<td style="text-align:right;"><?php //echo sprintf("%0.02f", $obj->invoice_amt); ?></td>
						<td style="text-align:right;"><?php //echo $obj->invoice_qty; ?></td>
						<td><?php// echo mmddyy($obj->createtime); ?></td>
                                                <?php //$itemobj = $db->fetchObject("select * from it_invoice_items where invoice_id = $obj->id ");
                                                  //if (strpos($itemobj->item_code,"89000") !== false) {
                                                ?>
                                                 <td><?php //echo $spObj->store_name; ?></td>
                                                  <?php //} else {?>
                                                <td><?php 
                                                //$obj1 = $db->fetchObject("select store_name from it_codes where id = $obj->store_id ");
                                                //echo $obj1->store_name; ?></td>
                                                 <?php// } ?>
                                                <td><u><a href="ck/invoice/id=<?php //echo $obj->id; ?>/">View</a></u></td>
<?php //if ($obj->sp_invoice_id) { ?>
						<td><a href="sp/invoice/id= //echo $obj->sp_invoice_id; /">View</a></td>
<? //} else { ?>
						<td>Not Created</td>
<? //} ?>
					</tr>-->
					<?php //}?>
		</table>
                </div>
	<!--</fieldset>--> 
	<!--</div>  class=box -->
</div>

<?php
	}
}
?>