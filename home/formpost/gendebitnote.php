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
//    print $insert_itDAChallan;
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
//                print $insert_item_lvl;
                $item_lvl = $db->execInsert($insert_item_lvl);

}

///////aprove
try {
    
    
    $user_id = getCurrUserId();
    $debit_notechallan_to_approve = ""
            . "select * from it_Debitnote_challans where disable=0 and debit_advoice_no is null";
    $debit_notechallan_to_approve_obj = $db->fetchObjectArray($debit_notechallan_to_approve);
    
    
    print_r($debit_notechallan_to_approve_obj);
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
//        echo $Q_store_disc;
//        exit();

        $insert_debit_advice = "insert into it_debit_advice"
                . " set debit_no=$debit_advoice_num,debit_dt=$debit_date,"
                . "debit_qty = $object->T_QTY,total_mrp=$object->T_PRICE,"
                . "store_id=$store_id,state_id=$state_id,user_id=$created_id,"
                . "procsd_date=now(),createtime=now()";
//        print 'header level   :'.$insert_debit_advice.'<br/>';
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
        
//        print "$Q_debit_note_item";
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
//    print 'header Update '.$update_DA_hedaer.'<br/>';
   $db->execUpdate($update_DA_hedaer); 
        $db->execQuery("update it_Debitnote_challans set debit_advoice_no=$debit_advoice_num,is_generated=1 where debit_advoice_no is null");
        $db->execQuery("update it_debitnote_num set dbnum=dbnum+1");
        
        ///////////////////update in lkportal
//        $records = ($debit_advoice_num+1) . "<>1";
//        $db = new DBConn();
//        $url = "http://localhost/ck_new_y/home/obsync/sendDNnumber.php";
//       // $url="http://cottonking.intouchrewards.com/home/obsync/sendDNnumber.php";
//        $fields = array('records' => urlencode($records));
//        $fields_string = "";
//        foreach ($fields as $key => $value) {
//            $fields_string .= $key . '=' . $value . '&';
//        }
//        rtrim($fields_string, '&');
//        $ch = curl_init();
//        curl_setopt($ch, CURLOPT_POST, true);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//        curl_setopt($ch, CURLOPT_URL, $url);
//        curl_setopt($ch, CURLOPT_POST, count($fields));
//        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
//
//        $outputresult = curl_exec($ch);
//        $info = curl_getinfo($ch);
//        curl_close($ch);
//
//        
//        print_r($outputresult);
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


//exit;
callDebitNoteAPI();



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
$irn="";
$AckDate="";
$AckNo="";
$Qr_Image="";
$irncn_query="select irn,AckDate,AckNo,Qr_Image from  it_irndebitnote where DebitNote_ID=$pdfobj->id";
//$irncn_query="select irn,AckDate,AckNo,Qr_Image from  it_irndebitnote where DebitNote_ID=236";
$irn_obj= $db->fetchObject($irncn_query);
//print_r($irn_obj);
if(isset($irn_obj))
{
$irn=$irn_obj->irn;
$AckDate=$irn_obj->AckDate;
$AckNo=$irn_obj->AckNo;
$Qr_Image=$irn_obj->Qr_Image;  
}

    
    

    

//  $html .= '<table  border="1px" align="center" style="border-collapse: collapse;">'
//  $html .= '<table  width="150%" border="1px" align="center" style="border-collapse: collapse;">'
  $html .= '<table  width="140%" border="1px" align="center" style="border-collapse: collapse;">'
              
                        .'
                           
                            <tr>';
            
            
//            $html .= '<td align="center" colspan="6" style="font-size:14px;" class="fix">&nbsp;&nbsp;&nbsp;&nbsp;<b>DEBIT NOTE</b>&nbsp;&nbsp;&nbsp;&nbsp;</td>';
//            $html .= '<td align="center" colspan="10" style="font-size:14px;" ><b>DEBIT NOTE</b></td>';
            $html .= '<td align="center" colspan="6" style="font-size:14px;">&nbsp;&nbsp;&nbsp;&nbsp;<b>DEBIT NOTE</b>&nbsp;&nbsp;&nbsp;&nbsp;</td>';
                        
            
            $datetime = strtotime($AckDate);
$AckDate= date("Y-m-d", $datetime);
            
            $html .= '</tr>'
                    
                    . '<tr>'
                    . '<td align="left" colspan="3" width="40%">&nbsp;Debit Note No.:DN' .$pdfobj->debit_no.'</td> '
                    
                    . '<td align="left" colspan="2" width="30%">&nbsp;Date Of Debit: ' . $pdfobj->debit_dt . '</td>'
//                    . '<td align="left" colspan="2" width="40%colspan" >&nbsp;Ack Date: ' . $AckDate . '</td>'; // new change
//                    . '<td align="left" colspan="2" width="30%" >Ack Date:' . $AckDate . '</td>'; // new change
                    . '<td align="left" colspan="1" width="30%" >Ack Date:' .$AckDate.'  </td>'; // new change
             
            $storequery="select * from it_codes where id=$pdfobj->store_id";
            $store=$db->fetchObject($storequery);
            
            $html .= '</tr>'
                    . '<tr>'
//                    . '<td align="left" colspan="2" class="fixed">To,<br/><b>'.$store->store_name .'</b><br/>' 
                    . '<td align="left" colspan="3" >To,<br/><b>'.$store->store_name .'</b><br/>' 
                    .$store->address.'<br/>' .
                    'Contact No.: ' . $store->phone2
                    .'<br/>'.
                    'Retail Net Margin:'.$Obj_store_disc->dealer_discount.'%'
                   .'</td>'
                   
//                   .'<td align="left" colspan="2" class="fixed1">From,';
                   .'<td align="left" colspan="2" >From,';
                    
            $html .= '<br/><b>Fashionking Brands Pvt. Ltd.</b>';
                    
                    
            $html .= '<br/>Plot No.21,22,23 <br/>Hi-Tech Textile Park,'
                    .'<br/> MIDC, Baramati, Dist. Pune- 413133'
                    .'<br/>Phone : 02112-244120/21'
                    .'</td>'
                    . '<td align="center" colspan="1">'
//                    .'<img src="../images/QR_code/'.$Qr_Image.'" width="130" height="130">' // new change
                    .'<img src="../images/QR_code/'.$Qr_Image.'" width="130" height="130">' // new change
                    
                    .'</td>'
                    .'</tr>'
                    //.'</table>'
                    //.'<table width="100%" border="1px" align="center" style="border-collapse: collapse;">'
                    .'<tr>';
                  
                    
            $html.= '<td align="left"><span style=\"font-size:14px; padding:12px 0 0px 0\">&nbsp;&nbsp;&nbsp;&nbsp;GSTIN NO :&nbsp;&nbsp;'.$store->gstin_no.'&nbsp;</span></td>'
                    .'<td align="left"><span style=\"font-size:14px; padding:12px 0 0px 0\">&nbsp;&nbsp;&nbsp;&nbsp;PAN No  :&nbsp;&nbsp;'.$store->pancard_no.'&nbsp;</span></td>'
                    .'<td align="left"><span style=\"font-size:14px; padding:12px 0 0px 0\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></td>'
//                    .'<td align="left"><span style=\"font-size:14px; padding:12px 0 0px 0\">&nbsp;&nbsp;&nbsp;&nbsp;PAN No  :&nbsp;&nbsp;'.$store->pancard_no.'&nbsp;</span></td>'

//                    .'<td align="left" colspan="4" style=\"font-size:12px;>&nbsp;GSTIN NO :&nbsp;27AAACC7418H1ZQ&nbsp;</td>'
//                    .'<td align="left" colspan="4" style=\"font-size:12px;>&nbsp;PAN No  :&nbsp;&nbsp;AAACC7418H&nbsp;</td>'
                    .'<td align="left"><span style=\"font-size:14px; padding:12px 0 0px 0\">&nbsp;&nbsp;&nbsp;&nbsp;GSTIN NO  :&nbsp;&nbsp;27AAACC7418H1ZQ&nbsp;</span></td>'
                    
                    .'<td align="left" ><span style=\"font-size:14px; padding:12px 0 0px 0\">&nbsp;&nbsp;&nbsp;&nbsp;PAN No  :&nbsp;&nbsp;AAACC7418H&nbsp;</span></td>'
                    .'<td align="left" ><span style=\"font-size:14px; padding:12px 0 0px 0\">&nbsp;&nbsp;&nbsp;&nbsp;ACK No :<br/> '.$AckNo.'</span></td>'// new change
//                    .'<td align="left" ><span style=\"font-size:14px; padding:12px 0 0px 0\">&nbsp;&nbsp;&nbsp;&nbsp; '.$AckNo.'</span></td>'// new change
//                    .'<td align="center" colspan="1"><span style=\"font-size:14px; padding:12px 0 0px 0\">&nbsp;&nbsp;&nbsp;&nbsp; '.$AckNo.'</span></td>'// new change
//                    .'<td align="center" ><span style=\"font-size:14px; padding:12px 0 0px 0\">&nbsp;&nbsp;&nbsp;&nbsp; '.$AckNo.'</span></td>'// new change
                    .'</tr>'
            
                   .'</table>';
            
           // '<table border="1px" align="center" style="border-collapse: collapse;  width:100%";>'
            
            $html .=  '<table width="95%" border="1px" align="center" style="border-collapse: collapse;">'
//            $html .=  '<table width="90%" border="1px" align="center" style="border-collapse: collapse;">'
                    
//            $html .=  '<table border="1px" align="center" style="border-collapse: collapse;">'
                //. '<thead>'
                .'<tr>'
                . '<th rowspan="2" align="center" width="4%" bgcolor=#C0C0C0 ><b>Sr.No</b></th>'
//                . '<th rowspan="2" align="center" width="12%" bgcolor=#C0C0C0><b>Description of Goods</b></th>'
//                . '<th rowspan="2" align="center" width="10%" bgcolor=#C0C0C0><b>Description <br/>of Goods</b></th>'
                . '<th rowspan="2" align="center" width="12%" bgcolor=#C0C0C0><b>Description of Goods</b></th>'
                 
                . '<th rowspan="2" align="center" width="5%" bgcolor=#C0C0C0><b>HSN</b></th>'
//                . '<th rowspan="2" align="center" width="8%" bgcolor=#C0C0C0><b>Received in <br/>Inv. No.</b></th>'
                . '<th rowspan="2" align="center" width="5%" bgcolor=#C0C0C0><b>Received in <br/>Inv. No.</b></th>'
                . '<th rowspan="2"align="center"  width="3%" bgcolor=#C0C0C0><b>Receive Inv.<br/> Date</b></th>'
                . '<th rowspan="2" align="center" width="5%" bgcolor=#C0C0C0><b>Qty. </b></th>'
                    
//                . '<th rowspan="2" align="center" width="5%" bgcolor=#C0C0C0><b>MRP</b></th>' // new change
                . '<th rowspan="2" align="center" width="5%" bgcolor=#C0C0C0><b>MRP</b></th>' // new change
                    
                . '<th rowspan="2" align="center" width="6%" bgcolor=#C0C0C0><b>Rate</b></th>'
//                . '<th rowspan="2" align="center" width="7%" bgcolor=#C0C0C0><b>Total Value</b></th>'
                . '<th rowspan="2" align="center" width="3%" bgcolor=#C0C0C0><b>Total <br/>Value</b></th>'
//                . '<th rowspan="2" align="center" width="7%" bgcolor=#C0C0C0><b>&nbsp;Discount</b></th>'
                . '<th rowspan="2" align="center" width="3%" bgcolor=#C0C0C0><b>Discount</b></th>'
                //. '<th rowspan="2" align="center" width="5%" bgcolor=#C0C0C0><b>Frt</b></th>'
//                . '<th rowspan="2" align="center" width="10%" bgcolor=#C0C0C0><b>Taxable<br/> value</b></th>'
                . '<th rowspan="2" align="center" width="10%" bgcolor=#C0C0C0><b>Taxable<br/> value</b></th>'
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
//                            .'<td align="center" width="8%">'.$dai->disc_of_goods.'</td>'
                            . '<td align="center" width="5%">'.$dai->hsncode.'</td>'
                            . '<td align="center"  width="8%">'.$pdfobj->ref_no.'</td>'
//                            . '<td align="center"  width="5%">'.$pdfobj->ref_no.'</td>'
                            . '<td align="center" width="3%">'.date('Y-m-d',$timestamp).'</td>'
                            . '<td align="center" width="5%">' .sprintf ("%.2f",$dai->finalqty).'</td>'
                                  
                            . '<td align="center" width="5%">' .sprintf ("%.2f",$dai->price).'</td>'// new change
                                  
                            . '<td align="center" width="6%">'.sprintf ("%.2f",$dai->rate).'</td>'
                            . '<td align="center" width="7%">'.sprintf ("%.2f",$value).'</td>'
//                            . '<td align="center" width="2%">'.sprintf ("%.2f",$value).'</td>'
                            . '<td align="center" width="7%">'.sprintf ("%.2f",$disc).'</td>';
//                            . '<td align="center" width="2%">'.sprintf ("%.2f",$disc).'</td>';
                            //. '<td align="center" width="5%">'.sprintf ("%.2f",$frt_ins_itemwise).'</td>';
        $printHtml .= '<td align="center" width="10%">'.sprintf ("%.2f",$taxable).'</td>';
//        $printHtml .= '<td align="center" width="7%">'.sprintf ("%.2f",$taxable).'</td>';
         
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
//                            .'<td align="center" width="6%">'. $i . '</td>'
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
                      .'<td align="center" width="5.5%"></td>'
                      .'<td align="center" width="5.5%"></td></tr>';
//            $printHtml .= '<td align="center" width="6%"></td>'
//                      .'<td align="center" width="6%"></td>'
//                      .'<td align="center" width="6%"></td>'
//                      .'<td align="center" width="6%"></td>'
//                      .'<td align="center" width="6%"></td>'
//                      .'<td align="center" width="6%"></td></tr>';
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
                      .'<td align="center" width="5.5%"></td>'
                      .'<td align="center" width="5.5%"></td></tr>';
//            $printHtml .= '<td align="center" width="6%"></td>'
//                      .'<td align="center" width="6%"></td>'
//                      .'<td align="center" width="6%"></td>'
//                      .'<td align="center" width="6%"></td>'
//                      .'<td align="center" width="6%"></td>'
//                      .'<td align="center" width="6%"></td></tr>';
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
//                          .'<td align="center" width="7%"></td>'
                          .'<td></td>'
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
//                             .'<td colspan="2" align="center" width="11%">'.sprintf ("%.2f",$totalTax3-$frt_ins_tax).'</td>';
                             .'<td colspan="2" align="center" width="11%">'.sprintf ("%.2f",$totalTax3-$frt_ins_tax).'</td>';
             }
                    $printHtml .= '</tr>'
                               .'<tr>'
                            .'<td align="center" width="13%" colspan="2">Total Invoice Value (In Figure):</td>'
                            //.'<td align="center" width="37%" colspan="6">'.sprintf ("%.2f",$totalInvoiceVal).'</td>'
                            .'<td align="center" width="37%" colspan="6">'.sprintf ("%.2f",$roundTotalInvoiceVal).'</td>'
                            .'<td align="center" width="13%" colspan="2">TOTAL OF TAXABLE VALUE </td>'
