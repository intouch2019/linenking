<?php
ini_set('max_execution_time', 300);

require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "session_check.php";
require_once "lib/core/strutil.php";

class cls_viewedit_creditpoint extends cls_renderer {

    var $params;
    var $desig;
    var $currStore;
    var $storeidreport = null;
    var $storeid;
    var $dtrange;
    var $categoryid;

    function __construct($params = null) {
        $this->currStore = getCurrUser();

        if (isset($_SESSION['account_dtrange'])) {
            $this->dtrange = $_SESSION['account_dtrange'];
        } else {
            $this->dtrange = date("d-m-Y");
        }

        if (isset($params['sid'])) {
            $this->storeid = $params['sid'];
        }

        if ($params && isset($params['category'])) {
            $this->categoryid = $params['category'];
        }

        //echo $this->storeid;
        //       exit();
    }

    function extraHeaders() {
        if (!$this->currStore) {
            return;
        }
        ?>
        <script type="text/javascript" src="jqueryui/js/jquery-ui-1.7.1.custom.min.js"></script>
        <script type="text/javascript" src="js/daterangepicker.jQuery.js"></script>
        <link rel="stylesheet" href="css/ui.daterangepicker.css" type="text/css" />
        <link rel="stylesheet" href="css/redmond/jquery-ui-1.7.1.custom.css" type="text/css" title="ui-theme" />


        <link rel="stylesheet" href="js/chosen/chosen.css" />
        <script src="js/chosen/chosen.jquery.js" type="text/javascript"></script>
        <link rel="stylesheet" href="css/prettyPhoto.css" type="text/css" media="screen" title="prettyPhoto main stylesheet" charset="utf-8" />
        <link rel="stylesheet" type="text/css" href="css/dark-glass/sidebar.css" />
        <script src="js/prettyPhoto/jquery.prettyPhoto.js" type="text/javascript" charset="utf-8"></script>
        <script type="text/javascript" src="js/sidebar/jquery.sidebar.js"></script>
        <script type="text/javascript">


            $(function () {
                $("a[rel^='prettyPhoto']").prettyPhoto({animation_speed: 'fast', slideshow: 3000, hideflash: true});
            });

            $(function () {
                $(".chzn-select").chosen();
                $(".chzn-select-deselect").chosen({allow_single_deselect: true});

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
                                //				window.location.reload();reloadreport(-1);
                                var storeid = $('#store').val();
                                //                        window.location.href="arorder/details/sid="+storeid;


                            }
                        });
                    }
                });

                $("ul#demo_menu1").sidebar({
                    width: 160,
                    height: 110,
                    injectWidth: 50,
                    events: {
                        item: {
                            enter: function () {
                                $(this).find("a").animate({color: "red"}, 250);
                            },
                            leave: function () {
                                $(this).find("a").animate({color: "white"}, 250);
                            }
                        }
                    }
                });




            });




            //          function reloadreport() {  
            //        var storeid = $('#store').val();
            //                        window.location.href="arorder/details/sid="+storeid;
            //                                 setfocus();
            //    }


            function genReport() {

                //                var category = $('#category').val();
                //alert(category);
                var storeid = $('#store').val();
                //  alert(storeid);
                var dtrange = $("#dateselect").val();//SET DATE TO PARAMS

                // alert(dtrange);
                if (storeid != 0 && dtrange != "") {
                    window.location.href = "viewedit/creditpoint/sid=" + storeid + "/dtrange=" + dtrange;
                    setfocus();
                } else {
                    alert('Please Fill All Values');

                    if (storeid == 0) {
                        document.getElementById('storelabel').style.display = 'inline';
                    }
                    if (category == 0) {
                        document.getElementById('catlabel').style.display = 'inline';
                    }
                    if (dtrange == "") {
                        // alert('hii');
                        document.getElementById('datelabel').style.display = 'inline';
                    }

                }



            }

            function is_confirm(num, store_name)
            {
                // var num   
                var r = confirm("Are you sure to remove Credit Points=" + num + " and Store Name=" + store_name + "  ?");
                if (r == true)
                {
                    return true;
                } else
                {
                    return false;
                }
            }

            function storelablehide() {


                document.getElementById('storelabel').style.display = 'none';
            }
            function catlablehide() {


                document.getElementById('catlabel').style.display = 'none';
            }
            function datelabelhide()
            {
                document.getElementById('datelabel').style.display = 'none';
            }

            function showPrevCP() {
                $('#newCP').hide();
                $('#prevCP').show();
            }

            function showNewCP() {
                $('#prevCP').hide();
                $('#newCP').show();
            }


        </script>

        <?php
    }

    //extra-headers close
    public function pageContent() {

        $menuitem = "vieweditcreditpoint";
        include "sidemenu." . $this->currStore->usertype . ".php";
        $sdate = "";
        $edate = "";
        $write_htm = true;
//         echo $this->currStore->usertype;
//                    print_r( $this->currStore);
//                    exit();
        ?>
        <div class="grid_10">
            <div class="box" style="clear:both;">
                <fieldset class="login">
                    <legend>Generate Viewedit Creditpoint Report</legend>

                    <h3>
                        <div class="grid_10" style="float:left">View/Edit Creditpoint</div><br>
                    </h3>
                    <div class="grid_3">

                        <b>Select Store*:</b><br/>
                        <span style="font-weight:bold;">

                            <select name="store" id="store" data-placeholder="Choose Store" class="chzn-select" style="width:100%;" onchange="storelablehide()">
                                <option value="0">Select store</option>  
                                <?php
                                if ($this->storeid == - 1) {
                                    $defaultSel = "selected";
                                } else {
                                    $defaultSel = "";
                                }




                                if ($this->currStore->usertype == UserType::Admin || $this->currStore->usertype == UserType::CKAdmin || $this->currStore->usertype == UserType::Manager || $this->currStore->usertype == UserType::Accounts || $this->currStore->usertype == UserType::BHMAcountant) {
                                    ?>
                                    <option value="-1" <?php echo $defaultSel; ?>>All Stores</option> 


                                <?php } ?>





                                <?php
                                if ($this->currStore->usertype == UserType::Admin || $this->currStore->usertype == UserType::CKAdmin || $this->currStore->usertype == UserType::Manager || $this->currStore->usertype == UserType::Accounts || $this->currStore->usertype == UserType::BHMAcountant) {
                                    $objs = $db->fetchObjectArray("select * from it_codes where usertype=4 order by store_name");
                                } else {
                                    $objs = $db->fetchObjectArray("select * from it_codes where id=" . $this->currStore->id);
                                }

                                foreach ($objs as $obj) {
                                    $selected = "";
                                    //	if (isset($this->storeidreport) && $obj->id==$this->storeidreport) { $selected = "selected"; }
                                    if ($this->storeid != - 1) {
                                        if ($obj->id == $this->storeid) {
                                            $selected = "selected";
                                        }
                                    }
                                    ?>
                                    <option value="<?php echo $obj->id; ?>" <?php echo $selected; ?>><?php echo $obj->store_name; ?></option> 
                                <?php }
                                ?>
                            </select>

                        </span>

                    </div>

                    <div class="grid_5">
                        <span style="font-weight:bold;">Date Filter : </span></br> <input size="17" type="text" id="dateselect" name="dateselect" value="<?php echo $this->dtrange; ?>" onclick="datelabelhide()" onchange="datelabelhide()"/> (Click to see date options)
                    </div>

                    <div class="grid_8">
                        <div class="grid_5" ><label><h5 style="color:#FF0000;" ><span id="storelabel" style="display:none;"  >please select store</span></h5> </label>     

                        </div>
                        <div class="grid_3"><label><h5 style="color:#FF0000;" ><span id="datelabel" style="display:none;"  >please select Date</span></h5> </label>     

                        </div>
                    </div>



                    <input type="radio" name="cpView" id="ccp" value="Current Credit Points" onclick="showNewCP()" checked>
                    <label for="ccp">Current Credit Points</label>
<!--                    <input type="radio" name="cpView" id="pcp" value="Previous Credit Points" onclick="showPrevCP()">
                    <label for="pcp">Previous Credit Points</label><br>-->



                    <div class="grid_5">
                        <input type="button"   name="genRep1" id="genRep1" value="Generate Report" onclick="genReport()">

                    </div>
                </fieldset>
            </div> <!-- class=box -->

            <?php
            if ($this->storeid != null && $this->dtrange != null) { //22 fields
                ?>



                <?php
                $currUri = $_SERVER["REQUEST_URI"];
                $cart_page = true;
                // include "cartinfo.php";
                $db = new DBConn();
                // $storeid = getCurrUserId();

                $newfname = "";
                $dtarr = explode(" - ", $this->dtrange);
                $_SESSION['storeid'] = $this->storeidreport;
                if (count($dtarr) == 1) {
                    list($dd, $mm, $yy) = explode("-", $dtarr[0]);
                    $sdate = "$yy-$mm-$dd";
                    $edate = "$yy-$mm-$dd";
                    //  $dQuery = " and o.bill_datetime >= '$sdate 00:00:00' and o.bill_datetime <= '$edate 23:59:59' ";
//                $dQuery = "  r.points_upload_date >= '$sdate 00:00:00' and r.points_upload_date <= '$edate 23:59:59' ";
                    $dQuery = "   r.points_upload_date <= '$edate 23:59:59' ";
                    $newfname = "CreditPointReports_" . $sdate . "_" . $edate . ".csv";

                    if ($edate == date("Y-m-d")) {
                        $dQuery = "   r.points_upload_date <= '$edate 23:59:59'  and r.is_completely_used=0  ";
                    }
                } else if (count($dtarr) == 2) {
                    list($dd, $mm, $yy) = explode("-", $dtarr[0]);
                    $sdate = "$yy-$mm-$dd";
                    list($dd, $mm, $yy) = explode("-", $dtarr[1]);
                    $edate = "$yy-$mm-$dd";
                    // $dQuery = " and o.bill_datetime >= '$sdate 00:00:00' and o.bill_datetime <= '$edate 23:59:59' ";
                    $dQuery = "  r.points_upload_date >= '$sdate 00:00:00' and r.points_upload_date <= '$edate 23:59:59' ";
                    $newfname = "CreditPointReports_" . $sdate . "_" . $edate . ".csv";
                } else {
                    $dQuery = "";
                }
                //  print $dQuery;
                // print'</br  >';
                ///  print ddmmyy2($edate);


                $storeClause = "";
                if ($this->storeid == "-1") {
                    $storeClause = " and c.usertype = 4";
                } else {
                    $storeClause = " and c.id in ( $this->storeid ) ";
                }



                try {

                    //$storequery1 = "select c.id as store_id, c.store_name  from it_codes c,it_orders o,it_order_items oi where c.is_closed = 0 $storeClause $dQuery and c.id=o.store_id  and o.tickettype in (0,1,6) and oi.order_id = o.id  group by c.id ";
                    //  $storequery1 = "select r.*, c.store_name  from it_codes c,it_store_redeem_points r where  $dQuery  and r.store_id= $this->storeid and r.store_id=c.id ";
//
                    //
                    //
                    //
                    //   echo $storequery1;
                    //     $sobjs1 = $db->fetchObjectArray($storequery1);
                    //  echo "<br><br><br>";   
                    //print_r($sobjs1);
//                if (empty($sobjs1)) {
//                    echo '<span style="font-weight:bold; color:red;" co><label><h3>Records not Available For Selected Store.</h3></lable></span>';
//                }
                    //   if (isset($sobjs1) && !empty($sobjs1) && $sobjs1 != null) {
                    ?>



                    <!--for old uploaded credit points-->
                    <div class="grid_12" style="overflow-y: scroll; display:none;" id="prevCP">

                        <div class="grid_12" >
                            <div id="dwnloadbtn" style='margin-left:15px;  height:24px;width:130px;border: solid gray 1px;background:#F5F5F5;padding-top:4px;'>
                                <a href='<?php echo "tmp1/credit_point.php?output=$newfname"; ?>' title='Export table to CSV'><img src="images/excel.png" width='20' hspace='3' style='margin-bottom:-6px;' /> Export To Excel</a>
                            </div>
                        </div> 

                        <table style="width:100%" >
                            <tr>
                                <th colspan="18"  align="center" style="font-size:14px">View Edit Creditpoint</th>
                            </tr>

                            <tr>

                                <th>Sr.No.:</th>
                                <th>Store Name</th>
                                <th>Points </th>
                                <th>Points Upload Date</th>
                                <th>Is Redeem</th>
                                <th>Points Redeem Date</th>
                                <th>Redeem in (Invoice No)</th>
                                <th>Remark</th>
                                <?php if ($this->currStore->usertype == UserType::CKAdmin) { ?>
                                    <th> Action </th>
                                <?php } ?>
                <!--                            <th><table style="width:100%" border="0"><tr><th>CGST</th></tr><tr><th>Rate</th><th>Amount</th></tr></table><tbody  style="overflow-y: auto;height: 20px;overflow-x: hidden"></th>
                            <th><table style="width:100%"><tr><th>SGST</th></tr><tr><th>Rate</th><th>Amount</th></tr></table></th>
                            <th><table style="width:100%"><tr><th>IGST</th></tr><tr><th>Rate</th><th>Amount</th></tr></table></th>-->
                            </tr>

                            <?php
                            $i = 1;
                            if ($this->storeid == -1) {
                                $iquery = "select r.*, c.store_name  from it_codes c,it_store_redeem_points r where  $dQuery  and r.active=1 and r.store_id=c.id order by c.store_name ";
                            } else {

                                $iquery = "select r.*, c.store_name  from it_codes c,it_store_redeem_points r where  $dQuery  and r.active=1 and r.store_id= $this->storeid and r.store_id=c.id";
                            }
                            $items = $db->fetchObjectArray($iquery);

                            $totalPoints = 0;
                            foreach ($items as $obj) {
                                $totalPoints += $obj->points_to_upload;
                                ?>
                                <tr>
                                    <td><?php echo $i; ?></td>
                                    <td><?php echo $obj->store_name; ?></td>

                                    <td><?php echo $obj->points_to_upload; ?></td>
                                    <td><?php echo $obj->points_upload_date; ?></td>
                                    <?php if ($obj->is_reddeme == 0) { ?>
                                        <td>No</td>
                                        <td> - </td>
                                        <td> - </td>

                                    <?php } else { ?>
                                        <td>Yes</td>
                                        <td><?php echo $obj->points_redeemdate; ?></td>
                                        <td><?php echo $obj->invoice_no; ?></td>

                                    <?php } ?>

                                    <td><?php echo $obj->remark; ?></td>

                                    <?php if (($obj->is_reddeme == 0 ) && ($this->currStore->usertype == UserType::CKAdmin )) { ?>
                                        <td>

                                            <!--<form method="POST" action="">-->
                                            <form method="POST" action="formpost/viewEditCreditpoint.php">
                                                <input type="hidden" name="id" value='<?php echo $obj->id; ?>'>
                                                <input type="hidden" name="store_id" value='<?php echo $obj->store_id; ?>'>
                                                <input type="hidden" name="active" value='<?php echo $obj->active; ?>'>
                                                <input type="submit" style="background-color: #EC311B;   border: none;  color: white;   text-align: center; padding:5px; font-size: 14px; font-style: bold" 
                                                <?php if (isset($obj->is_redeem) && $obj->is_redeem == 1) {
                                                    echo "abcf";
                                                    ?> disabled <?php }; ?> value="Remove" onclick="return is_confirm('<?php echo $obj->points_to_upload; ?>', '<?php echo $obj->store_name; ?>')"></form>
                                        </td>
                                    <?php }//else{   ?>
                                <!--<td>-</td>-->
                    <?php //}   ?>

                                </tr>
                                <?php
                                $i++;
                            }
                            ?>
                            <tr><td></td><td><b>Total-</b></td><td><b><?php echo $totalPoints; ?></b></td><td></td><td></td><td></td><td></td><td></td></tr>
                            <?php
                            if ($this->storeid == -1) {
                                $iquery = "select  c.store_name ,r.points_to_upload,r.points_upload_date,if(r.is_reddeme =1,'Yes','No' ) as Is_Redeem,if(r.is_reddeme =1,r.points_redeemdate,'-' ) as points_redeemdate,if(r.is_reddeme =1,r.invoice_no,'-' ) as invoice_no,r.remark from it_codes c,it_store_redeem_points r where  $dQuery  and r.active=1 and r.store_id=c.id order by c.store_name ";
                            } else {

                                $iquery = "select c.store_name ,r.points_to_upload,r.points_upload_date,if(r.is_reddeme =1,'Yes','No' ) as Is_Redeem,if(r.is_reddeme =1,r.points_redeemdate,'-' ) as points_redeemdate,if(r.is_reddeme =1,r.invoice_no,'-' ) as invoice_no,r.remark from it_codes c,it_store_redeem_points r where  $dQuery  and r.active=1 and r.store_id= $this->storeid and r.store_id=c.id";
                            }
                            $items = $db->fetchObjectArray($iquery);

                            if (isset($items)) {


                                $fp = fopen('tmp1/CreditPointReports.csv', 'w');

                                if ($write_htm) {
                                    $fp2 = fopen('tmp1/CreditPointReports.htm', 'w');
                                }
                                if ($fp) {
                                    $trow = array();
                                    $tcell = array();
                                    //write header info   
                                    if ($write_htm) {
                                        fwrite($fp2, "<table width='100%' style='overflow:auto;'><thead><tr>");
                                    }

                                    $tableheaders = "Store Name:Points:Points Upload Date:Is Redeem:Points Redeem Date:Redeem in (Invoice No):Remark";

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
                                    foreach ($items as $order) {
                                        $tcell = null;
                                        if ($write_htm) {
                                            fwrite($fp2, "<tr>");
                                        }
                                        foreach ($order as $field => $value) {


//                    
//                   if ($field=="Store Name") {
//                       $value = $order;
//                   } else if($field == "date"){                                              
//                       $t_str = ddmmyy2($value);
//                       $value = $t_str;
//                   }



                                            $tcell[] .= trim($value);
                                            if ($write_htm) {
                                                fwrite($fp2, "<td>" . trim($value) . "</td>");
                                            }
                                        }
                                        fputcsv($fp, $tcell, ',', chr(0));
                                        if ($write_htm) {
                                            fwrite($fp2, "</tr>");
                                        }
                                    }
//            if($this->gen==1){
//                $totTotalValue=$totAmt;
                                    //    }
                                    if ($write_htm) {
                                        // fwrite($fp2,"<tr><td><b></b></td><td><b></b></td></tr>");
                                        fwrite($fp2, "");
                                        fwrite($fp2, "</tbody></table>");
                                        fclose($fp2);
                                    }
                                    fclose($fp);
                                    if ($write_htm) {
                                        $table = file_get_contents("tmp1/CreditPointReports.htm");
//                echo $table;
                                    }
                                } else {
                                    echo "<br/>Unable to create file. Contact Intouch.";
                                }
                            }
                            ?>    
                            <tbody id="scrl" style="overflow-y: auto;height: 20px;overflow-x: hidden">

                        </table>
                    </div>











                    <!--for new uploaded credit points-->

                    <div class="grid_12" style="overflow-y: scroll;" id="newCP">

                        <div class="grid_12" >
                            <div id="dwnloadbtn" style='margin-left:15px;  height:24px;width:130px;border: solid gray 1px;background:#F5F5F5;padding-top:4px;'>
                                <a href='<?php echo "tmp1/credit_point_New.php?output=$newfname"; ?>' title='Export table to CSV'><img src="images/excel.png" width='20' hspace='3' style='margin-bottom:-6px;' /> Export To Excel</a>
                            </div>
                        </div><br><br>
                        
                        <div style="margin-bottom: 10px;">
                            <span style="display: inline-flex; align-items: center; margin-right: 15px;">
                                <span style="width: 12px; height: 12px; background-color: green; border-radius: 50%; display: inline-block; margin-right: 5px;"></span>
                                Utilised Points
                            </span>
                            <span style="display: inline-flex; align-items: center; margin-right: 15px;">
                                <span style="width: 12px; height: 12px; background-color: yellow; border-radius: 50%; display: inline-block; margin-right: 5px;"></span>
                                Active Points
                            </span>
                            <span style="display: inline-flex; align-items: center;">
                                <span style="width: 12px; height: 12px; background-color: red; border-radius: 50%; display: inline-block; margin-right: 5px;"></span>
                                Inactive Points
                            </span>
                        </div>

                        <table style="width:100%" >
                            <tr>
                                <th colspan="18"  align="center" style="font-size:14px">View Edit Creditpoint</th>
                            </tr>

                            <tr>

                                <th>Sr.No.:</th>
                                <th>Store Name</th>
                                <th>Points </th>
                                <th>Points Upload Date</th>
                                <th>Is Redeem</th>
                                <th>Points Redeem Date</th>
                                <th>Redeem in (Invoice No)</th>
                                <th>Points Used</th>
                                <th>Remark</th>
                                <?php if ($this->currStore->usertype == UserType::CKAdmin) { ?>
                                    <th> Action </th>
                <?php } ?>
                <!--                            <th><table style="width:100%" border="0"><tr><th>CGST</th></tr><tr><th>Rate</th><th>Amount</th></tr></table><tbody  style="overflow-y: auto;height: 20px;overflow-x: hidden"></th>
                            <th><table style="width:100%"><tr><th>SGST</th></tr><tr><th>Rate</th><th>Amount</th></tr></table></th>
                            <th><table style="width:100%"><tr><th>IGST</th></tr><tr><th>Rate</th><th>Amount</th></tr></table></th>-->
                            </tr>

                            <?php
                            $i = 1;
                            if ($this->storeid == -1) {
//                            $iquery = "select r.*, c.store_name  from it_codes c,it_store_redeem_points r where  $dQuery  and r.active=1 and r.store_id=c.id order by c.store_name ";
                                $iquery = "select r.id,r.store_id,r.points_to_upload,r.points_upload_date,r.is_reddeme,rp.points_redeemdate,rp.invoice_no, rp.points_used,r.active,r.remark,r.is_completely_used,r.is_sent, c.store_name from it_codes c inner join it_store_redeem_points r on r.store_id=c.id left join  it_store_redeem_points_partial rp on r.id=rp.it_store_redeem_points_id where  $dQuery order by c.store_name ";
                            } else {

//                            $iquery = "select r.*, c.store_name  from it_codes c,it_store_redeem_points r where  $dQuery  and r.active=1 and r.store_id= $this->storeid and r.store_id=c.id";
                                $iquery = "SELECT r.id, r.store_id, r.points_to_upload, r.points_upload_date, r.is_reddeme, rp.points_redeemdate, rp.invoice_no, rp.points_used, r.active, r.remark, r.is_completely_used, r.is_sent, c.store_name FROM it_codes c INNER JOIN it_store_redeem_points r ON r.store_id = c.id LEFT JOIN it_store_redeem_points_partial rp ON r.id = rp.it_store_redeem_points_id WHERE $dQuery AND r.store_id = $this->storeid";
                            }
                            $items = $db->fetchObjectArray($iquery);
                            $pointsToUploadIdArrayForTotal = array();
                            $pointsToUploadIdArray = array();
                            $totalPoints = 0;
                            $totalUsedPoints = 0;
                            foreach ($items as $obj) {

                                if (!in_array($obj->id, $pointsToUploadIdArrayForTotal)) {
                                    array_push($pointsToUploadIdArrayForTotal, $obj->id);
                                    $totalPoints += $obj->points_to_upload;
                                }
                                $totalUsedPoints += $obj->points_used;
                                ?>
                                <tr>
                                    <td><?php echo $i; ?></td>
                                    <td><?php echo $obj->store_name; ?></td>

                                    <td style="
                                        <?php 
                                            if ($obj->active == 0 && $obj->is_sent == 0) {
                                                echo 'background-color: red; color: white;';
                                            } elseif ($obj->active == 1 && $obj->is_completely_used == 0  ) {
                                                echo 'background-color: yellow; color: black;';
                                            } elseif ($obj->active == 1 && $obj->is_completely_used == 1 && !in_array($obj->id, $pointsToUploadIdArray)) {
                                                echo 'background-color: green; color: white;';
                                            } else {
                                                echo '';
                                            }
                                        ?>
                                        ">
                                        <?php
                                        if (!in_array($obj->id, $pointsToUploadIdArray)) {
                                            echo $obj->points_to_upload;
                                        } else {
                                            ?>-<?php } ?>
                                    </td>
                                    <td>
                                        <?php
                                        if (!in_array($obj->id, $pointsToUploadIdArray)) {
                                            echo $obj->points_upload_date;
                                        } else {
                                            ?>-<?php } ?>
                                    </td>
                    <?php if ($obj->is_reddeme == 0) { ?>
                                        <td>No</td>
                                        <td> - </td>
                                        <td> - </td>
                                        <td> - </td>

                    <?php } else { ?>
                                        <td>Yes</td>
                                        <td><?php echo $obj->points_redeemdate; ?></td>
                                        <td><?php echo $obj->invoice_no; ?></td>
                                        <td style="width: 100"><?php if (isset($obj->points_used)) {
                            ?>(<?php echo $obj->points_to_upload; ?>)-><?php echo $obj->points_used; ?>
                                            <?php } else { ?>
                                                -
                                        <?php } ?></td>

                    <?php } ?>


                                    <td>
                                        <?php
                                        if (!in_array($obj->id, $pointsToUploadIdArray)) {
                                            echo $obj->remark;
                                            array_push($pointsToUploadIdArray, $obj->id);
                                        } else {
                                            ?>-<?php } ?>
                                    </td>

                    <?php if (($obj->is_reddeme == 0 ) && ($this->currStore->usertype == UserType::CKAdmin )) { ?>
                                        <td>

                                            <!--<form method="POST" action="">-->
                                            <form method="POST" action="formpost/viewEditCreditpoint.php">
                                                <input type="hidden" name="id" value='<?php echo $obj->id; ?>'>
                                                <input type="hidden" name="store_id" value='<?php echo $obj->store_id; ?>'>
                                                <input type="hidden" name="active" value='<?php echo $obj->active; ?>'>
                                                <input type="submit" style="background-color: #EC311B;   border: none;  color: white;   text-align: center; padding:5px; font-size: 14px; font-style: bold" 
                                                       <?php if (isset($obj->is_redeem) && $obj->is_redeem == 1) {
                                                           echo "abcf";
                                                           ?> disabled <?php }; ?> value="Remove" onclick="return is_confirm('<?php echo $obj->points_to_upload; ?>', '<?php echo $obj->store_name; ?>')"></form>
                                        </td>
                                    <?php }else{  ?>
                                <td></td>
                                <?php }   ?>

                                </tr>
                                <?php
                                $i++;
                            }
                            ?>
                            <?php $remainingPoints = $totalPoints - $totalUsedPoints; ?>
                            <tr><td></td><td><b>Total-</b></td><td><b><?php echo $totalPoints; ?></b></td><td></td><td></td><td></td><td></td><td><b><?php echo $totalUsedPoints; ?></b></td><td></td><td></td></tr>
                            <tr><td></td><td><b>Remaining-</b></td><td><b><?php echo $remainingPoints; ?></b></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                            <?php
                            if ($this->storeid == -1) {
//                                    $iquery = "select  c.store_name ,r.points_to_upload,r.points_upload_date,if(r.is_reddeme =1,'Yes','No' ) as Is_Redeem,if(r.is_reddeme =1,r.points_redeemdate,'-' ) as points_redeemdate,if(r.is_reddeme =1,r.invoice_no,'-' ) as invoice_no,r.remark from it_codes c,it_store_redeem_points r where  $dQuery  and r.active=1 and r.store_id=c.id order by c.store_name ";
                                $iquery = "select c.store_name ,r.points_to_upload,r.points_upload_date,if(r.is_reddeme =1,'Yes','No' ) as Is_Redeem,if(r.is_reddeme =1,rp.points_redeemdate,'-' ) as points_redeemdate,if(r.is_reddeme =1,rp.invoice_no,'-' ) as invoice_no,rp.points_used,r.remark from it_codes c inner join it_store_redeem_points r on r.store_id=c.id left join it_store_redeem_points_partial rp on r.id=rp.it_store_redeem_points_id where $dQuery and r.active=1 order by c.store_name ";
                            } else {

//                                    $iquery = "select c.store_name ,r.points_to_upload,r.points_upload_date,if(r.is_reddeme =1,'Yes','No' ) as Is_Redeem,if(r.is_reddeme =1,r.points_redeemdate,'-' ) as points_redeemdate,if(r.is_reddeme =1,r.invoice_no,'-' ) as invoice_no,r.remark from it_codes c,it_store_redeem_points r where  $dQuery  and r.active=1 and r.store_id= $this->storeid and r.store_id=c.id";
                                $iquery = "select c.store_name ,r.points_to_upload,r.points_upload_date,if(r.is_reddeme =1,'Yes','No' ) as Is_Redeem,if(r.is_reddeme =1,rp.points_redeemdate,'-' ) as points_redeemdate,if(r.is_reddeme =1,rp.invoice_no,'-' ) as invoice_no,rp.points_used,r.remark from it_codes c inner join it_store_redeem_points r on r.store_id=c.id left join it_store_redeem_points_partial rp on r.id=rp.it_store_redeem_points_id where $dQuery and r.active=1 and r.store_id= $this->storeid";
                            }
                            $items = $db->fetchObjectArray($iquery);

                            if (isset($items)) {


                                $fp = fopen('tmp1/CreditPointReportNew.csv', 'w');

                                if ($write_htm) {
                                    $fp2 = fopen('tmp1/CreditPointReportNew.htm', 'w');
                                }
                                if ($fp) {
                                    $trow = array();
                                    $tcell = array();
                                    //write header info   
                                    if ($write_htm) {
                                        fwrite($fp2, "<table width='100%' style='overflow:auto;'><thead><tr>");
                                    }

                                    $tableheaders = "Store Name:Points:Points Upload Date:Is Redeem:Points Redeem Date:Redeem in (Invoice No):Points Used:Remark";

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
                                    foreach ($items as $order) {
                                        $tcell = null;
                                        if ($write_htm) {
                                            fwrite($fp2, "<tr>");
                                        }
                                        foreach ($order as $field => $value) {


//                    
//                   if ($field=="Store Name") {
//                       $value = $order;
//                   } else if($field == "date"){                                              
//                       $t_str = ddmmyy2($value);
//                       $value = $t_str;
//                   }



                                            $tcell[] .= trim($value);
                                            if ($write_htm) {
                                                fwrite($fp2, "<td>" . trim($value) . "</td>");
                                            }
                                        }
                                        fputcsv($fp, $tcell, ',', chr(0));
                                        if ($write_htm) {
                                            fwrite($fp2, "</tr>");
                                        }
                                    }
//            if($this->gen==1){
//                $totTotalValue=$totAmt;
                                    //    }
                                    if ($write_htm) {
                                        // fwrite($fp2,"<tr><td><b></b></td><td><b></b></td></tr>");
                                        fwrite($fp2, "");
                                        fwrite($fp2, "</tbody></table>");
                                        fclose($fp2);
                                    }
                                    fclose($fp);
                                    if ($write_htm) {
                                        $table = file_get_contents("tmp1/CreditPointReport.htm");
//                echo $table;
                                    }
                                } else {
                                    echo "<br/>Unable to create file. Contact Intouch.";
                                }
                            }
                            ?>    
                            <tbody id="scrl" style="overflow-y: auto;height: 20px;overflow-x: hidden">

                        </table>
                    </div>
                    <br><br>

                </div>

                <?php
                //    }
            } // end foreach allDesigns
            catch (Exception $ex) {
                print $ex;
            }
        }
    }

}
?>
</div>