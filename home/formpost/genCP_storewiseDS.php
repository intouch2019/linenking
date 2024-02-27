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

$query="select * from cp_calculations where Store_ID=$storeid and id=$id";
$obj=$db->fetchObject($query);
//print_r($obj);exit();
if (!$obj) {
    $errors['nodata'] = "Invalid Store Id";
}

$id= $obj->Store_ID;

// Create Instance of Html2pdf
$html2fpdf = new HTML2PDF('P', 'A4', 'en');

$html1= '<style type="text/css">
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
           td { padding: 5px;border-collapse: collapse;width: 500px;height: 10px;}
           th { padding: 5px; text-align:center;border-collapse: collapse;width: 100px;height: 70px;}
           tr { padding: 5px; border-collapse: collapse;width: 200px;height: 10px;}
</style>';

$html2= '<style type="text/css">
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
           td { padding: 5px;border-collapse: collapse;width: 500px;height: 10px;}
           th { padding: 5px; text-align:center;border-collapse: collapse;width: 100px;height: 70px;}
           tr { padding: 5px; border-collapse: collapse;width: 200px;height: 10px;}
</style>';

$html3= '<style type="text/css">
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
           td { padding: 5px;border-collapse: collapse;width: 500px;height: 10px;}
           th { padding: 5px; text-align:center;border-collapse: collapse;width: 100px;height: 70px;}
           tr { padding: 5px; border-collapse: collapse;width: 200px;height: 10px;}
</style>';

// Generate the first HTML content section
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
  
  $reimbursement_p12s12 = $actualPricePurchase_p12s12_int - $priceByCK_p12s12_int;
  $reimbursement_p12s12_int = round((float)$reimbursement_p12s12);

$html1 .='<page>';

    
$html1 .= "<table style=\"margin-top: 50px;\" width=\"70%\" align=\"center\" border=\"1\">";

    $html1 .= "<tr><th align='center' colspan=3><span style=\"font-size:14px; padding:14px 0 0px 0\">"
            . "<b>" . trim($obj->Row_Labels) . "</b></span></th></tr>";
    $html1 .= "<tr><td align='center' colspan=3><span style=\"font-size:14px; padding:14px 0 0px 0\">"
            . "<b>" . trim($obj->Credit_Point_Heading) . "</b></span></td></tr>";
    $html1 .= "<tr><td align='center' colspan=3><span style=\"font-size:14px; padding:14px 0 0px 0\">"
            . "<b>Calculation Working for Purchase @12% and Sales @12%</b></span></td></tr>";
    
    //column headers
    $html1 .= "<tr><td width=50% align='center'><b>Sr No.</b></td>"
            . "<td align='center'><b>Details</b></td>"
            . "<td width=50% align='center'><b>Amount</b></td></tr>";
    
    //fields
    $html1 .= "<tr><td width=50% align='center'>1</td>"
            . "<td>Original MRP sale during sale period.</td>"
            . "<td width=50% align='center'>" . round((float)trim($obj->MRP_Sale_p12_s12)) . "</td></tr>";
    
    $html1 .= "<tr><td width=50% align='center'>2</td>"
            . "<td>Sales of garment without discount.</td>"
            . "<td width=50% align='center'>$saleWoDiscount_p12s12</td></tr>";
   
    $html1 .= "<tr><td width=50% align='center'>3</td>"
            . "<td>Original MRP of Garments sold under discount scheme.</td>"
            . "<td width=50% align='center'>" . round($soldunserdiscschem_p12s12_int) . "</td></tr>";
   
    $html1 .= "<tr><td width=50% align='center'>4</td>"
            . "<td>Discount passed on to customers as per your report.</td>"
            . "<td width=50% align='center'>" . round((float)trim($obj->Discount_p12_s12)) . "</td></tr>";
    
    $html1 .= "<tr><td width=50% align='center'>5</td>"
            . "<td>Actual sale of dealer ( or revised MRP sale ) after discount.( 3 - 4 )</td>"
            . "<td width=50% align='center'>".round($actualSale_p12s12_int)."</td></tr>";
    
    $html1 .= "<tr><td width=50% align='center'>6</td>"
            . "<td>Dealer discount applicable on actual sale.( 5 x 20% )</td>"
            . "<td width=50% align='center'>".round($dealerDiscountAC_p12s12_int)."</td></tr>";
    
    $html1 .= "<tr><td width=50% align='center'>7</td>"
            . "<td>This should be the price at which dealer should buy from C.K. ( 5 - 6 = 7 )</td>"
            . "<td width=50% align='center'>".round($priceByCK_p12s12_int)."</td></tr>";

    $html1 .= "<tr><td width=50% align='center'>8</td>"
            . "<td>Actual price at which dealer has made his purchases. ( 3 MRP - Dealer discount )</td>"
            . "<td width=50% align='center'>".round($actualPricePurchase_p12s12_int)."</td></tr>";
 
    $html1 .= "<tr><td width=50% align='center'>9</td>"
            . "<td><b>Reimbursement of difference.  ( 7 - 8 ) ( Inclusive of Tax Amt )</b></td>"
            . "<td width=50% align='center'>".round($reimbursement_p12s12_int)."</td></tr>";
    
    //blank space
    $html1 .= "<tr><th width=70% align='center' colspan=3></th></tr>";   
    
    //fields
    $html1 .= "<tr><td width=50%></td>"
            . "<td><b>Regular Dealer Margin</b></td>"
            . "<td width=50% align='center'><b>" . trim($obj->Dealer_Margin * 100) . "%</b></td></tr>";
    
    $html1 .= "<tr><td width=50%></td>"
            . "<td><b>Margin During Sale Period</b></td>"
            . "<td width=50% align='center'><b>" . trim($obj->Scheme_Discount * 100) . "%</b></td></tr>";
 
  $html1 .="</table>";
  
  $html1 .= "<div style=\"text-align: right; margin-top: 50px; margin-right: 60px;\">"
        . "<img src=\"../images/FK_sealstamp_min.jpg\" alt=\"Fk Seal Stamp\"height=\"100\" width=\"100\">"
        . "</div>";
    
 $html1 .='</page>';
 
 
