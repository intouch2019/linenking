<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once 'Classes/PHPExcel/IOFactory.php';
require_once "Classes/html2pdf/html2pdf.class.php";
require_once "lib/conv/CurrencyConv.php";
$commit = false;
$errors = array();
$success = "";
$err = "";
$resp = "";
$db=new DBConn();
$conv = new CurrencyConv();
extract($_POST);

//print_r($_POST);
if($remark=="")
{
    //$errors="Please add remark";
    //$_SESSION['form_errors'] = $errors;
    session_write_close();
    header("Location: ".DEF_SITEURL."admin/debitnote");
//header("Location: {$_SERVER['HTTP_REFERER']}");
    exit;
}
else{

  $tvalue=0.0;
    $tqty=0.0;
////
//////
for($i=0;$i<count($tpricearr);$i++)
{

                    
                    $tqty +=$qtyarr[$i];
                    $tvalue +=$tpricearr[$i];
}

$curr_challan_obj = $db->fetchObject("select num from it_danum");
    $current_DAnum = $curr_challan_obj->num;
    $created_by = getCurrUserId();
    $insert_itDAChallan = "insert into it_Debitnote_challans set CHALLAN_ID=$current_DAnum,"
            . "store_id = $store,created_by='',"
            . "T_QTY = $tqty,T_PRICE=$tvalue,"
            . "CREATETIME = now()";
    $insertDA = $db->execInsert($insert_itDAChallan);
    //print $insert_itDAChallan;
    //print "id>>>".$insertDA;
    if ($insertDA > 0) {
        $db->execUpdate("update it_danum set num=num+1");
    }

    
    
for($i=0;$i<count($bararr);$i++)
{
 
                $insert_item_lvl = "insert into it_debitnote_challan_item "
                        . "set CHALLAN_ID= $insertDA,PRODUCT=$bararr[$i],"
                        . "qty = $qtyarr[$i],price = $tpricearr[$i],"
                        . "CREATETIME = now()";
                //print $insert_item_lvl;
                $item_lvl = $db->execInsert($insert_item_lvl);

}

///////aprove
try {
    
    
    $user_id = getCurrUserId();
    $debit_notechallan_to_approve = ""
            . "select * from it_Debitnote_challans where disable=0 and debit_advoice_no is null";
    $debit_notechallan_to_approve_obj = $db->fetchObjectArray($debit_notechallan_to_approve);
    
    
    //print_r($debit_notechallan_to_approve_obj);
    foreach ($debit_notechallan_to_approve_obj as $object) {
        //debit advoice number
        $Q_cur_debitnote_num = "select dbnum from it_debitnote_num";
        $Obj_debit_note = $db->fetchObject($Q_cur_debitnote_num);
        $debit_advoice_num = $db->safe($Obj_debit_note->dbnum);
        
        $debit_date = $db->safe(date("Y/m/d"));

        $Q_store = "select * from it_codes where id=$object->store_id";
        $Obj_store = $db->fetchObject($Q_store);

        //print_r($Obj_store);
        if(isset($Obj_store->state_id))
        {
            $state_id = $Obj_store->state_id;
        }
        else {
                $state_id=22;
        }
        $store_id = $Obj_store->id;
        $created_id = $user_id;
        
        $Q_store_disc = "select dealer_discount from it_ck_storediscount where store_id=$Obj_store->id";
        $Obj_store_disc = $db->fetchObject($Q_store_disc);
        $disc = $Obj_store_disc->dealer_discount;

        $insert_debit_advice = "insert into it_debit_advice"
                . " set debit_no=$debit_advoice_num,debit_dt=$debit_date,"
                . "debit_qty = $object->T_QTY,total_mrp=$object->T_PRICE,"
                . "store_id=$store_id,state_id=$state_id,user_id=$created_id,"
                . "procsd_date=now(),createtime=now()";
        //print 'header level   :'.$insert_debit_advice.'<br/>';
        $debit_id=$db->execInsert($insert_debit_advice);
        //print ">>>>>>>>>>>$debit_id";
        //$db->execQuery("update it_debitnote_num set dbnum=dbnum+1");
        //cal culation part
        $discount_amount = 0;
        $itemwise_discount_amount = 0;
        $rate = 0;
        $total_rate = 0;
        $total_tax_in_bill = 0;
        $cgst = 0; $cgst_cls = FALSE;
        $sgst = 0;
        $igst = 0; $igst_cls = FALSE;
        $total_rate_in_bill = 0;
        $total_disc_in_bill=0;
        $total_cgst=0;
        $total_sgst=0;
        $total_igst=0;
//        
//        
        $Q_debit_note_item = "select * from it_debitnote_challan_item where CHALLAN_ID=$object->ID";
        $Obj_Debit_note_item = $db->fetchObjectArray($Q_debit_note_item);
        //  item lvl info
        
        //print "$Q_debit_note_item";
        foreach ($Obj_Debit_note_item as $obj_item) {

            $barcode = $obj_item->PRODUCT;
            $Q_category = "select ctg_id,design_no from it_items where barcode=$barcode";
            //print "$Q_category";
            $Obj_cat = $db->fetchObject($Q_category);
            $category = $Obj_cat->ctg_id;
            
            ///
            $hsnquery="select * from it_categories where id=$category";
            $hsnobj=$db->fetchObject($hsnquery);
            $hsncode=$hsnobj->it_hsncode;
            $desc_of_goods=''.$hsnobj->name;
            //discount ---------------------------------------------
            $unit_price = $obj_item->price / $obj_item->qty;
            $inner_bracket = 0;
            if ($unit_price <= 1050) {
                $inner_bracket = $unit_price - ($unit_price / (1 + 0.05));
            } else {
                $inner_bracket = $unit_price - ($unit_price / (1 + 0.12));
            }
            $itemwise_discount_amount = ($unit_price * $disc / 100) + $inner_bracket;
            $rate=$unit_price-$itemwise_discount_amount;
            $total_disc_for_barcode = $itemwise_discount_amount * $obj_item->qty;
            $total_disc_in_bill = $total_disc_in_bill + $total_disc_for_barcode;
            //discount ----------------------------------------------
       
            //tax calculation--------------------------
            $tax_rate=0.0;
            if ($state_id == 22) {
               
                    if ($rate <= 1000) {
                        //$rate = $unit_price - $itemwise_discount_amount;
                        $total_rate = $rate * $obj_item->qty;
                        $tax = $total_rate * 0.05;
                        $tax_rate=0.05;
                    } else {
                        //$rate = $unit_price - $itemwise_discount_amount;
                        $total_rate = $rate * $obj_item->qty;
                        $tax = $total_rate * 0.12;
                        $tax_rate=0.12;
                    }
                    
                //}
                $cgst = $tax / 2;
                $total_cgst = $total_cgst + $cgst;
                
                $sgst = $cgst;
                $total_sgst = $total_sgst + $sgst;
                
                $CGgstClause = ",cgst=$cgst,sgst=$sgst";                
                $cgst_cls = TRUE;
                $total_rate_in_bill = $total_rate_in_bill + $total_rate;
                $total_tax_in_bill = $total_tax_in_bill + $tax;
            } else {
                
                    if ($rate <= 1000) {
                        $rate = $unit_price - $itemwise_discount_amount;
                        $total_rate = $rate * $obj_item->qty;
                        $tax = $total_rate * 0.05;
                        $tax_rate=0.05;
                    } else {
                        $rate = $unit_price - $itemwise_discount_amount;
                        $total_rate = $rate * $obj_item->qty;
                        $tax = $total_rate * 0.12;
                        $tax_rate=0.12;
                    }
                //}
                $igst = $tax;
                $total_igst = $total_igst + $igst;
                $Igstclause = ",igst=$igst";
                $igst_cls = TRUE;
                $total_rate_in_bill = $total_rate_in_bill + $total_rate;
                $total_tax_in_bill = $total_tax_in_bill + $tax;
            }
            //tax calculation-------------------------------
            //frt_ins
            $Q_insert_DA_item="insert into it_debit_advice_items set debit_id=$debit_id,"
                    . "item_code = $barcode,price = $obj_item->price,quantity=$obj_item->qty,"
                    . "rate=$rate,total_rate_qty=$total_rate,discount_val=$total_disc_for_barcode,"
                    . "createtime=now(),tax_rate=$tax_rate,hsncode='$hsncode',disc_of_goods='$desc_of_goods'";
            
            if($cgst_cls){
                $Q_insert_DA_item = $Q_insert_DA_item.$CGgstClause;
            }else{
                $Q_insert_DA_item = $Q_insert_DA_item.$Igstclause;
            }
            //print 'item lvel  :'.$Q_insert_DA_item.'<br/>';
            $item_insert = $db->execInsert($Q_insert_DA_item);
        }
        
    if($cgst_cls){
        $final_query = ",cgst_total=$total_cgst,sgst_total=$total_sgst";
    }else{
        $final_query = ",igst_total=$total_igst";
    }
    
    
    $update_DA_hedaer = "update it_debit_advice set debit_amt=$total_rate_in_bill,discount_value=$total_disc_in_bill," 
                        . "tax = $total_tax_in_bill,total_taxable_value=$total_rate_in_bill,"
                        . "rate_subtotal=$total_rate_in_bill"
                        . $final_query.",updatetime=now(),ref_no='$storeinvoice',ref_date='$ref_date',remark='$remark',frt_ins=$frt_ins where id=$debit_id";
    print 'header Update '.$update_DA_hedaer.'<br/>';
   $db->execUpdate($update_DA_hedaer); 
        $db->execQuery("update it_Debitnote_challans set debit_advoice_no=$debit_advoice_num,is_generated=1 where debit_advoice_no is null");
        $db->execQuery("update it_debitnote_num set dbnum=dbnum+1");
        
        ///////////////////update in lkportal
        $records = ($debit_advoice_num+1) . "<>1";
        $db = new DBConn();
        //$url = "http://192.168.0.124/ck_new_y/home/obsync/sendDNnumber.php";
        $url="http://cottonking.intouchrewards.com/home/obsync/sendDNnumber.php";
        $fields = array('records' => urlencode($records));
        $fields_string = "";
        foreach ($fields as $key => $value) {
            $fields_string .= $key . '=' . $value . '&';
        }
        rtrim($fields_string, '&');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, count($fields));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);

        $outputresult = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        
        print_r($outputresult);
        $result = "success";
    }
} catch (Exception $e) {
    $result = "fail";
    echo json_encode(array("error" => "1", "message" => $result));
    return;
}
//
$updatebar="update it_temp_barcodes set processed=0,barcode_string=''";
$i=$db->execUpdate($updatebar);  
}


