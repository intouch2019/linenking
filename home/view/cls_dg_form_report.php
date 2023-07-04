<?php
ini_set('max_execution_time', 300);

require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "session_check.php";
require_once "lib/core/strutil.php";

class cls_dg_form_report extends cls_renderer {

    var $params;
    var $desig;
    var $currStore;
    var $storeidreport = null;
    var $storeid;
    var $dtrange;
    var $page;

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

        if (isset($params['page'])) {
            $this->page = $params['page'];
        } else {
            $this->page = 1;
        }
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
        <style>
            #pagination_div{

                margin-left: 35%;


            }
            .button_element{

                display: block;
                color: black;
                text-align: center;
                padding: 6px 6px 6px 6px;

                text-decoration: none;

            }

            .li_element{
                float: left;
                margin-top:2%;
            }


        </style>
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
                                var storeid = $('#store').val();
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



            function genReport() {
                var storeid = $('#store').val();
                //  alert(storeid);
                var dtrange = $("#dateselect").val();//SET DATE TO PARAMS

                // alert(dtrange);
                if (storeid != 0 && dtrange != "") {
                    window.location.href = "dg/form/report/sid=" + storeid + "/dtrange=" + dtrange;
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



            function storelablehide() {
                document.getElementById('storelabel').style.display = 'none';
            }

            function catlablehide() {
                document.getElementById('catlabel').style.display = 'none';
            }

            function datelabelhide() {
                document.getElementById('datelabel').style.display = 'none';
            }




            function DownloadDGReturnExcel() {
                var storeid = $('#store').val();
                var dtrange = $("#dateselect").val();//SET DATE TO PARAMS
                //                alert(dtrange);
                if (storeid !== 0 && dtrange !== "") {
                    window.location.href = "util/DGReturnExport.php?storeid=" + storeid + "&dtrange=" + dtrange;
                    setfocus();
                }

            }


            function thermalReceipt(id) {
                $.ajax({
                    url: "ajax/printThermalDGReport.php?id="+id,

                    success: function (response) {
                        if (response !== "") {
                            var popupWin = window.open(' ');
                            popupWin.document.open();
                            popupWin.document.write(response);
                            popupWin.document.close();
                        }
                    }
                });
            }

        </script>

        <?php
    }

    //extra-headers close
    public function pageContent() {
        $menuitem = "dgReturnReport";
        include "sidemenu." . $this->currStore->usertype . ".php";
        $sdate = "";
        $edate = "";
        $write_htm = true;
        ?>
        <div class="grid_10">
            <div class="box" style="clear:both;">
                <fieldset class="login">
                    <legend>Defective Garment Return Report</legend>

                    <h3>
                        <div class="grid_10" style="float:left">Defective Garment Report</div><br>
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


                                if ($this->currStore->usertype == UserType::Admin || $this->currStore->usertype == UserType::CKAdmin || $this->currStore->usertype == UserType::Accounts || $this->currStore->usertype == UserType::Dispatcher || $this->currStore->usertype == UserType::Picker || $this->currStore->usertype == UserType::Manager) {
                                    ?>
                                    <option value="-1" <?php echo $defaultSel; ?>>All Stores</option> 
                                <?php } ?>

                                <?php
                                if ($this->currStore->usertype == UserType::Admin || $this->currStore->usertype == UserType::CKAdmin || $this->currStore->usertype == UserType::Accounts || $this->currStore->usertype == UserType::Dispatcher || $this->currStore->usertype == UserType::Picker || $this->currStore->usertype == UserType::Manager) {
                                    $objs = $db->fetchObjectArray("select id,store_name from it_codes where usertype=4  order by store_name");
                                } else {
                                    $objs = $db->fetchObjectArray("select id,store_name from it_codes where id=" . $this->currStore->id);
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
                    $dQuery = "   d.createdate like '%$edate%' ";
                    $newfname = "DefectiveGarmentReport_" . $sdate . "_" . $edate . ".csv";

                    if ($edate == date("Y-m-d")) {
                        $dQuery = "   d.createdate like '%$edate%' ";
                    }
                } else if (count($dtarr) == 2) {
                    list($dd, $mm, $yy) = explode("-", $dtarr[0]);
                    $sdate = "$yy-$mm-$dd";
                    list($dd, $mm, $yy) = explode("-", $dtarr[1]);
                    $edate = "$yy-$mm-$dd";
                    $dQuery = "  d.createdate >= '$sdate 00:00:00' and d.createdate <= '$edate 23:59:59' ";
                    $newfname = "DefectiveGarmentReport_" . $sdate . "_" . $edate . ".csv";
                } else {
                    $dQuery = "";
                }


                try {
                    ?>
                    <div id="thermalPrint" style="display:none;">
                      
                    </div>
                    <div class="grid_12" >
                        <button name="dwnDGReturn" id="dwnDGReturn" onclick="DownloadDGReturnExcel();">Export to Excel</button>
                    </div> 
                    <div class="grid_12" style="overflow-y: scroll;">
                        <table style="width:100%" >
                            <tr>
                                <th colspan="18"  align="center" style="font-size:14px;">Defective Garment Report</th>
                                <th></th><th></th><th></th><th></th>
                            </tr>

                            <tr>
                                <th>Sr.No.</th>
                                <th>Date</th>
                                <th>Customer Name</th>
                                <th>Customer Mobile No.</th>
                                <th>Customer Old Bill No.</th>
                                <th>Old Bill Date</th>
                                <th>Original Purchase Store Name</th>
                                <th>Exchange Bill No.</th>
                                <th>Exchange Bill Date</th>
                                <th>Exchange given at the store</th>
                                <th>Store Address</th>
                                <th>Store Manager Name</th>
                                <th>Store Manager Mobile No</th>
                                <th>Product</th>
                                <th>Design No</th>
                                <th>Size</th>
                                <th>Style</th>
                                <th>Defective Garment MRP</th>
                                <th>Defective Garment Barcode</th>
                                <th>Defects</th>    
                                <th>Remark</th>
                                <th>Print Receipts</th>
                            </tr>

                            <?php
                            $limit = 30;

                            $start_from = ($this->page - 1) * $limit;
//                print_r($start_from);
//                exit();

                            $sr_no = ($limit * ($this->page - 1)) + 1;

                            if ($this->storeid == -1) {
                                $iquery = "select d.id,d.createdate, d.customer_name, d.customer_mobile_no, d.cust_old_bill_no, "
                                        . "d.old_bill_date, d.orignal_purchase_store_name, d.exchange_bill_no, d.exchange_bill_date, "
                                        . "c.store_name as exchange_given_at_store, d.store_address, d.store_manager_name, d.store_manager_mob_no, "
                                        . "d.product, d.design_no, d.defects, d.remark_for_other_defects, d.size, d.style, "
                                        . "d.barcode, d.mrp from "
                                        . "defective_garment_form d inner join it_codes c on d.exchange_given_at_store=c.id "
                                        . "where $dQuery order by d.createdate desc limit $start_from, $limit";
                                
                               
//                    print_r($iquery);
//                    exit();
                            } else {
                                $iquery = "select d.id,d.createdate, d.customer_name, d.customer_mobile_no, d.cust_old_bill_no, "
                                        . "d.old_bill_date, d.orignal_purchase_store_name, d.exchange_bill_no, d.exchange_bill_date, "
                                        . "c.store_name as exchange_given_at_store, d.store_address, d.store_manager_name, d.store_manager_mob_no, "
                                        . "d.product, d.design_no, d.defects, d.remark_for_other_defects, d.size, d.style, "
                                        . "d.barcode, d.mrp from "
                                        . "defective_garment_form d inner join it_codes c on d.exchange_given_at_store=c.id "
                                        . "where $dQuery and d.exchange_given_at_store=$this->storeid order by d.createdate desc limit $start_from, $limit";
//                    print_r($iquery);
//                    exit();
                            }
                            $items = $db->fetchObjectArray($iquery);

                            if (isset($items)) {
                                foreach ($items as $obj) {
                                    ?>
                                    <tr>
                                        <td><?php echo $sr_no; ?></td>
                                        <td><?php echo $obj->createdate; ?></td>

                                        <td><?php echo $obj->customer_name; ?></td>
                                        <td><?php echo $obj->customer_mobile_no; ?></td>
                        <?php if ($obj->cust_old_bill_no == null || $obj->cust_old_bill_no == "") { ?>
                                            <td> - </td>
                                        <?php } else { ?><td><?php echo $obj->cust_old_bill_no; ?></td><?php } ?>


                        <?php if ($obj->old_bill_date == null || $obj->old_bill_date == "") { ?>
                                            <td> - </td>
                                        <?php } else { ?><td><?php echo $obj->old_bill_date; ?></td><?php } ?>


                        <?php if ($obj->orignal_purchase_store_name == null || $obj->orignal_purchase_store_name == "") { ?>
                                            <td> - </td>
                                        <?php } else { ?><td><?php echo $obj->orignal_purchase_store_name; ?></td><?php } ?>


                                        <td><?php echo $obj->exchange_bill_no; ?></td>
                                        <td><?php echo $obj->exchange_bill_date; ?></td>
                                        <td><?php echo $obj->exchange_given_at_store; ?></td>
                                        <td><?php echo $obj->store_address; ?></td>
                                        <td><?php echo $obj->store_manager_name; ?></td>
                                        <td><?php echo $obj->store_manager_mob_no; ?></td>
                                        <td><?php echo $obj->product; ?></td>
                                        <td><?php echo $obj->design_no; ?></td>
                                        <td><?php echo $obj->size; ?></td>
                                        <td><?php echo $obj->style; ?></td>
                                        <td><?php echo $obj->mrp; ?></td>
                                        <td><?php echo $obj->barcode; ?></td>
                                        <td><?php echo $obj->defects; ?></td>


                        <?php if ($obj->remark_for_other_defects == null || $obj->remark_for_other_defects == "") { ?>
                                            <td> - </td>
                                        <?php } else { ?><td><?php echo $obj->remark_for_other_defects; ?></td><?php } ?>
                                        <td> 
                                            <button name="print_thermal" value="<?php echo $obj->id ?>" type="button" style="width:100px;" onclick="thermalReceipt(this.value);">Print Receipt</button>
                                        </td>
                                    </tr>

                        <?php
                        $sr_no++;
                    }
                }
                ?>    
                            <tbody id="scrl" style="overflow-y: auto;overflow-x: hidden;">

                        </table>




                    </div>
                    <br><br>
                    <div id="pagination_div">
                        <ul style="list-style-type: none;">
                <?php
                $sql = "SELECT COUNT(*) as count FROM defective_garment_form d inner join it_codes c on d.exchange_given_at_store=c.id where $dQuery";
                $row = $db->fetchObject($sql);
//                                $total_records = $row;
                $total_records = $row->count;
//                                print_r($total_records);
//                                exit();
                $total_pages = ceil($total_records / $limit);
//                                print_r($total_pages);
//                                exit();
                if ($total_pages < 5) {
//                                    $k = (($this->page+2>5)?5-2:(($this->page-2<1)?3:$this->page));
                    $k = 3;
                } else {
                    $k = (($this->page + 2 > $total_pages) ? $total_pages - 2 : (($this->page - 2 < 1) ? 3 : $this->page));
                }
                $pagLink = "";
                ?>


                            <script type="text/javascript">
                                function go2Page() {
                                    var storeid = $('#store').val();
                                    var dtrange = $("#dateselect").val();//SET DATE TO PARAMS
                                    var pn = document.getElementById("pn").value;
                                    pn = ((pn ><?php echo $total_pages; ?>) ?<?php echo $total_pages; ?> : ((pn < 1) ? 1 : pn));
                                    window.location.href = "dg/form/report/page=" + pn + "/sid=" + storeid + "/dtrange=" + dtrange;
                                }

                                function pageClick(val) {
                                    var storeid = $('#store').val();
                                    var dtrange = $("#dateselect").val();//SET DATE TO PARAMS
                                    var pn = val;
                                    window.location.href = "dg/form/report/page=" + pn + "/sid=" + storeid + "/dtrange=" + dtrange;
                                }

                            </script>


                <?php
//                                print_r($pn);
//                                exit();
                if ($this->page >= 2) {
                    echo "<li class='li_element'><button class='button_element' type='button' value='1' id='page1' onclick='pageClick(this.value)'> First </button></li>";
                    echo "<li class='li_element'><button class='button_element' type='button' value='" . ($this->page - 1) . "' id='prev' onclick='pageClick(this.value)'> Prev </button></li>";
                }
//                                print_r($pn);
//                                exit();
                for ($i = -2; $i <= 2; $i++) {
                    if ($k + $i == $this->page) {
                        $pagLink .= "<li class='li_element'><button class='button_element' style='background-color:powderblue;' type='button' value='" . ($k + $i) . "' id='pgNum1' onclick='pageClick(this.value)'>" . ($k + $i) . "</button></li>";
//                                        print_r($i);
//                                        exit();
                    } else {
                        $pagLink .= "<li class='li_element'><button class='button_element' type='button' value='" . ($k + $i) . "' id='pgNum2' onclick='pageClick(this.value)'>" . ($k + $i) . "</button></li>";
                    }
                }
                echo $pagLink;
                if ($this->page < $total_pages) {
                    echo "<li class='li_element'><button class='button_element' type='button' value='" . ($this->page + 1) . "' id='next' onclick='pageClick(this.value)'> Next </button></li>";
                    echo "<li class='li_element'><button class='button_element' type='button' value='" . $total_pages . "' id='total_pages' onclick='pageClick(this.value)'> Last </button></li>";
                }
//                                print_r($this->page);
//                                exit();
                ?>
                            <li class='li_element'>
                                <input class='button_element' id="pn" type="number" min="1" max="<?php echo $total_pages ?>" 
                                       placeholder="<?php echo $this->page . "/" . $total_pages; ?>" required>
                            </li>
                            <li class='li_element'>
                                <button class='button_element' onclick="go2Page();">Go</button>
                            </li>
                        </ul>

                    </div> 
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