// Generate the second HTML content section
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
  
  $diffofTaxcredit_p12s5 = $originalMrp_p12s5 * 0.07;
  $diffofTaxcredit_p12s5_int = round((float)$diffofTaxcredit_p12s5);
  
  $reimbursement_p12s5 = $actualPricePurchase_p12s5_int - $priceByCK_p12s5_int - $diffofTaxcredit_p12s5_int;
  $reimbursement_p12s5_int = round((float)$reimbursement_p12s5);
 
 $html2 .='<page>';

    
$html2 .= "<table style=\"margin-top: 50px;\" width=\"70%\" align=\"center\" border=\"1\">";

    $html2 .= "<tr><th align='center' colspan=3><span style=\"font-size:14px; padding:14px 0 0px 0\">"
            . "<b>" . trim($obj->Row_Labels) . "</b></span></th></tr>";
    $html2 .= "<tr><td align='center' colspan=3><span style=\"font-size:14px; padding:14px 0 0px 0\">"
            . "<b>" . trim($obj->Credit_Point_Heading) . "</b></span></td></tr>";
    $html2 .= "<tr><td align='center' colspan=3><span style=\"font-size:14px; padding:14px 0 0px 0\">"
            . "<b>Calculation Working for Purchase @12% and Sales @5%</b></span></td></tr>";
    
    //column headers
    $html2 .= "<tr><td width=50% align='center'><b>Sr No.</b></td>"
            . "<td align='center'><b>Details</b></td>"
            . "<td width=50% align='center'><b>Amount</b></td></tr>";
    
    //fields
    $html2 .= "<tr><td width=50% align='center'>1</td>"
            . "<td>Original MRP sale during sale period.</td>"
            . "<td width=50% align='center'>" . round((float)trim($obj->MRP_Sale_p12_s5)) . "</td></tr>";
    
    $html2 .= "<tr><td width=50% align='center'>2</td>"
            . "<td>Sales of garment without discount.</td>"
            . "<td width=50% align='center'>$saleWoDiscount_p12s5</td></tr>";
   
    $html2 .= "<tr><td width=50% align='center'>3</td>"
            . "<td>Original MRP of Garments sold under discount scheme.</td>"
            . "<td width=50% align='center'>$soldunserdiscschem_p12s5_int</td></tr>";
   
    $html2 .= "<tr><td width=50% align='center'>4</td>"
            . "<td>Discount passed on to customers as per your report.</td>"
            . "<td width=50% align='center'>" . round((float)trim($obj->Discount_p12_s5)) . "</td></tr>";
    
    $html2 .= "<tr><td width=50% align='center'>5</td>"
            . "<td>Actual sale of dealer ( or revised MRP sale ) after discount.( 3 - 4 )</td>"
            . "<td width=50% align='center'>$actualSale_p12s5_int</td></tr>";
    
    $html2 .= "<tr><td width=50% align='center'>6</td>"
            . "<td>Dealer discount applicable on actual sale.( 5 x 20% )</td>"
            . "<td width=50% align='center'>$dealerDiscountAC_p12s5_int</td></tr>";
    
    $html2 .= "<tr><td width=50% align='center'>7</td>"
            . "<td>This should be the price at which dealer should buy from C.K. ( 5 - 6 = 7 )</td>"
            . "<td width=50% align='center'>$priceByCK_p12s5_int</td></tr>";

    $html2 .= "<tr><td width=50% align='center'>8</td>"
            . "<td>Actual price at which dealer has made his purchases. ( 3 MRP - Dealer discount )</td>"
            . "<td width=50% align='center'>$actualPricePurchase_p12s5_int</td></tr>";
    
    $html2 .= "<tr><td width=50% align='center'>9</td>"
            . "<td>Difference of Tax Credit in Bill & Actual Tax Paid = 7%</td>"
            . "<td width=50% align='center'>$diffofTaxcredit_p12s5_int</td></tr>";
 
    $html2 .= "<tr><td width=50% align='center'>10</td>"
            . "<td><b>Reimbursement of difference.  ( 7 - 8 - 9) ( Inclusive of Tax Amt )</b></td>"
            . "<td width=50% align='center'>$reimbursement_p12s5_int</td></tr>";
    
    //blank space
    $html2 .= "<tr><th width=70% align='center' colspan=3></th></tr>";   
    
    //fields
    $html2 .= "<tr><td width=50%></td>"
            . "<td><b>Regular Dealer Margin</b></td>"
            . "<td width=50% align='center'><b>" . trim($obj->Dealer_Margin * 100) . "%</b></td></tr>";
    
    $html2 .= "<tr><td width=50%></td>"
            . "<td><b>Margin During Sale Period</b></td>"
            . "<td width=50% align='center'><b>" . trim($obj->Scheme_Discount * 100) . "%</b></td></tr>";
 
  $html2 .="</table>";
  
  $html2 .= "<div style=\"text-align: right; margin-top: 50px; margin-right: 60px;\">"
        . "<img src=\"../images/FK_sealstamp_min.jpg\" alt=\"Fk Seal Stamp\"height=\"100\" width=\"100\">"
        . "</div>";
    
 $html2 .='</page>';
 
 
 // Generate the third HTML content section
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
  
  $reimbursement_p5s5 = $actualPricePurchase_p5s5_int - $priceByCK_p5s5_int;
  $reimbursement_p5s5_int = round((float)$reimbursement_p5s5);
  

