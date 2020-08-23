<?php

require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/conv/CurrencyConv.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once 'lib/users/clsUsers.php';
////editable
extract($_GET);
$db = new DBConn();
$conv = new CurrencyConv();
$errors = array();
$success = array();

$startdate = ddmmyy($from);
$enddate = ddmmyy($to);
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


if ($qt1 != $qt2) {
    $errors['nodata'] = "date range selected belongs to diff qtr,Kindly select date range from same qtr ";
}


if ($qt1 == 1) {
    $refdatequery = "and invoice_dt>='" . ($yr - 1) . "-11-01' and invoice_dt<='$yr-03-31'";
    $qt1=4;
    $dated="$yr-03-31";
} else if ($qt1 == 2) {
    $refdatequery = "and invoice_dt>='$yr-01-01' and invoice_dt<='$yr-06-30'";
    $qt1=1;
    $dated="$yr-06-30";
} else if ($qt1 == 3) {
    $refdatequery = "and invoice_dt>='$yr-03-01' and invoice_dt<='$yr-09-30'";
    $qt1=2;
    $dated="$yr-09-30";
} else {
    $refdatequery = "and invoice_dt>='$yr-06-01' and invoice_dt<='$yr-12-31'";
    $qt1=3;
    $dated="$yr-12-31";
}
//print "$refdatequery";
if (date('m') >= 3) {
//Upto June 2016-2017
    $financial_year = date('Y') . '-' . (date('Y')+1);
} else {//After June 2017-2018
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

        $dtClause = " and o.bill_datetime >= '$startdate 00:00:00' and o.bill_datetime <= '$enddate 23:59:59' ";
    } else {
        $dtClause = " and o.bill_datetime >= '$startdate 00:00:00'";
    }

//$dQuery = " and o.bill_datetime >= '$sdate 00:00:01' and o.bill_datetime <= '$edate 23:59:59'";
} else {
    $dtClause = "";
}

$query = "select c.store_name,c.id as id,c.ds_taxable_amt as amt,c.ds_remark,c.state_id,c.gstin_no from it_codes c where c.ds_taxable_amt!=0 group by c.store_name order by c.id desc;";
//print "$query";
$orders = $db->fetchObjectArray($query);
$sr_no = 0;
$output = "<!DOCTYPE html><html><head><title></title><meta charset=\"UTF-8\">"
        . "<style>"
        . "@page {"
        . "size: a4;"
        . "}"
        . ".tabletop { border-top:1px solid #000000;}"
        . ".tdborder { border:none;  }"
        . "p {text-align:justify;}"
        . "#test{border: 1px solid black;border-collapse: collapse;}"
        . "#test2{border: 1px solid black;border-left:none;}</style>"
        . "</head>"
        . "<body>";
