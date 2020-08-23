<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");
require_once ("lib/core/strutil.php");

class cls_approve_dgcreditnotes extends cls_renderer{

	var $currUser;
	var $userid;
		
	function __construct($params=null) {
		//parent::__construct(array(UserType::Admin, UserType::CKAdmin));
                //ini_set('max_execution_time', 300);///
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
     var url = "ajax/tb_dgcreditnote.php";
//     alert(url);//
      oTable = $('#tb_creditnoteses').dataTable({
                    "bProcessing": true,
                    "bServerSide": true,
                    "aoColumns": [{"bSortable": false},null, null, null, {"bSortable": false},{"bSortable": false}, {"bSortable": false},{"bSortable": false},{"bSortable": false},{"bSortable": false}],                     
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
//    alert("dg/creditnote/id="+invid);
    window.location.href = "dg/creditnote/id="+invid;
}
</script>		
		<?php
		}

		public function pageContent() {
			$menuitem = "approve_creditnote";
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
             <legend>CottonKing DG Creditnotes</legend>		
             <table align="center" border="1" cellpadding="0" cellspacing="0" border="0" class="display" id="tb_creditnoteses">
					<thead>
                                            <tr>
                                                    <th>Id</th>
                                                    <th>Credit Note No.</th>
                                                    <th>Date</th>
                                                    <th style="text-align:left;">Store Name</th>
                                                    <th style="text-align:right;">Amount</th>
                                                    <th style="text-align:right;">Quantity</th>
                                                  
                                                    <th>Create By</th>
                                                    <th>Approve By</th>
                                                    <th>Status</th>
                                                    <th>Creditnote Details</th>  
                                            </tr>
                                        </thead> 
		</table>
                </div>
 </div>

<?php
	}
}
?>
