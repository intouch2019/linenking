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
require_once('Classes/FPDF/fpdf.php'); 

        
extract($_GET);
//print_r($id);exit();
$db = new DBConn();
$conv = new CurrencyConv();
$errors = array();
$success = array();

$query = "select * from cp_calculations where Store_ID=$storeid and id=$id";
$obj = $db->fetchObject($query);
//print_r($obj);exit();
if (!$obj) {
    $errors['nodata'] = "Invalid Store Id";
}

$id = $obj->Store_ID;

// Create Instance of Html2pdf
$html2fpdf = new HTML2PDF('L', 'A4', 'en');

$html1 = '<style type="text/css">
                @page {
                  margin: 0.1cm;
                  margin-bottom: 2.5cm;
                  @frame footer {
                    -pdf-frame-content: footerContent;
                    bottom: 2cm;
                    margin-left: 0.0cm;
                    margin-right: 0.0cm;
                    height: 10cm;
                  }
                }
                
#test2{border: 1px solid black;border-left:none;border-right:none;}
             table {
               border-collapse: collapse; width: 800px;
        }   
           td { padding: 4px;border-collapse: collapse;width: 500px;height: 10px;}
           th { padding: 5px; text-align:center;border-collapse: collapse;width: 100px;height: 70px;}
           tr { padding: 4px; border-collapse: collapse;width: 200px;height: 10px;}
</style>';

// Generate the third HTML content section P5-S5 Calculations
  if ($obj->Sale_Without_Discount_p5_s5 == 0){
       $saleWoDiscount_p5s5 = "0";
   } else {
       $saleWoDiscount_p5s5 = $obj->Sale_Without_Discount_p5_s5;
   }
  $soldunserdiscschem_p5s5 = trim($obj->MRP_Sale_p5_s5)-$saleWoDiscount_p5s5;
  $soldunserdiscschem_p5s5_int = round((float)$soldunserdiscschem_p5s5);
  
  $actualSale_p5s5 = $soldunserdiscschem_p5s5_int - round($obj->Discount_p5_s5);
  $actualSale_p5s5_int = round((float)$actualSale_p5s5);
  
  $dealerDiscountAC_p5s5 = $actualSale_p5s5_int * trim($obj->Scheme_Discount);
  $dealerDiscountAC_p5s5_int = round((float)$dealerDiscountAC_p5s5);
  
  $priceByCK_p5s5= $actualSale_p5s5_int - $dealerDiscountAC_p5s5_int;
  $priceByCK_p5s5_int = round((float)$priceByCK_p5s5);
  
  $originalMrpUnderDiscSchm_p5s5 = $soldunserdiscschem_p5s5_int;
  
  $dealerDiscount_p5s5= $originalMrpUnderDiscSchm_p5s5 * trim($obj->Dealer_Margin);
  $dealerDiscount_p5s5_int = round((float)$dealerDiscount_p5s5);
  
  $actualPricePurchase_p5s5 =  $originalMrpUnderDiscSchm_p5s5 - $dealerDiscount_p5s5_int;
  $actualPricePurchase_p5s5_int = round((float)$actualPricePurchase_p5s5);
  
  $mrp_sale_P5_S5 = trim($obj->MRP_Sale_p5_s5);
  $mrp_sale_GST_P5_S5 = round((float)($mrp_sale_P5_S5 / 1.05) * 0.05);
  $mrp_disc_sale_GST_P5_S5 = round((float)($actualSale_p5s5_int / 1.05) * 0.05);
  $GST_diff_P5_S5 = $mrp_sale_GST_P5_S5 - $mrp_disc_sale_GST_P5_S5;
  
  $reimbursement_p5s5 = $actualPricePurchase_p5s5_int - $priceByCK_p5s5_int - $GST_diff_P5_S5;
  $reimbursement_p5s5_int = round((float)$reimbursement_p5s5);


