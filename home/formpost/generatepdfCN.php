<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/conv/CurrencyConv.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once 'lib/users/clsUsers.php';


extract($_GET);
$db = new DBConn();
$conv = new CurrencyConv();
$errors = array();
$success = array();

$startdate = date('Y-m-d', strtotime($from));
$enddate = date('Y-m-d', strtotime($to));
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

//$qt1=
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

//$query = "select c.store_name,c.id as id,sum(o.amount) as amt,c.incentive_percent,c.state_id,c.remark from it_orders o,it_codes c where o.store_id=c.id and c.incentive_percent!=0 $dtClause group by c.store_name order by c.id desc";
$query ="select c.store_name,c.tally_name,c.address,c.gstin_no,c.id as id, sum(oi.price*(case when (o.tickettype in (0,1,6)) then (oi.quantity) else 0  end)) as amt,c.incentive_percent,c.state_id,c.remark from it_orders o,it_order_items oi, it_items i, it_codes c where c.incentive_percent!=0 and oi.order_id=o.id and i.id = oi.item_id and o.store_id = c.id $dtClause group by o.store_id";
//print "$query";
$orders = $db->fetchObjectArray($query);
$srno_query="select cn_no from creditnote_no;";
$srno_obj=$db->fetchObject($srno_query);

$sr_no =$srno_obj->cn_no;

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




    //$output .=  "<table width=\"95%\" border=\"1px\" align=\"center\">";
    $output .= "<table width=\"80%\" align=\"center\">";

    $output .= "<tr><th align='center' colspan=2><div style=\"font-size:18px; padding:12px 0 0px 0\">Fashionking Brands Pvt. Ltd. $financial_year</div></th></tr>";
    //Baramati Textile Park
    $output .= "<tr><td align='center' colspan=2><div style=\"font-size:18px; padding:12px 0 0px 0\">Baramati Textile Park,MIDC</div></td>"
            . "</tr>";
//    $output .= "<tr><td align='center' colspan=2><div style=\"font-size:18px; padding:12px 0 0px 0\">M.I.D.C,</div></td></tr>";
    $output .= "<tr><td align='center' colspan=2><div style=\"font-size:18px; padding:12px 0 0px 0\">Baramati, Pune-413133</div></td></tr>";
    $output .= "<tr><td align='center' colspan=2><div style=\"font-size:18px; padding:12px 0 0px 0\">State Name : Maharashtra, Code : 27</div></td></tr>";
    $output .= "<tr><td align='center' colspan=2><div style=\"font-size:18px; padding:12px 0 0px 0\">GSTIN: 27AAACC7418H1ZQ</div></td></tr>";
    
