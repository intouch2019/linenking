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

$startdate = date('Y-m-d', strtotime($from));
$enddate = date('Y-m-d', strtotime($to));
$checkstart=date('Y-m-d H:i:s', strtotime($from));
$checkend=date('Y-m-d H:i:s', strtotime($to));


//$invtyp=$invoicetype;
$qt1date = yymmdd($from);
$qt2date = yymmdd($to);
$parts = explode('-', $qt1date);
$yr = $parts[0];
$qtrquery = "SELECT QUARTER('$qt1date') as qt1";
$qtr2query = "SELECT QUARTER('$qt1date') as qt2";
$qt1obj = $db->fetchObject($qtrquery);
$qt2obj = $db->fetchObject($qtr2query);
$qt1 = $qt1obj->qt1;
$qt2 = $qt2obj->qt2;

$refdatequery = "";
if ($qt1 != $qt2) {
    $errors['nodata'] = "date range selected belongs to diff qtr,Kindly select date range from same qtr ";
}


if ($qt1 == 1) {
    $refdatequery = "and invoice_dt>='" . ($yr - 1) . "-11-01' and invoice_dt<='$yr-03-31'";
    $qt1=4;
    $dated="$yr-03-31";
    $enddate="31-03-$yr";
} else if ($qt1 == 2) {
    $refdatequery = "and invoice_dt>='$yr-01-01' and invoice_dt<='$yr-06-30'";
    $qt1=1;
    $dated="$yr-06-30";
    $enddate="30-06-$yr";
} else if ($qt1 == 3) {
    $refdatequery = "and invoice_dt>='$yr-03-01' and invoice_dt<='$yr-09-30'";
    $qt1=2;
    $dated="$yr-09-30";
    $enddate="30-09-$yr"; 
} else {
    $refdatequery = "and invoice_dt>='$yr-06-01' and invoice_dt<='$yr-12-31'";
    $qt1=3;
    $dated="$yr-12-31";
    $enddate="31-12-$yr";
}

if (date('m') >= 3) {

    $financial_year = date('Y') . '-' . (date('Y')+1);
} else {
    $financial_year = (date('Y')-1) . '-' . date('Y');
}
$dtClause = "";

$effectiveDate = date('d-m-Y', strtotime("+3 months", strtotime($startdate)));




if (isset($startdate) && trim($startdate) != "") {

    if (isset($enddate) && trim($enddate) != "") {
        $date1 = date_create($effectiveDate);
        $date2 = date_create($enddate);
        $diff = date_diff($date2, $date1);
        $dd = $diff->format("%R%a");

        $dtClause = " and o.bill_datetime >= '$startdate 00:00:00' and o.bill_datetime <= '$dated 23:59:59' ";
    } else {
        $dtClause = " and o.bill_datetime >= '$startdate 00:00:00'";
    }

//$dQuery = " and o.bill_datetime >= '$sdate 00:00:01' and o.bill_datetime <= '$edate 23:59:59'";
} else {
    $dtClause = "";
}


$query ="select c.store_name,c.tally_name,c.address,c.gstin_no,c.id as id, sum(case when (o.discount_pct is not NULL) then ((((100-o.discount_pct)/100)*oi.price) * (case when (o.tickettype in (0,1,6)) then (oi.quantity) else 0 end )) else oi.price*(case when (o.tickettype in (0,1,6)) then (oi.quantity) else 0 end ) end) as amt,c.incentive_percent,c.state_id,c.remark from it_orders o,it_order_items oi, it_items i, it_codes c where c.incentive_percent!=0 and oi.order_id=o.id and i.id = oi.item_id and o.store_id = c.id $dtClause group by o.store_id";
//print "$query";
$orders = $db->fetchObjectArray($query);
$srno_query="select cn_no from creditnote_no;";
$srno_obj=$db->fetchObject($srno_query);

$sr_no =$srno_obj->cn_no;

