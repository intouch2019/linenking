<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");

class cls_admin_emandateapi extends cls_renderer{

        var $currUser;
        var $userid;
        
        function __construct($params=null) {
		//parent::__construct(array(UserType::Admin, UserType::CKAdmin));
		$this->currUser = getCurrUser();
		$this->userid = $this->currUser->id;
                  $this->params = $params;
        if (isset($params['status'])) {
            $this->status = $params['status'];
        } else {
            $this->status = "";
        }
       if (isset($params['message'])) {
            $this->message = $params['message'];
//            echo $this->message;
        } else {
            $this->message = "";
        }
        }

	function extraHeaders() {
        ?>
<script type="text/javascript" src="js/ajax.js"></script>
<script type="text/javascript" src="js/daterangepicker.jQuery.js"></script>


<!--<script type="text/javascript" src="js/ajax.js"></script>
<script type="text/javascript" src="js/ajax-dynamic-list.js">-->

</script>

<head><style>.input-disabled{background-color:#EBEBE4;border:1px solid #ABADB3;padding:2px 1px;}</style></head>>
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

<link rel='stylesheet' href='form-style.css' type='text/css' />  

<link rel="stylesheet" href="css/bigbox.css" type="text/css" />
<script type="text/javascript">

 $(function () {


//    var at_value='NACH00000000000020';//util code uat testing new on 3-05-2024
    var at_value='NACH00000000004689';//util code for live on 20-05-2024 | same for TZ, CK, LK
               $.ajax({
			type: "POST",
                        dataType: "json",
			url: "ajax/AesEncryption.php",
			data: "value1="+at_value,
                        success:function(data)
                        {
                         if(data.error==0)
                         {
                             var en_msg=data.encrypt_message;
//                            alert(en_msg);
                            $("#Utilcode").val("\\x"+en_msg);
                         }else if(data.error==1)
                         {
                           $("#Utilcode").val("");  
                         }
                             
                        }
                        });
                        
                        
    var at_value='FKPL';//short code on 29-07-2024 for live/test linenking
               $.ajax({
			type: "POST",
                        dataType: "json",
			url: "ajax/AesEncryption.php",
			data: "value1="+at_value,
                        success:function(data)
                        {
                         if(data.error==0)
                         {
                             var en_msg=data.encrypt_message;
//                            alert(en_msg);
                            $("#shortcode").val("\\x"+en_msg);
                         }else if(data.error==1)
                         {
                           $("#shortcode").val("");  
                         }
                             
                        }
                        });                                 
            });            



 function generateAes(id,value) {


//$("#"+id).on("change", function() { 
//    alert("changed");
//}); 

var value1 = value.toString();  

               $.ajax({
			type: "POST",
                        dataType: "json",
			url: "ajax/AesEncryption.php",
			data: "value1="+value1,
                        success:function(data)
                        {
                         if(data.error==0)
                         {
                             var en_msg=data.encrypt_message;
//                            alert(en_msg);
                            $("#"+id).val("\\x"+en_msg);
                         }else if(data.error==1)
                         {
                           $("#"+id).val("");  
                         }
                             
                        }
                        });
        }
        
        
 function generateAesChecksum() {

if($("#CustomerAccountNo").val()==="")
{
  alert("please enter Customer_AccountNo");
  $("#CustomerAccountNo").focus();
  return;
}

if($("#CustomerStartDate").val()==="")
{
  alert("please enter Customer_startDate");  
  $("#CustomerStartDate").focus();
  return;
}
if($("#CustomerExpiryDate").val()==="")
{
  alert("please enter Customer_ExpiryDate");  
  $("#CustomerExpiryDate").focus();
  return;
  }
  if($("#Customer_MaxAmount").val()==="")
{
  alert("please enter Customer_MaxAmount");
  $("#Customer_MaxAmount").focus();
  return;
}
if($("#filler_6").val()==="")
{
  alert("Please enter Bank Code in Filler 6");
  $("#filler_6").focus();
  return;
}
if($("#IFSCCode").val()==="")
{
  alert("Please enter Bank IFSC Code");
  $("#IFSCCode").focus();
  return;
}
var account_no=$("#CustomerAccountNo").val();
account_no=account_no.substr(2,account_no.length);
//alert(account_no)

var account_no_orignal="";
               $.ajax({
			type: "POST",
                        dataType: "json",
			url: "ajax/AESDecryption.php",
			data: "value1="+account_no,
                        success:function(data)
                        {
                         if(data.error==0)
                         {
                             account_no_orignal=data.decrypt_message;
                             genChecksum(account_no_orignal);

                         }else if(data.error==1)
                         {
                           return; 
                         }
                             
                        }
                        });


    }
    
    
 function genChecksum(account_no){
    var mobile = $("#CustomerMobile").val();
    var email = $("#CustomerEmailId").val();
    // Check if either mobile or email is provided
    if (mobile || email) {
    var checksumtext = account_no+"|"+$("#CustomerStartDate").val()+"|"+$("#CustomerExpiryDate").val()+"|"+$("#CustomerDebitAmount").val()+"|"+$("#Customer_MaxAmount").val(); 
//alert(checksumtext);


 $.ajax({
			type: "POST",
                        dataType: "json",
			url: "ajax/SHA2Encryption.php",
			data: "value1="+checksumtext,
                        success:function(data)
                        {
                         if(data.error==0)
                         {   
                             $('#CustomerAccountNo').attr('readonly', true);
                             $('#CustomerAccountNo').addClass('input-disabled');
                             $('#CustomerStartDate').attr('readonly', true);
                             $('#CustomerStartDate').addClass('input-disabled');
                             $('#CustomerExpiryDate').attr('readonly', true);
                             $('#CustomerExpiryDate').addClass('input-disabled');
                             $('#CustomerDebitAmount').attr('readonly', true);
                             $('#CustomerDebitAmount').addClass('input-disabled');
                             $('#Customer_MaxAmount').attr('readonly', true);
                             $('#Customer_MaxAmount').addClass('input-disabled');
                             var en_msg=data.encrypt_message;
//                            alert(en_msg);
                            $("#Check_Sum").val(en_msg);
//                            $('#CheckSum').addClass('input-disabled');
//                            
                             // Enable/disable the sponsor button based on CheckSum field value
                             enableDisableSponsorButton();
                         }else if(data.error==1)
                         {
                           $("#Check_Sum").val(""); 
                         }
                             
                        }
                        });
    } else {
        alert("Please provide either a mobile number or an email address.");
    }
 }
 
 function enableDisableSponsorButton() {
    var checkSumField = document.getElementById('Check_Sum');
    var sponsorButton = document.getElementById('sponsorButton');

    if (checkSumField.value.trim() === '') {
        sponsorButton.disabled = true;
    } else {
        sponsorButton.disabled = false;
    }
}
    
function isvalid(id,at_value,pattern,attribute_name){
    
           $.ajax({
			type: "POST",
                        dataType: "json",
			url: "ajax/validate.php",
			data: "at_value="+at_value+"&pattern="+pattern+"&att_name="+attribute_name,
                        success:function(data)
                        {
                            if(data.error==1)
                          {
                            alert(data.message);
                            $("#"+id).val("");
                            $("#"+id).focus();
                           }  
                          
                        }
                                
		});
   }
   
   
   
function maxamount_isvalid(id,at_value,pattern,attribute_name){
    
    var max_amount= $("#Customer_MaxAmount").val();
    if(max_amount.includes(".",1)===false)
    {
          alert("please entere Customer_MaxAmount in 2 Digit Decimal format");
          $("#Customer_MaxAmount").val("");
          $("#Customer_MaxAmount").focus();
          return;
        }

    
           $.ajax({
			type: "POST",
                        dataType: "json",
			url: "ajax/validate.php",
			data: "at_value="+at_value+"&pattern="+pattern+"&att_name="+attribute_name,
                        success:function(data)
                        {
                            if(data.error==1)
                          {
                            alert(data.message);
                            $("#"+id).val("");
                            $("#"+id).focus();
                           }  
                          
                        }
                                
		});
   }
   
   
   
function save( store_id)
{
    if( $("#CustomerMobile").val()==="" && $("#CustomerEmailId").val()==="")
    {
       alert("At least 1 value is required either customer mobile number or Email id."); 
       return false;
    }
var msg_id= $("#Msg_Id").val();   

$.ajax({
        type: "POST",
        dataType: "json",
        url: "ajax/saveemandate_request.php",
        data: "store_id="+store_id+"&msg_id="+msg_id,
        success:function()
                {

                }
       });

}

    function calculateExpiryDate() {
    var startDate = document.getElementById("CustomerStartDate").value;
    var today = new Date(); // Get today's date
    
    // Convert today's date to the format YYYY-MM-DD
    var formattedToday = today.getFullYear() + '-' + ('0' + (today.getMonth() + 1)).slice(-2) + '-' + ('0' + today.getDate()).slice(-2);
    
    // Validate the format of Customer_StartDate
    if (!isValidDateFormat(startDate)) {
        alert("Please enter Customer_StartDate in the format YYYY-MM-DD.");
        document.getElementById("CustomerExpiryDate").value = "";
        document.getElementById("CustomerStartDate").value ="";
        return;
    }
    
    // Validate if the entered start date is not earlier than today's date
    if (startDate < formattedToday) {
        alert("Start date cannot be earlier than today's date.");
        document.getElementById("CustomerExpiryDate").value = "";
        document.getElementById("CustomerStartDate").value = "";
        return;
    }
    
    var parts = startDate.split("-");
    var year = parseInt(parts[0], 10);
    var month = parseInt(parts[1], 10);
    var day = parseInt(parts[2], 10);

    // Calculate expiry date
    var expiryYear = year + 40;

    // Format month and day with leading zeros if needed
    var formattedMonth = ("0" + month).slice(-2);
    var formattedDay = ("0" + day).slice(-2);

    var expiryDate = expiryYear + "-" + formattedMonth + "-" + formattedDay;

    // Set the calculated expiry date to the Customer_ExpiryDate input
    document.getElementById("CustomerExpiryDate").value = expiryDate;
}


    function isValidDateFormat(dateString) {
    var regex = /^\d{4}-\d{2}-\d{2}$/;
    return regex.test(dateString);
}

</script>
<style>
    .required {
        color: red;
        font-weight: bold;
        font-size: 1.2em;
    }
</style>
        
        <?php
        }

        public function pageContent() {
            $currUser = getCurrUser();
//            print_r($currUser);
            $menuitem = "emandateapi";
            include "sidemenu.".$currUser->usertype.".php";
            $formResult = $this->getFormResult();
            
//            print_r($formResult);
            $db = new DBConn();
?>
<div class="grid_10">
    <?php
    
    $display="none";
    $num = 0;
    ?>
    <div class="box" style="clear:both;">
	<fieldset class="login">
	<legend>E Mandate API</legend>
<!--        <p>testing</p>-->
        <!--<form action="https://emandateut.hdfcbank.com/Emandate.aspx" method="POST" >-->
            <!--        <p>live</p>-->
            <form action="https://emandate.hdfcbank.com/Emandate.aspx" method="POST" >
		<div class="grid_12">
		<div class="grid_2">
		
		</div>
                <table ><tr><td align="centere">
                    Util Code <span class="required">*</span>:<br/>
                    <input type="text" class="input-disabled" name="UtilCode" id="Utilcode"  readonly="" required>
                </td><td>
                    Short Code <span class="required">*</span>:<br/>
                    <input type="text" name="Short_Code" id="shortcode"  readonly="" required>
               
                    
                </td></tr>
                
                <tr><td>
                    Merchant_PartyName:<br/>
                    <input type="text" name="Merchant_PartyName" id="MerchantPartyName" value="Fashionking Brands Pvt Ltd" readonly>
                </td><td>
                    Merchant_Category_Code <span class="required">*</span>:<br/>
                    <input type="text" name="Merchant_Category_Code" id="MerchantCategoryCode" value="U099" required readonly>
               
                    
                </td></tr>
                
                   <tr><td>
                    Merchant_Category_Desc:<br/>
                    <input type="text" name="Merchant_Category_Desc" id="MerchantCategoryDesc" value="Others" readonly>
                </td><td>
                    Merchant_CreditorName:<br/>
                    <input type="text" name="Merchant_CreditorName" id="MerchantCreditorName" value="<?php echo $this->getFieldValue("Merchant_CreditorName"); ?>" >
                </td></tr>
                   
                <tr><td>
                    Merchant_CreditorAccountNo:<br/>
                    <input type="text" name="Merchant_CreditorAccountNo" id="MerchantCreditorAccountNo" value="<?php echo $this->getFieldValue("Merchant_CreditorAccountNo"); ?>">
                </td><td>
                    CheckSum <span class="required">*</span>:<br/>
                    <input type="text" name="CheckSum" id="Check_Sum" value="<?php echo $this->getFieldValue("Check_Sum"); ?>"   required="" readonly=""><br>
                        <span style="color: #a9a9a9; " >click CHECKSUM to generate checksum</span>    
                </td></tr>
                
                <?php
                $msg_id="SP".$currUser->id;
                
                $query="select emandate_msgid from it_codes where id=".$currUser->id;
                $objq=$db->fetchObject($query);
                if($objq !=null)
                {
                   $msg_id .= sprintf("%05d",$objq->emandate_msgid); 
                }else{
               $msg_id .="00000";
                }
                
                ?>
                <tr><td>
                    MsgId <span class="required">*</span>:<br/>
                    <input type="text" name="MsgId" id="Msg_Id" value="<?php echo $msg_id; ?>" required readonly="">
                </td><td>
                    Customer_Name <span class="required">*</span>:<br/>
                    <input type="text" name="Customer_Name" id="CustomerName" value="<?php echo $this->getFieldValue("Customer_Name"); ?>" onchange="generateAes(this.id,this.value)"  required>
                </td></tr>
                
                <tr><td>
                    Customer_TelNo. :<br/>
                    <input type="text" name="Customer_TelphoneNo" id="CustomerTelNo" value="<?php echo $this->getFieldValue("Customer_TelNo"); ?>" onchange="generateAes(this.id,this.value)" >
                </td><td>
                    Customer_Mobile:<br/>
                    <input type="text" name="Customer_Mobile" id="CustomerMobile" value="<?php echo $this->getFieldValue("Customer_Mobile"); ?>" onchange="generateAes(this.id,this.value)" ><br>
                        <span style="color: #a9a9a9; " >Required either mobile no or Email id.</span>
                </td></tr>
                
                <tr><td>
                    Customer_EmailId:<br/>
                    <input type="text" name="Customer_EmailId" id="CustomerEmailId" onchange="generateAes(this.id,this.value)" value="<?php echo $this->getFieldValue("Customer_EmailId"); ?>" >
                </td><td>
                    Customer_AccountNo <span class="required">*</span>:<br/>
                    <input type="text" name="Customer_AccountNo" id="CustomerAccountNo" onchange="generateAes(this.id,this.value)" value="<?php echo $this->getFieldValue("Customer_AccountNo"); ?>" required>  
                </td></tr>
                
                <tr>
                    <td>
                        Customer_StartDate <span class="required">*</span>:<br/>
                        <input type="text" name="Customer_StartDate" id="CustomerStartDate" value="<?php echo $this->getFieldValue("Customer_StartDate"); ?>" placeholder="YYYY-MM-DD" onchange="calculateExpiryDate()" onchange="isvalid(this.id,this.value,'/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/','Customer StartDate')" required>
                    </td>
                        <td>
                        Customer_ExpiryDate <span class="required">*</span>:<br/>
                        <input type="text" name="Customer_ExpiryDate" id="CustomerExpiryDate" placeholder="YYYY-MM-DD" readonly required>
                        </td>
                </tr>    
                
                
                <tr><td>
                    Customer_DebitAmount :<br/>
                    <input type="text" name="Customer_DebitAmount" id="CustomerDebitAmount" value="<?php echo $this->getFieldValue("Customer_DebitAmount"); ?>" placeholder="For e.g. 100.00 0r 20000.00   " onchange="isvalid(this.id,this.value,'/^[0-9]*\.[0-9]*$/','Customer DebitAmount')" readonly="">
                </td><td>
                    Customer_MaxAmount <span class="required">*</span>:<br/>
                    <input type="text" name="Customer_MaxAmount" id="Customer_MaxAmount" value="<?php echo $this->getFieldValue("Customer_MaxAmount"); ?>" placeholder="For e.g. 100.00 0r 20000.00   " onchange="maxamount_isvalid(this.id,this.value,'/^[0-9]*\.[0-9]{1}[0-9]{1}$/','Customer MaxAmount')"  required> 
                </td></tr>    
                
                
                <tr><td>
                    Customer_DebitFrequency <span class="required">*</span>:<br/>
                    <input type="text" name="Customer_DebitFrequency" id="CustomerDebitFrequency" value="ADHO" required readonly="">
                </td><td>
                    Customer_SequenceType <span class="required">*</span>:<br/>
                    <input type="text" name="Customer_SequenceType" id="CustomerSequenceType" value="RCUR" required readonly="">  
                </td></tr>    
                
                
                <tr><td>
                   Customer IFSC Code <span class="required">*</span>:<br/>
                    <input type="text" name="Customer_InstructedMemberId" id="IFSCCode" value="<?php echo $this->getFieldValue("IFSC_Code"); ?>" required>
                </td><td>
                    Customer_Reference1:<br/>
                    <input type="text" name="Customer_Reference1" id="CustomerReference1" value="<?php echo $this->getFieldValue("Customer_Reference1"); ?>" onchange="generateAes(this.id,this.value)">    
                </td></tr>    
                
                
                <tr><td>
                    Customer_Reference2:<br/>
                    <input type="text" name="Customer_Reference2" id="CustomerReference2" value="<?php echo $this->getFieldValue("Customer_Reference2"); ?>" onchange="generateAes(this.id,this.value)">
                </td><td>
                    Channel <span class="required">*</span>:<br/>
                    <input type="text" name="Channel" id="Channel_id" value="<?php echo $this->getFieldValue("Channel"); ?>" required><br>
                        <span style="color: #a9a9a9; " >"Debit" for Debit Card , "Net" for Net-banking</span>  
                </td></tr>
                
                <tr><td>
                    <input type="text" name="Filler1" id="filler_1" value="Filler1"><br>
                        <span style="color: #9c9c9c00; " >Filler 1 </span>
                </td>
                <td>
                    <input type="text" name="Filler2" id="filler_2" value="Filler2"><br>
                        <span style="color: #9c9c9c00; " >Filler 2 </span>
                </td></tr>  
                
                
                <tr><td>
                    <input type="text" name="Filler3" id="filler_3" value="Filler3"><br>
                        <span style="color: #9c9c9c00; " >Filler 3 </span>
                </td><td>
                    <input type="text" name="Filler4" id="filler_4" placeholder="Filler4(Valid PAN Number)"><br>
                        <span style="color: #a9a9a9; " >Customer PAN number </span>
                </td></tr>  
                
                <tr><td>
                    <input type="text" name="Filler5" id="filler_5" placeholder="Filler5(Account Type)" required=""><br>
                        <span style="color: #a9a9a9; " >"S” for Savings ,“C” for Current or “O” for “Other”. </span>
                </td><td>
                    <input type="text" name="Filler6" id="filler_6" placeholder="Filler6(Bank Code)" required><br>
                        <span style="color: #9c9c9c00; " >Filler 6 </span>
                </td></tr>  
                
                <tr><td>
                    <input type="text" name="Filler7" id="filler_7" value="Filler7"><br>
                        <span style="color: #9c9c9c00; " >Filler 7 </span>
                </td><td>
                    <input type="text" name="Filler8" id="filler_8" value="Filler8"><br>
                        <span style="color: #9c9c9c00; " >Filler 8 </span>
                </td></tr>  
                
                <tr><td>
                    <input type="text" name="Filler9" id="filler_9" placeholder="Filler9"><br>
                        <span style="color: #9c9c9c00; " >Filler 9 </span>
                </td><td>
                    <input type="text" name="Filler10" id="filler_10" placeholder="Filler10"><br>
                        <span style="color: #9c9c9c00; " >Filler 10 </span>
                </td></tr>  
                
                </table>
                </div>
                     
                <div class="grid_12" style="padding:10px;" id="resp">
                    <input type="button" name="addattr1" id="addattr1" value="CHECKSUM" onclick="generateAesChecksum()" style="background-color:#34de63;"/>
                    <input type="submit"  value="SPONSOR"  id="sponsorButton" style="background-color:#34de63;" onclick="save(<?php echo $currUser->id;?>)" disabled/>
                
                        <?php if ($this->status !="") { 
                           if($this->status==0){
                           ?>
                <p>
                    <span id="statusMsg" class="success" >Registration Successfull</span>
                </p>
                       <?php }else { ?>
                           
                        <p>
                    <span id="statusMsg" class="error" ><?php echo $this->message; ?></span>
                </p>
                           
                      <?php } } ?>
		</div>
            </form>
	</fieldset>
    </div> <!-- class=box -->
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js" type="text/javascript"></script>
<script src="js/chosen/chosen.jquery.js" type="text/javascript"></script>
<script type="text/javascript"> $(".chzn-select").chosen(); $(".chzn-select-deselect").chosen({allow_single_deselect:true}); </script>


<?php
	}
}
?>