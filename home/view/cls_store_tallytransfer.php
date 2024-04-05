<?php
require_once "view/cls_renderer.php";
require_once "lib/items/clsItems.php";
require_once "lib/core/Constants.php";
require_once ("lib/db/DBConn.php");

class cls_store_tallytransfer extends cls_renderer {

//    var $params;
    var $currStore;

    function __construct($params = null) {
        $this->currStore = getCurrUser();
//        $this->params = $params;
        if (!$this->currStore) {
            return;
        }
    }

    function extraHeaders() {
        if (!$this->currStore) {
            return;
        }
        ?>
        <link rel="stylesheet" href="js/chosen/chosen.css" />
        <script type="text/javascript" src="js/ajax.js"></script>
        <script type="text/javascript" src="js/ajax-dynamic-list.js">

        </script>
        <link rel="stylesheet" href="css/bigbox.css" type="text/css" />
        <script type="text/javascript">
            $(function () {
                var dates = $("#from, #to").datepicker({
                    changeMonth: true,
                    changeYear: true,
                    numberOfMonths: 1,
                    dateFormat: 'dd-mm-yy',
                    onSelect: function (selectedDate) {
                        var option = this.id == "from" ? "minDate" : "maxDate",
                                instance = $(this).data("datepicker"),
                                date = $.datepicker.parseDate(
                                        instance.settings.dateFormat ||
                                        $.datepicker._defaults.dateFormat,
                                        selectedDate, instance.settings);
                        dates.not(this).datepicker("option", option, date);
                    }
                });
            });

        //function tallyXML(){
        // //var xmltype = $("tallytype").val();
        // var xmltype = document.getElementById('tallytype').value;
        // alert(xmltype);
        //}
        </script>
        <?php
    }

// extraHeaders

    public function pageContent() {
        //For now Pg enabled only for store H.K.Textile
        //if ($this->currStore->id != 81 && $this->currStore->id != 82 && $this->currStore->id != 98) { print "Unauthorized Access"; return; }        
        $formResult = $this->getFormResult();
        $menuitem = "stallytransfer";
        include "sidemenu." . $this->currStore->usertype . ".php";
        $db = new DBConn();
        ?>
            <?php
            $query = "select * from it_codes where id = " . $this->currStore->id;
            $storeobj = $db->fetchObject($query);
            ?>
        <div class="grid_10">
        <?php if ($this->currStore->is_tallyxml == 1) { ?>
                <fieldset>  
                    <legend>Store Tally XML Download</legend>
                    <label><h1><b>Please Update Following Fields For Retail Sale Tally Transfer</b></h1></label>
                    <!--                    <div class="grid_5" > -->
                    <form name="istallyxml" method="post" action="formpost/updatetallyfield.php">
                        <p class="grid_3"><label><b>*Retail Sale Tally Name: </b></label>
                            <input type="text" name="retail_saletally_name" value="<?php echo $this->getFieldValue('retail_saletally_name', $storeobj->retail_saletally_name); ?>">
                        </p><p class="grid_3"><label><b>*Retail Sale Cash Name: </b></label>
                            <input type="text" name="retail_sale_cash_name" value="<?php echo $this->getFieldValue('retail_sale_cash_name', $storeobj->retail_sale_cash_name); ?>">
                        </p><p class="grid_3"><label><b>*Retail Sale Card Name: </b></label> 
                            <input type="text" name="retail_sale_card_name" value="<?php echo $this->getFieldValue('retail_sale_card_name', $storeobj->retail_sale_card_name); ?>"><br/>
                        </p><p class="grid_3"><label><b>*Retail Sale UPI Name: </b></label> 
                        <input type="text" name="retail_sale_upi_name" value="<?php echo $this->getFieldValue('retail_sale_upi_name',$storeobj->retail_sale_upi_name); ?>"><br/>
                        </p><p class="grid_3"><label><b>Retail Sale Bank Name: </b></label> 
                        <input type="text" name="retail_sale_bank_name" value="<?php echo $this->getFieldValue('retail_sale_bank_name',$storeobj->retail_sale_bank_name); ?>"><br/>
                        </p> <br/>  
                        <p class="grid_3">
                            <input type="submit" value="Update" style="width:30%">
                        </p> 
                    </form> <br/>
                    <form id="stallytranfer" name="stallytransfer"  method="POST" action="formpost/genStoreTallyXML.php">                                                    
                        <label class="grid_6">Select the type of Tally XML to download from below: </label><br/>
                        <div class="grid_10">
<!--                        <input type ="radio" name="tallytype" id="tallytype" value="1"  required>Purchase Voucher                 
                        <input type ="radio" name="tallytype" id="tallytype" value="2"  required>Debit Voucher
                        <input type ="radio" name="tallytype" id="tallytype" value="3"  required>Sales Voucher
                        <input type ="radio" name="tallytype" id="tallytype" value="4"  required>Purchase Voucher GST-->
                        <input type ="radio" name="tallytype" id="tallytype" value="5"  required>GST Purchase Voucher
                        <input type ="radio" name="tallytype" id="tallytype" value="6"  required>GST Purchase-Back Sales Voucher
                        <input type ="radio" name="tallytype" id="tallytype" value="7"  required>GST Retail Sales
                        <input type ="radio" name="tallytype" id="tallytype" value="8"  required>GST Payment Voucher
                        <br/><br/>
                        </div>
                        <div class="grid_5" id="storeDiv" name="storeDiv">
                            Select date range from below :*<br/>                                         
                            From * :
                            <input id="from" type="text" name="from" style ="width:30%" value="" required/> &nbsp;&nbsp;&nbsp;&nbsp;                    
                            To * :
                            <input id="to" type="text" name="to" style ="width:30%"  value="" required/>                   
                        </div>                  
                        <br/><br/><br/><br/>
                        <div class="grid_8">                
                        <!--<input type="button" value="Download" onclick="javascript:tallyXML();"/>-->
                            <input type = "submit" value = "Download" />
                            <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>               
                        </div>
                    </form>
                </fieldset>
        <?php } else { ?>
                <span style="color:#DC143C;text-align:center;font-weight: bold;font-size: 200%">"Your Tally Transfer feature not enabled. Please Contact Head Office to enable this feature"</span>
        <?php } ?>
        </div>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js" type="text/javascript"></script>
        <script src="js/chosen/chosen.jquery.js" type="text/javascript"></script>
        <script type="text/javascript"> $(".chzn-select").chosen(); $(".chzn-select-deselect").chosen({allow_single_deselect: true});</script>

        <?php
    }

//pageContent
}

//class
?>
