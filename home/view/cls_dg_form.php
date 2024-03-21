<?php
ini_set('max_execution_time', 300);

require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "session_check.php";
require_once "lib/core/strutil.php";

class cls_dg_form extends cls_renderer {

    var $currStore;
    var $storeid;
    var $params;

    function __construct($params = null) {

        if (isset($_SESSION['selectedStoreId'])) {
            $this->selectedStoreId = $_SESSION['selectedStoreId'];
        }

        if (isset($params['id'])) {
            $this->id = $params['id'];
        } else {
            $this->id = "";
        }
        $this->currStore = getCurrUser();
        $this->storeid = $this->currStore->id;
    }

    function extraHeaders() {
        if (!$this->currStore) {
            return;
        }
        ?>



        <script type="text/javascript" src="jqueryui/js/jquery-ui-1.7.1.custom.min.js"></script>
        <script type="text/javascript" src="js/daterangepicker.jQuery.js"></script>
        <link rel="stylesheet" href="css/ui.daterangepicker.css" type="text/css" />
        <link rel="stylesheet" href="css/redmond/jquery-ui-1.7.1.custom.css" type="text/css" title="ui-theme" />


        <link rel="stylesheet" href="js/chosen/chosen.css" />
        <script src="js/chosen/chosen.jquery.js" type="text/javascript"></script>
        <link rel="stylesheet" href="css/prettyPhoto.css" type="text/css" media="screen" title="prettyPhoto main stylesheet" charset="utf-8" />
        <link rel="stylesheet" type="text/css" href="css/dark-glass/sidebar.css" />
        <script src="js/prettyPhoto/jquery.prettyPhoto.js" type="text/javascript" charset="utf-8"></script>
        <script type="text/javascript" src="js/sidebar/jquery.sidebar.js"></script>
        <style>
            .checkbox{
                font-size: 20px;
            }
            /*            span{
                            color: red;
                        }*/
        </style>
        <script type="text/javascript">

            $(function () {
                $(".chzn-select").chosen();
                $(".chzn-select-deselect").chosen({allow_single_deselect: true});
            });

            function getSelectedStoreId() {
                const selectedStoreId = document.getElementById('exchange_given_at_store').value;
                //                                alert(selectedStoreId);
                $.ajax({
                    url: "ajax/getstoreaddress.php?storeid=" + selectedStoreId,

                    success: function (response) {

                        if (response !== "") {
                            document.getElementById("st_address").value = response;

                        }
                    }
                });
            }



            function getBarcode() {
                const designNo = document.getElementById('design_no').value;
                const size = document.getElementById('size').value;
                const style = document.getElementById('style').value;
                document.getElementById('mrp').value = "";
                document.getElementById('barcode').innerHTML = "";

                if (size === "" || size === null) {
                    document.getElementById('size_error').innerHTML = "Please Enter size first!";
                    document.getElementById('size_error').focus();
                    document.getElementById('style').value = "";
                    return false;
                } else {
                    document.getElementById('size_error').innerHTML = "";
                }

                if (designNo === "" || designNo === null) {
                    document.getElementById('design_no_error').innerHTML = "Please Enter Design No first!";
                    document.getElementById('design_no_error').focus();
                    document.getElementById('style').value = "";
                    return false;
                } else {
                    document.getElementById('design_no_error').innerHTML = "";
                }

                const values = designNo + "/" + size + "/" + style;

                $.ajax({
                    url: "ajax/getMRPBarcode.php?values=" + values,
                    success: function (response) {
        //            alert(response);
                        if (response !== "NA") {
                            const barcodesMrps = response.split(';');
                            const barcodeDropdown = document.getElementById('barcode');

                            barcodeDropdown.innerHTML = ""; // Clear existing options

                            // Create initial "Select Barcode" option
                            const selectBarcodeOption = document.createElement('option');
                            selectBarcodeOption.value = ""; // You can set the value to empty string or any default value
                            selectBarcodeOption.textContent = "Select Barcode";
                            barcodeDropdown.appendChild(selectBarcodeOption);
                            selectBarcodeOption.selected = true;

                            for (let i = 0; i < barcodesMrps.length - 1; i += 2) {
                                const barcode = barcodesMrps[i];

                                const barcodeOption = document.createElement('option');
                                barcodeOption.value = barcode;
                                barcodeOption.textContent = barcode;
                                barcodeDropdown.appendChild(barcodeOption);

                            }
                        } else {
                            alert("Not Found");
                        }
                    }
                });
            }

            function getMRP() {
                const barcode = document.getElementById('barcode').value;
        //    alert(barcode);
        //    return;

                const values = barcode;

                $.ajax({
                    url: "ajax/getMRPByBarcode.php?values=" + values,
                    success: function (response) {
                        if (response !== "Not Found") {
        //                alert(response);
                            // Set the response value to the input field with id "mrp"
                            document.getElementById('mrp').value = response;
                        } else {
                            alert("Barcode Not Found");
                        }
                    }
                });
            }




            function isRemarkVisible() {
                const otherCheckBox = document.getElementById("other");
                if (otherCheckBox.checked) {
                    document.getElementById("remarkTextField").style.display = "block";
                } else {
                    document.getElementById("remarkTextField").style.display = "none";
                }

            }


            function checkBarcode() {
                const barcode = document.getElementById('barcode').value;
                const mrp = document.getElementById('mrp').value;
                if (!validateNumber(barcode)) {
        //                    alert("inside barcode if condition");
                    document.getElementById('barcode_error').innerHTML = "Make sure value entered for Design no, size and style is correct !";
                    document.getElementById('barcode_error').focus();
                    return false;
                } else {
                    document.getElementById('barcode_error').innerHTML = "";
                }

                if (!validateNumber(mrp)) {
                    document.getElementById('mrp_error').innerHTML = "Make sure value entered for Design no, size and style is correct !";
                    document.getElementById('mrp_error').focus();
                    return false;
                } else {
                    document.getElementById('mrp_error').innerHTML = "";
                }
            }


            function validateNumber(phoneNumber) {
                // Regular expression pattern for phone number validation (numbers only)
                var pattern = /^\d+$/;

                // Check if the phone number matches the pattern
                return pattern.test(phoneNumber);
            }

            function setStyleDefault()
            {
                document.getElementById('style').value = "";
            }


            function checkFormValidations() {
                const custMobNo = document.getElementById('c_mobile_no').value;
                const storeManagerMobNo = document.getElementById('store_manager_mob_no').value;
                const exchangeGivenAtStore = document.getElementById('exchange_given_at_store').value;
                const size = document.getElementById('size').value;
                 var dgformid=document.getElementById('dggarmentreportid').value;

                if (exchangeGivenAtStore === "0") {
                    //                    alert("exchange given at store not selected-->"+exchangeGivenAtStore);
                    document.getElementById('storeError').innerHTML = "Please select value from dropdown !";
                    document.getElementById('storeError').focus();
                    return false;
                } else {
                    document.getElementById('storeError').innerHTML = "";
                }


                if (!validateNumber(custMobNo)) {
                    document.getElementById('c_mobile_no_error').innerHTML = "Please enter numbers only !";
                    document.getElementById('c_mobile_no_error').focus();
                    return false;
                } else {
                    document.getElementById('c_mobile_no_error').innerHTML = "";
                }


                if (!validateNumber(storeManagerMobNo)) {
                    document.getElementById('store_manager_mob_no_error').innerHTML = "Please enter numbers only !";
                    document.getElementById('store_manager_mob_no_error').focus();
                    return false;
                } else {
                    document.getElementById('store_manager_mob_no_error').innerHTML = "";
                }


                if (!validateNumber(size)) {
                    document.getElementById('size_error').innerHTML = "Please enter numbers only !";
                    document.getElementById('size_error').focus();
                    return false;
                } else {
                    document.getElementById('size_error').innerHTML = "";
                }


                const barcode = document.getElementById('barcode').value;
                const mrp = document.getElementById('mrp').value;
          

                var checkboxes = document.querySelectorAll('input[type="checkbox"]');
                var isChecked = false;
                for (var i = 0; i < checkboxes.length; i++) {
                    if (checkboxes[i].checked) {
                        isChecked = true;
                        break;
                    }
                }


                if (!isChecked) {
                    document.getElementById('checkBoxError').innerHTML = "Please check at least one checkbox in defects !";
                    document.getElementById('checkBoxError').focus();
                    return false;
                } else {
                    document.getElementById('checkBoxError').innerHTML = "";
                }


                if (document.getElementById('other').checked) {
                    const remark = document.getElementById('remarkTxtArea').value;
                    if (remark === null || remark === "") {
                        document.getElementById('remarkTextFieldError').innerHTML = "If Other checkbox is selected kindly mention the defect details in Remark !";
                        document.getElementById('remarkTextFieldError').focus();
                        return false;
                    }
                }
                const isConfirmed = confirm("Are you sure you want to submit form?");
                if (isConfirmed) {
                    document.getElementById('dgReturnForm').submit();
                } else {
                    return false;
                }
                return false;
            }
             function thermalReceipt() {
            const id=document.getElementById('dggarmentreportid').value;    
            alert("hii");
                $.ajax({
                    url: "ajax/printThermalDGReport.php?id="+id,

                success: function (response) {
                    if (response !== "") {
                        var popupWin = window.open();
                        popupWin.document.open();
                        popupWin.document.write(response);
                        popupWin.document.close();
                    }
                }
                });
            }


        </script>



        <?php
    }