foreach ($orders as $order) {
    $sr_no++;

    $otherstateflag = false;
    $stateid = $order->state_id;
    $total = $order->amt;
    $sgst_paid = 0.0;
    $cgst_paid = 0.0;
    $igst_paid = 0.0;

    if ($stateid == "") {
        $stateid = 22;
    }
    $statequery = "select * from states where id=$stateid";
    $sobj = $db->fetchObject($statequery);
    $state = $sobj->STATE;
    if ($state != "Maharashtra") {
        $otherstateflag = true;
        $igst_paid = round($total * 0.05, 2);
        $queryref = "select invoice_no,invoice_dt from it_invoices where store_id=$order->id and invoice_amt>$igst_paid $refdatequery limit 1";
    } else {
        $sgst_paid = round($total * 0.025, 2);
        $cgst_paid = round($total * 0.025, 2);
        $newtax = $sgst_paid + $cgst_paid;
        $queryref = "select invoice_no,invoice_dt from it_invoices where store_id=$order->id and invoice_amt>$newtax $refdatequery limit 1";
    }

    $totaltd = $total + $sgst_paid + $cgst_paid + $igst_paid;
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
    }

        $dated=ddMMyy($dated);
        $dated=strtotime($dated);
        $dated = date("Y-m-d H:i:s", $dated);
        $dated1 = new DateTime($dated);
    $amtwords = $conv->getIndianCurrency($totaltd);

          //var_dump($dated1);



    //$output .=  "<table width=\"95%\" border=\"1px\" align=\"center\">";
    $output .= "<table width=\"80%\" align=\"center\">";

    $output .= "<tr><th align='center' colspan=2><div style=\"font-size:18px; padding:12px 0 0px 0\">Fashionking Brands Pvt. Ltd. $financial_year</div></th></tr>";
    //Baramati Textile Park
    $output .= "<tr><td align='center' colspan=2><div style=\"font-size:18px; padding:12px 0 0px 0\">Baramati Textile Park</div></td>"
            . "</tr>";
    $output .= "<tr><td align='center' colspan=2><div style=\"font-size:18px; padding:12px 0 0px 0\">M.I.D.C,</div></td></tr>";
    $output .= "<tr><td align='center' colspan=2><div style=\"font-size:18px; padding:12px 0 0px 0\">Baramati, Pune</div></td></tr>";
    $output .= "<tr><td align='center' colspan=2><div style=\"font-size:18px; padding:12px 0 0px 0\">GSTIN/UIN: 27AAACC7418H1ZQ</div></td></tr>";
    $output .= "<tr><td align='center' colspan=2><div style=\"font-size:18px; padding:12px 0 0px 0\">State Name : Maharashtra, Code : 27</div></td></tr>";
    $output .= "<tr><td align='center' colspan=2><div style=\"font-size:18px; padding:12px 0 0px 0\">E-Mail : yogesh.shitole@kinglifestyle.com</div></td></tr>";
    $output .= "<tr><th align='center' colspan=2><div style=\"font-size:18px; padding:12px 0 0px 0\">Credit Note</div></th></tr>";
    $output .= "<tr><td width='60%'><div style=\"font-size:18px; padding:12px 0 0px 0\">No.:$sr_no</div></td>"
            . "<td align='right'><div style=\"font-size:18px; padding:12px 0 0px 0\">Dated
                     :" . date_format($dated1, 'jS F Y'). "</div></td>"
            . "</tr>";
    $output .= "<tr><td colspan=2><div style=\"font-size:18px; padding:12px 0 0px 0\">Ref. : $ref_inv dt. " . date_format($newdate, 'jS F Y') . "</div></td>"
            . ""
            . "</tr>";
    $output .= "<tr><td colspan=2><div style=\"font-size:18px; padding:12px 0 0px 0\">Party’s Name : <b>" . trim($order->store_name) . "</b></div></td></tr>";

    $output .= "<tr><td colspan=2><div style=\"font-size:18px; padding:12px 0 0px 0\">GSTIN/UIN :<b>" . trim($order->gstin_no) . "</b></div></td></tr>";


    $output.="</table><br/>";
    $output .= "<table id='test' width=\"80%\" align=\"center\">";
    $output .= "<tr><td id='test' align=\"center\" width='85%'><div style=\"font-size:18px; padding:12px 0 0px 0\"><b>Particulars</b></div></td>"
            . "<td id='test2' align=\"center\"><div style=\"font-size:18px; padding:12px 0 0px 0\"><b>Amount</b></div></td></tr>";
    //$otherstateflag=false;
    //$totaltd
    $output.="<tr><td id='test' align=\"center\"><span style=\"font-size:14px; padding:12px 0 0px 0\">Taxable Value&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></td>"
            . "<td id='test' align=\"center\"><span style=\"font-size:14px; padding:12px 0 0px 0\">$total</span></td></tr>";


    $output.="<tr><td id='test' align=\"center\"><span style=\"font-size:14px; padding:12px 0 0px 0\">SGST @ 6% Paid&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></td>"
            . "<td id='test' align=\"center\"><span style=\"font-size:14px; padding:12px 0 0px 0\">$sgst_paid</span></td></tr>";

    $output.="<tr><td id='test' align=\"center\"><span style=\"font-size:14px; padding:12px 0 0px 0\">CGST @ 6% Paid&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></td>"
            . "<td id='test' align=\"center\"><span style=\"font-size:14px; padding:12px 0 0px 0\">$cgst_paid</span></td></tr>";

    $output.="<tr><td id='test' align=\"center\"><span style=\"font-size:14px; padding:12px 0 0px 0\">IGST @ 12% Paid&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></td>"
            . "<td id='test' align=\"center\"><span style=\"font-size:14px; padding:12px 0 0px 0\">$igst_paid</span></td></tr>";

    $output.="<tr><td id='test' align=\"center\"><span style=\"font-size:14px; padding:12px 0 0px 0\">Total value&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></td>"
            . "<td id='test' align=\"center\"><span style=\"font-size:14px; padding:12px 0 0px 0\">$totaltd</span></td></tr>";

    $output .= "</table><br/><br/>";



