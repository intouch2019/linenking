<?php
require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once "session_check.php";
require_once "formpost/MatersStockQtyCalc.php"; ///var/www/html/cottonking/home/formpost/MatersStockQtyCalc.php

//print_r("Hi");exit();
class cls_admin_master_stock_qty extends cls_renderer {

    var $currStore;
    var $params;

    function __construct($params = null) {
        $this->currStore = getCurrUser();
        if (isset($params['storeid'])) {
            $this->store_id = $params['storeid'];
        } else {
            $this->store_id = "";
        }

        if (isset($params['catid'])) {
            $this->cat_id = $params['catid'];
        } else {
            $this->cat_id = "";
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
        <script type="text/javascript" src="js/expand.js"></script>
        <link rel="stylesheet" href="js/chosen/chosen.css" />
        <script src="js/chosen/chosen.jquery.js" type="text/javascript"></script>
        <link rel="stylesheet" href="css/prettyPhoto.css" type="text/css" media="screen" title="prettyPhoto main stylesheet" charset="utf-8" />
        <link rel="stylesheet" type="text/css" href="css/dark-glass/sidebar.css" />
        <script src="js/prettyPhoto/jquery.prettyPhoto.js" type="text/javascript" charset="utf-8"></script>
        <script type="text/javascript" src="js/sidebar/jquery.sidebar.js"></script>

        <script>

            function validateMasterQuantities() {
                // Get the minimum and maximum quantity values
                const minQty = parseInt(document.getElementById('min_qty').value, 10);
                const maxQty = parseInt(document.getElementById('max_qty').value, 10);

                // Get all master quantity input elements
                const masterQuantities = document.querySelectorAll('.master-quantity');

                // Calculate the sum of all master quantities
                let totalMasterQty = 0;
                masterQuantities.forEach(input => {
                    const value = parseInt(input.value, 10) || 0; // Default to 0 if input is empty
                    totalMasterQty += value;
                });

                // Check if the total is within the range
//                if (totalMasterQty < minQty || totalMasterQty > maxQty) {
                if (totalMasterQty > minQty) {
                    // Set the error message in the #statusMsg element
                    const errorStatusMsg = document.getElementById('validationError');
//                    errorStatusMsg.innerText = `The total master quantity (${totalMasterQty}) must be between ${minQty} and ${maxQty}.`;
                    errorStatusMsg.innerText = `The total master quantity (${totalMasterQty}) must be less than or equal to ${minQty}.`;

                    if (maxQty <= minQty) {
                        errorStatusMsg.innerText = `Minimum quantity cannot be greater than maximum quantity.`;
                    }

                    // Make the focusElement visible
                    const focusElement = document.getElementById('validationError');
                    focusElement.style.display = 'block';

                    // Scroll to the error message
                    focusElement.scrollIntoView({behavior: 'smooth', block: 'center'});

                    return false; // Prevent form submission
                }

                // Validation passed
                return true;
            }
            
            
            
            function calculateTotalMasterStock(){
                const masterQuantities = document.querySelectorAll('.master-quantity'); // Get all master quantity input elements
                
                // Calculate the sum of all master quantities
                let totalMasterQty = 0;
                masterQuantities.forEach(input => {
                    const value = parseInt(input.value, 10) || 0; // Default to 0 if input is empty
                    totalMasterQty += value;
                });
                document.getElementById('total_store_master_stock').value=totalMasterQty;                
            }
            


            $(function () {
                $(".chzn-select").chosen();
                $(".chzn-select-deselect").chosen({allow_single_deselect: true});
            });

            function getStoreId() {
                var storeid = $('#store_name').val();
                var catId = $('#cat_name').val();
                if (storeid === 0) {
                    alert("Please select store first!");
                    return;
                }
                if (storeid !== 0) {
                    window.location.href = "admin/master/stock/qty/storeid=" + storeid + "/catid=" + catId;
                    setfocus();
                }
            }
            
            document.addEventListener("DOMContentLoaded", function () {
                const minQtyInput = document.getElementById('min_qty');
                const maxQtyInput = document.getElementById('max_qty');

                // Skip JS update if values are already filled
                minQtyInput.addEventListener('input', function () {
                    const minVal = parseFloat(minQtyInput.value);
                    if (!isNaN(minVal)) {
                        const maxVal = Math.round(minVal * 1.2);
                        maxQtyInput.value = maxVal;
                    } else {
                        maxQtyInput.value = '';
                    }
                });
            });




        </script>

        <style>
            table {
                border: 1px solid black;
                border-collapse: collapse;
            }
            td, th {
                border: 1px solid black;
                padding: 5px;
                text-align: center;
                vertical-align: middle;
            }

            /* Chrome, Safari, Edge, Opera */
            input::-webkit-outer-spin-button,
            input::-webkit-inner-spin-button {
                -webkit-appearance: none;
                margin: 0;
            }
            
            #total_qty {
                font-size: 18px;
                font-weight: bold;
            }
            
            /* Firefox */
            input[type=number] {
                -moz-appearance: textfield;
            }
        </style>

        <?php
    }

