<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");

class cls_store_sequence extends cls_renderer{

        var $currUser;
        var $userid;
        
        function __construct($params=null) {
		//parent::__construct(array(UserType::Admin, UserType::CKAdmin));
		$this->currUser = getCurrUser();                
		$this->userid = $this->currUser->id;
        }

	function extraHeaders() {
        ?>

<link rel="stylesheet" href="js/chosen/chosen.css" />
<script type="text/javascript" src="js/ajax.js"></script>
<script type="text/javascript" src="js/ajax-dynamic-list.js">
	/************************************************************************************************************
	(C) www.dhtmlgoodies.com, April 2006
	
	This is a script from www.dhtmlgoodies.com. You will find this and a lot of other scripts at our website.	
	
	Terms of use:
	You are free to use this script as long as the copyright message is kept intact. However, you may not
	redistribute, sell or repost it without our permission.
	
	Thank you!
	
	www.dhtmlgoodies.com
	Alf Magne Kalleland
	
	************************************************************************************************************/	

</script>
<link rel="stylesheet" href="css/bigbox.css" type="text/css" />
<script type="text/javascript">
function enterPressed() {
    var key = window.event.keyCode;
    // If the user has pressed enter
    if (key == 13) {
        alert("Enter detected");
        return false;
    } else {
        return true;
    }
}
function fetchSampleExcel(){
    //alert("hello");
    window.location.href="formpost/storeSeqExcel.php";
}

function setStoreSeq(){
    value = $("#filename").val();
    var formname=eval("storeseq");
    var params = $(formname).serialize();
//        alert(params);
    window.location.href = "formpost/updateStoreSeq.php?"+params;
}
</script>
        
        <?php
        }

        public function pageContent() {
            $currUser = getCurrUser();
            $menuitem = "sseq";
            include "sidemenu.".$currUser->usertype.".php";
            $formResult = $this->getFormResult();
            $db = new DBConn();
?>
<div class="grid_10">
    <?php
    
    $display="none";
    $num = 0;
    ?>
    <div class="grid_3">&nbsp;</div>
    <div class="grid_5"><button name="dwnFile" id="dwnFile" onclick="fetchSampleExcel();">Download Excel to Set Store Sequence</button></div>
    <div class="clear"></div><br>
     <div class="grid_3">&nbsp;</div>
     <div class="grid_5">
     <div class="box" style="clear:both;">
	<fieldset class="login">
	<legend>Set Store Sequence</legend>	
        <form id="storeseq" name="storeseq" enctype="multipart/form-data" method="post" action="formpost/checkStoreSeqFile.php">      
            <div class="clsDid">Store Sequence File (Excel)</div>
            <div class="clsText"><input type="file" id="file" name="file" ></div>
            <br /><br />
            <input type="submit" value="Check File"/>
            <input type="hidden" name="form_id" value="1"/>
            <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
            <br />NOTE: SET/UPDATE STORE(S) SEQUENCE  TAKES 2-3 Minutes to complete.<br />Please Wait for it to do so.<br />Donot Hit the Browser Refresh or any other Buttons
            <?php
               if($formResult->cssClass == "success" && !isset($_SESSION['storeseq'])){
                   ?><p>CLICK ON BELOW BUTTON TO UPLOAD THE ORDERS</p>
                     <input type="hidden" id="filename" name="filename" value="<?php  if(isset($_SESSION['fpath'])){ echo $_SESSION['fpath'];}?>"/>
                     <input type ="button" name="order" id = "order" value ="Set/Update Stores Sequence" onclick="setStoreSeq();">
                   <?php

               }
            ?>

            </form>
	</fieldset>
    </div> <!-- class=box -->
    </div>
</div>
<script src="<?php echo CdnUrl('js/chosen/chosen.jquery.js'); ?>" type="text/javascript"></script>
<script type="text/javascript"> $(".chzn-select").chosen(); $(".chzn-select-deselect").chosen({allow_single_deselect:true}); </script>
<?php
	}
}
?>
