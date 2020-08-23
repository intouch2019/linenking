 <?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");
require_once "lib/core/strutil.php";
require_once "session_check.php";


class cls_create_creditnote extends cls_renderer{
        var $currUser;
        var $userid;
        var $params;
        var $storeid;
       
       
        function __construct($params=null)
                        {
//		parent::__construct(array(UserType::Admin, UserType::CKAdmin, UserType::Manager));
                ini_set('max_execution_time', 300);
		$this->currUser = getCurrUser();
		$this->userid = $this->currUser->id;
                if(isset($params['storeid']) && $params['storeid'] !=""){
                    $this->storeid=$params['storeid'];
                }else{
                    $this->storeid = 0;
                }
               
                }
        

	function extraHeaders() {
        ?>
<script type="text/javascript" src="jqueryui/js/jquery-ui-1.7.1.custom.min.js"></script>
<script type="text/javascript" src="js/daterangepicker.jQuery.js"></script>
<link rel="stylesheet" href="css/ui.daterangepicker.css" type="text/css" />
<link rel="stylesheet" href="css/redmond/jquery-ui-1.7.1.custom.css" type="text/css" title="ui-theme" />

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
        
        <?php
        }

        public function pageContent() {
            //$currUser = getCurrUser(); ///
//            $menuitem = "bnewbatch";
            $menuitem = "create_creditnote";
            include "sidemenu.".$this->currUser->usertype.".php";
            $formResult = $this->getFormResult();
            $db = new DBConn();  
       
?>
<div class="grid_10">
    <div class="box" style="clear:both;">
	<fieldset class="login">
	<legend>Generate DG Credit Note</legend>
	
        <form method="post" action="formpost/addCreditnoteitems.php">
		<div>
                <?php if($this->currUser->usertype != UserType::Dealer ){ ?>    
		
                    <div class="grid_4" >
                    <b>Select Store*:</b><br/>                    
                    <select name="store" id="store" data-placeholder="Choose Store" class="chzn-select" style="width:100%;" onchange="reloadreport()"  required="required">
                 <option value="-1" ></option> 
<?php
$objs = $db->fetchObjectArray("select * from it_codes where usertype=4 order by store_name");
 
 if($this->storeid ==0){ 
        foreach ($objs as $obj) {        
                $selected="";  
                        if($obj->id==$this->storeid) 
                        { $selected = "selected"; }
                   ?>
                  <option value="<?php echo $obj->id; ?>" <?php echo $selected; ?> ><?php echo $obj->store_name; ?></option> 
        <?php } }else{  
                  foreach ($objs as $obj) {        
                $selected="";  
                        if($obj->id==$this->storeid) 
                        { $selected = "selected"; 
                   ?>
                  <option value="<?php echo $obj->id; ?>" <?php echo $selected; ?> ><?php echo $obj->store_name; ?></option> 
         <?php break; }} }?>
		</select>
	  </div>
                    
                    
                    
                 <div class="grid_4">
                     
                     <b>Select Category*:</b><br/>
                    <select name="category" id="ctg" data-placeholder="Choose Category" class="chzn-select" style="width:100%;" required="required">
                
                              <?php   $defaultSel = ""; ?>
                                 
 
                <option value="-1" <?php echo $defaultSel;?>></option> 
<?php
$objc = $db->fetchObjectArray("select * from it_categories");
 foreach ($objc as $obj) {    ?>
          <option value="<?php echo $obj->id; ?>"  ><?php echo $obj->name; ?></option> 
<?php } ?>
		</select>
               
                     <b>Description of Defects : </b><input size="25" type="text" name="desc_defects" placeholder="Entere description of defects" required="required" /> 
                <br/> 
                <b>MRP :  <br/></b><input size="25" type="text" name="mrp" placeholder="Entere MRP" required="required" onkeypress="return isNumber(event)"/> 
                <br/>  
                <b>Quantity : <br/></b><input  size="25" type="number" name="qty"  required="required"/> 
                <input type="submit" id="submit" name="submit" style="background-color: #4CAF50;   border: none;  color: white;   text-align: center;  font-size: 20px; font-style: bold" value="  +  " onclick="return validate()">
                <br/> 
         <?php if ($formResult) { ?>
                <p>
                    <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
                </p>
                        <?php } ?>
        </div>
        <br/><br/>  
		
            </div>
	 </form>	
            
<!--            //Display reports-->
<?php if(isset($this->storeid)){
    $item_exist=$db->fetchObject("select id from it_portalinv_items_creditnote where is_proccessed=0 and store_id=$this->storeid");
    if(isset($item_exist) && ! empty($item_exist) && $item_exist != null){
    
    ?>
       <div class="grid_12" style="overflow-y: scroll;height: 300px">
        <table style="width:100%" >
            <tr>
                <th colspan="18" align="center">Credit Note Details</th>
            </tr>
            <tr><th colspan="18"></th></tr>
                        <tr>
                            
                            <th>Sr.No.:</th>
                            <th>Description of Goods</th>
                            <th>HSN</th>
                            <th>Qty.</th>
                            <th>MRP(per item)</th>
                            <th>Discount(per item)</th>
                            <th>Rate(per item)</th>
                            <th>MRP Total</th>
                            <th>Total Discount</th>
                            <th>Txable Value</th>
                            <th>CGST Rate</th>
                            <th>CGST Amount</th>
                            <th>SGST Rate</th>
                            <th>SGST Amount</th>
                            <th>IGST Rate</th>
                            <th>IGST Amoun</th>
                             <th>Date</th>
                            <th></th>
<!--                            <th><table style="width:100%" border="0"><tr><th>CGST</th></tr><tr><th>Rate</th><th>Amount</th></tr></table><tbody  style="overflow-y: auto;height: 20px;overflow-x: hidden"></th>
                            <th><table style="width:100%"><tr><th>SGST</th></tr><tr><th>Rate</th><th>Amount</th></tr></table></th>
                            <th><table style="width:100%"><tr><th>IGST</th></tr><tr><th>Rate</th><th>Amount</th></tr></table></th>-->
                            </tr>
                            
                                <?php
                                $i=1;
                                $total_qty=0;
                                $total_mrp=0; 
                                $total_disc=0.0;
                                $taxable_total=0.0;
                                $cgst_total=0.0;
                                $sgst_total=0.0;
                                $igst_total=0.0; 
                                $taxtype=0;
                                $iquery="select i.id,i.desc_defects as dsc,c.it_hsncode as hsn,i.price as mrp,i.quantity as qty,i.disc_peritem as disc_peritm,i.rate as rate_peritm,i.price*i.quantity as  total_mrp,i.discount_val as total_disc,i.tax_rate*100 as tax,i.cgst as cgst,i.sgst as sgst,i.igst as igst,cs.tax_type as taxtype,i.createtime as dttime from it_portalinv_items_creditnote i,it_categories c,it_codes cs where i.ctg_id=c.id and i.store_id=cs.id and is_proccessed=0 and store_id=$this->storeid";
                                 $items=$db->fetchObjectArray($iquery);
                                     foreach($items as $item){
                                       $total_qty +=$item->qty;  
                                        $total_mrp +=$item->total_mrp;
                                        $total_disc +=$item->total_disc;
                                        $taxable_total +=$item->rate_peritm*$item->qty;
                                        $cgst_total +=$item->cgst;
                                        $sgst_total +=$item->sgst;
                                        $igst_total +=$item->igst;
                                        $taxtype=$item->taxtype;
                                ?>
                            <tr>
                             <td><?php echo $i;?></td>
                            <td><?php echo $item->dsc;?></td>
                            <td><?php echo $item->hsn;?></td>
                            <td><?php echo $item->qty;?></td>
                            <td><?php echo $item->mrp;?></td>
                            <td><?php echo $item->disc_peritm;?></td>
                            <td><?php echo $item->rate_peritm;?></td>
                            <td><?php echo $item->total_mrp;?></td>
                            <td><?php echo $item->total_disc;?></td>
                             <td><?php echo $item->rate_peritm*$item->qty;?></td>
                            <td><?php if($item->taxtype==1){ $cgst_rate=$item->tax/2; echo $cgst_rate."%";}else{ echo "-";}?></td>
                            <td><?php if($item->taxtype==1){echo $item->cgst;}else{ echo "-";}?></td>
                            <td><?php if($item->taxtype==1){ $sgst_rate=$item->tax/2; echo $sgst_rate."%";}else{ echo "-";}?></td>
                            <td><?php if($item->taxtype==1){echo $item->sgst;}else{ echo "-";}?></td>
                            <td><?php if($item->taxtype !=1){ $igst_rate=$item->tax/2; echo $igst_rate."%";}else{ echo "-";}?></td>
                            <td><?php if($item->taxtype!=1){echo $item->igst;}else{ echo "-";}?></td>
                            <td><?php echo $item->dttime;?></td>
                            <td><form method="POST" action="formpost/removeCreditnoteitems.php">
                                    <input type="hidden" name="item_id" value='<?php echo $item->id;?>'>
                                    <input type="hidden" name="store" value='<?php echo $this->storeid;?>'>
                                    <input type="submit" style="background-color: #EC311B;   border: none;  color: white;   text-align: center;  font-size: 14px; font-style: bold" value="Remove" onclick="return is_confirm('<?php echo $i;?>')"></form></td>
                                </tr>
                                     <?php $i++; }?>    
                                
                            <tr><th></th> <th>Total</th> <th></th>
                            <th><?php echo $total_qty; ?></th>
                            <th></th> <th></th> <th></th>
                            <th><?php echo $total_mrp; ?></th>
                            <th><?php echo $total_disc; ?></th>
                            <th><?php echo $taxable_total; ?></th>
                            <th></th>
                            <th><?php if($taxtype==1){echo $cgst_total;}else{echo "-";} ?></th>
                            <th></th>
                            <th><?php if($taxtype==1){echo $sgst_total;}else{echo "-";} ?></th>
                            <th></th>
                            <th><?php if($taxtype!=1){echo $igst_total;}else{echo "-";} ?></th>
                             <th></th>
                            <th></th> 
                               </tr>    
                            <tbody id="scrl" style="overflow-y: auto;height: 20px;overflow-x: hidden">
                    
        </table>
       </div><div class="grid_12" style="overflow-y: scroll;height: 50px">
           <form action="formpost/genPortalCreditNote.php" method="POST">
               <input type="hidden" name="store" value='<?php echo $this->storeid;?>'>
               <input type="hidden" name="user_id" value='<?php echo $this->currUser->id;?>'>
               <input type="submit" value="Generate Credit note">
              </form>  
       </div>

    <?php } }?>
    

          
	</fieldset>
        
    </div> <!-- class=box -->
  
    <?php } 
     
    ?>
</div>
<script src="js/chosen/chosen.jquery.js" type="text/javascript"></script>
<script type="text/javascript"> </script>
<script type="text/javascript">
var dot_count=0;
$(function(){  $(".chzn-select").chosen(); $(".chzn-select-deselect").chosen({allow_single_deselect:true}); });

    
    var fieldlist = new Array();
    
//    function reloadreport(bstr) { 
//        barcodestring=bstr;
//       if(storeloggedin == '-1'){
//           storeid = $('#store').val();
//          //alert("SID:"+storeid);
//       }
//      //alert("1: "+storeid);
//        var aclause='';
//        if(storeid=='-1'){
//          resp = confirm("Please select the store properly"); 
//          if(resp){
//              aclause='/a=1';
//          }
//        }
//
//       if (storeid!="" && storeid != null) {
//           
//           
////            window.location.href="admin/debitnote/str="+storeid+":"+barcodestring;
//            setfocus();
//
//       } else {
//           alert("please select store(s) to genereate Debit Note");
//       }
//    }
//    
  function isNumber(evt) {
    evt = (evt) ? evt : window.event;
    var charCode = (evt.which) ? evt.which : evt.keyCode;
//    alert (charCode);

    if (charCode > 31 && (charCode < 48 || charCode > 57) && charCode !=46) {
        return false;
    }
    if(dot_count>0 && charCode===46){
        return false;
    }
    if(charCode===46){
    dot_count=1;
}
    return true;
}


function validate()
{
 var storeid = $('#store').val();  
 var catg=$('#ctg').val();
 if(storeid==='-1'){
     alert("Please select the store properly"); 
     return false;
}
if(catg==='-1'){
     alert("Please select the category"); 
     return false;
}
return true;
}
function reloadreport()
{
    var storeid = $('#store').val(); 
   window.location.href="create/creditnote/storeid="+storeid 
}
function is_confirm(num)
{
    // var num   
     var r=confirm("Are you sure to remove Item(Sr.no-"+num+") ?");
 if(r== true)
 {
     return true;
 }
 else
 {
     return false;
 }
}

//
//$(document).ready(function() {
//// $("#submit").click(function() {
////   if ($(this).is(":checked")) {
////      $("#dropdown").prop("disabled", true);
////   } else {
////      $("#dropdown").prop("disabled", false);  
////   }
//// });
//
//var stateid='<?php ///echo  $this->storeid?>';
//
//if(stateid!=""){
//    hide();
//    //alert('ggg');
//    //$("#dropdown").prop("disabled", true);
//}
//});
//function hide(){
//    alert('ggg');
//    document.getElementById("dropdown").disabled=true;
//    
//    
//}///



</script>
<?php
	}
        
        
}
?>
