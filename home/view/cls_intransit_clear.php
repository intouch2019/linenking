<?php
ini_set('max_execution_time', 300);

require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "session_check.php";
require_once "lib/core/strutil.php";
require_once "lib/core/Constants.php";

class cls_intransit_clear extends cls_renderer {

    var $currStore;
    var $storeid;
    var $storeIds;

    function __construct() {
        if (isset($_SESSION['selectedStoreId'])) {
            $this->selectedStoreId = $_SESSION['selectedStoreId'];
        }

        $this->currStore = getCurrUser();
        $this->storeid = $this->currStore->id;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->storeIds = $_POST['storeIds'];
        }
    }

    function extraHeaders() {
        if (!$this->currStore) {
            return;
        }
        ?>

        <script type="text/javascript" src="<?php CdnUrl('jqueryui/js/jquery-ui-1.7.1.custom.min.js'); ?>"></script>
        <link rel="stylesheet" href="<?php CdnUrl('css/redmond/jquery-ui-1.7.1.custom.css'); ?>" type="text/css" title="ui-theme" />
        <link rel="stylesheet" href="<?php CdnUrl('js/chosen/chosen.css'); ?>" />
        <script src="<?php CdnUrl('js/chosen/chosen.jquery.js'); ?>" type="text/javascript"></script>
        <link rel="stylesheet" href="<?php CdnUrl('css/prettyPhoto.css'); ?>" type="text/css" media="screen" title="prettyPhoto main stylesheet" charset="utf-8" />
        <link rel="stylesheet" type="text/css" href="<?php CdnUrl('css/dark-glass/sidebar.css'); ?>" />
        <script src="<?php CdnUrl('js/prettyPhoto/jquery.prettyPhoto.js'); ?>" type="text/javascript" charset="utf-8"></script>
        <script type="text/javascript" src="<?php CdnUrl('js/sidebar/jquery.sidebar.js'); ?>"></script>
        <style>
            .checkbox {
                font-size: 20px;
            }
            .form-row {
                display: flex;
                align-items: center;
                gap: 20px;
            }
            .form-roww {
                display: flex;
                align-items: center;
                gap: 10px;
            }
            .form-row label {
                min-width: 150px;
            }
            .escalation-row {
                display: flex;
                align-items: center;
                gap: 10px;
            }
            .escalation-row label {
                min-width: 150px;
                margin: 0;
            }
            .escalation-row input {
                margin: 0;
            }
            .escalation-row .date-label {
                margin-left: 20px;
            }
            .button-container {
                display: flex;
                justify-content: flex-end;
                gap: 10px;
            }
        </style>
        <script type="text/javascript">
            $(function () {
                $(".chzn-select").chosen();
               

                $('#searchinvoice').click(function() {
                    var storeIds = $('#storeIds').val();

                    $.ajax({
                        url: 'ajax/ajax_invoices.php',
                        type: 'GET',
                        data: {storeIds: storeIds},
                        success: function(response) {
                            $('#rentAmount').html(response);
                        },
                        error: function() {
                            alert('An error occurred while calculating the rent. Please try again.');
                        }
                    });

                    return false;
                });
            });
            
            
            
            
        </script>

        <?php
    }

    public function pageContent() {
        $formResult = $this->getFormResult();
        $menuitem = "intransitclear";
        include "sidemenu." . $this->currStore->usertype . ".php";

        ?>

        <div class="grid_10">
            <fieldset>
                <legend>Clear Intransit</legend>
                <div class="grid_12">
                    <div>

                        <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;">
                            <?php
                            if (is_array($formResult->status)) {
                                echo implode(', ', $formResult->status);
                            } else {
                                echo $formResult->status;
                            }
                            ?>
                        </span>

                        <form name="intransitclear" id="intransitclear" style='height:150px;'>
                            
                            <label for="store">*Select store: </label>
                            <select name="storeIds" id="storeIds" data-placeholder="Choose Store" class="chzn-select"  style="width:100%;">
                                <option value="-1">All Stores</option> 
                                <?php
                                $db = new DBConn();
                                $objs = $db->fetchObjectArray("select id, store_name from it_codes where usertype=4 and is_closed=0 order by store_name");
                                foreach ($objs as $obj) {
                                    ?>
                                    <option value="<?php echo $obj->id; ?>"><?php echo $obj->store_name; ?></option>
                                <?php } ?>
                            </select>

                            <div style="height:5%;"></div>



                            <button type="button" id="searchinvoice">Search invoices</button>
                            
                        </form>

                        <div id="rentAmount"></div> <!-- This is where the table will be inserted -->
                    </div>
                </div>
            </fieldset>
        </div>

        <?php
    }
}
?>