<?php
require_once "view/cls_renderer.php";
require_once "lib/items/clsItems.php";
require_once "lib/core/Constants.php";

class cls_admin_addtax extends cls_renderer {
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
        $menuitem="createTax";
        include "sidemenu.".$this->currStore->usertype.".php";
//        $clsItems = new clsItems();
//        $categories = $clsItems->getAllCategories();
        ?>
<div class="grid_10">

    <div class="grid_3">&nbsp;</div>
    <div class="grid_5">
        <fieldset style="background-color:#b0b0b0;">  
            <legend>Add Tax</legend>
    <form id="addtaxform" name="addtaxform"  method="POST" action="formpost/postAddTax.php">    
            Select Tax type:<br/>
            <input type ="radio" name="taxtype" id="taxtype" value="<?php echo taxType::VAT;?>" required><?php echo trim(taxType::getName(taxType::VAT));?>
            <input type ="radio" name="taxtype" id="taxtype" value="<?php echo taxType::CST;?>" required><?php echo trim(taxType::getName(taxType::CST));?>           
            <br/><br/>
            Enter tax percent (%):
            <input type ="text" name="taxper" id="taxper" value="" required/>
            <br/>
            <input type="submit" value="Add"/>
            <input type="hidden" name="form_id" value="addtaxform"/>
            <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
    </form>
        </fieldset>
    </div>
</div>
    <?php
    } //pageContent
}//class
?>
