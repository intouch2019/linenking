<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");

class cls_design_type extends cls_renderer {

    var $currUser;
    var $userid;
    var $designtype;
    var $designno;
    var $ctgid;
    var $data;
    var $core;
    var $designnodetails;

    function __construct($params = null) {
        //parent::__construct(array(UserType::Admin, UserType::CKAdmin));
        $this->currUser = getCurrUser();
        $this->userid = $this->currUser->id;
        $this->params = $params;
        if (isset($params['designno']))
            $this->designno = $params['designno'];
        else
            $this->designno = null;
//        if(isset($params)){
//            print_r($params);
//        }
//        if (isset($params) && isset($params['wo_order'])) {
//            $this->worker_order = $params['wo_order'];
//        } else {
//            $this->worker_order = "";
//        }
//
        if (isset($params) && isset($params['designtype'])) {
            $this->designtype = $params['designtype'];
        }

//        if (isset($params) && isset($params['designno'])) {
//            $this->designno = $params['designno'];
//        }
        if (isset($params) && isset($params['ctgid'])) {
            $this->ctgid = $params['ctgid'];
        }
        if (isset($params) && isset($params['core'])) {
            $this->core = $params['core'];
        }

        if (isset($params) && isset($params['designnodetails'])) {
            $this->designnodetails = $params['designnodetails'];
        }
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




            function check_mrpCAP()
            {
                var mrp = $("#mrp").val();
                //             alert(mrp);
                if (mrp > 3000)
                {
                    alert("Please Check MRP Value for Selected Design");
                    $("#mrp").val("");
                }

            }

            function reseteWorkOrderData()
            {
                window.location.href = "design/type";
            }



            function getcorevalue() {

                var ctgid = document.getElementById("selectcategory").value;
                var core = document.getElementById("getcore").value;

                window.location.href = "design/type/ctgid=" + ctgid + "/core=" + core;//+"/designtype=" + designtype;
                //              $("#designids").val(designnos);
                //alert($("#design_ids").val());
            }




            function getctgdesignid() {

                var ctgid = document.getElementById("selectcategory").value;
                //                var designno = document.getElementById("designno").value;
                //                var designtype = document.getElementById("designtype").value;

                window.location.href = "design/type/ctgid=" + ctgid; //+"/designno=" + designno +"/designtype=" + designtype;

            }

            function showdesigndetails() {


                var designnodetails = document.getElementById("designnodetails").value;
                //                alert( designnodetails);

                window.location.href = "design/type/designnodetails=" + designnodetails;
            }

            function designwise() {
                var designno = $("#designno").val();
                var ctgid = document.getElementById("selectcategory").value;
                var core = document.getElementById("getcore").value;

                window.location.href = "design/type/ctgid=" + ctgid + "/core=" + core + "/designno=" + designno;
            }
            function fetchSampleExcel() {
                var core = document.getElementById("getcore").value;
                window.location.href = "formpost/DesignTypeExcel.php?core=" + core;
            }

            function  confirmupdate() {
                var designno = document.getElementById("designno").selected;
                var designno = document.getElementById("designno");
                var gettext = designno.options[designno.selectedIndex].text;
//                alert(gettext);
                //                var ctgid = document.getElementById("selectcategory").value;
                //                var getctgname = ctgid.options[ctgid.selectedIndex].text;
                var core = document.getElementById("getcore").value;
                var confirmupdate = confirm("Are you sure to update designtype for designno   " + gettext);//+ "  of category " + getctgname
                if (confirmupdate == 1) {
                    a = 0;
                    //                    alert(a);
                    document.getElementById("myForm").submit();
                } else {
                    window.location.href = "design/type/";
                }
            }
        </script>

        <?php
    }

