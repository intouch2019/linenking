<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");
require_once "lib/core/strutil.php";
require_once "session_check.php";

class cls_report_sincentive_summary extends cls_renderer {

    var $currUser;
    var $userid;
    var $dtrange;
    var $storeidreport = null;
    var $params;
    var $salesmainid;
    var $totqty;
    var $amt;
    var $atv;
    var $upt;
    var $billno;
    var $nettotal;
    var $storeloggedin = -1;
    var $fields = array();
    var $returntotal;
    var $incentive;
    var $bill_no;
    var $ctg;

    function __construct($params = null) {
        //parent::__construct(array(UserType::Admin, UserType::CKAdmin,  UserType::Manager));                
        $this->currUser = getCurrUser();
        $this->userid = $this->currUser->id;
        if (isset($_SESSION['account_dtrange'])) {
            $this->dtrange = $_SESSION['account_dtrange'];
        } else {
            $this->dtrange = date("d-m-Y");
        }
        if (isset($params['str']))
            $this->storeidreport = $params['str'];
        else
            $this->storeidreport = null;
        if (isset($params['store'])) {
            $this->fields['store'] = $params['store'];
            $this->store = $params['store'];
        } else
            $this->fields['store'] = "0";
        if (isset($params['salesmainid'])) {
            $this->fields['salesmainid'] = $params['salesmainid'];
            $this->salesmainid = $params['salesmainid'];
        } else
            $this->fields['salesmainid'] = "-";
        if (isset($params['totqty'])) {
            $this->fields['totqty'] = $params['totqty'];
            $this->totqty = $params['totqty'];
        } else
            $this->fields['totqty'] = "0";
        if (isset($params['amt'])) {
            $this->fields['amt'] = $params['amt'];
            $this->amt = $params['amt'];
        } else
            $this->fields['amt'] = "0";
        if (isset($params['atv'])) {
            $this->fields['atv'] = $params['atv'];
            $this->atv = $params['atv'];
        } else
            $this->fields['atv'] = "0";
        if (isset($params['upt'])) {
            $this->fields['upt'] = $params['upt'];
            $this->upt = $params['upt'];
        } else
            $this->fields['upt'] = "0";
        if (isset($params['billno'])) {
            $this->fields['billno'] = $params['billno'];
            $this->billno = $params['billno'];
        } else
            $this->fields['billno'] = "0";
        if (isset($params['nettotal'])) {
            $this->fields['nettotal'] = $params['nettotal'];
            $this->nettotal = $params['nettotal'];
        } else
            $this->fields['nettotal'] = "0";
        if (isset($params['returntotal'])) {
            $this->fields['returntotal'] = $params['returntotal'];
            $this->returntotal = $params['returntotal'];
        } else
            $this->fields['returntotal'] = "0";
        if (isset($params['incentive'])) {
            $this->fields['incentive'] = $params['incentive'];
            $this->incentive = $params['incentive'];
        } else
            $this->fields['incentive'] = "0";
        if (isset($params['bill_no'])) {
            $this->fields['bill_no'] = $params['bill_no'];
            $this->bill_no = $params['bill_no'];
        } else
            $this->fields['bill_no'] = "-";
        if (isset($params['ctg'])) {
            $this->fields['ctg'] = $params['ctg'];
            $this->ctg = $params['ctg'];
        } else
            $this->fields['ctg'] = "0";



        if ($this->currUser->usertype == UserType::Dealer) {
            $this->storeidreport = $this->currUser->id;
            $this->storeloggedin = 1;
        }
    }

