<?php

/* 
 * Author -Nikhil.
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

//extract($_POST);
$errors = array();
$success = array();

class showPDF {
    

  function getIndianCurrency($number)
{
    $decimal = round($number - ($no = floor($number)), 2) * 100;
    $hundred = null;
    $digits_length = strlen($no);
    $i = 0;
    $str = array();
    $words = array(0 => '', 1 => 'One', 2 => 'Two',
        3 => 'Three', 4 => 'Four', 5 => 'Five', 6 => 'Six',
        7 => 'Seven', 8 => 'Eight', 9 => 'Nine',
        10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve',
        13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
        16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen',
        19 => 'Nineteen', 20 => 'Twenty', 30 => 'Thirty',
        40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty',
        70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety');
    $digits = array('', 'Hundred','Thousand','Lakh', 'Crore');
    while( $i < $digits_length ) {
        $divider = ($i == 2) ? 10 : 100;
        $number = floor($no % $divider);
        $no = floor($no / $divider);
        $i += $divider == 10 ? 1 : 2;
        if ($number) {
            $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
            $hundred = ($counter == 1 && $str[0]) ? ' And ' : null;
            $str [] = ($number < 21) ? $words[$number].' '. $digits[$counter]. $plural.' '.$hundred:$words[floor($number / 10) * 10].' '.$words[$number % 10]. ' '.$digits[$counter].$plural.' '.$hundred;
        } else $str[] = null;
    }
    $Rupees = implode('', array_reverse($str));
    $paise = ($decimal) ? "And " . ($words[$decimal / 10] . " " . $words[$decimal % 10]) . ' Paise' : '';
    return ($Rupees ? $Rupees . 'Rupees ' : '') . $paise ;
}

    function addPageHeader($str, $invObj) { //pass query obj as param for eg addPageHeader($obj_debit,$obj_debit_supplier)
            //table1
        $html=null; 
            $html = '<table width="100%" border="1px" align="center">'
                    .'<tr>';

            $html .= '<th align="center" colspan="3" style="font-size:14px;"><b>PROFORMA INVOICE</b></th>';

//            $html .= '</tr><tr><th align="left" width="25%">&nbsp; Debit Note No.: ' .$obj_debit->debit_no.'</th> '
                    $html .= '</tr><tr><th align="left" width="32.9%">&nbsp; Invoice No.: '.$invObj->proforma_invno.'</th> '
                    . '<th align="left" width="29.7%">&nbsp; Date: '.ddmmyy($invObj->proforma_time).'</th>'
                    .'<th align="left" width="37.5%">&nbsp; </th>';
           
                  
            $html .= '</tr>'
                    . '<td align="left" >To,'
                    . '<br/><b>'. $str->store_name .'</b><br/>' //supplier name from db
                    . '<br/>'. $str->address .','
                    . '<br/>'. $str->city .','. $str->zipcode .','
                    . '<br/>Contact No.: '. $str->phone .','. $str->phone2 .''
                    . '<br/>Retail Net Margin: '. $str->discountset .'%'
                    . '</td>';
            
            $html .= '<td align="left">From,'      
                    . '<br/><b>Fashionking Brands Pvt. Ltd.</b>';
                    
            $html .= '<br/>Plot No. 21,22,23 Hi-Tech Textile Park,'
                    .'<br/> MIDC, Baramati, Dist. Pune- 413133'
                    .'<br/>Phone : 02112-244120/21'
                    .'</td>';
            
            $html .= '<td align="left"><br/></td>';
            
            $html .= '</tr>'
                    .'</table>'; //end table1
            
            //table2 start
            $html .= '<table width="100%" border="1px" align="center">';

            $html.= '<tr>'
                    . '<td align="left" width="32.9%">GSTIN NO :&nbsp;&nbsp; '. $str->gstin_no.'</td>' //Supplier GST No
                    .'<td align="left" width="29.7%">GSTIN NO :&nbsp;&nbsp; 27AAACC4315H1ZQ</td>'  //FashionKing GST No
                    .'<td align="left" width="37.5%">E-Payments Details:Axis Bank Ltd Kothrud,Pune</td>' //FashionKing Account Details
                    .'</tr>';
            
            $html.= '<tr>'
                    . '<td align="left" width="32.9%">PAN NO :&nbsp;&nbsp; '. $str->pancard_no.'</td>' //Supplier Pan No
                    .'<td align="left" width="29.7%">PAN NO :&nbsp;&nbsp; AAACC4315H</td>'  //FashionKing Pan No
                    .'<td align="left" width="37.5%">A/c No:104010200006651 IFSC:UTIB0000104 MICR Code:411211004</td>' //FashionKing Account Details
                    .'</tr>' 
                    .'</table>';  //end table2

            return $html;
    }
    
    function addTableHeader() {  //pass query obj as param for eg addTableHeader($obj_debit,$obj_debit_total_items)
        $html=null; 
        $html .=  '<table width="100%" border="1px" align="center">'
                . '<thead>'
                . '<tr>'
                . '<th rowspan="2" align="center" width="2%" bgcolor=#C0C0C0><b>Sr. No</b></th>'
                . '<th rowspan="2" align="center" width="11%" bgcolor=#C0C0C0><b>Description of Goods</b></th>'
                . '<th rowspan="2" align="center" width="5%" bgcolor=#C0C0C0><b>HSN</b></th>'
                . '<th rowspan="2" align="center" width="5%" bgcolor=#C0C0C0><b>Qty</b></th>'
                . '<th rowspan="2"align="center" width="6%" bgcolor=#C0C0C0><b>MRP/Rate<br>(per item)</b></th>'
                . '<th rowspan="2" align="center" width="6%" bgcolor=#C0C0C0><b>Dealer<br>Discount<br>(per item)</b></th>'
                . '<th rowspan="2" align="center" width="6%" bgcolor=#C0C0C0><b>Add. Disc <br>(per item)</b></th>'
                . '<th rowspan="2" align="center" width="6%" bgcolor=#C0C0C0><b>Rate post<br>disc.<br>(per item)</b></th>'
                . '<th rowspan="2" align="center" width="6%" bgcolor=#C0C0C0><b>MRP Total</b></th>'
                . '<th rowspan="2" align="center" width="6%" bgcolor=#C0C0C0><b>Total Dealer<br>Discount</b></th>'
                . '<th rowspan="2" align="center" width="6%" bgcolor=#C0C0C0><b>Total Add.<br> Disc</b></th>'
                . '<th rowspan="2" align="center" width="6.2%" bgcolor=#C0C0C0><b>Taxable<br>Value</b></th>'
                . '<th colspan="2" align="center" width="9.6%" bgcolor=#C0C0C0><b>CGST</b></th>'
                . '<th colspan="2" align="center" width="9.6%" bgcolor=#C0C0C0><b>SGST</b></th>'
                . '<th colspan="2" align="center" width="9.6%" bgcolor=#C0C0C0><b>IGST</b></th>'
                . '</tr>'
                . '<tr>'
                . '<th align="center" width="4.8%" bgcolor=#C0C0C0>Rate</th>'
                . '<th align="center" width="4.8%" bgcolor=#C0C0C0>Amount</th>'
                . '<th align="center" width="4.8%" bgcolor=#C0C0C0>Rate</th>'
                . '<th align="center" width="4.8%" bgcolor=#C0C0C0>Amount</th>'
                . '<th align="center" width="4.8%" bgcolor=#C0C0C0>Rate</th>'
                . '<th align="center" width="4.8%" bgcolor=#C0C0C0>Amount</th>'
                . '</tr>'
                . '</thead>';
        return $html;
    }
    
    function TableBody($items, $str, $stateObj, $avail_cp, $invObj, $cp){
//        print_r($items);
//        echo '<br>HI';
//        print_r($str); exit();
        $printHtml=null;
        $totalQty=0.0;
        $Sum_mrpTotal=0.0;
        $Sum_dealerDiscTotal=0.0;
        $Sum_addDiscTotal=0.0;
        $Sum_TaxableVal=0.0;
        $totalTaxvalue=0.0;
        $i=1;
        $totalLinesCount =0;
        $count =1;
        $myCount = 20;
        $total_mrp = 0.0;
        $cp_to_be_used = 0.0;
        
        $tax_rate = 0;
        $tax_rate_pd = 0;
        $totalTax1 = 0.0;
        $totalTax2 = 0.0;
        $totalTax3 = 0.0;
        
        $sum_MrpValue5 = 0.0;
        $sum_MrpValue12 = 0.0;
        $sum_MrpValue18 = 0.0;
        $sum_MrpValue28 = 0.0;
        $sum_DiscValue5 = 0.0;
        $sum_DiscValue12 = 0.0;
        $sum_DiscValue18 = 0.0;
        $sum_DiscValue28 = 0.0;
        $sum_TaxValue5 = 0.0;
        $sum_TaxValue12 = 0.0;
        $sum_TaxValue18 = 0.0;
        $sum_TaxValue28 = 0.0;
        $tax_value5 = 0.0;
        $tax_value12 = 0.0;
        $tax_value18 = 0.0;
        $tax_value28 = 0.0;
        $total_sum_MrpValue = 0.0;
        $total_sum_DiscValue = 0.0;
        $total_sum_TaxValue5 = 0.0;
        $total_CGST = 0.0;
        $total_SGST = 0.0;
        $total_IGST = 0.0;

        foreach($items as $obj){ 
            $total_mrp += ($obj->mrp * $obj->order_qty);
            $totalLinesCount++;   
        }
//        print_r($total_mrp); 
//        exit();
        
        if(($avail_cp + ($avail_cp * 0.6)) <= $total_mrp){
            $cp_to_be_used = $avail_cp;
            
        } else {
            $cp_to_be_used = $avail_cp * 0.6;
        }
//        print_r($cp_to_be_used); 
//        exit();
        
        foreach ($items as $item) {
            
            if ($item->order_qty * $item->mrp * 0.6 <= $cp_to_be_used) {
                $add_disc = $item->mrp * 0.6;

            } else if ($item->order_qty * $item->mrp * 0.6 > $cp_to_be_used) {
                    $add_disc = $cp_to_be_used / $item->order_qty;
            } else {
                    $add_disc = $cp_to_be_used;
            } 



            //tax rate on MRP value
            if($item->mrp > 1050) {
                $tax_rate = 12;
            } else {
                $tax_rate = 5;
            }
            
            $gst_disc = ($item->mrp / (100 + $tax_rate)) * $tax_rate;
            $margin_disc = $item->mrp * ($str->discountset / 100);
            $dealer_disc = round($gst_disc + $margin_disc, 2, PHP_ROUND_HALF_EVEN);
            
//            $add_disc = 0.0;  //temporary  || Add credit point logic for this
            $rate_post_disc = round(($item->mrp - $dealer_disc - $add_disc), 2, PHP_ROUND_HALF_EVEN);
            $mrp_total = round(($item->order_qty * $item->mrp), 2, PHP_ROUND_HALF_EVEN);
            $total_dealer_disc = round(($item->order_qty * $dealer_disc), 2, PHP_ROUND_HALF_EVEN);
            $total_add_disc = round(($item->order_qty * $add_disc), 2, PHP_ROUND_HALF_EVEN);
            $taxable_value = round(($mrp_total - $total_dealer_disc - $total_add_disc), 2, PHP_ROUND_HALF_EVEN);
            
            if($cp_to_be_used > 0 ){
            $cp_to_be_used = $cp_to_be_used - $total_add_disc;
            }
            
//            echo $cp_to_be_used."<br>"; 
             //tax rate on RATE POST DISCOUNT value
            if($rate_post_disc > 1000) {
                $tax_rate_pd = 12;  
            } else {
                $tax_rate_pd = 5;
            }
            $tax_val = round(($taxable_value * $tax_rate_pd / 100), 2, PHP_ROUND_HALF_EVEN);
            
            //Sum of MRP Value, Discount value,  Taxable Value, Calculation for each Tax slab  || Last table in Invoice PDF
            
            if($tax_rate_pd ==5){
                $mrp_value5 = round(($item->order_qty * $item->mrp), 2, PHP_ROUND_HALF_EVEN);
                $sum_MrpValue5 += $mrp_value5;
                $disc_value5 = round(($item->order_qty * $dealer_disc), 2, PHP_ROUND_HALF_EVEN) + round(($item->order_qty * $add_disc), 2, PHP_ROUND_HALF_EVEN);
                $sum_DiscValue5 += $disc_value5;
                $add_disc_value5 = round(($item->order_qty * $add_disc), 2, PHP_ROUND_HALF_EVEN);
                $sum_TaxValue5 += round(($mrp_value5 - $disc_value5), 2, PHP_ROUND_HALF_EVEN);
                $tax_value5 = round(($sum_TaxValue5 * $tax_rate_pd / 100), 2, PHP_ROUND_HALF_EVEN);
                
            } else if($tax_rate_pd ==12){
                $mrp_value12 = round(($item->order_qty * $item->mrp), 2, PHP_ROUND_HALF_EVEN);
                $sum_MrpValue12 += $mrp_value12;
                $disc_value12 = round(($item->order_qty * $dealer_disc), 2, PHP_ROUND_HALF_EVEN) + round(($item->order_qty * $add_disc), 2, PHP_ROUND_HALF_EVEN);
                $sum_DiscValue12 += $disc_value12;
                $add_disc_value12 = round(($item->order_qty * $add_disc), 2, PHP_ROUND_HALF_EVEN);
                $sum_TaxValue12 += round(($mrp_value12 - $disc_value12), 2, PHP_ROUND_HALF_EVEN);
                $tax_value12 = round(($sum_TaxValue12 * $tax_rate_pd / 100), 2, PHP_ROUND_HALF_EVEN);
                
            } else if($tax_rate_pd ==18){
                $mrp_value18 = round(($item->order_qty * $item->mrp), 2, PHP_ROUND_HALF_EVEN);
                $sum_MrpValue18 += $mrp_value18;
                $disc_value18 = round(($item->order_qty * $dealer_disc), 2, PHP_ROUND_HALF_EVEN) + round(($item->order_qty * $add_disc), 2, PHP_ROUND_HALF_EVEN);
                $sum_DiscValue18 += $disc_value18;
                $add_disc_value18 = round(($item->order_qty * $add_disc), 2, PHP_ROUND_HALF_EVEN);
                $sum_TaxValue18 += round(($mrp_value18 - $disc_value18), 2, PHP_ROUND_HALF_EVEN);
                $tax_value18 = round(($sum_TaxValue18 * $tax_rate_pd / 100), 2, PHP_ROUND_HALF_EVEN);
                
            } else if($tax_rate_pd ==28){
                $mrp_value28 = round(($item->order_qty * $item->mrp), 2, PHP_ROUND_HALF_EVEN);
                $sum_MrpValue28 += $mrp_value28;
                $disc_value28 = round(($item->order_qty * $dealer_disc), 2, PHP_ROUND_HALF_EVEN) + round(($item->order_qty * $add_disc), 2, PHP_ROUND_HALF_EVEN);
                $sum_DiscValue28 += $disc_value28;
                $add_disc_value28 = round(($item->order_qty * $add_disc), 2, PHP_ROUND_HALF_EVEN);
                $sum_TaxValue28 += round(($mrp_value28 - $disc_value28), 2, PHP_ROUND_HALF_EVEN);
                $tax_value28 = round(($sum_TaxValue28 * $tax_rate_pd / 100), 2, PHP_ROUND_HALF_EVEN);
                
            }
            //end for -Sum of MRP Value, Discount value,  Taxable Value, Calculation for each Tax slab  || Last table in Invoice PDF
            
//dynamic value of product details
            $printHtml .= '<tbody>'
                    . '<tr>'
                    . '<td align="center" width="2%">' . $i . '</td>'
                    . '<td align="center" width="11%">' . $this->breakText($item->categoryname) . '</td>'
                    . '<td align="center" width="5%">' . $item->hsncode . '</td>'
                    . '<td align="center"  width="5%">' . $item->order_qty . '</td>'
                    . '<td align="center" width="6%">' . $item->mrp . '</td>'
                    . '<td align="center" width="6%">' . sprintf("%.2f", $dealer_disc) . '</td>'
                    . '<td align="center" width="6%">' . sprintf("%.2f", $add_disc) . '</td>'  //Additional Discount Calculate from Credit Point
                    . '<td align="center" width="6%">' . sprintf("%.2f", $rate_post_disc) . '</td>'
                    . '<td align="center" width="6%">' . sprintf("%.2f", $mrp_total) . '</td>'
                    . '<td align="center" width="6%">' . sprintf("%.2f", $total_dealer_disc) . '</td>'
                    . '<td align="center" width="6%">' . sprintf("%.2f", $total_add_disc) . '</td>'
                    . '<td align="center" width="6%">' . sprintf("%.2f", $taxable_value) . '</td>';
            
        if(trim($stateObj->state_code) == "MH"){
          $tax_2 = round(($tax_val/2), 2, PHP_ROUND_HALF_EVEN);
          $tax_per = $tax_rate_pd/2;
          
           $printHtml .= '<td align="center" width="4.8%">' .$tax_per.'%</td>'
                      .'<td align="center" width="4.8%">'.sprintf ("%.2f",$tax_2).'</td>'
                      .'<td align="center" width="4.8%">'.$tax_per.'%</td>'
                      .'<td align="center" width="4.8%">'.sprintf ("%.2f",$tax_2). '</td>'
                      .'<td align="center" width="4.8%">'.'-'.'</td>'
                      .'<td align="center" width="4.8%">'.'-'. '</td>';
           
           $totalTax1 += $tax_2;
           $totalTax2 += $tax_2;
//           $totalInvoiceVal1 += ($taxable_value+$tax_2+$tax_2);
           
        } else {
            
                $printHtml .= '<td align="center" width="4.8%">'.'-'.'</td>'
                            .'<td align="center" width="4.8%">' . '-'.'</td>'
                            .'<td align="center" width="4.8%">'.'-'.'</td>'
                            .'<td align="center" width="4.8%">'. '-'.'</td>'
                            .'<td align="center" width="4.8%">' . $tax_rate_pd.'%</td>'
                            .'<td align="center" width="4.8%">' .sprintf ("%.2f",$tax_val).'</td>';
                
                $totalTax3 += $tax_val;  
//                $totalInvoiceVal1 += ($taxable_value+$tax_val);
          
        }

            $printHtml .= '</tr>';
            $printHtml .= '</tbody>';
            //tbody end

            $totalQty += $item->order_qty;
            $Sum_mrpTotal += $mrp_total;
            
            $Sum_dealerDiscTotal += $total_dealer_disc;
            $Sum_addDiscTotal += $total_add_disc;
            $Sum_TaxableVal += $taxable_value;
            $totalTaxvalue += $tax_val;


            $totalInvoiceVal = $Sum_TaxableVal + $totalTaxvalue;
            $roundTotalInvoiceVal = round($totalInvoiceVal);
            $roundoff = $roundTotalInvoiceVal - $totalInvoiceVal;

            $tcsValue = round($totalInvoiceVal * 0.1 / 100);
            $actualInvoiceValue = $roundTotalInvoiceVal + $tcsValue;

            if ($totalLinesCount > 8) {
//                        print_r($count); 
//                        print_r($myCount); 
//                        exit();
                if ($count >= $myCount && $count != $totalLinesCount) {
                    $printHtml .= "</table>";
                    $printHtml .= "<h2 align=\"right\">PTO</h2>";
                    $printHtml .= "<pdf:nextpage>";
                    $printHtml .= $this->addPageHeader();
                    $printHtml .= $this->addTableHeader();
                    //printHtml+="<table width=\"95%\" align=\"center\" border=\"1px\">";                                
                    //count = count-5;
                    $myCount += 20;
//                    print_r($count);
//                    echo '<br>';
//                    print_r($myCount);
//                    exit();
                }
            }

            if ($count == $totalLinesCount && ($totalLinesCount > $myCount - 12) && $totalLinesCount >= $myCount - 20) {
                if ($totalLinesCount > 8) {
//                    print_r($count);
//                    echo '<br>';
//                    print_r($myCount);
//                    exit();
                    $printHtml .= "</table>";
                    $printHtml .= "<h2 align=\"right\">PTO</h2>";
                    $printHtml .= "<pdf:nextpage>";
                    $printHtml .= $this->addPageHeader();
                    $printHtml .= $this->addTableHeader();

                    $ct = $myCount;
                    while ($ct < $myCount + 20 - 18) {
                        $printHtml .= '<tr>'
                                . '<td align="center" width="2%"></td>'
                                . '<td align="center" width="11%"></td>'
                                . '<td align="center" width="5%"></td>'
                                . '<td align="center"  width="5%"></td>'
                                . '<td align="center" width="6%"></td>'
                                . '<td align="center" width="6%"></td>'
                                . '<td align="center" width="6%"></td>'
                                . '<td align="center" width="6%"></td>'
                                . '<td align="center" width="6%"></td>'
                                . '<td align="center" width="6%"></td>'
                                . '<td align="center" width="6%"></td>'
                                . '<td align="center" width="6%"></td>';
                        $printHtml .= '<td align="center" width="4.8%"></td>'
                                . '<td align="center" width="4.8%"></td>'
                                . '<td align="center" width="4.8%"></td>'
                                . '<td align="center" width="4.8%"></td>'
                                . '<td align="center" width="4.8%"></td>'
                                . '<td align="center" width="4.8%"></td>';
                        $ct++;
                    }
                }
            } else if ($count == $totalLinesCount && $totalLinesCount < $myCount - 12 && $totalLinesCount > $myCount - 20) {
                if ($totalLinesCount < 8) {
                    $ct = $count;
                    while ($ct < $myCount - 12) {
                        $printHtml .= '<tr>'
                                . '<td align="center" width="2%">' . $ct . '</td>'
                                . '<td align="center" width="11%"></td>'
                                . '<td align="center" width="5%"></td>'
                                . '<td align="center"  width="5%"></td>'
                                . '<td align="center" width="6%"></td>'
                                . '<td align="center" width="6%"></td>'
                                . '<td align="center" width="6%"></td>'
                                . '<td align="center" width="6%"></td>'
                                . '<td align="center" width="6%"></td>'
                                . '<td align="center" width="6%"></td>'
                                . '<td align="center" width="6%"></td>'
                                . '<td align="center" width="6%"></td>';
                        $printHtml .= '<td align="center" width="4.8%"></td>'
                                . '<td align="center" width="4.8%"></td>'
                                . '<td align="center" width="4.8%"></td>'
                                . '<td align="center" width="4.8%"></td>'
                                . '<td align="center" width="4.8%"></td>'
                                . '<td align="center" width="4.8%"></td>';
                        $ct++;
                    }
                } else {
                    $ct = $count;
                    while ($ct < $myCount - 16) {
                        $printHtml .= '<tr>'
                                . '<td align="center" width="2%"></td>'
                                . '<td align="center" width="11%"></td>'
                                . '<td align="center" width="5%"></td>'
                                . '<td align="center"  width="5%"></td>'
                                . '<td align="center" width="6%"></td>'
                                . '<td align="center" width="6%"></td>'
                                . '<td align="center" width="6%"></td>'
                                . '<td align="center" width="6%"></td>'
                                . '<td align="center" width="6%"></td>'
                                . '<td align="center" width="6%"></td>'
                                . '<td align="center" width="6%"></td>'
                                . '<td align="center" width="6%"></td>';
                        $printHtml .= '<td align="center" width="4.8%"></td>'
                                . '<td align="center" width="4.8%"></td>'
                                . '<td align="center" width="4.8%"></td>'
                                . '<td align="center" width="4.8%"></td>'
                                . '<td align="center" width="4.8%"></td>'
                                . '<td align="center" width="4.8%"></td>';
                        $ct++;
                    }
                }
            }
            $i++;
            $count++;
        }
        
        $db = new DBConn();
        
        if (!empty($cp)) {
            $cp_used = intval($Sum_addDiscTotal);
            $sql = "insert into proforma_invoice_detail set store_id = $str->id, it_store_redeem_points_id = $cp->id, cp_used = $cp_used,"
                    . "proforma_invoice_no = '$invObj->proforma_invno' , order_no = $invObj->order_no, createtime = now()";
        } else {
            $cp_used = intval($Sum_addDiscTotal);
            $sql = "insert into proforma_invoice_detail set store_id = $str->id, cp_used = $Sum_addDiscTotal,"
                    . "proforma_invoice_no = '$invObj->proforma_invno' , order_no = $invObj->order_no, createtime = now()";
        }
//        echo $sql;
//        exit();
        $db->execInsert($sql);
//        exit();
//            return $printHtml;
        $printHtml .='<tfoot>'
                            .'<tr>'
                            .'<td align="center"  width="2%"></td>'
                            .'<td align="center" width="11%"><b>TOTAL</b> </td>'
                            .'<td align="center" width="5%"></td>'
                            .'<td align="center" width="5%">'.sprintf ("%.2f",$totalQty).'</td>'
                            .'<td align="center" width="6%"></td>'
                            .'<td align="center" width="6%"></td>'
                            .'<td align="center" width="6%"></td>'
                            .'<td align="center" width="6%"></td>'
                            .'<td align="center" width="6%">'.sprintf ("%.2f",$Sum_mrpTotal).'</td>'
                            .'<td align="center" width="6%">'.sprintf ("%.2f",$Sum_dealerDiscTotal).'</td>'
                            .'<td align="center" width="6%">'.sprintf ("%.2f",$Sum_addDiscTotal).'</td>'
                            .'<td align="center" width="6%">'.sprintf ("%.2f",$Sum_TaxableVal).'</td>';
                 
                   $printHtml .= '<td colspan="2" align="center" width="9.6%">'.sprintf ("%.2f",$totalTax1).'</td>'        
                             .'<td colspan="2" align="center" width="9.6%">'.sprintf ("%.2f",$totalTax2).'</td>'
                             .'<td colspan="2" align="center" width="9.6%">'.sprintf ("%.2f",$totalTax3).'</td>'
                             .'</tr>';
                   
                    $printHtml .= '<tr>'
                            .'<td align="center" width="13%" colspan="3">Total Invoice Value (In Figure):</td>'
                            .'<td align="center" width="37%" colspan="5">'.sprintf ("%.2f",$actualInvoiceValue).'</td>'
                            .'<td align="center" width="13%" colspan="4">TOTAL OF TAXABLE VALUE </td>'
                            .'<td align="center" width="37%" colspan="6">'.sprintf ("%.2f",$Sum_TaxableVal).'</td>'
                            .'</tr>';
                    
                    $printHtml .= '<tr>'
                                     .'<td align="center" width="13%" colspan="3">Total Invoice Value (In Words):  </td>'
                                        .'<td align="center" width="37%" colspan="5">'.$this->getIndianCurrency($actualInvoiceValue).'</td>';
                    
                    $printHtml .='<td align="center" width="13%" colspan="4">TOTAL OF CGST </td>'
                                     .'<td align="center" width="37%" colspan="6">'.sprintf ("%.2f",$totalTax1).'</td>'
                                     .'</tr>';
                    
                    $printHtml .='<tr>'
                           .'<td align="center" width="13%" colspan="3"> Total TAX Value (In Figure):</td>'
                            .'<td align="center" width="37%" colspan="5">'.sprintf ("%.2f",$totalTaxvalue).' </td>';  
                    
                    $printHtml .='<td align="center" width="13%" colspan="4">TOTAL OF SGST </td>'
                            .'<td align="center" width="37%" colspan="6">'.sprintf ("%.2f",$totalTax2).'</td>'     
                            .'</tr>';
                    
                    $printHtml .= '<tr>'
                           .'<td align="center" width="13%" colspan="3"> Total TAX Value (In Words): </td>'
                            .'<td align="center" width="37%" colspan="5">'.$this->getIndianCurrency($totalTaxvalue).'</td>'
                            .'<td align="center" width="13%" colspan="4">TOTAL OF IGST </td>'
                            .'<td align="center" width="37%" colspan="6">'.sprintf ("%.2f",$totalTax3).'</td>'   
                            .'</tr>'; 
                    
                    $printHtml .= '<tr>'
                            .'<td align="center" width="50%" colspan="8"></td>'
                            .'<td align="center" width="13%" colspan="4">Net Amount</td>'
                            .'<td align="center" width="37%" colspan="6">'.sprintf ("%.2f",$totalInvoiceVal).'</td>'     
                            .'</tr>';
                            
                    $printHtml .= '<tr>'
                           .'<td align="center" width="50%" colspan="8"></td>'
                            .'<td align="center" width="13%" colspan="4">TCS @0.1%</td>'
                            .'<td align="center" width="37%" colspan="6">'.sprintf ("%.2f",$tcsValue).'</td>'    
                            .'</tr>';
                    
                    $printHtml .= '<tr>'
                           .'<td align="center" width="50%" colspan="8"></td>'
                            .'<td align="center" width="13%" colspan="4">Round Off</td>'
                            .'<td align="center" width="37%" colspan="6">'.sprintf ("%.2f",$roundoff).'</td>'    
                            .'</tr>';
                    
                    $printHtml .= '<tr>'
                            .'<td align="center" width="50%" colspan="8"></td>'
                            .'<th align="center" width="13%" colspan="4"><b>INVOICE VALUE</b> </th>'      
                            .'<th align="center" width="37%" colspan="6"><b>'.sprintf ("%.2f",$actualInvoiceValue).'</b> </th>'             
                            .'</tr>';
                    
                    $printHtml .= '<tr>'
                            .'<th align="center" width="7.14%">Tax Rate</th>'
                            .'<th align="center" width="7.14%">Mrp Value</th>'
                            .'<th align="center" width="7.14%">Discount</br>Value</th>'
                            .'<th align="center" width="7.14%">Taxable</br>Value</th>'
                            .'<th align="center" width="7.14%">CGST</th>'
                            .'<th align="center" width="7.14%">SGST</th>'
                            .'<th align="center" width="7.14%">IGST</th>'
                            .'<th align="center" width="20%" rowspan="3" colspan="5">We declare that this invoice shows the actual price of the goods described and all particulars are true and correct. This is Computer Generated Invoice</th>'
                            .'<th align="center" width="15%" rowspan="6" colspan="3">Fashionking Brands Pvt Ltd</br></br></br> Authorised Signatory</th>'
                            .'<th align="center" width="15%" rowspan="6" colspan="3">Fashionking Brands Pvt Ltd</br></br></br> Authorised Signatory</th>' 
                            .'</tr>';

                    $printHtml .= '<tr>'
                            .'<td align="center" width="7.14%">5%</td>'
                            .'<td align="center" width="7.14%">'. $sum_MrpValue5 .'</td>'
                            .'<td align="center" width="7.14%">'. $sum_DiscValue5 .'</td>'
                            .'<td align="center" width="7.14%">'. $sum_TaxValue5 .'</td>';
                    
                    $total_sum_MrpValue += $sum_MrpValue5;
                    $total_sum_DiscValue += $sum_DiscValue5;
                    $total_sum_TaxValue5 += $sum_TaxValue5;

                    if(trim($stateObj->state_code) == "MH"){
                        $tax_val5 = round(($tax_value5/2), 2, PHP_ROUND_HALF_EVEN);
                        $printHtml .='<td align="center" width="7.14%">'. $tax_val5 .'</td>'
                            .'<td align="center" width="7.14%">'. $tax_val5 .'</td>'
                            .'<td align="center" width="7.14%">0.0</td>' 
                            .'</tr>';
                        
                        $total_CGST = round($total_CGST + $tax_val5, 2);
                        $total_SGST = round($total_SGST + $tax_val5, 2);

                        
                    } else {
                        $printHtml .='<td align="center" width="7.14%">0.0</td>'
                            .'<td align="center" width="7.14%">0.0</td>'
                            .'<td align="center" width="7.14%">'. $tax_value5 .'</td>' 
                            .'</tr>';
                        
                        $total_IGST = round($total_IGST + $tax_val5, 2);
                        
                    }
     
                    $printHtml .= '<tr>'
                            .'<td align="center" width="7.14%">12%</td>'
                            .'<td align="center" width="7.14%">'. $sum_MrpValue12 .'</td>'
                            .'<td align="center" width="7.14%">'. $sum_DiscValue12 .'</td>'
                            .'<td align="center" width="7.14%">'. $sum_TaxValue12 .'</td>';
                    
                    $total_sum_MrpValue += $sum_MrpValue12;
                    $total_sum_DiscValue += $sum_DiscValue12;
                    $total_sum_TaxValue5 += $sum_TaxValue12;
                    
                    if(trim($stateObj->state_code) == "MH"){
                        $tax_val12 = round(($tax_value12/2), 2, PHP_ROUND_HALF_EVEN);
                        $printHtml .='<td align="center" width="7.14%">'. $tax_val12 .'</td>'
                            .'<td align="center" width="7.14%">'. $tax_val12 .'</td>'
                            .'<td align="center" width="7.14%">0.0</td>' 
                            .'</tr>';
                        
                        $total_CGST = round($total_CGST + $tax_val12, 2);
                        $total_SGST = round($total_SGST + $tax_val12, 2);
                       
                    } else {
                        $printHtml .='<td align="center" width="7.14%">0.0</td>'
                            .'<td align="center" width="7.14%">0.0</td>'
                            .'<td align="center" width="7.14%">'. $tax_value12 .'</td>' 
                            .'</tr>'; 
                        
                        $total_IGST = round($total_IGST + $tax_val12, 2);
                    }
                    
                    $printHtml .= '<tr>'
                            .'<td align="center" width="7.14%">18%</td>'
                            .'<td align="center" width="7.14%">'. $sum_MrpValue18 .'</td>'
                            .'<td align="center" width="7.14%">'. $sum_DiscValue18 .'</td>'
                            .'<td align="center" width="7.14%">'. $sum_TaxValue18 .'</td>';
                    
                    $total_sum_MrpValue += $sum_MrpValue18;
                    $total_sum_DiscValue += $sum_DiscValue18;
                    $total_sum_TaxValue5 += $sum_TaxValue18;
                    
                    if(trim($stateObj->state_code) == "MH"){
                        $tax_val18 = round(($tax_value18/2), 2, PHP_ROUND_HALF_EVEN);
                        $printHtml .='<td align="center" width="7.14%">'. $tax_val18 .'</td>'
                            .'<td align="center" width="7.14%">'. $tax_val18 .'</td>'
                            .'<td align="center" width="7.14%">0.0</td>';
                        
                        $total_CGST = round($total_CGST + $tax_val18, 2);
                        $total_SGST = round($total_SGST + $tax_val18, 2);

                    } else {
                        $printHtml .='<td align="center" width="7.14%">0.0</td>'
                            .'<td align="center" width="7.14%">0.0</td>'
                            .'<td align="center" width="7.14%">'. $tax_value18 .'</td>'; 
                        
                        $total_IGST = round($total_IGST + $tax_val18, 2);
                    }
                    
                    $printHtml .='<th align="center" width="20%" rowspan="3" colspan="5">Value of each item includes, value of packing materials, Paper Carry bags & Gift Boxes.</th>'
                            .'</tr>';
                    
                    $printHtml .= '<tr>'
                            .'<td align="center" width="7.14%">28%</td>'
                            .'<td align="center" width="7.14%">'. $sum_MrpValue28 .'</td>'
                            .'<td align="center" width="7.14%">'. $sum_DiscValue28 .'</td>'
                            .'<td align="center" width="7.14%">'. $sum_TaxValue28 .'</td>';
                    
                    $total_sum_MrpValue += $sum_MrpValue28;
                    $total_sum_DiscValue += $sum_DiscValue28;
                    $total_sum_TaxValue5 += $sum_TaxValue28;
                    
                    if(trim($stateObj->state_code) == "MH"){
                        $tax_val28 = round(($tax_value28/2), 2, PHP_ROUND_HALF_EVEN);
                        $printHtml .='<td align="center" width="7.14%">'. $tax_val28 .'</td>'
                            .'<td align="center" width="7.14%">'. $tax_val28 .'</td>'
                            .'<td align="center" width="7.14%">0.0</td>' 
                            .'</tr>';
                        
                        $total_CGST = round($total_CGST + $tax_val28, 2);
                        $total_SGST = round($total_SGST + $tax_val28, 2);

                    } else {
                        $printHtml .='<td align="center" width="7.14%">0.0</td>'
                            .'<td align="center" width="7.14%">0.0</td>'
                            .'<td align="center" width="7.14%">'. $tax_value28 .'</td>' 
                            .'</tr>'; 
                        
                        $total_IGST = round($total_IGST + $tax_val28, 2);
                    }
                    
                    $printHtml .='</tr>';
                    $printHtml .= '<tr>'
                            .'<td align="center" width="7.14%">Total</td>'
                            .'<td align="center" width="7.14%">'.$total_sum_MrpValue.'</td>'
                            .'<td align="center" width="7.14%">'.$total_sum_DiscValue.'</td>'
                            .'<td align="center" width="7.14%">'.$total_sum_TaxValue5.'</td>'
                            .'<td align="center" width="7.14%">'.$total_CGST.'</td>'
                            .'<td align="center" width="7.14%">'.$total_SGST.'</td>'
                            .'<td align="center" width="7.14%">'.$total_IGST.'</td>' 
                            .'</tr>';

                    $printHtml .= '</tfoot>';
                    $printHtml .= '</table>';
                    $printHtml .= '</table>';
                    $printHtml .= '</table>';

                $printHtml .= '</body>'
                           .'</html>';
                return $printHtml;
    }
     
    function addPageFooter($pageno) {
        return '<page_footer>
                    <p align="center">Page ' . $pageno . '<br/>
                    </p>
                </page_footer>';
    }
   
   function breakText($text){
        $arr_text = array();
        $textToSend = "";
        if(strlen($text) > 15){
            $arr_text = str_split($text,15);
            for($i=0; $i<sizeof($arr_text); $i++){
                $textToSend = $textToSend . $arr_text[$i] . '<br/>';
            }
            return $textToSend;
        }else{
            return $text;
        }
    }    
}

try {
    extract($_GET);
//    print_r($_GET);
//    exit();
    $_SESSION['form_post'] = $_GET;
    $db = new DBConn();
    $str = getCurrUser();  //get Current users details
    $store_id = getCurrUserId();
    $proforma = "";
    $avail_cp = 0;
//    $html = null;

//    $firstquery="SELECT code.store_name, code.address, code.city, code.zipcode, st.state_code, code.phone, code.phone2, code.email, code.gstin_no, code.pancard_no, code.is_natch_required, code.discountset as dealermargin FROM it_ck_orders co "
//            . "JOIN it_codes code ON code.id=co.store_id JOIN states st ON st.id=code.state_id WHERE co.id=$orderid AND co.store_id=91";
    
    $iquery = "select id, ticketsnum_proforma_id as proforma_invno, proforma_time, order_no from it_ck_orders WHERE id = $orderid AND store_id = $store_id";
    $invObj = $db->fetchObject($iquery);
    
    $query="SELECT c.name as categoryname, c.it_hsncode as hsncode, coi.mrp, coi.order_qty, st.state_code FROM it_categories c "
            . "JOIN it_ck_designs cd ON cd.ctg_id=c.id JOIN it_ck_orderitems coi ON cd.design_no=coi.design_no "
            . "JOIN it_ck_orders co ON co.id=coi.order_id "
            . "JOIN it_codes code ON code.id=co.store_id JOIN states st ON"
            . " st.id=code.state_id WHERE coi.order_id = $orderid AND co.store_id = $store_id "; 
    $items = $db->fetchObjectArray($query);
    
    $squery = "select s.state_code from states s join it_codes c on c.state_id=s.id where c.id = $store_id ";      
    $stateObj = $db->fetchObject($squery);
    
    $cquery  = " select id,points_to_upload from it_store_redeem_points where is_completely_used = 0 and store_id = $store_id ";
    $cp = $db->fetchObject($cquery);
    
    
    if(isset($cp)){
    $total_cp = $cp->points_to_upload;
    $dquery = "select sum(points_used) as total_points_used from it_store_redeem_points_partial where it_store_redeem_points_id = $cp->id" ;
    $cpUsed = $db->fetchObject($dquery);
    $is_used = $cpUsed->total_points_used;
    
        if($is_used != null){
            $avail_cp = $total_cp - $is_used;
        } else {
            $avail_cp = $total_cp;
        }

        $pquery = "select sum(cp_used) as cp_used from proforma_invoice_detail where store_id = $store_id and it_store_redeem_points_id = $cp->id and inactive =0";
        $pobj  = $db->fetchObject($pquery);
        
        $used_pts = 0;
        
            if(isset($pobj) && $pobj->cp_used != null){
                $used_pts = $used_pts + $pobj->cp_used;
            }
            
            if($used_pts > 0){
                $avail_cp = $total_cp - $used_pts;
            }
    }
//    echo $avail_cp; exit();
    
    $html2fpdf = new HTML2PDF('P', 'A4', 'en');

        $pageno = 1;

                  $html = '<html>
                        <style type="text/css">            
                @page {
                    size: a4 landscape;
                    margin-top: 0.3cm;                    
                    margin-left: 0.5cm;
                    margin-right: 0.5cm;
                    margin-bottom: 0.3cm;                    
                }
                    td { 
                            padding-bottom: 0px;
                            padding-left: 2px;
                            padding-right: 2px;
                            padding-top:4px;
                            white-space:normal;
                        }
                  
                    th { padding-bottom: 0px;
                            padding-left: 2px;
                            padding-right: 2px;
                            padding-top:4px; }
                    
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
                    font-size:8px;
                }
                </style>
                        <body>';

        
        $showpdf=new showPDF();
        
        if(!empty($invObj) && !empty($items) && !empty($str) && !empty($stateObj)){
            
            if($invObj->proforma_invno != null){  
                $html.=$showpdf->addPageHeader($str, $invObj);
                $html.=$showpdf->addTableHeader();
                $html.=$showpdf->TableBody($items , $str, $stateObj, $avail_cp, $invObj, $cp);    
                $proforma = $invObj->proforma_invno;
            } else {
                $errors['status'] = "No Invoice No. Found. Please try again later";
//                echo count($errors); exit();
            }
            
        } else {
            $errors['status'] = "No Data Found. Please try again later";
        }
//        print $html;
//        return;

        $directory = "../proformapdf/";
        $filename = $proforma . '.html';
        $fp = fopen($directory . $filename, "w");
        fwrite($fp, $html);
        fclose($fp);
        $location = DEF_SITEURL . "proformapdf/" . $filename;
        $location = "../proformapdf/" . $filename;
        $cmd = "pisa -s" . " " . $location;
        $output = shell_exec($cmd);
   
} catch (Exception $xcp) {
    $clsLogger = new clsLogger();
    $clsLogger->logError("Failed:" . $xcp->getMessage());
    $errors['status'] = "There was a problem processing your request. Please try again later";
}
if (count($errors) > 0) {
    $_SESSION['form_errors'] = $errors;
    $redirect = "store/orders/active";
} else {
    unset($_SESSION['form_errors']);
//       $redirect = "proformapdf/testProformaInvice.pdf";
       $redirect = "store/orders/active";
}
//session_write_close();
header("Location: " . DEF_SITEURL . $redirect);
exit;