    public function pageContent() {
        $menuitem = "masterstackingcapacity";
        include "sidemenu." . $this->currStore->usertype . ".php";
        $db = new DBConn();
        $formResult = $this->getFormResult();
        ?>

        <div class="grid_10">


            <?php if ($formResult) { ?>
                <div style="width: 50%; margin: 0 auto; border-radius: 10px;background-color: white;">
                    <p>
                        <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
                    </p>
                </div>
            <?php } ?>


            <?php if ($this->currStore->usertype == UserType::Admin || $this->currStore->usertype == UserType::CKAdmin || $this->currStore->usertype == UserType::Dealer || $this->currStore->roles == RollType::VM) { ?>
            <div>
                <a href="admin/stores/stackcapacityupdate"><button style=" padding: 8px 16px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; ">Upload Excel</button></a>
            </div><br>
                <div id="storeSelection">
                    <label style="font-size: 15px;"><b>Store:</b></label><br>
                    <select name="store_name" id="store_name" 
                            data-placeholder="Choose Store" 
                            class="chzn-select" 
                            style="width:30%;" 
                            required 
                            onchange="getStoreId()">
                        <option value="0">Select store</option>  
                        <?php
                        if ($this->currStore->usertype == UserType::Dealer) {
                            $objs = $db->fetchObjectArray("select id, store_name from it_codes where usertype=4 and id=" . $this->currStore->id);
                        } else {
                            $objs = $db->fetchObjectArray("select id, store_name from it_codes where usertype=4 and is_closed=0 order by store_name");
                        }

                        foreach ($objs as $obj) {
                            $defaultSel = ($this->store_id == $obj->id) ? "selected" : "";
                            ?>
                            <option value="<?php echo $obj->id; ?>" <?php echo $defaultSel; ?> > <?php echo $obj->store_name; ?></option> 
                        <?php } ?>
                    </select>
                </div> 

                <br>
                <div>
                     <label style="font-size: 15px;"><b>Category:</b></label><br>
                    <select name="cat_name" id="cat_name" 
                            data-placeholder="Choose category" 
                            class="chzn-select" 
                            style="width:30%;" 
                            required 
                            onchange="getStoreId()">
                        <option value="0">Select category</option>  

                <?php 
                $objs = $db->fetchObjectArray("select id, name from it_categories where active=1 order by name");
                foreach ($objs as $obj) {
                    $defaultSel = ($this->cat_id == $obj->id) ? "selected" : "";
                    ?>
                    <option value="<?php echo $obj->id; ?>" <?php echo $defaultSel; ?> > <?php echo $obj->name; ?></option> 
                <?php } 
//                                print_r("Hiii");exit();
                ?>
            </select>
                </div><br><br>
                
            <?php if ($this->cat_id != 0 && $this->store_id != 0) { ?>

                <?php
//                echo '<pre>';
//                print_r("select id,store_name,facade,carpet from it_codes where id=$this->store_id");
//                echo '<pre>';
//                exit();
                $store_obj = $db->fetchObject("select id,store_name,facade,carpet from it_codes where id=$this->store_id");
                $cat_obj = $db->fetchObject("select id,name from it_categories where id=$this->cat_id");
                
                $store_curr_stock_ctgwise = getCurrentStoreStockctgwise($this->store_id, $this->cat_id);
//                print_r("total->  ".$store_curr_stock_ctgwise);exit();
                ?>

                <div>
                    <h3>Store Details</h3>
                    <p><strong>Store Name:</strong> <?php echo $store_obj->store_name; ?></p>
                    <p><strong>Store Facade (Ft):</strong> <?php echo $store_obj->facade; ?></p>
                    <p><strong>Store Carpet Area (Sq Ft):</strong> <?php echo $store_obj->carpet; ?></p>
                <!--                    <p><strong>Stores Min(Qty) Capacity:</strong> <?php // echo $store_obj->min_qty_stock_limit;            ?></p>
                    <p><strong>Stores Max(Qty) Capacity:</strong> <?php // echo $store_obj->max_qty_stock_limit;            ?></p>-->
                </div>

                <form action="formpost/SaveMatersStockQty.php" method="post" onsubmit="return validateMasterQuantities()">
                    <input type="hidden" name="curruser" value=<?php echo $this->currStore->id; ?>>
                    <div class="grid_5">
                        <h3>Category: <?php echo $cat_obj->name; ?></h3>
                        <table>
                            <thead>
                                <tr>
                                    <th>Current Store Qty</th>
                                    <th>Min Qty(Stacking Capacity)</th>
                                    <th>Max Qty(Stacking Capacity)</th>
                                </tr>
                            </thead>
                            <?php
                            $sel_qry = "select min_qty_ctg_wise, max_qty_ctg_wise from stock_limit_ctg_wise where store_id=$this->store_id and category_id=$this->cat_id";
                            $stock_limits_ctg_wise = $db->fetchObject($sel_qry);
                            ?>
                            <tbody>
                                <tr>
                                    <td><?php echo $store_curr_stock_ctgwise; ?></td>
                                    <td><input required type="number" id="min_qty" name="min_qty" value="<?php
                                        if (!empty($stock_limits_ctg_wise->min_qty_ctg_wise)) {
                                            echo $stock_limits_ctg_wise->min_qty_ctg_wise;
                                        }
                                        ?>"
                                               <?php if ($this->currStore->usertype == UserType::Dealer) { ?> disabled <?php } ?>
                                               >
                                    </td>

                                    <td><input required type="number" id="max_qty" name="max_qty" readonly value="<?php
                                        if (!empty($stock_limits_ctg_wise->max_qty_ctg_wise)) {
                                            echo $stock_limits_ctg_wise->max_qty_ctg_wise;
                                        }
                                        ?>"
                                               <?php if ($this->currStore->usertype == UserType::Dealer) { ?> disabled <?php } ?>
                                               >
                                    </td>

                                </tr>
                            </tbody>
                        </table>
                    </div><br>

                    
                    <div>
                        <table>
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Style ⬇ &nbsp;&nbsp;|&nbsp;&nbsp; Size ➡</th>
                                    <?php
                                    $size_by_ctg_qry = "select s.name as size,s.id as id from it_ck_sizes cs inner join it_sizes s on cs.size_id=s.id where ctg_id = $this->cat_id";
                                    $size_by_ctg = $db->fetchObjectArray($size_by_ctg_qry);
                                    foreach ($size_by_ctg as $size) {
                                        ?>
                                        <th><?php echo $size->size ?></th>
                                    <?php } ?>
                                        <th id="total_qty">Total</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php
                                $style_by_ctg_qry = "select s.name as style_name,s.id as id from it_ck_styles cs inner join it_styles s on cs.style_id = s.id and cs.ctg_id = $this->cat_id";
                                $style_by_ctg = $db->fetchObjectArray($style_by_ctg_qry);
                                $i=0;
                                $j = 0;
                                $num_of_rows_generated=0;
                                $total_store_master_stock=0;
                                
// <---------------------------------1 This foreach loops will get total master stock for current store and number of rows generated i.e. no of styles current catagory have ------------------------------>
                                foreach ($style_by_ctg as $style) {
                                    $num_of_rows_generated++;
                                    foreach ($size_by_ctg as $size) {
                                        $store_master_stock = getMaterStoreStock($this->store_id, $this->cat_id, $style->id, $size->id);
                                        $total_store_master_stock+=$store_master_stock;
                                    }
                                }
                                ?>
<!--  <-----------------------------------1 For loop ends ------------------------------------------------------------------------->
                                
                                <!--Row below will contain Current stack-->
                                <tr>
                                    <td rowspan="<?php echo $num_of_rows_generated; ?>" style="text-align: left;">Current Stock</td>

                                    <?php
                                    foreach ($style_by_ctg as $style) {
                                        $total_store_curr_stock=0;
                                        ?>
                                        <td><?php echo $style->style_name; ?></td>
                                        <?php
                                        foreach ($size_by_ctg as $size) {
                                            $store_curr_stock = getCurrentStoreStock($this->store_id, $this->cat_id, $style->id, $size->id);
                                            $total_store_curr_stock+=$store_curr_stock;
                                            ?>
                                            <td><?php echo $store_curr_stock; ?></td>
                                        <?php } ?>
                                            <td><b><?php echo $total_store_curr_stock; ?></b></td>
                                    </tr>
                            
                                    <?php } ?>


                                <!--Row below will contain Stacking Capacity Set(Master Stock)-->
                                <tr>
                                    <td rowspan="<?php echo $num_of_rows_generated; ?>" style="text-align: left;">Stacking Capacity Set</td>

                                    <?php
                                    foreach ($style_by_ctg as $style) {
                                        ?>
                                        <td><?php echo $style->style_name; ?></td>
                                        <?php
                                        foreach ($size_by_ctg as $size) {
                                            $store_master_stock = getMaterStoreStock($this->store_id, $this->cat_id, $style->id, $size->id);
                                            $j++;
                                            ?>
                                            <td>
                                                <input type="number" 
                                                       class="master-quantity" 
                                                       id="<?php echo "store_master_stock_" . $j; ?>" 
                                                       min="0" 
                                                       max="300" 
                                                       value="<?php echo $store_master_stock; ?>" 
                                                       style="width: 30px;" 
                                                       name="style_<?php echo $style->id . "size_" . $size->id; ?>"
                                                       <?php if ($this->currStore->usertype == UserType::Dealer) { ?> disabled <?php } ?>
                                                       onchange="calculateTotalMasterStock()">
                                            </td>
                                            
                                        <?php } ?>
                                        <?php 
                                            if(($num_of_rows_generated==2 || $num_of_rows_generated==1) && $i==0){
                                                $i++;
                                                ?>
                                                    <td rowspan="<?php echo $num_of_rows_generated; ?>">
                                                        <b>
                                                            <input type="number" 
                                                                   style="width: 30px;" 
                                                                   id="total_store_master_stock" 
                                                                   value="<?php echo $total_store_master_stock; ?>" 
                                                                   disabled
                                                            >  
                                                        </b>
                                                    </td>
                                                <?php
                                            } 
                                        ?> 
                                    </tr>
                              <?php } ?>
                                        
                            <input type="hidden" name="cat_id" id="cat_id" value="<?php echo $this->cat_id; ?>">
                            <input type="hidden" name="store_id" id="store_id" value="<?php echo $this->store_id; ?>">
                            </tbody>
                        </table>
                    </div>


                    <div style="width: 50%; margin: 0 auto; border-radius: 10px;background-color: white;">
                        <p>
                            <span id="validationError" style="color: red; display: none;" ></span>
                        </p>
                    </div>

                    <br>
                    <button id="submitform" type="submit">Submit</button>
                </form>
            <?php } ?>
        <?php } ?>
        </div>
        <?php
    }
}
?>


