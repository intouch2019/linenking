<?php
require_once "view/cls_renderer.php";
require_once "lib/core/Constants.php";
require_once "session_check.php";

class cls_admin_stock_upload extends cls_renderer {
    var $params;
    var $currStore;
    function __construct($params=null) {
        $this->currStore = getCurrUser();
	//parent::__construct(array(UserType::Admin, UserType::CKAdmin));        
        $this->params = $params;
    }

    function extraHeaders() {
    ?>
    <script type="text/javascript">
    
    function uploadStock(){        
        value = $("#filename").val();
        var formname=eval("loadstock");
         var params = $(formname).serialize();
//        alert(params);
        window.location.href = "formpost/loadStock.php?"+params;
    }
</script>  
<?php
    } // extraHeaders

    public function pageContent() {
        $formResult = $this->getFormResult();
        $menuitem="loadstock";
        include "sidemenu.".$this->currStore->usertype.".php";
        ?>
<div class="grid_10">

    <div class="grid_3">&nbsp;</div>
    <div class="grid_5">
        <fieldset style="background-color:#b0b0b0;">
            <legend>Upload Stock</legend>    
    <form id="loadstock" name="loadstock" enctype="multipart/form-data" method="post" action="formpost/checkStockFile.php">      
        <div class="clsDid">Stock File (CSV)</div>
        <div class="clsText"><input type="file" id="file" name="file" ></div>
<br /><br />
        <input type="submit" value="Check File"/>
        <input type="hidden" name="form_id" value="1"/>
        <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
	<br />NOTE: STOCK UPLOAD TAKES 2-3 Minutes to complete.<br />Please Wait for it to do so.<br />Donot Hit the Browser Refresh or any other Buttons
        <?php
           if($formResult->cssClass == "success" && !isset($_SESSION['stockuploaded'])){
               ?><p><br/> Do you want to continue? If Yes CLICK ON BELOW BUTTON TO UPLOAD THE STOCK<br/></p>
                 <input type="hidden" id="filename" name="filename" value="<?php  if(isset($_SESSION['fpath'])){ echo $_SESSION['fpath'];}?>"/>
                 <input type ="button" name="order" id = "order" value ="Upload Stock" onclick="uploadStock();">
               <?php
               
           }
        ?>
    </form>
        </fieldset>
    </div>
</div>
    <?php
    } //pageContent
}//class
?>
