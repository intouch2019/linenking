<?php
require_once "view/cls_renderer.php";
require_once "lib/items/clsItems.php";
require_once "lib/core/Constants.php";

class cls_grn_release extends cls_renderer {
    var $params;
    var $currStore;
    var $storeid;
    var $designids;
    
    
    function __construct($params=null) {
        $this->currStore = getCurrUser();
        $this->params = $params;
        if (!$this->currStore) { return; }
        $this->storeid = $this->currStore->id;
        if($params && isset($params['designids'])){
           $this->designids = $params['designids']; 
        }
    }

    function extraHeaders() {
        if (!$this->currStore) {
            return;
	}
?>
<link rel="stylesheet" href="js/chosen/chosen.css')" />
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
<link rel="stylesheet" href="css/bigbox.css" type="text/css" />
<script type="text/javascript">
            $(function() {                
            $("#results").hide();
        <?php  if (isset($this->designids)) { ?>
                    var url = "ajax/tb_grn_release.php?designids=<?php echo $this->designids; ?>";
                   //  alert(url);
        <?php } ?>                   
  
                oTable = $('#tb_allitems').dataTable({
                    "bProcessing": true,
                    "bServerSide": true,
                    "aoColumns": [{"bSortable": false},null,null,null,null,null,null,{"bSortable": false}], 
                    "aaSorting": [[2,"asc"]],                    
                    "sAjaxSource": url,
//                    "sDom": 'T<"clear">lfrtip',
//                    "oTableTools": {
//                        "fnRowSelected": function ( nodes ) {
//                            alert( 'The row with ID '+nodes[0].id+' was selected' );
//                        }
//                    }
                });
//                oTable.fnSort([[0, 'desc']]);
        // search on pressing Enter key only
                $('.dataTables_filter input').unbind('keyup').bind('keyup', function(e) {
                    if (e.which == 13) {
                        oTable.fnFilter($(this).val(), null, false, true);
                    }
                });

//                $("#dialog").dialog({
//                    autoOpen: false,
//                    width: 900,
//                    height: 500,
//                    resizable: true
//                });
//                
//                $("#selectallttk").click(function () {
//                        $('.case1').attr('checked', this.checked);
//                        $("#selectall_nonttk").removeAttr("checked");
//                  });
//
//                  // if all ttk radio btns are selected, check the selectall checkbox
//                  // and viceversa
//                  $(".case1").click(function(){
//
//                      if($(".case1").length == $(".case1:checked").length) {
//                          $("#selectallttk").attr("checked", "checked");
//                      } else {
//                          $("#selectallttk").removeAttr("checked");
//                      }
//
//                  });
//                  
//                  $("#selectall_nonttk").click(function () {
//                        $('.case2').attr('checked', this.checked);
//                         $("#selectallttk").removeAttr("checked");
//                  });
//
//                  // if all non ttk radio btns are selected, check the selectall checkbox
//                  // and viceversa
//                  $(".case2").click(function(){
//
//                      if($(".case2").length == $(".case2:checked").length) {
//                          $("#selectall_nonttk").attr("checked", "checked");
//                      } else {
//                          $("#selectall_nonttk").removeAttr("checked");
//                      }
//
//                  });

            });
            
function qtyCheck(entered_qty,grn_qty){
//    alert("hello "+entered_qty+"grn_qty : "+grn_qty+"element_id: "+element_id);
//    alert("hello "+entered_qty+"grn_qty : "+grn_qty+" nm: "+nm);
    if(entered_qty < 0){
        alert("Release quantity cannot be negative");
    }else if(entered_qty > grn_qty){
        alert("Release quantity cannot be greater then Qty");
    }
}            
    
function grnRelease(theForm){
    $("#results").show();
//    $("#selectallttk").removeAttr("checked");
//    $("#selectall_nonttk").removeAttr("checked");
    var formName = theForm.name;        
    var params = $(theForm).serialize();
//    alert(params);
//    window.location.href = "formpost/grntst.php?"+params;
    var ajaxURL = "ajax/grnRelease.php?"+params;    
    //alert(ajaxURL);
    $.ajax({
        url:ajaxURL,
        dataType: 'json',
        success:function(data){
         // alert(data);
             $("#results").hide();
            console.log(data);  
            if(data.error==1){
                alert(data.message);                            
            }else{
                if(data.message == ''){ alert("Grn not released. Either no barcode selected or release qty is negative or zero");}
                else{ alert(data.message); window.location.reload();}
              
            }
             
        }
    });
}

function grnGrid(){
    var designids = $("#designos").val();
//    alert(designids);
    window.location.href = "grn/release/designids="+designids;
}
</script>
<?php

    } // extraHeaders

