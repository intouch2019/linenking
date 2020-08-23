<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");
require_once ("lib/core/strutil.php");

class cls_sp_invoices extends cls_renderer{

	var $currUser;
	var $userid;
		
	function __construct($params=null) {
		parent::__construct(array(UserType::Admin, UserType::CKAdmin));
		$this->currUser = getCurrUser();
		$this->userid = $this->currUser->id;
	}

	function extraHeaders() {
		?>
<script type="text/javascript" src="js/ajax.js"></script>
<script type="text/javascript" src="js/ajax-dynamic-list.js">

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
     var url = "ajax/tb_sp_invoices.php";
//     alert(url);
      oTable = $('#tb_allinvoices').dataTable({
                    "bProcessing": true,
                    "bServerSide": true,
                    "aoColumns": [null,null, null, null, null, {"bSortable": false},{"bSortable": false}, {"bSortable": false}],                     
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

function showSPInvoiceDetails( invid){
    window.location.href = "sp/invoice/id="+invid;
}

function showCKInvoiceDetails( invid){
    window.location.href = "lk/invoice/id="+invid;
}
</script>
		
		<?php
		}

		public function pageContent() {
			$menuitem = "spinvoices";
			include "sidemenu.".$this->currUser->usertype.".php";
			$formResult = $this->getFormResult();
?>
<div class="grid_10">
	<?php $_SESSION['form_post'] = array(); ?>
	<?php
	
	$display="none";
	$num = 0;
	$db = new DBConn();
	//$invoices = $db->fetchObjectArray("select i.*, c.store_name, convert(i.invoice_no,unsigned integer) as iino from it_sp_invoices i, it_codes c where i.store_id = c.id order by i.invoice_dt desc");
	?>
	<!--<div class="box" style="clear:both;">-->
	<!--<fieldset class="login">-->
	<div class="grid_12" id="tablebox" class="ui-widget-content ui-corner-bottom" style="overflow:auto;">
        <legend>SP Lifestyle Invoices</legend>
				<table align="center" border="1" cellpadding="0" cellspacing="0" border="0" class="display" id="tb_allinvoices">
                                    <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Invoice No</th>
                                        <th>Store</th>
                                        <th>Date</th>
                                        <th style="text-align:right;">Amount</th>
                                        <th style="text-align:right;">Quantity</th>
                                        <th>Sync Date</th>
                                        <th>LK Invoice</th>
                                    </tr>
                                    </thead>
					<?php //foreach($invoices as $obj) { ?>
<!--					<tr>
						<td><?php //echo $obj->invoice_no; ?> [ <a href="sp/invoice/id=<?php //echo $obj->id; ?>/">View</a> ]</td>
						<td><?php //echo $obj->store_name; ?></td>
						<td><?php //echo $obj->invoice_dt; ?></td>
						<td style="text-align:right;"><?php //echo sprintf("%0.02f", $obj->invoice_amt); ?></td>
						<td style="text-align:right;"><?php //echo $obj->invoice_qty; ?></td>
						<td><?php //echo mmddyy($obj->createtime); ?></td>
<?php //if ($obj->ck_invoice_id) { ?>
						<td><a href="ck/invoice/id=<?php //echo $obj->ck_invoice_id; ?>/">View</a></td>
<?php //} else { ?>
						<td>Not Applicable</td>
<?php //} ?>
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
