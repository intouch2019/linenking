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

extract($_GET); //hi

$db = new DBConn();
$conv = new CurrencyConv();
$errors = array();
$success = array();

$query = "select * from it_creditnote_td where cn_no=$cn_no";
$obj = $db->fetchObject($query);
//print_r($obj);
$qt1date = yymmdd($obj->from_datetime);
$qt1 = $obj->qtr;
//$qt2date = yymmdd($to);
$parts = explode('-', $qt1date);
$yr = $parts[0];
if ($qt1 == 4) {

    $dated = "$yr-03-31";
    $financial_year = ($yr - 1) . '-' . $yr;
} else if ($qt1 == 1) {

    $dated = "$yr-06-30";
    $financial_year = $yr . '-' . ($yr + 1);
} else if ($qt1 == 2) {

    $dated = "$yr-09-30";
    $financial_year = $yr . '-' . ($yr + 1);
} else {

    $dated = "$yr-12-31";
    $financial_year = $yr . '-' . ($yr + 1);
}


$dated = ddMMyy($dated);
$dated = strtotime($dated);
$dated = date("Y-m-d H:i:s", $dated);
//   // $dated1 = new DateTime($dated);

$dated1 = new DateTime($obj->to_datetime);
$sr_no = $obj->cn_no;

$ref_inv = $obj->ref_no;
$ref_date = ddMMyy($obj->ref_date);
//$ref_date=date_create($ref_date);

$ref_date = strtotime($ref_date);
$ref_date = date("Y-m-d H:i:s", $ref_date);
$newdate = new DateTime($ref_date);
$squery = "select * from it_codes where id=$obj->store_id";
$order = $db->fetchObject($squery);


$new_setdate = ddMMyy($obj->createtime);
$new_setdate = strtotime($new_setdate);
$new_setdate = date("Y-m-d H:i:s", $new_setdate);
$new_setdate2 = new DateTime($new_setdate);



$html2fpdf = new HTML2PDF('P', 'A4', 'en');
$html2fpdf->pdf->SetDisplayMode('fullpage');
$html = "";




//$html = "<style type='text/css'>"
//        . "@page {"
//        . "size: a4;"
//        . "}"
//        . ".tabletop { border-top:1px solid #000000;}"
//        . ".tdborder { border:none;  }"
//        . "p {text-align:justify;}"
//        . "#test{border: 1px solid black;border-collapse: collapse;}"
//        . "#test2{border: 1px solid black;border-left:none;}"
//        . "</style>";


$html = '<style type="text/css">
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
             table {
                    border-collapse: collapse;
        }   
            td { padding: 10px;}
            th { padding: 5px; text-align:center; vertical-align:top; }
            tr { padding: 10px;}

</style>
';

$html.='<page>';

$html .= "<table width=\"100%\" align=\"center\">";
$html .= "<tr><th colspan=2 align=\"center\"><span style=\"font-size:18px; padding:12px 0 0px 0\">Fashionking Brands Pvt. Ltd. $financial_year</span><br/></th></tr>";
$html .= "<tr><td colspan=2 align=\"center\"><span style=\"font-size:18px; padding:12px 0 0px 0\">Baramati Textile Park,MIDC<br/></span></td>"
        . "</tr>";
$html .= "<tr><td colspan=2 align=\"center\"><span style=\"font-size:18px; padding:12px 0 0px 0\">Baramati, Pune-413133</span></td></tr>";
$html .= "<tr><td colspan=2 align=\"center\"><span style=\"font-size:18px; padding:12px 0 0px 0\">State Name : Maharashtra, Code : 27</span></td></tr>";
$html .= "<tr><td colspan=2 ><span style=\"font-size:18px; padding:12px 0 0px 0\">GSTIN: 27AAACC7418H1ZQ</span></td></tr>";
$html .= "<tr><th colspan=2 align=\"center\"><span style=\"font-size:18px; padding:12px 0 0px 0\">Credit Note</span></th></tr>";
$html .= "<tr><td><span style=\"font-size:18px; padding:12px 0 0px 0\">No.:CN$sr_no</span></td>"
        . "<td align=\"right\"><span style=\"font-size:18px; padding:12px 0 0px 0\">Dated
                     :" . date_format($dated1, 'jS F Y') . "</span></td>"
        . "</tr>";
