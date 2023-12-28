<?php
require_once "view/cls_renderer.php";
require_once "lib/items/clsItems.php";
require_once "lib/core/Constants.php";
require_once ("lib/db/DBConn.php");

class cls_admin_tallytransfer extends cls_renderer {
//    var $params;
    var $currStore;

    function __construct($params=null) {
        $this->currStore = getCurrUser();
//        $this->params = $params;
        if (!$this->currStore) { return; }
    }

    function extraHeaders() {
        if (!$this->currStore) {
            return;
	}
?>
<link rel="stylesheet" href="js/chosen/chosen.css" />
<script type="text/javascript" src="js/ajax.js"></script>
<script type="text/javascript" src="js/ajax-dynamic-list.js">
    
</script>
<link rel="stylesheet" href="css/bigbox.css" type="text/css" />
<script type="text/javascript">
$(function(){
     var dates = $( "#from, #to, #txndt" ).datepicker({
    		changeMonth: true,
                changeYear: true,
    		numberOfMonths: 1,
    		dateFormat: 'dd-mm-yy',
    		onSelect: function( selectedDate ) {
    			var option = this.id == "from" || "txndt" ? "minDate" : "maxDate",
    			instance = $( this ).data( "datepicker" ),
    			date = $.datepicker.parseDate(
    				instance.settings.dateFormat ||
    				$.datepicker._defaults.dateFormat,
    				selectedDate, instance.settings );
    			dates.not( this ).datepicker( "option", option, date );
    		}
    	});
});

$(document).ready(function(){
        $("input[type='radio']").click(function(){
            var radioValue = $("input[name='tallytype']:checked").val();
            if(radioValue==5){
//                alert("Your are a - " + radioValue);
                txndtDiv.style.display = "block";
                $("#btnSend").attr("disabled", false);
            }else{
                txndtDiv.style.display = "none";
            }
        });
        
        $("input[type='button']").click(function(){  
            $("#btnSend").attr("disabled", true);
        });        
    });
    
function SendMail(){
    var txndt = document.getElementById("txndt").value;
    var d1 = document.getElementById("from").value;
    var d2 = document.getElementById("to").value;
    
    if(d2=="" || d1=="" || txndt==""){
        alert("Please Fill All Required Fields..");        
        return;
    }
    
    var ajaxURL = "formpost/generateGSTNatchXL_sendMail.php?d1="+d1+"&d2="+d2+"&txndt="+txndt;
//    alert(ajaxURL);
         $.ajax({
         url:ajaxURL,
            //dataType: 'json',
            cache: false,
            success:function(html){
                if(html=="No Record Found"){
                    alert("No Record Found");
                }else{
                    alert("Email Sent Successfully.");
                }
//                alert(html);
            }
        });
    
    
}

//function tallyXML(){
// //var xmltype = $("tallytype").val();
// var xmltype = document.getElementById('tallytype').value;
// alert(xmltype);
//}
</script>  
<?php

    } // extraHeader

    public function pageContent() {
        //if ($this->currStore->usertype != UserType::Admin && $this->currStore->usertype != UserType::CKAdmin ) { print "Unauthorized Access"; return; }
        $formResult = $this->getFormResult();
        $menuitem="tallytransfer";
        include "sidemenu.".$this->currStore->usertype.".php";
//        $db = new DBConn();
        ?>
<div class="grid_10">
        <fieldset>  
        <legend>Tally XML Download</legend>
        <form id="tallytranfer" name="tallytransfer"  method="POST" action="formpost/generateTallyXML.php">                     
                Select the type of Tally XML to download from below: <br/>
                <input type ="radio" name="tallytype" id="tallytype" value="1"  required>Receipt Voucher 
                <input type ="radio" name="tallytype" id="tallytype" value="2"  required>Sales Voucher 
                <input type ="radio" name="tallytype" id="tallytype" value="3"  required>Credit Voucher 
                <input type ="radio" name="tallytype" id="tallytype" value="4"  required>Purchase XML
                <input type ="radio" name="tallytype" id="tallytype" value="5"  required>Generate GST Nach Report
                <input type ="radio" name="tallytype" id="tallytype" value="6"  required>GST SaleBack Purchase Voucher
                <input type ="radio" name="tallytype" id="tallytype" value="7"  required>GST Credit Voucher 2019
               <input type ="radio" name="tallytype" id="tallytype" value="8"  required>GST DG Credit Voucher
                <input type ="radio" name="tallytype" id="tallytype" value="9"  required>Debit Note Voucher Saleback
                <br/><br/>
                <div class="grid_5" id="storeDiv" name="storeDiv">
                    Select date range from below :*<br/>                                         
                    From * :
                    <input id="from" type="text" name="from" style ="width:30%" value="" required/> &nbsp;&nbsp;&nbsp;&nbsp;                    
                    To * :
                    <input id="to" type="text" name="to" style ="width:30%"  value="" required/>                   
                </div>                  
                <br/><br/><br/>
                
                <div class="grid_5" id="txndtDiv" name="txndtDiv" style ="display: none">
                    Select Transaction date :* &nbsp;&nbsp;&nbsp;&nbsp;              
                    <input id="txndt" type="text" name="txndt" style ="width:30%" value="" />
                    <input type = "button" id="btnSend" name="btnSend" value = "Send Email" onclick="SendMail()"/>
                    <br/><br/>
                </div>
                
                <div class="grid_8">                
                <!--<input type="button" value="Download" onclick="javascript:tallyXML();"/>-->
                <input type = "submit" value = "Download" />
                <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>               
                </div>
        </form>
        </fieldset>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js" type="text/javascript"></script>
<script src="js/chosen/chosen.jquery.js" type="text/javascript"></script>
<script type="text/javascript"> $(".chzn-select").chosen(); $(".chzn-select-deselect").chosen({allow_single_deselect:true}); </script>

    <?php
    } //pageContent
}//class
?>