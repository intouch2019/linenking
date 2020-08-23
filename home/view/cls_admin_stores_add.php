<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once "lib/core/Constants.php";

class cls_admin_stores_add extends cls_renderer {
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
        // --- Using the default options:
        //$("h2.expand").toggler();
        // --- Other options:
        //$("h2.expand").toggler({method: "toggle", speed: "slow"});
        //$("h2.expand").toggler({method: "toggle"});
        //$("h2.expand").toggler({speed: "fast"});
        //$("h2.expand").toggler({method: "fadeToggle"});
        $("h2.expand").toggler({method: "slideFadeToggle"});
        $("#content").expandAll({trigger: "h2.expand"});
    });
    // form fields description structure
    var a_fields = {
        'storec':{'l':'Username','r': true,'t':'t_storec'},
        'storep':{'l':'Password','r': true,'f':'alphanum','m':'storep2','t':'t_storep'},
        'storep2':{'l':'Password copy','r': true,'f':'alphanum','t':'t_storep2'},
        'dealer_name':{'l':'Dealer Name','r': true,'t':'t_dealer_name'},
        'address':{'l':'Store Address','r':true,'t':'t_address'},
        'city':{'l':'City','r':true,'f':'alpha','t':'t_city'},
        'zip':{'l':'Zip Code','r':true,'f':'num','t':'t_zip'},
        'name':{'l':'Contact Name','r':true,'t':'t_name'},
        'phone':{'l':'Phone Number 1','r':true,'f':'phone','mn':10,'mx':14,'t':'t_phone'},
        'email':{'l':'E-mail 1','r':true,'f':'email','t':'t_email'},
        'vat':{'l':'VAT/TIN number','r':true,'t':'t_vat'},
        'discval':{'l':'Dealer Discount','r':true,'t':'t_dealer_discount'}
    },
    o_config = {
        'to_disable' : ['Submit', 'Reset'],
        'alert' : 1
    }

    // validator constructor call
    var v = new validator('registration', a_fields, o_config);

    function deletestore(storecode)
    {
        var code=storecode;
        var r=confirm("remove this store ?");
        if (r==true) {
            var ajaxUrl = "formpost/removeStore.php?code="+code;
            $.getJSON(ajaxUrl, function(data){
                if (data.error=="0")
                    alert(data.message);
            });          
        }
        else {
            alert(data.message);
        }
        //window.location.reload();
    }


                        function Hidenatch() {
                                            
                                            document.getElementById("natch").style.display = "none";
                                        }
                                        
                        function Shownatch() {
                            
                                            document.getElementById("natch").style.display = "block";
                                        }
                                        
                                                                              
      function showregion(reasontype){
          
       //  alert('hcciiiiiii');
          //exit; 
 
                 var ajaxURL = "ajax/getregionbystate.php?stateid="+reasontype;
               //  alert(ajaxURL);
         $.ajax({
         url:ajaxURL,
            //dataType: 'json',
            cache: false,
            success:function(html){
                ///alert(html);
             
                   if(html=='region not set'){alert('Regions Are Not Set For Selected State ,Please Contact To Intouch Admin To Add Region');}
               
                    $('#regionid').empty().append('<option value=0 >Select region</option>');
                    $('#regionid').append(html); 
                    $("#regionid").selectpicker('refresh'); 

            }
        });

            }
            
            
            function showtallyData() {
                //alert("hiii");
                document.getElementById("Tallydata").style.display = "block";
            }
            function hidetallyData() {
                document.getElementById("Tallydata").style.display = "none";
                ///alert("hiii");
            }
           function blockSpecialChar(e){
           var k;        
           document.all ? k = e.keyCode : k = e.which;
           return ((k > 64 && k < 91) || (k > 96 && k < 123) || k == 8 || k == 32 || (k >= 48 && k <= 57));
           }
               
               
               
                               
