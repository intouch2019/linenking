<?php

require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");
require_once "lib/core/strutil.php";
require_once "session_check.php";

class cls_report_store_stock_summary extends cls_renderer{

        var $currUser;
        var $userid;
        var $dtrange;
        var $params;
        var $storeid = "-1";
        
       
        function __construct($params=null) {
            $this->currUser = getCurrUser();
//            if (isset($_SESSION['account_dtrange'])) { $this->dtrange = $_SESSION['account_dtrange']; }
//            else { $this->dtrange = date("d-m-Y"); }
            if($params && isset($params['storeid'])){
                $this->storeid = $params['storeid'];
            }
            if($params && isset($params['dtrange'])){
                $this->dtrange = $params['dtrange'];
            }
        }

	function extraHeaders() {
        ?>   
        <style type="text/css" title="currentStyle">
            @import "js/datatables/media/css/demo_page.css";
            @import "js/datatables/media/css/demo_table.css";
            @import "css/ui.daterangepicker.css";
            @import "css/redmond/jquery-ui-1.7.1.custom.css";
        </style>
        <script type="text/javascript" src="jqueryui/js/jquery-ui-1.7.1.custom.min.js"></script>
        <script type="text/javascript" src="js/daterangepicker.jQuery.js"></script>
        <script src="js/datatables/media/js/jquery.dataTables.min.js"></script>                
        <script src="js/chosen/chosen.jquery.js" type="text/javascript"></script>        
                
        <link rel="stylesheet" href="js/chosen/chosen.css" />
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
$(".chzn-select").chosen(); $(".chzn-select-deselect").chosen({allow_single_deselect:true});   
    var url = "ajax/tb_stock_summary.php?storeid=<?php echo $this->storeid; ?>&dtrange=<?php echo $this->dtrange; ?>";
//alert(url);
    oTable = $('#tb_stocksummary').dataTable( {
	"bProcessing": true,
	"bServerSide": true,
        //"bFilter": false,
        "aoColumns": [ null,null, null, null, null, null , null],        
	"sAjaxSource": url
    } );
// search on pressing Enter key only
    $('.dataTables_filter input').unbind('keyup').bind('keyup', function(e){
	if (e.which == 13){                     
		oTable.fnFilter($(this).val(), null, false, true);
	}
    });
    
    $('#dateselect').daterangepicker({
	 	dateFormat: 'dd-mm-yy',
		arrows:false,
		closeOnSelect:true,
		onOpen: function() { isOpen=true; },
		onClose: function() { isOpen=false; },
		onChange: function() {
		if (isOpen) { return; }
		var dtrange = $("#dateselect").val();
		$.ajax({
			url: "savesession.php?name=account_dtrange&value="+dtrange,
			success: function(data) {
				//window.location.reload();
                               // window.location.href = "report/store/stock/summary/dtrange = <?php echo $this->dtrange; ?>";
                                
			}
		});
		}
	});
});



function genRep(){    
    var store_ids = $("#store").val();
    var dtrange = $("#dateselect").val();
    if(store_ids == null){
        alert("Please select store(s) first");
    }
//    else if(dtrange == null){
//        alert("Please select date range");
//    }
    else{
        window.location.href="report/store/stock/summary/storeid="+store_ids+"/dtrange="+dtrange;
    }       
}
function genExcelRep(){
   var store_ids = $("#store").val();
    var dtrange = $("#dateselect").val();
    if(store_ids == null){
        alert("Please select store(s) first");
    }
//    else if(dtrange == null){
//        alert("Please select date range");
//    }
    else{
        window.location.href="formpost/genStoreStockSummaryExcel.php?storeid="+store_ids+"&dtrange="+dtrange;
    }   
}
</script>
 <link rel="stylesheet" href="js/chosen/chosen.css" />
        
        
        <?php
        }

        public function pageContent() {
            $currUser = getCurrUser();
            $menuitem = "ssummary";
            include "sidemenu.".$currUser->usertype.".php";    
//            if($currUser->usertype == UserType::Admin || $currUser->usertype == UserType::CKAdmin){
?>
<div class="grid_10">
    <div class="grid_12">       
       <div class="grid_4">
           
        <b>Select Store*:</b><br/>
        <select name="store" id="store" data-placeholder="Choose Store" class="chzn-select" multiple style="width:100%;">
                <?php if( $this->storeid == -1 ){
                                   $defaultSel = "selected";
                             }else{ $defaultSel = ""; } ?>
                <option value="-1" <?php echo $defaultSel;?>>All Stores</option> 
<?php
$objs = $db->fetchObjectArray("select * from it_codes where usertype= ".UserType::Dealer." and id in (select store_id from executive_assign where exe_id=".getCurrUser()->id." ) order by store_name");

if($this->storeid== "-1"){
    $storeid = array();     
    $allstoreArrays=$db->fetchObjectArray("select id from it_codes where usertype = 4 ");
    foreach($allstoreArrays as $storeArray){
        foreach($storeArray as $store){
            array_push($storeid,$store);
        }
    }
}else{
  $storeid = explode(",",$this->storeid);  
}
//print_r($allst);
print_r($storeid);
foreach ($objs as $obj) {        
	$selected="";
//	if (isset($this->storeidreport) && $obj->id==$this->storeidreport) { $selected = "selected"; }
        if ($this->storeid != -1){
            foreach($storeid as $sid){
                if($obj->id==$sid) 
                { $selected = "selected"; }
            }
        }
?>
          <option value="<?php echo $obj->id; ?>" <?php echo $selected; ?>><?php echo $obj->store_name; ?></option> 
<?php } ?>
	</select>
	</div>
        <div class="grid_8">
                <span style="font-weight:bold;">Date Filter : </span></br> <input size="17" type="text" id="dateselect" name="dateselect" value="<?php echo $this->dtrange; ?>" /> (Click to see date options)
        </div>
        <div class="grid_12">
            <div class="grid_8">
            <input type="button" name="genrep" value="Generate Report" onclick="javascript:genRep();">            
            </div>            
            <div class="grid_4">
            <input type="button" name="genexcel" value="Download Excel" onclick="javascript:genExcelRep();">           
            </div>
            <br> 
            <h7>Select the options and click on "Generate Report" button</h7>            
        </div>
    </div>
    <div class="clear"></div>
    <br>
    <div class="grid_12" id="tablebox" class="ui-widget-content ui-corner-bottom">
    <h5>Store Stock Summary Report</h5>
        <table cellpadding="0" cellspacing="0" border="0" class="display" id="tb_stocksummary">
	<thead>
            <tr> 
                <th>ID</th>                        
                <th>Store Name</th>
                <th>Store Stock Limit</th>
                <th>Stock DateTime</th>
                <th>Store Stock in Value</th>
                <th>Store Stock in Quantity</th>
                <th>Store Stock in Transit</th>                
	    </tr>
	</thead>
	<tbody>
		<tr>
			<td colspan="7" class="dataTables_empty">Loading data from server</td>
		</tr>
	</tbody>
    </table>
    </div>
</div>
            <?php // }else{ print "You are not authorized to access this page";}
	}
}
?>