    function extraHeaders() {
        ?>
<script type="text/javascript" src="jqueryui/js/jquery-ui-1.7.1.custom.min.js"></script>
<script type="text/javascript" src="js/daterangepicker.jQuery.js"></script>
<link rel="stylesheet" href="css/ui.daterangepicker.css" type="text/css" />
<link rel="stylesheet" href="css/redmond/jquery-ui-1.7.1.custom.css" type="text/css" title="ui-theme" />

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
            var storeid = '<?php echo $this->storeidreport; ?>';  
            var storeloggedin = '<?php echo $this->storeloggedin; ?>';
            //alert("STORE ID: "+storeid);
            //alert("STORE LOGGED IN: "+storeloggedin);
            $(function(){
            $(".chzn-select").chosen(); $(".chzn-select-deselect").chosen({allow_single_deselect:true});
            var isOpen=false;
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
            }
            }); 
            }
            });
            });

            function reloadreport2(){

            var dateselect=$('#dateselect').val();
            alert(dateselect);
            if(storeloggedin == '-1'){
            storeid = $('#store').val();
            alert("SID:"+storeid);
            //            return;
            window.location.href = "report/sincentive/summary/str=" + storeid + "/dateselect="+dateselect;
            }
            }

            function reloadreport() {
                storeid = $('#store').val();
            var dateselect=$('#dateselect').val();
            //            alert(dateselect);
            //            return;
            var reporttype=$('input[name=report]:radio:checked').val();       
            $('#selectRight option').attr('selected', 'selected');
            if(storeloggedin == '-1'){
                if(storeid=='-1'){
                alert("Please Select Store");
                return;
            }
            storeid = $('#store').val();
            //            alert("SID:"+storeid);
            //            return;
            }
            //var storeid = $('#store').val();  
            //alert(storeid);
            if (storeid!=null && storeid!='') {
            var multiplevalues = $('#selectRight').val();
            var append='';
            var sequence=1;
            for (var i=0;i<multiplevalues.length;i++) {
            append += "/"+multiplevalues[i]+"="+sequence;
            sequence++;
            }
            //            window.location.href = "report/sincentive1/str=" + storeid;
            window.location.href = "report/sincentive/summary/str=" + storeid + "/store=1/salesmainid=2/incentive=3/totqty=4/amt=5/atv=6/upt=7/ctg=8/bill_no=9";
            } else {
            alert("please select a store to genereate a report");
            } 
            }

            function moveToRightOrLeft(side){
            var listLeft=document.getElementById('selectLeft');
            var listRight=document.getElementById('selectRight');

            if(side==1){
            if(listLeft.options.length==0){
            alert('You have already moved all fields to Right');
            return false;
            }else{
            var selectedCountry=listLeft.options.selectedIndex;

            move(listRight,listLeft.options[selectedCountry].value,listLeft.options[selectedCountry].text);
            listLeft.remove(selectedCountry);

            if(listLeft.options.length>0){
            listLeft.options[selectedCountry].selected=true;
            }
            }
            } else if(side==2){
            if(listRight.options.length==0){
            alert('You have already moved all fields to Left');
            return false;
            }else{
            var selectedCountry=listRight.options.selectedIndex;

            move(listLeft,listRight.options[selectedCountry].value,listRight.options[selectedCountry].text);
            listRight.remove(selectedCountry);

            if(listRight.options.length>0){
            listRight.options[selectedCountry].selected=true;
            }
            }
            }
            }

            function move(listBoxTo,optionValue,optionDisplayText){
            var newOption = document.createElement("option"); 
            newOption.value = optionValue; 
            newOption.text = optionDisplayText; 
            newOption.selected = true;
            listBoxTo.add(newOption, null); 
            return true; 
            }

        </script>
    <link rel="stylesheet" href="css/bigbox.css" type="text/css" />

        

        <?php
    }

    public function pageContent() {
        //$currUser = getCurrUser();
        $menuitem = "sIncentiveRep1";
        include "sidemenu." . $this->currUser->usertype . ".php";
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
                    <legend>Salesman Incentive Report (Accessories Sale Not Considered)</legend>	
                    <form action="" method="" onsubmit="reloadreport();
                                    return false;">
                        <div class="grid_12">
                              
                                                                        <!--<p>Select store(s) below to view their current stock.</p>-->    
                                <div class="grid_4">
                                    <b>Select Store*:</b><br/>
                                    <select name="store" id="store" data-placeholder="Choose Store" class="chzn-select"  style="width:100%;">
                                        <?php
                                        if ($this->storeidreport == -1) {
                                            $defaultSel = "selected";
                                        } else {
                                            $defaultSel = "";
                                        }
                                        ?>
                                        <option value="-1" <?php echo $defaultSel; ?> >Select Store</option> 
                                         <!--<option value="-1" <?php // echo $defaultSel; ?> hidden>Select Store</option>--> 
                                        <?php
                                        if ($this->currUser->usertype != UserType::Dealer) { 
                                        $objs = $db->fetchObjectArray("select id,store_name from it_codes where usertype=" . UserType::Dealer . " order by store_name");
                                        }else{
                                             $storeid=$this->currUser->id;
                                             $objs = $db->fetchObjectArray("select id,store_name from it_codes where id=$storeid and usertype=" . UserType::Dealer . " order by store_name");
                                        }
                                        if ($this->storeidreport == "-1") {
                                            $storeid = array();
                                            $allstoreArrays = $db->fetchObjectArray("select id from it_codes where usertype = " . UserType::Dealer);
                                            foreach ($allstoreArrays as $storeArray) {
                                                foreach ($storeArray as $store) {
                                                    array_push($storeid, $store);
                                                }
                                            }
                                        } else {
                                            $storeid = explode(",", $this->storeidreport);
                                        }
                                        ;
                                        foreach ($objs as $obj) {
                                            $selected = "";
                                            if ($this->storeidreport != -1) {
                                                foreach ($storeid as $sid) {
                                                    if ($obj->id == $sid) {
                                                        $selected = "selected";
                                                    }
                                                }
                                            }
                                            ?>
                                            <option value="<?php echo $obj->id; ?>" <?php echo $selected; ?>><?php echo $obj->store_name; ?></option> 
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="grid_4">
                                    <span style="font-weight:bold;">Date Filter : </span></br> <input size="20" type="text" id="dateselect" name="dateselect" value="<?php echo $this->dtrange; ?>" /> (Click to see date options)
                                </div>
                            </div>
                       
                        <div class="clear"></div>
                        <br>
                        <div class="grid_12" id="itemselection">
                            <!--		<div class="grid_12" >-->
                            <div class="grid_12" style="display:block">
                                <table border="0" colspan="4" style="display:none">
                                    <tr>
                                        <td colspan="5">Custom Report Field Selection:</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">Available Fields </td>
                                        <td colspan="1">&nbsp;</td>
                                        <td colspan="2">Your Selection </td>
                                    </tr>
                                    <tr>
                                        <td rowspan="3" colspan="2" align="right"><label>
                                                <select name="selectLeft" size="10" width="100%" style="width:200px;" id="selectLeft"> 
                                                    <!--                                          <option value="itemctg">Item Category</option> billno-->

                                                    <!--                                               <option value="billno" selected>Ticket No</option>-->
                                                    <option value="ctg" selected >Category</option>
                                                    <option value="bill_no" >Bill No</option> 

                                                </select>
                                            </label></td>
                                        <td colspan="1" rowspan="3" style="vertical-align:middle;">
                                            <input name="btnRight" type="button" id="btnRight" value="&gt;&gt;" onClick="javaScript:moveToRightOrLeft(1);">
                                            <br/><br/>
                                            <input name="btnLeft" type="button" id="btnLeft" value="&lt;&lt;" onClick="javaScript:moveToRightOrLeft(2);">
                                        </td>
                                        <td rowspan="3" colspan="2" align="left">
                                            <select name="selectRight" multiple size="10" style="width:200px;" id="selectRight">
                                                <option value="store">Store Name</option>
                                                <option value="salesmainid" >Salesman Id</option>
                                                <option value="incentive" >Incentive</option> 

                                                <option value="totqty"  >Sale Quantity</option>
                                                <option value="amt" >Net Sale </option>
                                                <option value="atv" >ATV</option>
                                                <option value="upt" >UPT</option>
                                                <!--                                         <option value="salesmainid" selected>Salesman Id</option>
                                                                                        <option value="totqty" selected>Total Quantity</option>                                                                                                                      
                                                                                        <option value="amt" selected>Amount</option>-->
                                            </select>
                                        </td>
                                    </tr>
                                </table>

                                <style>
                                    table {
                                        /*width: 100%;*/
                                        border: 1px solid black;
                                        /*border-collapse: collapse;*/
                                    }/*
                                    */
                                    td {
                                        border: 1px solid black;
                                        border-bottom: 3px double black;
                                        /*padding: 5px;*/
                                        /*text-align: center;*/
                                    }/*

                                </style>

                                <?php
//                                        echo ;
                                $createtimequery = "";
                                $daterange = $this->dtrange; // or "12-04-2025 - 14-04-2025"

                                $dates = explode(' - ', $daterange);

                                if (count($dates) == 2) {
                                    $toDate = DateTime::createFromFormat('d-m-Y', trim($dates[0]))->format('Y-m-d');
                                    $fromDate = DateTime::createFromFormat('d-m-Y', trim($dates[1]))->format('Y-m-d');

                                    $createtimequery = "bill_datetime between '$toDate 00:00:00' and '$fromDate 23:59:59'";
                                } else {
                                    $date = DateTime::createFromFormat('d-m-Y', trim($daterange))->format('Y-m-d');
                                    $createtimequery = "bill_datetime between '$date 00:00:00' and '$date 23:59:59'";
                                }
                                ?>
                                <?php if (isset($this->storeidreport)) { //12 fields       ?>
                                    <?php
                                    if ($this->storeidreport == -1) {
                                        $salesmannoquery = $db->fetchObjectArray("SELECT DISTINCT(salesman_no) FROM it_salesmanreport ORDER BY salesman_no ASC;");
                                                                                                                                                                                                                                                            
                                        $fetchMult = "SELECT sr.bill_no, sr.salesman_no, sr.createtime,i.ctg_id FROM it_salesmanreport sr JOIN it_items i ON sr.barcode  = i.barcode WHERE i.ctg_id NOT IN (16,13,14,18,19,20,24,25,26,27,28,29,33,35,36,38,39,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54,55,56,57,58,59,60,61,62,63,64) AND sr.$createtimequery GROUP BY sr.bill_no, sr.salesman_no HAVING COUNT(sr.bill_no) > 1 ";

                                    } else {
                                        $salesmannoquery = $db->fetchObjectArray("SELECT DISTINCT(salesman_no) FROM it_salesmanreport where store_id in ($this->storeidreport) ORDER BY salesman_no ASC;");
                                        $fetchMult = "SELECT sr.bill_no, sr.salesman_no, sr.createtime,i.ctg_id FROM it_salesmanreport sr JOIN it_items i ON sr.barcode  = i.barcode WHERE sr.store_id in ($this->storeidreport)  and i.ctg_id NOT IN (16,13,14,18,19,20,24,25,26,27,28,29,33,35,36,38,39,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54,55,56,57,58,59,60,61,62,63,64) AND sr.$createtimequery GROUP BY sr.bill_no, sr.salesman_no HAVING COUNT(sr.bill_no) > 1";
                                    }

                                    $parameters = ["Total Incentive", "Single Qty Bills", "Qty in Single Bills", "Single Qty Value", "Single Qty Incentive Amt", "Multiple Qty No Membership Bills", "Qty in multiple bills", "Multiple Qty Bill Value", "Multiple Qty Incentive Amt", "Membership Bills 1st Hurdle (5999₹)", "Qty 1st Hurdle", "Value 1st Hurdle", "Membership 1st Hurdle Incentive Amt", "Membership Bills 2nd Hurdle (9999₹)", "Qty 2nd Hurdle", "Value 2nd Hurdle", "Membership 2nd Hurdle Incentive Amt"];
                                    $fetchMultobjescts = $db->fetchObjectArray($fetchMult);
                                    $salesmanHurdle1Counts = array();
                                    $salesmanHurdle2Counts = array();
                                    $salesmanHurdle1Qty = array();
                                    $salesmanHurdle2Qty = array();
                                    $salesmanHurdle1Amount = array();
                                    $salesmanHurdle2Amount = array();
                                    $singleQtyMap = [];
                                    $singleqtybillss = 0;
                                    $singleqtybillssvalue = 0;
                                    foreach ($fetchMultobjescts as $billobjs) {
                                        
                                        if ($this->storeidreport != -1) {
                                            $orderinfoofmultybillno = $db->fetchObject("SELECT id,quantity FROM it_orders WHERE bill_no = '{$billobjs->bill_no}' AND store_id in ($this->storeidreport)");
                                        } else {
                                            $orderinfoofmultybillno = $db->fetchObject("SELECT id,quantity FROM it_orders WHERE bill_no = '{$billobjs->bill_no}'");
                                        }

                                        if (empty($orderinfoofmultybillno) ) {
                                            continue;
                                        }
                                        $billQty = 0;
                                        $finalQty = 0;
                                        $getcnqty = 0;
                                        
                                        $getcreditnote = $db->fetchObject("select msg from it_order_payments where order_id=$orderinfoofmultybillno->id and payment_name='paperin'");

                                        $cnusedbillno = "";
                                        if (!empty($getcreditnote)) {
                                            $cnusedbillno = $getcreditnote->msg;
                                            $getcnqty = $db->fetchObject("select quantity from it_orders where bill_no='$cnusedbillno'");
                                        }

                                        $getorderidsumqty = $db->fetchObject(" SELECT SUM(oi.quantity) AS quantity FROM it_order_items oi JOIN it_items i ON oi.barcode  = i.barcode  WHERE oi.order_id = $orderinfoofmultybillno->id AND i.ctg_id NOT IN (16,13,14,18,19,20,24,25,26,27,28,29,33,35,36,38,39,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54,55,56,57,58,59,60,61,62,63,64) ");


                                        $billQty = $getorderidsumqty->quantity;
                                        if (!empty($getcnqty->quantity)) {
                                            if ($getcnqty->quantity > $billQty && $getcnqty->quantity > 0) {
                                                $finalQty = $getcnqty->quantity - $billQty;
                                            } else if ($getcnqty->quantity < 0) {
                                                $finalQty = $billQty + $getcnqty->quantity;

                                            } else {
                                                $finalQty = $billQty - $getcnqty->quantity;
                                            }
                                        } else {
                                            $finalQty = $billQty;
                                        }
                                        $secondhurlebillsno = array();
                                        $isHurdle1 = false;
                                        $isHurdle2 = false;
                                        $isMultiQty = false;
                                        if ($finalQty > 1) {
                                            $orderinfopayments = $db->fetchObjectArray("SELECT order_id,amount,payment_name FROM it_order_payments WHERE order_id = {$orderinfoofmultybillno->id}");

                                            $found500 = false;

                                            foreach ($orderinfopayments as $oipobj) {
                                                $paymentNameLower = strtolower($oipobj->payment_name);
                                                if (stripos($oipobj->payment_name, 'Loyalty') !== false) {
                                               
                                                    if ($oipobj->amount == 500) {
                                                        $found500 = true;
                                                        $isHurdle1 = true;
                                                        $checknetvalue1sthurdle = $db->fetchObject("select net_total from it_orders where id='$oipobj->order_id'");

                                                    }
                                                    if ($oipobj->amount == 1000) {
                                                   
                                                        $isHurdle2 = true;
                                                        $checknetvalue2ndhurdle = $db->fetchObject("select net_total from it_orders where id='$oipobj->order_id'");
                                                        if (!isset($salesmanHurdle2Counts[$billobjs->salesman_no])) {
                                                            $salesmanHurdle2Counts[$billobjs->salesman_no] = 0;
                                                        }
                                                        $salesmanHurdle2Counts[$billobjs->salesman_no]++;

                                                        if (!isset($salesmanHurdle2Qty[$billobjs->salesman_no])) {
                                                            $salesmanHurdle2Qty[$billobjs->salesman_no] = 0;
                                                        }
                                                        $salesmanHurdle2Qty[$billobjs->salesman_no] += $finalQty;

                                                        if (!isset($salesmanHurdle2Amount[$billobjs->salesman_no])) {
                                                            $salesmanHurdle2Amount[$billobjs->salesman_no] = 0;
                                                        }
                                                        $checkaccformultyqty = $db->fetchObject("SELECT SUM(sr.net_total) AS total_net, i.ctg_id FROM it_salesmanreport sr JOIN it_items i ON sr.barcode  = i.barcode JOIN it_categories ct ON i.ctg_id = ct.id WHERE sr.bill_no = '$billobjs->bill_no' AND ct.name  IN ( 'BLAZER+TIE+BROOCH', 'TROUSER PIECE', 'TIE', 'BLAZER+PANT PIECE', 'BLAZER+SHIRT+ TROUSER+TIE', '60''Lee', 'SHIRT PIECE', '60'' Li Dobby Linen', '60''s Li Dobby Linen', '60''s Li Dobby', 'INTERLOCK', 'Others', 'Starch Spray', 'Fabric', 'Handkerchiefs', 'Salwar', 'Pajama', 'Cloth Bag', 'Thermal Roll', 'Wallet & Belt Combo', 'KURTA PIECE', 'Cabin Bag', 'Trolly Bag', 'Paper Bags', 'SOCKS', 'Jacket Buttons', 'Watch Pocket', 'Jeans Button & Revet Set', 'Gift Box', 'Paper Carry Bags', '2 Layer Mask', 'Non Surgical Pollution Mask', 'Ear Loop Extender', 'Stool', 'Shoes', 'Belt', 'Job Work', 'Zipper', 'Mannequin', 'Wooden Shirt Hanger', 'Wooden Trouser Hanger', 'Accessories Stand' ) and sr.store_id in ($this->storeidreport) ;");

                                                        if (!empty($checkaccformultyqty)) {
                                                            $salesmanHurdle2Amount[$billobjs->salesman_no] += $checknetvalue2ndhurdle->net_total - $checkaccformultyqty->total_net;
                                                        } else {
                                                            $salesmanHurdle2Amount[$billobjs->salesman_no] += $checknetvalue2ndhurdle->net_total;
                                                        }
                                                    }
                                                } elseif (
                                                        stripos($paymentNameLower, 'cash') !== false ||
                                                        stripos($paymentNameLower, 'corporatesale') !== false ||
                                                        stripos($paymentNameLower, 'giftcoupon') !== false ||
                                                        stripos($paymentNameLower, 'level1discount') !== false ||
                                                        stripos($paymentNameLower, 'level1discountcorp') !== false ||
                                                        stripos($paymentNameLower, 'magcard') !== false ||
                                                        stripos($paymentNameLower, 'paperin') !== false ||
                                                        stripos($paymentNameLower, 'upi') !== false
                                                ) {
                                                    $isMultiQty = true;
                                                }
                                            }
                                            if (count($orderinfopayments) > 1 && $found500) {

                                                if (!isset($salesmanHurdle1Counts[$billobjs->salesman_no])) {
                                                    $salesmanHurdle1Counts[$billobjs->salesman_no] = 0;
                                                }
                                                $salesmanHurdle1Counts[$billobjs->salesman_no]++;

                                                if (!isset($salesmanHurdle1Qty[$billobjs->salesman_no])) {
                                                    $salesmanHurdle1Qty[$billobjs->salesman_no] = 0;
                                                }
                                                $salesmanHurdle1Qty[$billobjs->salesman_no] += $finalQty;
                                                if (!isset($salesmanHurdle1Amount[$billobjs->salesman_no])) {
                                                    $salesmanHurdle1Amount[$billobjs->salesman_no] = 0;
                                                }
                                                $checkaccformultyqty = $db->fetchObject("SELECT SUM(sr.net_total) AS total_net, i.ctg_id FROM it_salesmanreport sr JOIN it_items i ON sr.barcode  = i.barcode JOIN it_categories ct ON i.ctg_id = ct.id WHERE sr.bill_no = '$billobjs->bill_no' AND ct.name  IN ( 'BLAZER+TIE+BROOCH', 'TROUSER PIECE', 'TIE', 'BLAZER+PANT PIECE', 'BLAZER+SHIRT+ TROUSER+TIE', '60''Lee', 'SHIRT PIECE', '60'' Li Dobby Linen', '60''s Li Dobby Linen', '60''s Li Dobby', 'INTERLOCK', 'Others', 'Starch Spray', 'Fabric', 'Handkerchiefs', 'Salwar', 'Pajama', 'Cloth Bag', 'Thermal Roll', 'Wallet & Belt Combo', 'KURTA PIECE', 'Cabin Bag', 'Trolly Bag', 'Paper Bags', 'SOCKS', 'Jacket Buttons', 'Watch Pocket', 'Jeans Button & Revet Set', 'Gift Box', 'Paper Carry Bags', '2 Layer Mask', 'Non Surgical Pollution Mask', 'Ear Loop Extender', 'Stool', 'Shoes', 'Belt', 'Job Work', 'Zipper', 'Mannequin', 'Wooden Shirt Hanger', 'Wooden Trouser Hanger', 'Accessories Stand' ) and sr.store_id in ($this->storeidreport);");

                                                if (!empty($checkaccformultyqty)) {
                                                    $salesmanHurdle1Amount[$billobjs->salesman_no] += $checknetvalue1sthurdle->net_total - $checkaccformultyqty->total_net;
                                                } else {
                                                    $salesmanHurdle1Amount[$billobjs->salesman_no] += $checknetvalue1sthurdle->net_total;
                                                }
                                            }
                                            if (!$isHurdle1 && !$isHurdle2 && $isMultiQty) {
                                                $checkNetValue = $db->fetchObject("SELECT id,net_total FROM it_orders WHERE id = {$orderinfoofmultybillno->id}");

                                                $sm_no = $billobjs->salesman_no;

                                                if (!isset($multiQtyBills[$sm_no])) {
                                                    $multiQtyBills[$sm_no] = 0;
                                                }
                                                $multiQtyBills[$sm_no]++;

                                                if (!isset($multiQtyQty[$sm_no])) {
                                                    $multiQtyQty[$sm_no] = 0;
                                                }
                                                $multiQtyQty[$sm_no] += $finalQty;

                                                if (!isset($multiQtyAmount[$sm_no])) {
                                                    $multiQtyAmount[$sm_no] = 0;
                                                }

                                                $checkaccformultyqty = $db->fetchObject("SELECT SUM(sr.net_total) AS total_net, i.ctg_id FROM it_salesmanreport sr JOIN it_items i ON sr.barcode  = i.barcode JOIN it_categories ct ON i.ctg_id = ct.id WHERE sr.bill_no = '$billobjs->bill_no' AND ct.name  IN ('BLAZER+TIE+BROOCH', 'TROUSER PIECE', 'TIE', 'BLAZER+PANT PIECE', 'BLAZER+SHIRT+ TROUSER+TIE', '60''Lee', 'SHIRT PIECE', '60'' Li Dobby Linen', '60''s Li Dobby Linen', '60''s Li Dobby', 'INTERLOCK', 'Others', 'Starch Spray', 'Fabric', 'Handkerchiefs', 'Salwar', 'Pajama', 'Cloth Bag', 'Thermal Roll', 'Wallet & Belt Combo', 'KURTA PIECE', 'Cabin Bag', 'Trolly Bag', 'Paper Bags', 'SOCKS', 'Jacket Buttons', 'Watch Pocket', 'Jeans Button & Revet Set', 'Gift Box', 'Paper Carry Bags', '2 Layer Mask', 'Non Surgical Pollution Mask', 'Ear Loop Extender', 'Stool', 'Shoes', 'Belt', 'Job Work', 'Zipper', 'Mannequin', 'Wooden Shirt Hanger', 'Wooden Trouser Hanger', 'Accessories Stand' ) and sr.store_id IN ($this->storeidreport);");

                                                if (!empty($checkaccformultyqty)) {
                                                    $multiQtyAmount[$sm_no] += $checkNetValue->net_total - $checkaccformultyqty->total_net;
                                                } else {

                                                    $multiQtyAmount[$sm_no] += $checkNetValue->net_total;
                                                }
                                            }
                                        } else {

                                            $singleQtyResults = $db->fetchObject("select qty,net_total as total from it_salesmanreport where bill_no='$billobjs->bill_no' and store_id in ($this->storeidreport) and catg_name not in ('BLAZER+TIE+BROOCH', 'TROUSER PIECE', 'TIE', 'BLAZER+PANT PIECE', 'BLAZER+SHIRT+ TROUSER+TIE', '60''Lee', 'SHIRT PIECE', '60'' Li Dobby Linen', '60''s Li Dobby Linen', '60''s Li Dobby', 'INTERLOCK', 'Others', 'Starch Spray', 'Fabric', 'Handkerchiefs', 'Salwar', 'Pajama', 'Cloth Bag', 'Thermal Roll', 'Wallet & Belt Combo', 'KURTA PIECE', 'Cabin Bag', 'Trolly Bag', 'Paper Bags', 'SOCKS', 'Jacket Buttons', 'Watch Pocket', 'Jeans Button & Revet Set', 'Gift Box', 'Paper Carry Bags', '2 Layer Mask', 'Non Surgical Pollution Mask', 'Ear Loop Extender', 'Stool', 'Shoes', 'Belt', 'Job Work', 'Zipper', 'Mannequin', 'Wooden Shirt Hanger', 'Wooden Trouser Hanger', 'Accessories Stand' ) limit 1");

                                            $singleqtybillss += $singleQtyResults->qty;
                                            $singleqtybillssvalue += $singleQtyResults->total;

                                        }

                                    }

                                    $finalsinglebillqty = 0;


                                    if ($this->storeidreport == -1) {
                                        $storeClause = ""; // all stores
                                    } else {
                                        $storeClause = "AND sr.store_id IN ($this->storeidreport)";
                                    }

                                    $query = " SELECT t.salesman_no, t.bill_no, t.total_qty, t.net_total FROM ( SELECT sr.salesman_no, sr.bill_no, SUM(sr.qty) AS total_qty, SUM(sr.net_total) AS net_total FROM it_salesmanreport sr JOIN it_items i ON sr.barcode  = i.barcode WHERE i.ctg_id NOT IN (16,13,14,18,19,20,24,25,26,27,28,29,33,35,36,38,39,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54,55,56,57,58,59,60,61,62,63,64) $storeClause AND sr.$createtimequery GROUP BY sr.bill_no, sr.salesman_no ) AS t JOIN it_orders o ON t.bill_no = o.bill_no WHERE o.tickettype IN (0, 6) " . ($this->storeidreport != -1 ? "AND o.store_id IN ($this->storeidreport)" : "") . "; ";
                                    
                                    $singleQtyResults = $db->fetchObjectArray($query);

                                    foreach ($singleQtyResults as $bObjs) {
                                        $billNo = trim($bObjs->bill_no);
                                        $getbillorderid = $db->fetchObject(" SELECT id,amount FROM it_orders WHERE bill_no = '$billNo' " . ($this->storeidreport != -1 ? "AND store_id IN ($this->storeidreport)" : "") . " LIMIT 1 ");

                                        if (!$getbillorderid)
                                            continue;

                                        $getorderidbillqty = $db->fetchObject(" SELECT SUM(oi.quantity) AS quantity FROM it_order_items oi JOIN it_items i ON oi.barcode  = i.barcode  WHERE oi.order_id = $getbillorderid->id AND i.ctg_id NOT IN (16,13,14,18,19,20,24,25,26,27,28,29,33,35,36,38,39,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54,55,56,57,58,59,60,61,62,63,64) ");

                                        $billQty = $getorderidbillqty && $getorderidbillqty->quantity !== null ? (float) $getorderidbillqty->quantity : 0;

                                        // Get Credit Note No.
                                        $getcnno = $db->fetchObject(" SELECT DISTINCT(TRIM(return_no)) AS cn FROM it_salesmanreport WHERE bill_no = '$billNo' " . ($this->storeidreport != -1 ? "AND store_id IN ($this->storeidreport)" : "") . " LIMIT 1 ");

                                        $cnQty = 0;
                                        if (!empty($getcnno) && $getcnno->cn != "") {
                                            $cnNo = $getcnno->cn;
                                            $getcnorderid = $db->fetchObject(" SELECT id FROM it_orders WHERE bill_no = '$cnNo' " . ($this->storeidreport != -1 ? "AND store_id IN ($this->storeidreport)" : "") . " LIMIT 1 ");


                                            if ($getcnorderid) {
                                                $getorderidcnqty = $db->fetchObject(" SELECT SUM(oi.quantity) AS quantity FROM it_order_items oi JOIN it_items i ON oi.barcode  = i.barcode  WHERE oi.order_id = $getcnorderid->id AND i.ctg_id NOT IN (16,13,14,18,19,20,24,25,26,27,28,29,33,35,36,38,39,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54,55,56,57,58,59,60,61,62,63,64) ");
                                                $cnQty = $getorderidcnqty && $getorderidcnqty->quantity !== null ? (float) $getorderidcnqty->quantity : 0;
                                            }
                                        }


                                        $cnQtyabs=abs($cnQty);

                                        $finalsinglebillqty = $billQty - $cnQtyabs;

                                        if ($finalsinglebillqty == 1) {

                                        if (isset($singleQtyMap[$bObjs->salesman_no])) {
                                            
                                            $singleQtyMap[$bObjs->salesman_no]['singleqtybills'] += $finalsinglebillqty;
                                            $singleQtyMap[$bObjs->salesman_no]['value'] += $getbillorderid->amount;
                                        } else {
                                            // First entry for that salesman
                                            $singleQtyMap[$bObjs->salesman_no] = array(
                                                'singleqtybills' => 1,
                                                'value' => $getbillorderid->amount
                                            );
                                        }
                                    }
                                    }

                                    ?>
                                    <table id="incentivereportincentive">
                                        <tr>
                                            <td><strong>Salesman No. / Parameters</strong></td>
                                            <?php
                                            $totalincentivesum = 0;
                                            $singlebillscount = 0;
                                            $singleqtyvalue = 0;
                                            $singleqtyincentivetotal = 0;
                                            $multiybillcount = 0;
                                            $multiybillqtycount = 0;
                                            $multybillqtyvalue = 0;
                                            $multybillqtyincenvalue = 0;
                                            $memberbills1sthurdlecount = 0;
                                            $hurdle1totalqty = 0;
                                            $hurdle1totalvalue = 0;
                                            $firsthurdletotalvalue = 0;
                                            $memberbills2ndhurdlecount = 0;
                                            $hurdle2totalqty = 0;
                                            $hurdle2totalvalue = 0;
                                            $hurdle2totalincent = 0;

                                            $totalincentivearray = [];
                                            $singleqtybillsarray = [];
                                            $singleqtybillvaluessarray=[];
                                            $singleqtyincentiveamtarray = [];

                                            $multiQtyBillsarray = [];
                                            $multiQtyarray = [];
                                            $multiQtyValuearray = [];
                                            $multiQtyIncentivearray = [];

                                            $firsthundlebillsarray = [];
                                            $firsthurdleqtyarray = [];
                                            $firsthurdlevaluearray = [];
                                            $firsthurdleincentivearray = [];

                                            $secondhundlebillsarray = [];
                                            $secondhurdleqtyarray = [];
                                            $secondhurdlevaluearray = [];
                                            $secondhurdleincentivearray = [];

                                            foreach ($salesmannoquery as $obj):
                                                ?>
                                                <td id="<?= $obj->salesman_no ?>"><strong><?= $obj->salesman_no ?></strong></td>
            <?php endforeach; ?>    
                                            <td>Total </td>
                                        </tr>
                                            <?php foreach ($parameters as $param): ?>
                                            <tr>
                                                <?php
                                                $color = 'black';

                                                if ($param == "Total Incentive") {
                                                    $color = '#006400';
                                                } elseif (in_array($param, ["Single Qty Bills", "Qty in Single Bills", "Single Qty Value", "Single Qty Incentive Amt"])) {
                                                    $color = 'red';
                                                } elseif (in_array($param, ["Multiple Qty No Membership Bills", "Qty in multiple bills", "Multiple Qty Bill Value", "Multiple Qty Incentive Amt"])) {
                                                    $color = 'blue';
                                                } elseif (in_array($param, ["Membership Bills 1st Hurdle (5999₹)", "Qty 1st Hurdle", "Value 1st Hurdle", "Membership 1st Hurdle Incentive Amt"])) {
                                                    $color = 'rgb(170, 51, 106)';
                                                } elseif (in_array($param, ["Membership Bills 2nd Hurdle (9999₹)", "Qty 2nd Hurdle", "Value 2nd Hurdle", "Membership 2nd Hurdle Incentive Amt"])) {
                                                    $color = 'rgb(204, 85, 0)';
                                                }

                                                echo "<td style='color: $color;'>$param</td>";
                                                ?>

                                                <?php
                                                $salesmanarray = [];

                                                foreach ($salesmannoquery as $obj):
                                                    ?>


                                                        <?php if ($param == "Total Incentive") { ?>
                                                        <td style="color: #006400;">
                                                        <?php } elseif (in_array($param, ["Single Qty Bills", "Qty in Single Bills", "Single Qty Value", "Single Qty Incentive Amt"])) { ?>
                                                        <td style="color: red;">
                                                        <?php } elseif (in_array($param, ["Multiple Qty No Membership Bills", "Qty in multiple bills", "Multiple Qty Bill Value", "Multiple Qty Incentive Amt"])) { ?>
                                                        <td style="color: blue;">
                                                        <?php } elseif (in_array($param, ["Membership Bills 1st Hurdle (5999₹)", "Qty 1st Hurdle", "Value 1st Hurdle", "Membership 1st Hurdle Incentive Amt"])) { ?>
                                                        <td style="color: rgb(170, 51, 106);">
                                                        <?php } elseif (in_array($param, ["Membership Bills 2nd Hurdle (9999₹)", "Qty 2nd Hurdle", "Value 2nd Hurdle", "Membership 2nd Hurdle Incentive Amt"])) { ?>
                                                        <td style="color: rgb(204, 85, 0);">
                                                        <?php } else { ?>
                                                        <td>
                    <?php } ?>



                                                        <?php
                                                        $sm_no = $obj->salesman_no;
                                                        $salesmanarray[] = $sm_no;

                                                        if ($param == "Total Incentive") {
                                                            $totalincentive = 0;

                                                            if (!empty($singleQtyMap[$sm_no]['value'])) {
                                                                $totalincentive += ($singleQtyMap[$sm_no]['value'] * 0) / 100;
                                                            }

                                                            if (!empty($multiQtyAmount[$sm_no])) {
                                                                $totalincentive += ($multiQtyAmount[$sm_no] * 1) / 100;
                                                            }

                                                            $firsthurdleincentive = 0;
                                                            if (!empty($salesmanHurdle1Amount[$sm_no])) {
                                                                $firsthurdleincentive = ($salesmanHurdle1Amount[$sm_no] * 1) / 100;
                                                            }
                                                            if (!empty($salesmanHurdle1Counts[$sm_no])) {
                                                                $firsthurdleincentive += 40 * $salesmanHurdle1Counts[$sm_no];
                                                            }
                                                            $totalincentive += $firsthurdleincentive;

                                                            $secondhurdleincentive = 0;
                                                            if (!empty($salesmanHurdle2Amount[$sm_no])) {
                                                                $secondhurdleincentive = ($salesmanHurdle2Amount[$sm_no] * 1) / 100;
                                                            }
                                                            if (!empty($salesmanHurdle2Counts[$sm_no])) {
                                                                $secondhurdleincentive += 100 * $salesmanHurdle2Counts[$sm_no];
                                                            }
                                                            $totalincentive += $secondhurdleincentive;
                                                            $totalincentivesum += $totalincentive;
                                                            echo round($totalincentive);
                                                            array_push($totalincentivearray, $totalincentive);
                                                        } elseif ($param == "Single Qty Bills") {
                                                            echo!empty($singleQtyMap[$sm_no]['singleqtybills']) ? $singleQtyMap[$sm_no]['singleqtybills'] : '0';
                                                            $singlebillscount += !empty($singleQtyMap[$sm_no]['singleqtybills']) ? $singleQtyMap[$sm_no]['singleqtybills'] : 0;

                                                            array_push($singleqtybillsarray, !empty($singleQtyMap[$sm_no]['singleqtybills']) ? $singleQtyMap[$sm_no]['singleqtybills'] : '0');
                                                        } elseif ($param == "Qty in Single Bills") {
                                                            echo!empty($singleQtyMap[$sm_no]['singleqtybills']) ? $singleQtyMap[$sm_no]['singleqtybills'] : '0';
//                                                            $singlebillscount += !empty($singleQtyMap[$sm_no]['singleqtybills']) ? $singleQtyMap[$sm_no]['singleqtybills'] : 0;
                                                        } elseif ($param == "Single Qty Value") {
                                                            echo!empty($singleQtyMap[$sm_no]['value']) ? round($singleQtyMap[$sm_no]['value']) : '0';
                                                            $singleqtyvalue += !empty($singleQtyMap[$sm_no]['value']) ? $singleQtyMap[$sm_no]['value'] : 0;
                                                            
                                                             array_push($singleqtybillvaluessarray, !empty($singleQtyMap[$sm_no]['value']) ? $singleQtyMap[$sm_no]['value'] : 0);
                                                        } elseif ($param == "Single Qty Incentive Amt") {
                                                            $singleqtyincentive = 0;
                                                            $percentage = 0;
                                                            if (!empty($singleQtyMap[$sm_no]['value'])) {
                                                                $singleqtyincentive = ($singleQtyMap[$sm_no]['value'] * $percentage) / 100;
                                                            }
                                                            echo $singleqtyincentive;
                                                            array_push($singleqtyincentiveamtarray, $singleqtyincentive);
                                                            $singleqtyincentivetotal += $singleqtyincentive;
                                                        } elseif ($param == "Single Qty Incentive Amt") {
                                                            echo!empty($singleQtyMap[$sm_no]['value']) ? $singleQtyMap[$sm_no]['value'] * 0 : '0';
                                                            $singleqtyvalue += !empty($singleQtyMap[$sm_no]['value']) ? $singleQtyMap[$sm_no]['value'] : 0;
                                                        } elseif ($param == "Multiple Qty No Membership Bills") {
                                                            echo isset($multiQtyBills[$sm_no]) ? $multiQtyBills[$sm_no] : '0';
                                                            $multiybillcount += isset($multiQtyBills[$sm_no]) ? $multiQtyBills[$sm_no] : '0';

                                                            array_push($multiQtyBillsarray, isset($multiQtyBills[$sm_no]) ? $multiQtyBills[$sm_no] : '0');
                                                        } elseif ($param == "Qty in multiple bills") {
                                                            echo isset($multiQtyQty[$sm_no]) ? $multiQtyQty[$sm_no] : '0';
                                                            $multiybillqtycount += isset($multiQtyQty[$sm_no]) ? $multiQtyQty[$sm_no] : '0';

                                                            array_push($multiQtyarray, isset($multiQtyQty[$sm_no]) ? $multiQtyQty[$sm_no] : '0');
                                                        } elseif ($param == "Multiple Qty Bill Value") {
                                                            echo isset($multiQtyAmount[$sm_no]) ? $multiQtyAmount[$sm_no] : '0';
                                                            $multybillqtyvalue += isset($multiQtyAmount[$sm_no]) ? $multiQtyAmount[$sm_no] : '0';

                                                            array_push($multiQtyValuearray, isset($multiQtyAmount[$sm_no]) ? $multiQtyAmount[$sm_no] : '0');
                                                        } elseif ($param == "Multiple Qty Incentive Amt") {
                                                            $multyqtyincentive = 0;
                                                            $percentage = 1;
                                                            if (!empty($multiQtyAmount[$sm_no])) {
                                                                $multyqtyincentive = ($multiQtyAmount[$sm_no] * $percentage) / 100;
                                                            }
                                                            echo $multyqtyincentive;
                                                            array_push($multiQtyIncentivearray, $multyqtyincentive);
                                                            $multybillqtyincenvalue += $multyqtyincentive;
                                                        } elseif ($param == "Membership Bills 1st Hurdle (5999₹)") {
                                                            echo isset($salesmanHurdle1Counts[$sm_no]) ? $salesmanHurdle1Counts[$sm_no] : '0';
                                                            $memberbills1sthurdlecount += isset($salesmanHurdle1Counts[$sm_no]) ? $salesmanHurdle1Counts[$sm_no] : '0';
                                                            array_push($firsthundlebillsarray, isset($salesmanHurdle1Counts[$sm_no]) ? $salesmanHurdle1Counts[$sm_no] : '0');
                                                        } elseif ($param == "Qty 1st Hurdle") {
                                                            array_push($firsthurdleqtyarray, isset($salesmanHurdle1Qty[$sm_no]) ? $salesmanHurdle1Qty[$sm_no] : '0');
                                                            echo isset($salesmanHurdle1Qty[$sm_no]) ? $salesmanHurdle1Qty[$sm_no] : '0';
                                                            $hurdle1totalqty += isset($salesmanHurdle1Qty[$sm_no]) ? $salesmanHurdle1Qty[$sm_no] : '0';
                                                        } elseif ($param == "Value 1st Hurdle") {
                                                            echo isset($salesmanHurdle1Amount[$sm_no]) ? $salesmanHurdle1Amount[$sm_no] : '0';
                                                            array_push($firsthurdlevaluearray, isset($salesmanHurdle1Amount[$sm_no]) ? $salesmanHurdle1Amount[$sm_no] : '0');
                                                            $hurdle1totalvalue += isset($salesmanHurdle1Amount[$sm_no]) ? $salesmanHurdle1Amount[$sm_no] : '0';
                                                        } elseif ($param == "Membership 1st Hurdle Incentive Amt") {
//                                                        echo isset($salesmanHurdle1Amount[$sm_no]) ? $salesmanHurdle1Amount[$sm_no] : '0';
                                                            $firsthurdleincentive = 0;
                                                            $finalhurdle1incentive = 0;
                                                            $percentage = 1;
                                                            if (!empty($salesmanHurdle1Amount[$sm_no])) {
                                                                $firsthurdleincentive = ($salesmanHurdle1Amount[$sm_no] * $percentage) / 100;
                                                            }
                                                            if (!empty($salesmanHurdle1Counts[$sm_no])) {
                                                                $finalhurdle1incentive = $firsthurdleincentive + 40 * $salesmanHurdle1Counts[$sm_no];
                                                            }
                                                            echo $finalhurdle1incentive;
                                                            array_push($firsthurdleincentivearray, $finalhurdle1incentive);
                                                            $firsthurdletotalvalue += $finalhurdle1incentive;
                                                        } elseif ($param == "Membership Bills 2nd Hurdle (9999₹)") {

                                                            array_push($secondhundlebillsarray, isset($salesmanHurdle2Counts[$sm_no]) ? $salesmanHurdle2Counts[$sm_no] : '0');
                                                            echo isset($salesmanHurdle2Counts[$sm_no]) ? $salesmanHurdle2Counts[$sm_no] : '0';
                                                            $memberbills2ndhurdlecount += isset($salesmanHurdle2Counts[$sm_no]) ? $salesmanHurdle2Counts[$sm_no] : '0';
                                                        } elseif ($param == "Qty 2nd Hurdle") {
                                                            array_push($secondhurdleqtyarray, isset($salesmanHurdle2Qty[$sm_no]) ? $salesmanHurdle2Qty[$sm_no] : '0');
                                                            echo isset($salesmanHurdle2Qty[$sm_no]) ? $salesmanHurdle2Qty[$sm_no] : '0';
                                                            $hurdle2totalqty += isset($salesmanHurdle2Qty[$sm_no]) ? $salesmanHurdle2Qty[$sm_no] : '0';
                                                        } elseif ($param == "Value 2nd Hurdle") {
                                                            array_push($secondhurdlevaluearray, isset($salesmanHurdle2Amount[$sm_no]) ? $salesmanHurdle2Amount[$sm_no] : '0');
                                                            echo isset($salesmanHurdle2Amount[$sm_no]) ? $salesmanHurdle2Amount[$sm_no] : '0';
                                                            $hurdle2totalvalue += isset($salesmanHurdle2Amount[$sm_no]) ? $salesmanHurdle2Amount[$sm_no] : '0';
                                                        } elseif ($param == "Membership 2nd Hurdle Incentive Amt") {

                                                            $secondhurdleincentive = 0;
                                                            $finalhurdle2incentive = 0;
                                                            $percentage = 1;
                                                            if (!empty($salesmanHurdle2Amount[$sm_no])) {
                                                                $secondhurdleincentive = ($salesmanHurdle2Amount[$sm_no] * $percentage) / 100;
                                                            }
                                                            if (!empty($salesmanHurdle2Counts[$sm_no])) {
                                                                $finalhurdle2incentive = $secondhurdleincentive + 100 * $salesmanHurdle2Counts[$sm_no];
                                                            }
                                                            echo $finalhurdle2incentive;
                                                            array_push($secondhurdleincentivearray, $finalhurdle2incentive);
                                                            $hurdle2totalincent += $finalhurdle2incentive;
                                                        } else {
                                                            echo '0';
                                                        }
                                                        ?>
                                                    </td>

                <?php endforeach; ?>


                                                    <?php if ($param == "Total Incentive") { ?>
                                                    <td style="color: #006400;">
                                                    <?php } elseif (in_array($param, ["Single Qty Bills", "Qty in Single Bills", "Single Qty Value", "Single Qty Incentive Amt"])) { ?>
                                                    <td style="color: red;">
                                                    <?php } elseif (in_array($param, ["Multiple Qty No Membership Bills", "Qty in multiple bills", "Multiple Qty Bill Value", "Multiple Qty Incentive Amt"])) { ?>
                                                    <td style="color: blue;">
                                                    <?php } elseif (in_array($param, ["Membership Bills 1st Hurdle (5999₹)", "Qty 1st Hurdle", "Value 1st Hurdle", "Membership 1st Hurdle Incentive Amt"])) { ?>
                                                    <td style="color: rgb(170, 51, 106);">
                                                    <?php } elseif (in_array($param, ["Membership Bills 2nd Hurdle (9999₹)", "Qty 2nd Hurdle", "Value 2nd Hurdle", "Membership 2nd Hurdle Incentive Amt"])) { ?>
                                                    <td style="color: rgb(204, 85, 0);">
                                                    <?php } else { ?>
                                                    <td>
                <?php } ?>



                                                    <?php
                                                    if ($param == "Total Incentive") {
                                                        echo $totalincentivesum;
                                                    } elseif ($param == "Single Qty Bills" || $param == "Qty in Single Bills") {
                                                        echo $singlebillscount;
                                                    } elseif ($param == "Single Qty Value") {
                                                        // single quantity value
                                                        echo $singleqtyvalue;
                                                    } elseif ($param == "Single Qty Incentive Amt") {
                                                        echo $singleqtyincentivetotal;
                                                        // single quantity incentive amount
                                                    } elseif ($param == "Multiple Qty No Membership Bills") {
                                                        echo $multiybillcount;
                                                        // multiple quantity (no membership bills)
                                                    } elseif ($param == "Qty in multiple bills") {
                                                        echo $multiybillqtycount;
                                                        // qty in multiple bills
                                                    } elseif ($param == "Multiple Qty Bill Value") {
                                                        // multiple quantity bill value
                                                        echo $multybillqtyvalue;
                                                    } elseif ($param == "Multiple Qty Incentive Amt") {
                                                        // multiple quantity incentive amount
                                                        echo $multybillqtyincenvalue;
                                                    } elseif ($param == "Membership Bills 1st Hurdle (5999₹)") {
                                                        // membership bills first hurdle
                                                        echo $memberbills1sthurdlecount;
                                                    } elseif ($param == "Qty 1st Hurdle") {
                                                        // quantity in 1st hurdle
                                                        echo $hurdle1totalqty;
                                                    } elseif ($param == "Value 1st Hurdle") {
                                                        // value in 1st hurdle
                                                        echo $hurdle1totalvalue;
                                                    } elseif ($param == "Membership 1st Hurdle Incentive Amt") {
                                                        // membership 1st hurdle incentive amount
                                                        echo $firsthurdletotalvalue;
                                                    } elseif ($param == "Membership Bills 2nd Hurdle (9999₹)") {
                                                        echo $memberbills2ndhurdlecount;
                                                        // membership bills second hurdle
                                                    } elseif ($param == "Qty 2nd Hurdle") {
                                                        // quantity in 2nd hurdle
                                                        echo $hurdle2totalqty;
                                                    } elseif ($param == "Value 2nd Hurdle") {
                                                        // value in 2nd hurdle
                                                        echo $hurdle2totalvalue;
                                                    } elseif ($param == "Membership 2nd Hurdle Incentive Amt") {
                                                        // membership 2nd hurdle incentive amount
                                                        echo $hurdle2totalincent;
                                                    } else {
                                                        // default 0
                                                    }
                                                    ?></td>
                                            </tr>
            <?php endforeach; ?>


                                    </table>

        <?php } ?>
                            </div>
                        </div>
                        <div class="grid_12" id="submitbutton" style="padding:10px;">
                            <input type="submit" name="add" id="add" value="Generate Report" style="background-color:white;"/>

        <?php if ($formResult) { ?>
                                <p>
                                    <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
                                </p>
        <?php } ?>
                        </div>

                    </form>
        <?php if (isset($this->storeidreport)) { //12 fields          ?>
                        <form method="post" action="formpost/genSalesIncentiveExcel.php" >

                            <?php foreach ($salesmanarray as $value): ?>
                                <input type="hidden" name="salesman[]" value="<?php echo $value; ?>">
                            <?php endforeach; ?>
                            <?php foreach ($totalincentivearray as $value): ?>
                                <input type="hidden" name="totalincentivearray[]" value="<?php echo round($value); ?>">
                            <?php endforeach; ?>
                            <?php foreach ($singleqtybillsarray as $value): ?>
                                <input type="hidden" name="singleqtybillsarray[]" value="<?php echo round($value); ?>">
                            <?php endforeach; ?>
                                
                            
                              <?php foreach ($singleqtybillvaluessarray as $value): ?>
                                <input type="hidden" name="singleqtybillvaluessarray[]" value="<?php echo round($value); ?>">
                            <?php endforeach; ?>
                                
                                
                            <?php foreach ($singleqtyincentiveamtarray as $value): ?>
                                <input type="hidden" name="singleqtyincentiveamtarray[]" value="<?php echo round($value); ?>">
                            <?php endforeach; ?>

                            <?php foreach ($multiQtyBillsarray as $value): ?>
                                <input type="hidden" name="multiQtyBillsarray[]" value="<?php echo round($value); ?>">
                            <?php endforeach; ?>

                            <?php foreach ($multiQtyarray as $value): ?>
                                <input type="hidden" name="multiQtyarray[]" value="<?php echo round($value); ?>">
                            <?php endforeach; ?>

                            <?php foreach ($multiQtyValuearray as $value): ?>
                                <input type="hidden" name="multiQtyValuearray[]" value="<?php echo round($value); ?>">
                            <?php endforeach; ?>

                            <?php foreach ($multiQtyIncentivearray as $value): ?>
                                <input type="hidden" name="multiQtyIncentivearray[]" value="<?php echo round($value); ?>">
                            <?php endforeach; ?>

                            <?php foreach ($firsthundlebillsarray as $value): ?>
                                <input type="hidden" name="firsthundlebillsarray[]" value="<?php echo round($value); ?>">
            <?php endforeach; ?>


                            <?php foreach ($firsthurdleqtyarray as $value): ?>
                                <input type="hidden" name="firsthurdleqtyarray[]" value="<?php echo round($value); ?>">
                            <?php endforeach; ?>

                            <?php foreach ($firsthurdlevaluearray as $value): ?>
                                <input type="hidden" name="firsthurdlevaluearray[]" value="<?php echo round($value); ?>">
            <?php endforeach; ?>


                            <?php foreach ($firsthurdleincentivearray as $value): ?>
                                <input type="hidden" name="firsthurdleincentivearray[]" value="<?php echo round($value); ?>">
                            <?php endforeach; ?>

                            <?php foreach ($secondhundlebillsarray as $value): ?>
                                <input type="hidden" name="secondhundlebillsarray[]" value="<?php echo round($value); ?>">
            <?php endforeach; ?>



                            <?php foreach ($secondhurdleqtyarray as $value): ?>
                                <input type="hidden" name="secondhurdleqtyarray[]" value="<?php echo round($value); ?>">
            <?php endforeach; ?>



                            <?php foreach ($secondhurdlevaluearray as $value): ?>
                                <input type="hidden" name="secondhurdlevaluearray[]" value="<?php echo round($value); ?>">
            <?php endforeach; ?>



                            <?php foreach ($secondhurdleincentivearray as $value): ?>
                                <input type="hidden" name="secondhurdleincentivearray[]" value="<?php echo round($value); ?>">
            <?php endforeach; ?>   

                            <input type="hidden" name="stores" value="<?php echo $this->storeidreport; ?>"><br>
                            <input type="hidden" name="daterange" value="<?php echo $this->dtrange; ?>">
                            <input type="submit" value="Generate Excel">
                        </form>
        <?php } ?>
                </fieldset>

            </div> <!-- class=box -->

                <?php if (isset($this->storeidreport)) { //12 fields                ?>
                <div class="box grid_12" style="margin-left:0px; overflow:auto; height:500px; display: none">
                    <?php
                    $queryfields = "";
                    $tableheaders = "";
                    $group_by = array();
                    $gClause = "";
                    $storeClause = "";
                    $dtarr = explode(" - ", $this->dtrange);
                    $_SESSION['storeid'] = $this->storeidreport;
                    $dQuery = "";
                    if (count($dtarr) == 1) {
                        list($dd, $mm, $yy) = explode("-", $dtarr[0]);
                        $sdate = "$yy-$mm-$dd";
                        $dQuery = " and s.bill_datetime >= '$sdate 00:00:00' and s.bill_datetime <= '$sdate 23:59:59' ";
                    } else if (count($dtarr) == 2) {
                        list($dd, $mm, $yy) = explode("-", $dtarr[0]);
                        $sdate = "$yy-$mm-$dd";
                        list($dd, $mm, $yy) = explode("-", $dtarr[1]);
                        $edate = "$yy-$mm-$dd";
                        $dQuery = " and s.bill_datetime >= '$sdate 00:00:00' and s.bill_datetime <= '$edate 23:59:59' ";
                    } else {
                        $dQuery = "";
                    }


                    if ($this->storeidreport == "-1") {
                        $storeClause = " c.usertype = " . UserType::Dealer;
                        //  $storeClause ="c.usertype= " .UserType::Dealer :
                    } else {
                        $storeClause = "s.store_id in( $this->storeidreport)";
                    }
                    $queryfields = substr($queryfields, 0, -1);
                    if (!empty($group_by)) {
                        $gClause = " group by " . implode(",", $group_by);
                        //$groupby = substr($groupby, 0, -1);
                    } else if ($this->currUser->usertype == UserType::Dealer && empty($group_by)) {
                        //$queryfields .= "c.store_name,sum(cs.quantity) as quantity,sum(i.MRP * cs.quantity) as totalvalue";                
                        $queryfields .= "c.store_name";
                    }
                    //$query2 = "select c.store_name , cs.barcode, sum(cs.quantity) as quantity from it_codes c , it_current_stock cs where c.id = cs.store_id and cs.store_id in ( $storeClause ) group by cs.barcode ";
                    //echo $query2;
                    $query = "select $queryfields";
                    $query .= " from it_codes c,it_salesmanreport s where  c.id=s.store_id and  $storeClause $dQuery $gClause ";

//            $result = $db->execQuery($query);
                    ?>
                    <br /><div style='margin-left:40px; padding-left:15px; height:24px;width:130px;border: solid gray 1px;background:#F5F5F5;padding-top:4px;'>
                        <a href='tmp/SalesmanIncentive.csv' title='Export table to CSV'><img src=<?php echo $this->imageUrl("excel.png"); ?> width='20' hspace='3' style='margin-bottom:-6px;' /> Export To Excel</a>
                    </div><br />

                </div>
        <?php } ?>
        </div>
        <script src="js/chosen/chosen.jquery.js" type="text/javascript"></script>

        <?php
    }

}
?>
