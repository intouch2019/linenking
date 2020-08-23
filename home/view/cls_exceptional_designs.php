<?php
require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "session_check.php";
require_once "lib/core/strutil.php";
require_once "lib/core/Constants.php";

class cls_exceptional_designs extends cls_renderer {

    var $params;
    var $result;
    var $param_design_no;
    var $currUser;
    var $ctgid = "";
    var $sid = "";
    var $design_id = "";
    var $rtype = "";
    var $des_code="";

    function __construct($params = null) {
        //parent::__construct(array(UserType::Admin, UserType::CKAdmin, UserType::Dispatcher, UserType::Manager));
        $this->params = $params;
        $this->currUser = getCurrUser();
        if (isset($params['dno']))
            $this->param_design_no = $params['dno'];
        if (isset($_SESSION['design_dtrange'])) {
            $this->dtrange = $_SESSION['design_dtrange'];
        } else {
            $this->dtrange = date("d-m-Y");
        }

        if ($params && isset($params['ctgid'])) {
            $this->ctgid = $params['ctgid'];
        }
        if ($params && isset($params['sid'])) {
            $this->sid = $params['sid'];
        }
        if ($params && isset($params['design_id'])) {
            $this->design_id = $params['design_id'];
        }
        if ($params && isset($params['rtype'])) {
            $this->rtype = $params['rtype'];
        }
        if ($params && isset($params['dno']))
            $this->des_code = $params['dno'];
    }

    function extraHeaders() {
        if (!$this->currUser) {
            ?>
            <h2>Session Expired</h2>
            Your session has expired. Click <a href="">here</a> to login.
            <?php
            return;
        }
        ?>
        <style type="text/css" title="currentStyle">
            @import "js/datatables/media/css/demo_page.css";
            @import "js/datatables/media/css/demo_table.css";
        </style>
        <script src="js/datatables/media/js/jquery.dataTables.min.js"></script>       
        <link rel="stylesheet" href="css/redmond/jquery-ui-1.7.1.custom.css" type="text/css" title="ui-theme" />        
        <link rel="stylesheet" href="js/chosen/chosen.css" />
        <script type="text/javascript">
$(function() {
     var url = "ajax/tb_exceptional_designs.php?ctgid=<?php echo $this->ctgid; ?>";
    // alert(url);
      oTable = $('#tb_ex_designs').dataTable({
                    "bProcessing": true,
                    "bServerSide": true,
                    "aoColumns": [null,null,null, null,],                     
                    "aaSorting": [[0,"desc"]],                    
                    "sAjaxSource": url,
                    "iDisplayLength": 100
                });
                //                oTable.fnSort([[0, 'desc']]);
                // search on pressing Enter key only
                $('.dataTables_filter input').unbind('keyup').bind('keyup', function(e) {
                    if (e.which == 13) {
                        oTable.fnFilter($(this).val(), null, false, true);
                    }
                });
});

            
            function ctgwise(ctg_id){

                var cat_id = $("#sel_cat").val();
                //alert(cat_id);
                var ratio_type = $("#sel_ratio_type").val();
                if(cat_id == 0){
                   alert("Please select category");
                }else{                
                window.location.href = "exceptional/designs/ctgid="+cat_id;
               }
            }
                        
        </script>
        <link rel="stylesheet" href="css/bigbox.css" type="text/css" />
        <?php
    }

