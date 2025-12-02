<?php
set_time_limit(120); 
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");
require_once "lib/core/strutil.php";
require_once "session_check.php";

class cls_report_salesman_incentive extends cls_renderer {

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
            window.location.href = "report/salesman/incentive/str=" + storeid + "/dateselect="+dateselect;
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
            window.location.href = "report/salesman/incentive/str=" + storeid + "/store=1/salesmainid=2/incentive=3/totqty=4/amt=5/atv=6/upt=7/ctg=8/bill_no=9";
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
        $menuitem = "salesmanincentive";
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

                                    $createtimequery = "o.bill_datetime between '$toDate 00:00:00' and '$fromDate 23:59:59'";
                                } else {
                                    $date = DateTime::createFromFormat('d-m-Y', trim($daterange))->format('Y-m-d');
                                    $createtimequery = "o.bill_datetime between '$date 00:00:00' and '$date 23:59:59'";
                                }
                                ?>
                                <?php if (isset($this->storeidreport)) { //12 fields       ?>
                                    <?php

                                        $salesmannoquery = $db->fetchObjectArray("SELECT DISTINCT(salesman_no) FROM it_salesmanreport where store_id in ($this->storeidreport) ORDER BY salesman_no ASC;");

                                    $parameters = ["Total Incentive", "Multiple Qty No Membership Bills", "Qty in multiple bills", "Multiple Qty Bill Value", "Multiple Qty Incentive Amt"];
                                    
                                    $multiQtyBills = [];
                                    $multiQtyQty = [];
                                    $multiQtyAmount = [];
                                    
                                    
                                    
                                    
                                    
//                                    $query = "SELECT s.salesman_no as salesman_no, o.bill_no as bill_no ,(sum(s.net_total)-sum(return_total)) as net_total, s.return_no, CASE WHEN s.return_no IS NULL OR TRIM(s.return_no) = '' "
//                                                        . "THEN SUM(s.qty) ELSE SUM(s.qty) - ( SELECT abs(SUM(o2.quantity)) FROM it_orders o2 WHERE o2.store_id  in ($this->storeidreport) AND "
//                                                        . "o2.bill_no = trim(s.return_no) ) END AS ssum FROM it_orders o JOIN it_salesmanreport s ON o.store_id = s.store_id "
//                                                        . "AND o.bill_no = s.bill_no WHERE s.store_id in ($this->storeidreport) AND $createtimequery "
//                                                        . "AND catg_name NOT IN ('Handkerchiefs', 'Socks') GROUP BY s.salesman_no, o.bill_no, s.return_no";
                                    
                                    
                                    
//                                   $query =  "SELECT s.bill_no, s.salesman_no, SUM( CASE WHEN s.return_no IS NULL OR s.return_no = '' THEN s.qty ELSE s.qty - ( SELECT COALESCE(ABS(SUM(o2.quantity)),0) FROM it_orders o2 "
//                                           . "WHERE o2.store_id = s.store_id AND o2.bill_no = TRIM(s.return_no) ) END ) AS units,"
//                                           . " SUM(s.net_total - s.return_total) AS net_total, SUM( CASE WHEN s.catg_name NOT IN ('Handkerchiefs', 'Socks', 'Starch Spray') THEN "
//                                           . "CASE WHEN s.return_no IS NULL OR s.return_no = '' THEN s.qty ELSE s.qty - ( SELECT COALESCE(ABS(SUM(o2.quantity)),0) FROM it_orders o2 WHERE o2.store_id = s.store_id AND o2.bill_no = TRIM(s.return_no) ) END END ) AS raw_total_units, "
//                                           . "( SELECT SUM( CASE WHEN s2.return_no IS NULL OR s2.return_no = '' THEN s2.qty ELSE s2.qty - ( SELECT COALESCE(ABS(SUM(o3.quantity)),0) FROM it_orders o3 WHERE o3.store_id = s2.store_id AND o3.bill_no = TRIM(s2.return_no) ) END ) FROM it_salesmanreport "
//                                           . "s2 WHERE s2.bill_no = s.bill_no AND s2.store_id = s.store_id AND s2.catg_name NOT IN ('Handkerchiefs','Socks', 'Starch Spray') ) AS bill_total_units, o.bill_datetime FROM it_orders o JOIN it_salesmanreport s ON o.store_id = s.store_id AND o.bill_no = s.bill_no "
//                                           . "WHERE s.store_id IN ($this->storeidreport) AND $createtimequery AND s.catg_name NOT IN ('Handkerchiefs', 'Socks', 'Starch Spray') "
//                                           . "GROUP BY s.bill_no, s.salesman_no, o.bill_datetime ORDER BY s.bill_no, s.salesman_no;";
                                    $query =  "SELECT s.bill_no, s.salesman_no, ( SUM(s.qty) - COALESCE( ( SELECT ABS(SUM(o2.quantity)) FROM it_orders o2 WHERE o2.store_id = s.store_id AND o2.bill_no = TRIM(s.return_no) ), 0 ) ) AS units, SUM(s.net_total - s.return_total) AS net_total, ( SUM(CASE WHEN s.catg_name NOT IN ('Handkerchiefs', 'Socks', 'Starch Spray') THEN s.qty END) - COALESCE( ( SELECT ABS(SUM(o2.quantity)) FROM it_orders o2 WHERE o2.store_id = s.store_id AND o2.bill_no = TRIM(s.return_no) ), 0 ) ) AS raw_total_units, ( (SELECT SUM(s2.qty) FROM it_salesmanreport s2 WHERE s2.bill_no = s.bill_no AND s2.store_id = s.store_id AND s2.catg_name NOT IN ('Handkerchiefs','Socks', 'Starch Spray') ) - COALESCE( ( SELECT ABS(SUM(o3.quantity)) FROM it_orders o3 WHERE o3.store_id = s.store_id AND o3.bill_no = TRIM(s.return_no) ), 0 ) ) AS bill_total_units, o.bill_datetime FROM it_orders o JOIN it_salesmanreport s ON o.store_id = s.store_id AND o.bill_no = s.bill_no WHERE s.store_id IN ($this->storeidreport) AND $createtimequery AND s.catg_name NOT IN ('Handkerchiefs', 'Socks', 'Starch Spray') GROUP BY s.bill_no, s.salesman_no, o.bill_datetime ORDER BY s.bill_no, s.salesman_no;";

//                                                print_r($query);exit();
                                    $object = $db->fetchObjectArray($query);
                                    
                                    foreach ($object as $billobjs) {
                                        


                                     
                                     $finalQty = $billobjs->bill_total_units;
                                     
                                     
                                                // Start Multiple Qty (No Membership) logic 
                                        if ($finalQty > 1) { // Only process bills with Qty > 1 (Multiple Qty)

                                                $sm_no = ($billobjs->salesman_no);
                                                if (!isset($multiQtyBills[$sm_no])) {
                                                    $multiQtyBills[$sm_no] = 0;
                                                }
                                                    $multiQtyBills[$sm_no]++;
                                                
                                                if (!isset($multiQtyQty[$sm_no])) {
                                                    $multiQtyQty[$sm_no] = 0;
                                                }
                                                $multiQtyQty[$sm_no] += $billobjs->units;

                                                if (!isset($multiQtyAmount[$sm_no])) {
                                                    $multiQtyAmount[$sm_no] = 0;
                                                }

                                                $checkNetValue = $billobjs->net_total;
                                                 $multiQtyAmount[$sm_no] += $checkNetValue;
//                                               
                                            
                                        } 
                                        // End Multiple Qty (No Membership) logic

                                    }

                                    
                                    ?>
                                    <table id="incentivereportincentive">
                                        <tr>
                                            <td><strong>Salesman No. / Parameters</strong></td>
                                            <?php
                                            $totalincentivesum = 0;
                                            
                                            $multiybillcount = 0;
                                            $multiybillqtycount = 0;
                                            $multybillqtyvalue = 0;
                                            $multybillqtyincenvalue = 0;
                                            
                                            
                                            $totalincentivearray = [];
                                            
                                            $multiQtyBillsarray = [];
                                            $multiQtyarray = [];
                                            $multiQtyValuearray = [];
                                            $multiQtyIncentivearray = [];

                                            
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
                                                } else
                                                    if (in_array($param, ["Multiple Qty No Membership Bills", "Qty in multiple bills", "Multiple Qty Bill Value", "Multiple Qty Incentive Amt"])) {
                                                    $color = 'blue';
                                                }


                                                echo "<td style='color: $color;'>$param</td>";
                                                ?>

                                                <?php
                                                $salesmanarray = [];

                                                foreach ($salesmannoquery as $obj):
                                                    ?>


                                                        <?php if ($param == "Total Incentive") { ?>
                                                        <td style="color: #006400;">
                                                        <?php }  elseif (in_array($param, ["Multiple Qty No Membership Bills", "Qty in multiple bills", "Multiple Qty Bill Value", "Multiple Qty Incentive Amt"])) { ?>
                                                        <td style="color: blue;">
                                                        <?php } else { ?>
                                                        <td>
                    <?php } ?>



                                                        <?php
                                                        $sm_no = $obj->salesman_no;
                                                        $salesmanarray[] = $sm_no;

                                                        if ($param == "Total Incentive") {
                                                            $totalincentive = 0;

                                                            if (!empty($multiQtyAmount[$sm_no])) {
                                                                $totalincentive += ($multiQtyAmount[$sm_no] * 1.25) / 100;
                                                            }
                                                            
                                                            $totalincentivesum += $totalincentive;
                                                            echo round($totalincentive);
                                                            array_push($totalincentivearray, $totalincentive);
                                                        } else
