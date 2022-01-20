<?php
require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "session_check.php";
require_once "lib/core/strutil.php";
require_once "lib/core/Constants.php";

class cls_sales_incentive extends cls_renderer {

    var $currUser;
    var $userid;
    var $id;
    var $storeids = -1;
    var $rtype = -1;
    var $dtrange;
    var $params;
    var $yeartpess;
    var $qtrtypess;
    var $remark;

    function __construct($params = null) {
        $this->currUser = getCurrUser();
        $this->userid = $this->currUser->id;

        if (isset($_SESSION['id'])) {
            $this->id = $_SESSION['id'];
        } else {
            $this->id = "3";
        }//  var $id;
        //echo 'id is '.$this->id;
//                if (isset($_SESSION['dtrange'])) { $this->dtrange = $_SESSION['dtrange']; }
//                else { $this->dtrange = date("d-m-Y"); }
        if ($params && isset($params['storeids'])) {
            $this->storeids = $params['storeids'];
        }
        if ($params && isset($params['rtype'])) {
            $this->rtype = $params['rtype'];
        }
        if ($params && isset($params['dtrange'])) {
            $this->dtrange = $params['dtrange'];
        }
          if ($params && isset($params['remark'])) {
            $this->remark = $params['remark'];
        
        }
        if ($params && isset($params['yeartpes'])) {
            $this->yeartpess = $params['yeartpes'];
        }
        // echo 'date is set like'.$this->dtrange;
        //yeartpes //qtrtype///sss
        if ($params && isset($params['qtrtype'])) {
            $this->qtrtypess = $params['qtrtype'];
        }
    }