foreach ($orders as $order) {
    

    $otherstateflag = false;
    $stateid = $order->state_id;
    $total = $order->amt;
    $totaltd = round((($total * $order->incentive_percent) / 100), 2);
    $totaltdnet = round($totaltd / 1.12, 2);
    $totaltdpaid = round($totaltd - $totaltdnet, 2);
    $queryref = "select invoice_no,invoice_dt from it_invoices where store_id=$order->id and invoice_amt>$totaltd $refdatequery limit 1";
    $refinvobj = $db->fetchObject($queryref);
    if ($refinvobj == null) {
        $ref_inv = "Ref invoice not available";
        $ref_date = "";
    } else {
        $ref_inv = $refinvobj->invoice_no;
        $ref_date = ddMMyy($refinvobj->invoice_dt);
        //$ref_date=date_create($ref_date);

        $ref_date = strtotime($ref_date);
        $ref_date = date("Y-m-d H:i:s", $ref_date);
        $newdate = new DateTime($ref_date);
        //$dated=
    }
        $dated=ddMMyy($dated);
        $dated=strtotime($dated);
        $dated = date("Y-m-d H:i:s", $dated);
        $dated1 = new DateTime($dated);
    

    $amtwords = $conv->getIndianCurrency($totaltd);
    if ($stateid == "") {
        $stateid = 22;
    }
    $statequery = "select * from states where id=$stateid";
    $sobj = $db->fetchObject($statequery);
    $state = $sobj->STATE;
    if ($state != "Maharashtra") {
        $otherstateflag = true;
    }



    if ($otherstateflag == true) {
        
        $db_instquery = "insert into it_creditnote_td(store_id,store_name,from_datetime,to_datetime,net_sale,igst_paid,cgst_paid,sgst_paid,gst_net,gst_total,incentive_percent,qtr,ref_no,ref_date,is_generated,cn_no,remark)
                                    values($order->id,'$order->store_name','$qt1date','$qt2date',$total,$totaltdpaid,0.0,0.0,$totaltdnet,$totaltd,$order->incentive_percent,$qt1,'$ref_inv','$ref_date',1,$sr_no,'$order->remark')";
    } else {

             $db_instquery = "insert into it_creditnote_td(store_id,store_name,from_datetime,to_datetime,net_sale,igst_paid,cgst_paid,sgst_paid,gst_net,gst_total,incentive_percent,qtr,ref_no,ref_date,is_generated,cn_no,remark)
                                    values($order->id,'$order->store_name','$qt1date','$qt2date',$total,0.0," . ($totaltdpaid / 2) . "," . ($totaltdpaid / 2) . ",$totaltdnet,$totaltd,$order->incentive_percent,$qt1,'$ref_inv','$ref_date',1,$sr_no,'$order->remark')";
    }

    $checkQuery="select is_generated from it_creditnote_td where qtr=$qt1 and store_id=$order->id  and from_datetime='$qt1date' and to_datetime='$qt2date'";
   //print "$checkQuery";
    $chkobj=$db->fetchObject($checkQuery);
    //print_r($chkobj);
   if(isset($chkobj))
   {
       $errors['chk']="Credit Note is already generated for a Qt:$qt1";
        if (count($errors)>0) {
        unset($_SESSION['form_success']);
        unset($_SESSION['fpath']);
        unset($_SESSION['storeseq']);
        $_SESSION['form_errors'] = $errors;
        session_write_close();
        header("Location: ".DEF_SITEURL."turnoverdisc");
        exit;
        }
   }else {
       
        $i = $db->execInsert($db_instquery);
     
 }

$sr_no++;    
}
$srno_updatequery="update creditnote_no set cn_no=$sr_no";
$z=$db->execUpdate($srno_updatequery);

$records = $sr_no . "<>1";
        $db = new DBConn();
        //$url = "http://192.168.0.130/ck_new_y/home/sendCN/sendCNnumber.php";
       //   $url = "http://192.168.0.38/ck_new_y/home/sendCN/sendCNnumber.php";
       
          
        $url = "http://cottonking.intouchrewards.com/sendCN/sendCNnumber.php";
          
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

$cnquery="select * from it_creditnote_td where from_datetime='$checkstart' and  to_datetime='$checkend'";

//$cnquery="select * from it_creditnote_td where qtr=$qt1";
//print "$cnquery";
//return;
$cnobjs=$db->fetchObjectArray($cnquery);



$html= '<style type="text/css">
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