//                                                            
                                                            if ($param == "Multiple Qty No Membership Bills") {
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
                                                            $percentage = 1.25;
                                                            if (!empty($multiQtyAmount[$sm_no])) {
                                                                $multyqtyincentive = ($multiQtyAmount[$sm_no] * $percentage) / 100;
                                                            }
                                                            echo $multyqtyincentive;
                                                            array_push($multiQtyIncentivearray, $multyqtyincentive);
                                                            $multybillqtyincenvalue += $multyqtyincentive;
                                                        } else
                                                            
                                                        ?>
                                                    </td>

                <?php endforeach; ?>


                                                    <?php if ($param == "Total Incentive") { ?>
                                                    <td style="color: #006400;">
                                                    <?php } else
                                                        if (in_array($param, ["Multiple Qty No Membership Bills", "Qty in multiple bills", "Multiple Qty Bill Value", "Multiple Qty Incentive Amt"])) { ?>
                                                    <td style="color: blue;">
                                                    <?php }  else { ?>
                                                    <td>
                <?php } ?>



                                                    <?php
                                                    if ($param == "Total Incentive") {
                                                        echo $totalincentivesum;
                                                    } else
                                                        if ($param == "Multiple Qty No Membership Bills") {
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
                                                    } else

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
                        <form method="post" action="formpost/genSalesmanIncentiveExcel.php" >

                            <?php foreach ($salesmanarray as $value): ?>
                                <input type="hidden" name="salesman[]" value="<?php echo $value; ?>">
                            <?php endforeach; ?>
                            <?php foreach ($totalincentivearray as $value): ?>
                                <input type="hidden" name="totalincentivearray[]" value="<?php echo round($value); ?>">
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
   

                            <input type="hidden" name="stores" value="<?php echo $this->storeidreport; ?>"><br>
                            <input type="hidden" name="daterange" value="<?php echo $this->dtrange; ?>">
                            <input type="submit" value="Generate Excel">
                        </form>
        <?php } ?>
                </fieldset>

            </div> <?php if (isset($this->storeidreport)) { //12 fields                ?>
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