    public function pageContent() {
        $formResult = $this->getFormResult();
        $menuitem = "exDesign";
        include "sidemenu." . $this->currUser->usertype . ".php";
        $usertype = $this->currUser->usertype;
        $db = new DBConn();
        $designarr = array();
        if(isset($this->des_code) && trim($this->des_code)!=""){
         $designarr = explode(",",$this->des_code);
        }
            
        
        
        
        $allDesigns = array();
        
        ?>

        <div class="grid_10">

            <div class="grid_3">&nbsp;                        
            </div>
            <div class="grid_5">
                <!--<fieldset class="login">-->
                    <legend>Remove Exceptional Designs</legend>
                    <form name="exDesignForm" id="exDesignForm" method="post" action="formpost/removeExceptionalDesigns.php">
                    <table>
<!--                        <tr>
                        <td colspan="5">Select store:</td>
                        <td colspan="5">
                        <?php // if ($this->currUser->usertype != UserType::Dealer) { ?>
                            <select id="sel_store" name="sel_store" data-placeholder="Search Store" class="chzn-select" single style="width:100%" onchange="searchstore(this.value);">
                                <option value="0">Select Store</option> 
                                <?php
//                                $objs = $db->fetchObjectArray("select * from it_codes where usertype=" . UserType::Dealer . " and inactive=0 and isastore=1 and is_closed=0 order by store_name");
//
//                                foreach ($objs as $obj) {
//                                    if ($this->sid == $obj->id) {
//                                        $sel = 'selected';
//                                    } else {
//                                        $sel = '';
//                                    }
                                    ?>
                                    <option value="<?php // echo $obj->id; ?>" <?php // echo $sel; ?>><?php // echo $obj->store_name; ?></option> 
                                <?php // } ?>
                            </select>
                        <?php // } else { ?>
                            <input type="text" id="sel_store" name="sel_store" value="<?php // echo $this->currUser->store_name; ?>" readonly>
                        <?php // }
                        ?>
                            </td>
                    </tr>-->                                          
                    <tr>
                        <td colspan="5">
                        Select category:
                        </td>
                        <td colspan="5">
                        <select id="sel_cat" name="sel_cat" data-placeholder="Search Category" style="width:100%" onchange="ctgwise(this.value);"> <!--searchcat(this.value);-->
                            <option value="0">Select Category</option> 
                            <?php
                            $objs = $db->fetchObjectArray("select * from it_categories where active=1 order by name");
                            foreach ($objs as $obj) {
                                if ($this->ctgid == $obj->id) {
                                    $sel = 'selected';
                                } else {
                                    $sel = '';
                                }
                                ?>
                                <option value="<?php echo $obj->id; ?>" <?php echo $sel; ?>><?php echo $obj->name; ?></option> 
                            <?php } ?>
                        </select>
                        </td>
                    </tr>
                    
                    <tr>
                        <td colspan="5">Select Design no(s):</td>
                        <td colspan="5">
                            <select id="exdesigns" name="exdesigns[]" data-placeholder="Select Exceptional Design nos " style="width:100%" class="chzn-select" multiple > 
                            <?php if(isset($this->ctgid) && trim($this->ctgid)!="" && trim($this->ctgid)!=0){ ?>
                             <option value="-1">Select</option>
                             <?php
                                $dquery = "select d.id, d.design_no  from it_categories c, it_ck_designs d ,it_store_ratios s where s.ctg_id = c.id and s.design_id = d.id and s.ctg_id = d.ctg_id and s.is_exceptional = 1 and s.is_exceptional_active = 1  and s.ctg_id = $this->ctgid group by s.design_id";
                                $dobjs = $db->fetchObjectArray($dquery);
                                foreach($dobjs as $dobj){
                             ?>
                             <option value="<?php echo $dobj->id; ?>"><?php echo $dobj->design_no; ?></option>
                             <?php }} ?>
                           
                        </select>
                            
                        </td>    
                    </tr>                  
                    <tr>
                        <td colspan="10">
                            <input type="submit" name="Submit" value="Remove"></p>
                        </td>
                    </tr>
                    
<!--                    <tr>
                        <td colspan="10"><b>NOTE: Standing/Base ratio setting against 'All Designs' option TAKES 2-3 Minutes to complete.<br />Please Wait for it to do so.<br />Donot Hit the Browser Refresh or any other Buttons</b></td>
                    </tr>-->
                    <tr>
                        <td colspan="10">
                          <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
                        </td>
                    </tr>
                    
                    <?php // if ($this->param_design_no != "" && count($allDesigns) == 0) { ?>
                        <!--<p class="error">No Designs found matching "//<?php // echo $this->param_design_no; ?>"</p>-->	
                    <?php // } ?>
                        </table>
                    </form>
                <!--</fieldset>-->
            </div>

           <?php
              if(isset($this->ctgid) && trim($this->ctgid)!=""){
            ?>
            
            <div class="grid_12" id="tablebox" class="ui-widget-content ui-corner-bottom" style="overflow:auto;">
            <legend>Exceptional Designs List</legend>		
            <table align="center" border="1" cellpadding="0" cellspacing="0" border="0" class="display" id="tb_ex_designs">
                <thead>
                    <tr>
                            <th>Store Name</th>
                            <th>Category</th>
                            <th>Exceptional Design</th>                         
                            <th>Created DateTime</th>                                                                                
                    </tr>
                </thead>					
            </table>
            </div>
            
            <?php
              }                      
           ?> 
        </div>
        <script src="js/chosen/chosen.jquery.js" type="text/javascript"></script>
        <script type="text/javascript"> $(".chzn-select").chosen(); $(".chzn-select-deselect").chosen({allow_single_deselect: true});</script>

        <?php
    }

}
?>