    public function pageContent() {
        $currUser = getCurrUser();
        $menuitem = "designtypecn";
        include "sidemenu." . $currUser->usertype . ".php";
        $formResult = $this->getFormResult();
        $db = new DBConn();
        $designarr = array();
        if (isset($this->designno) && trim($this->designno) != "") {
            $designarr = explode(",", $this->designno);
        }
        ?>
        <div class="grid_10">
            <?php
            $display = "none";
            $num = 0;
            ?>
            <div class="box" style="clear:both;">
                <fieldset class="login">
                    <legend>Design Type</legend>

                    <hr/>
                  

                    <form id="myForm" action="formpost/updateCNdesigntype.php" method="post" class="old_form">
                        <div class="grid_12">

                            <div class="grid_4">
                                Category*:<br />
                                <select name="category" id='selectcategory'  data-placeholder="Choose Category..." class="chzn-select"  onchange=getctgdesignid() single style="width:100%;" required>
                                    <option value=""></option> 
                                    <?php
                                    $designid;
                                    $form_value = $this->ctgid;
                                    //  $form_value = $this->getFieldValue('category');

                                    $objs = $db->fetchObjectArray("select id,name from it_categories  where setaccesories=0 order by name");
                                    foreach ($objs as $obj) {
                                        $selected = "";
                                        if ($obj->id == $form_value) {
                                            $selected = "selected";
                                        }
                                        ?>
                                        <option value="<?php echo $obj->id; ?>" <?php
                                        if (isset($this->data) && $selected == "") {
                                            $mf_id = explode(':', $data1[6]);
                                            if ($mf_id[1] == $obj->id) {
                                                ?>selected="selected" <?php
                                                    }
                                                } else {
                                                    echo $selected;
                                                }
                                                ?>><?php
                                                    echo $obj->name;
                                                    $designid = $obj->id;
                                                    ?></option>
                                    <?php } ?>
                                </select>

                            </div>



                            <div class="grid_4">
                                Select Design Type:
                                <select name="designtype" id="getcore" data-placeholder="Choose design no..." class="chzn-select"   single style="width:100%;" onchange="getcorevalue(this.value);" required>


                                    <?php
                                    $objs = coreNoncore::getcore();
                                    foreach ($objs as $key => $value) {
                                        ?><option <?php echo ( $this->core == $key ) ? "selected" : "" ?>  value="<?php echo $key; ?>"><?php echo $value; ?></option><?php
                                        if ($this->core == $value) {
                                            $sel = "selected";
                                        } else {
                                            $sel = "";
                                        }
                                    }
                                    if (!isset($this->core)) {
                                        ?><option value=-1 selected> Select Design Type </option><?php } ?>  
                                </select>
                            </div>


                            <!--//===================================================================================================================================================-->

                            <div class="grid_4">
                                Select Design*:<br />

                                <select name="designno[]" id="designno" data-placeholder="Choose Design" class="chzn-select"  onchange="designwise()" multiple style="width:100%;">
                                    <?php
                                    if ($this->designno == -1) {
                                        $defaultSel = "selected";
                                    } else {
                                        $defaultSel = "";
                                    }
                                    ?>
                                    <option value="-1" <?php echo $defaultSel; ?>>All Designs</option> 
                                    <?php
                                    $objs = array();

                                    $designArraysobj = $db->fetchObjectArray("select id as design_id,design_no from it_ck_designs where   ctg_id = " . $this->ctgid . " order by design_no");

                                    if ($this->designno == "-1") {
                                        $designid = array();
                                        $designArraysobj = array();

                                        $designArraysobj = $db->fetchObjectArray("select id as design_id,design_no from it_ck_designs where  ctg_id = " . $this->ctgid . " order by design_no");

                                        foreach ($designArraysobj as $designArray) {
                                            foreach ($designArray as $design) {
                                                array_push($designid, $design);
                                            }
                                        }
                                    } else {
                                        $designid = explode(",", $this->designno);
                                    }

                                    foreach ($designArraysobj as $obj) {
                                        $selected = "";

                                        if ($this->designno != -1) {
                                            foreach ($designid as $did) {
                                                if ($obj->design_id == $did) {
                                                    $selected = "selected";
                                                }
                                            }
                                        }
                                        ?>
                                        <option value="<?php echo $obj->design_id; ?>" <?php echo $selected; ?>><?php echo $obj->design_no; ?></option> 
                                    <?php } ?>
                                </select>

                            </div>


                            <!--=========================================================================================================================================================-->


                        </div>

                        <div class="grid_12" style="padding:10px;">

                            <div class="grid_5"><input type="button" name="add" id="add" value="Update Design type" onclick="confirmupdate()" style="background-color:#34de63;"/> &nbsp &nbsp &nbsp &nbsp &nbsp <button name="dwnFile" id="dwnFile" onclick="fetchSampleExcel()">Download Design Type Excel</button></div>

                          </div>
                        <div>
                            <?php if ($formResult) { ?>
                                <p>
                                    &nbsp;&nbsp; <span id="statusMsg"   style="color:white; display:<?php echo $formResult->showhide; ?>;"><b><?php echo $formResult->status; ?></b></span>
                                </p>
                            <?php } ?>
                        </div>
                        

                    </form>                    
                </fieldset>
            </div> <!-- class=box -->
            <div class="box" style="clear:both;">
                <fieldset class="login">
                    <legend> Show Design Details</legend>

                 
                    <form id="myForm" action="formpost/updateCNdesigntype.php" method="post" class="old_form">
                        <div class="grid_12">
                            <div class="grid_4">
                                Enter Design Number*:<br />
                                <input type="text" name="designnodetails" id="designnodetails"  placeholder="Enter Design Number"  style="width:100%" >
                            </div>
                        </div>

                        <div class="grid_12" style="padding:10px;">
                            <input type="button" name="SDD" id="SDD" value="Show Design Details" onclick="showdesigndetails()" style="background-color:#34de63;"/>

                         
                        </div>
                    </form>                    
                </fieldset>
            </div> <!-- class=box -->
            <div class="grid_12" style="overflow-y: scroll;">
                <table style="width:100%" >
                    <tr>
                        <th colspan="18"  align="center" style="font-size:14px">Design Type</th>
                    </tr>
                    <tr>
                        <th>Sr.No.:</th>
                        <th>Design No</th>
                        <th>Category Name</th>
                        <th>Design Type</th>
                    </tr>

                    <?php
                    echo "<br>";
                    $i = 1;
                    $design_no = $db->safe($this->designnodetails);
               
                    if (isset($this->designnodetails) && $this->designnodetails != null) {
                        $designquery = "  design_no in( " . $design_no . ")";
                    } else {
                        $designquery = "";
                       
                    }


                    if ((isset($this->designnodetails) && $this->designnodetails != null)) {
                        $iquery = "select id as design_id,design_no,ctg_id,core from it_ck_designs where   $designquery order by design_no";

                        $items = $db->fetchObjectArray($iquery);
                        
                        foreach ($items as $obj) {
                            ?>
                            <tr>
                                <?php
                                $catgname = $db->fetchObject("select name from it_categories where id=$obj->ctg_id");
                                
                                ?> 
                                <td><?php echo $i; ?></td>
                                <td><?php echo $obj->design_no; ?></td>
                                <td><?php echo $catgname->name ?></td>

                                <td><?php
                                    if ($obj->core == 0) {
                                        echo "Non core";
                                    } else {
                                        echo "core";
                                    }
                                    ?></td>


                            </tr>
                            <?php
                            $i++;
                        }
                    }
                    ?>    
                    <tbody id="scrl" style="overflow-y: auto;height: 20px;overflow-x: hidden">

                </table>
            </div>

        </div>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js" type="text/javascript"></script>
        <script src="js/chosen/chosen.jquery.js" type="text/javascript"></script>
        <script type="text/javascript"> $(".chzn-select").chosen();
                                        $(".chzn-select-deselect").chosen({allow_single_deselect: true});</script>

        <?php
    }

}
?>