<?php
require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "session_check.php";
require_once "lib/core/strutil.php";
require_once "lib/core/Constants.php";

class cls_invoice_sendmailandsms extends cls_renderer {

    var $params;
    var $result;
    var $currUser;
    var $sid = "";
    var $invoiceid = "";
    var $sent = "";

    function __construct($params = null) {
        //parent::__construct(array(UserType::Admin, UserType::CKAdmin, UserType::Dispatcher, UserType::Manager));
        $this->params = $params;
        $this->currUser = getCurrUser();
        
        if ($params && isset($params['sid'])) {
            $this->sid = $params['sid'];
        }
        if ($params && isset($params['invoiceid'])) {
            $this->invoiceid = $params['invoiceid'];
        }
        if ($params && isset($params['sent'])) {
            $this->sent = $params['sent'];
        }
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
        <script type="text/javascript" src="jqueryui/js/jquery-ui-1.7.1.custom.min.js"></script>
        <script type="text/javascript" src="js/daterangepicker.jQuery.js"></script>
        <link rel="stylesheet" href="css/ui.daterangepicker.css" type="text/css" />
        <link rel="stylesheet" href="css/redmond/jquery-ui-1.7.1.custom.css" type="text/css" title="ui-theme" />

        <link rel="stylesheet" href="js/chosen/chosen.css" />
        <script type="text/javascript" src="js/ajax.js"></script>   
        <script language="JavaScript" src="js/tigra/validator.js"></script>
        <script type="text/javascript">

            function searchstore(store_id) {
//                var store_id = $("#sel_store").val();
                window.location.href = "invoice/sendmailandsms/sid=" + store_id ;
            }
            function searchinvoice(invoice_id) {
                var store_id = $("#sel_store").val();
                var invoice_ids = $("#sel_invoice").val();
                window.location.href = "invoice/sendmailandsms/sid=" + store_id + "/invoiceid=" + invoice_ids ;
            }
            
            function submitform(){ // for users of type dealer
                var storeid = $("#sel_store").val();
                var invoiceid = $("#sel_invoice").val();
                var transporter = $("#sel_transporter").val();
                var vehicleno = $("#vehicleno").val();
                var drivername = $("#drivername").val();
                var drivermob = $("#drivermob").val();
//                alert(storeid+"--"+invoiceid+"--"+transporterid+"--"+vehicleno+"--"+drivername+"--"+drivermob);
                if(storeid=="0" || invoiceid=="0" || transporter=="0" || vehicleno=="" || drivername=="" || drivermob==""){
                    alert("Please Fill all the fields.");
                    return;
                }else{
                    window.location.href = "formpost/sendmailandsms.php?storeid="+storeid +"&invoiceid="+invoiceid +"&transporter="+transporter +"&vehicleno="+vehicleno +"&drivername="+drivername +"&drivermob="+drivermob; 
                }
            }
            
        </script>
        <?php
    }

