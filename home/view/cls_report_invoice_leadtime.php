<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");
require_once "lib/core/strutil.php";
require_once "session_check.php";

class cls_report_invoice_leadtime extends cls_renderer {

    var $currUser;
    var $userid;
    var $dtrange;
    var $params;
    var $storeid = "-1";

    function __construct($params = null) {
        $this->currUser = getCurrUser();
        if ($params && isset($params['storeid'])) {
            $this->storeid = $params['storeid'];
        }
        if ($params && isset($params['dtrange'])) {
            $this->dtrange = $params['dtrange'];
        } else {
            $this->dtrange = date("d-m-Y");
        }
    }

    function extraHeaders() {
        ?>
        <style type="text/css" title="currentStyle">
            @import "js/datatables/media/css/demo_page.css";
            @import "js/datatables/media/css/demo_table.css";
            @import "css/ui.daterangepicker.css";
            @import "css/redmond/jquery-ui-1.7.1.custom.css";
        </style>
      
        <script type="text/javascript" src="jqueryui/js/jquery-ui-1.7.1.custom.min.js"></script>
        <script type="text/javascript" src="js/daterangepicker.jQuery.js"></script>
        <script src="js/datatables/media/js/jquery.dataTables.min.js"></script>                
        <script src="js/chosen/chosen.jquery.js" type="text/javascript"></script>        
                
        <link rel="stylesheet" href="js/chosen/chosen.css" />
        <script type="text/javascript" src="js/ajax.js"></script>
        <script type="text/javascript" src="js/ajax-dynamic-list.js"></script>
        
        <script type="text/javaScript">
            $(function(){
                $(".chzn-select").chosen();
                $(".chzn-select-deselect").chosen({allow_single_deselect:true});

                function formatAvgLeadTime(avgHours) {
                    var d = parseFloat(avgHours);
                    if (isNaN(d)) { return ""; }
                    return Math.round(d) + " days";
                }

                var url = "ajax/tb_invoice_leadtime.php?storeid=<?php echo $this->storeid; ?>&dtrange=<?php echo $this->dtrange; ?>";
                oTable = $('#tb_invoice_leadtime').dataTable({
                    "bProcessing": true,
                    "bServerSide": true,
                    "aaSorting": [[1, "desc"]],
                    "aoColumns": [ null, null, null, null, null, null ],
                    "sAjaxSource": url,
                    "fnServerData": function (sSource, aoData, fnCallback) {
                        $.ajax({
                            "dataType": 'json',
                            "type": "GET",
                            "url": sSource,
                            "data": aoData,
                            "success": function (json) {
                                fnCallback(json);
                                var avg = (json && json.avg_leadtime_days !== undefined && json.avg_leadtime_days !== null) ? json.avg_leadtime_days : "";
                                $("#avgLeadTime").text(avg === "" ? "" : formatAvgLeadTime(avg));
                            }
                        });
                    }
                });

                $('.dataTables_filter input').unbind('keyup').bind('keyup', function(e){
                    if (e.which == 13){
                        oTable.fnFilter($(this).val(), null, false, true);
                    }
                });

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
                            success: function(data) {}
                        });
                    }
                });
            });

            function genRep(){
                var store_ids = $("#store").val();
                var dtrange = $("#dateselect").val();
                if(store_ids == null){
                    alert("Please select store(s) first");
                } else {
                    window.location.href="report/invoice/leadtime/storeid="+store_ids+"/dtrange="+dtrange;
                }
            }

            function genExcelRep(){
                var store_ids = $("#store").val();
                var dtrange = $("#dateselect").val();
                if(store_ids == null){
                    alert("Please select store(s) first");
                } else {
                    window.location.href="formpost/genInvoiceLeadtimeExcel.php?storeid="+store_ids+"&dtrange="+dtrange;
                }
            }
        </script>
        <?php
    }

    public function pageContent() {
        $currUser = getCurrUser();
        $menuitem = "invoiceleadtime";
        include "sidemenu." . $currUser->usertype . ".php";

        $db = new DBConn();
        ?>
        <div class="grid_10">
            <div class="grid_12">
                <div class="grid_4">
                    <b>Select Store*:</b><br/>
                    <select name="store" id="store" data-placeholder="Choose Store" class="chzn-select" multiple style="width:100%;">
                        <?php
                        if ($this->storeid == -1) { $defaultSel = "selected"; }
                        else { $defaultSel = ""; }
                        ?>
                        <option value="-1" <?php echo $defaultSel; ?>>All Stores</option>
                        <?php
                        $objs = $db->fetchObjectArray("select id,store_name from it_codes where usertype=" . UserType::Dealer . " and is_closed=0 and id in (select store_id from executive_assign where exe_id=" . getCurrUser()->id . ") order by store_name");

                        if ($this->storeid == "-1") {
                            $storeid = array();
                            $allstoreArrays = $db->fetchObjectArray("select id from it_codes where usertype=4");
                            foreach ($allstoreArrays as $storeArray) {
                                foreach ($storeArray as $store) {
                                    array_push($storeid, $store);
                                }
                            }
                        } else {
                            $storeid = explode(",", $this->storeid);
                        }

                        foreach ($objs as $obj) {
                            $selected = "";
                            if ($this->storeid != -1) {
                                foreach ($storeid as $sid) {
                                    if ($obj->id == $sid) { $selected = "selected"; }
                                }
                            }
                            ?>
                            <option value="<?php echo $obj->id; ?>" <?php echo $selected; ?>><?php echo $obj->store_name; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="grid_8">
                    <span style="font-weight:bold;">Date Filter : </span></br>
                    <input size="17" type="text" id="dateselect" name="dateselect" value="<?php echo $this->dtrange; ?>" /> (Click to see date options)
                </div>
                <div class="grid_12">
                    <div class="grid_8">
                        <input type="button" name="genrep" value="Generate Report" onclick="javascript:genRep();">
                    </div>
                    <div class="grid_4">
                        <input type="button" name="genexcel" value="Download Excel" onclick="javascript:genExcelRep();">
                    </div>
                    <br>
                    <h7>Select the options and click on "Generate Report" button</h7>
                </div>
            </div>
            <div class="clear"></div>
            <br>
            <div class="grid_12" id="tablebox" class="ui-widget-content ui-corner-bottom">
                <h5>Invoice Lead Time Report</h5>
                <table cellpadding="0" cellspacing="0" border="0" class="display" id="tb_invoice_leadtime">
                    <thead>
                        <tr>
                            <th>Store</th>
                            <th>Invoice No</th>
                            <th>Invoice Date</th>
                            <th>Invoice Pull Date</th>
                            <th>Status</th>
                            <th>Lead Time (days)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="6" class="dataTables_empty">Loading data from server</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="5" style="text-align:right;">Avg Lead Time (days):</th>
                            <th id="avgLeadTime"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <?php
    }
}
?>
