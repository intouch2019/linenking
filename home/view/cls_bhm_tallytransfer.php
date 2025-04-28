<?php
require_once "view/cls_renderer.php";
require_once "lib/items/clsItems.php";
require_once "lib/core/Constants.php";
require_once ("lib/db/DBConn.php");

class cls_bhm_tallytransfer extends cls_renderer {

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
        <script type="text/javascript" src="js/ajax-dynamic-list.js"></script>
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
        </script>
        <?php
    }

// extraHeaders

    public function pageContent() {
        $formResult = $this->getFormResult();
        $menuitem = "BHMtallytransfer";
        include "sidemenu." . $this->currStore->usertype . ".php";
        ?>
        <div class="grid_10">
            <?php if ($this->currStore->is_tallyxml == 1) { ?>
                <fieldset>  
                    <legend>BHM Stores Tally XML Download</legend>
                    <form id="stallytranfer" name="stallytransfer"  method="POST" action="formpost/genBHMStoreTallyXML.php">                                                    
                        Select the type of Tally XML to download from below: <br/><br/>
                        <input type ="radio" name="tallytype" id="tallytype" value="1"  required>GST Counter Sales
                        <input type ="radio" name="tallytype" id="tallytype" value="2"  required>GST Cash Receipt Voucher
                        <br/><br/>
                        <div class="grid_5" id="storeDiv" name="storeDiv">
                            Select date range from below :*<br/>                                         
                            From * :
                            <input id="from" type="text" name="from" style ="width:30%" value="" required/> &nbsp;&nbsp;&nbsp;&nbsp;                    
                            To * :
                            <input id="to" type="text" name="to" style ="width:30%"  value="" required/>                   
                        </div>                  
                        <br/><br/><br/><br/>
                        <div class="grid_8">                
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
