<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");

class cls_admin_stores_stackcapacityupdate extends cls_renderer {

    var $currUser;
    var $userid;

    function __construct($params = null) {
        //parent::__construct(array(UserType::Admin, UserType::CKAdmin));
        $this->currUser = getCurrUser();
        $this->userid = $this->currUser->id;
    }

    function extraHeaders() {
        ?>
        <link rel="stylesheet" href="js/chosen/chosen.css" />
        <script type="text/javascript" src="js/ajax.js"></script>
        <script type="text/javascript" src="js/ajax-dynamic-list.js"></script>
        <link rel="stylesheet" href="'css/bigbox.css" type="text/css" />
        <script type="text/javascript">
            function fetchSampleExcel() {
                //alert("hello");
                window.location.href = "formpost/stackCapacityUpdateExcel.php";
            }
        </script>
        <?php
    }

    public function pageContent() {
        $currUser = getCurrUser();
        $menuitem = "Stackcapacityupdate";    // pagecode
        include "sidemenu." . $currUser->usertype . ".php";
        $formResult = $this->getFormResult();
        $db = new DBConn();
        ?>

        <div class="grid_10" id="tvo">
            <div class="grid_3">&nbsp;</div>

            <div class="clear"></div><br>
            <div class="grid_3">&nbsp;</div>
            <div class="grid_5">
                <div class="box" style="clear:both;">
                    <fieldset class="login">
                        <legend>Store Stack Capacity Update</legend>	
                        <br><center> <button onclick="fetchSampleExcel()">Download Store Stack Capacity Update Excel</button></center><br><br>
                        <form  id="storeseq" name="storeseq" enctype="multipart/form-data" method="post" action="formpost/updateStoreStackCapacity.php">      


                            <div>
                                <div class="clsDid" >Add File (Excel)</div>
                                <div class="clsText"><input type="file" id="file" name="file" ></div>
                                <br/>
                                <input type="submit" value="Submit File">
                            </div>
                            <label>
                                <?php
                                $filename_arr = explode(".", $formResult->status);
                                $fname = explode("(", $filename_arr[0]);
                                $temp = $fname[0];
                                if ($temp = "") {
                                    $temp = $fname[1] . '.xls';
                                }


                                echo $temp;
                                ?>
                            </label>

                            <input type="hidden" name="form_id" value="1">
                            <span id="statusMsg" class="<?php
                            if ($formResult->form_id == 1) {
                                echo $formResult->cssClass;
                            }
                            ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php
                        if ($formResult->form_id == 1) {
                            echo $formResult->status;
                        }
                        ?></span>
                            <br/>Do not hit the browser <b>refresh</b> or any other buttons           
                        </form>
                    </fieldset>
                </div>
            </div>
        </div>

        <script src="js/chosen/chosen.jquery.js" type="text/javascript"></script>
        <script type="text/javascript"> $(".chzn-select").chosen();
                                    $(".chzn-select-deselect").chosen({allow_single_deselect: true});</script>
        <?php
    }
}
?>