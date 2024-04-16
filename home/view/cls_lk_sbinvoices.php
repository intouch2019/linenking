<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");
require_once ("lib/core/strutil.php");

class cls_lk_sbinvoices extends cls_renderer{

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
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
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
<!-- Include jQuery -->

<script src="js/datatables/media/js/jquery.dataTables.min.js"></script>
<script src="js/datatables/media/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="js/chosen/chosen.css" />
<link rel="stylesheet" href="css/bigbox.css" type="text/css" />
<script type="text/javascript">

$(function() {
     var url = "ajax/tb_Sbinvoices.php";
//     alert(url);
      oTable = $('#tb_allinvoices').dataTable({
                    "bProcessing": true,
                    "bServerSide": true,
                    "aoColumns": [null,null, null, null, null,{"bSortable": false},null, {"bSortable": false},null,null,null],                     
                    "aaSorting": [[0,"desc"]],                    
                    "sAjaxSource": url,
                    "iDisplayLength": 50
                });
                //                oTable.fnSort([[0, 'desc']]);
                // search on pressing Enter key only
//                $('.dataTables_filter input').unbind('keyup').bind('keyup', function(e) {
//                    if (e.which == 13) {
//                        oTable.fnFilter($(this).val(), null, false, true);
//                    }
//                });
                
                $('#statusFilter').on('change', function() {
//    alert("hiii");
    var status = $(this).val(); // Get selected value
//    alert("Selected status: " + status); // Debug alert
    // Filter table based on selected value
    oTable.api().column(6).search(status).draw();
});
});

function showInvoiceDetails( invid){
    window.location.href = "lk/sbinvoice/id="+invid;
}

function Saveinvoicedetails( invid){

//   alert(invid);

 var utrval = document.getElementById("utr" + invid).value;
  var remarkval = document.getElementById("remark"+invid).value;

//             var query = "update it_saleback_invoices set utr=".utrval.",remark=".remarkval.",is_sb_transit_complete=2 where id=".invid.""; 
//              var query = "update it_saleback_invoices set utr="+utrval+",remark="+remarkval+",is_sb_transit_complete=2 where id="+invid+""; 
//             alert(query);

$.ajax({
        type: "get",
        url: "ajax/update_salebackinvoicepayment.php",///var/www/html/cottonking/home/ajax/update_salebackinvoicepayment.php
        data: {
            utr: utrval,
            remark: remarkval,
            invid: invid
        },
        success: function(response) {
            // Handle the response
//            alert("hii");
            if (response === 'success') {
                alert("Update successful!");
            }else if(response === ''){

            } else {
                alert(response);

            }
        },
        error: function(xhr, status, error) {
            alert("Error occurred: " + error);
        }
    });
   window.location.href = "lk/sbinvoices";
}
</script>		
		<?php
		}
		public function pageContent() {
			$menuitem = "lksbinvoices";
			include "sidemenu.".$this->currUser->usertype.".php";
			$formResult = $this->getFormResult();
?>
<div class="grid_10">
	<?php $_SESSION['form_post'] = array(); ?>
	<?php
	
	$display="none";
	$num = 0;
	$db = new DBConn();
	?>
	
         <div class="grid_12" id="tablebox" class="ui-widget-content ui-corner-bottom" style="overflow:auto;">
             <legend>LinenKing SaleBack Invoices</legend>
             <select id="statusFilter">
    <option value="">All</option>
    <option value="0">In Transit</option>
    <option value="1">Received at Warehouse</option>
    <option value="2">Payment Done</option>
</select>
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
                        <th>Submit</th>
                        <th></th>
                    </tr>
                </thead>
	     </table>
         </div>
</div>

<?php
	}
}
?>