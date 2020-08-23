<?php

  require_once("../../it_config.php");
require_once("session_check.php");
require_once "../lib/db/DBConn.php";
require_once "lib/core/Constants.php"; 
require_once 'lib/users/clsUsers.php';
extract($_POST);
try
{
   $db = new DBConn(); 
     unset($_SESSION['form_errors']); 
     unset($_SESSION['form_success']);
   $success="";
if(isset($store) && $store !="" && isset($user_id) && $user_id !="")
{
    echo "storeid=".$store." user=".$user_id;
   $item_exist=$db->fetchObject("select id from it_portalinv_items_creditnote where is_proccessed=0 and store_id=$store");
    if(isset($item_exist) && ! empty($item_exist) && $item_exist != null)
     {
        $credit_no="DCN-";
        $credit_num=0;
       $creditno=$db->fetchObject("select cn_no from dgcreditnote_no"); 
       if($creditno)
       {
           $credit_no .=$creditno->cn_no+1;
           $credit_num=$creditno->cn_no+1;}
           $qty=0;
            $mrp_total=0.0;
            $disc_total=0.0;
            $storename="";
            $rate_total=0.0;
            $taxable_total=0.0;
            $cgst_total=0.0;
            $sgst_total=0.0;
            $igst_total=0.0;
       $query="select i.quantity as qty,(i.price * i.quantity) as mrp_total,i.discount_val as disc, c.tally_name as storename,i.total_rate_qty as total_rate ,i.taxable_value as taxable_total,i.cgst as cgst,i.sgst as sgst,i.igst as igst from it_portalinv_items_creditnote i,it_codes c where i.store_id=c.id and is_proccessed=0 and store_id=$store";    
//      echo $query;//te
       $items_obj=$db->fetchObjectArray($query);
       foreach ($items_obj as $items)
     {
//        echo "</br>creditnote no=".$credit_no."</br></br>"; ///
        $qty +=$items->qty;
        $mrp_total +=$items->mrp_total;
        $disc_total +=$items->disc;
        $storename =$items->storename;
        $rate_total +=$items->total_rate;
        $taxable_total +=$items->taxable_total;
        $cgst_total+=$items->cgst;
        $sgst_total +=$items->sgst;
        $igst_total +=$items->igst;
     }
        $invoice_amount= $taxable_total+$cgst_total+$sgst_total+$igst_total;
        $round_invoice_amount=round($invoice_amount);
        $round=$round_invoice_amount-$invoice_amount;
        $round=round($round,2);        
        $insquery="insert into it_portalinv_creditnote set invoice_no='$credit_no',invoice_type=5,invoice_amt=$round_invoice_amount,invoice_qty=$qty,total_mrp=$mrp_total,discount_total=$disc_total,store_id=$store,store_name='$storename',rate_subtotal=$rate_total,total_taxable_value=$taxable_total,cgst_total=$cgst_total,sgst_total=$sgst_total,igst_total=$igst_total,round_off=$round,created_by=$user_id,is_approved=0";
//        echo $insquery;
//        $insert_id=1;///
        $insert_id=$db->execInsert($insquery);
//        echo "</br>".$insert_id;
        $update_query="update it_portalinv_items_creditnote set  is_proccessed=1 ,invoice_id=$insert_id where is_proccessed=0 and store_id=$store";
        $db->execUpdate($update_query);
        $db->execUpdate("update DGcreditnote_no set cn_no=$credit_num");
        
        $_SESSION['form_success'] ="Credit note '$credit_no' crated for $storename";
        
     
    }
    else{
       $_SESSION['form_errors'] = "Items not available for store id $store"; 
    }
}else{
 $_SESSION['form_errors'] = "Error during creatinp defective garments creditnote"; 
}
} catch (Exception $ex) {}
 

session_write_close();
header("Location: ".DEF_SITEURL."create/creditnote");
exit;