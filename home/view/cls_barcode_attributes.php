
<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");

class cls_barcode_attributes extends cls_renderer {

    var $currUser;
    var $userid;

    function __construct($params = null) {
        //parent::__construct(array(UserType::Admin, UserType::CKAdmin));
        $this->currUser = getCurrUser();
        $this->userid = $this->currUser->id;
    }

    function extraHeaders() {
        ?>
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
        <link rel="stylesheet" href="css/bigbox.css" type="text/css" />
        <script type="text/javascript">
            $(function () {
                var atype = $("#atype").val();
                if (atype == "categories") {
                    // document.getElementById('vatDiv').style.visibility = 'visible';
                    //  document.getElementById('cstDiv').style.visibility = 'visible';
                    document.getElementById('hsnDiv').style.visibility = 'visible';
                    document.getElementById('marginDiv').style.visibility = 'visible';
                    document.getElementById('accesetDiv').style.visibility = 'visible';
                    document.getElementById('othersetDiv').style.visibility = 'visible';
                } else {
                    document.getElementById('vatDiv').style.visibility = 'hidden';
                    document.getElementById('cstDiv').style.visibility = 'hidden';
                    document.getElementById('hsnDiv').style.visibility = 'hidden';
                    document.getElementById('marginDiv').style.visibility = 'hidden';
                    document.getElementById('accesetDiv').style.visibility = 'hidden';
                    document.getElementById('othersetDiv').style.visibility = 'hidden';
                }
            });


            function ajaxValues(inputObj, e) {
                var selectVal = $("#atype").val();
                return ajax_showOptions(inputObj, 'type=' + selectVal, e);
            }

            function enterPressed() {
                var key = window.event.keyCode;
                // If the user has pressed enter
                if (key == 13) {
                    alert("Enter detected");
                    return false;
                } else {
                    return true;
                }
            }

            function cateadd(id) {
                if (id == "categories") {
                    // alert(id);     
                    // document.getElementById('vatDiv').style.visibility = 'visible';
                    // document.getElementById('cstDiv').style.visibility = 'visible';
                    document.getElementById('hsnDiv').style.visibility = 'visible';
                    document.getElementById('marginDiv').style.visibility = 'visible';
                    document.getElementById('accesetDiv').style.visibility = 'visible';
                    document.getElementById('othersetDiv').style.visibility = 'visible';
                } else {
                    document.getElementById('vatDiv').style.visibility = 'hidden';
                    document.getElementById('cstDiv').style.visibility = 'hidden';
                    document.getElementById('hsnDiv').style.visibility = 'hidden';
                    document.getElementById('marginDiv').style.visibility = 'hidden';
                    document.getElementById('accesetDiv').style.visibility = 'hidden';
                    document.getElementById('othersetDiv').style.visibility = 'hidden';
                }

            }
        </script>

        <?php
    }