//                            .'<td align="center" width="37%" colspan="6">'.sprintf ("%.2f",$totalVal).'</td></tr>'
                            .'<td align="center" width="37%" colspan="7">'.sprintf ("%.2f",$totalVal).'</td></tr>'
                            .'<tr>'
                            .'<td align="center" width="13%" colspan="2">Total Invoice Value (In Words):  </td>'
                            //.'<td align="center" width="37%" colspan="6">'.$this->getIndianCurrency($totalInvoiceVal).'</td>'
                            .'<td align="center" width="37%" colspan="6">'.$conv->getIndianCurrency($roundTotalInvoiceVal).'</td>' 
                            .'<td align="center" width="13%" colspan="2">TOTAL OF FRT/INS/EXTRA</td>'
//                            .'<td align="center" width="37%" colspan="6">'.sprintf ("%.2f",$pdfobj->frt_ins).'</td>';
                            .'<td align="center" width="37%" colspan="7">'.sprintf ("%.2f",$pdfobj->frt_ins).'</td>';
                             $printHtml .= '</tr>'
                                .'<tr>'
                            .'<td align="center" width="13%" colspan="2"> Total TAX Value (In Figure):</td>'
                            .'<td align="center" width="37%" colspan="6">'.sprintf ("%.2f",$totalTaxValue).' </td>'                 
                            .'<td align="center" width="13%" colspan="2">TOTAL OF CGST </td>' 