$html .= "<tr><td colspan=2><span style=\"font-size:18px; padding:12px 0 0px 0\">Ref. : $ref_inv dt. " . date_format($newdate, 'jS F Y') . "</span></td>"
        . ""
        . "</tr>";
$html .= "<tr><td><span style=\"font-size:18px; padding:12px 0 0px 0\">Party’s Name : </span></td>"
        . "<td><span style=\"font-size:14px; padding:12px 0 0px 0\"><b>" . trim($order->tally_name) . "</b></span></td></tr>"; //$order->gstin_no
$html .= "<tr><td><span style=\"font-size:18px; padding:12px 0 0px 0\">Party’s <br/>Address:</span></td>"
        . "<td><span style=\"font-size:14px; padding:12px 0 0px 0\"><br/>" . trim($order->address) . "</span><br/></td></tr>";

$html .= "<tr><td><span style=\"font-size:18px; padding:12px 0 0px 0\">GSTIN: </span></td>"
        . "<td><span style=\"font-size:14px; padding:12px 0 0px 0\">" . trim($order->gstin_no) . "</span></td></tr>";
$html.="</table>";




$html .= "<table style='width:100%;' align=\"center\">";
$html .= "<tr><td align=\"center\" border=1><span style=\"font-size:18px; padding:12px 0 0px 0\">Particulars</span></td>"
        . "<td align=\"center\" border=1><span style=\"font-size:18px; padding:12px 0 0px 0\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Amount&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></td></tr>";



if ($obj->igst_paid != 0) {


    $html.="<tr><td align=\"center\" border=1><span style=\"font-size:14px; padding:12px 0 0px 0\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;GST Turnover Discount Net 12% &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></td>"
            . "<td align=\"center\" border=1><span style=\"font-size:14px; padding:12px 0 0px 0\">$obj->gst_net</span></td></tr>";


    $html.="<tr><td align=\"center\" border=1><span style=\"font-size:14px; padding:12px 0 0px 0\">IGST Turnover Discount Paid 12% &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></td>"
            . "<td align=\"center\" border=1><span style=\"font-size:14px; padding:12px 0 0px 0\">$obj->igst_paid</span></td></tr>";

    $html.="<tr><td align=\"center\" border=1><span style=\"font-size:14px; padding:12px 0 0px 0\">Total value&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></td>"
            . "<td align=\"center\" border=1><span style=\"font-size:14px; padding:12px 0 0px 0\"><b>$obj->gst_total</b></span></td></tr>";
} else {

    $html.="<tr><td align=\"center\" border=1><span style=\"font-size:14px; padding:12px 0 0px 0\">GST Turnover Discount Net 12%&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></td>"
            . "<td align=\"center\" border=1><span style=\"font-size:14px; padding:12px 0 0px 0\">$obj->gst_net</span></td></tr>";


    $html.="<tr><td align=\"center\" border=1><span style=\"font-size:14px; padding:12px 0 5px 0\">SGST Turnover Discount Paid 6%&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></td>"
            . "<td align=\"center\" border=1><span style=\"font-size:14px; padding:12px 0 5x 0\">" . round($obj->cgst_paid, 2) . "</span></td></tr>";

    $html.="<tr><td align=\"center\" border=1><span style=\"font-size:14px; padding:12px 0 0px 0\">CGST Turnover Discount Paid 6%&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></td>"
            . "<td align=\"center\" border=1><span style=\"font-size:14px; padding:12px 0 0px 0\">" . round($obj->cgst_paid, 2) . "</span></td></tr>";

    $html.="<tr><td align=\"center\" border=1><span style=\"font-size:14px; padding:12px 0 0px 0\">Total value&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></td>"
            . "<td align=\"center\" border=1><span style=\"font-size:14px; padding:12px 0 0px 0\"><b>$obj->gst_total</b></span></td></tr>";
}

