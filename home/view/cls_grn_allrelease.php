<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once "lib/core/Constants.php";


class cls_grn_allrelease extends cls_renderer {
    var $currStore;
    var $storeid;
    function __construct($params=null) {
        $this->currStore = getCurrUser();
        $this->params = $params;
        if (!$this->currStore) { return; }
        $this->storeid = $this->currStore->id;
    }

    function extraHeaders() { ?>
<script type="text/javascript" src="js/expand.js"></script>
<script language="JavaScript" src="js/tigra/validator.js"></script>
<script type="text/javascript">
$(function() {
       $("#results").hide();
       $("#results2").hide();
});

function releaseALL(){
    $("#results").hide();
    r = confirm("Are you sure you want to clear all grn ?");
    if(r == 1){
       // alert("Released");
        $("#results").show();
        var ajaxURL = "ajax/clearALLGRN.php";
        $.ajax({
            url:ajaxURL,
            dataType: 'json',
            success:function(data){
             // alert(data.message);
                $("#results").hide();
                //console.log(data);  
                if(data.error==1){
                    alert(data.message);                            
                }else{
                    if(data.message == ''){ alert("Grn not cleared.Contact Intouch");}
                    else{ 
                        alert(data.message);                         
                    }

                  }
            }
        });
    }
}


function loadUnreleasedDesigns(){    
    value = $("#filename").val();
    var formname=eval("loadunreleasedD");
     var params = $(formname).serialize();
//        alert(params);
    window.location.href = "formpost/loadUnreleasedDesigns.php?"+params;    
}
</script>
    <?php
    } //end of extra headers

    public function pageContent() {
        //if ($this->currStore->usertype == UserType::Admin || $this->currStore->usertype == UserType::CKAdmin) {} else { print "Unauthorized Access"; return; }
        if (getCurrUser()) {
            $menuitem="stores";
            include "sidemenu.".$this->currStore->usertype.".php";
        }
        $formResult = $this->getFormResult();
        $db = new DBConn();
        ?>
<div class="grid_10">
    <fieldset style="background-color:#b0b0b0;">  
        <legend>Clear GRN Section</legend>
        <div class="grid_12">
            <div class="addstore">
                <h5>Click on below button to clear all grn</h5>
                <label></label><br><br>
                <form action="" method="post" name="registration" onsubmit="">
                    <input type="button" name="gall" id="gall" value="CLEAR ALL GRN" onclick="javascript:releaseALL();" >    
                </form>
                <?php unset($_SESSION['form_post']);?>
            </div>
        </div>        
    </fieldset>
    
    <div  class="grid_4" id="results" name="results" style="background:#DBECFF; margin-left:10px; width:60%;">Processing. Please wait... <img src="images/loading.gif" /></div>


   

</div>

<div class="grid_10">
    <fieldset style="background-color:#b0b0b0;">  
        <legend>Load Unreleased Designs Section</legend>
        <div class="grid_12">
            <form id="loadunreleasedD" name="loadunreleasedD" enctype="multipart/form-data" method="post" action="formpost/checkUnReleasedDesignsFile.php">           
            <!--<h5>Note: File should be of type .csv</h5>-->
             <br />NOTE: LOAD UNRELEASED DESIGNS TAKES 2-3 Minutes to complete.<br />Please Wait for it to do so.<br />Donot Hit the Browser Refresh or any other Buttons<br><br>
            <h5>Unreleased Design File (CSV)</h5>
             <div class="clsText"><input type="file" id="file" name="file" ></div>
            <br /><br />
            <input type="submit" value="Check File"/>
            <input type="hidden" name="form_id" value="1"/>
            <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
            <br /><br />
           
            <?php
               if($formResult->cssClass == "success" && !isset($_SESSION['loadedunreleased'])){
                   ?><p>CLICK ON BELOW BUTTON TO LOAD UNRELEASED DESIGNS</p>
                     <input type="hidden" id="filename" name="filename" value="<?php  if(isset($_SESSION['ufpath'])){ echo $_SESSION['ufpath'];}?>"/>
                     <input type ="button" name="udesigns" id = "udesigns" value ="Load Unreleased Designs" onclick="loadUnreleasedDesigns();">
                   <?php

               }
            ?>
            </form>
        </div>        
    </fieldset>
    
    <div  class="grid_4" id="results2" name="results2" style="background:#DBECFF; margin-left:10px; width:60%;">Processing. Please wait... <img src="<?php echo $this->imageUrl("loading.gif"); ?>" /></div>


   

</div>
    <?php
    }
}
?>