    public function pageContent() {
        $formResult = $this->getFormResult();
        $menuitem = "invoicesendmailandsms";
        include "sidemenu." . $this->currUser->usertype . ".php";
        $usertype = $this->currUser->usertype;
        $db = new DBConn();
        ?>

        
        <div class="grid_10">
            
            <div class="grid_3">&nbsp;</div>
            <div class="grid_5">
                <!--<fieldset class="login">-->
                    <legend>Send Mail and SMS</legend>
                    <table>
                        <tr>
                        <td colspan="5">Select store:</td>
                        <td colspan="5">
                            <select id="sel_store" name="sel_store" data-placeholder="Search Store" class="chzn-select" single style="width:100%" onchange="searchstore(this.value);">
                                <option value="0">Select Store</option> 
                                <?php
                                $objs = $db->fetchObjectArray("select id,store_name from it_codes where usertype=" . UserType::Dealer . " and is_closed=0 order by store_name");

                                $sids = explode(',', $this->sid);
                                foreach ($objs as $obj) {
//                                    if ($this->sid == $obj->id) {
                                    if (in_array($obj->id, $sids)){
                                        $sel = 'selected';
                                    } else {
                                        $sel = '';
                                    }
                                    ?>
                                    <option value="<?php echo $obj->id; ?>" <?php echo $sel; ?>><?php echo $obj->store_name; ?></option> 
                                <?php } ?>
                            </select>
                            </td>
                    </tr>
                    <?php if($this->sid != ""){?>
                    <tr>
                        <td colspan="5">Select Invoice:</td>
                        <td colspan="5">
                            <select id="sel_invoice" name="sel_invoice" data-placeholder="Search Invoice" class="chzn-select" multiple style="width:100%" onchange="searchinvoice(this.value);">
                                <option value="0">Select Invoice</option> 
                                <?php
                                $date = date("Y-m-d");
                                $date_arr = explode('-', $date);
                                if($date_arr[1] < 4){$date_arr[0] = $date_arr[0]-1;}
                                
                                $objs = $db->fetchObjectArray("select id,invoice_no from it_sp_invoices where is_procsdForRetail = 0 and store_id = $this->sid and invoice_dt >= '$date_arr[0]-04-01 00:00:00' order by invoice_no desc");

                                $invoiceids = explode(',', $this->invoiceid);
                                foreach ($objs as $obj) {
//                                    if ($this->sid == $obj->id) {
                                    if (in_array($obj->id, $invoiceids)){
                                        $sel = 'selected';
                                    } else {
                                        $sel = '';
                                    }
                                    ?>
                                    <option value="<?php echo $obj->id; ?>" <?php echo $sel; ?>><?php echo $obj->invoice_no; ?></option> 
                                <?php } ?>
                            </select>
                            </td>
                    </tr>
                    <?php } ?>
                    <?php if($this->invoiceid != ""){?>
                    <tr>
                        <td colspan="5">Select Transporter:</td>
                        <td colspan="5">
                            <select id="sel_transporter" name="sel_transporter" data-placeholder="Search Transporter" class="chzn-select" single style="width:100%">
                                <option value="0">Select Transporter</option> 
                                <?php
                                $objs = $db->fetchObjectArray("select distinct transportdtl from it_sp_invoices where id in ($this->invoiceid)");

//                                $invoiceids = explode(',', $this->invoiceid);
                                foreach ($objs as $obj) {
//                                    if ($this->sid == $obj->id) {
//                                    if (in_array($obj->id, $invoiceids)){
                                        $sel = 'selected';
//                                    } else {
//                                        $sel = '';
//                                    }
                                    ?>
                                    <option value="<?php echo $obj->transportdtl; ?>" <?php echo $sel; ?>><?php echo $obj->transportdtl; ?></option> 
                                <?php } ?>
                            </select>
                            </td>
                    </tr>
                    
                    <tr>
                        <td colspan="5">Enter Vehicle No:</td>
                        <td colspan="5">
                            <input type="text" id="vehicleno" name="vehicleno" placeholder="Enter vehicle No" style="width:100%" required>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="5">Enter Driver Name:</td>
                        <td colspan="5">
                            <input type="text" id="drivername" name="drivername" placeholder="Enter Driver Name" style="width:100%" required>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="5">Enter Driver Mob:</td>
                        <td colspan="5">
                            <input type="text" id="drivermob" name="drivermob" placeholder="Enter Driver Mob" style="width:100%" required>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="5"></td>
                        <td colspan="5">
                            <input type="button" id="submit" name="submit" value="Send SMS & Email" onclick="submitform();">
                        </td>
                    </tr>
                    <?php } ?>
                    <?php if($this->sent == 1){?>
                    <tr>
                        <td colspan="5"></td>
                        <td colspan="5">
                            <span id="statusMsg" style="color: green;font: bold;font-size: 13px;">Email & SMS Sent Successfully...!!!</span>
                        </td>
                    </tr>
                    <?php }elseif($this->sent == 2){ ?>
                     <tr>
                        <td colspan="5"></td>
                        <td colspan="5">
                            <span id="statusMsg" style="color: red;font: bold;font-size: 13px;">Something Wrong Email or SMS Not Sent.</span>
                        </td>
                    </tr>
                    <?php } ?>
                        </table>
                <!--</fieldset>-->
            </div>
        
        </div> <!--end div class 10-->
<!--        <form method="post" action="formpost/addRatioDetails.php"></form>-->
        <script src="js/chosen/chosen.jquery.js" type="text/javascript"></script>
    <script type="text/javascript"> $(".chzn-select").chosen(); $(".chzn-select-deselect").chosen({allow_single_deselect:true}); </script>
        <?php
    }

}
?>



