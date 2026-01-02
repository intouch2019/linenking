<?php
ini_set('max_execution_time', 300);

require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "session_check.php";
require_once "lib/core/strutil.php";

class cls_report_discountschemecalculations extends cls_renderer {

    var $params;
    var $desig;
    var $currStore;
    var $schemediscountreport = null;
    var $storeid;
    var $page;

    function __construct($params = null) {
        $this->currStore = getCurrUser();

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
                if (storeid != 0) {
                    window.location.href = "report/discountschemecalculations/sid=" + storeid;
                    setfocus();
                } else {
                    if (storeid == 0) {
                        document.getElementById('storelabel').style.display = 'inline';
                    }
                }
            }

            function storelablehide() {
                document.getElementById('storelabel').style.display = 'none';
            }

        </script>

        <?php
    }

    //extra-headers close
    public function pageContent() {
        $menuitem = "reportDiscountSchemeCalculations";
        include "sidemenu." . $this->currStore->usertype . ".php";
        $write_htm = true;
        ?>
        <div class="grid_10">
            <div class="box" style="clear:both;">
                <fieldset class="login">
                    <legend>Discount Scheme Calculations</legend>

                    <h3>
                        <div class="grid_10" style="float:left">Credit Point Discount Scheme</div><br>
                    </h3>
                    <div class="grid_3">

                        <b>Select Store:</b><br/>
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
                    <style>
                        /* Style for the "View" button */
                        .view-button {
                            background-color: white; /* White background color */
                            color: black; /* Black text color */
                            border: none; /* No border */
                            padding: 8px 15px; /* Padding around the text */
                            border-radius: 5px; /* Rounded corners */
                            cursor: pointer; /* Cursor changes to a hand on hover */
                        }
                    </style>

                    <div class="grid_8">
                        <div class="grid_5" ><label><h5 style="color:#FF0000;" ><span id="storelabel" style="display:none;"  >please select store</span></h5> </label>     
                        </div>
                    </div>
                    <br>
                    <div class="grid_5">
                        <input type="button" class="view-button" name="genRep1" id="genRep1" value="Generate Report" onclick="genReport()">
                    </div>
                </fieldset>
            </div> <!-- class=box -->

            <?php
            if ($this->storeid != null) { //22 fields
                ?>


                <?php
                $currUri = $_SERVER["REQUEST_URI"];
                $cart_page = true;
                // include "cartinfo.php";
                $db = new DBConn();
                // $storeid = getCurrUserId();

                $newfname = "";

                $_SESSION['storeid'] = $this->schemediscountreport;

                try {
                    ?>
                    <div id="thermalPrint" style="display:none;">

                    </div>
                    <div class="grid_12" style="overflow-y: scroll;">
                        <style>
                            th, td {
                                border: 1px solid black;
                                text-align: center;
                            }
                            th[colspan="4"] {
                                text-align: center;
                            }
                        </style>
                        <table style="width:250%" >
                            <tr>
                                <th colspan="24" style="font-size: 14px; text-align: left; border: none;">Credit Point Discount Scheme
                                    <span style="font-size: 10px;">( Scroll right to Download Pdf )</span>
                                </th>

                            </tr>

                            <tr>
                                <th colspan="6"></th>
                                <th colspan="4" style="background-color: lightpink;">P18 S5</th>
                                <th colspan="4" style="background-color: lightgreen;">P12 S12</th>
                                <th colspan="4" style="background-color: lightsalmon;">P12 S5</th>
                                <th colspan="4" style="background-color: lightblue;">P5 S5</th>
                                <th colspan="4" style="background-color: lightgoldenrodyellow;">P18 S18</th>
                                <th></th>
                                <th></th>
                            </tr>
                            <tr>
                                <td><b>Sr No.</b></td>
                                <td><b>Row Labels</b></td>
                                <td><b>Store ID</b></td>
                                <td><b>Credit Point Heading</b></td>
                                <td><b>Dealer Margin</b></td>
                                <td><b>Scheme Discount</b></td>
                                <td><b>MRP Sale</b></td>
                                <td><b>Sale Without Discount</b></td>
                                <td><b>Discount</b></td>
                                <td><b>Total Value</b></td>
                                <td><b>MRP Sale</b></td>
                                <td><b>Sale Without Discount</b></td>
                                <td><b>Discount</b></td>
                                <td><b>Total Value</b></td>
                                 <td><b>MRP Sale</b></td>
                                <td><b>Sale Without Discount</b></td>
                                <td><b>Discount</b></td>
                                <td><b>Total Value</b></td>
                                <td><b>MRP Sale</b></td>
                                <td><b>Sale Without Discount</b></td>
                                <td><b>Discount</b></td>
                                <td><b>Total Value</b></td>
                                <td><b>MRP Sale</b></td>
                                <td><b>Sale Without Discount</b></td>
                                <td><b>Discount</b></td>
                                <td><b>Total Value</b></td>
                                <td><b>Non Scheme Sale Value</b></td>
                                <td><b>Export to Pdf</b></td>
                            </tr>
                            
                            <?php
                            $limit = 10;

                            $start_from = ($this->page - 1) * $limit;
                            $sr_no = ($limit * ($this->page - 1)) + 1;

                            if ($this->storeid == -1) {
                                $iquery = "select id, Row_Labels,Store_ID,Credit_Point_Heading,Dealer_Margin,Scheme_Discount,
                                    MRP_Sale_p18_s5,Sale_Without_Discount_p18_s5,Discount_p18_s5, Total_Value_p18_s5,
                                    MRP_Sale_p12_s5,Sale_Without_Discount_p12_s5,Discount_p12_s5,Total_Value_p12_s5,
                                    MRP_Sale_p5_s5,Sale_Without_Discount_p5_s5,Discount_p5_s5,Total_Value_p5_s5,
                                    MRP_Sale_p18_s18,Sale_Without_Discount_p18_s18,Discount_p18_s18,Total_Value_p18_s18,
                                    MRP_Sale_p12_s12,Sale_Without_Discount_p12_s12,Discount_p12_s12, Total_Value_p12_s12,
                                    non_scheme_sale from cp_calculations order by CreateTime desc limit $start_from, $limit";
                            } else {
                                $iquery = "select id, Row_Labels,Store_ID,Credit_Point_Heading,Dealer_Margin,Scheme_Discount,
                                    MRP_Sale_p18_s5,Sale_Without_Discount_p18_s5,Discount_p18_s5, Total_Value_p18_s5,
                                    MRP_Sale_p12_s5,Sale_Without_Discount_p12_s5,Discount_p12_s5,Total_Value_p12_s5,
                                    MRP_Sale_p5_s5,Sale_Without_Discount_p5_s5,Discount_p5_s5,Total_Value_p5_s5,
                                    MRP_Sale_p18_s18,Sale_Without_Discount_p18_s18,Discount_p18_s18,Total_Value_p18_s18,
                                    MRP_Sale_p12_s12,Sale_Without_Discount_p12_s12,Discount_p12_s12, Total_Value_p12_s12,
                                    non_scheme_sale from cp_calculations where Store_ID=$this->storeid order by CreateTime desc limit $start_from, $limit";
                            }
//       
                            $items = $db->fetchObjectArray($iquery);

                            if (isset($items)) {
                                foreach ($items as $obj) {
                                    ?>
                                    <tr>
                                        <td><?php echo $sr_no; ?></td>
                                        <td><?php echo $obj->Row_Labels; ?></td>
                                        <td><?php echo $obj->Store_ID; ?></td>
                                        <td><?php echo $obj->Credit_Point_Heading; ?></td>
                                        <td><?php echo $obj->Dealer_Margin * 100; ?>%</td>
                                        <td><?php echo $obj->Scheme_Discount * 100; ?>%</td>
                                        <td><?php echo round($obj->MRP_Sale_p18_s5) ?></td>
                                        <td><?php echo round($obj->Sale_Without_Discount_p18_s5) ?></td>
                                        <td><?php echo round($obj->Discount_p18_s5) ?></td>
                                        <td><?php echo round($obj->Total_Value_p18_s5) ?></td>
                                        <td><?php echo round($obj->MRP_Sale_p12_s12) ?></td>
                                        <td><?php echo round($obj->Sale_Without_Discount_p12_s12) ?></td>
                                        <td><?php echo round($obj->Discount_p12_s12) ?></td>
                                        <td><?php echo round($obj->Total_Value_p12_s12) ?></td>
                                        <td><?php echo round($obj->MRP_Sale_p12_s5) ?></td>
                                        <td><?php echo round($obj->Sale_Without_Discount_p12_s5) ?></td>
                                        <td><?php echo round($obj->Discount_p12_s5) ?></td>
                                        <td><?php echo round($obj->Total_Value_p12_s5) ?></td>
                                        <td><?php echo round($obj->MRP_Sale_p5_s5) ?></td>
                                        <td><?php echo round($obj->Sale_Without_Discount_p5_s5) ?></td>
                                        <td><?php echo round($obj->Discount_p5_s5) ?></td>
                                        <td><?php echo round($obj->Total_Value_p5_s5) ?></td>
                                        <td><?php echo round($obj->MRP_Sale_p18_s18) ?></td>
                                        <td><?php echo round($obj->Sale_Without_Discount_p18_s18) ?></td>
                                        <td><?php echo round($obj->Discount_p18_s18) ?></td>
                                        <td><?php echo round($obj->Total_Value_p18_s18) ?></td>
                                        <td><?php echo round($obj->non_scheme_sale) ?></td>
                                        <td>
                                            <a href='formpost/genCP_storewiseDS.php?storeid=<?php echo $obj->Store_ID; ?>&id=<?php echo $obj->id; ?>' target="_blank"><button class="view-button">Download</button></a>
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
                            if ($this->storeid == -1) {
                                $sql = "SELECT COUNT(*) as count FROM cp_calculations";
                            } else {
                                $sql = "SELECT COUNT(*) as count FROM cp_calculations where Store_ID=$this->storeid";
                            }
                            $row = $db->fetchObject($sql);
                            $total_records = $row->count;
                            $total_pages = ceil($total_records / $limit);
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
                                    var pn = document.getElementById("pn").value;
                                    pn = ((pn ><?php echo $total_pages; ?>) ?<?php echo $total_pages; ?> : ((pn < 1) ? 1 : pn));
                                    window.location.href = "report/discountschemecalculations/page=" + pn + "/sid=" + storeid;

                                }

                                function pageClick(val) {
                                    var storeid = $('#store').val();
                                    var pn = val;
                                    window.location.href = "report/discountschemecalculations/page=" + pn + "/sid=" + storeid;
                                }

                            </script>


                            <?php
                            if ($this->page >= 2) {
                                echo "<li class='li_element'><button class='button_element' type='button' value='1' id='page1' onclick='pageClick(this.value)'> First </button></li>";
                                echo "<li class='li_element'><button class='button_element' type='button' value='" . ($this->page - 1) . "' id='prev' onclick='pageClick(this.value)'> Prev </button></li>";
                            }

                            for ($i = -2; $i <= 2; $i++) {
                                if ($k + $i == $this->page) {
                                    $pagLink .= "<li class='li_element'><button class='button_element' style='background-color:powderblue;' type='button' value='" . ($k + $i) . "' id='pgNum1' onclick='pageClick(this.value)'>" . ($k + $i) . "</button></li>";
                                } else {
                                    $pagLink .= "<li class='li_element'><button class='button_element' type='button' value='" . ($k + $i) . "' id='pgNum2' onclick='pageClick(this.value)'>" . ($k + $i) . "</button></li>";
                                }
                            }
                            echo $pagLink;
                            if ($this->page < $total_pages) {
                                echo "<li class='li_element'><button class='button_element' type='button' value='" . ($this->page + 1) . "' id='next' onclick='pageClick(this.value)'> Next </button></li>";
                                echo "<li class='li_element'><button class='button_element' type='button' value='" . $total_pages . "' id='total_pages' onclick='pageClick(this.value)'> Last </button></li>";
                            }
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