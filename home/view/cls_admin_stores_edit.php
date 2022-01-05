<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once "lib/core/Constants.php";

class cls_admin_stores_edit extends cls_renderer {
    var $currStore;
    var $storeid;
    var $state_id;
    function __construct($params=null) {
        $this->currStore = getCurrUser();
        $this->params = $params;
	if (isset($params['id'])) { $this->storeid = $params['id']; }

        
               
          if (isset($params['state_id'])) {
            $this->state_id = $params['state_id']; //   var $state_id;// //
        }
        
        
        
        }
     

    function extraHeaders() { ?>
    <?php
    }
    public function pageContent() {
        //if ($this->currStore->usertype == UserType::Admin || $this->currStore->usertype == UserType::CKAdmin) {} else { print "Unauthorized Access"; return; }
        if (getCurrUser()) {
            $menuitem="stores";
            include "sidemenu.".$this->currStore->usertype.".php";
        }
        $formResult = $this->getFormResult();
        $db = new DBConn();
	//$store = $db->fetchObject("select * from it_codes where id=$this->storeid and usertype=".UserType::Dealer);
        $store = $db->fetchObject("select c.*,d.* from it_codes c left outer join it_ck_storediscount d on c.id=d.store_id where c.id=$this->storeid and usertype=".UserType::Dealer);
        //print "select c.*,d.* from it_codes c left outer join it_ck_storediscount d on c.id=d.store_id where c.id=$this->storeid and usertype=".UserType::Dealer;
       // print "select c.*,d.* from it_codes c left outer join it_ck_storediscount d on c.id=d.store_id where c.id=$this->storeid and usertype=".UserType::Dealer;
        if (!$store) { print "Store not found [$this->storeid]. Please report this error"; return; }
        ?>
 <script>
               
                        function Hidenatch() {
                                             //alert(document.getElementByName('is_natch').value);
                                            
                                            document.getElementById("natch").style.display = "none";
                                            //document.getElementById("umrn").value="";
                                            //document.getElementById("cust_dbt").value="";
                                            //document.getElementById("cust_ifsc").value="";
                                            //document.getElementById("cust_dbt_acc").value="";
                                        }
                                        
                        function Shownatch() {
                            
                                            document.getElementById("natch").style.display = "block";
                                        }
                                        
                
 function backtoedit(){
                    
                   // alert('hiiiiiiiiiii');admin/stores
    window.location.href ="admin/stores";
    
    }
    
                                        
//function show(val){
////          alert(val); 
//          //alert(a);
//            var store_id = '<?php //echo $this->storeid; ?>';
////              alert(store_id);
//      if(val){
//   //     document.getElementById("state_id").value = val;
//       window.location.href ="admin/stores/edit/id="+store_id+"/state_id="+val;
//    }  
//}
//
//function show1(){
////        var store_id = '<?php //echo $this->storeid; ?>';
//        var val = document.getElementById("stateid").value;
//        show(val);
////       window.location.href ="admin/stores/edit/id="+store_id+"/state_id="+val;
//}
// $(document).ready(function() {
//     var state_id = '<?php// echo $this->state_id; ?>';
//     if(state_id == ""){
//         show1();
//     }
//    });
    
    
    
    
    
    function discountsets(discval){
       // alert(discval);
        //document.getElementById("discval").value=discval;
        
        //if(discval==12 ||discval==24 ||discval==26)
        if(discval==12 ||discval==16 ||discval==18 ||discval==21 ||discval==24 ||discval==26)
        {
            
        document.getElementById("discval").value = discval;
        //adddisc
        
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

        // document.getElementById("adddisc").value=effecteddisc;  //Math.round(effecteddisc * 100) / 100;
         document.getElementById("adddisc").value=Math.round(effecteddisc * 100) / 100;
    }
}


      
function isCheckedById() {
 
    var  newcashval=0.0;
    var  newnonclaim=0.0;
    var  effecteddisc=0.0;
    var discval=document.getElementById("discval").value;
//alert(discval);
if(discval>0){
//alert(discval);
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

         //document.getElementById("adddisc").value=effecteddisc;  //Math.round(effecteddisc * 100) / 100;
         
         document.getElementById("adddisc").value=Math.round(effecteddisc * 100) / 100;

}
}



  function showregion(reasontype){
          
       //  alert('hiiiiiiiiiiiii');
          //exit; 
 
                 var ajaxURL = "ajax/getregionbystate.php?stateid="+reasontype;
               //  alert(ajaxURL);
         $.ajax({
         url:ajaxURL,
            //dataType: 'json',
            cache: false,
            success:function(html){
             //   alert(html);
             
             if(html=='region not set'){alert('Regions Are Not Set For Selected State ,Please Contact To Intouch Admin To Add Region');}
                    $('#regionid').empty().append('<option value=0 >Select region</option>');
                    $('#regionid').append(html); 
                    $("#regionid").selectpicker('refresh'); 

            }
        });

            }
            
    
    
                       
                        </script>
<div class="grid_10">
    <fieldset>
        <legend>Edit Store</legend>
        <div class="grid_12">
            <div class="addstore">
                <input type="hidden"   value="< Back" id="back" onclick="backtoedit();"><br>
                <label><h5>* - required fields</h5></label><br><br>
                <form action="formpost/editStore.php" method="post" name="registration">
                    <input type="hidden" name="storeid" value="<?php echo $this->storeid; ?>" />
                    <input type="hidden" name="usrname" value="<?php echo $this->currStore->code; ?>" />
                    <input type="hidden" name="usrid" value="<?php echo $this->currStore->id; ?>" />
                    
                    <p class="grid_6">
                        <label id="t_storep">*Store Password:</label>
                        <input type="password" name="password" id="password" style="width:20%;" value="">(leave this blank if you don't want to change the password)
                    </p>
                    <p class="grid_6">
                        <label id="t_storep2">*Confirm Password: </label>
                        <input type="password" name="password2" id="password" style="width:20%;" value="">
                    </p>
                    <br><br>
                    <p class="grid_12">
                        <label id="t_dealer_name">*Dealer Name: </label>
                        <input type="text" name="store_name" value="<?php echo $this->getFieldValue('store_name',$store->store_name); ?>" required>
                    </p>
                    <p class="grid_12">
                        <label id="t_address">*Address: </label>
                        <input type="text" name="address" value="<?php echo $this->getFieldValue('address',$store->address); ?>" required>
                    </p>
<!--                       <?php  
                      //  $query = "select id, state from states";
                        //print $query;
                      //  $obj_states = $db->fetchObjectArray($query);
                        ?>
                    <p class="grid_12" style="width:40%">
                    <label>*State: </label>
                    <select name="nstate" id="stateid">
                            <option <?php //echo ($store->state_id == ""||$store->state_id==NULL)?"selected":"" ?> value="0">Select State</option>
                            <?php 
                           // foreach($obj_states as $state){
                                ?>
                                <option <?php //echo ( $store->state_id == $state->id )?"selected":"" ?> value="<?php //echo $state->id;?>">
                                        <?php //echo $state->state;?></option>
                            <?php //}?>
                    </select>       
                    </p>-->
                    
                                        
                                         <?php
        $query = "select id, state,tin from states";
        //print $query;
      
        $obj_states = $db->fetchObjectArray($query);

        ?>
                            <p class="grid_12" style="width:40%">
                                <label>*State: </label>
                                <select name="nstate" id="stateid" required>
                                    <option <?php echo ($store->state_id == "" || $store->state_id == NULL) ? "selected" : "" ?> value="0" >Select State</option>
        <?php
        foreach ($obj_states as $state) {
            if(isset($this->state_id)){ ?>
                
                 <option <?php echo ( $this->state_id == $state->id ) ? "selected" : "" ?> value="<?php echo $state->id; ?>" onclick="showregion(this.value)" > <?php echo $state->state; ?> </option>
            
            <?php     
            }else{
              ?>
                 <option <?php echo ( $store->state_id == $state->id ) ? "selected" : "" ?> value="<?php echo $state->id; ?>"  onclick="showregion(this.value)" > <?php echo $state->state; ?> </option>
            <?php 
            }

             ?>

        <?php } ?>
                                </select>       
                            </p>
                    
                                             <?php

                           
    if(isset($store->state_id)){    
        $query ="select id,region,regioncode from region where state_id= $store->state_id order by region";     
// $query = "<script> document.getElementById('stateid').value </script>"; 
 
//print "hxxxxiiiii".$query;
        $obj_Region = $db->fetchObjectArray($query);
    }
        ?>
                            <p class="grid_12" style="width:40%">
                                <label>*Region: </label>
                                <select name="region" id="regionid" required>
                                    <option <?php echo ($store->region_id == "" || $store->region_id == NULL) ? "selected" : "" ?> value="0">Select Region</option>
        <?php
        foreach ($obj_Region as $region) {
            
            
            ?>
                                        <option <?php echo ( $store->region_id == $region->id ) ? "selected" : "" ?> value="<?php echo $region->id; ?>">
            <?php echo $region->region; ?></option>
        <?php } ?>
                                </select>       
                            </p>
                    
                    
                            <p class="grid_12" style="width:40%">
                                <label>*Status: </label>
                                <select name="status" id="status" required>
                                    <option <?php echo ($store->status == "" || $store->status == NULL) ? "selected" : "" ?> value="0">Select Status</option>
                                    <?php
                                        $allstorestatus = StoreStatus::getALL();
                                        foreach ($allstorestatus as $key => $value) { ?>
                                            <option <?php echo ( $store->status == $key ) ? "selected" : "" ?> value="<?php echo $key; ?>" <?php echo $selected; ?>><?php echo $value; ?></option>
                                    <?php } ?>
                                </select> 
                            </p>
                            
                            
                    
                    <p class="grid_12">
                        <label id="t_city">*City: </label>
                        <input type="text" name="city"  value="<?php echo $this->getFieldValue('city',$store->city); ?>" required>
                    </p>
                     <p class="grid_12">
                        <label id="t_area">Area: </label>
                        <input type="text" name="area"  value="<?php echo $this->getFieldValue('area',$store->Area); ?>">
                    </p>
                    <p class="grid_12">
                        <label id="t_location">Location: </label>
                        <input type="text" name="location"  value="<?php echo $this->getFieldValue('location',$store->Location); ?>">
                    </p>
                    <p class="grid_12">
                        <label id="t_zip">*Postal Area Code/Zip code: </label>
                        <input type="text" name="zip"  value="<?php echo $this->getFieldValue('zip',$store->zipcode); ?>" required>
                    </p>
                    <p class="grid_12">
                        <label id="t_name">*Owner Name: </label>
                        <input type="text" name="owner" value="<?php echo $this->getFieldValue('owner',$store->owner); ?>" required>
                    </p>
                    <p class="grid_12">
                        <label id="t_phone">*Phone Number 1: </label>
                        <input type="text" name="phone" value="<?php echo $this->getFieldValue('phone',$store->phone); ?>" required>
                    </p>
                    <p class="grid_12">
                        <label id="t_phone2">Phone Number 2: </label>
                        <input type="text" name="phone2" value="<?php echo $this->getFieldValue('phone2',$store->phone2); ?>">
                    </p>
                    <p class="grid_12">
                        <label id="t_email">*Email Address 1: </label>
                        <input type="text" name="email" value="<?php echo $this->getFieldValue('email',$store->email); ?>" required>
                    </p>
                    <p class="grid_12">
                        <label id="t_email2">Email Address 2: </label>
                        <input type="text" name="email2" value="<?php echo $this->getFieldValue('email2',$store->email2); ?>">
                    </p>
                    <p >
<!--                        <label id="t_vat">VAT/TIN number: </label>-->
                        <input type="hidden" name="vat" value="<?php echo $this->getFieldValue('vat'); ?>" >
                    </p>
                     <p class="grid_12">
                        <label id="t_vat">*GSTIN number: </label>
                        <input type="text" name="gstin_no" value="<?php echo $this->getFieldValue('gstin_no',$store->gstin_no); ?>" required>
                    </p>
                    <p class="grid_12">
                        <label id="t_tally">*Tally Name: </label>
                        <input type="text" name="tally_name" value="<?php echo $this->getFieldValue('tally_name',$store->tally_name);?>">
                    </p> 
                    
                    <p class="grid_12">
                        <label id="t_tally">Distance </label>
                        <input type="text" name="distance" value="<?php echo $this->getFieldValue('distance',$store->distance);?>">
                    </p> 
                     <p class="grid_12">
                                <label id="upi_id">Upi ID </label>
                                <input type="text" name="upi_id" value="<?php echo $this->getFieldValue('upi_id', $store->upi_id); ?>">
                            </p> 
                            <p class="grid_12">
                                <label id="upi_name">Upi Name </label>
                                <input type="text" name="upi_name" value="<?php echo $this->getFieldValue('upi_name', $store->upi_name); ?>">
                            </p>

                    
                    
                     <p class="grid_3">
                                <label>Dealer Discount: *</label>
                         <?php //if ($this->currStore->usertype == UserType::CKAdmin) { ?>
                                    <!--<input   type="text" name="discval" id="discval" value="<?php echo $this->getFieldValue('discval', $store->discountset); ?>" required oninput="isCheckedById();" >-->
                                <?php //}
                                
                                
                                if($this->currStore->usertype == UserType::CKAdmin||$this->currStore->usertype == UserType::Admin ||$this->currStore->id==128) {
                                    
                                     $query ="select * from it_dealer_discount order by discount asc";     
                                        $obj_deler_disc = $db->fetchObjectArray($query);
                                        
                                    
                                    ?>
                                         
                               
                                    <select name="discvalset" id="discvalset"  onclick="discountsets(this.value);" >
                                    <option value=0 disabled="" selected="">Select Discount</option>
        <?php
        foreach ($obj_deler_disc as $disc) {
             $selected = "";
             
             if( $disc->id > 3 && $this->currStore->usertype != UserType::CKAdmin) { continue;}
            
            ?>
                                    <option <?php echo $selected; ?> value="<?php echo $disc->discount; ?>" >
            <?php echo $disc->discount; ?></option>
        <?php } ?>
                                </select>       
                               <p class="grid_3" >
                                  <label> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
                                   <input type="text" name="discval" id="discval" value="<?php echo $this->getFieldValue('', $store->discountset); ?>" readonly="" required="" >
                                    </p>
                                   
                            <?php } else {?>
                                     <input  type="text" name="discval" value="<?php echo $this->getFieldValue('discval', $store->discountset); ?>" required readonly="">
                                    <textarea readonly>You can't change this…?</textarea>
                                    
                                      <?php } ?>
                                    
                                    
                                    
                            </p>

                            <p class="grid_3">
                                <label>Total Effecive  Discount Given: </label>
                             <?php $addDisc = $store->discountset  + $store->cash + $store->nonclaim; ?>
                                <input type="text" id="adddisc" name="adddisc" value="<?php echo $this->getFieldValue('', $addDisc); ?>" readonly>
                            </p>
                            
                            
                            
                            
                    
                    
                    <p>
<!--                        <label>Transport : </label>-->
                        <input type="hidden" name="transport" value="<?php echo $this->getFieldValue('transport'); ?>">
                    </p>
                    <p class="grid_12">
<!--                        <label>Octroi : </label>-->
                        <input type="hidden" name="octroi" value="<?php echo $this->getFieldValue('octroi'); ?>">
                    </p>
                    
                    
<!--                    <p class="grid_3">
                        <label>Cash : </label>
                        <input type="text" name="cash" value="<?php// echo $this->getFieldValue('cash',$store->cash); ?>">
                    </p>
                    <p class="grid_3">
                        <label>Non Claim : </label>
                        <input type="text" name="nonclaim" value="<?php// echo $this->getFieldValue('nonclaim',$store->nonclaim); ?>">
                    </p>-->
                     <!--Note: Below MSL entry n to_close_store access only to:-
                   Intouch-admin, Koushik,kunal
                -->
                
                
                
                               <?php if ($this->currStore->usertype == UserType::CKAdmin || $this->currStore->usertype == UserType::Admin ||$this->currStore->id==128 ) { ?>
                            <p class="grid_1">
                                <label>Cash:</label>
                                <input type="checkbox"  name="cashes" id="<?php echo $store->is_cash;?>" value="1" <?php if ($store->is_cash=="1") echo "checked"; ?> onclick="isCheckedById();"/>
                            
                            </p>
                            
                            <p class="grid_1">
                                <label>NonClaim:</label>
                               <input type="checkbox" name="claimed" id="<?php echo $store->is_claim;?>" value="1" <?php if ($store->is_claim=="1") echo "checked"; ?> onclick="isCheckedById();"/>
                            
                            
                            </p>
                            
                                 <?php } else { ?>
                            
                            
                            
                            
                            
                            <?php }  ?>
                
                
                    <?php if ($this->currStore->usertype==UserType::Admin || $this->currStore->usertype==UserType::CKAdmin ||$this->currStore->id==128) { ?>
                    <p class="grid_12">
                        <label>Minimum Stock Level: </label><br>
                        <input type="text" name="msl" style='width:30%' value="<?php echo $this->getFieldValue('msl',$store->min_stock_level); ?>">
                    </p>     
                    <p class="grid_12">
                        <label>Maximum Stock Level: </label><br>
                        <input type="text" name="maxsl" style='width:30%' value="<?php echo $this->getFieldValue('maxsl',$store->max_stock_level); ?>">
                    </p> 
                    
                    
                    <p class="grid_12">
                        <label>Is Store Closed: </label><br>
                        <input type ="radio" name="is_closed" id="is_closed" style="width:5%" <?php if ($store->is_closed==0) { ?>checked <?php }?> value="0">No
                        <input type ="radio" name="is_closed" id="is_closed" style="width:5%" <?php if ($store->is_closed==1) { ?>checked <?php }?> value="1">Yes
                    </p>
                     <p class="grid_12">
                        <label> Select Store Type: </label><br>
                        <input type ="radio" name="storetype" id="storetype" style="width:5%" <?php if ($store->store_type==StoreType::NormalStore) { ?>checked <?php }?> value="<?php echo StoreType::NormalStore;?>" required><?php echo trim(StoreType::getName(StoreType::NormalStore));?>
                        <input type ="radio" name="storetype" id="storetype" style="width:5%" <?php if ($store->store_type==StoreType::Store50percent) { ?>checked <?php }?> value="<?php echo StoreType::Store50percent;?>" required><?php echo trim(StoreType::getName(StoreType::Store50percent));?>
                        <input type ="radio" name="storetype" id="storetype" style="width:5%" <?php if ($store->store_type==StoreType::CompanyStore) { ?>checked <?php }?> value="<?php echo StoreType::CompanyStore;?>" required><?php echo trim(StoreType::getName(StoreType::CompanyStore));?>
                    </p>
                    <?php // } if($this->currStore->usertype==UserType::Admin){?>
                    <p class="grid_12">
                        <label>Is Autorefill enabled: </label><br>
                        <input type ="radio" name="is_autorefill" id="is_autorefill" style="width:5%" <?php if ($store->is_autorefill==0) { ?>checked <?php }?> value="0">No
                        <input type ="radio" name="is_autorefill" id="is_autorefill" style="width:5%" <?php if ($store->is_autorefill==1) { ?>checked <?php }?> value="1">Yes
                    </p>
                    <?php // } if($this->currStore->usertype==UserType::Admin || $this->currStore->usertype==UserType::CKAdmin){?>
                    <p class="grid_12">
                        <label>Is Standing/Base Stock feature enabled: </label><br>
                        <input type ="radio" name="sbstock_active" id="sbstock_active" style="width:5%" <?php if ($store->sbstock_active==0) { ?>checked <?php }?> value="0">No
                        <input type ="radio" name="sbstock_active" id="sbstock_active" style="width:5%" <?php if ($store->sbstock_active==1) { ?>checked <?php }?> value="1">Yes
                    </p>

                                <p class="grid_12">
                                    <label>Composite Billing Opted : </label><br>
                                    <input type ="radio" name="composite_billing_opted" id="composite_billing_opted" style="width:5%" <?php if ($store->composite_billing_opted == 0) { ?>checked <?php } ?> value="0">No
                                    <input type ="radio" name="composite_billing_opted" id="composite_billing_opted" style="width:5%" <?php if ($store->composite_billing_opted == 1) { ?>checked <?php } ?> value="1">Yes
                                </p>
                                <p class="grid_12">
                                    <label>Select Margin for Mask : </label><br>
                                    <input type ="radio" name="mask_margin_type" id="mask_margin_type" style="width:5%" <?php if ($store->mask_margin == 0) { ?>checked <?php } ?> value="0">Regular
                                    <input type ="radio" name="mask_margin_type" id="mask_margin_type" style="width:5%" <?php if ($store->mask_margin == 1) { ?>checked <?php } ?> value="1">55% margin
                                </p>
                                
                    <?php } ?>
                    <p class="grid_12">
                       <label>Is Tally Transfer feature enabled: </label><br>
                       <input type ="radio" name="is_tallyxml" id="is_tallyxml" style="width:5%" <?php if ($store->is_tallyxml==0) { ?>checked <?php }?> value="0">No
                       <input type ="radio" name="is_tallyxml" id="is_tallyxml" style="width:5%" <?php if ($store->is_tallyxml==1) { ?>checked <?php }?> value="1">Yes
                   </p>
                    <p class="grid_12">
                     <label>PANCARD No: </label><br>                     
                     <input type ="text" name="pancard_no" id="pancard_no" style="width:30%"  value="<?php echo $this->getFieldValue('pancard_no',$store->pancard_no); ?>" >                     
                    </p> 
                    <p class="grid_12">
                     <label>Select Tax Type:</label>                     
                     <input type ="radio" name="taxtype" id="taxtype" style="width:5%" <?php if ($store->tax_type==taxType::VAT) { ?>checked <?php }?> value="<?php echo taxType::VAT;?>" required><?php echo trim(taxType::getName(taxType::VAT));?>
                     <input type ="radio" name="taxtype" id="taxtype" style="width:5%" <?php if ($store->tax_type==taxType::CST) { ?>checked <?php }?> value="<?php echo taxType::CST;?> " required><?php echo trim(taxType::getName(taxType::CST));?>           
                    </p>
                    <p class="grid_12" id="natchrad">
                        <label>Is Nach Required: </label><br>
                        <input type ="radio" name="is_natch" id="is_natch1" style="width:5%" <?php if ($store->is_natch_required==0) { ?>checked <?php }?> value="0" onclick="Hidenatch()">No
                        <input type ="radio" name="is_natch" id="is_natch2" style="width:5%" <?php if ($store->is_natch_required==1) { ?>checked <?php }?>value="1" onclick="Shownatch()">Yes
                    </p>
    
                      <div id="natch" <?php if ($store->is_natch_required==0) { ?>style="display:none" <?php }?> >
                        <p class="grid_3">
                    <label>UMRN*</label>
                     <input type="text" id="umrn" name="umrn" value="<?php echo $this->getFieldValue('umrn',$store->UMRN); ?>">
                    </p>
                    
                    <p class="grid_3">
                        <label>Cust. To Be Debited</label>
                        <input type="text" id="cust_dbt" name="cust_tobe_debtd" value="<?php echo $this->getFieldValue('cust_tobe_debtd',$store->cust_tobe_debited); ?>">
                    </p>
                    <p class="grid_3">
                        <label>Cust. IFSC/MCR</label>
                        <input type="text" id="cust_ifsc" name="cust_ifsc_mcr" value="<?php echo $this->getFieldValue('cust_ifsc_mcr',$store->cust_ifsc_or_mcr); ?>">
                    </p>
                    <p class="grid_3">
                        <label>Cust. Debit Acc.</label>
                        <input type="text" id="cust_dbt_acc" name="cust_debit_account" value="<?php echo $this->getFieldValue('cust_debit_account',$store->cust_debit_account); ?>">
                    </p>
                    
                
                        
                    </div>
                    <p class="grid_12">
                        <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
                    </p>
                    <p class="grid_12" align="center">
                    <input type="submit" value="Update" style="width:35%">
                    </p>
                </form>
            </div>
        </div>
    </fieldset>
</div>
    <?php
    }
}
?>
