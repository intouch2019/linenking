<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");

class cls_admin_payment_gateway_hdfc extends cls_renderer {

    var $currUser;
    var $userid;
    var $sid = "";
    var $invoiceid = "";

    function __construct($params = null) {
        $this->currUser = getCurrUser();
        $this->userid = $this->currUser->id;
        $this->params = $params;
        if ($params && isset($params['sid'])) {
            $this->sid = $params['sid'];
        }
        if ($params && isset($params['invoiceid'])) {
            $this->invoiceid = $params['invoiceid'];
        }

        if ($params && isset($params['invoice_no'])) {
            $this->invoice_no = $params['invoice_no'];
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
                if (mrp <= 0 || mrp >= 500000)
                {
                    alert("Please Check Invoice Value should be greater than 0 and less than 500000");
                    $("#mrp").val("");
                }

            }

            function searchstore() {
                var store_id = $("#storeid").val();
                window.location.href = "admin/payment/gateway/hdfc/sid=" + store_id;
            }


        </script>

        <?php
    }

    public function pageContent() {
        $currUser = getCurrUser();
        $menuitem = "viewpaymenthdfc";
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
                    <legend>Payment HDFC NACH Stores</legend>

                    <p>Select values for all fields below.</p>

                    <form id="payment_details" name="payment_details" enctype="multipart/form-data" method="POST" action="formpost/add_paymentGateway_hdfc.php">

                    <div class="grid_12">

                        <div class="grid_4">
                            Store*:<br />
                            <select name="storeid" data-placeholder="Select StoreName..." class="chzn-select"  single style="width:100%" id="storeid"  onchange="searchstore();">
                                <option value=""></option> 
                                <?php
                                $form_value = $this->getFieldValue('store');
                                $objs = $db->fetchObjectArray("select id,store_name from it_codes where usertype=" . UserType::Dealer . " and is_closed=0 order by store_name");
                                foreach ($objs as $obj) {
                                    $selected = "";
                                    if ($obj->id == $this->sid) {
                                        $selected = "selected";
                                    }
                                    ?>
                                    <option value="<?php echo $obj->id; ?>" <?php
                                    if (isset($this->data)) {
                                        $mf_id = explode(':', $data1[9]);
                                        if ($mf_id[1] == $obj->id) {
                                            ?>selected="selected" <?php
                                                }
                                            } else {
                                                echo $selected;
                                            }
                                            ?>><?php echo $obj->store_name; ?></option> 
                                        <?php } ?>
                            </select>
                        </div>
                        <div class="grid_4">
                            Mobile no*:<br />
 <select id="Mobile_no" name="Mobile_no" data-placeholder="Mobile No..." class="chzn-select" single style="width:100%;">
                             <?php
                             $objs = $db->fetchObjectArray("select id,phone  from it_codes where id =$this->sid");
                                foreach ($objs as $obj) {

                                    $selected = "";
                                    if ($obj->id == $this->sid) {
                                        $selected = "selected";
                                    }
                                    ?>
                                    <option value="<?php echo $obj->phone; ?>"><?php echo $obj->phone ?></option> 
                                <?php } ?>
                            </select>

                        </div >
                        <div class="grid_4">
                            
                            
                            Email id*:<br />
                             <select id="Email_id" name="Email_id" data-placeholder="Email Id..." class="chzn-select" single style="width:100%;">

                                <?php
                                $objs = $db->fetchObjectArray("select id,email  from it_codes where id =$this->sid");

                                foreach ($objs as $obj) {

                                    $selected = "";
                                    if ($obj->id == $this->sid) {
                                        $selected = "selected";
                                    }
                                    ?>
                                    <option value="<?php echo $obj->email; ?>"><?php echo $obj->email ?></option> 
                                <?php } ?>
                            </select>
                        </div >


                    </div>


                    </br> </br> </br>

                    <div class="grid_12">



                        <div class="grid_4">

                            Invoice nos*:<br /><input type="text" id="invoiceid" name="invoiceid" placeholder="Invoice Nos..." style="width:100%;height:25px;font-size:15px;" value="<?php ?>"/>

                        </div>


                        <div class="grid_4">

                            Invoice Amount*:<br /><input type="text" id="mrp" name="mrp" placeholder="Invoice Amount..." style="width:100%;height:25px;font-size:15px;" value="<?php
                            if (isset($this->data)) {
                                $mrp = explode(':', $data1[3]);
                                echo $mrp[1];
                            }
                            ?>" onchange="check_mrpCAP()"/>

                        </div>

                        <div class="grid_4">

                            Remark || Reason:</br>
                            <textarea id="description" name="description" rows="3" cols="40"></textarea>

                        </div>

                    </div> 
                    <div class="grid_12">
                        <div class="grid_4">
                            <input type="submit" id="submit" name="submit" value="Send Payment Request...">

                        </div>  
                    </div>
                     </form>

                    <?php if ($formResult) { ?>
                        <p>
                            <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
                        </p>
                    <?php } ?>
            </div>

        </fieldset>
        </div> <!-- class=box -->
        </div>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js" type="text/javascript"></script>
        <script src="<?php CdnUrl('js/chosen/chosen.jquery.js'); ?>" type="text/javascript"></script>
        <script type="text/javascript"> $(".chzn-select").chosen();
                                $(".chzn-select-deselect").chosen({allow_single_deselect: true});</script>

        <?php
    }
}
?>