//$html.="</table>";
/////////////////here
//$html .= "<table width=\"80%\" align=\"center\">";
$html .= "<tr><td><span style=\"font-size:14px; padding:12px 0 0px 0\"></span><br/></td></tr>"
        . "<tr><td colspan=2><span style=\"font-size:18px; padding:12px 0 0px 0\"><b>On Account of :</b></span><br/>"
        . "</td></tr>";

$html .= "<tr><td colspan=2><span style=\"font-size:18px; padding:12px 0 0px 0\">Being Turnover Discount Qtr-$obj->qtr F. Y.</span><br/>"
        . "<span style=\"font-size:14px; padding:12px 0 0px 0\">&nbsp;$financial_year</span><br/></td>"
        . "</tr>";


$html .= "<tr><td colspan=2><span style=\"font-size:18px; padding:12px 0 0px 0\"><b>Remark:</b></span><br/>"
        . "<span style=\"font-size:14px; padding:12px 0 0px 0\">&nbsp;$obj->remark</span><br/></td>"
        . "</tr>";

$html .= "<tr><td colspan=2><span style=\"font-size:18px; padding:12px 0 0px 0\"><b>Amount (in words) :</b></span><br/>"
        . "<span style=\"font-size:14px; padding:12px 0 0px 0\">&nbsp;" . $conv->getIndianCurrency($obj->gst_total) . "</span><br/></td>"
        . "</tr>";

$html .= "</table>";

/////////////////
//    $html .= "<table width=\"80%\" align=\"center\">";
//    $html .= "<tr><td><span style=\"font-size:14px; padding:12px 0 0px 0\"></span><br/></td></tr>" 
//            ."<tr><td colspan=2><span style=\"font-size:18px; padding:12px 0 0px 0\"><b>On Account of :</b></span><br/>"
//            . "</td></tr>";
//
//    $html .= "<tr><td colspan=2><span style=\"font-size:18px; padding:12px 0 0px 0\">Being Turnover Discount Qtr-$obj->qtr F. Y.</span><br/>"
//            . "<span style=\"font-size:14px; padding:12px 0 0px 0\">&nbsp;$financial_year</span><br/></td>"
//            . "</tr>";
//
//
//    $html .= "<tr><td colspan=2><span style=\"font-size:18px; padding:12px 0 0px 0\"><b>Remark:</b></span>"
//            . "<span style=\"font-size:14px; padding:12px 0 0px 0\">&nbsp;$obj->remark</span><br/></td>"
//            . "</tr>";
//
//    $html .= "<tr><td colspan=2><span style=\"font-size:18px; padding:12px 0 0px 0\"><b>Amount (in words) :</b></span><br/>"
//            . "<span style=\"font-size:14px; padding:12px 0 0px 0\">&nbsp;".$conv->getIndianCurrency($obj->gst_total)."</span><br/></td>"
//            . "</tr>";
//    
//    $html .= "</table>";




$html .= "<table width=\"80%\" align=\"right\">";

$html .= "<tr><td></td><td align=\"center\"><span style=\"font-size:16px; padding:12px 0 0px 0\"><b>For Fashionking Brands Pvt. Ltd.</b></span><br/>"
        . "<span align=\"center\"><img src='../images/koushik.jpg' width='150'/></span>"
        . "<br/><span align=\"left\">&nbsp;&nbsp;Authorised Signatory</span></td></tr>";
$html.="</table>";
$html.='</page>';

//echo $html;
$fname = "../creditnote/CN-$obj->cn_no.pdf";
$html2fpdf->writeHTML($html);
$html2fpdf->Output("../creditnote/CN-$obj->cn_no.pdf", "F");



header('Content-Type: application/pdf');
header("Content-Disposition: attachment; filename=\"" . basename($fname) . "\"");
echo file_get_contents($fname);
