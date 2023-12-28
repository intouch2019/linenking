<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

ini_set('max_execution_time', 300);
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/conv/CurrencyConv.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once 'lib/users/clsUsers.php';
require_once "Classes/html2pdf/html2pdf.class.php";

extract($_GET);

$db = new DBConn();
$conv = new CurrencyConv();
$errors = array();
$success = array();

$pdfqyery="select * from it_debit_advice where debit_no='$cn_no' order by id desc limit 1";
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
                .fixed { width:534px; }
                .fixed1 { width:534px; }
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




  $html .= '<table width="100%" border="1px" align="center" style="border-collapse: collapse;">'
              
                        .'
                            <tr>';
            
            
            $html .= '<td align="center" colspan="6" style="font-size:14px;" class="fixed">&nbsp;&nbsp;&nbsp;&nbsp;<b>DEBIT NOTE</b>&nbsp;&nbsp;&nbsp;&nbsp;</td>';
                        
            
            
            
            $html .= '</tr>'
                    . '<tr>'
                    . '<td align="left" colspan="2" width="40%">&nbsp;Debit Note No.:DN' .$pdfobj->debit_no.'</td> '
                    
                   . '<td align="left" colspan="2"  width="30%">&nbsp;Date Of Debit: ' . $pdfobj->debit_dt . '</td>'
                    . '<td align="left" colspan="2" width="30%" >&nbsp;Ack Date: ' . $AckDate . '</td>';
           
             
            $storequery="select * from it_codes where id=$pdfobj->store_id";
            $store=$db->fetchObject($storequery);
            
            $html .= '</tr>'
                    . '<tr>'
                    . '<td align="left" colspan="2" >To,<br/><b>'.$store->store_name .'</b><br/>'
                    .$store->address.'<br/>' .
                    'Contact No.: ' . $store->phone2.'<br/>' .
                    'Dealer Discount.: ' . $store->discountset."%"
                   .'</td>'
                   
                   .'<td align="left" colspan="2">From,';
                    
            $html .= '<br/><b>Fashionking Brands Pvt. Ltd.</b>';
                    
                    
            $html .= '<br/>Plot No.21,22,23 <br/>Hi-Tech Textile Park,'
                    .'<br/> MIDC, Baramati, Dist. Pune- 413133'
                    .'<br/>Phone : 02112-244121'
                    .'</td>'
                    . '<td align="center" colspan="2">'
                    .'<img src="../images/QR_code/'.$Qr_Image.'" width="130" height="130">'
                    . '</td>'
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
                    .'<td align="left" colspan="1"><span style=\"font-size:14px; padding:12px 0 0px 0\">&nbsp;&nbsp;&nbsp;&nbsp;ACK No : '.$AckNo.'&nbsp;</span></td>'
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
            $pdfitemsQuery="select *,sum(quantity) as finalqty from it_debit_advice_items where debit_id=$pdfobj->id and createtime >= '$pdfobj->createtime' group by hsncode,rate,disc_of_goods";
            $pdfitems=$db->fetchObjectArray($pdfitemsQuery);
            //print_r($pdfitems);
            //print "$pdfitemsQuery";
            $cnt=count($pdfitems);
        
            $frt_ins_tax=($pdfobj->frt_ins*5)/100;
            if($cnt<=10)
            {
                $html=$html2.$html;
            }
            else { 
                    $html=$html1.$html;
             }
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
            //$printHtml .='<tbody>';
            foreach($pdfitems as $dai){
                
                
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
                            .'<td align="center" width="13%" colspan="2"><b>Ack Date:</b></td>'
                            .'<td align="center" width="37%" colspan="6">'.$AckDate.'</td>' 
                            .'<td align="center" width="13%" colspan="2">TOTAL OF IGST </td>'
                            .'<td align="center" width="37%" colspan="6">'.sprintf ("%.2f",$totalTax3).'</td>';      
                            $printHtml .= '</tr>'
                                    .'<tr>'
                            .'<td align="center" width="13%" colspan="2"><b>Ack No.:</b></td>'
                            .'<td align="center" width="37%" colspan="6">'.$AckNo.'</td>'
                            .'<td align="center" width="13%" colspan="2">ROUND OFF </td>'
                            .'<td align="center" width="37%" colspan="6">'.sprintf ("%.2f",$roundoff).'</td>';      
                            $printHtml .= '</tr>'
                                    .'<tr>'
                            .'<td align="center" width="13%" colspan="2"><b>Irn No.:</b></td>'
                            .'<td align="center" width="37%" colspan="6">'.$irn.'</td>'
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