$html3 .='<page>';

    
$html3 .= "<table style=\"margin-top: 50px;\" width=\"70%\" align=\"center\" border=\"1\">";

    $html3 .= "<tr><th align='center' colspan=3><span style=\"font-size:14px; padding:14px 0 0px 0\">"
            . "<b>" . trim($obj->Row_Labels) . "</b></span></th></tr>";
    $html3 .= "<tr><td align='center' colspan=3><span style=\"font-size:14px; padding:14px 0 0px 0\">"
            . "<b>" . trim($obj->Credit_Point_Heading) . "</b></span></td></tr>";
    $html3 .= "<tr><td align='center' colspan=3><span style=\"font-size:14px; padding:14px 0 0px 0\">"
            . "<b>Calculation Working for Purchase @5% and Sales @5%</b></span></td></tr>";
    
    //column headers
    $html3 .= "<tr><td width=50% align='center'><b>Sr No.</b></td>"
            . "<td align='center'><b>Details</b></td>"
            . "<td width=50% align='center'><b>Amount</b></td></tr>";
    
    //fields
    $html3 .= "<tr><td width=50% align='center'>1</td>"
            . "<td>Original MRP sale during sale period.</td>"
            . "<td width=50% align='center'>" . round((float)trim($obj->MRP_Sale_p5_s5)) . "</td></tr>";
    
    $html3 .= "<tr><td width=50% align='center'>2</td>"
            . "<td>Sales of garment without discount.</td>"
            . "<td width=50% align='center'>$saleWoDiscount_p5s5</td></tr>";
   
    $html3 .= "<tr><td width=50% align='center'>3</td>"
            . "<td>Original MRP of Garments sold under discount scheme.</td>"
            . "<td width=50% align='center'>$soldunserdiscschem_p5s5_int</td></tr>";
   
    $html3 .= "<tr><td width=50% align='center'>4</td>"
            . "<td>Discount passed on to customers as per your report.</td>"
            . "<td width=50% align='center'>" . round((float)trim($obj->Discount_p5_s5)) . "</td></tr>";
    
    $html3 .= "<tr><td width=50% align='center'>5</td>"
            . "<td>Actual sale of dealer ( or revised MRP sale ) after discount.( 3 - 4 )</td>"
            . "<td width=50% align='center'>$actualSale_p5s5_int</td></tr>";
    
    $html3 .= "<tr><td width=50% align='center'>6</td>"
            . "<td>Dealer discount applicable on actual sale.( 5 x 20% )</td>"
            . "<td width=50% align='center'>$dealerDiscountAC_p5s5_int</td></tr>";
    
    $html3 .= "<tr><td width=50% align='center'>7</td>"
            . "<td>This should be the price at which dealer should buy from C.K. ( 5 - 6 = 7 )</td>"
            . "<td width=50% align='center'>$priceByCK_p5s5_int</td></tr>";

    $html3 .= "<tr><td width=50% align='center'>8</td>"
            . "<td>Actual price at which dealer has made his purchases. ( 3 MRP - Dealer discount )</td>"
            . "<td width=50% align='center'>$actualPricePurchase_p5s5_int</td></tr>";
 
    $html3 .= "<tr><td width=50% align='center'>9</td>"
            . "<td><b>Reimbursement of difference.  ( 7 - 8 ) ( Inclusive of Tax Amt )</b></td>"
            . "<td width=50% align='center'>$reimbursement_p5s5_int</td></tr>";
    
    //blank space
    $html3 .= "<tr><th width=70% align='center' colspan=3></th></tr>";   
    
    //fields
    $html3 .= "<tr><td width=50%></td>"
            . "<td><b>Regular Dealer Margin</b></td>"
            . "<td width=50% align='center'><b>" . trim($obj->Dealer_Margin * 100) . "%</b></td></tr>";
    
    $html3 .= "<tr><td width=50%></td>"
            . "<td><b>Margin During Sale Period</b></td>"
            . "<td width=50% align='center'><b>" . trim($obj->Scheme_Discount * 100) . "%</b></td></tr>";
 
  $html3 .="</table>";
  
  $html3 .= "<div style=\"text-align: right; margin-top: 50px; margin-right: 60px;\">"
        . "<img src=\"../images/FK_sealstamp_min.jpg\" alt=\"Fk Seal Stamp\"height=\"100\" width=\"100\">"
        . "</div>";
    
 $html3 .='</page>';
 

// echo $html1;


$combinedHtml = $html1 . $html2 . $html3;

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