//                           $db_instquery="insert into it_creditnote_ds(store_id,store_name,from_datetime,to_datetime,taxable_amt,igst_paid,cgst_paid,sgst_paid,qtr,ds_remark)
//                                    values($order->id,'$order->store_name','$qt1date','$qt2date',$total,$igst_paid,$cgst_paid,$igst_paid,$qt1,'$order->ds_remark')";
    $db_instquery = "insert into it_creditnote_ds(store_id,store_name,from_datetime,to_datetime,taxable_amt,igst_paid,cgst_paid,sgst_paid,qtr,ds_remark,ref_no,ref_date,is_generated)
                                    values($order->id,'$order->store_name','$qt1date','$qt2date',$total,$igst_paid,$cgst_paid,$igst_paid,$qt1,'$order->ds_remark','$ref_inv','$ref_date',1)";
//    
//
   $checkQuery="select is_generated from it_creditnote_ds where qtr=$qt1 and store_id=$order->id";
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
        header("Location: ".DEF_SITEURL."discscheme");
        exit;
 }
   }
    else {
       
        $i = $db->execInsert($db_instquery);
     
 }



  


    $output .= "<table width=\"80%\" align=\"center\">";
    $output .= "<tr><td colspan=2><span style=\"font-size:18px; padding:12px 0 0px 0\"><b>Remark:</b></span><br/>"
            . "<span style=\"font-size:14px; padding:12px 0 0px 0\">Discount Scheme:$order->ds_remark</span><br/></td>"
            . "</tr>";

    $output .= "<table width=\"80%\" align=\"center\">";
    $output .= "<tr><td colspan=2><span style=\"font-size:18px; padding:12px 0 0px 0\"><b>Amount (in words) :</b></span><br/>"
            . "<span style=\"font-size:14px; padding:12px 0 0px 0\">$amtwords</span><br/></td>"
            . "</tr>";

    $output .= "<table width=\"80%\" align=\"center\">"
//                     $output .=  "<tr><td colspan=2><span style=\"font-size:18px; padding:12px 0 0px 0\">Company’s PAN
//                     : <b>AAACC7418H</b></span></td>"
//                             . "</tr>"
            . "<tr><td></td><td align=\"right\"><span style=\"font-size:16px; padding:12px 0 0px 0\"><b>For Fashionking Brands Pvt. Ltd. $financial_year</b></span><br/>"
            . "<span align=\"center\"><img src='../images/koushik.jpg' width='150'/></span>"
            . "<br/><span align=\"left\">&nbsp;&nbsp;Authorised Signatory</span></td></tr>";
    $output.="</table><br/><br/>";
    $output .= "<pdf:nextpage>";






    //echo $output;
}
  
// echo $output;
$output.= "</body></html>";

//echo $output;


////$i=$db->execInsert($db_instquery);

//$myFile = '/tmp/TestCN1.html'; // or .php  

$myFile = '../images/TestCN1.html'; 
$fh = fopen($myFile, 'w'); // or die("error"); 
$stringData = "your html code php code goes here";
fwrite($fh, $output);
fclose($fh);

$fname = "/tmp/DSCreditNote$qt1date" . "to" . $qt2date . ".pdf";
$location = "/tmp/DSCreditNote$qt1date" . "to" . $qt2date . ".pdf";


$cmd = "pisa -s $myFile " . $location;
$result = shell_exec($cmd);

header('Content-Type: application/pdf');
header("Content-Disposition: attachment; filename=\"" . basename($fname) . "\"");
echo file_get_contents($fname);
