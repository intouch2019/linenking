<?php
ini_set('max_execution_time', 300);

require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "session_check.php";
require_once "lib/core/strutil.php";
require_once "lib/core/Constants.php";

class cls_enrollmembersscheme_masters extends cls_renderer {

    var $currStore;
    var $storeid;

    function __construct() {


        if (isset($_SESSION['selectedStoreId'])) {
            $this->selectedStoreId = $_SESSION['selectedStoreId'];
        }

        $this->currStore = getCurrUser();
        $this->storeid = $this->currStore->id;
    }

    function extraHeaders() {
        if (!$this->currStore) {
            return;
        }
        ?>

        <style>
            .form-container {
                width: 80%;
                margin: auto;
                padding: 20px;
                background: #f8f8f8;
                border-radius: 10px;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            }
            .form-row {
                display: flex;
                align-items: center;
                gap: 15px;
                margin-bottom: 10px;
            }
            .form-row label {
                min-width: 180px;
                font-weight: bold;
            }
            .form-row input, .form-row select {
                padding: 8px;
                border: 1px solid #ccc;
                border-radius: 5px;
                width: 100%;
            }
            .button-container {
                text-align: right;
                margin-top: 20px;
            }
            .button-container button {
                padding: 10px 20px;
                background: #007bff;
                color: white;
                border: none;
                border-radius: 5px;
                cursor: pointer;
            }
            .button-container button:hover {
                background: #0056b3;
            }
        </style>

       
        <script src="https://code.jquery.com/jquery-1.8.3.min.js"></script>

        <script type="text/javascript">
            $(document).ready(function () {
                $("#selectSchemeRow").hide();

                $("#schemeAction").change(function () {
                    var action = $(this).val();
                    if (action === "update") {
                        $("#selectSchemeRow").show();
                    } else {
                        $("#selectSchemeRow").hide();
                        clearForm();
                    }
                });

                $("#selectScheme").change(function () {
                    var schemeId = $(this).val();
                    //    alert(schemeId);

                    if (schemeId !== "-1") {
                        $.ajax({
                            url: 'ajax/fetch_scheme_details.php',
                            type: 'POST', // Changed from POST to GET
                            data: {schemeId: schemeId}, // Data is now passed as query parameters
                            dataType: "json",
                            success: function (response) {
                                if (response.success) {
                                    $("#schemeName").val(response.data.schemeName).prop("readonly", true);
                                    $("#schemeMinAmount").val(response.data.schemeMinAmount);
                                    $("#discountValue").val(response.data.discountValue);
                                    $("#ckEnrollmentFee").val(response.data.ckEnrollmentFee);
                                    $("#start_date").val(response.data.start_date.split("-").reverse().join("-"));
                                    $("#end_date").val(response.data.end_date.split("-").reverse().join("-"));
                                    $("input[name=schemeActive][value=" + response.data.schemeActive + "]").prop("checked", true);
                                } else {
                                    alert(response.message);
                                }
                            },
                            error: function () {
                                alert("Failed to retrieve data.");
                            }
                        });
                    } else {
                        clearForm();
                    }
                });



                function clearForm() {
                    $("#schemeName, #schemeMinAmount, #discountValue, #ckEnrollmentFee, #start_date, #end_date").val("");
                    $("input[name=schemeActive][value='1']").prop("checked", true);
                }
            });
            
            
    function deleteScheme() {
    let schemeId = $("#selectScheme").val(); // Get selected scheme ID

    if (schemeId === "-1" || !schemeId) {
        alert("Please select a scheme to delete.");
        location.reload();
        return;
    }

    if (confirm("Are you sure you want to delete this scheme?")) {
        $.ajax({
            url: "ajax/delete_ckmembership_scheme.php",
            type: "POST",
            data: { schemeId: schemeId },
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    alert("Scheme deleted successfully!");
                    location.reload();
                } else {
                    alert("Error: " + response.message);
                }
            },
            error: function (xhr, status, error) {
                console.log("AJAX Error:", status, error);
                console.log("Response Text:", xhr.responseText);
                alert("Request failed: " + xhr.responseText);
            }
        });
    }
}

        </script>

        <?php
    }

    public function pageContent() {
        $formResult = $this->getFormResult();
        $menuitem = "enrollmemschememasters";
        include "sidemenu." . $this->currStore->usertype . ".php";

        $db = new DBConn();
        $schemes = $db->fetchObjectArray("SELECT id, scheme_name FROM membership_scheme_masters where is_scheme_delete=0 ORDER BY scheme_name");
        ?>

        <div class="grid_10">
            <fieldset>
                <legend>Scheme's Master Set for Store (CK Membership Enroll)</legend>
                <div class="form-container">
                    <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;">
                        <?php echo is_array($formResult->status) ? implode(', ', $formResult->status) : $formResult->status; ?>
                    </span>
                    <button 
    onclick="window.location.href = 'ckmembershipenroll/schemestorewise';" 
    style="padding: 10px 20px; font-size: 16px; color: #fff; background-color: #007bff; border: none; border-radius: 4px; cursor: pointer;">
    Go to Storewise Scheme
</button>

        <button id="deleteSchemeBtn" 
        onclick="deleteScheme()" 
        style="background-color: red; color: black; padding: 10px; border: none; cursor: pointer; float: right;">
    Delete Scheme
</button>
<br><br><br><br><br>

                    <form action="formpost/save_ckmembership_schememasters.php" method="post" id="ckmembershipscheme">
                        <?php if (isset($_SESSION['form_success']) || isset($_SESSION['form_error'])): ?>
                            <div class="message-box" style="padding: 10px; margin-bottom: 10px; font-weight: bold; text-align: center;
                                 <?php echo isset($_SESSION['form_success']) ? 'background-color: #d4edda; color: #155724;' : 'background-color: #f8d7da; color: #721c24;'; ?>">
                                     <?php
                                     if (isset($_SESSION['form_success'])) {
                                         echo $_SESSION['form_success'];
                                         unset($_SESSION['form_success']);
                                     }
                                     if (isset($_SESSION['form_error'])) {
                                         echo $_SESSION['form_error'];
                                         unset($_SESSION['form_error']);
                                     }
                                     ?>
                            </div>
                        <?php endif; ?>

                        <div class="form-row">
                            <label for="schemeAction" style="color: black; font-weight: bold;">*Action:</label>
                            <select id="schemeAction" name="schemeAction" required>
                                <option value="">Select Action</option>
                                <option value="new">Create New Scheme</option>
                                <option value="update">Change in Existing Scheme</option>
                            </select>
                        </div>

                        <div class="form-row" id="selectSchemeRow">
                            <label for="selectScheme" style="color: black; font-weight: bold;">*Select Scheme:</label>
                            <select id="selectScheme" name="selectScheme">
                                <option value="-1">Select Scheme</option>
                                <?php foreach ($schemes as $scheme) { ?>
                                    <option value="<?php echo $scheme->id; ?>"><?php echo $scheme->scheme_name; ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="form-row">
                            <label for="schemeName" style="color: black; font-weight: bold;">*Scheme Name:</label>
                            <input type="text" id="schemeName" name="schemeName" maxlength="50" required>
                        </div>

                        <div class="form-row">
                            <label for="schemeMinAmount" style="color: black; font-weight: bold;">*Minimum Bill Amount:</label>
                            <input type="number" id="schemeMinAmount" name="schemeMinAmount" required>
                        </div>

                        <div class="form-row">
                            <label for="discountValue" style="color: black; font-weight: bold;">*Discount Value:</label>
                            <input type="number" id="discountValue" name="discountValue" required>
                        </div>

                        <div class="form-row">
                            <label for="ckEnrollmentFee" style="color: black; font-weight: bold;">*CK Membership Enrollment Fee:</label>
                            <input type="number" id="ckEnrollmentFee" name="ckEnrollmentFee" required>
                        </div>

                        <div class="form-row">
                            <label for="start_date" style="color: black; font-weight: bold;">*Scheme Start Date & Time:</label>
                            <input type="date" id="start_date" name="start_date" step="1" required>
                        </div>

                        <div class="form-row">
                            <label for="end_date" style="color: black; font-weight: bold;">*Scheme End Date & Time:</label>
                            <input type="date" id="end_date" name="end_date" step="1" required>
                        </div>


                        <div class="form-row">
                            <label style="color: black; font-weight: bold;">*Is Scheme Active:</label>
                            <input type="radio" id="schemeActiveYes" name="schemeActive" value="1" checked>
                            <label for="schemeActiveYes" style="color: black; font-weight: bold;">Yes</label>
                            <input type="radio" id="schemeActiveNo" name="schemeActive" value="0">
                            <label for="schemeActiveNo" style="color: black; font-weight: bold;">No</label>
                        </div>

                        <div class="button-container">
                            <?php if ($this->storeid == 107 || $this->storeid ==90 || $this->storeid ==68) { ?>
                                <button type="submit">Save Changes</button>
                            <?php } ?>
                        </div>
                    </form>
                </div>
            </fieldset>
        </div>

        <?php
    }
}
?>
