<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");
require_once "lib/core/strutil.php";
require_once "session_check.php";

class cls_report_ssales extends cls_renderer {

    var $currUser;
    var $userid;
    var $dtrange;
    var $params;
    var $date;
    var $store;
    var $billno;
    var $transaction;
    var $itemctg;
    var $designno;
    var $itemmrp;
    var $barcode;
    var $voucheramt;
    var $nnetsale;
    var $creditvoucherused;
    var $linediscountper;
    var $linediscountval;
    var $ticketdiscountper;
    var $ticketdiscountval;
    var $totaldiscount;
    var $tax;
    var $brand;
    var $category;
    var $style;
    var $size;
    var $fabric;
    var $material;
    var $prodtype;
    var $manuf;
    var $gen;
    var $itemqty;
    var $itemvalue;
    var $totalvalue;
    var $storeidreport = null;
    var $a = 0;
    var $storeloggedin = -1;
    var $month = 0;
    // var $cust;
    var $custname;
    var $custphone;
    var $hsncode;
    var $fields = array();
//         var $loyalty;
    var $area;
    var $city;
    var $location;
    var $state;
    var $region;
    var $status;
    var $salesmancode;
    var $day;

    function __construct($params = null) {
//		parent::__construct(array(UserType::Admin, UserType::CKAdmin, UserType::Manager));
        ini_set('max_execution_time', 300);
        $this->currUser = getCurrUser();
        $this->userid = $this->currUser->id;
        if (isset($_SESSION['account_dtrange'])) {
            $this->dtrange = $_SESSION['account_dtrange'];
        } else {
            $this->dtrange = date("d-m-Y");
        }
        //date,store,billno,transaction,itemctg,designno,itemmrp,barcode,linediscountper,linediscountval,ticketdiscountper,
        //ticketdiscountval, totaldiscount, tax, brand, category, style, size, fabric, material, prodtgype, manuf.
        if (isset($params['date'])) {
            $this->fields['date'] = $params['date'];
            $this->date = $params['date'];
        } else
            $this->fields['date'] = "0";
        if (isset($params['store'])) {
            $this->fields['store'] = $params['store'];
            $this->store = $params['store'];
        } else
            $this->fields['store'] = "0";
        if (isset($params['billno'])) {
            $this->fields['billno'] = $params['billno'];
            $this->billno = $params['billno'];
        } else
            $this->fields['billno'] = "0";
        if (isset($params['billtype'])) {
            $this->fields['billtype'] = $params['billtype'];
            $this->billtype = $params['billtype'];
        } else
            $this->fields['billtype'] = "0";
        if (isset($params['nnetsale'])) {
            $this->fields['nnetsale'] = $params['nnetsale'];
            $this->nnetsale = $params['nnetsale'];
        } else
            $this->fields['nnetsale'] = "0";
        if (isset($params['voucheramt'])) {
            $this->fields['voucheramt'] = $params['voucheramt'];
            $this->voucheramt = $params['voucheramt'];
        } else
            $this->fields['voucheramt'] = "0";
        if (isset($params['creditvoucherused'])) {
            $this->fields['creditvoucherused'] = $params['creditvoucherused'];
            $this->creditvoucherused = $params['creditvoucherused'];
        } else
            $this->fields['creditvoucherused'] = "0";
//                if (isset($params['transaction'])) { $this->fields['transaction']=$params['transaction']; $this->transaction = $params['transaction']; } else $this->fields['transaction']="0";
        if (isset($params['itemctg'])) {
            $this->fields['itemctg'] = $params['itemctg'];
            $this->itemctg = $params['itemctg'];
        } else
            $this->fields['itemctg'] = "0";
        if (isset($params['hsncode'])) {
            $this->fields['hsncode'] = $params['hsncode'];
            $this->hsncode = $params['hsncode'];
        } else
            $this->fields['hsncode'] = "0";
        if (isset($params['designno'])) {
            $this->fields['designno'] = $params['designno'];
            $this->designno = $params['designno'];
        } else
            $this->fields['designno'] = "0";
        if (isset($params['itemmrp'])) {
            $this->fields['itemmrp'] = $params['itemmrp'];
            $this->itemmrp = $params['itemmrp'];
        } else
            $this->fields['itemmrp'] = "0";
        if (isset($params['itemvalue'])) {
            $this->fields['itemvalue'] = $params['itemvalue'];
            $this->itemvalue = $params['itemvalue'];
        } else
            $this->fields['itemvalue'] = "0";
        if (isset($params['totalvalue'])) {
            $this->fields['totalvalue'] = $params['totalvalue'];
            $this->totalvalue = $params['totalvalue'];
        } else
            $this->fields['totalvalue'] = "0";
        if (isset($params['itemqty'])) {
            $this->fields['itemqty'] = $params['itemqty'];
            $this->itemqty = $params['itemqty'];
        } else
            $this->fields['itemqty'] = "0";
        if (isset($params['barcode'])) {
            $this->fields['barcode'] = $params['barcode'];
            $this->barcode = $params['barcode'];
        } else
            $this->fields['barcode'] = "0";
        if (isset($params['linediscountper'])) {
            $this->fields['linediscountper'] = $params['linediscountper'];
            $this->linediscountper = $params['linediscountper'];
        } else
            $this->fields['linediscountper'] = "0";
        if (isset($params['linediscountval'])) {
            $this->fields['linediscountval'] = $params['linediscountval'];
            $this->linediscountval = $params['linediscountval'];
        } else
            $this->fields['linediscountval'] = "0";
        if (isset($params['ticketdiscountper'])) {
            $this->fields['ticketdiscountper'] = $params['ticketdiscountper'];
            $this->ticketdiscountper = $params['ticketdiscountper'];
        } else
            $this->fields['ticketdiscountper'] = "0";
        if (isset($params['ticketdiscountval'])) {
            $this->fields['ticketdiscountval'] = $params['ticketdiscountval'];
            $this->ticketdiscountval = $params['ticketdiscountval'];
        } else
            $this->fields['ticketdiscountval'] = "0";
//                if (isset($params['totaldiscount'])) { $this->fields['totaldiscount']=$params['totaldiscount']; $this->totaldiscount = $params['totaldiscount']; } else $this->fields['totaldiscount']="0";
        if (isset($params['tax'])) {
            $this->fields['tax'] = $params['tax'];
            $this->tax = $params['tax'];
        } else
            $this->fields['tax'] = "0";
        if (isset($params['brand'])) {
            $this->fields['brand'] = $params['brand'];
            $this->brand = $params['brand'];
        } else
            $this->fields['brand'] = "0";
//                if (isset($params['category'])) { $this->fields['category']=$params['category']; $this->category = $params['category']; } else $this->fields['category']="0";
        if (isset($params['style'])) {
            $this->fields['style'] = $params['style'];
            $this->style = $params['style'];
        } else
            $this->fields['style'] = "0";
        if (isset($params['size'])) {
            $this->fields['size'] = $params['size'];
            $this->size = $params['size'];
        } else
            $this->fields['size'] = "0";
        if (isset($params['fabric'])) {
            $this->fields['fabric'] = $params['fabric'];
            $this->fabric = $params['fabric'];
        } else
            $this->fields['fabric'] = "0";
        if (isset($params['material'])) {
            $this->fields['material'] = $params['material'];
            $this->material = $params['material'];
        } else
            $this->fields['material'] = "0";
        if (isset($params['prodtype'])) {
            $this->fields['prodtype'] = $params['prodtype'];
            $this->prodtype = $params['prodtype'];
        } else
            $this->fields['prodtype'] = "0";
        if (isset($params['manuf'])) {
            $this->fields['manuf'] = $params['manuf'];
            $this->manuf = $params['manuf'];
        } else
            $this->fields['manuf'] = "0";
        if (isset($params['gen']))
            $this->gen = $params['gen'];
        else
            $this->gen = "0";
        if (isset($params['str']))
            $this->storeidreport = $params['str'];
        else
            $this->storeidreport = null;
        if (isset($params['month'])) {
            $this->fields['month'] = $params['month'];
            $this->month = $params['month'];
        } else {
            $this->fields['month'] = "0";
        }
        //if (isset($params['cust'])){ $this->fields['cust'] =$params['cust']; $this->cust = $params['cust']; }else{ $this->fields['cust'] = "0"; }

        if (isset($params['custname'])) {
            $this->fields['custname'] = $params['custname'];
            $this->custname = $params['custname'];
        } else {
            $this->fields['custname'] = "0";
        }
        if (isset($params['custphone'])) {
            $this->fields['custphone'] = $params['custphone'];
            $this->custphone = $params['custphone'];
        } else {
            $this->fields['custphone'] = "0";
        }
        if (isset($params['day'])) {
            $this->fields['day'] = $params['day'];
            $this->date = $params['day'];
        } else
            $this->fields['day'] = "0";

        if (isset($params['a'])) {
            $this->a = $params['a'];
        }
        if ($this->currUser->usertype == UserType::Dealer) {
            $this->storeidreport = $this->currUser->id;
            $this->storeloggedin = 1;
        }
        //facade start
        if (isset($params['monthlyrent'])) {

            $this->fields['monthlyrent'] = $params['monthlyrent'];
            $this->monthlyrent = $params['monthlyrent'];
        } else
            $this->fields['monthlyrent'] = "0";
        if (isset($params['facade'])) {

            $this->fields['facade'] = $params['facade'];
            $this->facade = $params['facade'];
        } else
            $this->fields['facade'] = "0";
        if (isset($params['carpet'])) {
            $this->fields['carpet'] = $params['carpet'];
            $this->carpet = $params['carpet'];
        } else
            $this->fields['carpet'] = "0";
//                if (isset($params['loyalty'])){ $this->fields['loyalty'] =$params['loyalty']; $this->loyalty = $params['loyalty']; }else{ $this->fields['loyalty'] = "0"; }

        if (isset($params['area'])) {
            $this->fields['area'] = $params['area'];
            $this->area = $params['area'];
        } else
            $this->fields['area'] = "0";
        if (isset($params['city'])) {
            $this->fields['city'] = $params['city'];
            $this->city = $params['city'];
        } else
            $this->fields['city'] = "0";
        if (isset($params['location'])) {
            $this->fields['location'] = $params['location'];
            $this->location = $params['location'];
        } else
            $this->fields['location'] = "0";
        if (isset($params['state'])) {
            $this->fields['state'] = $params['state'];
            $this->state = $params['state'];
        } else
            $this->fields['state'] = "0";
        if (isset($params['region'])) {
            $this->fields['region'] = $params['region'];
            $this->region = $params['region'];
        } else
            $this->fields['region'] = "0";
        if (isset($params['status'])) {
            $this->fields['status'] = $params['status'];
            $this->status = $params['status'];
        } else
            $this->fields['status'] = "0";
        if (isset($params['salesmancode'])) {
            $this->fields['salesmancode'] = $params['salesmancode'];
            $this->salesmancode = $params['salesmancode'];
        } else
            $this->fields['salesmancode'] = "-";
    }

