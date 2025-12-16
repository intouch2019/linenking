<?php
require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once "session_check.php";

//echo "hi";
class cls_payment_link_status extends cls_renderer {

    var $currStore;
    var $params;
    var $dtrange;
    var $orders; // Added property to hold report data

    function __construct($params = null) {
        $this->currStore = getCurrUser();
        $this->params = $params;
        if (isset($_SESSION['account_dtrange'])) {
            $this->dtrange = $_SESSION['account_dtrange'];
        } else {
            $this->dtrange = date("d-m-Y");
        }
        if (isset($params['storeid'])) {
            $this->store_id = $params['storeid'];
        } else {
            $this->store_id = "";
        }
        
        // --- START: MOVED DATA FETCHING LOGIC ---
        
        $db = new DBConn();
        $dtarr = explode(" - ", $this->dtrange);
        $dQuery = "";
        
        if (count($dtarr) == 1) {
            list($dd, $mm, $yy) = explode("-", $dtarr[0]);
            $sdate = "$yy-$mm-$dd";
            $edate = "$yy-$mm-$dd";
            $dQuery = " and p.createtime like '%$edate%' ";

            if ($edate == date("Y-m-d")) {
                $dQuery = " and p.createtime like '%$edate%' ";
            }
        } else
        if (count($dtarr) == 2) {
            list($dd, $mm, $yy) = explode("-", $dtarr[0]);
            $sdate = "$yy-$mm-$dd";
            list($dd, $mm, $yy) = explode("-", $dtarr[1]);
            $edate = "$yy-$mm-$dd";
            $dQuery = " and  p.createtime >= '$sdate 00:00:00' and p.createtime <= '$edate 23:59:59' ";
        } 
        
        $where = "";

        if ($this->store_id == "" || empty($this->store_id)) {
            $this->orders = array(); // Initialize with empty array if store is not selected
        }
        else if ($this->store_id == -1) {
            $where = "";
        } else {
            $where = " and ic.id in ($this->store_id)";
        }

        if (!empty($where) || $this->store_id == -1) {
             $query = "select p.id,p.store_name,invoice_nos,invoice_amt, remark_text,p.status,p.createtime from it_payment_gateway_hdfc p, it_codes ic where ic.id=p.store_id and p.status = 'Shipped' $where $dQuery  ";

             $this->orders = $db->fetchObjectArray($query); 
        } else {
            $this->orders = array();
        }
        
        // --- END: MOVED DATA FETCHING LOGIC ---


        // Excel export check must happen AFTER $this->orders is populated
        if (isset($_GET["export"]) && $_GET["export"] == "excel") {
            // Check for potential error state before attempting to export
            if (!empty($this->orders)) {
                $this->exportPaymentExcelSimple($this->orders);
            } else {
                // If no orders were fetched (e.g., store not selected, or no data for range),
                // we can still create an empty file or just exit silently.
                $this->exportPaymentExcelSimple(array()); 
            }
        }
    } // End of __construct

