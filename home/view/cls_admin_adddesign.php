<?php
require_once "view/cls_renderer.php";
require_once "lib/items/clsItems.php";
require_once "lib/core/Constants.php";

class cls_admin_adddesign extends cls_renderer {
    var $params;
    var $currStore;
    var $storeid;
    var $ctg_id=null;
    function __construct($params=null) {
        $this->currStore = getCurrUser();
        $this->params = $params;
        if (!$this->currStore) { return; }
        $this->storeid = $this->currStore->id;
        if ($params && isset($params['ctg_id'])) { // ctg id is set
	   $this->ctg_id = intval($params['ctg_id']);
        }
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
        $menuitem="adddesign";
        include "sidemenu.".$this->currStore->usertype.".php";
        $clsItems = new clsItems();
        $categories = $clsItems->getAllCategories();
        ?>
<div class="grid_10">

    <div class="grid_3">&nbsp;</div>
    <div class="grid_5">
        <fieldset style="background-color:#b0b0b0;">
            <legend>Upload Design Image</legend>
    <form id="addform" name="addform" enctype="multipart/form-data" method="post" action="formpost/postAddDesign.php">
        <div class="clsDiv">Item Category</div>
        <div class="clsText"><select  name="category">
                        <?php foreach ($categories as $ctg) {
//			if ($ctg->id == $this->getFieldValue('category')) { $selected = "selected"; }
                        if ($ctg->id == $this->ctg_id) { $selected = "selected"; }    
			else { $selected = ""; }
			?>
                <option value="<?php echo $ctg->id; ?>" <?php echo $selected; ?>><?php echo $ctg->name; ?></option>
                        <?php } ?>
            </select></div>
        <div class="clsDiv">Design Number</div>
        <div class="clsText"><input id="design_no" name="design_no" value="<?php if (isset($_SESSION['form_design_no'])) {echo $_SESSION['form_design_no'];} ?>"/></div>

        <div class="clsDid">Image</div>
        <div class="clsText"><input type="file" id="image" name="image" onchange="fileSelect(this.value);"></div>

        <input type="submit" value="Add Design Image"/>
        <input type="hidden" name="form_id" value="1"/>
        <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
    </form>
        </fieldset>
    </div>
</div>
    <?php
    } //pageContent
}//class
?>