    function extraHeaders() {
        ?>
        <script type="text/javascript" src="jqueryui/js/jquery-ui-1.7.1.custom.min.js"></script>
        <script type="text/javascript" src="js/daterangepicker.jQuery.js"></script>
        <link rel="stylesheet" href="css/ui.daterangepicker.css" type="text/css" />
        <link rel="stylesheet" href="css/redmond/jquery-ui-1.7.1.custom.css" type="text/css" title="ui-theme" />

        <link rel="stylesheet" href="js/chosen/chosen.css" />
        <script type="text/javascript" src="js/ajax.js"></script>
        <script type="text/javascript" src="js/custom.js"></script>
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
            var selectedCountry=listRight.options.selectedIndex;  
            var sval = listRight.options[selectedCountry].value;  
            if(sval=='itemqty' || sval=='totalvalue' ){

            alert('You cannot move default fields to Left');
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
//            $menuitem = "bnewbatch";
        $creditnotearray = [];
        $menuitem = "ssales";
        include "sidemenu." . $this->currUser->usertype . ".php";
        $formResult = $this->getFormResult();
        $db = new DBConn();
        $write_htm = true;
        $categories = array();
        $sizes = array();
        $styles = array();
        $mfg_by = array();
        $brands = array();
        $prod_typs = array();
        $fabric_types = array();
        $materials = array();
        $sdate = "";
        $edate = "";
        $hsncodeqry = array();
        ?>
        <div class="grid_10">
            <?php
            $display = "none";
            $num = 0;
            //create categories array
            $query = "select * from it_categories ";
            $objs = $db->fetchObjectArray($query);
            if ($objs) {
                foreach ($objs as $obj) {
                    $categories[$obj->id] = $obj->name;
                }
            }
            //create Hsn code array
            $query = "select * from it_categories ";
            $objs = $db->fetchObjectArray($query);
            if ($objs) {
                foreach ($objs as $obj) {
                    $hsncodeqry[$obj->id] = $obj->it_hsncode;
                }
            }
            //create brands array
            $query = "select * from it_brands ";
            $objs = $db->fetchObjectArray($query);
            if ($objs) {
                foreach ($objs as $obj) {
                    $brands[$obj->id] = $obj->name;
                }
            }
            //create style array
            $query = "select * from it_styles ";
            $objs = $db->fetchObjectArray($query);
            if ($objs) {
                foreach ($objs as $obj) {
                    $styles[$obj->id] = $obj->name;
                }
            }
            //create size array
            $query = "select * from it_sizes ";
            $objs = $db->fetchObjectArray($query);
            if ($objs) {
                foreach ($objs as $obj) {
                    $sizes[$obj->id] = $obj->name;
                }
            }
            //create mfg by array
            $query = "select * from it_mfg_by ";
            $objs = $db->fetchObjectArray($query);
            if ($objs) {
                foreach ($objs as $obj) {
                    $mfg_by[$obj->id] = $obj->name;
                }
            }
            //create prod_type array
            $query = "select * from it_prod_types ";
            $objs = $db->fetchObjectArray($query);
            if ($objs) {
                foreach ($objs as $obj) {
                    $prod_typs[$obj->id] = $obj->name;
                }
            }
            //create fabric_type array
            $query = "select * from it_fabric_types ";
            $objs = $db->fetchObjectArray($query);
            if ($objs) {
                foreach ($objs as $obj) {
                    $fabric_types[$obj->id] = $obj->name;
                }
            }
            //create material array
            $query = "select * from it_materials ";
            $objs = $db->fetchObjectArray($query);
            if ($objs) {
                foreach ($objs as $obj) {
                    $materials[$obj->id] = $obj->name;
                }
            }
            ?>
            <div class="box" style="clear:both;">
                <fieldset class="login">
                    <legend>Generate Sales Report</legend>
                    <p>Select values for the various fields below. Some fields allow you to pick only a single value, others allow you to pick multiple values.</p>
                    <form action="" method="" onsubmit="reloadreport(); return false;">
                        <div class="grid_12">
                            <?php if ($this->currUser->usertype != UserType::Dealer) { ?>    
                                <div class="grid_4">
                                    <b>Select Store*:</b><br/>
                                    <select name="store" id="store" data-placeholder="Choose Store" class="chzn-select" multiple style="width:100%;">
                                        <?php
                                        if ($this->storeidreport == -1) {
                                            $defaultSel = "selected";
                                        } else {
                                            $defaultSel = "";
                                        }
                                        ?>
                                        <option value="9" <?php echo $defaultSel; ?>>All Stores</option> 
                                        <?php
                                        $objs = array();
// if($this->currUser->usertype == UserType::BHMAcountant ) {
//$objs = $db->fetchObjectArray("select * from it_codes where usertype=4 and  (is_bhmtallyxml=1 or store_type=3) order by store_name");
// }else{
//     $objs = $db->fetchObjectArray("select * from it_codes where usertype=4 order by store_name");
// }
                                        $usrid = $this->currUser->id;

                                        $asgnexe = "select store_id from executive_assign where exe_id in ($usrid)";

                                        //print_r("select exe_id from executive_assign where store_id in (".$this->currUser->id."));
                                        $fasnid = $db->fetchObjectArray($asgnexe);
                                        //print_r($fasnid);
                                        $st = "";
                                        foreach ($fasnid as $exe) {
                                            $fasnid = $exe->store_id;
                                            $st = $st . $fasnid . ",";
                                        }
                                        $rmvcoln = substr($st, 0, -1);
                                        $objs = $db->fetchObjectArray("select id,store_name from it_codes where usertype=4 and id in ($rmvcoln) order by store_name");

//$objs = $db->fetchObjectArray("select * from it_codes where usertype=4 order by store_name");
//if($this->storeidreport == "-1"){
//    $storeid = array(); 
//    if($this->a==0){ //means 'all stores report is req only in excel'
//     $write_htm = false;   
//    }
//     $allstoreArrays=array();
//    if($this->currUser->usertype == UserType::BHMAcountant ) {
//    $allstoreArrays=$db->fetchObjectArray("select id from it_codes where usertype = 4 and  (is_bhmtallyxml=1 or store_type=3)");
//     }else
//         { 
//      $allstoreArrays=$db->fetchObjectArray("select id from it_codes where usertype = 4");
//      }
////    $allstoreArrays=$db->fetchObjectArray("select id from it_codes where usertype = 4");
//    foreach($allstoreArrays as $storeArray){
//        foreach($storeArray as $store){
//            array_push($storeid,$store);
//        }
//    }
//}
                                        if ($this->storeidreport == "9") {
                                            $usrid = $this->currUser->id;
                                            $asgnexe = "select store_id from executive_assign where exe_id in ($usrid)";
                                            $fasnid = $db->fetchObjectArray($asgnexe);
                                            if (empty($fasnid)) {
                                                echo '<script language="javascript">';
                                                echo 'alert("You are not assign any store. Please contact to IT team.")';
                                                echo '</script>';
                                                exit;
                                                //alert("You are not assign any store. Please contact to IT team.");
                                            }
                                            $st = "";
                                            foreach ($fasnid as $exe) {
                                                $fasnid = $exe->store_id;
                                                $st = $st . $fasnid . ",";
                                            }
                                            $rmvcoln = substr($st, 0, -1);
                                            $result = $db->fetchObjectArray("select id,store_name from it_codes where usertype=4 and id in ($rmvcoln) order by store_name");
                                            //$result = $db->fetchObjectArray("select id from it_codes where usertype=4 and region_id=$fidd order by store_name");
                                            $b = "";
                                            foreach ($result as $re) {
                                                $a = $re->id;
                                                $b .= $a . ",";
                                            }

                                            $fnl = rtrim($b, ",");
                                            $storeClause = " o.store_id in ( $fnl ) ";
                                        } else {
                                            $storeid = explode(",", $this->storeidreport);
                                        }
//print_r($allst);
//print_r($storeid);
                                        foreach ($objs as $obj) {
                                            $selected = "";
//	if (isset($this->storeidreport) && $obj->id==$this->storeidreport) { $selected = "selected"; }
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
                            <?php } ?>    
                            <div class="grid_4">
                                <span style="font-weight:bold;">Date Filter : </span></br> <input size="17" type="text" id="dateselect" name="dateselect" value="<?php echo $this->dtrange; ?>" /> (Click to see date options)
                            </div>
                            <div class="grid_4">
                                Report type*:<br />
                                <input type="radio" name="report" value="billwise" <?php if ($this->gen == 1) echo "checked"; ?> onclick="showgeneral();">General bill wise sale summary<br>
                                <input type="radio" name="report" value="itemwise" <?php if ($this->gen == 0) echo "checked"; ?> onclick="showitemwise();">Group By
                            </div>
                        </div>

                        <div class="grid_12" id="itemselection">
                            <!--		<div class="grid_12" >-->
                            <div class="grid_7">
                                <table border="0" colspan="4">
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
                                                    <option value="store">Store Name</option> 
                                                    <option value="facade">Facade Area</option>
                                                    <option value="carpet">Carpet Area</option>
                                                    <option value="monthlyrent">Monthly Rent</option>
                                                    <!--                                          <option value="transaction">Transaction Type</option>-->
                                                    <option value="itemctg">Item Category</option>
                                                    <option value="hsncode">HSN Code</option>
                                                    <option value="designno">Item Design no</option>
                                                    <option value="itemmrp">Item Price</option>
                                                    <option value="barcode">Barcode</option>
                                                    <option value="linediscountper">Line discount %</option>
                                                    <option value="linediscountval">Line discount value</option>
                                                    <option value="ticketdiscountper">Ticket discount %</option>
                                                    <option value="ticketdiscountval">Ticket discount value</option>
                                                    <!--                                          <option value="totaldiscount">Total discount</option>-->
                                                    <option value="tax">Tax Incl</option>
                                                    <option value="brand">Brand</option>
                                                    <!--                                          <option value="category">Category</option> -->
                                                    <option value="style">Style</option>
                                                    <option value="size">Size</option> 
                                                    <option value="fabric">Fabric Type</option> 
                                                    <option value="material">Material</option> 
                                                    <option value="prodtype">Production Type</option> 
                                                    <option value="manuf">Manufactured By</option> 
                                                    <option value="date" selected>Date</option>
                                                    <option value="day" selected>Day</option>
                                                    <option value="billno" selected>Bill no</option>
                                                    <option value="billtype" selected>Bill Type</option>
                                                    <option value="voucheramt" selected>Voucher Amount</option>
                                                    <option value="nnetsale" selected>Net Sale Value</option>
                                                    <option value="creditvoucherused" selected >Creditvaucher Used</option>
                                                    <option value="salesmancode" selected>Salesman ID</option>
                                                    <!--                                        <option value="itemvalue">Sold Price</option>-->
                                                    <option value="month">Month</option>
                                                    <!--<option value="cust">Customer Info</option>-->
                                                    <!--<option value="loyalty">Loyalty Points</option>-->
                                                    <?php if ($this->currUser->usertype != UserType::Dealer) { ?>
                                                        <option value="custname">Customer name</option>
                                                        <option value="custphone">Customer phone</option>
                                                    <?php } ?>
                                                    <option value="area">Area</option>
                                                    <option value="city">City</option>
                                                    <option value="location">Location</option>
                                                    <option value="state">State</option>
                                                    <option value="region">Region</option>
                                                    <option value="status">Status</option>
                                                </select>
                                            </label></td>
                                        <td colspan="1" rowspan="3" style="vertical-align:middle;">
                                            <input name="btnRight" type="button" id="btnRight" value="&gt;&gt;" onClick="javaScript:moveToRightOrLeft(1);">
                                            <br/><br/>
                                            <input name="btnLeft" type="button" id="btnLeft" value="&lt;&lt;" onClick="javaScript:moveToRightOrLeft(2);">
                                        </td>
                                        <td rowspan="3" colspan="2" align="left">
                                            <select name="selectRight" multiple size="10" style="width:200px;" id="selectRight">                                     
                                                <option value="itemqty">Item Quantity</option>                                       
                                                <option value="totalvalue">Total Value</option>
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="grid_12" id="generalselection" style="display:none;">
                            <div class="grid_12" >
                                Fields in the report:<br />
                                (Date/Bill no/Bill quantity/Bill amount/Tax/Bill discount value/Monthly Rent/Bill discount %//Voucher/Store name/Area/City/Location/State/Region/Status)
                            </div>
                        </div>
                        <div class="grid_12" id="submitbutton" style="padding:10px;">
                            <input type="submit" name="add" id="add" value="Generate Report" style="background-color:white;"/>

                            <?php if ($formResult) {
                                ?>
                                <p>
                                    <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
                                </p>
                            <?php } ?>
                        </div>
                    </form>
                </fieldset>
            </div> <!-- class=box -->
            <?php if (isset($this->storeidreport)) { //22 fields  ?>
                <div class="box grid_12" style="margin-left:0px; overflow:auto; height:500px;">
                    <?php
                    $queryfields = "";
                    $tableheaders = "";
                    $total_td = "";
                    $dtarr = explode(" - ", $this->dtrange);
                    $_SESSION['storeid'] = $this->storeidreport;
                    if (count($dtarr) == 1) {
                        list($dd, $mm, $yy) = explode("-", $dtarr[0]);
                        $sdate = "$yy-$mm-$dd";
                        $dQuery = " and o.bill_datetime >= '$sdate 00:00:00' and o.bill_datetime <= '$sdate 23:59:59' ";
                    } else if (count($dtarr) == 2) {
                        list($dd, $mm, $yy) = explode("-", $dtarr[0]);
                        $sdate = "$yy-$mm-$dd";
                        list($dd, $mm, $yy) = explode("-", $dtarr[1]);
                        $edate = "$yy-$mm-$dd";
                        $dQuery = " and o.bill_datetime >= '$sdate 00:00:00' and o.bill_datetime <= '$edate 23:59:59' ";
                    } else {
                        $dQuery = "";
                    }
                            //store sales report excel name
                $filenameas_storename = "storesales_";
            if ($this->currUser->usertype == UserType::Dealer) {
                $filenameas_storename = $this->currUser->store_name . "_";
            } else {
                $ids = explode(',', $this->storeidreport);
                if (count($ids) > 1) {
                    $filenameas_storename = "Multistores_";
                } else if (count($ids) == 1) {
                    if ($this->storeidreport == 9) {
                        $filenameas_storename = "Allstores_";
                    } else {
                        $storename = $db->fetchObject("select store_name from it_codes where id=$this->storeidreport");
                        $filenameas_storename = $storename->store_name . "_";
                    }
                }
            }
                    if ($this->gen != 1) {
                        $totTotalValue = 0;
                        $totAmt = "";
                        $newfname = $filenameas_storename."Bill_itemwise_" . $sdate . "_" . $edate . ".csv";
                        $group_by = array();
                        $total_td = "";
                        $gClause = "";
                        for ($x = 1; $x < 23; $x++) {
                            foreach ($this->fields as $field => $seq) {
                                if ($seq == $x) {
                                    if ($field == "date") {
                                        $tableheaders .= "Date:";
                                        $queryfields .= " o.bill_datetime as date,";
                                        $group_by[] = "o.bill_datetime";
                                        $total_td .= "<td></td>";
                                    } // DATE_FORMAT(o.bill_datetime,'%d/%m/%Y')
                                    if ($field == "billno") {
                                        $tableheaders .= "Bill No.:";
                                        $queryfields .= "o.bill_no,";
                                        $group_by[] = "o.bill_no";
                                        $total_td .= "<td></td>";
                                    }
                                    if ($field == "voucheramt") {
                                        $tableheaders .= "Voucher Amount:";
                                        $queryfields .= "o.voucher_amt,";
                                        $total_td .= "<td></td>";
                                    }
                                    if ($field == "creditvoucherused") {
                                        $tableheaders .= "Creditvoucher Used:";
                                        $queryfields .= "o.orderinfo ,";
                                        $total_td .= "<td></td>";
                                    }
                                    if ($field == "billtype") {
                                        $tableheaders .= "Bill Type.:";
                                        $queryfields .= "o.tickettype,";
                                        $group_by[] = "o.tickettype";
                                        $total_td .= "<td></td>";
                                    }
                                    if ($field == "billtype") {
                                        $tableheaders .= "Net Sale Value:";
                                        $queryfields .= "if(o.tickettype<='0',o.net_total,'')  AS billnettotalval,";
                                        $total_td .= "<td></td>";
                                    }

                                    if ($field == "billno") {
                                        $tableheaders .= "Store Name:";
                                        $queryfields .= "c.store_name,";
                                        $group_by[] = "o.store_id";
                                        $total_td .= "<td></td>";
                                    }
                                    if ($field == "itemctg") {
                                        $tableheaders .= "Category:";
                                        $queryfields .= "i.ctg_id as itemctg,";
                                        $group_by[] = "i.ctg_id";
                                        $total_td .= "<td></td>";
                                    }
                                    if ($field == "hsncode") {
                                        $tableheaders .= "HSN Code:";
                                        $queryfields .= "i.ctg_id as hsncode,";
                                        $group_by[] = "i.ctg_id";
                                        $total_td .= "<td></td>";
                                    }
                                    if ($field == "designno") {
                                        $tableheaders .= "Design No.:";
                                        $queryfields .= "i.design_no,";
                                        $group_by[] = "i.design_no";
                                        $total_td .= "<td></td>";
                                    }
                                    if ($field == "itemmrp") {
                                        $tableheaders .= "MRP (Rs):";
                                        $queryfields .= "i.MRP,";
                                        $group_by[] = "i.MRP";
                                        $total_td .= "<td></td>";
                                    }
//                        if ($field=="itemvalue") { $tableheaders .="Sold Price:"; $queryfields .="sum(case when (o.discount_pct is not NULL) then (((100-o.discount_pct)/100)*oi.price) else oi.price end) as itemvalue,"; $total_td .= "<td></td>"; }
//                        if ($field=="itemqty") {$tableheaders.="Quantity:"; $queryfields .= "sum(oi.quantity) as quantity,";}
                                    ////if ($field=="itemqty") {$tableheaders.="Quantity:"; $queryfields .= "(case when (o.tickettype = 0 ) then sum(oi.quantity) else 0 end) as quantity,";}                        
                                    //if($field=="totalvalue"){$tableheaders.="Total:"; $queryfields .= "(case when (o.discount_pct is not NULL) then ((((100-o.discount_pct)/100)*oi.price) * (case when (o.tickettype = 0) then oi.quantity else 0 end )) else oi.price*(case when (o.tickettype = 0) then oi.quantity else 0 end ) end) as totalvalue,";}
                                    ////if($field=="totalvalue"){$tableheaders.="Total:"; $queryfields .= "(case when (o.discount_pct is not NULL) then ((((100-o.discount_pct)/100)*oi.price) * (case when (o.tickettype = 0) then sum(oi.quantity) else 0 end )) else oi.price*(case when (o.tickettype = 0) then sum(oi.quantity) else 0 end ) end) as totalvalue,";}
                                    if ($field == "barcode") {
                                        $tableheaders .= "Barcode:";
                                        $queryfields .= "oi.barcode,";
                                        $group_by[] = "oi.barcode";
                                        $total_td .= "<td></td>";
                                    }
                                    if ($field == "linediscountper") {
                                        $tableheaders .= "Line Discount %:";
                                        $queryfields .= "oi.discount_pct as itmdiscp,";
                                        $group_by[] = "oi.discount_pct ";
                                        $total_td .= "<td></td>";
                                    }
                                    //if ($field=="linediscountval") {$tableheaders.="Line Discount Value:"; $queryfields .= "sum(oi.discount_val) as itmdiscv,";}
                                    if ($field == "linediscountval") {
                                        $tableheaders .= "Line Discount Value:";
                                        $queryfields .= "sum(case when (o.tickettype = 0 ) then oi.discount_val else 0 end) as itmdiscv,";
                                    }
                                    if ($field == "ticketdiscountper") {
                                        $tableheaders .= "Ticket Discount %:";
                                        $queryfields .= "o.discount_pct as totdiscp,";
                                        $group_by[] = "o.discount_pct";
                                        $total_td .= "<td></td>";
                                    }
                                    if ($field == "ticketdiscountval") {
                                        $tableheaders .= "Ticket Discount Value:";
                                        $queryfields .= "sum(oi.discount_val) as totdiscv,";
                                    }
                                    if ($field == "tax") {
                                        $tableheaders .= "Tax on Bill Amt:";
                                        $queryfields .= "o.tax,";
                                    }
                                    if ($field == "brand") {
                                        $tableheaders .= "Brand:";
                                        $queryfields .= "i.brand_id as brand,";
                                        $group_by[] = "i.brand_id";
                                        $total_td .= "<td></td>";
                                    }
                                    if ($field == "style") {
                                        $tableheaders .= "Style:";
                                        $queryfields .= "i.style_id as style,";
                                        $group_by[] = "i.style_id";
                                        $total_td .= "<td></td>";
                                    }
                                    if ($field == "size") {
                                        $tableheaders .= "Size:";
                                        $queryfields .= "i.size_id as size,";
                                        $group_by[] = "i.size_id";
                                        $total_td .= "<td></td>";
                                    }
                                    if ($field == "fabric") {
                                        $tableheaders .= "Fabric:";
                                        $queryfields .= "i.fabric_type_id as fabric,";
                                        $group_by[] = "i.fabric_type_id";
                                        $total_td .= "<td></td>";
                                    }
                                    if ($field == "material") {
                                        $tableheaders .= "Material:";
                                        $queryfields .= "i.material_id as material,";
                                        $group_by[] = "i.material_id";
                                        $total_td .= "<td></td>";
                                    }
                                    if ($field == "prodtype") {
                                        $tableheaders .= "Production Type:";
                                        $queryfields .= "i.prod_type_id as prodtype,";
                                        $group_by[] = "i.prod_type_id";
                                        $total_td .= "<td></td>";
                                    }
                                    if ($field == "manuf") {
                                        $tableheaders .= "Mfg By:";
                                        $queryfields .= "i.mfg_id as manuf,";
                                        $group_by[] = "i.mfg_id";
                                        $total_td .= "<td></td>";
                                    }
                                    if ($field == "month") {
                                        $tableheaders .= "Month:";
                                        $queryfields .= " CONCAT(monthname(o.bill_datetime),'-',year(o.bill_datetime)) as month , ";
                                        $group_by[] = "month";
                                        $total_td .= "<td></td>";
                                    }
                                    if ($field == "custname") {
                                        $tableheaders .= "Customer Name:";
                                        $queryfields .= " o.cust_name as customername , ";
                                        $group_by[] = "customername";
                                        $total_td .= "<td></td>";
                                    }
                                    if ($field == "custphone") {
                                        $tableheaders .= "Customer Phone:";
                                        $queryfields .= "LEFT(o.cust_phone,10) as customerphone , ";
                                        $group_by[] = "customerphone";
                                        $total_td .= "<td></td>";
                                    }
                                    if ($field == "day") {
                                        $tableheaders .= "Day:";
                                        $queryfields .= " DAYNAME(o.bill_datetime)as day , ";
                                        $group_by[] = "day";
                                        $total_td .= "<td></td>";
                                    }
                                    if ($field == "monthlyrent") {
                                        $tableheaders .= "Stores Monthly Rent:";
                                        $queryfields .= "c.monthlyrent,";
                                        //$group_by[] = "o.store_id";
                                        $total_td .= "<td></td>";
                                    }
                                    if ($field == "facade") {
                                        $tableheaders .= "Store Facade Area:";
                                        $queryfields .= "c.facade,";
                                        //$group_by[] = "o.store_id";
                                        $total_td .= "<td></td>";
                                    }if ($field == "carpet") {
                                        //strat
                                        $alldealersobj = $db->fetchObjectArray("select carpet from it_codes where usertype=4");
                                        $maxCarpetLength = 0;
                                        foreach ($alldealersobj as $dealer) {
                                            $carpetArray = explode(',', $dealer->carpet); // Split the string by comma
                                            $numberOfValues = count($carpetArray);
                                            if ($numberOfValues > $maxCarpetLength) {
                                                $maxCarpetLength = $numberOfValues;
                                            }

//        }       
                                        }

                                        //end
                                        $tableheaders .= "Store Carpet Area:";

                                        for ($k = 1; $k < $maxCarpetLength; $k++) {
                                            $tableheaders .= "Store Floor " . $k . " Area:";
                                            $total_td .= "<td></td>";
                                        } $tableheaders .= "Total Carpet Area:";
                                        $total_td .= "<td></td>";
                                        $queryfields .= " c.carpet,";
                                        //$group_by[] = "o.store_id";
                                        $total_td .= "<td></td>";
                                    }
                                    //   if ($field=="cust") {$tableheaders.="Customer:"; $queryfields .= " CONCAT(cust_name,' : ',cust_phone) as customer , ";$group_by[] = "customer"; $total_td .= "<td></td>";}
                                    /* if ($field=="store") { $tableheaders.="Store Name:"; $queryfields .= "c.store_name,"; $group_by[] = "o.store_id"; $total_td .= "<td></td>"; }
                                      if ($field=="itemctg") {$tableheaders.="Category:"; $queryfields .= "i.ctg_id as itemctg,"; $group_by[] = "i.ctg_id"; $total_td .= "<td></td>"; }
                                      if ($field=="designno") {$tableheaders.="Design No.:"; $queryfields .= "i.design_no,"; $group_by[] = "i.design_no"; $total_td .= "<td></td>"; }
                                      if ($field=="itemmrp") {$tableheaders.="MRP (Rs):"; $queryfields .= "i.MRP,"; $group_by[] = "i.MRP"; $total_td .= "<td></td>"; }
                                      if ($field=="brand") {$tableheaders.="Brand:"; $queryfields .= "i.brand_id as brand,"; $group_by[] = "i.brand_id"; $total_td .= "<td></td>"; }
                                      if ($field=="style") {$tableheaders.="Style:"; $queryfields .= "i.style_id as style,"; $group_by[] = "i.style_id"; $total_td .= "<td></td>"; }
                                      if ($field=="size") {$tableheaders.="Size:"; $queryfields .= "i.size_id as size,"; $group_by[] = "i.size_id"; $total_td .= "<td></td>"; }
                                      if ($field=="fabric") {$tableheaders.="Fabric:"; $queryfields .= "i.fabric_type_id as fabric,"; $group_by[] = "i.fabric_type_id"; $total_td .= "<td></td>"; }
                                      if ($field=="material") {$tableheaders.="Material:"; $queryfields .= "i.material_id as material,"; $group_by[] = "i.material_id"; $total_td .= "<td></td>"; }
                                      if ($field=="prodtype") {$tableheaders.="Production Type:"; $queryfields .= "i.prod_type_id as prodtype,"; $group_by[] = "i.prod_type_id"; $total_td .= "<td></td>"; } */
//                        if ($field=="loyalty") {$tableheaders.="Loyalty Points:"; $queryfields .= " sum(o.use_lpoints) as loyalty , "; $total_td .= "<td></td>";}

                                    if ($field == "area") {
                                        $tableheaders .= "Area:";
                                        $queryfields .= "c.Area,";
                                        $group_by[] = "c.Area";
                                        $total_td .= "<td></td>";
                                    }
                                    if ($field == "city") {
                                        $tableheaders .= "City:";
                                        $queryfields .= "c.city,";
                                        $group_by[] = "c.city";
                                        $total_td .= "<td></td>";
                                    }
                                    if ($field == "location") {
                                        $tableheaders .= "Location:";
                                        $queryfields .= "c.Location,";
                                        $group_by[] = "c.Location";
                                        $total_td .= "<td></td>";
                                    }
                                    if ($field == "state") {
                                        $tableheaders .= "state:";
                                        $queryfields .= "s.state,";
                                        $group_by[] = "s.state";
                                        $total_td .= "<td></td>";
                                    }
                                    if ($field == "region") {
                                        $tableheaders .= "region:";
                                        $queryfields .= "r.region,";
                                        $group_by[] = "r.region";
                                        $total_td .= "<td></td>";
                                    }
                                    if ($field == "status") {
                                        $tableheaders .= "Status:";
                                        $queryfields .= "c.status,";
                                        $group_by[] = "c.status";
                                        $total_td .= "<td></td>";
                                    }
                                    if ($field == "salesmancode") {
                                        $tableheaders .= "Salesman Id:";
                                        $queryfields .= "o.salesman_code,";
                                        $group_by[] = "o.salesman_code";
                                        $total_td .= "<td></td>";
                                    }
                                }
                            }
                        }
                        if (!empty($group_by)) {
                            $gClause = " group by " . implode(",", $group_by);
                        }
//            if (strpos($queryfields,"o.tickettype") === false){
//               $queryfields .= "o.tickettype,"; 
//            }
                        $tableheaders .= "Quantity:";
                        $queryfields .= "sum(case when (o.tickettype in (0,1,6) ) then oi.quantity else 0 end) as quantity,";
                        $tableheaders .= "Total Value:";
                        $queryfields .= "sum(case when (o.discount_pct is not NULL) then ((((100-o.discount_pct)/100)*oi.price) * (case when (o.tickettype in (0,1,6)) then (oi.quantity) else 0 end )) else oi.price*(case when (o.tickettype in (0,1,6)) then (oi.quantity) else 0 end ) end) as totalvalue,";
                        $queryfields = substr($queryfields, 0, -1);
                        $storeClause = "";
//            if($this->storeidreport == "-1"){               
//                 if($this->currUser->usertype == UserType::BHMAcountant ) {
//                $storeClause = " c.usertype = ".UserType::Dealer." and  (is_bhmtallyxml=1 or store_type=3)" ;
//                }else{
//                    $storeClause = " c.usertype = ".UserType::Dealer ;
//                }
////                $storeClause = " c.usertype = ".UserType::Dealer ;
//            }
                        if ($this->storeidreport == "9") {
                            $usrid = $this->currUser->id;
                            $asgnexe = "select store_id from executive_assign where exe_id in ($usrid)";
                            $fasnid = $db->fetchObjectArray($asgnexe);
                            if (empty($fasnid)) {
                                echo '<script language="javascript">';
                                echo 'alert("You are not assign any store. Please contact to IT team.")';
                                echo '</script>';
                                exit;
                                //alert("You are not assign any store. Please contact to IT team.");
                            }
                            $st = "";
                            foreach ($fasnid as $exe) {
                                $fasnid = $exe->store_id;
                                $st = $st . $fasnid . ",";
                            }
                            $rmvcoln = substr($st, 0, -1);
                            $result = $db->fetchObjectArray("select id,store_name from it_codes where usertype=4 and id in ($rmvcoln) order by store_name");
                            //$result = $db->fetchObjectArray("select id from it_codes where usertype=4 and region_id=$fidd order by store_name");
                            $b = "";
                            foreach ($result as $re) {
                                $a = $re->id;
                                $b .= $a . ",";
                            }

                            $fnl = rtrim($b, ",");
                            $storeClause = " o.store_id in ( $fnl ) ";
                        } else {
                            $storeClause = " o.store_id in ( $this->storeidreport ) ";
                        }

                        $query = "select $queryfields,o.orderinfo";
                        // $query .= " from it_orders o,it_order_items oi, it_items i, it_codes c,states s,region r where $storeClause $dQuery and oi.order_id=o.id and i.id = oi.item_id and  o.store_id = c.id  and s.id = c.state_id and c.region_id=r.id  ".$gClause;
                        $query .= " from it_orders o,it_order_items oi, it_items i, it_codes c,states s,region r where $storeClause $dQuery and oi.order_id=o.id and i.id = oi.item_id and  o.store_id = c.id  and s.id = c.state_id and c.region_id=r.id  " . $gClause;
                        //print $query;
                        // print $query; //and c.id in ( $storeClause)
                        //error_log("1:$query\n",3, "../ajax/tmp.txt");
                        $result = $db->execQuery($query);
                    } else if ($this->gen == 1) {
                        $newfname = $filenameas_storename."Billwise_" . $sdate . "_" . $edate . ".csv";
                        $totTotalValue = "";
                        $totAmt = 0;
                        $storeClause = "";
                        $total_td .= "<td></td><td></td><td></td>";
//            if($this->storeidreport == "-1"){   
//                 if($this->currUser->usertype == UserType::BHMAcountant ) {
//                $storeClause = " c.usertype = ".UserType::Dealer." and  (is_bhmtallyxml=1 or store_type=3)" ;
//                }else{
//                    $storeClause = " c.usertype = ".UserType::Dealer ;
//                }
////                $storeClause = " c.usertype = ".UserType::Dealer ;
//            }
                        if ($this->storeidreport == "9") {
                            $usrid = $this->currUser->id;
                            $asgnexe = "select store_id from executive_assign where exe_id in ($usrid)";
                            $fasnid = $db->fetchObjectArray($asgnexe);
                            if (empty($fasnid)) {
                                echo '<script language="javascript">';
                                echo 'alert("You are not assign any store. Please contact to IT team.")';
                                echo '</script>';
                                exit;
                                //alert("You are not assign any store. Please contact to IT team.");
                            }
                            $st = "";
                            foreach ($fasnid as $exe) {
                                $fasnid = $exe->store_id;
                                $st = $st . $fasnid . ",";
                            }
                            $rmvcoln = substr($st, 0, -1);
                            $result = $db->fetchObjectArray("select id,store_name from it_codes where usertype=4 and id in ($rmvcoln) order by store_name");
                            //$result = $db->fetchObjectArray("select id from it_codes where usertype=4 and region_id=$fidd order by store_name");
                            $b = "";
                            foreach ($result as $re) {
                                $a = $re->id;
                                $b .= $a . ",";
                            }

                            $fnl = rtrim($b, ",");
                            $storeClause = " o.store_id in ( $fnl ) ";
                        } else {
                            $storeClause = " o.store_id in ( $this->storeidreport ) ";
                        }
                        $tableheaders = "Date:Bill No:Bill Type:Bill Quantity:Bill Amount:Tax:Bill Discount Value:Bill Discount %:Voucher:Store Name:Area:city:Location:State:Region:Status";
                        //$query2 = "select DATE_FORMAT(o.bill_datetime,'%d/%m/%Y') as bill_datetime,o.bill_no,o.tickettype,o.quantity,o.amount,o.tax,o.discount_val,o.discount_pct,o.voucher_amt from it_orders o where o.store_id in ( $storeClause ) $dQuery group by $gClause o.bill_no order by bill_datetime";
                        // $query2 = "select o.bill_datetime ,o.bill_no,o.tickettype,o.quantity,o.amount,o.tax,o.discount_val,o.discount_pct,o.voucher_amt,c.store_name,c.Area,c.city,c.Location,s.state,r.region,c.status  from it_orders o , it_codes c,states s,region r   where $storeClause  and  o.store_id = c.id  and s.id=c.state_id and r.id = c.region_id  $dQuery group by o.store_id, o.bill_no order by bill_datetime";
                        $query2 = "select o.bill_datetime ,o.bill_no,o.tickettype,o.orderinfo,o.quantity,o.amount,o.tax,o.discount_val,o.discount_pct,o.voucher_amt,c.store_name,c.Area,c.city,c.Location,s.state,r.region,c.status  from it_orders o , it_codes c,states s,region r   where $storeClause  and  o.store_id = c.id  and s.id=c.state_id and r.id = c.region_id  $dQuery group by o.store_id, o.bill_no order by bill_datetime";

                        // echo $query2;
                        $result = $db->execQuery($query2);
                    }
                    ?>
                    <br /><div id="dwnloadbtn" style='margin-left:40px; padding-left:15px; height:24px;width:130px;border: solid gray 1px;background:#F5F5F5;padding-top:4px;'>
                        <a href='<?php echo "tmp/storesales.php?output=$newfname"; ?>' title='Export table to CSV'><img src='images/excel.png' width='20' hspace='3' style='margin-bottom:-6px;' /> Export To Excel</a>
                    </div><br />

                    <?php
                    $totqty = 0;
                    $totsp = 0;
                    if (isset($result)) {
                        $fp = fopen('tmp/StoreSales.csv', 'w');
                        if ($write_htm) {
                            $fp2 = fopen('tmp/storesales.htm', 'w');
                        }
                        if ($fp) {
                            $trow = array();
                            $tcell = array();
                            //write header info
                            if ($write_htm) {
                                fwrite($fp2, "<table width='100%' style='overflow:auto;'><thead><tr>");
                            }
                            $headerarr = explode(":", $tableheaders);
                            foreach ($headerarr as $harr) {
                                if ($harr != "") {
                                    $tcell[] .= $harr;
                                    if ($write_htm) {
                                        fwrite($fp2, "<th>$harr</th>");
                                    }
                                }
                            }
                            fputcsv($fp, $tcell, ',', chr(0));
                            if ($write_htm) {
                                fwrite($fp2, "</tr></thead><tbody>");
                            }
                            //write body
                            while ($reportrows = $result->fetch_object()) {
                                $tcell = array(); // Initialize array to avoid null issues

                                if ($write_htm) {
                                    fwrite($fp2, "<tr>");
                                }
                                     $discountarray = json_decode($reportrows->orderinfo, true);
                                     $firstDiscountValue = $discountarray['ticketlines'][0]['discountval'];
                                // Decode orderinfo JSON
                                $json_array = !empty($reportrows->orderinfo) ? json_decode($reportrows->orderinfo, true) : array();
                                $isTouch = isset($json_array['creditNoteUsed']);
                                $ticketid = isset($json_array['ticketId']) ? $json_array['ticketId'] : "";

                                // Extract credit note information
                                $cn = isset($json_array['creditNoteUsed']) ? str_replace("Credit Voucher Used :", "", $json_array['creditNoteUsed']) : "";

                                if (!empty($cn)) {
                                    array_push($creditnotearray, $cn);
                                }
                                $totaldiscountvalue=0;
                                 if(!empty($reportrows->bill_no)){
                                    if($this->storeidreport!="9"){
                                        $orderid=$db->fetchObject("select id from it_orders where bill_no='$reportrows->bill_no' and store_id in ($this->storeidreport);");
                                    
                                 
                                    }
                                    else{
//                                        echo "select id,store_name from it_codes where store_name='$reportrows->store_name';";
                                        $getStoreids=$db->fetchObject("select id,store_name from it_codes where store_name='$reportrows->store_name';");
//                                        echo "select id from it_orders where bill_no='$reportrows->bill_no';"."<br>";
                                        $orderid=$db->fetchObject("select id from it_orders where bill_no='$reportrows->bill_no' and store_id=$getStoreids->id;");
                                    }
                                $isloyaltybill=$db->fetchObject("SELECT CASE WHEN EXISTS ( SELECT 1 FROM it_orders o, it_order_payments op WHERE o.id = op.order_id AND o.bill_no = '$reportrows->bill_no' AND o.store_id IN ($this->storeidreport) AND op.payment_name = 'loyalty' ) THEN 1 ELSE 0 END AS loyalty_payment_used");
//                                print_r("SELECT CASE WHEN EXISTS ( SELECT 1 FROM it_orders o, it_order_payments op WHERE o.id = op.order_id AND o.bill_no = 'LK-252600519' AND o.store_id IN (9) AND op.payment_name = 'loyalty' ) THEN 1 ELSE 0 END AS loyalty_payment_used");
                                 $billnettotalval = (isset($reportrows->billnettotalval) && $reportrows->billnettotalval !== '') ? $reportrows->billnettotalval : 0;
                                
                                    }                       
                                if(!empty($orderid)){
                                $orderidtotaldiscount=$db->fetchObject("select sum(discount_val) as discount from it_order_items where order_id=$orderid->id");
                                }
                                                              
                                $totaldiscountvalue=0;
                                if(!empty($orderidtotaldiscount)){
                                $totaldiscountvalue=$orderidtotaldiscount->discount;
                                }
                                else{
                                    $totaldiscountvalue=0;
                                }

                                foreach ($reportrows as $field => $value) {
                                    if ($field == "tax") {
                                        $value = sprintf('%.2f', $value);
                                    } else if ($field == "tickettype") {
                                        if ($value == '3') {
                                            $value = 'Cancelled';
                                        } else if ($reportrows->quantity < 0) {
                                          $ticketid = isset($json_array['ticketId']) ? $json_array['ticketId'] : "";

                                            if (in_array($ticketid, $creditnotearray)) {
                                                $value = "Exchanged";
                                            }  else {
                                                $value = 'Credit Note';
                                            }
                                        } else {
                                            if (!empty($json_array['creditNoteUsed'])) {
                                                $value = 'Exchanged';
                                            } elseif ($totaldiscountvalue > 0) {
                                                if($isloyaltybill->loyalty_payment_used == "1"){
                                                    if (round($totaldiscountvalue) >= 499 && round($totaldiscountvalue) <= 501) {
                                                $value = 'Hurdle 1 Discount';
                                            } elseif (round($totaldiscountvalue) >= 999 && round($totaldiscountvalue) <= 1001) {
                                                $value = 'Hurdle 2 Discount';
                                            } else {

                                                $value = 'Discount';
                                            }
                                        }else{
                                                 if (round($billnettotalval)>9999) {
                                                $value = 'Hurdle 2 Discount';
                                            } elseif (round($billnettotalval)>5999) {
                                                $value = 'Hurdle 1 Discount';
                                            } else {

                                                $value = 'Discount';
                                            }
                                                    
                                                }
                                                
                                            } else {
                                                $value = 'Sale';
                                            }
                                        }
                                    } else if ($field == "date") {
                                        $value = ddmmyy2($value);
                                    } else if ($field == "orderinfo") {
                                        // Display full creditNoteUsed value
//                                         $json_array = json_decode($reportrows->orderinfo, true);
//                                        if (!empty($json_array['creditNoteUsed']) && in_array($ticketid, $creditnotearray)) {
                                        if(isset($this->creditvoucherused)){
                                            $value = isset($json_array['creditNoteUsed']) ? substr($json_array['creditNoteUsed'], 21) : "";
                                        }
                                        else{
//                                            continue;
                                        $value="";
//                                            unset($value);
                                        }
                                        

                                    } else if ($field == "quantity") {
                                        $totqty += $value;
                                    } else if ($field == "itemvalue") {
                                        $totsp += $value;
                                    } else if ($field == "totalvalue") {
                                        $totTotalValue += $value;
                                    } else if ($field == "amount") {
                                        $totAmt += $value;
                                    } else if ($field == "itemctg") {
                                        $value = isset($categories[$value]) ? $categories[$value] : "";
                                    } else if ($field == "hsncode") {
                                        $value = isset($hsncodeqry[$value]) ? $hsncodeqry[$value] : "";
                                    } else if ($field == "brand") {
                                        $value = isset($brands[$value]) ? $brands[$value] : "";
                                    } else if ($field == "style") {
                                        $value = isset($styles[$value]) ? $styles[$value] : "";
                                    } else if ($field == "size") {
                                        $value = isset($sizes[$value]) ? $sizes[$value] : "";
                                    } else if ($field == "fabric") {
                                        $value = isset($fabric_types[$value]) ? $fabric_types[$value] : "";
                                    } else if ($field == "material") {
                                        $value = isset($materials[$value]) ? $materials[$value] : "";
                                    } else if ($field == "prodtype") {
                                        $value = isset($prod_typs[$value]) ? $prod_typs[$value] : "";
                                    } else if ($field == "manuf") {
                                        $value = isset($mfg_by[$value]) ? $mfg_by[$value] : "";
                                    } else if ($field == "status") {
                                        $statusname = StoreStatus::getName($value);
                                        $value = ($statusname == 'Unknown') ? "" : $statusname;
                                    } else if ($field == "carpet") {
                                        $numberArray = explode(",", $value);
                                        $value = '';

                                        for ($k = 0; $k < $maxCarpetLength; $k++) {
                                            if (!isset($numberArray[$k]) || $numberArray[$k] == "") {
                                                $value .= "<td>-</td>";
                                                $tcell[] = "";
                                            } else {
                                                $value .= "<td>" . $numberArray[$k] . "</td>";
                                                $tcell[] = trim($numberArray[$k]);
                                            }
                                        }

                                        $sum = array_sum(array_map('intval', $numberArray)); // Optimized sum calculation
                                        $value .= "<td>" . $sum . "</td>";
                                        $tcell[] = trim($sum);
                                    } else if ($field == "salesman_code") {
                                        $value = ($value == "" || $value == null) ? "-" : $value;
                                    }

                                    if ($field != "carpet") {
                                        $tcell[] = trim($value);
                                    }

                                    if ($write_htm) {
                                        fwrite($fp2, ($field == "carpet") ? trim($value) : "<td>" . trim($value) . "</td>");
                                    }
                                }

                                fputcsv($fp, $tcell, ',', chr(0));

                                if ($write_htm) {
                                    fwrite($fp2, "</tr>");
                                }
                            }


                            if ($this->gen == 1) {
                                $totTotalValue = $totAmt;
                            }
                            if ($write_htm) {
                               if(isset($this->creditvoucherused)){
                                    fwrite($fp2, "<tr><tr>$total_td-<td><b>$totqty</b></td><td><b>$totTotalValue</b></td></tr></tr>");
                                }
                                else{
                                    fwrite($fp2, "<tr>$total_td-<td><b>$totqty</b></td><td><b>$totTotalValue</b></td></tr>");
                                }
                                fwrite($fp2, "</tbody></table>");
                                fclose($fp2);
                            }
                            fclose($fp);
                            if ($write_htm) {
                                $table = file_get_contents("tmp/storesales.htm");
                                echo $table;
                            }
                        } else {
                            echo "<br/>Unable to create file. Contact Intouch.";
                        }
                    }
                    ?>
                </div>
            <?php } ?>
        </div>
        <script src="js/chosen/chosen.jquery.js" type="text/javascript"></script>
        <script type="text/javascript"></script>
        <script type="text/javascript">
                                                var storeid = '<?php echo $this->storeidreport; ?>';
                                                var storeloggedin = '<?php echo $this->storeloggedin; ?>';
        //alert("STORE ID: "+storeid);
        //alert("STORE LOGGED IN: "+storeloggedin);
                                                $(function () {
                                                    $(".chzn-select").chosen();
                                                    $(".chzn-select-deselect").chosen({allow_single_deselect: true});
                                                    var isOpen = false;
                                                    $('#dateselect').daterangepicker({
                                                        dateFormat: 'dd-mm-yy',
                                                        arrows: false,
                                                        closeOnSelect: true,
                                                        onOpen: function () {
                                                            isOpen = true;
                                                        },
                                                        onClose: function () {
                                                            isOpen = false;
                                                        },
                                                        onChange: function () {
                                                            if (isOpen) {
                                                                return;
                                                            }
                                                            var dtrange = $("#dateselect").val();
                                                            $.ajax({
                                                                url: "savesession.php?name=account_dtrange&value=" + dtrange,
                                                                success: function (data) {
                                                                    //window.location.reload();
                                                                }
                                                            });
                                                        }
                                                    });

                                                    var radio = $('input[name=report]:checked').val()
                                                    if (radio == 'billwise') {
                                                        showgeneral();
                                                    } else {
                                                        showitemwise();
                                                    }
        //        $('#dwnloadbtn').hide();
                                                });

                                                function showgeneral() {
                                                    $('#itemselection').hide();
                                                    $('#generalselection').show();
                                                }

                                                function showitemwise() {
                                                    $('#generalselection').hide();
                                                    $('#itemselection').show();
                                                }

                                                var fieldlist = new Array();

                                                function reloadreport() {

                                                    var ret = dateRange();
                                                    if (ret == 1)
                                                        return;


                                                    if (storeloggedin == '-1') {
                                                        storeid = $('#store').val();
                                                        //alert("SID:"+storeid);
                                                    }
                                                    //alert("1: "+storeid);
                                                    var aclause = '';
                                                    if (storeid == '-1') {
                                                        resp = confirm("Do you want all stores report visible on portal?");
                                                        if (resp) {
                                                            aclause = '/a=1';
                                                        }
                                                    }
                                                    //alert("a:"+aclause);
        //       // $('select.foo option:selected').val(); commented
                                                    var reporttype = $('input[name=report]:radio:checked').val();
                                                    //alert(reporttype);commented
                                                    $('#selectRight option').attr('selected', 'selected');
                                                    // var storeid = $('#store').val();      
                                                    //alert("2: "+storeid);//commented
                                                    if (storeid != "" && storeid != null) {
                                                        if (reporttype == "itemwise") {
                                                            var multiplevalues = $('#selectRight').val();
                                                            //var values = $('#itemfields').attr('name'); commented
                                                            //alert(values);commented
                                                            //alert(multiplevalues);commented
                                                            var append = '';
                                                            var sequence = 1;
                                                            for (var i = 0; i < multiplevalues.length; i++) {
                                                                append += "/" + multiplevalues[i] + "=" + sequence;
                                                                sequence++;
                                                            }
                                                            window.location.href = "report/ssales/str=" + storeid + append + aclause;
                                                        } else {
                                                            window.location.href = "report/ssales/str=" + storeid + "/gen=1" + aclause;
                                                        }
                                                        $('#dwnloadbtn').show();
                                                    } else {
                                                        alert("please select store(s) to genereate a report");
                                                    }
                                                }
        </script>
        <?php
    }

}
?>