// Generate the second HTML content section P12-S5 Calculations
  if ($obj->Sale_Without_Discount_p12_s5 == 0){
       $saleWoDiscount_p12s5 = "0";
   } else {
       $saleWoDiscount_p12s5 = $obj->Sale_Without_Discount_p12_s5;
   }
  $soldunserdiscschem_p12s5 = trim($obj->MRP_Sale_p12_s5)-$saleWoDiscount_p12s5;
  $soldunserdiscschem_p12s5_int = round((float)$soldunserdiscschem_p12s5);
  
  $actualSale_p12s5 = $soldunserdiscschem_p12s5 - round($obj->Discount_p12_s5);
  $actualSale_p12s5_int = round((float)$actualSale_p12s5);
  
  $dealerDiscountAC_p12s5 = $actualSale_p12s5_int * trim($obj->Scheme_Discount);
  $dealerDiscountAC_p12s5_int = round((float)$dealerDiscountAC_p12s5);
  
  $priceByCK_p12s5= $actualSale_p12s5_int - $dealerDiscountAC_p12s5_int;
  $priceByCK_p12s5_int = round((float)$priceByCK_p12s5);
  
  $originalMrpUnderDiscSchm_p12s5 = $soldunserdiscschem_p12s5_int;
  $originalMrp_p12s5 = round($obj->MRP_Sale_p12_s5);
  
  $dealerDiscount_p12s5= $originalMrpUnderDiscSchm_p12s5 * trim($obj->Dealer_Margin);
  $dealerDiscount_p12s5_int = round((float)$dealerDiscount_p12s5);
  
  $actualPricePurchase_p12s5 =  $originalMrpUnderDiscSchm_p12s5 - $dealerDiscount_p12s5_int;
  $actualPricePurchase_p12s5_int = round((float)$actualPricePurchase_p12s5);
  
//  $diffofTaxcredit_p12s5 = $originalMrp_p12s5 * 0.07;
//  $diffofTaxcredit_p12s5_int = round((float)$diffofTaxcredit_p12s5);
  
  $mrp_sale_P12_S5 = trim($obj->MRP_Sale_p12_s5);
  $mrp_sale_GST_P12_S5 = round((float)($mrp_sale_P12_S5 / 1.12) * 0.12);
  $mrp_disc_sale_GST_P12_S5 = round((float)($actualSale_p12s5_int / 1.05) * 0.05);
  $GST_diff_P12_S5 = $mrp_sale_GST_P12_S5 - $mrp_disc_sale_GST_P12_S5;

  
  $reimbursement_p12s5 = $actualPricePurchase_p12s5_int - $priceByCK_p12s5_int - $GST_diff_P12_S5;
  $reimbursement_p12s5_int = round((float)$reimbursement_p12s5);


// Generate the first HTML content section P12-S12 Calculations
  if ($obj->Sale_Without_Discount_p12_s12 == 0){
       $saleWoDiscount_p12s12 = "0";
   }
   else{
       $saleWoDiscount_p12s12 = trim($obj->Sale_Without_Discount_p12_s12);
   }
  $soldunserdiscschem_p12s12 = trim($obj->MRP_Sale_p12_s12)-$saleWoDiscount_p12s12;
  $soldunserdiscschem_p12s12_int = round((float)$soldunserdiscschem_p12s12);
  
  $actualSale_p12s12 = $soldunserdiscschem_p12s12_int - round($obj->Discount_p12_s12);
  $actualSale_p12s12_int = round((float)$actualSale_p12s12);
  
  $dealerDiscountAC_p12s12 = $actualSale_p12s12_int * trim($obj->Scheme_Discount);
  $dealerDiscountAC_p12s12_int = round((float)$dealerDiscountAC_p12s12);
  
  $priceByCK_p12s12= $actualSale_p12s12_int - $dealerDiscountAC_p12s12_int;
  $priceByCK_p12s12_int = round((float)$priceByCK_p12s12);
  
  $originalMrpUnderDiscSchm_p12s12 = $soldunserdiscschem_p12s12_int;
  
  $dealerDiscount_p12s12= $originalMrpUnderDiscSchm_p12s12 * trim($obj->Dealer_Margin);
  $dealerDiscount_p12s12_int = round((float)$dealerDiscount_p12s12);
  
  $actualPricePurchase_p12s12 =  $originalMrpUnderDiscSchm_p12s12 - $dealerDiscount_p12s12_int;
  $actualPricePurchase_p12s12_int = round((float)$actualPricePurchase_p12s12);
  
  $mrp_sale_P12_S12 = trim($obj->MRP_Sale_p12_s12);
  $mrp_sale_GST_P12_S12 = round((float)($mrp_sale_P12_S12 / 1.12) * 0.12);
  $mrp_disc_sale_GST_P12_S12 = round((float)($actualSale_p12s12_int / 1.12) * 0.12);
  $GST_diff_P12_S12 = $mrp_sale_GST_P12_S12 - $mrp_disc_sale_GST_P12_S12;
  
  $reimbursement_p12s12 = $actualPricePurchase_p12s12_int - $priceByCK_p12s12_int - $GST_diff_P12_S12;
  $reimbursement_p12s12_int = round((float)$reimbursement_p12s12);
  
  // Total Reimburstment