//    $output .= "<tr><td align='center' colspan=2><div style=\"font-size:18px; padding:12px 0 0px 0\">E-Mail : accounts@cottonking.in</div></td></tr>";
    $output .= "<tr><th align='center' colspan=2><div style=\"font-size:18px; padding:12px 0 0px 0\">Credit Note</div></th></tr>";
    $output .= "<tr><td width='60%'><div style=\"font-size:18px; padding:12px 0 0px 0\">No.:CN$sr_no</div></td>"
            . "<td align='right'><div style=\"font-size:18px; padding:12px 0 0px 0\">Dated
                     :" . date_format($dated1, 'jS F Y') . "</div></td>"
            . "</tr>";
    $output .= "<tr><td colspan=2><div style=\"font-size:18px; padding:12px 0 0px 0\">Ref. : $ref_inv dt. " . date_format($newdate, 'jS F Y') . "</div></td>"
            . ""
            . "</tr>";
    $output .= "<tr><td width='25%'><span style=\"font-size:18px; padding:12px 0 0px 0\">Party’s Name : </span></td>"
            . "<td><span style=\"font-size:14px; padding:12px 0 0px 0\"><b>" . trim($order->tally_name) . "</b></span></td></tr>";//$order->gstin_no
    $output .= "<tr><td><span style=\"font-size:18px; padding:12px 0 0px 0\">Party’s <br/>Address:</span></td>"
            . "<td><span style=\"font-size:14px; padding:12px 0 0px 0\"><br/>" . trim($order->address) . "</span><br/></td></tr>";
    
    $output .= "<tr><td><span style=\"font-size:18px; padding:12px 0 0px 0\">GSTIN: </span></td>"
            . "<td><span style=\"font-size:14px; padding:12px 0 0px 0\">" . trim($order->gstin_no) . "</span></td></tr>";


    $output.="</table><br/>";
    $output .= "<table id='test' width=\"80%\" align=\"center\">";
    $output .= "<tr><td id='test' align=\"center\" width='85%'><div style=\"font-size:18px; padding:12px 0 0px 0\">Particulars</div></td>"
            . "<td id='test2' align=\"center\"><div style=\"font-size:18px; padding:12px 0 0px 0\">Amount</div></td></tr>";
    //$otherstateflag=false;

    if ($otherstateflag == true) {

       
    $output.="<tr><td id='test' align=\"center\"><div style=\"font-size:14px; padding:12px 0 0px 0\">GST Turnover Discount Net 12%&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div></td>"
            . "<td id='test' align=\"center\"><div style=\"font-size:14px; padding:12px 0 0px 0\">$totaltdnet</div></td></tr>";


    $output.="<tr><td id='test' align=\"center\"><div style=\"font-size:14px; padding:12px 0 0px 0\">IGST Turnover Discount Paid 12%&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div></td>"
            . "<td id='test' align=\"center\"><div style=\"font-size:14px; padding:12px 0 0px 0\">$totaltdpaid</div></td></tr>";

    $output.="<tr><td id='test' align=\"center\"><div style=\"font-size:14px; padding:12px 0 0px 0\">Total value&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div></td>"
            . "<td id='test' align=\"center\"><div style=\"font-size:14px; padding:12px 0 0px 0\"><b>$totaltd</b></div></td></tr>";


        
        $db_instquery = "insert into it_creditnote_td(store_id,store_name,from_datetime,to_datetime,net_sale,igst_paid,cgst_paid,sgst_paid,gst_net,gst_total,incentive_percent,qtr,ref_no,ref_date,is_generated,cn_no,remark)
                                    values($order->id,'$order->store_name','$qt1date','$qt2date',$total,$totaltdpaid,0.0,0.0,$totaltdnet,$totaltd,$order->incentive_percent,$qt1,'$ref_inv','$ref_date',1,$sr_no,'$order->remark')";
    } else {

             $output.="<tr><td id='test' align=\"center\"><div style=\"font-size:14px; padding:12px 0 0px 0\">GST Turnover Discount Net 12%&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div></td>"
            . "<td id='test' align=\"center\"><div style=\"font-size:14px; padding:12px 0 0px 0\">$totaltdnet</div></td></tr>";


    $output.="<tr><td id='test' align=\"center\"><div style=\"font-size:14px; padding:12px 0 5px 0\">SGST Turnover Discount Paid 6%&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div></td>"
            . "<td id='test' align=\"center\"><div style=\"font-size:14px; padding:12px 0 5x 0\">".round(($totaltdpaid / 2), 2)."</div></td></tr>";

    $output.="<tr><td id='test' align=\"center\"><div style=\"font-size:14px; padding:12px 0 0px 0\">CGST Turnover Discount Paid 6%&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div></td>"
            . "<td id='test' align=\"center\"><div style=\"font-size:14px; padding:12px 0 0px 0\">".round(($totaltdpaid / 2), 2)."</div></td></tr>";

    $output.="<tr><td id='test' align=\"center\"><div style=\"font-size:14px; padding:12px 0 0px 0\">Total value&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div></td>"
            . "<td id='test' align=\"center\"><div style=\"font-size:14px; padding:12px 0 0px 0\"><b>$totaltd</b></div></td></tr>";

        $db_instquery = "insert into it_creditnote_td(store_id,store_name,from_datetime,to_datetime,net_sale,igst_paid,cgst_paid,sgst_paid,gst_net,gst_total,incentive_percent,qtr,ref_no,ref_date,is_generated,cn_no,remark)
                                    values($order->id,'$order->store_name','$qt1date','$qt2date',$total,0.0," . ($totaltdpaid / 2) . "," . ($totaltdpaid / 2) . ",$totaltdnet,$totaltd,$order->incentive_percent,$qt1,'$ref_inv','$ref_date',1,$sr_no,'$order->remark')";
    }

    $checkQuery="select is_generated from it_creditnote_td where qtr=$qt1 and store_id=$order->id";
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
   }
    else {
       
        $i = $db->execInsert($db_instquery);
     
 }


    $output .= "<table width=\"80%\" align=\"center\">";
    $output .= "<tr><td><span style=\"font-size:14px; padding:12px 0 0px 0\"></span><br/></td></tr>" 
            ."<tr><td colspan=2><span style=\"font-size:18px; padding:12px 0 0px 0\"><b>On Account of :</b></span><br/>"
