<?php
require_once "view/cls_renderer.php";
require_once "lib/items/clsItems.php";
require_once "lib/core/Constants.php";
require_once "session_check.php";

class cls_admin_strordersmembership extends cls_renderer {
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
    
    function placeOrder(){        
        value = $("#filename").val();
        var formname=eval("loadstock");
         var params = $(formname).serialize();
//        alert(params);
        window.location.href = "formpost/loadStoreOrders_membership.php?"+params; 
    }
    
    function fetchSampleExcel(){
    //alert("hello");
    window.location.href="formpost/membershiporderExcel.php"; 
}
</script>    
<?php
    } // extraHeaders

    public function pageContent() {
        $formResult = $this->getFormResult();
        $menuitem="storeorder";
        include "sidemenu.".$this->currStore->usertype.".php";
        ?>
<div class="grid_10">

    <div class="grid_3">&nbsp;</div>
    <div class="grid_5">
        <fieldset style="background-color:#b0b0b0;">
            <legend>Upload Store Orders (Membership)</legend>
             <br><center> <button onclick="fetchSampleExcel()">Download Membership Order Excel</button></center><br><br>
    <form id="loadstock" name="loadstock" enctype="multipart/form-data" method="post" action="formpost/checkOrderFile_membership.php"> 
        <div class="clsDid">Orders File (CSV)</div>
        <div class="clsText"><input type="file" id="file" name="file" ></div>
<br /><br />
        <input type="submit" value="Check File"/>
        <input type="hidden" name="form_id" value="1"/>
        <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
	<br />NOTE: UPLOAD STORE ORDER(S) TAKES 2-3 Minutes to complete.<br />Please Wait for it to do so.<br />Donot Hit the Browser Refresh or any other Buttons
        <?php
           if($formResult->cssClass == "success" && !isset($_SESSION['orderplace'])){
               ?><p>CLICK ON BELOW BUTTON TO UPLOAD THE ORDERS</p>
                 <input type="hidden" id="filename" name="filename" value="<?php  if(isset($_SESSION['fpath'])){ echo $_SESSION['fpath'];}?>"/>
                 <input type ="button" name="order" id = "order" value ="Place Store Order(s)" onclick="placeOrder();">
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