foreach ($cnobjs as $obj) {
    
$qt1date = yymmdd($obj->from_datetime);
$qt1=$obj->qtr;
//$qt2date = yymmdd($to);
$parts = explode('-', $qt1date);
$yr = $parts[0];
if ($qt1 == 4) {
   
    $dated="$yr-03-31";
    $financial_year = ($yr-1) . '-' . $yr;

} else if ($qt1 == 1) {
    
    $dated="$yr-06-30";
    $financial_year = $yr . '-' . ($yr+1);
   
} else if ($qt1 == 2) {
 
    $dated="$yr-09-30";
    $financial_year = $yr . '-' . ($yr+1);
} else {
   
    $dated="$yr-12-31";
    $financial_year = $yr . '-' . ($yr+1);
}


$dated=ddMMyy($dated);
        $dated=strtotime($dated);
        $dated = date("Y-m-d H:i:s", $dated);
        $dated1 = new DateTime($dated);
        $sr_no=$obj->cn_no;
        
        $ref_inv = $obj->ref_no;
        $ref_date = ddMMyy($obj->ref_date);
        //$ref_date=date_create($ref_date);

        $ref_date = strtotime($ref_date);
        $ref_date = date("Y-m-d H:i:s", $ref_date);
        $newdate = new DateTime($ref_date);
$squery="select * from it_codes where id=$obj->store_id";
$order=$db->fetchObject($squery);


$html2fpdf = new HTML2PDF('P', 'A4', 'en');
$html2fpdf->pdf->SetDisplayMode('fullpage');
//$html="";
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
            . "<td><span style=\"font-size:14px; padding:12px 0 0px 0\"><b>" . trim($order->tally_name) . "</b></span></td></tr>";//$order->gstin_no
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
            . "<td align=\"center\" border=1><span style=\"font-size:14px; padding:12px 0 5x 0\">".round($obj->cgst_paid, 2)."</span></td></tr>";

    $html.="<tr><td align=\"center\" border=1><span style=\"font-size:14px; padding:12px 0 0px 0\">CGST Turnover Discount Paid 6%&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></td>"
            . "<td align=\"center\" border=1><span style=\"font-size:14px; padding:12px 0 0px 0\">".round($obj->cgst_paid, 2)."</span></td></tr>";

    $html.="<tr><td align=\"center\" border=1><span style=\"font-size:14px; padding:12px 0 0px 0\">Total value&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></td>"
            . "<td align=\"center\" border=1><span style=\"font-size:14px; padding:12px 0 0px 0\"><b>$obj->gst_total</b></span></td></tr>";

    }
    
    //$html.="</table>";
 /////////////////here
 
    //$html .= "<table width=\"80%\" align=\"center\">";
    $html .= "<tr><td><span style=\"font-size:14px; padding:12px 0 0px 0\"></span><br/></td></tr>" 
            ."<tr><td colspan=2><span style=\"font-size:18px; padding:12px 0 0px 0\"><b>On Account of :</b></span><br/>"
            . "</td></tr>";

    $html .= "<tr><td colspan=2><span style=\"font-size:18px; padding:12px 0 0px 0\">Being Turnover Discount Qtr-$obj->qtr F. Y.</span><br/>"
            . "<span style=\"font-size:14px; padding:12px 0 0px 0\">&nbsp;$financial_year</span><br/></td>"
            . "</tr>";


    $html .= "<tr><td colspan=2><span style=\"font-size:18px; padding:12px 0 0px 0\"><b>Remark:</b></span><br/>"
            . "<span style=\"font-size:14px; padding:12px 0 0px 0\">&nbsp;$obj->remark</span><br/></td>"
            . "</tr>";

    $html .= "<tr><td colspan=2><span style=\"font-size:18px; padding:12px 0 0px 0\"><b>Amount (in words) :</b></span><br/>"
            . "<span style=\"font-size:14px; padding:12px 0 0px 0\">&nbsp;".$conv->getIndianCurrency($obj->gst_total)."</span><br/></td>"
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

    
}


$fname="../creditnote/TDCN".$startdate."to".$dated.".pdf";
$html2fpdf->writeHTML($html);
$html2fpdf->Output("../creditnote/TDCN".$startdate."to".$dated.".pdf", "F");
 


header('Content-Type: application/pdf');
header("Content-Disposition: attachment; filename=\"" . basename($fname) . "\"");
echo file_get_contents($fname);