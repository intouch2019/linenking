<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");
require_once ("lib/core/strutil.php");

class cls_barcode_batches extends cls_renderer {

    var $currUser;
    var $userid;

    function __construct($params = null) {
        //parent::__construct(array(UserType::Admin, UserType::CKAdmin));
        $this->currUser = getCurrUser();
        $this->userid = $this->currUser->id;
    }

    function extraHeaders() {
        ?>

        <script type="text/javascript" src="js/ajax.js"></script>
        <script type="text/javascript" src="js/ajax-dynamic-list.js">

        </script>

        <style type="text/css" title="currentStyle">
            @import "js/datatables/media/css/demo_page.css";
            @import "js/datatables/media/css/demo_table.css";
        </style>
        <script src="js/datatables/media/js/jquery.dataTables.min.js"></script>
        <link rel="stylesheet" href="js/chosen/chosen.css" />
        <link rel="stylesheet" href="css/bigbox.css" type="text/css" />
        <script type="text/javascript">
            $(function () {
                var url = "ajax/tb_barcode_batches.php";
                //alert(url);
                oTable = $('#tb_allinvoices').dataTable({
                    "bProcessing": true,
                    "bServerSide": true,
                    "aoColumns": [null, null, null, null, null, {"bSortable": false}, {"bSortable": false}, null, null],
                    "aaSorting": [[0, "desc"]],
                    "sAjaxSource": url,
                    "iDisplayLength": 10
                });
                //                oTable.fnSort([[0, 'desc']]);
                // search on pressing Enter key only
                $('.dataTables_filter input').unbind('keyup').bind('keyup', function (e) {
                    if (e.which == 13) {
                        oTable.fnFilter($(this).val(), null, false, true);
                    }
                });
            });

        </script>

        <?php
    }

    public function pageContent() {
        $menuitem = "bbatches";
        include "sidemenu." . $this->currUser->usertype . ".php";
        $formResult = $this->getFormResult();
        ?>
        <div class="grid_10">
            <?php $_SESSION['form_post'] = array(); ?>
            <?php
            $display = "none";
            $num = 0;
            $db = new DBConn();
//	$invoices = $db->fetchObjectArray("select * from it_invoices where invoice_type = 0 order by id desc");
            ?>
            <!--<div class="box" style="clear:both1;">-->
            <!--<fieldset class="login">-->
            <div class="grid_12" id="tablebox" class="ui-widget-content ui-corner-bottom" style="overflow:auto;">
                <legend>Barcode Batches</legend>
                <table align="center" border="1" cellpadding="0" cellspacing="0" border="0" class="display" id="tb_allinvoices">
                    <thead>
                        <tr>
                            <th>Batch No</th>
                            <th>Manufactured By</th>
                            <th>Category</th>
                            <th style="text-align:right;">Design</th>
                            <th style="text-align:right;">MRP</th>
                            <th>Date Created</th>
                            <th>View Batch</th> 
                            <th>Status</th>
                            <th>Download CSV</th>             
                        </tr>
                    </thead>

                </table>
            </div>
            <!--</fieldset>-->
            <!--</div>  class=box -->
        </div>

        <?php
    }

}
?>
