<?php

//require_once("../../it_config.php");
//require_once("session_check.php");
//require_once "lib/db/DBConn.php";
//require_once "lib/conv/CurrencyConv.php";
//require_once "lib/core/Constants.php";
//require_once "lib/core/strutil.php";
//require_once 'lib/users/clsUsers.php';


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

$taxrate =$taxpct/100;

//$enddate = ddmmyy($to);

$until = mktime(0, 0, 0, date('n'), date('d'), date('Y'));
$from = mktime(0, 0, 0, date('n', $until)-5, 1, date('Y', $until));

$startdt=yymmdd(date('F j, Y', $from));
$enddt=yymmdd(date('F j, Y', $until));
//echo $startdt.'from'.$enddt;

$qtrquery = "SELECT QUARTER('$enddt') as qt1";
$qt1obj = $db->fetchObject($qtrquery);
$qt1 = $qt1obj->qt1;
$qt1=$qt1-1;
    $refdatequery = "and invoice_dt>='$startdt' and invoice_dt<='$enddt'";


if (date('m') >= 3) {

    $financial_year = date('Y') . '-' . (date('Y')+1);
} else {
    $financial_year = (date('Y')-1) . '-' . date('Y');
}


$query = "select c.store_name,c.id as id,c.ds_taxable_amt as amt,c.ds_remark,c.state_id,c.gstin_no,c.tally_name from it_codes c where c.ds_taxable_amt!=0 group by c.store_name order by c.id desc;";
//print "$query";
$orders = $db->fetchObjectArray($query);
//print_r($orders);
$srno_query="select cn_no from creditnote_no;";
$srno_obj=$db->fetchObject($srno_query);

$sr_no =$srno_obj->cn_no;

foreach ($orders as $order) {
    

    $otherstateflag = false;
    $stateid = $order->state_id;
    $total = round((($order->amt)/(1+$taxrate)),2);
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
        $igst_paid = round($total * $taxrate, 2);
        $totaltd = $total + $igst_paid;
        $queryref = "select invoice_no,invoice_dt from it_invoices where store_id=$order->id and invoice_amt>$totaltd $refdatequery and  invoice_no not in(select ref_no from it_creditnote_ds) limit 1";
    } else {
        $sgst_paid = round($total * ($taxrate/2), 2);
        $cgst_paid = round($total * ($taxrate/2), 2);
        $newtax = $sgst_paid + $cgst_paid;
        $totaltd = $total + $newtax;
        $queryref = "select invoice_no,invoice_dt from it_invoices where store_id=$order->id and invoice_amt>$totaltd $refdatequery and  invoice_no not in(select ref_no from it_creditnote_ds) limit 1";
    }

    //$totaltd = $total + $sgst_paid + $cgst_paid + $igst_paid;
    //print "$queryref";
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
    
    ////////hsncode////////
    $strhsn="-";
    $invoice_id_query="select id from it_invoices where invoice_no=$ref_inv";
    print "$invoice_id_query<br/>";
    $invobj=$db->fetchObject($invoice_id_query);
                            if(isset($invobj)){
                            
                                //print "HSNcode";
                            $hsnquery="select item_code from it_invoice_items where invoice_id=$invobj->id order by id desc limit 1";
                            $hsnobj=$db->fetchObjectArray($hsnquery);
                            //print_r($hsnobj);
                            if(isset($hsnobj)){
                            
                            $strhsn="(";
                            $cnt=1;
                            foreach($hsnobj as $hsnbb)
                            {
                                if($cnt==1)
                                {
                                    $strhsn.="$hsnbb->item_code";
                                }
                                else {
                                       $strhsn.=",$hsnbb->item_code";
                                 }
                                $cnt=0;
                            }
                            $strhsn.=")";
                            //print "$strhsn";
                            }
                            $finalQuery="select c.it_hsncode from it_items i left outer join it_categories c on i.ctg_id = c.id 
                                         where i.barcode in$strhsn group by c.it_hsncode";
                            //print "$finalQuery";
                            $hsnobj=$db->fetchObjectArray($finalQuery);
                            //print_r($hsnobj);
                            if(isset($hsnobj) && $hsnobj!=null){
                            
                                $strhsn="";
                                    $cnt=1;
                                    foreach($hsnobj as $hsnbb)
                                        {
                                            if($cnt==1)
                                                {
                                                    $strhsn.="$hsnbb->it_hsncode";
                                                }
                                                else {
                                                        $strhsn.=",$hsnbb->it_hsncode";
                                                    }
                                            $cnt=0;
                                        }
                                            $strhsn.="";
                            }
                            else
                            {
                                $strhsn="-";
                            }
                            
                            print "str:$strhsn<br/>";
                     }
                     
                     
    $amtwords = $conv->getIndianCurrency($totaltd);

    $db_instquery = "insert into it_creditnote_ds(store_id,store_name,from_datetime,to_datetime,taxable_amt,igst_paid,cgst_paid,sgst_paid,qtr,ds_remark,ref_no,ref_date,is_generated,tally_name,cn_no,taxpct,hsncode)
                                    values($order->id,'$order->store_name','$startdt','$enddt',$total,$igst_paid,$cgst_paid,$sgst_paid,$qt1,'$order->ds_remark','$ref_inv','$ref_date',0,'$order->tally_name',$sr_no,$taxpct,'$strhsn')";

    //print "$db_instquery";
   $checkQuery="select is_generated from it_creditnote_ds where qtr=$qt1 and store_id=$order->id and taxpct=$taxpct";
   $chkobj=$db->fetchObject($checkQuery);


   
 $i = $db->execInsert($db_instquery);