    public function pageContent() {
        $currUser = getCurrUser();
        $menuitem = "battributes";
        include "sidemenu." . $currUser->usertype . ".php";
        $formResult = $this->getFormResult();
        $db = new DBConn();
        ?>
        <div class="grid_10">
            <?php
            $display = "none";
            $num = 0;
            ?>
            <div class="box" style="clear:both;">
                <fieldset class="login"> 
                    <legend>Add New Attribute Values</legend>
                    <p>Use this page to define attributes. Select the Attribute Type and enter <key>*</key> to get a list of current values. To add a new value, type the value in the box and click on the <key>Add Attribute</key> button.</p>
                    <form action="formpost/addAttribute.php" method="post">
                        <div class="grid_12">
                            <div class="grid_2">
                                <select name="atype" id="atype" onchange="cateadd(this.value);">
                                    <?php
                                    $atypes = array(
                                        "categories" => "Categories",
                                        "prod_types" => "Production Types",
                                        "styles" => "Styles",
                                        "sizes" => "Sizes",
                                        "materials" => "Materials",
                                        "brands" => "Brands",
                                        "fabric_types" => "Fabric Types",
                                        "mfg_by" => "Manufactured By"
                                    );
                                    $form_atype = $this->getFieldValue('atype');
                                    foreach ($atypes as $avalue => $atype) {
                                        $selected = "";
                                        if ($avalue == $form_atype) {
                                            $selected = "selected";
                                        }
                                        ?>
                                        <option value="<?php echo $avalue; ?>" <?php echo $selected; ?>><?php echo $atype; ?></option>
        <?php } ?>
                                </select>
                            </div>
                            <div class="grid_2">&nbsp;</div>  
                            <div class="grid_2">
                                <textarea id="avalue" name="avalue" value="" onkeyup="ajaxValues(this, event)" cols="32" rows="1"></textarea>
                            </div>

                        </div> <!-- grid_12 -->
                        <div class="grid_12" style="padding:10px;">
                            <div class="grid_4" id="hsnDiv" name="vatDiv" >
                                HSNCODE*:<br/>
                                <input type="text" name="hsncode" id="hsncode" value="<?php echo $this->getFieldValue("hsncode"); ?>">
                            </div>  

                            <div class="grid_4" id="vatDiv" name="vatDiv" style="display:none">
                                VAT Type*:<br />
                                <select name="vat_type" data-placeholder="Choose VAT Type..." class="chzn-select" single style="width:100%;">
                                    <option value=""></option> 
                                    <?php
                                    $vattype = $this->getFieldValue("vat_type");
                                    $objs = $db->fetchObjectArray("select * from it_taxes where tax_type = " . taxType::VAT . " order by name");
                                    foreach ($objs as $obj) {
                                        $selected = "";
                                        if ($obj->id == $vattype) {
                                            $selected = "selected";
                                        }
                                        $per = $obj->percent * 100;
                                        ?>
                                        <option value="<?php echo $obj->id; ?>" <?php echo $selected; ?>><?php echo $obj->name; ?> (<?php echo $per; ?>%)</option> 
        <?php } ?>
                                </select>
                            </div>  
                            <div class="grid_4" id="cstDiv" name="cstDiv" style="display:none">    
                                CST TYpe*:<br/>
                                <select name="cst_type" data-placeholder="Choose CST TYpe.." class="chzn-select" single style="width:100%;">
                                    <option value=""></option>  
                                    <?php
                                    $csttype = $this->getFieldValue("cst_type");
                                    $objs = $db->fetchObjectArray("select * from it_taxes where tax_type = " . taxType::CST . " order by name");
                                    foreach ($objs as $obj) {
                                        $selected = "";
                                        if ($obj->id == $csttype) {
                                            $selected = "selected";
                                        }
                                        $per = $obj->percent * 100;
                                        ?>
                                        <option value="<?php echo $obj->id ?>" <?php echo $selected; ?>><?php echo $obj->name; ?>(<?php echo $per; ?>%)</option>
        <?php } ?>
                                </select>

                            </div>  
                            <div class="grid_12" id="marginDiv" style="padding:10px;" id="resp">
                                SELECT MARGIN *: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <span style="font-size:1.1em">  <input type="radio" name="margin" value="<?php echo Cat_MarginType::Regular; ?>" > <?php echo trim(Cat_MarginType::getName(Cat_MarginType::Regular)); ?> 
                                    <input type="radio" name="margin" value="<?php echo Cat_MarginType::margin0; ?>" > <?php echo trim(Cat_MarginType::getName(Cat_MarginType::margin0)); ?> 

                                </span>
                            </div>
                            <div class="grid_12" id="othersetDiv" style="padding:10px;" id="resp">
                        SET AS OTHER *: &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <span style="font-size:1.1em">  <input type="radio" name="othercat" value="1" > Yes 
                                    <input type="radio" name="othercat" value="0"  >No
                        
                        </span>
                    </div>
                <div class="grid_12" id="accesetDiv" style="padding:10px;" id="resp">
                        SET AS ACCESORIES *: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <span style="font-size:1.1em">  <input type="radio" name="accesorycat" value="1" > Yes 
                                    <input type="radio" name="accesorycat" value="0"  >No
                        
                        </span>
                    </div>
                        </div>   

                        <div class="grid_12" style="padding:10px;" id="resp">
                            <input type="submit" name="addattr" id="addattr" value="Add Attribute" style="background-color:#34de63;"/>

        <?php if ($formResult) { ?>
                                <p>
                                    <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
                                </p>
        <?php } ?>
                        </div>
                    </form>
                </fieldset>
            </div> <!-- class=box -->
        </div>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js" type="text/javascript"></script>
        <script src="js/chosen/chosen.jquery.js" type="text/javascript"></script>
        <script type="text/javascript"> $(".chzn-select").chosen();
                                    $(".chzn-select-deselect").chosen({allow_single_deselect: true});</script>


        <?php
    }

}
?>