    function extraHeaders() {
        ?>

        <script type="text/javascript" src="jqueryui/js/jquery-ui-1.7.1.custom.min.js"></script>
        <script type="text/javascript" src="js/daterangepicker.jQuery.js"></script>
        <link rel="stylesheet" href="css/ui.daterangepicker.css" type="text/css" />
        <link rel="stylesheet" href="css/redmond/jquery-ui-1.7.1.custom.css" type="text/css" title="ui-theme" />

        <!--<link rel="stylesheet" href="js/chosen/chosen.css" />
        <script src="js/chosen/chosen.jquery.js" type="text/javascript"></script>-->

        <script type="text/javascript" src="js/ajax.js"></script>
        <style type="text/css" title="currentStyle">
            @import "js/datatables/media/css/demo_page.css";
            @import "js/datatables/media/css/demo_table.css";
            @import "css/redmond/jquery-ui-1.7.1.custom.css";
        </style>

        <script src="js/chosen/chosen.jquery.js" type="text/javascript"></script>
        <script src="js/datatables/media/js/jquery.dataTables.min.js"></script>
        <link rel="stylesheet" href="js/chosen/chosen.css" />
        <link rel="stylesheet" href="css/bigbox.css" type="text/css" />

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
        <!--<link rel="stylesheet" href="css/bigbox.css" type="text/css" />-->
        <script type="text/javascript">

            $(function () {
                $(".chzn-select").chosen();
                $(".chzn-select-deselect").chosen({allow_single_deselect: true});

            });

            //JS CODE For Representive Login 
            function setQtr_sales(qtr) {
                //  alert(qtr);

        //                 document.getElementById('salesincentive').style.display = 'none';
                var yr = $("#yrtype").val();

                if (qtr != 'd' && yr != 'fd') {
                    $yearsssss = yr;
                    $yearset = $yearsssss.split("-");
                    $syear = $yearset[0];
                    $eyear = $yearset[1];

                    switch (qtr) {

                        case 'q1':
                            $fromdate = $syear + "-04-01 00:00:00";
                            $enddate = $syear + "-06-30 23:59:59";
                            $fromdate1 = $fromdate.split(" ");
                            $enddate1 = $enddate.split(" ");
                            document.getElementById("from").value = $fromdate1[0];
                            document.getElementById("to").value = $enddate1[0];
                            break;

                        case 'q2':
                            $fromdate = $syear + "-07-01 00:00:00";
                            $enddate = $syear + "-09-30 23:59:59";
                            $fromdate1 = $fromdate.split(" ");
                            $enddate1 = $enddate.split(" ");
                            document.getElementById("from").value = $fromdate1[0];
                            document.getElementById("to").value = $enddate1[0];
                            break;

                        case 'q3':
                            $fromdate = $syear + "-10-01 00:00:00";
                            $enddate = $syear + "-12-31 23:59:59";
                            $fromdate1 = $fromdate.split(" ");
                            $enddate1 = $enddate.split(" ");
                            document.getElementById("from").value = $fromdate1[0];
                            document.getElementById("to").value = $enddate1[0];
                            break;

                        case 'q4':

                            $fromdate = $eyear + "-01-01 00:00:00";
                            $enddate = $eyear + "-03-31 23:59:59";
                            $fromdate1 = $fromdate.split(" ");
                            $enddate1 = $enddate.split(" ");
                            document.getElementById("from").value = $fromdate1[0];
                            document.getElementById("to").value = $enddate1[0];
                            break;



                    }
                } else {
                    alert('Select Year First');
                    $('#atype option:selected').removeAttr('selected');
                }



            }

            function insertinc() {  // Function For Inserting Salesman And Store Incentive From Sales Login

                var storeids = $("#store").val();  //SET STORE ID     
                var from = $("#from").val();//SET DATE
                var to = $("#to").val();//SET DATE 
                var yrtypes = $("#yrtype").val();// SET YR 
                var atypes = $("#atype").val();//SET QUATER 
                var user_id = $("#user_id").val();//SET USER ID
                var sman = $("#sman").val();//set salesman incentive 
                var sincentive = $("#sincentive").val(); //set store incentive 
                  var remark = $("#remark").val();
                if (sman != "" && sincentive != "" && storeids != null && from != "" && to != "" && atypes != 'd' && yrtypes != 'fd') {
                    window.location.href = "formpost/salesRepresentiveIncentive.php?storeids=" + storeids + "&from=" + from + "&to=" + to + "&user_id=" + user_id + "&sman=" + sman + "&sincentive=" + sincentive+ "&remark=" + remark;
                } else {

                    //document.getElementById('Logo').style.display = 'inline'; //salesincentive
                    //document.getElementById('salesincentive').style.display = 'inline';
                    alert('Please Fill All values');

                    if (yrtypes == "fd") {
                        document.getElementById('yearlabel').style.display = 'inline';
                    }
                    if (atypes == "d") {
                        document.getElementById('qtrlabel').style.display = 'inline';
                    }
                    if (storeids == null)
                    {
                        document.getElementById('storelabel').style.display = 'inline';
                    }

                    if (sman == "") {
                        document.getElementById('salesinclabel').style.display = 'inline';
                    }
                    if (sincentive == "") {
                        document.getElementById('storesinclabel').style.display = 'inline';
                    }
                }

            }

            function yearlablehide() {

                // alert('yearlable');
                document.getElementById('yearlabel').style.display = 'none';
            }
            function qtrlablehide() {
                //  alert('yearlabel');
                document.getElementById('qtrlabel').style.display = 'none';
            }
            function storelablehide() {

                //alert('storelable');
                document.getElementById('storelabel').style.display = 'none';

            }


            function saleslablehide() {

                // alert('saleslable');
                document.getElementById('salesinclabel').style.display = 'none';
            }
            function storeinclablehide() {

                //alert('storeslable');
                document.getElementById('storesinclabel').style.display = 'none';
            }

            // JS CODE FOR RADIO BUTTON FOR DOWNLOAD AND EDIT RECORDS START HERE 
            $(function () {

                $("#cn").change(function () {
                    var id = $("#cn").val();

                    $.ajax({
                        url: "savesession.php?name=id&value=" + id,
                        success: function (data) {
                            //window.location.reload();
                            window.location.href = "sales/incentive";
                        }
                    });

                });
                $("#cn1").change(function () {
                    var id = $("#cn1").val();

                    $.ajax({
                        url: "savesession.php?name=id&value=" + id,
                        success: function (data) {

                            //window.location.reload();
                            window.location.href = "sales/incentive";
                        }
                    });
                });
            });

            // JS CODE FOR EDIT RECORDS START HERE 

            function setyr(yr) {
                //alert($('select[name="store"]:selected').attr('class')); 
                //$(".store").removeAttr("class="chzn-select"");    //it remove attribute for class chzn select of                   
                //$('#store option:selected').removeAttr('selected');  /// it remove attribute of select option
                //$('#from').removeAttr('value');   //it remove attribute for text
                window.location.href = "sales/incentive/yeartpes=" + yr;

            }

            function setQtr(qtr) {
                //alert(qtr);
                var yr = $("#yrtype").val();
                //alert(yr);
                if (qtr != 'd' && yr != 'fd') {
                    $yearsssss = yr;
                    $yearset = $yearsssss.split("-");
                    $syear = $yearset[0];
                    $eyear = $yearset[1];

                    switch (qtr) {

                        case 'q1':
                            $fromdate = $syear + "-04-01 00:00:00";
                            $enddate = $syear + "-06-30 23:59:59";
                            $fromdate1 = $fromdate.split(" ");
                            $enddate1 = $enddate.split(" ");
                            document.getElementById("from").value = $fromdate1[0];
                            document.getElementById("to").value = $enddate1[0];
                            break;

                        case 'q2':
                            $fromdate = $syear + "-07-01 00:00:00";
                            $enddate = $syear + "-09-30 23:59:59";
                            $fromdate1 = $fromdate.split(" ");
                            $enddate1 = $enddate.split(" ");
                            document.getElementById("from").value = $fromdate1[0];
                            document.getElementById("to").value = $enddate1[0];
                            break;

                        case 'q3':
                            $fromdate = $syear + "-10-01 00:00:00";
                            $enddate = $syear + "-12-31 23:59:59";
                            $fromdate1 = $fromdate.split(" ");
                            $enddate1 = $enddate.split(" ");
                            document.getElementById("from").value = $fromdate1[0];
                            document.getElementById("to").value = $enddate1[0];
                            break;

                        case 'q4':

                            $fromdate = $eyear + "-01-01 00:00:00";
                            $enddate = $eyear + "-03-31 23:59:59";
                            $fromdate1 = $fromdate.split(" ");
                            $enddate1 = $enddate.split(" ");
                            document.getElementById("from").value = $fromdate1[0];
                            document.getElementById("to").value = $enddate1[0];
                            break;



                    }
                } else {
                    if (yr != 'fd' && qtr == 'd') {
                        alert('Please Select Quarter');
        //                    if(a){
        //                    window.location.reload();}
                    } else if (qtr != 'd' && yr == 'fd') {
                        alert('Please Select Year');
                    }
        //                    else{    alert('Please Select Year And Quarter First');
        //                    }
                }

                var storrr = $("#store").val();
                var yrs = $("#yrtype").val();// var qtr= $( "#atype" ).val();
                if (storrr != "" && yrs != 'fd') {
                    // alert('store is already set');
                    setIncentives(storrr);
                }


            }





            function setIncentives(storeid) {
                //   alert(storeid);//atype1
                var yr = $("#yrtype").val();
                var qtr = $("#atype").val();
                var fromdate = $("#from").val();
                var enddate = $("#to").val();
                if (fromdate != '' && enddate != '' && qtr != 'd') {
                    //alert(fromdate);
                    //alert(enddate);  
                    $('#sman option:selected').removeAttr('selected');
                    $('#sincentive option:selected').removeAttr('selected');
                    var ajaxURL = "ajax/incentinformation.php?storeid=" + storeid + "&fromdate=" + fromdate + "&enddate=" + enddate;
                    //alert(ajaxURL);
                    $.ajax({
                        url: ajaxURL,
                        //dataType: 'json',
                        cache: false,
                        success: function (html) {
                            // alert(html);
                            var resdata = html;
                            $result = resdata.split("<=====>");

                            var len = $result.length;  //Calcluate length of the array from ajax responce
                            //alert(len);
                            if (len == 2) {
                                $('#sman').empty().append('<option value="" >Select SalesMan incentive</option>');
                                $('#sincentive').empty().append('<option value="" >Select Store Incentive</option>');
                                $('#sman').append($result[0]);
                                $('#sincentive').append($result[1]);
                            } else {
                                alert("Records Are Not Available For This Selected Quarter");

                                $('#sman').empty().append('<option value="" >Select Salesman incentive</option>');
                                $('#sincentive').empty().append('<option value="" >Select Store Incentive</option>');
                                $('#sman').append($result[1]);
                                $('#sincentive').append($result[2]);
                            }
                        }
                    });
                } else {

                    // alert('Please Select Year And Quarter');
                    if (yr != 'fd' && qtr == 'd') {
                        alert('Please Select Quarter To Show Store Records');
                        window.location.reload();
                    } else if (qtr != 'd' && yr == 'fd') {
                        alert('Please Select Year');
                    } else if (qtr == 'd' && yr == 'fd') {
                        alert('Please Select Year And Quarter First');
                        window.location.reload();
                    }
                }
            }

            //JS SCRIPT CODE FOR DOWNLOAD RECORDS START

            function setyr1(yr) {
                // alert(yr);
                var qtr = $("#atype1").val();
                if (qtr != 'd') {
                    //alert('qtr is already set');
                    setQtr1(qtr);//set date against qtr if qtr is set
                }
            }

            function setQtr1(qtr1) {
                // alert(qtr1);
                var years = $("#yrtype1").val();// $dat = $msg.split("[");

                if (qtr1 != 'd' && years != 'fd') {
                    $yearsssss = years;
                    //alert($yearsssss);
                    $yearset = $yearsssss.split("-");
                    $syear = $yearset[0];
                    $eyear = $yearset[1];

                    switch (qtr1) {
                        case 'q1':
                            $fromdate = $syear + "-04-01 00:00:00";
                            $enddate = $syear + "-06-30 23:59:59";
                            $fromdate1 = $fromdate.split(" ");
                            $enddate1 = $enddate.split(" ");
                            document.getElementById("dateselect").value = $fromdate1[0] + " - " + $enddate1[0];
                            break;

                        case 'q2':
                            $fromdate = $syear + "-07-01 00:00:00";
                            $enddate = $syear + "-09-30 23:59:59";
                            $fromdate1 = $fromdate.split(" ");
                            $enddate1 = $enddate.split(" ");
                            document.getElementById("dateselect").value = $fromdate1[0] + " - " + $enddate1[0];
                            break;

                        case 'q3':
                            $fromdate = $syear + "-10-01 00:00:00";
                            $enddate = $syear + "-12-31 23:59:59";
                            $fromdate1 = $fromdate.split(" ");
                            $enddate1 = $enddate.split(" ");
                            document.getElementById("dateselect").value = $fromdate1[0] + " - " + $enddate1[0];
                            break;

                        case 'q4':
                            $fromdate = $eyear + "-01-01 00:00:00";
                            $enddate = $eyear + "-03-31 23:59:59";
                            $fromdate1 = $fromdate.split(" ");
                            $enddate1 = $enddate.split(" ");
                            document.getElementById("dateselect").value = $fromdate1[0] + " - " + $enddate1[0];
                            break;
                    }
                } else {
                    // alert('Select Year First')
                    if (years != 'fd' && qtr1 == 'd') {
                        alert('Please Select Quarter');
                    } else if (qtr1 != 'd' && years == 'fd') {
                        alert('Please Select Year');
                    } else {
                        alert('Please Select Year And Quarter First');
                    }
                }

            }


            function genReport() {

                var storeids = $("#selstore").val();  //SET STORE ID TO PARAMS    
                var dtrange = $("#dateselect").val();//SET DATE TO PARAMS
                var yrtypes = $("#yrtype1").val();// SET YR TO PARAMS
                var atypes = $("#atype1").val();//SET QUATER TO PARAMS
                if (atypes != 'd' && yrtypes != 'fd') {
                    window.location.href = "sales/incentive/storeids=" + storeids + "/dtrange=" + dtrange + "/yeartpes=" + yrtypes + "/qtrtype=" + atypes;
                } else {
                    if (yrtypes != 'fd' && atypes == 'd') {
                        alert('Please Select Quarter');
                    } else if (atypes != 'd' && yrtypes == 'fd') {
                        alert('Please Select Year');
                    } else {
                        alert('Please Select Year And Quarter First');
                    }
                    //alert("Please Select Year And Quarter To Generate Reports");
                }

            }

            function genRepExcel() {
                //alert('hii');
                var storeids = $("#selstore").val();  //SET STORE ID TO PARAMS  
                var dtrange = $("#dateselect").val();//SET DATE TO PARAMS
                var yrtypes = $("#yrtype1").val();// SET YR TO PARAMS
                var atypes = $("#atype1").val();//SET QUATER TO PARAMS
                //var remark = $("#remark").val();
                // alert(storeids);
                //alert(dtrange);
                if (atypes != 'd' && yrtypes != 'fd') {
                    //alert('hii');
                    window.location.href = "formpost/genSalesAndStoreIncentive.php?storeids=" + storeids + "&dtrange=" + dtrange + "&atypes=" + atypes + "&yrtypes=" + yrtypes+ "&remark=" + remark;
                } else {

                    if (yrtypes != 'fd' && atypes == 'd') {
                        alert('Please Select Quarter');
                    } else if (atypes != 'd' && yrtypes == 'fd') {
                        alert('Please Select Year');
                    } else {
                        alert('Please Select Year And Quarter First');
                    }

                    //alert("Please Select Year And Quarter To Generate Excel Sheet");
                }

            }


        </script>


        <?php
    }