$totalReimburstment = ($reimbursement_p5s5_int + $reimbursement_p12s5_int + $reimbursement_p12s12_int);

$html1 .='<page>';

    
$html1 .= "<table style=\"margin-top: 50px;\" width=\"70%\" align=\"center\" border=\"1\">";

    $html1  .= "<tr><td align='center' colspan=6><span style=\"font-size:14px; padding:14px 0 0px 0\">"
            . "<b>" . trim($obj->Row_Labels) . "</b><br>"
            . "<b>" . trim($obj->Credit_Point_Heading) . "</b><br>"
            . "<b>Calculations & Working for Credit Points</b></span></td></tr>";
    
    //column headers
    $html1 .= "<tr>"
            . "<td width=50% align='center'><b>Sr No.</b></td>"
            . "<td align='center'><b>Details</b></td>"
            . "<td width=50% align='center'><b>Amount for P5-S5</b></td>"
            . "<td width=50% align='center'><b>Amount for P12-S5</b></td>"
            . "<td width=50% align='center'><b>Amount for P12-S12</b></td>"
            . "<td width=50% align='center'><b>TOTAL</b></td>"
            . "</tr>";
    
    //fields
    $html1 .= "<tr>"
            . "<td width=50% align='center'>1</td>"
            . "<td>Original MRP sale during sale period.</td>"
            . "<td width=50% align='center'>" . round((float)trim($obj->MRP_Sale_p5_s5)) . "</td>"
            . "<td width=50% align='center'>" . round((float)trim($obj->MRP_Sale_p12_s5)) . "</td>"
            . "<td width=50% align='center'>" . round((float)trim($obj->MRP_Sale_p12_s12)) . "</td>"
            . "<td width=50% align='center'></td>"
            . "</tr>";
    
    $html1 .= "<tr>"
            . "<td width=50% align='center'>2</td>"
            . "<td>Sales of garment without discount.</td>"
            . "<td width=50% align='center'>$saleWoDiscount_p5s5</td>"
            . "<td width=50% align='center'>$saleWoDiscount_p12s5</td>"
            . "<td width=50% align='center'>$saleWoDiscount_p12s12</td>"
            . "<td width=50% align='center'>$obj->non_scheme_sale</td>"
            . "</tr>";
   
    $html1 .= "<tr>"
            . "<td width=50% align='center'>3</td>"
            . "<td>Original MRP of Garments sold under discount scheme.</td>"
            . "<td width=50% align='center'>$soldunserdiscschem_p5s5_int</td>"
            . "<td width=50% align='center'>$soldunserdiscschem_p12s5_int</td>"
            . "<td width=50% align='center'>" . round($soldunserdiscschem_p12s12_int) . "</td>"
            . "<td width=50% align='center'></td>"
            . "</tr>";
   
    $html1 .= "<tr>"
            . "<td width=50% align='center'>4</td>"
            . "<td>Discount passed on to customers as per your report.</td>"
            . "<td width=50% align='center'>" . round((float) trim($obj->Discount_p5_s5)) . "</td>"
            . "<td width=50% align='center'>" . round((float) trim($obj->Discount_p12_s5)) . "</td>"
            . "<td width=50% align='center'>" . round((float) trim($obj->Discount_p12_s12)) . "</td>"
            . "<td width=50% align='center'></td>"
            . "</tr>";
    
    $html1 .= "<tr>"
            . "<td width=50% align='center'>5</td>"
            . "<td>Actual sale of dealer ( or revised MRP sale ) after discount.( 3 - 4 )</td>"
            . "<td width=50% align='center'>$actualSale_p5s5_int</td>"
            . "<td width=50% align='center'>$actualSale_p12s5_int</td>"
            . "<td width=50% align='center'>" . round($actualSale_p12s12_int) . "</td>"
            . "<td width=50% align='center'></td>"
            . "</tr>";
    
    $html1 .= "<tr>"
            . "<td width=50% align='center'>6</td>"
            . "<td>Dealer discount applicable on actual sale.( 5 x 20% )</td>"
            . "<td width=50% align='center'>$dealerDiscountAC_p5s5_int</td>"
            . "<td width=50% align='center'>$dealerDiscountAC_p12s5_int</td>"
            . "<td width=50% align='center'>" . round($dealerDiscountAC_p12s12_int) . "</td>"
            . "<td width=50% align='center'></td>"
            . "</tr>";
    
    $html1 .= "<tr>"
            . "<td width=50% align='center'>7</td>"
            . "<td>This should be the price at which dealer should buy from C.K. ( 5 - 6 = 7 )</td>"
            . "<td width=50% align='center'>$priceByCK_p5s5_int</td>"
            . "<td width=50% align='center'>$priceByCK_p12s5_int</td>"
            . "<td width=50% align='center'>" . round($priceByCK_p12s12_int) . "</td>"
            . "<td width=50% align='center'></td>"
            . "</tr>";

    $html1 .= "<tr>"
            . "<td width=50% align='center'>8</td>"
            . "<td>Actual price at which dealer has made his purchases. ( 3 MRP - Dealer discount )</td>"
            . "<td width=50% align='center'>$actualPricePurchase_p5s5_int</td>"
            . "<td width=50% align='center'>$actualPricePurchase_p12s5_int</td>"
            . "<td width=50% align='center'>" . round($actualPricePurchase_p12s12_int) . "</td>"
            . "<td width=50% align='center'></td>"
            . "</tr>";
    
    $html1 .= "<tr>"
            . "<td width=50% align='center'>9</td>"
            . "<td>GST difference between MRP Sale & Disc Sale (GST on MRP - GST on discounted price)</td>"
            . "<td width=50% align='center'>".round($GST_diff_P5_S5)."</td>"
            . "<td width=50% align='center'>".round($GST_diff_P12_S5)."</td>"
            . "<td width=50% align='center'>".round($GST_diff_P12_S12)."</td>"
            . "<td width=50% align='center'></td>"
            . "</tr>";
 
    $html1 .= "<tr>"
            . "<td width=50% align='center'>10</td>"
            . "<td><b>Reimbursement of difference.  ( 7 - 8 - 9 ) ( Inclusive of Tax Amt )</b></td>"
            . "<td width=50% align='center'><b>$reimbursement_p5s5_int</b></td>"
            . "<td width=50% align='center'><b>$reimbursement_p12s5_int</b></td>"
            . "<td width=50% align='center'><b>" . round($reimbursement_p12s12_int) . "</b></td>"
            . "<td width=50% align='center'><b>$totalReimburstment</b></td>"
            . "</tr>";
    
    $html1 .= "</table>";
    
    //blank space 
    $html1 .= "<br><br><br>";
    
    
   //New Table
