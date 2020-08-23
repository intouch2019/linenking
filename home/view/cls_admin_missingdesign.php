<?php
require_once "view/cls_renderer.php";
require_once "lib/items/clsItems.php";
require_once "lib/core/Constants.php";

class cls_admin_missingdesign extends cls_renderer {
    var $params;
    var $currStore;
    var $storeid;
    function __construct($params=null) {
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
</script>
<?php

    } // extraHeaders

    public function pageContent() {
        //if ($this->currStore->usertype != UserType::Admin && $this->currStore->usertype != UserType::CKAdmin && $this->currStore->usertype != UserType::Manager) { print "Unauthorized Access"; return; }
        $formResult = $this->getFormResult();
        $menuitem="finddesign";
        include "sidemenu.".$this->currStore->usertype.".php";
        $clsItems = new clsItems();
        $categories = $clsItems->getAllCategories();
        ?>
<div class="grid_10">

    <div class="grid_3">&nbsp;</div>
    <div class="grid_5">
        <fieldset>
            <legend>Get Missing Image List</legend>
    <form method="post" action="formpost/csvexport.php">
        <div class="clsDiv">Item Category</div>
        <div class="clsText">
            <select  name="category">
                <option value="-1">ALL</option>
                        <?php foreach ($categories as $ctg) {
			if ($ctg->id == $this->getFieldValue('category')) { $selected = "selected"; }
			else { $selected = ""; }
			?>
                <option value="<?php echo $ctg->id; ?>" <?php echo $selected; ?>><?php echo $ctg->name; ?></option>
                        <?php } ?>
            </select></div><br/>
        <div class="clsDiv">Status</div>
        <div class="clsText">
            <select  name="status">
                <option value="-1">ALL</option>
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select></div>
        <Br/>
        <input type="hidden" name="form_name" value="missingimage"/>
        <input type="submit" value="Retrieve List"/>
        <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
    </form>
        </fieldset>
    </div>
</div>
    <?php
    } //pageContent
}//class
?>
