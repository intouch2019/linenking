<?php
require_once "view/cls_renderer.php";
require_once "lib/items/clsItems.php";
require_once "lib/core/Constants.php";

class cls_admin_discounts_update extends cls_renderer {
    var $params;
    var $currStore;
    var $storeid;
    var $sid;
    function __construct($params=null) {
        //parent::__construct(array(UserType::Admin, UserType::CKAdmin));
        $this->currStore = getCurrUser();
        $this->params = $params;
        if (!$this->currStore) { return; }
        $this->storeid = $this->currStore->id;
        if (isset($params['sid']))
            $this->sid = $params['sid'];
    }

    function extraHeaders() {
        if (!$this->currStore) {
            return;
	}
?>
<script type="text/javascript">
    function disStoreInfo(value){
       // alert("storeid:-"+value);
       window.location.href="admin/discounts/update/sid="+value;
    }
 </script>
<?php

    } // extraHeaders

    public function pageContent() {
        $formResult = $this->getFormResult();
        $menuitem="discount";
        include "sidemenu.".$this->currStore->usertype.".php";
        $db = new DBConn();
        $store = $db->fetchObjectArray("select * from it_codes where usertype=".UserType::Dealer." order by store_name");
        $discinfo = $db->fetchObject("select * from it_ck_storediscount where store_id=$this->sid");
        ?>
<div class="grid_10">

    <div class="grid_1">&nbsp;</div>
    <div class="grid_10">
        <fieldset>
            <legend>Update Discount Info</legend>
    <form id="discupdate" name="discupdate" method="post" action="formpost/postUpdateDisc.php">
        <p>
            <input type="hidden" name="discid" value="<?php echo $discinfo->id; ?>"/>
                    <label>Select Store: *</label>
                    <select name="storeid" onchange="javascript:disStoreInfo(this.value);">
                        <option value="0">Please Select a Store</option>
                                <?php
                                $display = "block";
                                foreach ($store as $str) {
                                    if ($discinfo->store_id==$str->id) {
                                        $selected = "selected";
                                    } else {
                                        $selected = "";
                                    }
                                    ?>
                        <option value="<?php echo $str->id; ?>" <?php echo $selected; ?>><?php echo $str->store_name; ?></option>
                                <?php } ?>
                    </select>
                </p>
             <!--   <p>
                    <label>Store Name: *</label>
                    <input required type="text" name="storename" value=" //if (isset($discinfo->storename)) echo $discinfo->storename; ">
                </p>
                <p>
                    <label>Location: *</label>
                    <input required type="text" name="location" value=" //if (isset($discinfo->location)) echo $discinfo->location; ">
                </p> -->
<!--                <p>
                    <label>Polaris Code: *</label>
                    <input required type="text" name="polaris" value="<?php if (isset($discinfo->polariscode)) echo $discinfo->polariscode; ?>">
                </p>-->
                <p class="grid_3">
                    <label>Dealer Discount: *</label>
                    <input required type="float" name="dealerdisc" value="<?php if (isset($discinfo->dealer_discount)) echo $discinfo->dealer_discount; ?>">
                </p>
                <p class="grid_3">
                    <label>Additional Discount: </label>
                    <input type="float" name="adddisc" value="<?php if (isset($discinfo->additional_discount)) echo $discinfo->additional_discount; ?>">
                </p>
                <p class="grid_3">
                    <label>VAT :</label>
                    <input type="float" name="vat" value="<?php if (isset($discinfo->vat)) echo $discinfo->vat; ?>">
                </p>
                <p class="grid_3">
                    <label>CST: </label>
                    <input type="float" name="cst" value="<?php if (isset($discinfo->cst)) echo $discinfo->cst; ?>">
                </p>
                <p class="grid_3">
                    <label>Transport : </label>
                    <input type="float" name="transport" value="<?php if (isset($discinfo->transport)) echo $discinfo->transport; ?>">
                </p>
                <p class="grid_3">
                    <label>Octroi : </label>
                    <input type="float" name="octroi" value="<?php if (isset($discinfo->octroi)) echo $discinfo->octroi; ?>">
                </p>
                <p class="grid_3">
                    <label>Cash : </label>
                    <input type="float" name="cash" value="<?php if (isset($discinfo->cash)) echo $discinfo->cash; ?>">
                </p>
                <p class="grid_3">
                    <label>Non Claim : </label>
                    <input type="float" name="nonclaim" value="<?php if (isset($discinfo->nonclaim)) echo $discinfo->nonclaim; ?>">
                </p>
                        <?php if ($formResult) { ?>
                <p class="grid_3">
                    <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
                </p>
                        <?php } ?>
                <input type="submit" value="Update">
    </form>
        </fieldset>
    </div>
</div>
    <?php
    } //pageContent
}//class
?>