//                            .'<td align="center" width="37%" colspan="6">'.sprintf ("%.2f",$totalTax1).'</td>';      
                            .'<td align="center" width="37%" colspan="7">'.sprintf ("%.2f",$totalTax1).'</td>';      
                            $printHtml .= '</tr>'
                                 .'<tr>'
                            .'<td align="center" width="13%" colspan="2"> Total TAX Value (In Words):</td>'
                            .'<td align="center" width="37%" colspan="6">'.$conv->getIndianCurrency($totalTaxValue).' </td>'
                            .'<td align="center" width="13%" colspan="2">TOTAL OF SGST </td>'
//                            .'<td align="center" width="37%" colspan="6">'.sprintf ("%.2f",$totalTax2).'</td>';      
                            .'<td align="center" width="37%" colspan="7">'.sprintf ("%.2f",$totalTax2).'</td>';      
                            $printHtml .= '</tr>'   
                                 .'<tr>'
                            .'<td align="left" width="13%" colspan="8">IRN :'.$irn.'</td>'
//                            .'<td align="center" width="37%" colspan="6"></td>' 
                            .'<td align="center" width="13%" colspan="2">TOTAL OF IGST </td>'
//                            .'<td align="center" width="37%" colspan="6">'.sprintf ("%.2f",$totalTax3).'</td>';      
                            .'<td align="center" width="37%" colspan="7">'.sprintf ("%.2f",$totalTax3).'</td>';      
                            $printHtml .= '</tr>'
                                    .'<tr>'
                            .'<td align="center" width="13%" colspan="2"></td>'
                            .'<td align="center" width="37%" colspan="6"></td>'
                            .'<td align="center" width="13%" colspan="2">ROUND OFF </td>'
//                            .'<td align="center" width="37%" colspan="6">'.sprintf ("%.2f",$roundoff).'</td>';      
                            .'<td align="center" width="37%" colspan="7">'.sprintf ("%.2f",$roundoff).'</td>';      
                            $printHtml .= '</tr>'
                                    .'<tr>'
                            .'<td align="center" width="13%" colspan="8"></td>'
//                            .'<td align="center" width="37%" colspan="6"><b></b> </td>'
                            .'<th align="center" width="13%" colspan="2"><b>INVOICE VALUE</b> </th>'
                            //.'<th align="center" width="37%" colspan="6"><b>'.sprintf ("%.2f",$totalInvoiceVal).'</b> </th>';      
//                            .'<th align="center" width="37%" colspan="6"><b>'.sprintf ("%.2f",$roundTotalInvoiceVal).'</b> </th>';   //new           
                            .'<th align="center" width="37%" colspan="7"><b>'.sprintf ("%.2f",$roundTotalInvoiceVal).'</b> </th>';              
                            $printHtml .= '</tr>'
                                     .'<tr>'
                            .'<td colspan="10">Remark:<b>'.$pdfobj->remark.'</b> </td>'
                            
//                            .'<td colspan="6" align="right">Fashionking Brands Pvt Ltd<br/><img src="../images/koushik.jpg" width="150"/><br/><b>Authorised Signatory</b></td>';
                            .'<td colspan="7" align="center">Fashionking Brands Pvt Ltd<br/><img src="../images/koushik.jpg" width="150"/><br/><b>Authorised Signatory</b></td>';
                                
                            $printHtml .= '</tr>'
                                    .'';
                    $printHtml .= '</table>';
            //}
   $html.= $printHtml;
 
}
$html.='</page>';

//echo $html;
//exit();

$fname="../debitnote/DN".$pdfobj->debit_no."_LK.pdf";
$html2fpdf->writeHTML($html);
$html2fpdf->Output("../debitnote/DN".$pdfobj->debit_no."_LK.pdf", "F");
 


header('Content-Type: application/pdf');
header("Content-Disposition: attachment; filename=\"" . basename($fname) . "\"");
echo file_get_contents($fname);


