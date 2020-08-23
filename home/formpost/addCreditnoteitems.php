<?php 
 require_once("../../it_config.php");
require_once("session_check.php");
require_once "../lib/db/DBConn.php";
require_once "lib/core/Constants.php"; 
//require_once 'lib/users/clsUsers.php';
extract($_POST);
//print_r($_POST);
//exit();

$store_id=0;
$ctg_id=0; 
$MRP=0.0;
$Qty=0;
$dealer_disc=0;
$dis_peritem;
$rate_peritem;
$tax_rate;
$taxable_value;
$insquery="";
$margin=1;
$cgst=0;
$sgst=0;
$igst=0;

if(isset($store) && $store !="")
{
    $store_id=$store;
}
if(isset($category) && $category !="")
{
   $ctg_id=$category;
}
if(isset($desc_defects) && $desc_defects !="")
{
   $insquery.=",desc_defects='$desc_defects'";
}
if(isset($mrp) && $mrp !="")
{
    $MRP=$mrp;
}
if(isset($qty) && $qty !="")
{
    $Qty=$qty;
}

try{
       $db = new DBConn();
       unset($_SESSION['form_errors']); 
        unset($_SESSION['form_success']);
       //find margin/store discount
       $marginq="select margin from it_categories where id=$ctg_id";
        $margin_obj = $db->fetchObject($marginq);
        if(isset($margin_obj) && ! empty($margin_obj) && $margin_obj != null){
         $margin= $margin_obj->margin;  
        }
        if($margin==0){
          $dealer_disc=0;  
        } else {
             $discq="select dealer_discount from it_ck_storediscount where store_id=$store_id";
        $disc_obj = $db->fetchObject($discq);
        if(isset($disc_obj) && ! empty($disc_obj) && $disc_obj != null){
         $dealer_disc= $disc_obj->dealer_discount;  
        }
          }
       

       //find tax rate
        $taxq="select tax_rate from it_category_taxes where category_id=$ctg_id";//Category level taxes
        $taxobj = $db->fetchObject($taxq);
        if(isset($taxobj) && ! empty($taxobj) && $taxobj != null){
         $tax_rate= $taxobj->tax_rate;  
        }else
            {
        $mqry = "select tax_rate from it_mrp_taxes where mrp = $mrp ";//mrp taxes
        $mobj = $db->fetchObject($mqry);
        if(isset($mobj) && ! empty($mobj) && $mobj != null){
           $tax_rate= $mobj->tax_rate; 
        }else{
             if(trim($mrp) <= "1050"){  $tax_rate = 0.05;  }else{ $tax_rate = 0.12; }
          }
        }


    
    $cmpzquery="select id,composite_billing_opted ,tax_type from it_codes where id=$store_id";
    $cmpz_obj=$db->fetchObject($cmpzquery);
    if(isset($cmpz_obj))
        {
        if($margin==2){
        
            $dis_peritem=99.9*$MRP /100;
        }
        else
            {
        if($cmpz_obj->composite_billing_opted==0){
            
           $dis_peritem= $MRP-($MRP/(1+$tax_rate)) +($dealer_disc*$MRP/100);
        }
        else { 
            $tax_val=0;
            $disc_val=1-($dealer_disc/100);
            if($MRP * $disc_val >1050) {  $tax_val=0.12; }else{$tax_val=0.05;}
                 $dis_peritem= $MRP-($MRP/(1+($tax_val *$disc_val))) +($dealer_disc*$MRP/100) +(1*$MRP/100);
          }
        }
        $dis_peritem=round($dis_peritem,2);
        $rate_peritem=  $MRP-$dis_peritem;
        if($rate_peritem<1000){
            $tax_rate=0.05;
        } else {
            $tax_rate=0.12;
        }
        $taxable_value=$rate_peritem*$Qty;
        $tax=$taxable_value*$tax_rate;
        $total_rate=$rate_peritem* $Qty;
        $total_dis=$dis_peritem*$Qty;
         if($cmpz_obj->tax_type==1){
            $cgst= round($tax/2,2);
            $sgst=round($tax/2,2);
         } else {
            $igst=round($tax,2); 
         } 
          
         $ins_query="insert into it_portalinv_items_creditnote set price=$MRP,quantity=$Qty,disc_peritem=$dis_peritem, rate=$rate_peritem,total_rate_qty=$total_rate,discount_val=$total_dis,taxable_value=$taxable_value,cgst=$cgst,sgst=$sgst,igst=$igst,tax_rate=$tax_rate,is_proccessed=0,store_id=$store_id,ctg_id=$ctg_id".$insquery; 
//         echo $ins_query;
//         exit();
         $db->execInsert($ins_query);
         $_SESSION['form_success']="item insert with MRP:$MRP ";
         $db->closeConnection();
          
        }
        
        
} catch (Exception $ex) {

}
    


session_write_close();
header("Location: ".DEF_SITEURL."create/creditnote/storeid=$store");
exit;