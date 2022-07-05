<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");
require_once "lib/core/strutil.php";
require_once "session_check.php";

class cls_report_storestocklimit extends cls_renderer{

        var $currUser;
        var $userid;
        var $dtrange;
        var $params;
        
       
        function __construct($params=null) {

        }

	function extraHeaders() {
        ?>
<style type="text/css" title="currentStyle">
            @import "js/datatables/media/css/demo_page.css";
            @import "js/datatables/media/css/demo_table.css";
        </style>
<script src="js/datatables/media/js/jquery.dataTables.min.js"></script>
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
<script type="text/javaScript">    
$(function(){    
    var url = "ajax/tb_msl.php";
//alert(url);
    oTable = $('#tb_msl').dataTable( {
	"bProcessing": true,
	"bServerSide": true,
        "aoColumns": [ null, null, {bSortable:false}, {bSortable:false}, {bSortable:false}, {bSortable:false}, {bSortable:false}, {bSortable:false}, {bSortable:false}, {bSortable:false},{bSortable:false}], //,{bSortable:false} ,{bSortable:false} 
	"sAjaxSource": url
    } );
// search on pressing Enter key only
    $('.dataTables_filter input').unbind('keyup').bind('keyup', function(e){
	if (e.which == 11){                     
		oTable.fnFilter($(this).val(), null, false, true);
	}
    });    
});

function genRep(){
    window.location.href="formpost/generateMSLExcel.php";
}
</script>
<link rel="stylesheet" href="css/bigbox.css" type="text/css" />
        
        <?php
        }

        public function pageContent() {
            $currUser = getCurrUser();
            $menuitem = "reportstocklimit";
            include "sidemenu.".$currUser->usertype.".php";    
//            if($currUser->usertype == UserType::Admin || $currUser->usertype == UserType::CKAdmin){
?>
<div class="grid_10">
    <div class="grid_12">
        <button onclick="javascript:genRep();">Generate Report</button>
    </div>
    <br><br><br><br>
    <div class="grid_12" id="tablebox" class="ui-widget-content ui-corner-bottom">
    <h5>MSL Report</h5>
        <table cellpadding="0" cellspacing="0" border="0" class="display" id="tb_msl">
	<thead>
            <tr> 
                 <th>ID</th>                        
                <th>Store Name</th>
                <th>Store Apparels Current Stock</th>
                <!--<th>Store mask Current Stock</th>-->
                <th>Store Total Current Stock</th>
                <th>Store Apparels Stock in Transit</th>
                <!--<th>Store Mask Stock in Transit</th>-->
                <th>Store Total in Transit</th>
                <th>Store Apparels Total Stock</th>
                <!--<th>Store Mask Total Stock</th>-->
                <th>Store Total Stock</th>
                <th>Store Minimum Stock Level</th>
                <th>Store Maximum Stock Level</th>                
                <th>Difference</th>
	    </tr>
	</thead>
	<tbody>
		<tr>
			<td colspan="13" class="dataTables_empty">Loading data from server</td>
		</tr>
	</tbody>
    </table>
    </div>
</div>
            <?php // }else{ print "You are not authorized to access this page";}
	}
}
?>