function callDebitNoteAPI()
{
    $Push_Data_List=Array();
    $db=new DBConn();
    $data= Array();
    $dn_items=Array();
    $item_cnt=0;
    $i=0;
    
    $pdfqyery="select * from it_debit_advice where is_procsdForRetail=0 order by id desc limit 1";
    $pdfobj=$db->fetchObject($pdfqyery);
    if(isset($pdfobj)){
        
        $storequery="select * from it_codes where id=$pdfobj->store_id";//store details
        
            $store=$db->fetchObject($storequery);
             $statequery="select * from states where id=$store->state_id";//store details
//        print $statequery;
            $state=$db->fetchObject($statequery);
         $totalQty=0.0;
            $totalVal=0.0;
            $totalDisc=0.0;
            $totalTaxable=0.0;
            $totalTax1=0.0;
            $totalTax2=0.0;
            $totalTax3=0.0;
            $totalInvoiceVal=0.0;
             $pdfitemsQuery1="select *,sum(quantity) as finalqty from it_debit_advice_items where debit_id=$pdfobj->id group by hsncode,rate,disc_of_goods";
       // echo $pdfitemsQuery."</br>";//exit();
            $pdfitems1=$db->fetchObjectArray($pdfitemsQuery1);
            $cnt=count($pdfitems1);
            $frt_ins_itemwise=($pdfobj->frt_ins)/$cnt;
            foreach($pdfitems1 as $dai){
                   $value=round(($dai->finalqty*$dai->rate), 2, PHP_ROUND_HALF_EVEN)+$frt_ins_itemwise;
            $disc=$dai->discount_val;
            $taxable=round(($value), 2, PHP_ROUND_HALF_EVEN);
            $tax_val=round((($dai->cgst*2)+$dai->igst), 2, PHP_ROUND_HALF_EVEN)*$dai->finalqty;
            
              $totalQty+=$dai->finalqty;
           $totalVal+=$value;
           $totalDisc+=$disc;
           $totalTaxable+=$taxable;
           
                  if(trim($pdfobj->state_id)==22){
                        $tax_2=round(($tax_val/2), 2, PHP_ROUND_HALF_EVEN);
          $tax_per=($dai->tax_rate*100)/2;
            $totalTax1+=$tax_2;
           $totalTax2+=$tax_2;
           $totalInvoiceVal+=($taxable+$tax_2+$tax_2);
                      
                  }else{
                           $totalTax3+=$tax_val;  
               $totalInvoiceVal+=($taxable+$tax_val);
                      
                  }
                
            }
              $roundTotalInvoiceVal= round($totalInvoiceVal);
        $roundoff = $roundTotalInvoiceVal- $totalInvoiceVal;
        
        $pdfitemsQuery="select *,sum(quantity) as finalqty from it_debit_advice_items where debit_id=$pdfobj->id group by hsncode,rate,disc_of_goods";
       // echo $pdfitemsQuery."</br>";//exit();
            $pdfitems=$db->fetchObjectArray($pdfitemsQuery);
            foreach($pdfitems as $dai){
            $value=round(($dai->finalqty*$dai->rate), 2, PHP_ROUND_HALF_EVEN)+$frt_ins_itemwise;
            $disc=$dai->discount_val;
            $taxable=round(($value), 2, PHP_ROUND_HALF_EVEN);
            $tax_val=round((($dai->cgst*2)+$dai->igst), 2, PHP_ROUND_HALF_EVEN)*$dai->finalqty;
            
            
            
            
                $item_cnt++;
                       $dnoi = new debitNoteEinvItems();
                
                        $dnoi->set_GSTIN("27AAACW3775F007"); //for testing
//                        $dnoi->set_GSTIN("27AAACC7418H1ZQ"); //for live
                        $dnoi->set_Irn("");
                        $dnoi->set_Tran_SupTyp("B2B");
                        if ($store->state_id == 22) {
                        $dnoi->set_Tran_RegRev("N");
                         } else {
                        $dnoi->set_Tran_RegRev("Y");
                         }
                        $dnoi->set_Tran_Typ("REG");
                        $dnoi->set_Tran_EcmGstin("");
                         if ($store->state_id == 22) {
                        $dnoi->set_Tran_IgstOnIntra("N");
                            } else {
                        $dnoi->set_Tran_IgstOnIntra("Y");
                       }
                        $dnoi->set_Doc_Typ("DBN");
                        $dnoi->set_Doc_No($pdfobj->debit_no);
//                        $timestamp = strtotime($pdfobj->ref_date);
                        $date=date("d/m/Y");
//                        $date=date("YYYY-MM-DD",$timestamp);
//                        $date=date("Ymd",$timestamp);
//                        $date="";
//                        $d;
//                        $m;
//                        $y;
//                        list($d, $m, $y) = explode('/', $pdfobj->ref_date);
////                        $date=date("Ymd",$timestamp);
                        $dnoi->set_Doc_Dt($date);
                        
                        
                        //NOTE: check all credential when we tested it from local otherwiaw it directly affect live data so do carefully while calling api from local
                
        //                $dnoi->set_BillFrom_Gstin("27AAACC7418H1ZQ");  //for ck live gstn,  
                        $dnoi->set_BillFrom_Gstin("27AAACW3775F007");  //for testing gstin

                        $dnoi->set_BillFrom_LglNm("Fashionking Brands Pvt.Ltd.");
                        $dnoi->set_BillFrom_TrdNm("Fashionking Brands Pvt.Ltd.");
                        $dnoi->set_BillFrom_Addr1("Textile Park");
                        $dnoi->set_BillFrom_Addr2("MIDC");
                        $dnoi->set_BillFrom_Loc("Baramati");
                        $dnoi->set_BillFrom_Pin("413133");
                        $dnoi->set_BillFrom_Stcd("27");
                        $dnoi->set_BillFrom_Ph("02112-244121");
                        $email = "info@cottonking.com";
                        $dnoi->set_BillFrom_Em($email);
                        $custGstIn = str_replace("\\s", "",$store->gstin_no);
                        $dnoi->set_BillTo_Gstin($custGstIn);
                        $dnoi->set_BillTo_LglNm($store->tally_name);
                        $dnoi->set_BillTo_TrdNm($store->tally_name);
                        $dnoi->set_BillTo_Pos("27"); // wh pos in mh so it 27
                        $dnoi->set_BillTo_Addr1($store->address);
                        $dnoi->set_BillTo_Addr2("");
                        $dnoi->set_BillTo_Loc($store->city);
                        $dnoi->set_BillTo_Stcd($state->TIN);
                        $dnoi->set_BillTo_Pin($store->zipcode);
                        $dnoi->set_BillTo_Ph($store->phone);
                        $dnoi->set_BillTo_Em($store->email);
                        
                        
                        
                        $dnoi->set_Item_SlNo($item_cnt);///////////////////////////////////////////////////
                        //$ctg_name="";
                        $dnoi->set_Item_PrdDesc($dai->disc_of_goods);
                        $dnoi->set_Item_IsServc("N");
//                        $dnoi->set_Item_HsnCd($dai->hsncode);
                        $dnoi->set_Item_HsnCd("620319");
                        $dnoi->set_Item_Bch_Nm("");
                        $dnoi->set_Item_Bch_ExpDt("");
                        $dnoi->set_Item_Bch_WrDt("");
                        $dnoi->set_Item_Barcde("");
//                        $dnoi->set_Item_Qty($obj_item->qty);
                        $dnoi->set_Item_Qty($dai->finalqty); ///////////////////////////////////////////////
                        $dnoi->set_Item_FreeQty("");
                        $dnoi->set_Item_Unit("PCS");
                        
//                        $item_price=0;
//                        $item_price= round($dai->price,2)
                      $dnoi->set_Item_UnitPrice($dai->price);
                      
                      $dnoi->set_Item_TotAmt($value);
                      
                      //$dnoi->set_Item_TotAmt($dai->);
//                       $dnoi->set_Item_Discount($dai->discount_val);
                       $dnoi->set_Item_Discount("");
                       $dnoi->set_Item_PreTaxVal("");
                       $dnoi->set_Item_AssAmt("");
                       // $rate
                      $tax_per=($dai->tax_rate*100);
                      $dnoi->set_Item_GstRt($tax_per);
                      if(trim($pdfobj->state_id)==22){
                           $tax_2=round(($tax_val/2), 2, PHP_ROUND_HALF_EVEN);
                           
                       $dnoi->set_Item_IgstAmt("");
                       $dnoi->set_Item_CgstAmt($tax_2);
                       $dnoi->set_Item_SgstAmt($tax_2);
                      }else{
                               $dnoi->set_Item_IgstAmt($tax_val);
                       $dnoi->set_Item_CgstAmt("");
                       $dnoi->set_Item_SgstAmt("");
                          
                          
                      }
                         $dnoi->set_Item_CesRt("");
                         $dnoi->set_Item_CesAmt("");
                         $dnoi->set_Item_CesNonAdvlAmt("");
                         $dnoi->set_Item_StateCesRt("");
                         $dnoi->set_Item_StateCesAmt("");
                         $dnoi->set_Item_StateCesNonAdvlAmt("");
                         $dnoi->set_Item_OthChrg("");
                         
                            
                             $dnoi->set_Item_OrdLineRef("");
                             $dnoi->set_Item_OrgCntry("");
                             $dnoi->set_Item_PrdSlNo("");
                             $dnoi->set_Item_Attrib_Nm("");
                             $dnoi->set_Item_Attrib_Val("");
                             
//                             irn.setItem_Bch_Nm("");
//                            irn.setItem_Bch_ExpDt("");
//                            irn.setItem_Bch_WrDt("");
                             
                             $dnoi->set_Val_AssVal($taxable);
                             $total_item_value=0;
                             if($dai->igst==null)
                             {
//                                 $total_item_value = $dai->total_rate_qty + $dai->cgst+$dai->sgst;
                             $dnoi->set_Val_CgstVal($totalTax1);
                             $dnoi->set_Val_SgstVal($totalTax2);
                             $dnoi->set_Val_IgstVal("");
                             }
                             else{
//                                 $total_item_value = $dai->total_rate_qty + $dai->igst;
                                 $dnoi->set_Val_CgstVal("");
                             $dnoi->set_Val_SgstVal("");
                             $dnoi->set_Val_IgstVal($totalTax3);
                             }
                             
//                             $dnoi->set_Item_TotItemVal($total_item_value);
                            $dnoi->set_Val_CesVal("");
                            $dnoi-> set_Val_StCesVal("");
                            $dnoi-> set_Val_Discount("");
                            
                            //
//                            $dnoi->set_Val_Discount("");
                            $dnoi-> set_Val_OthChrg("");
                            $dnoi->set_Item_TotItemVal("");
                            $dnoi-> set_Val_RndOffAmt("");
                            $dnoi-> set_Val_TotInvVal($roundTotalInvoiceVal);
                            
                            $dnoi-> set_Val_TotInvValFc("");
                            $dnoi-> set_Pay_Nm("");
                            $dnoi-> set_Pay_AccDet("");
                            $dnoi-> set_Pay_Mode("");
                            $dnoi-> set_Pay_FinInsBr("");
                          $dnoi-> set_Pay_CrTrn("");
                            $dnoi-> set_Pay_DirDr("");
                            $dnoi-> set_Pay_CrDay("");
                            $dnoi-> set_Pay_PaidAmt("");
                
                            $dnoi-> set_Pay_PaymtDue("");
                            $dnoi-> set_Ref_InvRm("");
                            $dnoi-> set_Ref_InvStDt("");
                            $dnoi-> set_Ref_InvEndDt("");
                            $dnoi-> set_Ref_PrecDoc_InvNo("");
                            $dnoi-> set_Ref_PrecDoc_InvDt("");
                            $dnoi-> set_Ref_PrecDoc_OthRefNo("");
                            $dnoi-> set_Ref_Contr_RecAdvRefr("");
                            $dnoi-> set_Ref_Contr_RecAdvDt("");
                  
                            $dnoi-> set_Ref_Contr_TendRefr("");
                            $dnoi-> set_Ref_Contr_ContrRefr("");
                            $dnoi-> set_Ref_Contr_ExtRefr("");
                            $dnoi-> set_Ref_Contr_ProjRefr("");
                            $dnoi-> set_Ref_Contr_PORefr("");
                            $dnoi-> set_Ref_Contr_PORefDt("");
                            $dnoi-> set_AddlDoc_Url("");
                            $dnoi-> set_AddlDoc_Docs("");
                            $dnoi-> set_AddlDoc_Info("");
                          $dnoi-> set_Pay_PayTerm("");
                            $dnoi-> set_Pay_PayInstr("");
                            $dnoi-> set_Pay_CrTrn("");
                            $dnoi-> set_Pay_DirDr("");
                            $dnoi-> set_Pay_CrDay("");
                            $dnoi-> set_Pay_PaidAmt("");
               
                            $dnoi-> set_Pay_PaymtDue("");
                            $dnoi-> set_Ref_InvRm("");
                            $dnoi-> set_Ref_InvStDt("");
                            $dnoi-> set_Ref_InvEndDt("");
                            $dnoi-> set_Ref_PrecDoc_InvNo("");
                            $dnoi-> set_Ref_PrecDoc_InvDt("");
                            $dnoi-> set_Ref_PrecDoc_OthRefNo("");
                            $dnoi-> set_Ref_Contr_RecAdvRefr("");
                            $dnoi-> set_Ref_Contr_RecAdvDt("");
       
                            $dnoi-> set_Ref_Contr_TendRefr("");
                            $dnoi-> set_Ref_Contr_ContrRefr("");
                            $dnoi-> set_Ref_Contr_ExtRefr("");
                            $dnoi-> set_Ref_Contr_ProjRefr("");
                            $dnoi-> set_Ref_Contr_PORefr("");
                            $dnoi-> set_Ref_Contr_PORefDt("");
                            $dnoi-> set_AddlDoc_Url("");
                            $dnoi-> set_AddlDoc_Docs("");
                            $dnoi-> set_AddlDoc_Info("");
                            
                            $dnoi-> set_Ewb_TransId("");
                            $dnoi-> set_Ewb_TransName("");
                            $dnoi-> set_Ewb_TransMode("");
    
                            $dnoi-> set_Ewb_Distance("");
  
                            $dnoi-> set_Ewb_TransDocNo("");
                           $dnoi-> set_Ewb_TransDocDt("");
   
                            $dnoi-> set_Ewb_VehNo("");
                            $dnoi-> set_Ewb_VehType("");
                            $dnoi-> set_GetQRImg("1");
                            $dnoi->set_GetSignedInvoice("0");
                            
                          
                            
//---------------------------------------------------------------------------------------------------------
                //for Live credentials uncomment when deploy on live
            //              $dnoi-> set_CDKey("1695383");
            //              $dnoi-> set_EInvUserName("FASHIONKIN_API_CKP");
            //              $dnoi-> set_EInvPassword("Fashionking@29");
            //              $dnoi-> set_EFUserName("B4FFC421-D76D-406D-9F09-8ACEC72BD3C1");
            //              $dnoi-> set_EFPassword("3689CEA7-5DE2-41E7-A696-28F9FB3852B7");

                            //---------------------------------------------------------------------------------------------------------
                //for testing credential comment when deploy on live

                           $dnoi-> set_CDKey("1000687");
                           $dnoi-> set_EInvUserName("27AAACW3775F007");
                           $dnoi-> set_EInvPassword("Admin!23");
                           $dnoi-> set_EFUserName("29AAACW3775F000");
                           $dnoi-> set_EFPassword("Admin!23.."); 
                          
                     
                       
                
                
                
                
                $dn_items[$i]=$dnoi;
                
            }
     }
    
    $data_array['Data']=$dn_items;
    $Push_Data_List_array['Push_Data_List']=$data_array;
    echo json_encode($Push_Data_List_array);
    $fields_string=json_encode($Push_Data_List_array);
    
   
    
       $url = "http://einvSandbox.webtel.in/v1.03/GenIRN";//for testing
//       $url = "http://einvlive.webtel.in/v1.03/GenIRN";    //for live
       $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
//        curl_setopt($ch, CURLOPT_POST, count($fields));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);

        $output = curl_exec($ch);
//        print_r($output);
//        exit();
        
            //----------------------------------------API Response handling-----------------------
//            $output= '[{"ErrorMessage":"","ErrorCode":"","Status":"1","GSTIN":"27AAACW3775F007","DocNo":"202100095","DocType":"DBN","DocDate":"23/04/2021","Irn":"8f1c539bbbfda1cda384e0313d6280ec19c069933201288f4700e476dc2d1cd3","AckDate":"2021-04-23 15:09:33","AckNo":122110043722496,"EwbNo":null,"EwbDt":null,"EwbValidTill":null,"SignedQRCode":"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","SignedInvoice":"","IrnStatus":"ACT","InfoDtls":null,"Remarks":null,"UniqueKey":""}]';
                    
                    
                    
                    
            $reslogs_ins="insert into irn_response_log_dnote set DebitNote_No='".$pdfobj->debit_no."' ,Response = '".$output."'";
            $db->execInsert($reslogs_ins);
            
              $response= json_decode($output);
//      print_r($response);
//      exit();
         $imagename="";
       if(strcmp($response[0]->Status, "1") == 0){
//           $imagename="";
           $SignedQRCode=$response[0]->SignedQRCode;
//           echo $SignedQRCode;
           $path='../images/QR_code/';
//           $path='..images\QR_code';
            $image_base64 = base64_decode($SignedQRCode);
            $imagename=$pdfobj->debit_no. '.png';
            $file = $path .$imagename;
            file_put_contents($file, $image_base64);
            
//          
//       
            
                 }
//   $imagename="";
      $imagename =$db->safe($imagename);
      $ErrorMessage=$db->safe($response[0]->ErrorMessage);
      $ErrorCode=$db->safe($response[0]->ErrorCode);
      $Status=$db->safe($response[0]->Status);
      $GSTIN=$db->safe($response[0]->GSTIN);
      $DocNo=$db->safe($response[0]->DocNo);
      $DocType=$db->safe($response[0]->DocType);
      $DocDate=$db->safe($response[0]->DocDate);
      $Irn=$db->safe($response[0]->Irn);
      $AckDate=$db->safe($response[0]->AckDate);
//      $AckNo=$db->safe($response[0]->AckNo);
      $AckNo=$response[0]->AckNo;
      $EwbNo=$db->safe($response[0]->EwbNo);
      $EwbDt=$db->safe($response[0]->EwbDt);
      $EwbValidTill=$db->safe($response[0]->EwbValidTill);
      $SignedQRCode=$db->safe($response[0]->SignedQRCode);
      $SignedInvoice=$db->safe($response[0]->SignedInvoice);
      $IrnStatus=$db->safe($response[0]->IrnStatus);
      $InfoDtls=$db->safe($response[0]->InfoDtls);
      $Remarks=$db->safe($response[0]->Remarks);
      $UniqueKey=$db->safe($response[0]->UniqueKey);
      
      
       $response_data="";
      if($ErrorMessage !="")
      {
          $response_data .=", ErrorMessage =$ErrorMessage ";
      }
      if($ErrorCode !="")
      {
          $response_data .=", ErrorCode =$ErrorCode ";
      }
      if($Status !="")
      {
          $response_data .=", Status =$Status ";
      }
      if($GSTIN !="")
      {
          $response_data .=", GSTIN =$GSTIN ";
      }
      if($DocNo !="")
      {
          $response_data .=", DocNo =$DocNo ";
      }
      if($DocType !="")
      {
          $response_data .=", DocType =$DocType ";
      }
      if($DocDate !="")
      {
          $response_data .=", DocDate =$DocDate ";
      }
      if($Irn !="")
      {
          $response_data .=", irn =$Irn ";
      }
      if($AckDate !="")
      {
          $response_data .=", AckDate =$AckDate ";
      }
      if($AckNo !="")
      {
          $response_data .=", AckNo =$AckNo ";
      }
      if($EwbNo !="")
      {
          $response_data .=", EwbNo =$EwbNo ";
      }
      if($EwbDt !="")
      {
          $response_data .=", EwbDt =$EwbDt ";
      }
      if($EwbValidTill !="")
      {
          $response_data .=", EwbValidTill =$EwbValidTill ";
      }
      if($SignedQRCode !="")
      {
          $response_data .=", SignedQRCode =$SignedQRCode ";
      }
      if($SignedInvoice !="")
      {
          $response_data .=", SignedInvoice =$SignedInvoice ";
      }
      if($IrnStatus !="")
      {
          $response_data .=", IrnStatus =$IrnStatus ";
      }
      if($InfoDtls !="")
      {
          $response_data .=", InfoDtls =$InfoDtls ";
      }
      if($Remarks !="")
      {
          $response_data .=", Remarks =$Remarks ";
      }
      if($UniqueKey !="")
      {
          $response_data .=", UniqueKey =$UniqueKey ";
      }
     
//      echo "</br>".$response_data;
     
      $exist =$db->fetchObject("select id from it_irndebitnote where DeditNote_ID=$pdfobj->id");
      if(isset($exist))
      {
         $update_query="update it_irndebitnote set DebitNote_ID=$pdfobj->id $response_data ,Qr_Image=$imagename where DebitNote_ID=$order->id" ;
//         echo "</br></br></br>".$update_query;
         $db->execUpdate($update_query);
      }else{
          $insert_query="insert into it_irndebitnote set DebitNote_ID=$pdfobj->id $response_data ,Qr_Image=$imagename, CREATETIME=now()" ;
//          echo "</br></br></br>".$insert_query;
          $db->execInsert($insert_query);
      }
     
//exit();
    
////     echo strcmp($response[0]->Status, "1");
////     exit();
//            if(strcmp($response[0]->Status, "1")==0)
//      {
//          return "1::Irn Generate Successfully";
//      }else{
//          return "0::Debit Note API error: $ErrorMessage";
//      }
//    
//    
//    exit();
}