function discountsets(discval){
       // alert(discval);
        //document.getElementById("discval").value=discval;
        
        if(discval==12 ||discval==24 ||discval==26)
        {
         var  newcashval=0.0;
         var  newnonclaim=0.0;
         var  effecteddisc=0.0;
        //var discval=document.getElementById("discval").value;
    
        if(  $('input[name="claimed"]').is(':checked')  &&  $('input[name="cashes"]').is(':checked'))
        {
            //alert('yes1');
          var newnonclaim=(100-discval)*0.01;
          var newcashval=(100-discval)*0.02;
          effecteddisc=(+discval + +newnonclaim + +newcashval);  
   
  
        }
   
          else if(!$('input[name="claimed"]').is(':checked') && !$('input[name="cashes"]').is(':checked'))
        {
           //alert('yes2');
           effecteddisc=(+discval + +newnonclaim + +newcashval);
        }
        else if($('input[name="claimed"]').is(':checked') &&  !$('input[name="cashes"]').is(':checked') )
        {
        //alert('yes3');
            newnonclaim=(100-discval)*0.01;
            effecteddisc=(+discval + +newnonclaim + +newcashval);
        }
        else if( !$('input[name="claimed"]').is(':checked')  && $('input[name="cashes"]').is(':checked'))
        {
         // alert('yes4');
           newcashval=(100-discval)*0.02;
           effecteddisc=(+discval + +newnonclaim + +newcashval);
        }

         document.getElementById("additional_discount").value=Math.round(effecteddisc * 100) / 100;
        
    }
}

            
function isCheckedById() {
 
    var newcashval=0.0;
    var newnonclaim=0.0;
    var effecteddisc=0.0;
    var discval=document.getElementById("discval").value;
//alert(discval);
if(discval>0){
        if(  $('input[name="claimed"]').is(':checked')  &&  $('input[name="cashes"]').is(':checked'))
        {
           //alert('yes1');
            var newnonclaim=(100-discval)*0.01;
            var newcashval=(100-discval)*0.02;
            effecteddisc=(+discval + +newnonclaim + +newcashval);  
        }

        else if(!$('input[name="claimed"]').is(':checked') && !$('input[name="cashes"]').is(':checked'))
        {
            //alert('yes2');
            effecteddisc=(+discval + +newnonclaim + +newcashval);
        }
        else if($('input[name="claimed"]').is(':checked') &&  !$('input[name="cashes"]').is(':checked') )
        {
           //alert('yes3');
            newnonclaim=(100-discval)*0.01;
             effecteddisc=(+discval + +newnonclaim + +newcashval);
        }
        else if( !$('input[name="claimed"]').is(':checked')  && $('input[name="cashes"]').is(':checked'))
        {
         // alert('yes4');
            newcashval=(100-discval)*0.02;
            effecteddisc=(+discval + +newnonclaim + +newcashval);
        }

            document.getElementById("additional_discount").value=Math.round(effecteddisc * 100) / 100; //Math.round(effecteddisc * 100) / 100
   }

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
    <fieldset>
        <legend>Add Store</legend>
        <div class="grid_12">
            <div class="addstore">
                <label>* - required fields</label><br><br>
                <form action="formpost/addStore.php" method="post" name="registration" onsubmit="return v.exec()">
                    <p class="grid_4">
                        <label id="t_storec">*Store Login Id: </label>
                        <input type="text" name="storec"  value="<?php echo $this->getFieldValue('storec'); ?>" required >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    </p>
                    <p class="grid_4">
                        <label id="t_storep">*Store Password: </label>
                        <input type="password" name="storep" value="<?php echo $this->getFieldValue('storep'); ?>" required>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    </p>    
                    <p class="grid_4">
                        <label id="t_storep2">*Confirm Password: </label>
                        <input type="password" name="storep2" value="<?php echo $this->getFieldValue('storep2'); ?>" required >
                    </p>    
                    
                    <p class="grid_12">
                        <label id="t_dealer_name" >*Dealer Name: </label>
                        <input type="text" name="dealer_name" value="<?php echo $this->getFieldValue('dealer_name'); ?>" required>
                    </p>
                    <p class="grid_12">
                        <label id="t_address">*Address: </label>
                        <input type="text" name="address" value="<?php echo $this->getFieldValue('address'); ?>" required>
                    </p>
                    
                    
                    
                     <?php  
                        $query = "select id, state from states";
                      //  print $query; ///
                        $obj_states = $db->fetchObjectArray($query);
                        ?>
                    <p class="grid_12" style="width:40%">
                    <label>*State: </label>
                    <select name="nstate" id="stateid" class="selectpicker form-control" data-show-subtext="true" data-live-search="true" onchange="showregion(this.value)">
                            <option value="0" disabled="" selected="">Select State</option>
                            <?php 
                            foreach($obj_states as $state){
                                 $selected = "";
                                ?>
                             <option value="<?php echo $state->id;?>"><?php echo $state->state;?></option>
                             

                            <?php 
                            
                            
                            }?>
                    </select>       
                    </p>
                    
                       <p class="grid_12" style="width:40%">
                                <label>*Region: </label>
                              <select id="regionid" name="region" class="selectpicker form-control" data-show-subtext="true" data-live-search="true" required >
                                            <option value=0 disabled="">Select Region</option>
                                        </select>     
                         </p>
                            
                            
                    
                    
                    <p class="grid_12">
                        <label id="t_city">*City: </label>
                        <input type="text" name="city" value="<?php echo $this->getFieldValue('city'); ?>" required >
                    </p>
                    <p class="grid_12">
                        <label id="t_area">Area: </label>
                        <input type="text" name="area" value="<?php echo $this->getFieldValue('area'); ?>">
                    </p>
                    <p class="grid_12">
                        <label id="t_location">Location: </label>
                        <input type="text" name="location" value="<?php echo $this->getFieldValue('location'); ?>">
                    </p>
                    <p class="grid_12">
                        <label id="t_zip">*Postal Area Code/Zip code: </label>
                        <input type="text" name="zip" value="<?php echo $this->getFieldValue('zip'); ?>" required >
                    </p>
                    <p class="grid_12">
                        <label id="t_name">*Owner Name: </label>
                        <input type="text" name="name" value="<?php echo $this->getFieldValue('name'); ?>" required >
                    </p>
                    <p  class="grid_12">
                        <label id="t_phone">*Phone Number 1: </label>
                        <input type="text" name="phone" value="<?php echo $this->getFieldValue('phone'); ?>" required >
                    </p>
                    <p class="grid_12">
                        <label id="t_phone2">Phone Number 2: </label>
                        <input type="text" name="phone2" value="<?php echo $this->getFieldValue('phone2'); ?>">
                    </p>
                    <p class="grid_12">
                        <label id="t_email">*Email Address 1: </label>
                        <input type="text" name="email" value="<?php echo $this->getFieldValue('email'); ?>" required>
                    </p>
                    <p class="grid_12">
                        <label id="t_email2">Email Address 2: </label>
                        <input type="text" name="email2" value="<?php echo $this->getFieldValue('email2'); ?>">
                    </p>
                    <p class="">
<!--                        <label id="t_vat">*VAT/TIN number: </label>-->
                        <input type="hidden" name="vat" value="<?php echo $this->getFieldValue('vat'); ?>">
                    </p>  
                    <p class="grid_12">
                        <label id="t_vat">*GSTIN number: </label>
                        <input type="text" name="gstin_no" value="<?php echo $this->getFieldValue('gstin_no'); ?>" required>
                    </p>
                    <p class="grid_12">
                        <label id="t_tally">*Tally Name: </label>
                        <input type="text" name="tally_name" value="<?php echo $this->getFieldValue('tally_name'); ?>" required>
                    </p> 
                    
                      <p class="grid_12">
                                <label id="t_tally">Distance </label>
                                <input type="text" name="distance" value="<?php echo $this->getFieldValue('distance'); ?>">
                      </p> 
                            
                            
<!--                    <p class="grid_3">                        
                        <label id="t_dealer_discount">*Dealer Discount:</label>
                        <input type="text" name="dealer_discount" value="<?php //echo $this->getFieldValue('dealer_discount'); ?>" required>
                    </p>
                    <p class="grid_3">                        
                        <label id="t_dealer_discount">Additional Discount</label>
                        <input type="text" name="additional_discount" value="<?php //echo $this->getFieldValue('additional_discount'); ?>" readonly >
                    </p>-->
                      
                      
                            <p class="grid_3">
                                <label>Dealer Discount: *</label>
                         <?php if ($this->currStore->usertype == UserType::CKAdmin) { ?>
                                    <input  type="text" name="discval" id="discval"  value="<?php echo $this->getFieldValue('discval'); ?>" oninput="isCheckedById();" required>
                                <?php }
                                
                                
                                elseif($this->currStore->usertype == UserType::Admin ) {
                                    
                                     $query ="select * from it_dealer_discount order by discount asc";     
                                        $obj_deler_disc = $db->fetchObjectArray($query);
                                        
                                    
                                    ?>
                                         
                               
                                <select name="discval" id="discval" onclick="discountsets(this.value);" required  >
                                    <option value="0" disabled="" selected="">Select Discount</option>
        <?php
        foreach ($obj_deler_disc as $disc) {
              $selected = "";
            
            ?>
                                        <option value="<?php echo $disc->discount; ?>">
            <?php echo $disc->discount; ?></option>
        <?php } ?>
                                </select>       
                            
                               <?php } ?>
    
                                    
                            </p>
                    
                    
                    
                    
                    <p class="grid_3">                        
                        <label id="t_dealer_discount">Total Effecive  Discount Given: </label>
                        
                        <input type="text" name="additional_discount" id="additional_discount" value="" readonly >
                    </p>
                      
                    <p >
<!--                    <label>Transport : </label>-->
                    <input type="hidden" name="transport" value="<?php echo $this->getFieldValue('transport'); ?>">
                </p>
                <p class="grid_12">
<!--                    <label>Octroi : </label>-->
                    <input type="hidden" name="octroi" value="<?php echo $this->getFieldValue('octroi'); ?>">
                </p>
<!--                <p class="grid_3">
                    <label>Cash : </label>
                    <input type="text" name="cash" value="<?php// echo $this->getFieldValue('cash'); ?>">
                </p>
                <p class="grid_3">
                    <label>Non Claim : </label>
                    <input type="text" name="nonclaim" value="<?php// echo $this->getFieldValue('nonclaim'); ?>">
                </p>-->
<!--                <p class="grid_6"><br/><br/></p>
                <br/><br/>-->
                <!--Note: Below MSL entry access only to:-
                   Intouch-admin, Koushik,kunal
                -->
                
                
                
                               <?php if ($this->currStore->usertype == UserType::CKAdmin || $this->currStore->usertype == UserType::Admin ) { ?>
                            <p class="grid_1">
                                <label>Cash:</label>
                                <input type="checkbox"  name="cashes"  value="1" onclick="isCheckedById();" />
                            
                            </p>
                            
                            <p class="grid_1">
                                <label>NonClaim:</label>
                               <input type="checkbox" name="claimed"  value="1" onclick="isCheckedById();" />
                            
                            
                            </p>
                            
                                 <?php } ?>
                
                <?php if ($this->currStore->usertype==UserType::Admin || $this->currStore->usertype==UserType::CKAdmin) { ?>
                <p class="grid_12">
                    <label>Minimum Stock Level: </label><br>
                    <input type="text" name="msl" style='width:30%' value="<?php echo $this->getFieldValue('msl'); ?>">
                </p>
                
                  <p class="grid_12">
                                    <label>Is Store Closed: </label><br>
                                    <input type ="radio" name="is_closed" id="is_closed" style="width:5%"  value="0" checked="checked">No
                                    <input type ="radio" name="is_closed" id="is_closed" style="width:5%"  value="1">Yes
                  </p>
                         
                  
                
                <p class="grid_12">
                        <label> Select Store Type: </label><br>
                        <input type ="radio" name="storetype" id="storetype" style="width:5%" value="<?php echo StoreType::NormalStore;?>" required><?php echo trim(StoreType::getName(StoreType::NormalStore));?>
                        <input type ="radio" name="storetype" id="storetype" style="width:5%" value="<?php echo StoreType::Store50percent;?>" required><?php echo trim(StoreType::getName(StoreType::Store50percent));?>
                </p>
                <?php } ?>
                
                
                
                
                        
                
                 <p class="grid_12">
                        <label>Is Auto Refill enabled: </label><br>
                        <input type ="radio" name="is_autorefill" id="is_autorefill" style="width:5%"  value="0" required >No
                        <input type ="radio" name="is_autorefill" id="is_autorefill" style="width:5%"  value="1" required>Yes
                    </p>
                    <?php // } if($this->currStore->usertype==UserType::Admin || $this->currStore->usertype==UserType::CKAdmin){?>
                    <p class="grid_12">
                        <label>Is Standing/Base Stock feature enabled: </label><br>
                        <input type ="radio" name="sbstock_active" id="sbstock_active" style="width:5%" value="0" required >No
                        <input type ="radio" name="sbstock_active" id="sbstock_active" style="width:5%" value="1" required >Yes
                    </p>
                     <p class="grid_12">
                                    <label>Composite Billing Opted : </label><br>
                                    <input type ="radio" name="composite_billing_opted" id="composite_billing_opted" style="width:5%" value="0" checked="checked" required>No
                                    <input type ="radio" name="composite_billing_opted" id="composite_billing_opted" style="width:5%" value="1" required>Yes
                     </p>
                
                     
                       <p class="grid_12">
                                <label>Is Tally Transfer feature enabled: </label><br>
                                <input type ="radio" name="is_tallyxml" id="is_tallyxml" style="width:5%"  value="0" required>No
                                <input type ="radio" name="is_tallyxml" id="is_tallyxml" style="width:5%"  value="1" required>Yes
                         </p>

            
                         
                     
                     
                
                <p class="grid_12">
                     <label>PANCARD No: </label><br>                     
                     <input type ="text" name="pancard_no" id="pancard_no" style="width:30%"  value="<?php echo $this->getFieldValue('pancard_no'); ?>" required>                     
                </p>
                <p class="grid_12">
                     <label>Select Tax Type:</label>
                     <input type ="radio" name="taxtype" id="taxtype" style="width:5%" value="<?php echo taxType::VAT;?>" required><?php echo trim(taxType::getName(taxType::VAT));?>
                     <input type ="radio" name="taxtype" id="taxtype" style="width:5%" value="<?php echo taxType::CST;?>" required><?php echo trim(taxType::getName(taxType::CST));?>           
                </p>
                
                <p class="grid_12" id="natchrad">
                        <label>Is Nach Required: </label><br>
                        <input type ="radio" name="is_natch" id="is_natch1" style="width:5%" value="0" onclick="Hidenatch()">No
                        <input type ="radio" name="is_natch" id="is_natch2" style="width:5%" value="1" onclick="Shownatch()">Yes
                    </p>
                 
                     <div id="natch" style="display:none" >
                        <p class="grid_3">
                    <label>UMRN*</label>
                     <input type="text" id="umrn" name="umrn" value="">
                    </p>
                    
                    <p class="grid_3">
                        <label>Cust. To Be Debited</label>
                        <input type="text" id="cust_dbt" name="cust_tobe_debtd" value="">
                    </p>
                    <p class="grid_3">
                        <label>Cust. IFSC/MCR</label>
                        <input type="text" id="cust_ifsc" name="cust_ifsc_mcr" value="">
                    </p>
                    <p class="grid_3">
                        <label>Cust. Debit Acc.</label>
                        <input type="text" id="cust_dbt_acc" name="cust_debit_account" value="">
                    </p>
                        
                    </div>
                    
                <p class="grid_12">
                    <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
                </p>
                <p align="center" class="grid_10">                    
                    <input type="submit" value="Add" style="width:35%">
                </p>    
                </form>
                <?php unset($_SESSION['form_post']);?>
            </div>
        </div>
    </fieldset>



</div>
    <?php
    }
}
?>