///////////////pdf code//






$pdfqyery="select * from it_debit_advice where is_procsdForRetail=0 order by id desc limit 1";
$pdfobj=$db->fetchObject($pdfqyery);
//print_r($pdfobj);


$html="";
$html1 = '<html>';
$html2 = '<html>';
       $html1=   '<style type="text/css">            
                @page {
                    size: a4 landscape;
                    margin-top: 0.5cm;                    
                    margin-left: 0.5cm;
                    margin-right: 0.5cm;
                    margin-bottom: 0.1cm;                    
                }
                table {
                width:80px;
                height:100px;
                table-layout:fixed;
                }
                    td { 
                            padding-bottom: 0px;
                            padding-left: 2px;
                            padding-right: 2px;
                            padding-top:4px;
                            overflow: hidden;
                            
                           
                        }
                  
                    th { padding-bottom: 0px;
                            padding-left: 2px;
                            padding-right: 2px;
                            padding-top:4px;
                            
                            }
                    
                .td_border {
                        border-top:1px;
                        border-bottom:1px solid;
                        border-right:1px; 
                        border-left:1px solid; 
                }
                
              
                   
                .table_border{
                        border-top:1px solid;
                        border-bottom:1px solid;
                        border-right:1px solid; 
                        border-left:1px solid; 
                }
                
                .font_size{
                    font-size:10px;
                }
                .fixed { width:537px; }
                .fixed1 { width:537px; }
                </style>
                        ';
       $html2=   '<style type="text/css">            
                @page {
                    size: a4 landscape;
                    margin-top: 0.5cm;                    
                    margin-left: 0.5cm;
                    margin-right: 0.5cm;
                    margin-bottom: 0.1cm;                    
                }
                table {
                width:80px;
                height:100px;
                table-layout:fixed;
                }
                    td { 
                            padding-bottom: 0px;
                            padding-left: 2px;
                            padding-right: 2px;
                            padding-top:4px;
                            overflow: hidden;
                            
                           
                        }
                  
                    th { padding-bottom: 0px;
                            padding-left: 2px;
                            padding-right: 2px;
                            padding-top:4px;
                            
                            }
                    
                .td_border {
                        border-top:1px;
                        border-bottom:1px solid;
                        border-right:1px; 
                        border-left:1px solid; 
                }
                
              
                   
                .table_border{
                        border-top:1px solid;
                        border-bottom:1px solid;
                        border-right:1px solid; 
                        border-left:1px solid; 
                }
                
                .font_size{
                    font-size:10px;
                }
                .fixed { width:529px; }
                .fixed1 { width:530px; }
                </style>
                        ';