    public function pageContent() {
        //if ($this->currStore->usertype != UserType::Admin && $this->currStore->usertype != UserType::CKAdmin && $this->currStore->usertype != UserType::Manager) { print "Unauthorized Access"; return; }
        $formResult = $this->getFormResult();
        $menuitem="grnrelease";
        include "sidemenu.".$this->currStore->usertype.".php";
        $db = new DBConn();
        //print_r($this->params);
        //print $this->designids;
        if(isset($this->designids) && trim($this->designids)!=""){
         $designarr = explode(",",$this->designids);
        }else{
            $designarr = array();
        }
        //print_r($designarr);
        ?>
<div class="grid_10">

    <div class="grid_3">&nbsp;</div>    
    <div class="grid_5">
        <h5>Note: Designs with missing image won't show up in below selection box</h5>
        <fieldset>
        <form>
            <h5>Select Design(s) : </h5>
            <select id="designos" name="designnos" style="width:75%" class="chzn-select" multiple>
                <option value="-1">Select</option>
                <?php
                    // $query="select d.id as design_id,d.design_no,c.name,c.id from it_ck_designs d , it_categories c where d.ctg_id = c.id  and image is not null"; //and d.active = 1
                   $query = "select d.id as design_id,d.design_no,c.name,c.id from it_ck_designs d , it_categories c , it_items i  where d.ctg_id = c.id and i.design_id = d.id and i.grn_qty >  0  and d.image is not null group by d.id " ;
//                    print $query;
                   $objs = $db->fetchObjectArray($query);
                   foreach($objs as $obj){
                       $selected = "";
                       if(! empty($designarr)){
                           if(in_array($obj->design_id, $designarr)){ 
                               $selected = "selected";}
                               else{ $selected = ""; }
                       }
                       $option_value = $obj->design_no." ( ".$obj->name." ) ";
                ?>
                <option value="<?php echo $obj->design_id; ?>" <?php echo $selected; ?>><?php echo $option_value; ?></option>
                <?php } ?>
            </select>
            <br/><br/>
            <input type="button" onclick="javascript:grnGrid()" name="search" id="search" value="Search">
        </form>
        </fieldset>
        </div>
        <div  class="grid_4" id="results" name="results" style="background:#DBECFF; margin-left:10px; width:60%;">Processing. Please wait... <img src="images/loading.gif" /></div>
        <?php if(isset($this->designids) && trim($this->designids)!=""){ ?>
           <div class="clear"></div><br/> 
           
            <div class="clear"></div><br/>
            <div class="grid_12" id="tablebox" class="ui-widget-content ui-corner-bottom">
                <h5>Note: GRN will not be release in case where qty is 0</h5>
               <form name="grnReleaseForm" id="grnReleaseForm" action="" method="post" onsubmit="grnRelease(this); return false;">
                <input type="hidden" name="designids" id="designids" value="<?php echo $this->designids;?>">   
                <table cellpadding="0" cellspacing="0" border="0" class="display" id="tb_allitems">                    
                    <thead>
                        <tr>
                            <th>Select</th>
                            <th>Design No</th>
                            <th>Barcode</th>
                            <th>Style</th>
                            <th>Size</th>
                            <th>MRP</th>
                            <th>Qty</th>
                            <th>Release Qty</th>
<!--                            <th>Action<br/>
                                <input type='checkbox' name='selectallttk' id='selectallttk' value='1'/>ALL TTK &nbsp;&nbsp;<input type='checkbox' name='selectall_nonttk' id='selectall_nonttk'/>All Non TTK 
                            </th>                            -->
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="5" class="dataTables_empty">Loading data from server</td>
                        </tr>
                    </tbody>
                </table>
                <input class="topmenu" type="Submit" name="Submit" value="Save" />   
                </form>   
                </div>
        
        <?php  } ?>
    </div>
   
<script src="<?php  CdnUrl('js/chosen/chosen.jquery.js'); ?>" type="text/javascript"></script>
<script type="text/javascript"> $(".chzn-select").chosen(); $(".chzn-select-deselect").chosen({allow_single_deselect:true}); </script>
    <?php
    } //pageContent
}//class
?>
