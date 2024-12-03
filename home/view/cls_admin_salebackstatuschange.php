<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");
require_once "lib/core/strutil.php";
require_once "session_check.php";

///
class cls_admin_salebackstatuschange extends cls_renderer {

    var $params;
    var $currUser;
    var $userid;
    var $currStore;
    var $storeid;

    function __construct($params = null) {
        $this->currStore = getCurrUser();
        $this->currUser = getCurrUser();
        $this->userid = $this->currUser->id;
        $this->params = $params;
        if (!$this->currStore) {
            return;
        }
        $this->storeid = $this->currStore->id;
    }

    function extraHeaders() {
//	if (!$this->currStore) {
//	    return;
//	}
        ?>
        <script type="text/javascript" src="jqueryui/js/jquery-ui-1.7.1.custom.min.js"></script>
        <script type="text/javascript" src="js/daterangepicker.jQuery.js"></script>
        <link rel="stylesheet" href="css/ui.daterangepicker.css" type="text/css" />
        <link rel="stylesheet" href="css/redmond/jquery-ui-1.7.1.custom.css" type="text/css" title="ui-theme" />

        <link rel="stylesheet" href="js/chosen/chosen.css" />
        <script type="text/javascript" src="js/ajax.js"></script>   
        <script language="JavaScript" src="js/tigra/validator.js"></script>

        <!--<link rel="stylesheet" href="css/bigbox.css" type="text/css" />-->
        <?php
    }

// extraHeaders

    public function pageContent() {
        $menuitem = "salebackstatuschange";
        include "sidemenu." . $this->currUser->usertype . ".php";
        $formResult = $this->getFormResult();
        $db = new DBConn();
        ?>
        <div class="grid_10">

            <div class="grid_3">&nbsp;</div>
            <div class="grid_6">
                <fieldset>
                    <legend>Update Store Saleback Status</legend>
                    <p>Select Stores to set the Saleback Active Status for a time period.</p>
                    <form id="ruleform" name="ruleform" method="post" action="formpost/UpdateStoreSalebackStatus.php" >
                                <?php if ($this->currUser->usertype != UserType::Dealer) { ?>
                            <div class="clsDiv" style="width:100%;height:120px"><b>Select Stores</b><br/>

                                <select id="store" data-placeholder="Choose Store"  name="store[]" class="chzn-select" multiple style="width:75%;" > 

                                    <?php
                                    if ($this->storeidreport == -1) {
                                        $defaultSel = "selected";
                                    } else {
                                        $defaultSel = "";
                                    }
                                    ?>
                                    <option value="-1" <?php echo $defaultSel; ?>>All Stores</option> 
                                    <?php
                                    $objs = $db->fetchObjectArray("select * from it_codes where usertype = " . UserType::Dealer . "  and is_closed=0  order by trim(store_name)");

                                    if ($this->storeidreport == "-1") {
                                        $storeid = array();
                                        $allstoreArrays = $db->fetchObjectArray("select id from it_codes where usertype = 4");
                                        foreach ($allstoreArrays as $storeArray) {
                                            foreach ($storeArray as $store) {
                                                array_push($storeid, $store);
                                            }
                                        }
                                    } else {
                                        $storeid = explode(",", $this->storeidreport);
                                    }

                                    foreach ($objs as $obj) {
                                        $selected = "";
                                        if ($this->storeidreport != -1) {
                                            foreach ($storeid as $sid) {
                                                if ($obj->id == $sid) {
                                                    $selected = "selected";
                                                }
                                            }
                                        }
                                        ?>
                                        <option value="<?php echo $obj->id; ?>" <?php echo $selected; ?>><?php echo $obj->store_name; ?></option> 
            <?php } ?>
                                </select>

                            </div>
                        <?php } ?>

                        <br/>
                        <label for="date">Select a Start Date:</label>
                        <input type="date" id="date" name="startdate" required>
                        <br/>
                        <br/>
                        <label for="date">Select a End Date:</label>
                        <input type="date" id="date" name="enddate" required>
                        <br/>
                        <br/>
        <?php
        if (!empty($_SESSION['form_errors'])) {
            $error = $_SESSION['form_errors'];
            echo $error;
            return;
        }
        ?>
                        <input type="submit" value="Update"/><br/>
                        <input type="hidden" name="form_id" value="1"/>
                        <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span> 
                    </form>
                    <script src="js/chosen/chosen.jquery.js" type="text/javascript"></script>
                    <script type="text/javascript"> $(".chzn-select").chosen(); $(".chzn-select-deselect").chosen({allow_single_deselect: true});</script>
                    <div class="clear"></div>
                </fieldset>
            </div>
        </div>
        <?php
    }

}
?>
<script src="js/chosen/chosen.jquery.js" type="text/javascript"></script>
<script type="text/javascript"></script>
<script type="text/javascript">

</script>
<?php ?>