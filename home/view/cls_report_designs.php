<?php
require_once ("view/cls_renderer.php");
require_once ("lib/db/DBConn.php");
require_once "lib/core/Constants.php";

class cls_report_designs extends cls_renderer {
    var $params;
    var $storeid;
    var $ctg="";
    var $pg=0;
    var $active=0;
    var $currUser;
    function __construct($params=null) {
       // parent::__construct(array(UserType::Admin, UserType::CKAdmin, UserType::Dispatcher, UserType::Manager));
       $this->currUser = getCurrUser();
        if (isset($params['active']))
            $this->active = $params['active'];
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
<link rel="stylesheet" href="js/chosen/chosen.css')" />
<link rel="stylesheet" href="css/bigbox.css" type="text/css" />
<script type="text/javascript">
$(function() {
     var url = "ajax/tb_designmrp_status.php?is_design_mrp_active=<?php echo $this->active; ?>";
     //alert(url);
      oTable = $('#tb_alldesignmrp').dataTable({
                    "bProcessing": true,
                    "bServerSide": true,
                    "aoColumns": [null,null, null, null, null,null],                                         
                    "aaSorting": [[3,"asc"]],                    
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
    window.location.href = "lk/invoice/id="+invid;
}

function genExcelDesignRep(){
     window.location.href="formpost/genActiveInactiveDesignExcel.php";
} 

</script>

<?php    }

    public function pageContent() {
        $formResult = $this->getFormResult();
        $menuitem="aidesigns";
        include "sidemenu.".$this->currUser->usertype.".php";
        $active = "";
        $imgex = "";
        ?>
<div class="grid_10">
<select onchange="document.location.href=this.options[this.selectedIndex].value;">
<option value="report/designs/active=0" <?php if ($this->active == 0) echo 'selected'; ?> >Show Inactive</option>
<option value="report/designs/active=1" <?php if ($this->active != 0) echo 'selected'; ?> >Show Active</option>
</select> 

<input type="button" name="genexcel" value="Export to Excel" onclick="javascript:genExcelDesignRep();"> 

   <br> <br>
<div class="grid_12" id="tablebox" class="ui-widget-content ui-corner-bottom" style="overflow:auto;">
	
<table align="center" border="1" cellpadding="0" cellspacing="0" border="0" class="display" id="tb_alldesignmrp">
        <thead>
            <tr>
                    <th>Design No</th>
                    <th>Category</th>
                    <th>MRP</th>
                    <th>LineNo</th>
                    <th>RackNo</th>
                    <th>Active</th>                    
            </tr>
        </thead>
   </table>
   </div>
    
    
    
    
    
<!--    (Type Ctrl-F to search on this page)
            <?php
//            $db = new DBConn();
            ?>
        <table>
        <tr><th>Design No</th><th>Category</th><th>MRP</th><th>LineNo</th><th>RackNo</th><th>Active</th></tr>
<?php
//            $query = "select d.*,ctg.name as ctg_name,i.MRP,i.is_design_mrp_active from it_ck_designs d,it_categories ctg , it_items i where d.ctg_id=ctg.id and d.design_no = i.design_no and d.ctg_id = i.ctg_id and d.id = i.design_id and  i.is_design_mrp_active=$this->active order by lineno, rackno";
//            
//            $activedesigns = $db->fetchObjectArray($query);
//            $row_no = 0;
//            foreach ($activedesigns as $design) {
//                print "<tr>";
//                print "<td>$design->design_no</td>";
//                print "<td>$design->ctg_name</td>";
//                print "<td>$design->MRP</td>";
//                print "<td>$design->lineno</td>";
//                print "<td>$design->rackno</td>";
//                print "<td>$design->active</td>";
//                print "</tr>";
//            }
?>
        </table>-->

        
</div>
    <?php
    }
}