class debitNoteEinvItems {
    //put your code here
    public $GSTIN;
    public $Irn;
    public $Tran_SupTyp;
    public $Tran_RegRev;
    public $Tran_Typ;
    public $Tran_EcmGstin;
    public $Tran_IgstOnIntra;
    public $Doc_Typ;
    public $Doc_No;
    public $Doc_Dt;
    public $BillFrom_Gstin;
    public $BillFrom_LglNm;
    public $BillFrom_TrdNm;
    public $BillFrom_Addr1;
    public $BillFrom_Addr2;
    public $BillFrom_Loc;
    public $BillFrom_Pin;
    public $BillFrom_Stcd;
    public $BillFrom_Ph;
    public $BillFrom_Em;
    public $BillTo_Gstin;
    public $BillTo_LglNm;
    public $BillTo_TrdNm;
    public $BillTo_Pos;
    public $BillTo_Addr1;
    public $BillTo_Addr2;
    public $BillTo_Loc;
    public $BillTo_Pin;
    public $BillTo_Stcd;
    public $BillTo_Ph;
    public $BillTo_Em;
//    public $ShipFrom_Nm;
//    public $ShipFrom_Addr1;
//    public $ShipFrom_Addr2;
//    public $ShipFrom_Loc;
//    public $ShipFrom_Pin;
//    public $ShipFrom_Stcd;
//    public $ShipTo_Gstin;
//    public $ShipTo_LglNm;
//    public $ShipTo_TrdNm;
//    public $ShipTo_Addr1;
//    public $ShipTo_Addr2;
//    public $ShipTo_Loc;
//    public $ShipTo_Pin;
//    public $ShipTo_Stcd;
    public $Item_SlNo;
    public $Item_PrdDesc;
    public $Item_IsServc;
    public $Item_HsnCd;
    public $Item_Bch_Nm;
    public $Item_Bch_ExpDt;
    public $Item_Bch_WrDt;
    public $Item_Barcde;
    public $Item_Qty;
    public $Item_FreeQty;
   
