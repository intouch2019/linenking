<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");
require_once "lib/core/strutil.php";
require_once "session_check.php";

class cls_report_storelist extends cls_renderer {

    var $currStore;
    var $storeid;

    function __construct($params = null) {
        $this->currStore = getCurrUser();
        $this->params = $params;
        if (!$this->currStore) {
            return;
        }
        $this->storeid = $this->currStore->id;
    }

    function extraHeaders() {
        ?>

        <link rel="stylesheet" href="js/datatables/media/css/demo_page.css" type="text/css" />
        <link rel="stylesheet" href="js/datatables/media/css/demo_table.css" type="text/css" />
        <script src="js/datatables/media/js/jquery.dataTables.min.js"></script>
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
        $(function(){      
            var url = "ajax/tb_storelist.php";
        //alert(url);
            oTable = $('#tb_storelist').dataTable( {
                "bProcessing": true,
                "bServerSide": true,
                "aoColumns": [ null, {bSortable:false}, {bSortable:false}, {bSortable:false},{bSortable:false} ,{bSortable:false} ],
                "sAjaxSource": url,
                "bPaginate": false,
                "iDisplayLength":25
            } ); 
        // search on pressing Enter key only
            $('.dataTables_filter input').unbind('keyup').bind('keyup', function(e){
                if (e.which == 13){                     
                        oTable.fnFilter($(this).val(), null, false, true);
                }
            });     
        });   
         function  storeEdit(id){
                        window.location.href = "admin/stores/editlist/id="+id;
                    }  
           


        </script>
        <link rel="stylesheet" href="css/bigbox.css" type="text/css" />

        <?php
    }

    public function pageContent() {
        $currUser = getCurrUser();
        $menuitem = "store_list";
        include "sidemenu." . $currUser->usertype . ".php";
//            if($currUser->usertype == UserType::Admin || $currUser->usertype == UserType::CKAdmin){
        ?>
        <div class="grid_10">


            <div class="grid_12" id="tablebox" class="ui-widget-content ui-corner-bottom">
                <h5>Store List</h5>
                <table cellpadding="0" cellspacing="0" border="0" class="display" id="tb_storelist">
                    <thead>
                        <tr>

                            <th>Sr no:</th>                        
                            <th>Store Name</th>
                            <th>Store Address</th>
                            <th>Owner Name</th>
                            <th>Contact details</th>
                            <?php if ($this->currStore->usertype == UserType::Admin || $this->currStore->usertype == UserType::CKAdmin) { ?>
                                <th>Edit&nbsp;</th>
                            <?php } else { ?>
                                <th></th>
        <?php } ?>

                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="13" class="dataTables_empty">Loading data from server</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

}
?>
