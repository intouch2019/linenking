<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");
require_once "lib/core/strutil.php";
require_once "session_check.php";

class cls_old_store_map extends cls_renderer {

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

        if (!$this->currStore)
            return;

        $this->storeid = $this->currStore->id;
    }

    function extraHeaders() {
        ?>
        <script src="jqueryui/js/jquery-ui-1.7.1.custom.min.js"></script>
        <link rel="stylesheet" href="js/chosen/chosen.css" />
        <script src="js/ajax.js"></script>
        <?php
    }

    public function pageContent() {

        $menuitem = "oldstoreupdate";
        include "sidemenu." . $this->currUser->usertype . ".php";

        $formResult = $this->getFormResult();
        $db = new DBConn();
        ?>

        <div class="grid_10">

            <div class="grid_3">&nbsp;</div>

            <div class="grid_6">

                <fieldset>

                    <legend>OLD STORE MAPPING</legend>

                    <p>Select Old Store Map For New Store.</p>

                    <form method="post" action="formpost/mapoldstore.php">

                        <!-- NEW STORE -->

                        <div class="clsDiv" style="height:120px">

                            <b>Select New Store</b><br/>

                            <select id="new_store" name="new_store" class="chzn-select" style="width:75%;" required>

                                <option value="-1">Select New Store</option>

                                <?php
                                $stores = $db->fetchObjectArray("select * from it_codes where usertype=" . UserType::Dealer . "  and is_closed=0  order by trim(store_name)");

                                foreach ($stores as $store) {
                                    ?>

                                    <option value="<?php echo $store->id; ?>">
            <?php echo $store->store_name; ?>
                                    </option>

        <?php } ?>

                            </select>

                        </div>


                        <!-- OLD STORE -->

                        <div class="clsDiv" style="height:120px">

                            <b>Select Old Store</b><br/>

                            <select id="old_store" name="old_store[]" class="chzn-select" style="width:75%;" multiple >

                                <?php
                                $oldStores = $db->fetchObjectArray("select * from it_codes where usertype=" . UserType::Dealer . "   order by store_name");

                                foreach ($oldStores as $store) {
                                    ?>

                                    <option value="<?php echo $store->id; ?>">
            <?php echo $store->store_name; ?>
                                    </option>

        <?php } ?>

                            </select>

                        </div>

                        <br>

                        <input type="submit" value="Update">

                        <input type="hidden" name="form_id" value="1">

                        <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" 
                              style="display:<?php echo $formResult->showhide; ?>;">

        <?php echo $formResult->status; ?>

                        </span>

                    </form>


                    <script src="js/chosen/chosen.jquery.js"></script>

                    <script>

                        $(".chzn-select").chosen({
                            width: "75%",
                            search_contains: true
                        });

                        $("#new_store").change(function () {

                            var newStore = $(this).val();

                            if (newStore == "-1") {
                                $("#old_store option").removeAttr("selected");
                                $("#old_store").trigger("chosen:updated");
                                return;
                            }

                            $.ajax({

                                url: "ajax/get_mapped_oldstores.php",
                                type: "GET",
                                data: {new_store_id: newStore},
                                dataType: "json",

                                success: function (data) {

                                    $("#old_store option").removeAttr("selected");

                                    for (var i = 0; i < data.length; i++) {
                                        $("#old_store option[value='" + data[i] + "']").attr("selected", "selected");
                                    }

                                    $("#old_store").trigger("chosen:updated");
                                    $("#old_store").trigger("liszt:updated");

                                }

                            });

                        });

                    </script>

                </fieldset>

            </div>
        </div>

        <?php
    }
}
?>