    public $Item_Unit;
    public $Item_UnitPrice;
    public $Item_TotAmt;
    public $Item_Discount;
    public $Item_PreTaxVal;
    public $Item_AssAmt;
    public $Item_GstRt;
    public $Item_IgstAmt;
    public $Item_CgstAmt;
    public $Item_SgstAmt;
    public $Item_CesRt;
    public $Item_CesAmt;
    public $Item_CesNonAdvlAmt;
    public $Item_StateCesRt;
    public $Item_StateCesAmt;
    public $Item_StateCesNonAdvlAmt;
    public $Item_OthChrg;
    public $Item_TotItemVal;
    public $Item_OrdLineRef;
    public $Item_OrgCntry;
    public $Item_PrdSlNo;
    public $Item_Attrib_Nm;
    public $Item_Attrib_Val;
    public $Val_AssVal;
    public $Val_CgstVal;
    public $Val_SgstVal;
    public $Val_IgstVal;
    public $Val_CesVal;
    public $Val_StCesVal;
    public $Val_Discount;
    public $Val_OthChrg;
    public $Val_RndOffAmt;
    public $Val_TotInvVal;
    public $Val_TotInvValFc;
    public $Pay_Nm;
    public $Pay_AccDet;
    public $Pay_Mode;
    public $Pay_FinInsBr;
    public $Pay_PayTerm;
    public $Pay_PayInstr;
    public $Pay_CrTrn;
    public $Pay_DirDr;
    public $Pay_CrDay;
    public $Pay_PaidAmt;
    public $Pay_PaymtDue;
    public $Ref_InvRm;
    public $Ref_InvStDt;
    public $Ref_InvEndDt;
    public $Ref_PrecDoc_InvNo;
    public $Ref_PrecDoc_InvDt;
    public $Ref_PrecDoc_OthRefNo;
    public $Ref_Contr_RecAdvRefr;
    public $Ref_Contr_RecAdvDt;
    public $Ref_Contr_TendRefr;
    public $Ref_Contr_ContrRefr;
    public $Ref_Contr_ExtRefr;
    public $Ref_Contr_ProjRefr;
    public $Ref_Contr_PORefr;
    public $Ref_Contr_PORefDt;
    public $AddlDoc_Url;
    public $AddlDoc_Docs;
    public $AddlDoc_Info;
//    public $Exp_ShipBNo;
//    public $Exp_ShipBDt;
//    public $Exp_Port;
//    public $Exp_RefClm;
//    public $Exp_ForCur;
//    public $Exp_CntCode;
//    public $Exp_ExpDuty;
    public $Ewb_TransId;
    public $Ewb_TransName;
    public $Ewb_TransMode;
    public $Ewb_Distance;
    public $Ewb_TransDocNo;
    public $Ewb_TransDocDt;
    public $Ewb_VehNo;
    public $Ewb_VehType;
    public $GetQRImg;
    public $GetSignedInvoice;
    public $CDKEY;
    public $EFUSERNAME;
    public $EFPASSWORD;
    public $EINVUSERNAME;
    public $EINVPASSWORD;
    
    function set_GSTIN($GSTIN) {
        $this->GSTIN = $GSTIN;
    }

    function set_Irn($Irn) {
        $this->Irn = $Irn;
    }

    function set_Tran_SupTyp($Tran_SupTyp) {
        $this->Tran_SupTyp = $Tran_SupTyp;
    }

    function set_Tran_RegRev($Tran_RegRev) {
        $this->Tran_RegRev = $Tran_RegRev;
    }

    function set_Tran_Typ($Tran_Typ) {
        $this->Tran_Typ = $Tran_Typ;
    }

    function set_Tran_EcmGstin($Tran_EcmGstin) {
        $this->Tran_EcmGstin = $Tran_EcmGstin;
    }

    function set_Tran_IgstOnIntra($Tran_IgstOnIntra) {
        $this->Tran_IgstOnIntra = $Tran_IgstOnIntra;
    }

    function set_Doc_Typ($Doc_Typ) {
        $this->Doc_Typ = $Doc_Typ;
    }

    function set_Doc_No($Doc_No) {
        $this->Doc_No = $Doc_No;
    }

    function set_Doc_Dt($Doc_Dt) {
        $this->Doc_Dt = $Doc_Dt;
    }

    function set_BillFrom_Gstin($BillFrom_Gstin) {
        $this->BillFrom_Gstin = $BillFrom_Gstin;
    }

    function set_BillFrom_LglNm($BillFrom_LglNm) {
        $this->BillFrom_LglNm = $BillFrom_LglNm;
    }

    function set_BillFrom_TrdNm($BillFrom_TrdNm) {
        $this->BillFrom_TrdNm = $BillFrom_TrdNm;
    }

    function set_BillFrom_Addr1($BillFrom_Addr1) {
        $this->BillFrom_Addr1 = $BillFrom_Addr1;
    }

    function set_BillFrom_Addr2($BillFrom_Addr2) {
        $this->BillFrom_Addr2 = $BillFrom_Addr2;
    }

    function set_BillFrom_Loc($BillFrom_Loc) {
        $this->BillFrom_Loc = $BillFrom_Loc;
    }

    function set_BillFrom_Pin($BillFrom_Pin) {
        $this->BillFrom_Pin = $BillFrom_Pin;
    }

    function set_BillFrom_Stcd($BillFrom_Stcd) {
        $this->BillFrom_Stcd = $BillFrom_Stcd;
    }

    function set_BillFrom_Ph($BillFrom_Ph) {
        $this->BillFrom_Ph = $BillFrom_Ph;
    }

    function set_BillFrom_Em($BillFrom_Em) {
        $this->BillFrom_Em = $BillFrom_Em;
    }

    function set_BillTo_Gstin($BillTo_Gstin) {
        $this->BillTo_Gstin = $BillTo_Gstin;
    }