//            . "<span style=\"font-size:14px; padding:12px 0 0px 0\"></span><br/></td>"
            . "</tr>";
 
    $output .= "<table width=\"80%\" align=\"center\">";
    $output .= "<tr><td colspan=2><span style=\"font-size:18px; padding:12px 0 0px 0\">Being Turnover Discount Qtr-$qt1 F. Y.</span><br/>"
            . "<span style=\"font-size:14px; padding:12px 0 0px 0\">$financial_year</span><br/></td>"
            . "</tr>";

    $output .= "<table width=\"80%\" align=\"center\">";
    $output .= "<tr><td colspan=2><span style=\"font-size:18px; padding:12px 0 0px 0\"><b>Remark:</b></span><br/>"
            . "<span style=\"font-size:14px; padding:12px 0 0px 0\">$order->remark</span><br/></td>"
            . "</tr>";

    $output .= "<table width=\"80%\" align=\"center\">";
    $output .= "<tr><td colspan=2><span style=\"font-size:18px; padding:12px 0 0px 0\"><b>Amount (in words) :</b></span><br/>"
            . "<span style=\"font-size:14px; padding:12px 0 0px 0\">$amtwords</span><br/></td>"
            . "</tr>";
    
    $output .= "<table width=\"80%\" align=\"center\">";


    $output .= "<table width=\"80%\" align=\"center\">";
   
    $output .= "<tr><td></td><td align=\"right\"><span style=\"font-size:16px; padding:12px 0 0px 0\"><b>For Fashionking Brands Pvt. Ltd.</b></span><br/>"
            . "<span align=\"center\"><img src='../images/koushik.jpg' width='150'/></span>"
            . "<br/><span align=\"left\">&nbsp;&nbsp;Authorised Signatory</span></td></tr>";
    $output.="</table><br/><br/>";
    $output .= "<pdf:nextpage>";




    //print "$db_instquery";
    //echo $output; 
$sr_no++;    
}
$srno_updatequery="update creditnote_no set cn_no=$sr_no";
//print "$srno_updatequery";
$z=$db->execUpdate($srno_updatequery);

$output.= "</body></html>";


//echo $output;
//$myFile = '/tmp/TestCN.html'; // or .php   

$myFile = '../images/TestCN1.html'; 
$fh = fopen($myFile, 'w'); // or die("error");  
$stringData = "your html code php code goes here";
fwrite($fh, $output);
fclose($fh);

$fname = "/tmp/TDCreditNote$qt1date" . "to" . $qt2date . ".pdf";
$location = "/tmp/TDCreditNote$qt1date" . "to" . $qt2date . ".pdf";

$cmd = "pisa -s $myFile " . $location;
$result = shell_exec($cmd);
header('Content-Type: application/pdf');
header("Content-Disposition: attachment; filename=\"" . basename($fname) . "\"");
echo file_get_contents($fname);