$sr_no++;   
}


$srno_updatequery="update creditnote_no set cn_no=$sr_no";
$z=$db->execUpdate($srno_updatequery);

$records = $sr_no . "<>1";
        $db = new DBConn();
        //$url = "http://192.168.0.118/limelight_new/home/sendCN/sendCNnumber.php";
         //  $url = "http://192.168.0.31/ck_new_y/home/sendCN/sendCNnumber.php";
        //$url="http://linenking.intouchrewards.com/home/sendCN/sendCNnumber.php";
        
         
        
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

///////////////////htmlto pdf code generation////////////////////

$cnquery="select * from it_creditnote_ds where is_generated=0";
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
                
#test2{border: 1px solid black;border-left:none;border-right:none;}
             table {
               border-collapse: collapse; width: 700px;    
        }   
            td { padding: 5px;border-collapse: collapse;width: 300px;}
            th { padding: 5px; text-align:center;border-collapse: collapse;width: 200px;}
            tr { padding: 5px; border-collapse: collapse;width: 200px;}

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
//$enddate=$dated;
$dated=yymmdd($obj->createtime);
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
$address=trim($order->address);
$addlen=strlen($address);
$newadd="";


$html2fpdf = new HTML2PDF('P', 'A4', 'en');
//$html2fpdf->pdf->SetDisplayMode('fullpage');
//$html="";
$html.='<page>';

    
$html .= "<table width=\"80%\" align=\"center\">";

    $html .= "<tr><th align='center' colspan=2><span style=\"font-size:18px; padding:12px 0 0px 0\">Fashionking Brands Pvt. Ltd. $financial_year</span></th></tr>";
    //Baramati Textile Park
    $html .= "<tr><td align='center' colspan=2><span style=\"font-size:18px; padding:12px 0 0px 0\">Baramati Textile Park,MIDC</span></td>"
            . "</tr>";
    $html .= "<tr><td align='center' colspan=2><span style=\"font-size:18px; padding:12px 0 0px 0\">Baramati, Pune-413133</span></td></tr>";
    
    $html .= "<tr><td align='center' colspan=2><span style=\"font-size:18px; padding:12px 0 0px 0\">State Name : Maharashtra, Code : 27</span></td></tr>";
    
    $html .= "<tr><td align='center' colspan=2><span style=\"font-size:18px; padding:12px 0 0px 0\">GSTIN: 27AAACC7418H1ZQ</span></td></tr>";
    
    $html .= "<tr><th align='center' colspan=2><span style=\"font-size:18px; padding:12px 0 0px 0\">Credit Note</span></th></tr>";
    $html .= "<tr><td width='60%'><span style=\"font-size:18px; padding:12px 0 0px 0\">No.:CN$sr_no</span></td>"
            . "<td align='right'><span style=\"font-size:18px; padding:12px 0 0px 0\">Dated
                     :" . date_format($dated1, 'jS F Y'). "</span></td>"
            . "</tr>";
    $html .= "<tr><td colspan=2><span style=\"font-size:18px; padding:12px 0 0px 0\">Ref. : $ref_inv dt. " . date_format($newdate, 'jS F Y') . "</span></td>"
            . ""
            . "</tr>";
    $html .= "<tr><td colspan=2><span style=\"font-size:18px; padding:12px 0 0px 0\">Party’s Name : <b>" . trim($order->tally_name) . "</b></span></td></tr>";
    $html .= "<tr><td colspan=2><span style=\"font-size:18px; padding:12px 0 0px 0\">Party’s Address :" . $address. "</span></td></tr>";   

    $html .= "<tr><td colspan=2><span style=\"font-size:18px; padding:12px 0 0px 0\">GSTIN:<b>" . trim($order->gstin_no) . "</b></span></td><br/></tr>";


    //$html.="</table>";
    //$html .= "<table width=\"80%\" align=\"center\" border=1>";
    $html .= "<tr><td border=1 id=test2 align=\"center\" width='85%'><span style=\"font-size:18px; padding:12px 0 0px 0\"></span></td>"
            . "<td border=1 id=test2 align=\"center\"><span style=\"font-size:18px; padding:12px 0 0px 0\"></span></td></tr>";
    
    
    $html .= "<tr><td border=1 align=\"center\" width='85%'><span style=\"font-size:18px; padding:12px 0 0px 0\"><b>Particulars</b></span></td>"
            . "<td border=1 align=\"center\"><span style=\"font-size:18px; padding:12px 0 0px 0\"><b>Amount</b></span></td></tr>";

    $html.="<tr><td border=1 align=\"center\"><span style=\"font-size:14px; padding:12px 0 0px 0\">Taxable Value&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></td>"
            . "<td border=1 align=\"center\"><span style=\"font-size:14px; padding:12px 0 0px 0\">".$obj->taxable_amt."</span></td></tr>";


    $html.="<tr><td border=1 align=\"center\"><span style=\"font-size:14px; padding:12px 0 0px 0\">SGST @ ".($obj->taxpct/2)."% Paid&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></td>"
            . "<td border=1 align=\"center\"><span style=\"font-size:14px; padding:12px 0 0px 0\">$obj->sgst_paid</span></td></tr>";

    $html.="<tr><td border=1 align=\"center\"><span style=\"font-size:14px; padding:12px 0 0px 0\">CGST @ ".($obj->taxpct/2)."% Paid&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></td>"
            . "<td border=1 align=\"center\"><span style=\"font-size:14px; padding:12px 0 0px 0\">$obj->cgst_paid</span></td></tr>";

    $html.="<tr><td border=1 align=\"center\"><span style=\"font-size:14px; padding:12px 0 0px 0\">IGST @ $obj->taxpct% Paid&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></td>"
            . "<td border=1 align=\"center\"><span style=\"font-size:14px; padding:12px 0 0px 0\">$obj->igst_paid</span></td></tr>";

    $totaltd=round($obj->taxable_amt+$obj->sgst_paid+$obj->cgst_paid+$obj->igst_paid);
    $html.="<tr><td border=1 align=\"center\"><span style=\"font-size:14px; padding:12px 0 0px 0\">Total value&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></td>"
            . "<td border=1 align=\"center\"><span style=\"font-size:14px; padding:12px 0 0px 0\">$totaltd</span></td></tr>";

    //$html .= "</table>";
    
    
    
    
    //$html .= "<table width=\"80%\">";
    $html .= "<tr><td colspan=2><span style=\"font-size:18px; padding:12px 0 0px 0\"><b>Remark:</b></span><br/>"
            . "<span style=\"font-size:14px; padding:12px 0 0px 0\">Discount Scheme:$order->ds_remark</span><br/></td>"
            . "</tr>";
            //. "</table>";
    $amtwords=$conv->getIndianCurrency($totaltd);
    //$html .= "<table width=\"80%\">";
    $html .= "<tr><td colspan=2><span style=\"font-size:18px; padding:12px 0 0px 0\"><b>Amount (in words) :</b></span><br/>"
            . "<span style=\"font-size:14px; padding:12px 0 0px 0\">$amtwords</span><br/></td>"
            . "</tr>"

    //$html .= "<table width=\"80%\" align=\"right\">"
            . "<tr><td></td><td align=\"right\"><span style=\"font-size:14px; padding:12px 0 0px 0\"><b>For Fashionking Brands Pvt. Ltd.</b></span><br/>"
            . "<span align=\"center\"><img src='../images/koushik.jpg' width='150'/></span>"
            . "<br/><span align=\"left\">&nbsp;&nbsp;Authorised Signatory</span></td></tr>";
    $html.="</table>";
    
 $html.='</page>';
$updatecnquery="update it_creditnote_ds set is_generated=1 where cn_no=$sr_no";
$z=$db->execUpdate($updatecnquery);

}

//echo $html;
//
////
$fname="../creditnote/DSCN".$dated."($taxpct%)_LK.pdf";
$html2fpdf->writeHTML($html);
$html2fpdf->Output("../creditnote/DSCN".$dated."($taxpct%)_LK.pdf", "F");
 


header('Content-Type: application/pdf');
header("Content-Disposition: attachment; filename=\"" . basename($fname) . "\"");
echo file_get_contents($fname);
////        