    function set_BillTo_LglNm($BillTo_LglNm) {
        $this->BillTo_LglNm = $BillTo_LglNm;
    }

    function set_BillTo_TrdNm($BillTo_TrdNm) {
        $this->BillTo_TrdNm = $BillTo_TrdNm;
    }

    function set_BillTo_Pos($BillTo_Pos) {
        $this->BillTo_Pos = $BillTo_Pos;
    }

    function set_BillTo_Addr1($BillTo_Addr1) {
        $this->BillTo_Addr1 = $BillTo_Addr1;
    }

    function set_BillTo_Addr2($BillTo_Addr2) {
        $this->BillTo_Addr2 = $BillTo_Addr2;
    }

    function set_BillTo_Loc($BillTo_Loc) {
        $this->BillTo_Loc = $BillTo_Loc;
    }

    function set_BillTo_Pin($BillTo_Pin) {
        $this->BillTo_Pin = $BillTo_Pin;
    }

    function set_BillTo_Stcd($BillTo_Stcd) {
        $this->BillTo_Stcd = $BillTo_Stcd;
    }

    function set_BillTo_Ph($BillTo_Ph) {
        $this->BillTo_Ph = $BillTo_Ph;
    }

    function set_BillTo_Em($BillTo_Em) {
        $this->BillTo_Em = $BillTo_Em;
    }

//    function set_ShipFrom_Nm($ShipFrom_Nm) {
//        $this->ShipFrom_Nm = $ShipFrom_Nm;
//    }
//
//    function set_ShipFrom_Addr1($ShipFrom_Addr1) {
//        $this->ShipFrom_Addr1 = $ShipFrom_Addr1;
//    }
//
//    function set_ShipFrom_Addr2($ShipFrom_Addr2) {
//        $this->ShipFrom_Addr2 = $ShipFrom_Addr2;
//    }
//
//    function set_ShipFrom_Loc($ShipFrom_Loc) {
//        $this->ShipFrom_Loc = $ShipFrom_Loc;
//    }
//
//    function set_ShipFrom_Pin($ShipFrom_Pin) {
//        $this->ShipFrom_Pin = $ShipFrom_Pin;
//    }

//    function set_ShipFrom_Stcd($ShipFrom_Stcd) {
//        $this->ShipFrom_Stcd = $ShipFrom_Stcd;
//    }
//
//    function set_ShipTo_Gstin($ShipTo_Gstin) {
//        $this->ShipTo_Gstin = $ShipTo_Gstin;
//    }
//
//    function set_ShipTo_LglNm($ShipTo_LglNm) {
//        $this->ShipTo_LglNm = $ShipTo_LglNm;
//    }
//
//    function set_ShipTo_TrdNm($ShipTo_TrdNm) {
//        $this->ShipTo_TrdNm = $ShipTo_TrdNm;
//    }
//
//    function set_ShipTo_Addr1($ShipTo_Addr1) {
//        $this->ShipTo_Addr1 = $ShipTo_Addr1;
//    }
//
//    function set_ShipTo_Addr2($ShipTo_Addr2) {
//        $this->ShipTo_Addr2 = $ShipTo_Addr2;
//    }
//
//    function set_ShipTo_Loc($ShipTo_Loc) {
//        $this->ShipTo_Loc = $ShipTo_Loc;
//    }

    function set_ShipTo_Pin($ShipTo_Pin) {
        $this->ShipTo_Pin = $ShipTo_Pin;
    }

    function set_ShipTo_Stcd($ShipTo_Stcd) {
        $this->ShipTo_Stcd = $ShipTo_Stcd;
    }

    function set_Item_SlNo($Item_SlNo) {
        $this->Item_SlNo = $Item_SlNo;
    }

    function set_Item_PrdDesc($Item_PrdDesc) {
        $this->Item_PrdDesc = $Item_PrdDesc;
    }

    function set_Item_IsServc($Item_IsServc) {
        $this->Item_IsServc = $Item_IsServc;
    }

    function set_Item_HsnCd($Item_HsnCd) {
        $this->Item_HsnCd = $Item_HsnCd;
    }

    function set_Item_Bch_Nm($Item_Bch_Nm) {
        $this->Item_Bch_Nm = $Item_Bch_Nm;
    }

    function set_Item_Bch_ExpDt($Item_Bch_ExpDt) {
        $this->Item_Bch_ExpDt = $Item_Bch_ExpDt;
    }

    function set_Item_Bch_WrDt($Item_Bch_WrDt) {
        $this->Item_Bch_WrDt = $Item_Bch_WrDt;
    }

    function set_Item_Barcde($Item_Barcde) {
        $this->Item_Barcde = $Item_Barcde;
    }

    function set_Item_Qty($Item_Qty) {
        $this->Item_Qty = $Item_Qty;
    }

    function set_Item_FreeQty($Item_FreeQty) {
        $this->Item_FreeQty = $Item_FreeQty;
    }

    function set_Item_Unit($Item_Unit) {
        $this->Item_Unit = $Item_Unit;
    }

    function set_Item_UnitPrice($Item_UnitPrice) {
        $this->Item_UnitPrice = $Item_UnitPrice;
    }

    function set_Item_TotAmt($Item_TotAmt) {
        $this->Item_TotAmt = $Item_TotAmt;
    }

    function set_Item_Discount($Item_Discount) {
        $this->Item_Discount = $Item_Discount;
    }

    function set_Item_PreTaxVal($Item_PreTaxVal) {
        $this->Item_PreTaxVal = $Item_PreTaxVal;
    }

    function set_Item_AssAmt($Item_AssAmt) {
        $this->Item_AssAmt = $Item_AssAmt;
    }

    function set_Item_GstRt($Item_GstRt) {
        $this->Item_GstRt = $Item_GstRt;
    }

    function set_Item_IgstAmt($Item_IgstAmt) {
        $this->Item_IgstAmt = $Item_IgstAmt;
    }

    function set_Item_CgstAmt($Item_CgstAmt) {
        $this->Item_CgstAmt = $Item_CgstAmt;
    }

    function set_Item_SgstAmt($Item_SgstAmt) {
        $this->Item_SgstAmt = $Item_SgstAmt;
    }

    function set_Item_CesRt($Item_CesRt) {
        $this->Item_CesRt = $Item_CesRt;
    }

    function set_Item_CesAmt($Item_CesAmt) {
        $this->Item_CesAmt = $Item_CesAmt;
    }

    function set_Item_CesNonAdvlAmt($Item_CesNonAdvlAmt) {
        $this->Item_CesNonAdvlAmt = $Item_CesNonAdvlAmt;
    }

    function set_Item_StateCesRt($Item_StateCesRt) {
        $this->Item_StateCesRt = $Item_StateCesRt;
    }

    function set_Item_StateCesAmt($Item_StateCesAmt) {
        $this->Item_StateCesAmt = $Item_StateCesAmt;
    }

    function set_Item_StateCesNonAdvlAmt($Item_StateCesNonAdvlAmt) {
        $this->Item_StateCesNonAdvlAmt = $Item_StateCesNonAdvlAmt;
    }

    function set_Item_OthChrg($Item_OthChrg) {
        $this->Item_OthChrg = $Item_OthChrg;
    }

    function set_Item_TotItemVal($Item_TotItemVal) {
        $this->Item_TotItemVal = $Item_TotItemVal;
    }

    function set_Item_OrdLineRef($Item_OrdLineRef) {
        $this->Item_OrdLineRef = $Item_OrdLineRef;
    }

    function set_Item_OrgCntry($Item_OrgCntry) {
        $this->Item_OrgCntry = $Item_OrgCntry;
    }

    function set_Item_PrdSlNo($Item_PrdSlNo) {
        $this->Item_PrdSlNo = $Item_PrdSlNo;
    }

    function set_Item_Attrib_Nm($Item_Attrib_Nm) {
        $this->Item_Attrib_Nm = $Item_Attrib_Nm;
    }

    function set_Item_Attrib_Val($Item_Attrib_Val) {
        $this->Item_Attrib_Val = $Item_Attrib_Val;
    }

    function set_Val_AssVal($Val_AssVal) {
        $this->Val_AssVal = $Val_AssVal;
    }

    function set_Val_CgstVal($Val_CgstVal) {
        $this->Val_CgstVal = $Val_CgstVal;
    }

    function set_Val_SgstVal($Val_SgstVal) {
        $this->Val_SgstVal = $Val_SgstVal;
    }