    public function pageContent() {
        $currUser = getCurrUser();
        $menuitem = "salesrepresentive";
        include "sidemenu." . $this->currUser->usertype . ".php";
        $formResult = $this->getFormResult();
        $db = new DBConn();
        ?>

        <?php
        $display = "none";
        $num = 0;
        ?>

        <?php if ($this->currUser->usertype == UserType::Manager) { ?>
            <?php
            //Sales Login  Code
//            if (date('m') > 3) {
//            $current_financial_year = date('Y') . '-' . (date('Y')+1);    /// current finacial year calculation start
//           // echo 'curreny financial year'.$current_financial_year;
//            //echo'</br>';
//            $prev_financial_year = (date('Y')-1) . '-' . date('Y');   // Previous  finacial year calculation start
//            //echo 'previous financial year'.$prev_financial_year;
//            }
            if (date('m') > 3) {
                $current_financial_year = date('Y') . '-' . (date('Y') + 1);    /// current finacial year calculation start
                // echo 'current fy'.$current_financial_year;
                $prev_financial_year = (date('Y') - 1) . '-' . date('Y');   // Previous  finacial year calculation start
                //echo 'previous fy'.$prev_financial_year;
            } else {
                // echo 'hello';
                $current_financial_year = (date('Y') - 1) . '-' . (date('Y'));    /// current finacial year calculation start
                // echo 'current fy'.$current_financial_year;
                $prev_financial_year = (date('Y') - 2) . '-' . (date('Y') - 1);   // Previous  finacial year calculation start
                //echo 'previous fy'.$prev_financial_year;
            }
            ?>
            <div class="grid_10">
                <div class="box" style="clear:both;">
                    <fieldset class="login">
                        <b>For Sales Login</b>
                        <div class="grid_17">
                            <div class="grid_17" >
                                <fieldset class="login">
                                    <div class="grid_8">

                                        <div class="grid_2" >  <b>Select Year:</b>

                                            <select name="yrtype" id="yrtype" onchange="yearlablehide();" >
            <?php
            $atypes = array(
                "fd" => "Select Year",
                "$current_financial_year" => "$current_financial_year",
                "$prev_financial_year" => "$prev_financial_year");
            $form_atype = $this->getFieldValue('yrtype');
            foreach ($atypes as $avalue => $yrtype) {
                $selected = "";
                if ($avalue == $form_atype) {
                    $selected = "selected";
                }
                ?>
                                                    <option value="<?php echo $avalue; ?>" <?php echo $selected; ?> ><?php echo $yrtype; ?></option>
                                                <?php } ?>
                                            </select>

                                            </br>
                                            <br/>
                                        </div>
                                        <div class="grid_1"><label><h3 style="color:#FF0000;"><span id="yearlabel" style="display:none;"   >*</span></h3> </label>     

                                        </div>

                                        <div class="grid_3">  <b>Select Quarter:</b>
                                            <br/>
                                            <select name="atype" id="atype" onchange="setQtr_sales(this.value);qtrlablehide();">
            <?php
            $atypes = array(
                "d" => "Select Quarter",
                "q1" => "Quarter 1",
                "q2" => "Quarter 2",
                "q3" => "Quarter 3",
                "q4" => "Quarter 4");

            $form_atype = $this->getFieldValue('atype');
            foreach ($atypes as $avalue => $atype) {
                $selected = "";
                if ($avalue == $form_atype) {
                    $selected = "selected";
                }
                ?>
                                                    <option value="<?php echo $avalue; ?>" <?php echo $selected; ?> ><?php echo $atype; ?></option>
                                                <?php } ?>
                                            </select>
                                            </br>
                                        </div > 
                                        <div class="grid_1"><label><h3 style="color:#FF0000;"><span id="qtrlabel" style="display:none;"   >*</span></h3> </label>     

                                        </div>
                                    </div> 

                                    <div class="grid_12">
                                        <div class="grid_4">  
                                            <h5>Select Store : </h5>
                                            <select id="store" name="store" data-placeholder="Choose Store" class="chzn-select" multiple style="width:100%"  onchange="storelablehide();">
                                                <option value="0">Select Store</option>

            <?php
            //  $query = "select * from it_codes where usertype = ".UserType::Dealer;
            $query = "select * from it_codes where usertype = 4 order by store_name";
            $objs = $db->fetchObjectArray($query);
            $sarr = explode(",", $this->storeids);
            foreach ($objs as $obj) {
                if (in_array($obj->id, $sarr)) {
                    $selected = "selected";
                } else {
                    $selected = "";
                }
                ?>

                                                    <option value="<?php echo $obj->id; ?>" <?php echo $selected; ?>><?php echo $obj->store_name; ?></option>
                                                <?php } ?>
                                            </select>
                                            </br>
                                            </br>
                                        </div>
                                        <div class="grid_8"><label><h3 style="color:#FF0000;"><span id="storelabel" style="display:none;"  >*</span></h3> </label>     

                                        </div>
                                    </div>

                                    <input type="hidden" name="user_id" id="user_id" value='<?php echo $this->userid; ?>'>
                                    <div >
                                        <input id="from" type="hidden" name="from" style ="width:20%" value=""/> &nbsp;&nbsp;&nbsp;&nbsp;                    
                                        <input id="to" type="hidden" name="to" style ="width:20%"  value=""/>
                                        </br>
                                    </div>

                                    <div class="grid_8">

                                        <div class="grid_12">
                                            <span style="font-weight:bold;">SalesMan Incentive : </span>
                                        </div>
            <?php
            $query = "select salesman_incentive from it_salesman_incen";
            $obj_Region = $db->fetchObjectArray($query);
            ?>
                                        <div class="grid_16" style="width:40.91%" >
                                            <div class="grid_16" style="width:86.69%">
                                                <select name="sman" id="sman" class="selectpicker form-control" data-show-subtext="true" data-live-search="true" onchange="saleslablehide();" required>
                                                    <option  value="">Select Salesman Incentive</option>
                                        <?php
                                        foreach ($obj_Region as $region) {
                                            ?>
                                                        <option  value="<?php echo $region->salesman_incentive; ?>" ><?php echo $region->salesman_incentive; ?></option>
            <?php } ?>
                                                </select>       

                                            </div>
                                            <div class="grid_1"><label><h3 style="color:#FF0000;"><span id="salesinclabel" style="display:none;"  >*</span></h3> </label>     

                                            </div>
                                        </div>     
                                        </br>
                                        </br>
                                        </br>
                                        <div class="grid_12">
                                            <span size="20" style="font-weight:bold;">Store Incentive : </span>
                                        </div>
            <?php
            $query = "select store_incentive from it_store_incen";
            $obj_Region = $db->fetchObjectArray($query);
            ?>
                                        <div class="grid_16" style="width:35%" >
                                            <div class="grid_16" style="width:89.69%">
                                                <select name="sincentive" id="sincentive" class="selectpicker form-control" data-show-subtext="true" data-live-search="true" onchange="storeinclablehide();" required>
                                                    <option  value="">Select Store Incentive</option>
                                        <?php
                                        foreach ($obj_Region as $region) {
                                            ?>
                                                        <option value="<?php echo $region->store_incentive; ?>" ><?php echo $region->store_incentive; ?></option>
            <?php } ?>
                                                </select>     
                                                </br> 
                                                </br>   
                                            </div>
                                            <div class="grid_1"><label><h3 style="color:#FF0000;"><span id="storesinclabel"  style="display:none;" >*</span></h3> </label>     

                                            </div>
                                        </div>  
                                    </div>
                                    <div class="grid_12">
                                        <div class="grid_4">
                                            <div class="grid_12" style="font-weight:bold;">Remark :</div>
                                           <div class ="grid_12"><textarea cols="40" rows="3" id="remark" name="remark" value="<?php echo $this->remark; ?>"></textarea>&nbsp;&nbsp;
                                            <input type="button" style="background-color:#34de63;" name="insersave" id="insersave" value="Save" onclick="insertinc();">
                                        </div>
                                    </div>  

            <?php if ($formResult) { ?>
                                        <div class="grid_12">
                                            <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
                                        </div>
            <?php }
            ?>

                                    <br><br>
                                    <br><br>
                                    <br><br>
                                    <br><br>
                                    <br><br>
                                    <br>


                                </fieldset>
                            </div>  
                        </div> 

                    </fieldset>
                </div>  
            </div>  
        <?php } else if ($this->currUser->usertype == UserType::CKAdmin) {
            ?>


            <?php
            //director   ||$this->currUser->usertype == UserType::Admin
//            if (date('m') > 3) {
//            $current_financial_year = date('Y') . '-' . (date('Y')+1);    /// current finacial year calculation start
//           // echo 'curreny financial year'.$current_financial_year;
//            //echo'</br>';
//            $prev_financial_year = (date('Y')-1) . '-' . date('Y');   // Previous  finacial year calculation start
//            //echo 'previous financial year'.$prev_financial_year;
//            }
            if (date('m') > 3) {
                $current_financial_year = date('Y') . '-' . (date('Y') + 1);    /// current finacial year calculation start
                // echo 'current fy'.$current_financial_year;
                $prev_financial_year = (date('Y') - 1) . '-' . date('Y');   // Previous  finacial year calculation start
                //echo 'previous fy'.$prev_financial_year;
            } else {
                // echo 'hello';
                $current_financial_year = (date('Y') - 1) . '-' . (date('Y'));    /// current finacial year calculation start
                // echo 'current fy'.$current_financial_year;
                $prev_financial_year = (date('Y') - 2) . '-' . (date('Y') - 1);   // Previous  finacial year calculation start
                //echo 'previous fy'.$prev_financial_year;
            }
            ?>
            <div class="grid_10">
                <div class="box" style="clear:both;">
                    <fieldset class="login">
                        <b>for director login</b>
                        </br>
                        <div class="grid_12">  
                            <input type="radio" id="cn" name="cn" value="1" <?php if ($this->id == 1) { ?>checked <?php } ?>>Edit/Update
                            <input type="radio" id="cn1" name="cn" value="2" <?php if ($this->id == 2) { ?>checked <?php } ?>>Download Incentive Report
                        </div>

            <?php if ($this->id == 1) { ?>

                            <form action="formpost/directorRepresentiveIncentive.php" method="post">
                                <div class="grid_4">     
                                    <br/>  

                                    <div class="grid_12"> 
                                        <div class="grid_6">  <b>Select Year:</b>
                                            <br/>
                                            <select name="yrtype" id="yrtype" onchange="setyr(this.value);">
                            <?php
                            $atypes = array(
                                "fd" => "Select Year",
                                "$current_financial_year" => "$current_financial_year",
                                "$prev_financial_year" => "$prev_financial_year");
                            $form_atype = $this->getFieldValue('yrtype');
                            foreach ($atypes as $avalue => $yrtype) {
                                $selected = "";
                                if ($avalue == $this->yeartpess) {
                                    $selected = "selected";
                                }
                                ?>
                                                    <option value="<?php echo $avalue; ?>" <?php echo $selected; ?> ><?php echo $yrtype; ?></option>
                                                <?php } ?>
                                            </select>

                                            </br>
                                            <br/>
                                        </div > 
                                        <div class="grid_6">  <b>Select Quarter:</b>
                                            <br/>
                                            <select name="atype" id="atype" onchange="setQtr(this.value);">
                <?php
                $atypes = array(
                    "d" => "Select Quarter",
                    "q1" => "Quarter 1",
                    "q2" => "Quarter 2",
                    "q3" => "Quarter 3",
                    "q4" => "Quarter 4");

                $form_atype = $this->getFieldValue('atype');
                foreach ($atypes as $avalue => $atype) {
                    $selected = "";
                    if ($avalue == $form_atype) {
                        $selected = "selected";
                    }
                    ?>
                                                    <option value="<?php echo $avalue; ?>" <?php echo $selected; ?> ><?php echo $atype; ?></option>
                                                <?php } ?>
                                            </select>

                                            </br>
                                            <br/>
                                        </div > 
                                    </div>

                                    <b>Select Store:</b>
                                    <br/>
                                    <select name="store" id="store" data-placeholder="Choose Store" class="chzn-select"  style="width:100%;" onchange="setIncentives(this.value);" >
                                        <option  value="" >Select Stores</option>
                <?php
                $objs = $db->fetchObjectArray("select * from it_codes where usertype=4 order by store_name");

                if ($this->storeidreport == "-1") {
                    
                } else {
                    $storeid = explode(",", $this->storeidreport);
                }

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

                                </br>
                                </br>
                                </br>
                                </br>
                                <input type="hidden" name="user_id" value='<?php echo $this->userid; ?>'>
                                <div >
                                    <br/>   
                                    <br/>                                         
                                    <input id="from" type="hidden" name="from" style ="width:20%" value=""/> &nbsp;&nbsp;&nbsp;&nbsp;                    
                                    <input id="to" type="hidden" name="to" style ="width:20%"  value=""/>
                                </div>

                                <div class="grid_12">
                                    </br>
                                    <span style="font-weight:bold;">SalesMan Incentive : </span>
                                </div>
                                <!--<input type="text" name="sman" value=''>-->

                                <p class="grid_12" style="width:25%">
                                    <select name="sman" id="sman" class="selectpicker form-control" data-show-subtext="true" data-live-search="true" required>
                                        <option  value="">Select Salesman Incentive</option>
                                    </select>       
                                </p>

                                </br>
                                </br>
                                </br>
                                <div class="grid_12">
                                    <span size="20" style="font-weight:bold;">Store Incentive : </span>
                                </div>
                                <p class="grid_12" style="width:25%">
                                    <select name="sincentive" id="sincentive" class="selectpicker form-control" data-show-subtext="true" data-live-search="true" required>
                                        <option  value="">Select Store Incentive</option>

                                    </select>       
                                </p>


                                      <div class="grid_12" style="font-weight:bold;">Remark :</div>
                                       <div class ="grid_12"><textarea cols="40" rows="3" id="remark" name="remark" value="<?php echo $this->remark; ?>"></textarea>&nbsp;&nbsp;
                                   </div>
                                <div class="grid_12" style="padding:10px;" id="resp">
                                    <input type="submit" name="ssave" id="addattr" value="Update" style="background-color:#34de63;"/>

                <?php if ($formResult) { ?>
                                        <p>
                                            <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
                                        </p>
                                    <?php }
                                    ?>

                                </div>
                            </form>

                                <?php } elseif ($this->id == 2) {
                                    ?>

                            <div class="grid_17">
                                <div class="grid_17" >
                                    <fieldset class="login">	
                                        <div class="grid_12">
                                            <div class="grid_4">  
                                                <h5>Select Store : </h5>
                                                <select id="selstore" name="selstore" data-placeholder="Choose Store" class="chzn-select" multiple style="width:100%" >
                                                    <option value="0">Select</option>
                <?php
                if (trim($this->storeids == "-1")) {
                    $str = "selected";
                } else {
                    $str = "";
                }
                ?>
                                                    <option value="-1" <?php echo $str; ?>>ALL</option>
                                                    <?php
                                                    // $query = "select * from it_codes where usertype = ".UserType::Dealer;
                                                    $query = "select * from it_codes where usertype = 4 order by store_name";
                                                    $objs = $db->fetchObjectArray($query);
                                                    $sarr = explode(",", $this->storeids);
                                                    foreach ($objs as $obj) {
                                                        if (in_array($obj->id, $sarr)) {
                                                            $selected = "selected";
                                                        } else {
                                                            $selected = "";
                                                        }
                                                        ?>
                                                        <option value="<?php echo $obj->id; ?>" <?php echo $selected; ?>><?php echo $obj->store_name; ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="grid_8">
                                            <br/>
                                            <div class="grid_3">  <b>Select Year:</b>
                                                <br/>
                                                <select name="yrtype1" id="yrtype1" onchange="setyr1(this.value);" >
                                                    <?php
                                                    $atypes = array(
                                                        "fd" => "Select Year",
                                                        "$current_financial_year" => "$current_financial_year",
                                                        "$prev_financial_year" => "$prev_financial_year");
                                                    $form_atype = $this->getFieldValue('yrtype1');
                                                    foreach ($atypes as $avalue => $yrtype1) {
                                                        $selected = "";
                                                        if ($avalue == $this->yeartpess) {
                                                            $selected = "selected";
                                                        }
                                                        ?>
                                                        <option value="<?php echo $avalue; ?>" <?php echo $selected; ?> ><?php echo $yrtype1; ?></option>
                <?php } ?>
                                                </select>

                                                </br>
                                                <br/>
                                            </div > 
                                            <div class="grid_4">  <b>Select Quarter:</b>
                                                <br/>
                                                <select name="atype1" id="atype1" onchange="setQtr1(this.value);">
                                                    <?php
                                                    $atypes = array(
                                                        "d" => "Select Quarter",
                                                        "q1" => "Quarter 1",
                                                        "q2" => "Quarter 2",
                                                        "q3" => "Quarter 3",
                                                        "q4" => "Quarter 4");

                                                    $form_atype = $this->getFieldValue('atype1');
                                                    foreach ($atypes as $avalue => $atype1) {
                                                        $selected = "";
                                                        if ($avalue == $this->qtrtypess) {
                                                            $selected = "selected";
                                                        }
                                                        ?>
                                                        <option value="<?php echo $avalue; ?>" <?php echo $selected; ?> ><?php echo $atype1; ?></option>
                <?php } ?>
                                                </select>

                                                </br>
                                                <br/>
                                            </div > 
                                        </div>    

                                        <div class="grid_7">
                                            <div class="grid_4">
                                                <input type="button" style="background-color:#34de63;" name="genRep1" id="genRep1" value="Generate Report" onclick="genReport()">
                                            </div>
                                            <div class="grid_4">
                                                <input type="button" name="genRepExcel1" style="background-color:#34de63;" id="genRepExcel1" value="Download Excel" onclick="genRepExcel()">
                                            </div>
                                            <div>
                                                </br> <input size="17" type="hidden" id="dateselect" name="dateselect" value="<?php if (isset($this->dtrange)) {
                    echo $this->dtrange;
                } ?> " />
                                            </div>
                                        </div>  

                                        <br><br>
                                        <br><br>
                                        <br><br>
                                        <br><br>
                                        <br><br>
                                        <br>



                                        <?php
                                        if ($this->storeids != null && $this->dtrange != null) {
                                            // echo  'hiii';
                                            $dtarr = explode(" - ", $this->dtrange);
                                            // print_r($dtarr);
                                            //$_SESSION['storeid'] = $this->storeidreport;
                                            if (count($dtarr) == 1) {
                                                list($dd, $mm, $yy) = explode("-", $dtarr[0]);
                                                $sdate = "$yy-$mm-$dd";
                                                $dQuery = "si.start_date >= '$sdate 00:00:00' and si.end_date <= '$sdate 23:59:59' ";
                                            } else if (count($dtarr) == 2) {
                                                //  echo 'arrr2';
                                                //list($dd,$mm,$yy) = explode("-",$dtarr[0]);
                                                $sdate = "$dtarr[0]";
                                                //list($dd,$mm,$yy) = explode("-",$dtarr[1]);
                                                $edate = "$dtarr[1]";
                                                $dQuery = "si.start_date >= '$sdate 00:00:00' and si.end_date <= '$edate 23:59:59' ";
                                            } else {
                                                $dQuery = "";
                                            }


                                            $sClause = "";
                                            //  if($this->storeids!=-1){
                                            if ($this->storeids != null && trim($this->storeids) != "-1") {
                                                $sClause = "si.store_id in ($this->storeids) and ";
                                            } else {
                                                $sClause = "";
                                            }
                                        }


                                        $table = "<table>
                <tr><th width=\"100%\"  colspan=20 align=\"center\">Incentive Reports</th></tr>
                <tr>
                <th width=\"10px\">Sr. No</th>
                <th>Store id </th>
                <th>Store Name</th>   
                <th>Quarter</th>
                <th>Sales Representive Name</th>
                <th>SalesMan Incentive</th>
                <th>Store Incentive</th>
                <th>Dated</th>
                <th>Remark</th>
                </tr>";

                                        if ($this->storeids != null && $this->dtrange != null) {
                                            $db = new DBConn();
                                            $query = "select * from it_sales_incentive si where  $sClause $dQuery ";
                                            // print "$query";
                                            $orders = $db->fetchObjectArray($query);
                                            // print_r($orders);
                                            $count = 0;
                                            foreach ($orders as $order) {
                                                $count++;
                                                if (isset($order->createtime)) {
                                                    $createtime = mmddyy($order->createtime);
                                                } else {
                                                    $createtime = " - ";
                                                }
                                                //$storenamequery="select tally_name from it_codes where id=$order->store_id";
                                                //$storename=$db->fetchObject($storenamequery);
                                                $table .= "<tr>
                            <td>$count</td>
                            <td>$order->store_id</td>
                            <td>$order->store_name</td>
                            <td>$order->quarter</td>
                            <td>$order->createdby_name</td>
                            <td>$order->salesman_incentive</td>
                            <td>$order->store_incentive</td>
                           <td>$createtime</td>
                            <td>$order->remark</td>";
                                            }
                                        }
                                        $table .= "

                    </table>";
                                        echo $table;
                                        ?>
                                    </fieldset>
                                </div>  
                            </div>  


            <?php } ?>
                    </fieldset>
                </div>
            </div>
        <?php } ?>
        <?php
    }

}
?>