<?php
require_once "view/cls_renderer.php";
require_once "lib/items/clsItems.php";
require_once "lib/core/Constants.php";

class cls_admin_discounts_add extends cls_renderer {
    var $params;
    var $currStore;
    var $storeid;
    function __construct($params=null) {
        //parent::__construct(array(UserType::Admin, UserType::CKAdmin));
        $this->currStore = getCurrUser();
        $this->params = $params;
        if (!$this->currStore) { return; }
        $this->storeid = $this->currStore->id;
    }

    function extraHeaders() {
        if (!$this->currStore) {
            return;
	}
?>
<script type="text/javascript">
function fileSelect(fname) {
arr = fname.split("\\");
fname = arr[arr.length-1];
arr = fname.split(".");
$("#design_no").val(arr[0]);
return; // disable automatic file upload
document.getElementById("addform").submit();
}

</script>
<?php

    } // extraHeaders

    public function pageContent() {
        $formResult = $this->getFormResult();
        $menuitem="discount";
        include "sidemenu.".$this->currStore->usertype.".php";
        $db = new DBConn();
       // $store = $db->fetchObjectArray("select * from it_codes where usertype=".UserType::Dealer);
           $store = $db->fetchObjectArray("select c.* from it_codes c where c.usertype=".UserType::Dealer." and c.id not in (Select sd.store_id from it_ck_storediscount sd, it_codes c where sd.store_id=c.id)");
        ?>
<div class="grid_10">

    <div class="grid_1">&nbsp;</div>
    <div class="grid_10">
        <fieldset>
            <legend>Add Discount Info</legend>
    <form id="discadd" name="discadd" method="post" action="formpost/addStoreDiscount.php">
                <p>
                    <label>Select Store: *</label>
                    <select name="storeid">
                        <option value="0">Please Select a Store</option>
                                <?php
                                $display = "block";
                                foreach ($store as $str) {
                                    ?>
                        <option value="<?php echo $str->id; ?>" <?php echo $selected; ?>><?php echo $str->store_name; ?></option>
                                <?php } ?>
                    </select>
                </p>
              <!--  <p>
                    <label>Store Name: *</label>
                    <input required type="text" name="storename" value="">
                </p>
                <p>
                    <label>Location: *</label>
                    <input required type="text" name="location" value="">
                </p>-->
<!--                <p>
                    <label>Polaris Code: *</label>
                    <input required type="text" name="polaris" value="">
                </p>-->
                <p class="grid_3">
                    <label>Dealer Discount: *</label>
                    <input required type="float" name="dealerdisc" value="">
                </p>
                <p class="grid_3">
                    <label>Additional Discount: </label>
                    <input type="float" name="adddisc" value="">
                </p>
                <p class="grid_3">
                    <label>VAT :</label>
                    <input type="float" name="vat" value="">
                </p>
                <p class="grid_3">
                    <label>CST: </label>
                    <input type="float" name="cst" value="">
                </p>
                <p class="grid_3">
                    <label>Transport : </label>
                    <input type="float" name="transport" value="">
                </p>
                <p class="grid_3">
                    <label>Octroi : </label>
                    <input type="float" name="octroi" value="">
                </p>
                <p class="grid_3">
                    <label>Cash : </label>
                    <input type="float" name="cash" value="">
                </p>
                <p class="grid_3">
                    <label>Non Claim : </label>
                    <input type="float" name="nonclaim" value="">
                </p>
                        <?php if ($formResult) { ?>
                <p class="grid_3">
                    <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
                </p>
                        <?php } ?>
                <input type="submit" value="Add">
    </form>
        </fieldset>
    </div>
</div>
    <?php
    } //pageContent
}//class
?>