$html2fpdf = new HTML2PDF('L', 'A4', 'en');
$html1.='<page>';
$html2.='<page>';
if(isset($pdfobj)){

    
    

    

  $html .= '<table width="100%" border="1px" align="center" style="border-collapse: collapse;">'
              
                        .'
                            <tr>';
            
            
            $html .= '<td align="center" colspan="4" style="font-size:14px;" class="fixed">&nbsp;&nbsp;&nbsp;&nbsp;<b>DEBIT NOTE</b>&nbsp;&nbsp;&nbsp;&nbsp;</td>';
                        
            
            
            
            $html .= '</tr>'
                    . '<tr>'
                    . '<td align="left" colspan="2" class="fixed">&nbsp;Debit Note No.:DN' .$pdfobj->debit_no.'</td> '
                    
                    . '<td align="left" colspan="2" class="fixed1">&nbsp;Date Of Debit: ' . $pdfobj->debit_dt . '</td>';
           
             
            $storequery="select * from it_codes where id=$pdfobj->store_id";
            $store=$db->fetchObject($storequery);
            
            $html .= '</tr>'
                    . '<tr>'
                    . '<td align="left" colspan="2" class="fixed">To,<br/><b>'.$store->store_name .'</b><br/>' 
                    .$store->address.'<br/>' .
                    'Contact No.: ' . $store->phone2
                   .'</td>'
                   
                   .'<td align="left" colspan="2" class="fixed1">From,';
                    
            $html .= '<br/><b>Fashionking Brands Pvt. Ltd.</b>';
                    
                    
            $html .= '<br/>Plot No.21,22,23 <br/>Hi-Tech Textile Park,'
                    .'<br/> MIDC, Baramati, Dist. Pune- 413133'
                    .'<br/>Phone : 02112-244120/21'
                    .'</td>'
                    .'</tr>'
                    //.'</table>'
                    //.'<table width="100%" border="1px" align="center" style="border-collapse: collapse;">'
                    .'<tr>';
                  
                    
            $html.= '<td align="left"><span style=\"font-size:14px; padding:12px 0 0px 0\">&nbsp;&nbsp;&nbsp;&nbsp;GSTIN NO :&nbsp;&nbsp;'.$store->gstin_no.'&nbsp;</span></td>'
                    .'<td align="left"><span style=\"font-size:14px; padding:12px 0 0px 0\">&nbsp;&nbsp;&nbsp;&nbsp;PAN No  :&nbsp;&nbsp;'.$store->pancard_no.'&nbsp;</span></td>'
//                    .'<td align="left" colspan="4" style=\"font-size:12px;>&nbsp;GSTIN NO :&nbsp;27AAACC7418H1ZQ&nbsp;</td>'
//                    .'<td align="left" colspan="4" style=\"font-size:12px;>&nbsp;PAN No  :&nbsp;&nbsp;AAACC7418H&nbsp;</td>'
                    .'<td align="left"><span style=\"font-size:14px; padding:12px 0 0px 0\">&nbsp;&nbsp;&nbsp;&nbsp;GSTIN NO  :&nbsp;&nbsp;27AAACC7418H1ZQ&nbsp;</span></td>'
                    .'<td align="left"><span style=\"font-size:14px; padding:12px 0 0px 0\">&nbsp;&nbsp;&nbsp;&nbsp;PAN No  :&nbsp;&nbsp;AAACC7418H&nbsp;</span></td>'
                    .'</tr>'
                   .'</table>';
            
            
            
            $html .=  '<table width="100%" border="1px" align="center" style="border-collapse: collapse;">'
                //. '<thead>'
                .'<tr>'
                . '<th rowspan="2" align="center" width="4%" bgcolor=#C0C0C0 ><b>Sr.No</b></th>'
                . '<th rowspan="2" align="center" width="12%" bgcolor=#C0C0C0><b>Description of Goods</b></th>'
                . '<th rowspan="2" align="center" width="5%" bgcolor=#C0C0C0><b>HSN</b></th>'
                . '<th rowspan="2" align="center" width="8%" bgcolor=#C0C0C0><b>Received in Inv. No.</b></th>'
                . '<th rowspan="2"align="center"  width="3%" bgcolor=#C0C0C0><b>Receive Inv. Date</b></th>'
                . '<th rowspan="2" align="center" width="5%" bgcolor=#C0C0C0><b>Qty. </b></th>'
                . '<th rowspan="2" align="center" width="6%" bgcolor=#C0C0C0><b>Rate</b></th>'
                . '<th rowspan="2" align="center" width="7%" bgcolor=#C0C0C0><b>Total Value</b></th>'
                . '<th rowspan="2" align="center" width="7%" bgcolor=#C0C0C0><b>&nbsp;Discount</b></th>'
                //. '<th rowspan="2" align="center" width="5%" bgcolor=#C0C0C0><b>Frt</b></th>'
                . '<th rowspan="2" align="center" width="10%" bgcolor=#C0C0C0><b>Taxable value</b></th>'
                . '<th colspan="2" align="center" width="11%" bgcolor=#C0C0C0><b>CGST</b></th>'
                . '<th colspan="2" align="center" width="11%" bgcolor=#C0C0C0><b>SGST</b></th>'
                . '<th colspan="2" align="center" width="11%" bgcolor=#C0C0C0><b>IGST</b></th>'
                . '</tr>'
                . '<tr>'
                . '<th align="center" width="5.5%" bgcolor=#C0C0C0> Rate </th>'
                . '<th align="center" width="5.5%" bgcolor=#C0C0C0> Amount </th>'
                . '<th align="center" width="5.5%" bgcolor=#C0C0C0> Rate </th>'
                . '<th align="center" width="5.5%" bgcolor=#C0C0C0> Amount </th>'
                . '<th align="center" width="5.5%" bgcolor=#C0C0C0>Rate </th>'
                . '<th align="center" width="5.5%" bgcolor=#C0C0C0>Amount </th>'
                . '</tr>';
                //. '</thead>';
           //$pdfitemsQuery="select *,quantity finalqty from it_debit_advice_items where debit_id=$pdfobj->id";
            $pdfitemsQuery="select *,sum(quantity) as finalqty from it_debit_advice_items where debit_id=$pdfobj->id group by hsncode,rate,disc_of_goods";
            $pdfitems=$db->fetchObjectArray($pdfitemsQuery);
            //print_r($pdfitems);
            //print "$pdfitemsQuery";
            $cnt=count($pdfitems);
        
            $frt_ins_tax=($pdfobj->frt_ins*5)/100;
//            if($cnt<=10)
//            {
//                $html=$html2.$html;
//            }
//            else { 
//                    $html=$html1.$html;
//             }
            $i=1;
            $printHtml="";
            $totalQty=0.0;
            $totalVal=0.0;
            $totalDisc=0.0;
            $totalTaxable=0.0;
            $totalTax1=0.0;
            $totalTax2=0.0;
            $totalTax3=0.0;
            $totalInvoiceVal=0.0;
            $desc_of_gd_flag=false;
            //$printHtml .='<tbody>';
            foreach($pdfitems as $dai){
                
                if($dai->disc_of_goods=='BLAZER+SHIRT+ TROUSER+TIE')
                {
                    $desc_of_gd_flag=true;
                }
            $value=round(($dai->finalqty*$dai->rate), 2, PHP_ROUND_HALF_EVEN);
            $disc=$dai->discount_val;
            $taxable=round(($value), 2, PHP_ROUND_HALF_EVEN);
            $tax_val=round((($dai->cgst*2)+$dai->igst), 2, PHP_ROUND_HALF_EVEN)*$dai->finalqty;
        //for($i=1;$i<=10;$i++){
            
        $timestamp = strtotime($pdfobj->ref_date);
                          $printHtml .='<tr>'
                            .'<td align="center" width="4%">'. $i . '</td>'
                            .'<td align="center" width="12%">'.$dai->disc_of_goods.'</td>'
                            . '<td align="center" width="5%">'.$dai->hsncode.'</td>'
                            . '<td align="center"  width="8%">'.$pdfobj->ref_no.'</td>'
                            . '<td align="center" width="3%">'.date('Y-m-d',$timestamp).'</td>'
                            . '<td align="center" width="5%">' .sprintf ("%.2f",$dai->finalqty).'</td>'
                            . '<td align="center" width="6%">'.sprintf ("%.2f",$dai->rate).'</td>'
                            . '<td align="center" width="7%">'.sprintf ("%.2f",$value).'</td>'
                            . '<td align="center" width="7%">'.sprintf ("%.2f",$disc).'</td>';
                            //. '<td align="center" width="5%">'.sprintf ("%.2f",$frt_ins_itemwise).'</td>';
        $printHtml .= '<td align="center" width="10%">'.sprintf ("%.2f",$taxable).'</td>';
         
           $totalQty+=$dai->finalqty;
           $totalVal+=$value;
           $totalDisc+=$disc;
           $totalTaxable+=$taxable;
      if(trim($pdfobj->state_id)==22){
          $tax_2=round(($tax_val/2), 2, PHP_ROUND_HALF_EVEN);
          $tax_per=($dai->tax_rate*100)/2;
          
           $printHtml .= '<td align="center" width="5.5%">' .$tax_per.'%</td>'
                      .'<td align="center" width="5.5%">'.sprintf ("%.2f",$tax_2).'</td>'
                      .'<td align="center" width="5.5%">'.$tax_per.'%</td>'
                      .'<td align="center" width="5.5%">'.sprintf ("%.2f",$tax_2). '</td>'
                      .'<td align="center" width="5.5%">'.'-'.'</td>'
                      .'<td align="center" width="5.5%">'.'-'. '</td>';
           
           $totalTax1+=$tax_2;
           $totalTax2+=$tax_2;
           $totalInvoiceVal+=($taxable+$tax_2+$tax_2);
        }else{
            
                 $printHtml .='<td align="center" width="5.5%">'.'-'.'</td>'
                            .'<td align="center" width="5.5%">' . '-'.'</td>'
                            .'<td align="center" width="5.5%">'.'-'.'</td>'
                            .'<td align="center" width="5.5%">'. '-'.'</td>'
                            .'<td align="center" width="5.5%">' . ($dai->tax_rate*100).'%</td>'
                            .'<td align="center" width="5.5%">' .sprintf ("%.2f",$tax_val).'</td>';
               $totalTax3+=$tax_val;  
               $totalInvoiceVal+=($taxable+$tax_val);
          
        }
        
                $printHtml .= '</tr>';
                $i++;
        }
        
        if($cnt<=10)
            {
               if($desc_of_gd_flag) 
               {
                   $html=$html1.$html;
               }
               else
               {
                   $html=$html2.$html;
               }
               
            }
            else { 
                    $html=$html1.$html;
             }
        if(trim($pdfobj->state_id)==22){
              $totalTax1+=($frt_ins_tax/2);
              $totalTax2+=($frt_ins_tax/2);
        }
        else {
            $totalTax3+=($frt_ins_tax);
        }
      
        $totalInvoiceVal+=$pdfobj->frt_ins+$frt_ins_tax;
        $roundTotalInvoiceVal= round($totalInvoiceVal);
        $roundoff = $roundTotalInvoiceVal- $totalInvoiceVal;
        
        while($i<=10){
            $printHtml.='<tr>'
                            .'<td align="center" width="4%">'. $i . '</td>'
                            .'<td align="center" width="9%"></td>'
                            . '<td align="center" width="5%"></td>'
                            . '<td align="center"  width="8%"></td>'
                            . '<td align="center" width="6%"></td>'
                            . '<td align="center" width="5%"></td>'
                            . '<td align="center" width="6%"></td>'
                            . '<td align="center" width="7%"></td>'
                            . '<td align="center" width="7%"></td>';
            $printHtml .= '<td align="center" width="10%"></td>';
            $printHtml .= '<td align="center" width="5.5%"></td>'
                      .'<td align="center" width="5.5%"></td>'
                      .'<td align="center" width="5.5%"></td>'
                      .'<td align="center" width="5.5%"></td>'
                      .'<td align="center" width="5.5%"></td>'
                      .'<td align="center" width="5.5%"></td></tr>';
            $i++;
        }
        $cntsmthng=0;
        if($i>=14)
        {
            $cntsmthng=$i-25;
           while($i>=14 && $i<=25){
            $printHtml.='<tr>'
                            .'<td align="center" width="4%">'. $i . '</td>'
                            .'<td align="center" width="9%"></td>'
                            . '<td align="center" width="5%"></td>'
                            . '<td align="center"  width="8%"></td>'
                            . '<td align="center" width="6%"></td>'
                            . '<td align="center" width="5%"></td>'
                            . '<td align="center" width="6%"></td>'
                            . '<td align="center" width="7%"></td>'
                            . '<td align="center" width="7%"></td>';
            $printHtml .= '<td align="center" width="10%"></td>';
            $printHtml .= '<td align="center" width="5.5%"></td>'
                      .'<td align="center" width="5.5%"></td>'
                      .'<td align="center" width="5.5%"></td>'
                      .'<td align="center" width="5.5%"></td>'
                      .'<td align="center" width="5.5%"></td>'
                      .'<td align="center" width="5.5%"></td></tr>';
            $i++;
        } 
        }
        


        
         $totalTaxValue=$totalTax1+$totalTax2+$totalTax3;
                      //$printHtml.= '</tbody>';
        
                  $printHtml .=''
                            .'<tr>'
                            .'<td align="center"  width="4%"></td>'
                            .'<td align="center" width="9%"><b>TOTAL</b> </td>'
                            .'<td align="center" width="5%">'.''.'</td>'
                            .'<td align="center" width="8%"></td>'
                            .'<td align="center" width="6%"></td>'
                            .'<td align="center" width="5%">'.sprintf ("%.2f",$totalQty).'</td>'
                            .'<td align="center" width="6%">'.''.'</td>'
                            .'<td align="center" width="7%">'.sprintf ("%.2f",$totalVal).'</td>'
                            .'<td align="center" width="7%">'.sprintf ("%.2f",$totalDisc).'</td>'
                            .'<td align="center" width="10%">'.sprintf ("%.2f",$totalVal).'</td>';
           if(trim($pdfobj->state_id)==22){ 
//                 
                 $printHtml .= '<td colspan="2" align="center" width="11%">'.sprintf ("%.2f",$totalTax1-$frt_ins_tax/2).'</td>'
                             . '<td colspan="2" align="center" width="11%">'.sprintf ("%.2f",$totalTax2-$frt_ins_tax/2).'</td>'
                             . '<td colspan="2" align="center" width="11%">'.'-'.'</td>';
             }else {
                 
                   $printHtml .= '<td colspan="2" align="center" width="11%">'.'-'.'</td>'        
                             .'<td colspan="2" align="center" width="11%">'.'-'.'</td>'
                             .'<td colspan="2" align="center" width="11%">'.sprintf ("%.2f",$totalTax3-$frt_ins_tax).'</td>';
             }
                    $printHtml .= '</tr>'
                               .'<tr>'
                            .'<td align="center" width="13%" colspan="2">Total Invoice Value (In Figure):</td>'
                            //.'<td align="center" width="37%" colspan="6">'.sprintf ("%.2f",$totalInvoiceVal).'</td>'
                            .'<td align="center" width="37%" colspan="6">'.sprintf ("%.2f",$roundTotalInvoiceVal).'</td>'
                            .'<td align="center" width="13%" colspan="2">TOTAL OF TAXABLE VALUE </td>'
                            .'<td align="center" width="37%" colspan="6">'.sprintf ("%.2f",$totalVal).'</td></tr>'
                            .'<tr>'
                            .'<td align="center" width="13%" colspan="2">Total Invoice Value (In Words):  </td>'
                            //.'<td align="center" width="37%" colspan="6">'.$this->getIndianCurrency($totalInvoiceVal).'</td>'
                            .'<td align="center" width="37%" colspan="6">'.$conv->getIndianCurrency($roundTotalInvoiceVal).'</td>' 
                            .'<td align="center" width="13%" colspan="2">TOTAL OF FRT/INS/EXTRA</td>'
                            .'<td align="center" width="37%" colspan="6">'.sprintf ("%.2f",$pdfobj->frt_ins).'</td>';
                             $printHtml .= '</tr>'
                                .'<tr>'
                            .'<td align="center" width="13%" colspan="2"> Total TAX Value (In Figure):</td>'
                            .'<td align="center" width="37%" colspan="6">'.sprintf ("%.2f",$totalTaxValue).' </td>'                 
                            .'<td align="center" width="13%" colspan="2">TOTAL OF CGST </td>' 
                            .'<td align="center" width="37%" colspan="6">'.sprintf ("%.2f",$totalTax1).'</td>';      
                            $printHtml .= '</tr>'
                                 .'<tr>'
                            .'<td align="center" width="13%" colspan="2"> Total TAX Value (In Words):</td>'
                            .'<td align="center" width="37%" colspan="6">'.$conv->getIndianCurrency($totalTaxValue).' </td>'
                            .'<td align="center" width="13%" colspan="2">TOTAL OF SGST </td>'
                            .'<td align="center" width="37%" colspan="6">'.sprintf ("%.2f",$totalTax2).'</td>';      
                            $printHtml .= '</tr>'   
                                 .'<tr>'
                            .'<td align="center" width="13%" colspan="2"></td>'
                            .'<td align="center" width="37%" colspan="6"></td>' 
                            .'<td align="center" width="13%" colspan="2">TOTAL OF IGST </td>'
                            .'<td align="center" width="37%" colspan="6">'.sprintf ("%.2f",$totalTax3).'</td>';      
                            $printHtml .= '</tr>'
                                    .'<tr>'
                            .'<td align="center" width="13%" colspan="2"></td>'
                            .'<td align="center" width="37%" colspan="6"></td>'
                            .'<td align="center" width="13%" colspan="2">ROUND OFF </td>'
                            .'<td align="center" width="37%" colspan="6">'.sprintf ("%.2f",$roundoff).'</td>';      
                            $printHtml .= '</tr>'
                                    .'<tr>'
                            .'<td align="center" width="13%" colspan="2"></td>'
                            .'<td align="center" width="37%" colspan="6"><b></b> </td>'
                            .'<th align="center" width="13%" colspan="2"><b>INVOICE VALUE</b> </th>'
                            //.'<th align="center" width="37%" colspan="6"><b>'.sprintf ("%.2f",$totalInvoiceVal).'</b> </th>';      
                            .'<th align="center" width="37%" colspan="6"><b>'.sprintf ("%.2f",$roundTotalInvoiceVal).'</b> </th>';              
                            $printHtml .= '</tr>'
                                     .'<tr>'
                            .'<td colspan="10">Remark:<b>'.$pdfobj->remark.'</b> </td>'
                            
                            .'<td colspan="6" align="right">Fashionking Brands Pvt Ltd<br/><img src="../images/koushik.jpg" width="150"/><br/><b>Authorised Signatory</b></td>';
                                
                            $printHtml .= '</tr>'
                                    .'';
                    $printHtml .= '</table>';
            //}
   $html.= $printHtml;
 
}
$html.='</page>';

//echo $html;
$fname="../debitnote/DN".$pdfobj->debit_no."_LK.pdf";
$html2fpdf->writeHTML($html);
$html2fpdf->Output("../debitnote/DN".$pdfobj->debit_no."_LK.pdf", "F");
 


header('Content-Type: application/pdf');
header("Content-Disposition: attachment; filename=\"" . basename($fname) . "\"");
echo file_get_contents($fname);
                    
           
          
                           
        
         