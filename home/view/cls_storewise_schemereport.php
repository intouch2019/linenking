<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");
require_once ("lib/core/strutil.php");

class cls_storewise_schemereport extends cls_renderer {

    var $currUser;
    var $userid;

    function __construct($params = null) {
        //parent::__construct(array(UserType::Admin, UserType::CKAdmin));
        $this->currUser = getCurrUser();
        $this->userid = $this->currUser->id;
    }

    function extraHeaders() {
        ?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
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

 <style type="text/css" title="currentStyle">
    @import "js/datatables/media/css/demo_page.css";
    @import "js/datatables/media/css/demo_table.css";
</style>
<!-- Include jQuery -->

<script src="js/datatables/media/js/jquery.dataTables.min.js"></script>
<script src="js/datatables/media/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="js/chosen/chosen.css" />
<link rel="stylesheet" href="css/bigbox.css" type="text/css" />

        <script type="text/javascript">
            $(function () {
                var url = "ajax/tb_storewisescheme.php";
                //alert(url);
                oTable = $('#tb_storewisescheme').dataTable({
                    "bProcessing": true,
                    "bServerSide": true,
                    "aoColumns": [null, null, null, null, null,{"bSortable": false}],
                    "aaSorting": [[0, "desc"]],
                    "sAjaxSource": url,
                    "iDisplayLength": 50
                });
                //                oTable.fnSort([[0, 'desc']]);
                // search on pressing Enter key only
                $('.dataTables_filter input').unbind('keyup').bind('keyup', function (e) {
                    if (e.which == 13) {
                        oTable.fnFilter($(this).val(), null, false, true);
                    }
                });
            });

function deletestoreassigscheme(storeassignschemeid) {
    // Show confirmation alert before AJAX call
    if (!confirm("Are you sure you want to delete this scheme?")) {
        return; // Exit function if user clicks "Cancel"
    }

    $.ajax({
        type: "POST",
        url: "ajax/delete_storeassignscheme.php",
        data: { storeassignschemeid: storeassignschemeid },
        dataType: "json", // Expecting JSON response
        success: function(response) {
            if (response.status === 'success') {
                alert(response.message);
                window.location.href = "storewise/schemereport"; // Redirect after success
            } else {
                alert("Error: " + response.message);
            }
        },
        error: function(xhr, status, error) {
            alert("AJAX Error: " + error);
        }
    });
}


        </script>		
        <?php
    }

    public function pageContent() {
        $menuitem = "storewiseschemereport";
        include "sidemenu." . $this->currUser->usertype . ".php";
        $formResult = $this->getFormResult();
        ?>
        <div class="grid_10">
            <?php $_SESSION['form_post'] = array(); ?>
            <?php
            $display = "none";
            $num = 0;
            $db = new DBConn();
//        $spObj = $db->fetchObject("select * from it_codes where id = ".DEF_SP_LIFESTYLE_ID); // S.P. Life Style ID
            //$invoices = $db->fetchObjectArray("select i.* , c.store_name from it_invoices i , it_codes c where i.store_id = c.id and i.invoice_type = 0 group by i.id order by i.id desc");
            // $invoices = $db->fetchObjectArray("select i.*  from it_invoices i  where  i.invoice_type = 0 group by i.id order by i.id desc");
            ?>
            <!--<div class="box" style="clear:both;">-->
            <!--<fieldset class="login">-->	
            <div class="grid_12" id="tablebox" class="ui-widget-content ui-corner-bottom" style="overflow:auto;">
                <legend>CottonKing Storewise Scheme</legend>
                <table align="center" border="1" cellpadding="0" cellspacing="0" border="0" class="display" id="tb_storewisescheme">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Store Name</th>
                            <th>Scheme Name</th>
                            <th>Scheme Start date</th>
                            <th>Scheme End Date</th>
                            <th>Delete Scheme</th>
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
