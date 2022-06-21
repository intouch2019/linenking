<?php
require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "session_check.php";
require_once "lib/orders/clsOrders.php";
require_once "lib/items/clsItems.php";

/**
 * Description of cls_Resync_batch
 *
 * @author Dante
 */
class cls_Resync_batch extends cls_renderer {
  
      var $currUser;
    var $userid;
    var $worker_order;
    var $design_id;
    var $data;
    var $cat;
  

    function __construct($params = null) {
        //parent::__construct(array(UserType::Admin, UserType::CKAdmin));
        $this->currUser = getCurrUser();
        $this->userid = $this->currUser->id;
        $this->params = $params;

        
        if (isset($params) && isset($params['cat'])) {
            $this->cat = $params['cat'];
        }
         if (isset($params) && isset($params['design_id'])) {
            $this->design_id = $params['design_id'];
        }
    }

    
    function extraHeaders() {
        ?>

        <link rel="stylesheet" href="js/chosen/chosen.css" />
        <script type="text/javascript" src=" js/ajax.js "></script>
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
     
    function ctgwise() {
                var ctg_id = document.getElementById("sel_cat").value;
                
               // window.location.href = "Resync/batch/cat=" + ctg_id;

            
            }

            function designwise() {

                var designnos = $("#designos").val();
                $("#designids").val(designnos);
                var ctg_id = $("#sel_cat").val();

              //  window.location.href = "Resync/batch/design_id=" + designnos;
             
            }

        </script>

        <?php
    }

    public function pageContent() {
        $formResult = $this->getFormResult();
        $currUser = getCurrUser();
        $menuitem = "Resyncbatch";
        include "sidemenu." . $currUser->usertype . ".php";
       
        $db = new DBConn();
       ?>
       
        <div class="grid_10">
            <?php
            $display = "none";
            $num = 0;
      //
            ?>
            <div class="box" style="clear:both;">
                <fieldset class="login">
                    <legend>Resync Barcode, Categories and Mrp</legend>

                    <hr/>

                    <form action="formpost/ResyncBarcode.php" method="post" class="old_form">
                        <p><h2>If u want to sync only category , barcode and Mrp then <b style="color:Tomato;">search</b> seperately and press  <b style="color:Tomato;">resync batch </b>button .</h2></p>
                  
                        <div class="grid_12">

                            <div class="grid_4">
                                <td colspan="5"> Category:</td>
                                <td colspan="5">
                                    <select id="sel_cat" name="sel_cat" data-placeholder="Search Category" class="chzn-select"  style="width:100%" onchange="ctgwise(this.value);"> <!--searchcat(this.value);-->
                                        <option value="0">Select Category</option> 
                                        <?php
                                        $objs = $db->fetchObjectArray("select id,name from it_categories where active=1 order by name");
                                        foreach ($objs as $obj) {
                                            if ($this->cat == $obj->id) {
                                                $sel = 'selected';
                                            } else {
                                                $sel = '';
                                            }
                                            ?>
                                            <option value="<?php echo $obj->id; ?>" <?php echo $sel; ?>><?php echo $obj->name; ?></option> 
                                        <?php } ?>
                                    </select>
                                </td>
                            </div>

                    <div class="grid_4">

                                <td colspan="5">Design:</td>
                                <td colspan="5">
                                    <select id="designos" name="designnos" data-placeholder="Search Design" style="width:100%" class="chzn-select"  onchange="designwise(this.value);"> 
                                      <option value="0">Select Design</option> 
                                        <?php
                               
                                        $query = "select id as design_id,design_no from it_ck_designs  order by design_no";

                                            $objs = $db->fetchObjectArray($query);
                                            if ($objs != NULL) {
                                                foreach ($objs as $obj) {
                                               if ($this->design_id == $obj->design_id) {
                                                $sel = 'selected';
                                            } else {
                                                $sel = '';
                                            }                                                    ?>
                                                      <option value="<?php echo $obj->design_id; ?>" <?php echo $sel; ?>><?php echo $obj->design_no; ?></option>
                                                <?php }
                                            } ?>
                                        </select>

                                    </td>    
                                </div>
                 
                            
                              <div class="grid_4">

                                <td colspan="5">Mrp:</td>
                                <td colspan="5">
                                    <select id="mrp" name="mrp" data-placeholder="Search mrp" style="width:100%" class="chzn-select" onchange=""> 
                                      <option value="0">Select Mrp</option> 
                                        <?php
                               
                                        $query = "select  distinct (mrp) as mrp from it_items order by mrp desc ";
                            
                                            $objs = $db->fetchObjectArray($query);
                                            if ($objs != NULL) {
                                                foreach ($objs as $obj) {
                                        
                                                    $obj_mrp = $obj->mrp;
                                                    ?>
                                                    <option value="<?php echo $obj->mrp; ?>"><?php echo $obj_mrp; ?></option>
                                                <?php }
                                            } ?>
                                        </select>

                                    </td>    
                                </div>
        
                        </div>

                        <div class="grid_12" style="padding:10px;">
                            <input type="submit" name="add" id="add" value="Resync batch" style="background-color:#34de63;"/>
                           

        <?php  if ($formResult) { ?>
                                <p>
                                    <span id="statusMsg" class="<?php ?>" style="display:<?php echo $formResult->showhide; ?>;"><h6 style="color:white;"><br><?php echo $formResult->status; ?></br></h6></span>
                                </p>
        <?php } ?>
                        </div>
                    </form>                    
                </fieldset>
            </div> <!-- class=box -->
        </div>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js" type="text/javascript"></script>
        <script src="js/chosen/chosen.jquery.js " type="text/javascript"></script>
        <script type="text/javascript"> $(".chzn-select").chosen();
                                            $(".chzn-select-deselect").chosen({allow_single_deselect: true});</script>

        <?php
    }


}
