<?php
ini_set('max_execution_time', 300);

require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "session_check.php";
require_once "lib/core/strutil.php";
require_once "lib/core/Constants.php";

class cls_ckmembershipenroll_schemestorewise extends cls_renderer {

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
   
        
<script type="text/javascript" src="jqueryui/js/jquery-ui-1.7.1.custom.min.js"></script>
<link rel="stylesheet" href="css/redmond/jquery-ui-1.7.1.custom.css" type="text/css" title="ui-theme" />
<link rel="stylesheet" href="js/chosen/chosen.css" />
<script src="js/chosen/chosen.jquery.js" type="text/javascript"></script>
<script type="text/javascript" src="js/daterangepicker.jQuery.js"></script>
<link rel="stylesheet" href="css/ui.daterangepicker.css" type="text/css" />
<link rel="stylesheet" href="css/redmond/jquery-ui-1.7.1.custom.css" type="text/css" title="ui-theme" />
        <style>
            .container {
                max-width: 600px;
                margin: auto;
                font-family: Arial, sans-serif;
                border: 1px solid #ddd;
                padding: 20px;
                border-radius: 10px;
                background-color: #f9f9f9;
                color: black;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            }
            .form-group {
                margin-bottom: 20px;
            }
            .form-group label {
                font-weight: bold;
                display: block;
                margin-bottom: 5px;
            }
            select, button {
                width: 100%;
                padding: 10px;
                border: 1px solid #ccc;
                border-radius: 5px;
            }
            button {
                background-color: #007bff;
                color: white;
                font-weight: bold;
                cursor: pointer;
                transition: 0.3s;
            }
            button:hover {
                background-color: #0056b3;
            }
        </style>
        <script type="text/javascript">
            $(function () {
                $(".chzn-select").chosen();

                $('#assignschemetostore').click(function () {
                    var storeIds = $('#storeIds').val(); // Get multiple selected store IDs
                    var schemeId = $('#selectScheme').val(); // Get selected scheme ID

                    if (!schemeId || schemeId == "-1") {
                        alert("Please select a valid scheme.");
                        return false;
                    }

                    if (!storeIds || storeIds.length === 0) {
                        alert("Please select at least one store.");
                        return false;
                    }
                $('#assignschemetostore').hide();
                $('#loadingIndicator').show();
                    $.ajax({
                        url: 'ajax/assign_scheme_storewise.php',
                        type: 'POST',
                        data: {
                            storeIds: storeIds.join(","), // Convert array to comma-separated string
                            schemeId: schemeId
                        },
                        success: function (response) {
                            alert(response); // Display success or error message from PHP
                             $('#assignschemetostore').show();
                            $('#loadingIndicator').hide();
                             location.reload();
                        },
                        error: function () {
                            alert('An error occurred while assigning the scheme. Please try again.');
                        }
                    });

                    return false;
                });
            });


        </script>
        <?php
    }

    public function pageContent() {
        $menuitem = "membershipenrollschemstorewise";
        include "sidemenu." . $this->currStore->usertype . ".php";
        $db = new DBConn();
        $schemes = $db->fetchObjectArray("SELECT id, scheme_name FROM membership_scheme_masters WHERE is_scheme_delete=0 AND is_scheme_active=1 ORDER BY scheme_name");
        ?>

        <div class="grid_10">
            <fieldset>
                <legend>Assign Scheme Storewise</legend>
                <div class="container">
              <div style="display: flex; justify-content: flex-end; margin-top: 20px;">
    <button 
        onclick="window.location.href = 'storewise/schemereport';" 
        style="padding: 4px 8px; font-size: 12px; color: #fff; background-color: #007bff; border: none; border-radius: 4px; cursor: pointer; width: auto;">
        Show Storewise Scheme
    </button>
</div>


                    <form id="giftvoucherclear">
                        <div class="form-group">
                            <label for="selectScheme">*Select Scheme:</label>
                            <select id="selectScheme" name="selectScheme">
                                <option value="-1">Select Scheme</option>
                                <?php foreach ($schemes as $scheme) { ?>
                                    <option value="<?php echo $scheme->id; ?>"><?php echo $scheme->scheme_name; ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="storeIds">*Select Store:</label>
                            <select name="storeIds" id="storeIds" class="chzn-select" multiple>
                                <option value="-1">All Stores</option> 
                                <?php
                                $stores = $db->fetchObjectArray("SELECT id, store_name FROM it_codes WHERE usertype=4 AND is_closed=0 and id not in (70,147,160,162,168) ORDER BY store_name");
                                foreach ($stores as $store) {
                                    ?>
                                    <option value="<?php echo $store->id; ?>"><?php echo $store->store_name; ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <br><br><br><br><br><br><br>

                                <?php if ($this->storeid == 67) { ?>
                                    <button type="button" id="assignschemetostore">Assign Scheme To Store</button>
                                    <span id="loadingIndicator" style="display: none;">
                                        <img src="images/loading.gif" alt="Processing..." width="20">
                                        Assign Scheme to store Processing... Please Wait.
                                    </span>
                                <?php } ?>

                    </form>
                    <br>
                   
                </div>
            </fieldset>
        </div>

        <?php
    }
}
?>