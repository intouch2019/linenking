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
                txndtDiv2.style.display = "none";
                $("#btnSend").attr("disabled", false);
                $("#btnSend2").attr("disabled", true);
            } else if(radioValue==10){
                  txndtDiv2.style.display = "block";
                   txndtDiv.style.display = "none";
                $("#btnSend2").attr("disabled", false);
                $("#btnSend").attr("disabled", true);
            }else{
                txndtDiv.style.display = "none";
                txndtDiv2.style.display = "none";
            }
        });
        
        $("input[type='button']").click(function(){  
            $("#btnSend").attr("disabled", true);
        });
        $("input[type='button']").click(function(){  
            $("#btnSend2").attr("disabled", true);
        });  
    });
    
function SendMailHdfc(){
    var txndt = document.getElementById("txndt1").value;
    var d1 = document.getElementById("from").value;
    var d2 = document.getElementById("to").value;
    
    if(d2=="" || d1=="" || txndt==""){
        alert("Please Fill All Required Fields..");
        $("input[type='button']").click(function(){  
            $("#btnSend").attr("disabled", false);
        });
        return;
    }
    
    window.location.href = "formpost/generateGSTNatchXL_sendMail.php?d1="+d1+"&d2="+d2+"&txndt="+txndt;
}

function SendMailAxis(){
    var txndt = document.getElementById("txndt2").value;
    var d1 = document.getElementById("from").value;
    var d2 = document.getElementById("to").value;

    if(d2=="" || d1=="" || txndt==""){
        alert("Please Fill All Required Fields..");  
        $("input[type='button']").click(function(){  
            $("#btnSend2").attr("disabled", false);
        });
        return;
    }
    window.location.href = "formpost/generateGSTNatchXLAxis_sendMail.php?d1="+d1+"&d2="+d2+"&txndt="+txndt;
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
                <input type ="radio" name="tallytype" id="tallytype" value="4"  required>Purchase XML</br>
                <input type ="radio" name="tallytype" id="tallytype" value="5"  required><l style="color: yellow">Generate GST Nach Report HDFC</l>
                <input type ="radio" name="tallytype" id="tallytype" value="10" required><l style="color: yellow">Generate GST Nach Report Axis</l><br/>
                <input type ="radio" name="tallytype" id="tallytype" value="6"  required>GST SaleBack Purchase Voucher<br/>
                <input type ="radio" name="tallytype" id="tallytype" value="7"  required>GST Credit Voucher 2019<br/>
               <input type ="radio" name="tallytype" id="tallytype" value="8"  required>GST DG Credit Voucher<br/>
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
                
                <div class="grid_6" id="txndtDiv" name="txndtDiv" style ="display: none">
                    Select Transaction date (HDFC):* &nbsp;&nbsp;&nbsp;&nbsp;              
                    <input id="txndt1" type="date" name="txndt1" style ="width:30%" value="" />
                    <input type = "button" style="margin-left: 10px;" id="btnSend" name="btnSend" value = "Send Email" onclick="SendMailHdfc()"/>
                    <br/><br/>
                </div>
                <div class="grid_6" id="txndtDiv2" name="txndtDiv2" style ="display: none">
                    Select Transaction date (AXIS) :* &nbsp;&nbsp;&nbsp;&nbsp;              
                        <input id="txndt2" type="date" name="txndt2" style ="width:30%" value="" />
                    <input type = "button" style="margin-left: 10px;" id="btnSend2" name="btnSend2" value = "Send Email" onclick="SendMailAxis()"/>
                    <br/><br/>
                </div>
                <div class="grid_8">                
                <!--<input type="button" value="Download" onclick="javascript:tallyXML();"/>-->
                <input type = "submit" value = "Download" />
                </div>
        </form>
        </fieldset>
        <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>               
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js" type="text/javascript"></script>
<script src="js/chosen/chosen.jquery.js" type="text/javascript"></script>
<script type="text/javascript"> $(".chzn-select").chosen(); $(".chzn-select-deselect").chosen({allow_single_deselect:true}); </script>

    <?php
    } //pageContent
}//class
?>