    function extraHeaders() {
        // Removed the redundant export check and call from here
        if (!$this->currStore) {
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
                    <script src="js/chosen/chosen.jquery.js" type="text/javascript"></script>
                    <script type="text/javascript"> $(".chzn-select").chosen(); $(".chzn-select-deselect").chosen({allow_single_deselect: true});</script>
                    <script src="js/chosen/chosen.jquery.js" type="text/javascript"></script>
<script type="text/javascript"></script>
        <script>
            $(function () {
                $(".chzn-select").chosen();
                $(".chzn-select-deselect").chosen({allow_single_deselect: true});
            });

            function genReport(usertype, storeid) {
                if ((usertype != 4) && (storeid == 0 || storeid == null)) {
                    alert('Select Store');
                    return;
                    //                    alert(storeid);
                }
                var dtrange = $("#dateselect").val();//SET DATE TO PARAMS

                // alert(dtrange);
                if (storeid != 0 && dtrange != "") {
                    window.location.href = "payment/link/status/storeid=" + storeid + "/dtrange=" + dtrange;
                    setfocus();
                } else {
                    //                    alert('Please Fill All Values');

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


            function getStoreId() {
                var storeid = $('#store_name').val();
                //                alert(storeid);
                if (storeid !== 0) {
                    window.location.href = "payment/link/status/storeid=" + storeid;
                    setfocus();
                }

            }
            function searchbydate() {
                var storeid = $('#store_name').val();
                var startDate = $('#start_date').val();
                var endDate = $('#end_date').val();
                var url = "payment/link/status/storeid=" + storeid;
                if ((startDate == "") || (endDate == "")) {
                    alert("select both dates");
                    return;
                }
                ;
                //if(startDate == 
                if (startDate && endDate) {
                    url += "&start_date=" + startDate + "&end_date=" + endDate;
                }
                window.location.href = url;
            }
            ;

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

        </script>

        <?php
    }

    //extra-headers close

    public function pageContent() {
        $menuitem = "paymentlinkstatus";
        include "sidemenu." . $this->currStore->usertype . ".php";
        $store_id = getCurrUserId();
        $formResult = $this->getFormResult();
        $usertype = getCurrUser()->usertype; // Get usertype
        ?>

        <?php if ($usertype == 4): ?>
            <script>
                window.onload = function () {
                    var storeId = <?php echo (int) $store_id; ?>;

                    function formatDate(date) {
                        var dd = ('0' + date.getDate()).slice(-2);
                        var mm = ('0' + (date.getMonth() + 1)).slice(-2);
                        var yyyy = date.getFullYear();
                        return dd + '-' + mm + '-' + yyyy;
                    }

                    var now = new Date();
                    var past = new Date(now.getTime() - (48 * 60 * 60 * 1000)); // 48 hours ago
                    var fromDate = formatDate(past);
                    var toDate = formatDate(now);
                    var dtrange = encodeURIComponent(fromDate + ' - ' + toDate);

                    var currentUrl = window.location.href;

                    if (!currentUrl.includes('dtrange=')) {
                        var newUrl = currentUrl + '/storeid=' + storeId + '/dtrange=' + dtrange;
                        window.history.replaceState({}, '', newUrl);
                    }
                };
            </script>
        <?php endif; ?>

        <div class="grid_10">
            <?php if ($formResult) { ?>
                <div style="width: 50%; margin: 0 auto; border-radius: 10px;background-color: white;">
                    <p>
                        <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
                    </p>
                </div>
            <?php } ?>
            <div id="daterangeselection">

                <?php if ($this->currStore->usertype == UserType::Admin || $this->currStore->usertype == UserType::Accounts || $this->currStore->usertype == UserType::CKAdmin) { ?>
                    <div id="storeSelection">
                        <label for="store">*Search store: </label>
                        <select name="store_name" id="store_name" 
                                data-placeholder="Choose Store" 
                                class="chzn-select" 
                                style="width:30%;" 
                                required 
                                onchange="getStoreId()"
                                >
                            <option value="0">Select store</option>  
                            <?php
                            if ($this->store_id == -1) {
                                $allSel = "selected";
                            } else {
                                $allSel = "";
                            }
                            ?>
                            <option value="-1" <?php echo $allSel; ?>>All store</option> 
                            <?php
                            if ($this->currStore->usertype != UserType::Admin && $this->currStore->usertype != UserType::Accounts && $this->currStore->usertype != UserType::CKAdmin) {
                                $other_con = "and id = $this->storeid ";
                            } else {
                                $other_con = "";
                            }

                            $db = new DBConn();
                            $objs = $db->fetchObjectArray("select id, store_name from it_codes where usertype=4 $other_con order by store_name");

                            foreach ($objs as $obj) {
                                if ($this->store_id == $obj->id) {
                                    $defaultSel = "selected";
                                } else {
                                    $defaultSel = "";
                                }
                                ?>
                                <option value="<?php echo $obj->id; ?>" <?php echo $defaultSel; ?> > <?php echo $obj->store_name; ?></option> 
                            <?php } ?>
                        </select><br>

                    <?php } ?>

                    <div style="margin-bottom: 5px" >
                        <span style="font-weight:bold;">Date Filter : </span><br> <input size="17" type="text" id="dateselect" name="dateselect" value="<?php echo $this->dtrange; ?>" onclick="datelabelhide()" onchange="datelabelhide()"> (Click to see date options)
                        <br><br> 
                        <?php if ($this->currStore->usertype == UserType::Admin || $this->currStore->usertype == UserType::Accounts || $this->currStore->usertype == UserType::CKAdmin) { ?>
                            <button  onclick="genReport(<?php echo $this->currStore->usertype ?>, <?php echo $this->store_id; ?>)" id="searchButton">Search</button> 
                        <?php } else { ?>
                            <button  onclick="genReport(<?php echo $this->currStore->usertype ?>, <?php echo $store_id; ?>)" id="searchButton">Search</button> 

                        <?php }
                        ?>    

                    </div>      <br> 

                    <?php
                    // Data is now already fetched in the constructor, assign it to a local variable for display
                    $orders = $this->orders;

                    if($this->store_id == "" || empty($this->store_id) || $this->store_id == 0){
                            // Do not return here to allow the form to still display
                    }
                    
                    ?>
                        <a href="payment/link/status/storeid=<?php echo $this->store_id; ?>/dtrange=<?php echo urlencode($this->dtrange); ?>&export=excel">
    <button type="button">Download Excel</button>
</a>


                    <div class="box">
                        <input type="hidden" id="storeId" value="<?php echo $store_id; ?>" >
                        <h2>
                            <a href="#" id="toggle-accordion" style="cursor: pointer; ">Payment Link Status Report</a>
                        </h2><br>

                        <div style="margin-top: 0px; margin-right: 0px; margin-bottom: 0px; margin-left: 0px; position: static; overflow-x: scroll; overflow-y: scroll; ">
                            <div class="block" id="accordion" style="margin-top: 0px; margin-right: 0px; margin-bottom: 0px; margin-left: 0px; ">
                                <div id="accordion">
                                    <table>
                                        <tr>

                                            <th>Store Name.</th>
                                            <th>invoice_nos. </th>
                                            <th>invoice_amt.  </th>
                                            <th>remark_text.   </th>
                                            <th>status.  </th>
                                            <th>createtime  </th>


                                        </tr>

                                        <?php
                                        if (isset($orders) && !empty($orders)) {
                                            foreach ($orders as $order) {
                                                ?>
                                                <tr>

                                                    <td><?php echo $order->store_name; ?></td>
                                                    <td><?php echo $order->invoice_nos; ?></td>
                                                    <td><?php echo $order->invoice_amt; ?></td>
                                                    <td><?php echo $order->remark_text; ?></td>
                                                    <td><?php echo $order->status; ?></td>
                                                    <td><?php echo $order->createtime; ?></td>

                                                </tr>
                                                <?php
                                            }
                                        }
                                        ?>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
   function exportPaymentExcelSimple($orders) {
    
       if (ob_get_level()) {
        ob_clean();
        }
    
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }   
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=payment_link_report.xls");
    header("Pragma: no-cache");
    header("Expires: 0");

    echo "<table border='1'>";
    echo "<tr>
            <th>Store Name</th>
            <th>Invoice No</th>
            <th>Invoice Amt</th>
            <th>Remark Text</th>
            <th>Status</th>
            <th>Date</th>
          </tr>";


    
    if (!empty($orders)) {
        foreach ($orders as $o) {
            echo "<tr>
                    <td>{$o->store_name}</td>
                    <td>{$o->invoice_nos}</td>
                    <td>{$o->invoice_amt}</td>
                    <td>{$o->remark_text}</td>
                    <td>{$o->status}</td>
                    <td>{$o->createtime}</td>
                  </tr>";
        }
    }

    echo "</table>";
    exit;
}


}
?>