$html1 .= "<div style='display: flex; align-items: center; margin-left: 30px; width: 100%;'>"
        . "<table style='flex: 1;' width='50%' border='1'>"
        . "<tr>"
        . "<td><b>Regular Dealer Margin</b></td>"
        . "<td width='50%' align='center'><b>" . trim($obj->Dealer_Margin * 100) . "%</b></td>"
        . "</tr>"
        . "<tr>"
        . "<td><b>Margin During Sale Period</b></td>"
        . "<td width='50%' align='center'><b>" . trim($obj->Scheme_Discount * 100) . "%</b></td>"
        . "</tr>"
        . "<tr>"
        . "<td>GST for Selling Price upto 1050</td>"
        . "<td width='50%' align='center'>5%</td>"
        . "</tr>"
        . "<tr>"
        . "<td>GST for Selling Price above 1051</td>"
        . "<td width='50%' align='center'>12%</td>"
        . "</tr>"
        . "<span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>"
        . "<span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>"
        . "<span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>"
        . "<span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>"
        . "<img src='../images/FK_sealstamp_min.jpg' alt='Fk Seal Stamp' height='100' width='100'>"
        . "</table>"
        . "</div>";
    
 $html1 .='</page>';

// echo $html1;

$combinedHtml = $html1;

// Generate the PDF using the combined HTML content
$html2fpdf->writeHTML($combinedHtml);

// Output the PDF to the browser
$html2fpdf->Output();

if (count($errors) > 0) {
    $_SESSION['form_errors'] = $errors;
    $redirect = "report/discountschemecalculations/";
}
session_write_close();
header("Location: " . DEF_SITEURL . $redirect);
exit;