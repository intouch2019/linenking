<?php
//ini_set('max_execution_time', 300);

require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "session_check.php";
require_once "lib/core/strutil.php";
require_once "lib/items/clsItems.php";
require_once "lib/core/Constants.php";

class cls_admin_generategrnpdf extends cls_renderer {

    var $currStore;
    var $storeid;
    var $selectedDesignIds = array();

    function __construct() {
        if (isset($_SESSION['selectedStoreId'])) {
            $this->selectedStoreId = $_SESSION['selectedStoreId'];
        }
        $this->currStore = getCurrUser();
        $this->storeid = $this->currStore->id;

        // Capture selected design IDs if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//            print_r($_POST['designIds']);exit();
            if (isset($_POST['designIds'])) {
                $this->selectedDesignIds = $_POST['designIds']; // store selected values in an array
            }
        }
    }

    function extraHeaders() {
        if (!$this->currStore) {
            return;
        }
        ?>
        <style>
            .checkbox {
                font-size: 20px;
            }
            .form-row {
                display: flex;
                align-items: center;
                gap: 20px;
            }
            .form-row label {
                min-width: 150px;
            }
        </style>

        <script type="text/javascript">
            function getSelectedValues() {
                var selectElement = document.getElementById("designos");
                var selectedValues = Array.from(selectElement.selectedOptions).map(option => option.value);
                console.log(selectedValues);
                document.getElementById("result").innerHTML = "Selected Values: " + selectedValues.join(", ");
            }

            function generatepdf(a) {
        //                console.log(a);
                        window.alert(a);
                $.ajax({
                    url: 'ajax/regenerategrnpdf.php',
                    type: 'GET',
                    data: {ids: a},
                    success: function (response) {

                        window.alert("PDF genrated successfully");
//                        resetChosenSelect(); // Reset select field
//                        resetChosenChoices();
                        location.reload();
                    },
                    error: function () {
                        alert('An error occurred while calculating the rent. Please try again.');
                    }
                });
            }



        </script>

        <script src="js/chosen/chosen.jquery.js" type="text/javascript"></script>
        <link rel="stylesheet" href="js/chosen/chosen.css" />
        <script type="text/javascript">
            $(document).ready(function () {
                $(".chzn-select").chosen();
            });

        </script>
        <?php
    }

    public function pageContent() {
        $formResult = $this->getFormResult();
        $menuitem = "generategrnpdf";
        include "sidemenu." . $this->currStore->usertype . ".php";

        $db = new DBConn(); // Assuming you have a DB connection setup
        ?>

        <div class="grid_10">
            <form name="designSel" id="designSel" method="POST" action="" style="display:inline;">
                <h5>Select Design(s) : </h5>
                <select id="designos" name="designIds[]" style="width:75%" class="chzn-select" multiple > 
                    <option value="-1">Select</option>
                    <?php
                $date = date("Y-m-d");
//                    $date = '2024-10-23';
                    $query = "select d.id as design_id,d.design_no,c.name from it_ck_designs d , it_categories c , it_items i  where d.ctg_id = c.id and (i.createtime>='$date 00:00:00' or i.updatetime>='$date 00:00:00') and i.design_id = d.id and i.barcode like '89%'  and d.image is not null and d.image != '' group by d.id";
//                    echo $query;exit();
                    $objs = $db->fetchObjectArray($query);
                    foreach ($objs as $obj) {
                        $selected = in_array($obj->design_id, $this->selectedDesignIds) ? "selected" : "";
                        $option_value = $obj->design_no . " ( " . $obj->name . " ) ";
                        ?>
                        <option value="<?php echo $obj->design_id; ?>" <?php echo $selected; ?>><?php echo $option_value; ?></option>
                    <?php } ?>
                </select>
                <br/><br/>
                <button type="submit" onclick="getSelectedValues()">Submit</button>

            </form>

            <!-- Display selected values here -->
            <div id="result"></div>

            <?php
            // Show the selected design IDs (after form submission)
            $a = "";
            if (!empty($this->selectedDesignIds)) {
                echo "<h5>Selected Designs:</h5>";
                echo "<ul>";
                foreach ($this->selectedDesignIds as $designId) {
                    $a .= "$designId,";
                    echo "$designId,";
                }
                echo "</ul>";
            }
            $seldesign = rtrim($a, ',');
            ?>
            <button onclick='generatepdf("<?php echo $seldesign; ?>")'>Generate PDF</button>

        </div>

        <?php
    }

}
?>