    //extra-headers close
    public function pageContent() {

        $formResult = $this->getFormResult();
        $menuitem = "dgForm";
        include "sidemenu." . $this->currStore->usertype . ".php";
        $sdate = "";
        $edate = "";
        $write_htm = true;

        $month = date('m');
        $day = date('d');
        $year = date('Y');
        $today = $year . '-' . $month . '-' . $day;
        ?>

        <div class="grid_10">
            <fieldset>
                <legend>Defective Garment Form</legend>
                <div class="grid_12">
                    <div>
                        <label>* - required fields</label><br><br>

                        <?php if ($formResult) { ?>
                            <p>
                                <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
                            </p>
                            <?php if($formResult->status == "Form has been saved successfully."){?>
                            <script>
                            // Automatically call thermalReceipt() when page loads
                            document.addEventListener("DOMContentLoaded", function() {
                            thermalReceipt();
                            });
        </script>
                           <?php }
                           else { ?>
                              <script>
                              console.log("in else");
                              </script>
                          <?php }
?>
                        <?php } ?>
                        <form action="formpost/dgForm.php" method="POST" name="dgForm" id="dgReturnForm" onsubmit="return checkFormValidations()">


                            <input type="hidden" value="<?php echo $this->id?>" id="dggarmentreportid">
                            <h1 style="margin-left: 50px">CUSTOMER DEFECT RETURN FORM</h1><br><br>


                            <div style="margin-left: 10px;">
                                <label for="date">*Date: </label>                        
                                <input type="date" name="dg_form_date"  value="<?php echo $today; ?>" disabled > <br><br>
                            </div>

                                                                <!--<p class="grid_7"></p>-->

                            <p class="grid_7">
                                <label for="cust_name">*Customer Name: </label>
                                <input type="text" name="cust_name"  id="cust_name" value="<?php echo isset($_POST["cust_name"]) ? $_POST["cust_name"] : ''; ?>" required>
                            </p>
                            <p class="grid_7">
                                <label for="c_mobile_no">*Customer Mobile No: </label>                        
                                <input maxlength="10" minlength="10" type="text" id="c_mobile_no" required name="c_mobile_no"  value="<?php echo isset($_POST["c_mobile_no"]) ? $_POST["c_mobile_no"] : ''; ?>" >
                                <span tabindex="0" id="c_mobile_no_error" style="color:#FFCCCB; font-size: 14px;"></span>
                            </p>    
                            <p class="grid_7">
                                <label for="c_old_bill_no">Customer Old Bill No: </label>                        
                                <input type="text" name="c_old_bill_no"  value=""  >
                            </p>    
                            <p class="grid_3">
                                <label for="date">Old Bill Date: </label>                        
                                <input type="date" name="c_old_bill_no_date"  value=""  > 
                            </p>

                            <p class="grid_7">
                                <label for="og_purchase_store_name">Original Purchase Store Name: </label>                        
                                <select name="og_purchase_store_name" id="og_purchase_store_name" 
                                        data-placeholder="Choose Store" 
                                        class="chzn-select" 
                                        style="width:100%;">
                                    <option value="">Select store</option>  
                                    <?php
                                    if ($this->storeid == - 1) {
                                        $defaultSel = "selected";
                                    } else {
                                        $defaultSel = "";
                                    }

                                    $db = new DBConn();
                                    if ($this->currStore->usertype == UserType::Admin || $this->currStore->usertype == UserType::CKAdmin || $this->currStore->usertype == UserType::Accounts || $this->currStore->usertype == UserType::Dispatcher || $this->currStore->usertype == UserType::Picker || $this->currStore->usertype == UserType::Dealer || $this->currStore->usertype == UserType::Manager) {
                                        $objs = $db->fetchObjectArray("select id,store_name from it_codes where usertype=4 and is_closed=0 order by store_name");
                                    } else {
                                        $objs = $db->fetchObjectArray("select id,store_name from it_codes where id=" . $this->currStore->id . " and is_closed=0");
                                    }
                                    foreach ($objs as $obj) {
                                        ?>
                                        <option value="<?php echo $obj->store_name; ?>" <?php echo $defaultSel; ?> > <?php echo $obj->store_name; ?></option> 
                                    <?php }
                                    ?>
                                </select>
                            </p>  

                            <p class="grid_7">
                                <label for="exg_bill_no">*Exchange Bill No.: </label>                        
                                <input type="text" required name="exg_bill_no"  value=""  >
                            </p>  

                            <p class="grid_3">
                                <label for="date">*Exchange Bill Date: </label>                        
                                <input type="date" required name="exg_bill_no_date"  value=""  > 
                            </p>

                            <p class="grid_7">
                                <label for="store">*Exchange given at the store: </label>
                                <select name="exchange_given_at_store" id="exchange_given_at_store" 
                                        data-placeholder="Choose Store" 
                                        class="chzn-select" 
                                        style="width:100%;" 
                                        required 
                                        onchange="getSelectedStoreId()" 
                                        >
                                    <option value="0">Select store</option>  
                                    <?php
                                    if ($this->storeid == - 1) {
                                        $defaultSel = "selected";
                                    } else {
                                        $defaultSel = "";
                                    }


//                                    $db = new DBConn();
                                    if ($this->currStore->usertype == UserType::Admin || $this->currStore->usertype == UserType::CKAdmin || $this->currStore->usertype == UserType::Accounts || $this->currStore->usertype == UserType::Dispatcher || $this->currStore->usertype == UserType::Picker || $this->currStore->usertype == UserType::Manager) {
                                        $objs = $db->fetchObjectArray("select id,store_name from it_codes where usertype=4 and is_closed=0 order by store_name");
                                    } else {
                                        $objs = $db->fetchObjectArray("select id,store_name from it_codes where id=" . $this->currStore->id . " and is_closed=0");
                                    }
                                    foreach ($objs as $obj) {
                                        ?>
                                        <option value="<?php echo $obj->id; ?>" <?php echo $defaultSel; ?> > <?php echo $obj->store_name; ?></option> 
                                    <?php } ?>
                                </select>
                                <span tabindex="0" id="storeError" style="color:#FFCCCB; font-size: 14px;"></span>

                            </p> 

                            <p class="grid_7">
                                <label for="st_address_stamp">*Store Address: </label>                        
                                <textarea name="st_address" required id="st_address"  value=""  row="2" style="width:100% ; padding:5px" placeholder="Select Exchange given at store to auto fill address"></textarea>
                            </p>  

                            <p class="grid_7">
                                <label for="st_manager_name">*Store Manager Name: </label>                        
                                <input type="text" required name="st_manager_name"  value=""  >
                            </p>  

                            <p class="grid_3">
                                <label for="st_manager_mob_no">*Store Manager Mobile No: </label>                        
                                <input maxlength="10" minlength="10" type="text" id="store_manager_mob_no" required name="st_manager_mob_no"  value="">
                                <span tabindex="0" id="store_manager_mob_no_error" style="color:#FFCCCB; font-size: 14px;"></span>
                            </p> 

                            <p class="grid_7">
                                <label for="prod">*Product: </label>                        
                                <select name="prod" id="prod" required>
                                    <option value="">Select product</option>  
                                    <?php
                                    $objs = $db->fetchObjectArray("select name from it_categories where name in ('Formal Shirt','Regular Shirt','Semi Formal','Slim Formal','Slim Casual','Slim Shirt','Salwar','T-Shirt','Trouser','Narrow Trouser','Stylized Trouser','Casual Shirt','Jeans','Jacket','Pajama','Long Kurta','Short Kurta')");
                                    foreach ($objs as $obj) {
                                        echo '<option value="' . $obj->name . '">' . $obj->name . '</option>';
                                    }
                                    ?>
                                </select>
                            </p>

                            <p class="grid_3">
                                <label for="prod">*Design No: </label>                        
                                <input type="text" required name="design_no" id="design_no"  value="" onkeypress="setStyleDefault()">
                                <span tabindex="0" id="design_no_error" style="color:#FFCCCB; font-size: 14px;"></span>
                            </p> 


                            <p class="grid_3">
                                <label for="size">*Size: </label>                        
                                <input maxlength="2" minlength="2" type="text" required name="size" id="size"  value="" onkeypress="setStyleDefault()">
                                <span tabindex="0" id="size_error" style="color:#FFCCCB; font-size: 14px;"></span>
                            </p>


                        <p class="grid_7">
                                <label for="style">*Style: </label>                        
                                <select name="style" required id="style" onchange="getBarcode()">
                                    <option value="">Select Style</option>
<!--                                    <option value="Half Sleeve">Half Sleeve</option>
                                    <option value="Full Sleeve">Full Sleeve</option>
                                    <option value="NP">NP</option>
                                    <option value="PL">PL</option>-->
        <?php
        $objs = $db->fetchObjectArray("select name from it_styles where name in ('FS','HS','NP','PL')");
        foreach ($objs as $obj) {
            echo '<option value="' . $obj->name . '">' . $obj->name . '</option>';
        }
        ?>
                                </select>


                                            
                            <p class="grid_3">
                                        <label for="barcode">*Barcode: </label>                        
                                        <select name="barcode" required id="barcode" onChange="getMRP()">
                                            <option  value="">Select Barcode</option>
                                        </select>
                                    </p> 
                                    <p class="grid_3">
                                        <label for="mrp">*MRP: </label>                        
                                        <input type="text" required name="mrp" required id="mrp" value=""  readonly>
                                    </p>
  




                            <h3 class="grid_7">*Defects:</h3>

                            <div class="grid_7 checkbox" style="font-size: 16px;">
                                <input type="checkbox" name="color_fade" value="Color Fade" id="color_fade" >
                                <label for="colorFade">1.Color Fade </label><br>

                                <input type="checkbox" name="pilling" value="Pilling" id="pilling" >
                                <label for="pilling1"> 2.Pilling</label><br>

                                <input type="checkbox" name="slippage" value="Slippage" id="slippage" >
                                <label for="slippage"> 3.Slippage</label><br>

                                <input type="checkbox" name="shrinkage" value="Shrinkage" id="shrinkage" >
                                <label for="shrinkage">4.Shrinkage</label><br>

                                <input type="checkbox" name="tearing" value="Tearing" id="tearing" >
                                <label for="tearing">5.Tearing</label><br>

                                <input type="checkbox" name="staining" value="Staining" id="staining" >
                                <label for="staining">6.Staining</label><br>

                                <input type="checkbox" name="soap_mark" value="Soap mark" id="soap_mark" >
                                <label for="soapMark3">7.Soap Mark</label><br>

                                <input type="checkbox" name="cust_defect_but_change_for_service"  value="Customer Defect But Change For Service" id="cust_defect_but_change_for_service">
                                <label for="cust_def_but_chg_for_service">8.Customer Defect But Change For Service</label><br>

                                <input type="checkbox" name="other"  value="Other" id="other" onchange="isRemarkVisible()">
                                <label for="other">9.Other</label><br>

                                <span tabindex="0" id="checkBoxError" style="color:#FFCCCB; font-size: 14px;"></span>





                                                    <!--<p class="grid_7" id="remark"  style="display: none;">-->
                                <p class="grid_7" style="display: none;" id="remarkTextField">
                                    <label for="remark">Remark: </label>                        
                                    <textarea id="remarkTxtArea" name="remarkTxtArea" rows="5" cols="100"></textarea>
                                    <span tabindex="0" id="remarkTextFieldError" style="color:#FFCCCB; font-size: 14px;"></span>
                                </p> 
                            </div> 


                            <p class="grid_7"  style="margin-left: 15%;">
                                <input type="submit" value="Submit" style="width:25%" >
                                <input type="reset" value="Reset" style="width:25%">
                            </p>                               
                        </form>

                    </div>
                </div>
            </fieldset>
        </div>
        <?php
    }
}
?>
</div>