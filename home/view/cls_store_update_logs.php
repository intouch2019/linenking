<?php
require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once "session_check.php";

//echo "hi";
class cls_store_update_logs extends cls_renderer {

    var $currStore;
    var $params;
    var $dtrange;

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
    }

    function extraHeaders() {
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
                    window.location.href = "store/update/logs/storeid=" + storeid + "/dtrange=" + dtrange;
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
                    window.location.href = "store/update/logs/storeid=" + storeid;
                    setfocus();
                }

            }
            function searchbydate() {
                var storeid = $('#store_name').val();
                var startDate = $('#start_date').val();
                var endDate = $('#end_date').val();
                var url = "store/update/logs/storeid=" + storeid;
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
        $menuitem = "viewLogs";
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

                <?php if ($this->currStore->usertype == UserType::Admin || $this->currStore->usertype == UserType::Accounts || $this->currStore->usertype == UserType::CKAdmin || $this->currStore->usertype == UserType::Manager) { ?>
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
                            if ($this->currStore->usertype != UserType::Admin && $this->currStore->usertype != UserType::Accounts && $this->currStore->usertype != UserType::CKAdmin && $this->currStore->usertype != UserType::Manager) {
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
                        <?php if ($this->currStore->usertype == UserType::Admin || $this->currStore->usertype == UserType::Accounts || $this->currStore->usertype == UserType::CKAdmin || $this->currStore->usertype == UserType::CKAdmin || $this->currStore->usertype == UserType::Manager) { ?>
                            <button  onclick="genReport(<?php echo $this->currStore->usertype ?>, <?php echo $this->store_id; ?>)" id="searchButton">Search</button> 
                        <?php } else { ?>
                            <button  onclick="genReport(<?php echo $this->currStore->usertype ?>, <?php echo $store_id; ?>)" id="searchButton">Search</button> 
                        <?php } ?>    
                    </div>      <br> 

                    <?php
                    $db = new DBConn();
                    $dtarr = explode(" - ", $this->dtrange);
                    if (count($dtarr) == 1) {
                        list($dd, $mm, $yy) = explode("-", $dtarr[0]);
                        $sdate = "$yy-$mm-$dd";
                        $edate = "$yy-$mm-$dd";
                        $dQuery = " and createtime like '%$edate%' ";

                        if ($edate == date("Y-m-d")) {
                            $dQuery = " and createtime like '%$edate%' ";
                        }
                    } else
                    if (count($dtarr) == 2) {
                        list($dd, $mm, $yy) = explode("-", $dtarr[0]);
                        $sdate = "$yy-$mm-$dd";
                        list($dd, $mm, $yy) = explode("-", $dtarr[1]);
                        $edate = "$yy-$mm-$dd";
                        $dQuery = " and  createtime >= '$sdate 00:00:00' and createtime <= '$edate 23:59:59' ";
                    } else {
                        $dQuery = "";
                    }
                    $where = "";
                    $extract_id = "TRIM(BOTH ';' FROM TRIM(SUBSTRING_INDEX(REPLACE(message,'id =','id='),'id=',-1)))";

                    if ($this->currStore->usertype == UserType::Admin || $this->currStore->usertype == UserType::Accounts || $this->currStore->usertype == UserType::CKAdmin || $this->currStore->usertype == UserType::Manager) {

                        if (!empty($this->store_id) && $this->store_id != -1) {
                            // Specific store
                            $where = "WHERE $extract_id = '$this->store_id'";
                        } else if ($this->store_id == -1) {
                            // All stores with non-zero id
                            $where = "WHERE $extract_id != '0'";
                        } else {
                            // No store id
                            $where = "WHERE $extract_id = ''";
                        }
                    } else {
                        // Other users
                        $where = "WHERE $extract_id = '$store_id'";
                    }

                    $query = "SELECT id,SUBSTRING_INDEX(pg_name, '/', -1) AS filename,incomingid,msgtype,message,ipaddr,createtime FROM it_codes_logs $where $dQuery order by id desc";

//                    echo $query . "<br>";
                    $orders = $db->fetchObjectArray($query);
                    $disabled = $db->fetchObject("select inactive from it_codes where id=$store_id");
                    ?>
                    <div class="box">
                        <input type="hidden" id="storeId" value="<?php echo $store_id; ?>" >
                        <h2>
                            <a href="#" id="toggle-accordion" style="cursor: pointer; ">Store Update Logs (Reflecting From 4 Dec 2025)</a>
                        </h2><br>

                        <div style="margin-top: 0px; margin-right: 0px; margin-bottom: 0px; margin-left: 0px; position: static; overflow-x: hidden; overflow-y: hidden; ">
                            <div class="block" id="accordion" style="margin-top: 0px; margin-right: 0px; margin-bottom: 0px; margin-left: 0px; ">
                                <div id="accordion">
                                    <table>
                                        <tr><?php
                                            if ($this->currStore->usertype == UserType::Admin || $this->currStore->usertype == UserType::Accounts || $this->currStore->usertype == UserType::CKAdmin  || $this->currStore->usertype == UserType::Manager) {
                                                ?> <th>User Name/Updated By</th><?php
                                            }
                                            ?>

                                            <th>Store Name.</th>
                                            <th>Activity/Page Name</th>
                                            <th style="display:none">IP Address</th>
                                            <th>Reason</th>
                                            <th style="display:none">Reason</th>
                                            <th>Create time</th>


                                        </tr>

                                        <?php
                                        if (isset($orders) && !empty($orders)) {
                                            foreach ($orders as $order) {

                                                $store_name_qry = "select store_name from it_codes where id=$store_id";
                                                $store_name = $db->fetchObject($store_name_qry);
                                                ?>
                                                <tr>
                                                    <?php
                                                    if ($this->currStore->usertype == UserType::Admin || $this->currStore->usertype == UserType::Accounts || $this->currStore->usertype == UserType::CKAdmin  || $this->currStore->usertype == UserType::Manager) {
                                                        ?><td style="display:none"> <?php echo $store_name->store_name; ?></td><?php }
                                                    ?>

                                                    <td><?php
                                                    if($order->incomingid == 1){
                                                        $st_id = 67;
                                                    }else{
                                                        $st_id=$order->incomingid;
                                                    }
                                                        $userName = $db->fetchObject("select id,store_name from it_codes where id=$st_id");
                                                        echo $userName->store_name;
                                                        ?></td>
                                                    <td><?php
//                                                    echo "<br>";
//                                                    echo "select store_name from it_codes where id=$this->store_id";
                                                        if ($this->store_id != -1) {
                                                            $storeName = $db->fetchObject("select store_name from it_codes where id=$this->store_id");
                                                        } else {
                                                            $storeId = $db->fetchObject("SELECT CAST( TRIM(BOTH ''' ' FROM SUBSTRING_INDEX( REPLACE(REPLACE(message, 'id =', 'id='), 'id= ', 'id='), 'id=', -1 ) ) AS UNSIGNED ) AS store_id FROM it_codes_logs where id=$order->id;");
                                                            $storeName = $db->fetchObject("select store_name from it_codes where id=$storeId->store_id");
                                                           $is_allstore= $db->fetchObject("select CASE WHEN message LIKE '%usertype%' THEN 1 ELSE 0 END AS is_allstore FROM it_codes_logs WHERE id =$order->id")->is_allstore;
                                                        }
                                                        if($order->filename == "clsProperties.php"){
                                                            echo 'All Stores'; 
                                                        }else
                                                        if($order->filename == "storeLoginDisableReason.php" && $is_allstore=="1"){
                                                             echo 'All Stores'; 
                                                        }else
                                                        {
                                                        echo $storeName->store_name;
                                                        }
                                                        ?></td>
                                                    <td><?php
                                                        $filename = $order->filename;

                                                        if ($filename == 'editStore.php') {
                                                            $message = "Edit Store Page";
                                                        } elseif ($filename == 'cls_admin_stores_enable.php') {
                                                            $message = "Store Login Enabled Manually";
                                                        } elseif ($filename == 'proforma_payment_link_hdfc.php') {
                                                            $message = "Dealer Generated Payment Link Using HDFC Payment Gateway";
                                                        } elseif ($filename == 'payment_receiver_order_lookup_hdfc.php') {
                                                            $message = "HDFC Payment API Status Check";
                                                        } elseif ($filename == 'add_paymentGateway_hdfc.php') {
                                                            $message = "User Manually generated Invoice Link";
                                                        } elseif ($filename == 'proforma_order_auto_cancel.php') {
                                                            $message = "Proforma Order Cancel Cron";
                                                        } elseif ($filename == 'storeLoginDisableReason.php') {
                                                            $message = "Store Login Disabled Manually";
                                                        }elseif ($filename == 'clsProperties.php') {
                                                        $valObj = $db->fetchObject( "SELECT SUBSTRING_INDEX( SUBSTRING_INDEX(message, \"value='\", -1), \"'\", 1 ) AS value FROM it_codes_logs WHERE id = $order->id" ); 
                                                        $val = $valObj ? $valObj->value : null;                                                            
                                                        if(($valObj->value)=='0'){
                                                           $message = "All Stores Enabled";  
                                                        }elseif(($valObj->value)=='1'){
                                                           $message = "All Stores Disabled";  
                                                        }
                                                        }
                                                        elseif ($filename == 'cancel_selected_proformas.php') {
                                                            $message = "Proforma Canceled Manually";
                                                        }
                                                        else {
                                                            $message = "Unknown Action";
                                                        }

                                                        echo $message;
                                                        ?></td>
                                                    <td><?php  
                                                    
                                                    $query="SELECT CASE WHEN message LIKE '%inactivating_reason%' THEN TRIM(BOTH '''' FROM SUBSTRING( SUBSTRING_INDEX(message, 'inactivating_reason =', -1), LOCATE('\'', SUBSTRING_INDEX(message, 'inactivating_reason =', -1)) + 1, LOCATE('\'', SUBSTRING_INDEX(message, 'inactivating_reason =', -1), LOCATE('\'', SUBSTRING_INDEX(message, 'inactivating_reason =', -1)) + 1) - LOCATE('\'', SUBSTRING_INDEX(message, 'inactivating_reason =', -1)) - 1 ) ) ELSE NULL END AS inactivating_reason FROM it_codes_logs WHERE id= $order->id order by id desc;";
                                                    
//                                                    echo $order->id;
                                                    
                                                    $reason=$db->fetchObject($query);
                                                    
                                                    if($reason==null){
                                                        echo "-";
                                                    }else{
                                                        echo $reason->inactivating_reason;
//                                                        
                                                    }
                                                    
                                                    ?></td>
                                                    <td style="display:none"><?php echo $order->ipaddr; ?></td>                                                       
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

}