    function set_Val_IgstVal($Val_IgstVal) {
        $this->Val_IgstVal = $Val_IgstVal;
    }

    function set_Val_CesVal($Val_CesVal) {
        $this->Val_CesVal = $Val_CesVal;
    }

    function set_Val_StCesVal($Val_StCesVal) {
        $this->Val_StCesVal = $Val_StCesVal;
    }

    function set_Val_Discount($Val_Discount) {
        $this->Val_Discount = $Val_Discount;
    }

    function set_Val_OthChrg($Val_OthChrg) {
        $this->Val_OthChrg = $Val_OthChrg;
    }

    function set_Val_RndOffAmt($Val_RndOffAmt) {
        $this->Val_RndOffAmt = $Val_RndOffAmt;
    }

    function set_Val_TotInvVal($Val_TotInvVal) {
        $this->Val_TotInvVal = $Val_TotInvVal;
    }

    function set_Val_TotInvValFc($Val_TotInvValFc) {
        $this->Val_TotInvValFc = $Val_TotInvValFc;
    }

    function set_Pay_Nm($Pay_Nm) {
        $this->Pay_Nm = $Pay_Nm;
    }

    function set_Pay_AccDet($Pay_AccDet) {
        $this->Pay_AccDet = $Pay_AccDet;
    }

    function set_Pay_Mode($Pay_Mode) {
        $this->Pay_Mode = $Pay_Mode;
    }

    function set_Pay_FinInsBr($Pay_FinInsBr) {
        $this->Pay_FinInsBr = $Pay_FinInsBr;
    }

    function set_Pay_PayTerm($Pay_PayTerm) {
        $this->Pay_PayTerm = $Pay_PayTerm;
    }

    function set_Pay_PayInstr($Pay_PayInstr) {
        $this->Pay_PayInstr = $Pay_PayInstr;
    }

    function set_Pay_CrTrn($Pay_CrTrn) {
        $this->Pay_CrTrn = $Pay_CrTrn;
    }

    function set_Pay_DirDr($Pay_DirDr) {
        $this->Pay_DirDr = $Pay_DirDr;
    }

    function set_Pay_CrDay($Pay_CrDay) {
        $this->Pay_CrDay = $Pay_CrDay;
    }

    function set_Pay_PaidAmt($Pay_PaidAmt) {
        $this->Pay_PaidAmt = $Pay_PaidAmt;
    }

    function set_Pay_PaymtDue($Pay_PaymtDue) {
        $this->Pay_PaymtDue = $Pay_PaymtDue;
    }

    function set_Ref_InvRm($Ref_InvRm) {
        $this->Ref_InvRm = $Ref_InvRm;
    }

    function set_Ref_InvStDt($Ref_InvStDt) {
        $this->Ref_InvStDt = $Ref_InvStDt;
    }

    function set_Ref_InvEndDt($Ref_InvEndDt) {
        $this->Ref_InvEndDt = $Ref_InvEndDt;
    }

    function set_Ref_PrecDoc_InvNo($Ref_PrecDoc_InvNo) {
        $this->Ref_PrecDoc_InvNo = $Ref_PrecDoc_InvNo;
    }

    function set_Ref_PrecDoc_InvDt($Ref_PrecDoc_InvDt) {
        $this->Ref_PrecDoc_InvDt = $Ref_PrecDoc_InvDt;
    }

    function set_Ref_PrecDoc_OthRefNo($Ref_PrecDoc_OthRefNo) {
        $this->Ref_PrecDoc_OthRefNo = $Ref_PrecDoc_OthRefNo;
    }

    function set_Ref_Contr_RecAdvRefr($Ref_Contr_RecAdvRefr) {
        $this->Ref_Contr_RecAdvRefr = $Ref_Contr_RecAdvRefr;
    }

    function set_Ref_Contr_RecAdvDt($Ref_Contr_RecAdvDt) {
        $this->Ref_Contr_RecAdvDt = $Ref_Contr_RecAdvDt;
    }

    function set_Ref_Contr_TendRefr($Ref_Contr_TendRefr) {
        $this->Ref_Contr_TendRefr = $Ref_Contr_TendRefr;
    }

    function set_Ref_Contr_ContrRefr($Ref_Contr_ContrRefr) {
        $this->Ref_Contr_ContrRefr = $Ref_Contr_ContrRefr;
    }

    function set_Ref_Contr_ExtRefr($Ref_Contr_ExtRefr) {
        $this->Ref_Contr_ExtRefr = $Ref_Contr_ExtRefr;
    }

    function set_Ref_Contr_ProjRefr($Ref_Contr_ProjRefr) {
        $this->Ref_Contr_ProjRefr = $Ref_Contr_ProjRefr;
    }

    function set_Ref_Contr_PORefr($Ref_Contr_PORefr) {
        $this->Ref_Contr_PORefr = $Ref_Contr_PORefr;
    }

    function set_Ref_Contr_PORefDt($Ref_Contr_PORefDt) {
        $this->Ref_Contr_PORefDt = $Ref_Contr_PORefDt;
    }

    function set_AddlDoc_Url($AddlDoc_Url) {
        $this->AddlDoc_Url = $AddlDoc_Url;
    }

    function set_AddlDoc_Docs($AddlDoc_Docs) {
        $this->AddlDoc_Docs = $AddlDoc_Docs;
    }

    function set_AddlDoc_Info($AddlDoc_Info) {
        $this->AddlDoc_Info = $AddlDoc_Info;
    }

//    function set_Exp_ShipBNo($Exp_ShipBNo) {
//        $this->Exp_ShipBNo = $Exp_ShipBNo;
//    }
//
//    function set_Exp_ShipBDt($Exp_ShipBDt) {
//        $this->Exp_ShipBDt = $Exp_ShipBDt;
//    }
//
//    function set_Exp_Port($Exp_Port) {
//        $this->Exp_Port = $Exp_Port;
//    }
//
//    function set_Exp_RefClm($Exp_RefClm) {
//        $this->Exp_RefClm = $Exp_RefClm;
//    }
//
//    function set_Exp_ForCur($Exp_ForCur) {
//        $this->Exp_ForCur = $Exp_ForCur;
//    }
//
//    function set_Exp_CntCode($Exp_CntCode) {
//        $this->Exp_CntCode = $Exp_CntCode;
//    }
//
//    function set_Exp_ExpDuty($Exp_ExpDuty) {
//        $this->Exp_ExpDuty = $Exp_ExpDuty;
//    }

    function set_Ewb_TransId($Ewb_TransId) {
        $this->Ewb_TransId = $Ewb_TransId;
    }

    function set_Ewb_TransName($Ewb_TransName) {
        $this->Ewb_TransName = $Ewb_TransName;
    }

    function set_Ewb_TransMode($Ewb_TransMode) {
        $this->Ewb_TransMode = $Ewb_TransMode;
    }

    function set_Ewb_Distance($Ewb_Distance) {
        $this->Ewb_Distance = $Ewb_Distance;
    }

    function set_Ewb_TransDocNo($Ewb_TransDocNo) {
        $this->Ewb_TransDocNo = $Ewb_TransDocNo;
    }

    function set_Ewb_TransDocDt($Ewb_TransDocDt) {
        $this->Ewb_TransDocDt = $Ewb_TransDocDt;
    }

    function set_Ewb_VehNo($Ewb_VehNo) {
        $this->Ewb_VehNo = $Ewb_VehNo;
    }

    function set_Ewb_VehType($Ewb_VehType) {
        $this->Ewb_VehType = $Ewb_VehType;
    }

    function set_GetQRImg($GetQRImg) {
        $this->GetQRImg = $GetQRImg;
    }

    function set_GetSignedInvoice($GetSignedInvoice) {
        $this->GetSignedInvoice = $GetSignedInvoice;
    }

    function set_CDKEY($CDKEY) {
        $this->CDKEY = $CDKEY;
    }

    function set_EFUSERNAME($EFUSERNAME) {
        $this->EFUSERNAME = $EFUSERNAME;
    }

    function set_EFPASSWORD($EFPASSWORD) {
        $this->EFPASSWORD = $EFPASSWORD;
    }

    function set_EINVUSERNAME($EINVUSERNAME) {
        $this->EINVUSERNAME = $EINVUSERNAME;
    }

    function set_EINVPASSWORD($EINVPASSWORD) {
        $this->EINVPASSWORD = $EINVPASSWORD;
    }





}
                    
           
          
                           
        
         