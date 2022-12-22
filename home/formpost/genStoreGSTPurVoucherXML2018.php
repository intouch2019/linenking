<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once 'lib/users/clsUsers.php';



extract($_GET);
$db = new DBConn();
$startdate = $db->safe(yymmdd($d1));
$enddate = $db->safe(yymmdd($d2));
$user = getCurrUser();
$userpage = new clsUsers();
$pagecode = $db->safe($_SESSION['pagecode']);
$page = $db->fetchObject("select * from it_pages where pagecode = $pagecode");
if ($page) {
    $allowed = $userpage->isAuthorized($user->id, $page->pagecode);
    if (!$allowed) {
        header("Location: " . DEF_SITEURL . "unauthorized");
        return;
    }
} else {
    header("Location:" . DEF_SITEURL . "nopagefound");
    return;
}



$envelope = new SimpleXMLElement('<ENVELOPE/>');
$dt1 = str_replace("-", "", $d1);
$dt2 = str_replace("-", "", $d2);
$name = "Store_PurchaseVoucher_" . $dt1 . "_" . $dt2 . ".xml";


$query = "select i.* FROM it_invoices i  WHERE  i.invoice_type = 0 and i.invoice_dt >= '2018-01-01 00:00:00' and date(i.invoice_dt) >= $startdate  and "
        . "date(i.invoice_dt) <= $enddate and store_id = $user->id order by invoice_no";

//print $query;
$objs = $db->fetchObjectArray($query);
if ($objs) {
    $header = $envelope->addChild("HEADER");
    $header->addChild("TALLYREQUEST", "Import Data");
    $body = $envelope->addChild("BODY");
    $importdata = $body->addChild("IMPORTDATA");
    $reqdesc = $importdata->addChild("REQUESTDESC"); //"REPORTNAME","Vouchers"
    $reqdesc->addChild("REPORTNAME", "Vouchers");
    $staticvariable = $reqdesc->addChild("STATICVARIABLES");
    $staticvariable->addChild("SVCURRENTCOMPANY", $user->tally_name);
    $reqdata = $importdata->addChild("REQUESTDATA");
    foreach ($objs as $obj) {
        if (isset($obj) && !empty($obj) && $obj != null) {

            $tax_5_obj = $db->fetchObject("select a.id,a.invoice_no,a.invoice_dt,sum(b.taxable_value+b.cgst+b.sgst+b.igst) as taxable_valwithtax,"
                    . "sum(b.quantity) as qty,sum(b.total_price_qty) as total_price_qty,round(sum(b.discount_val),2) as discount_val,a.discount_2,"
                    . "sum(b.cgst+b.sgst+b.igst) as total_tax, a.createtime, round(sum(b.total_rate_qty),2) as Rate, sum(b.taxable_value) as total_taxable_val,"
                    . "sum(cgst) as cgst, sum(sgst) as sgst, sum(igst) as igst,b.tax_rate*100 from it_invoices a,it_invoice_items b "
                    . "where a.id=b.invoice_id and b.invoice_id=$obj->id and b.tax_rate*100 = 5 group by b.tax_rate,a.id order by invoice_dt");

            $tax_12_obj = $db->fetchObject("select a.id,a.invoice_no,a.invoice_dt,sum(b.taxable_value+b.cgst+b.sgst+b.igst) as taxable_valwithtax,"
                    . "sum(b.quantity) as qty,sum(b.total_price_qty) as total_price_qty,round(sum(b.discount_val),2) as discount_val,a.discount_2,"
                    . "sum(b.cgst+b.sgst+b.igst) as total_tax, a.createtime, round(sum(b.total_rate_qty),2) as Rate, sum(b.taxable_value) as total_taxable_val,"
                    . "sum(cgst) as cgst, sum(sgst) as sgst, sum(igst) as igst,b.tax_rate*100 from it_invoices a,it_invoice_items b "
                    . "where a.id=b.invoice_id and b.invoice_id=$obj->id and b.tax_rate*100 = 12 group by b.tax_rate,a.id order by invoice_dt");

            $tax_28_obj = $db->fetchObject("select a.id,a.invoice_no,a.invoice_dt,sum(b.taxable_value+b.cgst+b.sgst+b.igst) as taxable_valwithtax,"
                    . "sum(b.quantity) as qty,sum(b.total_price_qty) as total_price_qty,round(sum(b.discount_val),2) as discount_val,a.discount_2,"
                    . "sum(b.cgst+b.sgst+b.igst) as total_tax, a.createtime, round(sum(b.total_rate_qty),2) as Rate, sum(b.taxable_value) as total_taxable_val,"
                    . "sum(cgst) as cgst, sum(sgst) as sgst, sum(igst) as igst,truncate(b.tax_rate*100,0) from it_invoices a,it_invoice_items b "
                    . "where a.id=b.invoice_id and b.invoice_id=$obj->id and b.tax_rate = 0.28 group by b.tax_rate,a.id order by invoice_dt");

            $tax_18_obj = $db->fetchObject("select a.id,a.invoice_no,a.invoice_dt,sum(b.taxable_value+b.cgst+b.sgst+b.igst) as taxable_valwithtax,"
                    . "sum(b.quantity) as qty,sum(b.total_price_qty) as total_price_qty,round(sum(b.discount_val),2) as discount_val,a.discount_2,"
                    . "sum(b.cgst+b.sgst+b.igst) as total_tax, a.createtime, round(sum(b.total_rate_qty),2) as Rate, sum(b.taxable_value) as total_taxable_val,"
                    . "sum(cgst) as cgst, sum(sgst) as sgst, sum(igst) as igst,truncate(b.tax_rate*100,0) from it_invoices a,it_invoice_items b "
                    . "where a.id=b.invoice_id and b.invoice_id=$obj->id and b.tax_rate = 0.18 group by b.tax_rate,a.id order by invoice_dt");        

            //$itemobj = $db->fetchObject("select * from it_invoice_items_reports where invoice_id = $obj->id ");

            $sname = $obj->store_name;
            if (isset($obj->round_off) && $obj->round_off != NULL) {
                //$roundoff=$obj->round_off;
                $roundoff = 0.0;
            } else {
                $roundoff = 0.0;
            }

            if(isset($obj->tcs_0075pct) && $obj->tcs_0075pct != NULL ){
                          
                $tcs_0075pct=$obj->tcs_0075pct;
          
            }else{
                $tcs_0075pct=0.0;
            }
          if(isset($obj->additional_disc_val) && $obj->additional_disc_val != NULL ){
              
                $additional_disc_val=$obj->additional_disc_val*-1;
          
            }else{
                $additional_disc_val=0.0;
            }

            //$roundoff = round((round($obj->invoice_amt,2,PHP_ROUND_HALF_DOWN)-(round($obj->rate_subtotal,2,PHP_ROUND_HALF_DOWN)-round($obj->discount_val,2,PHP_ROUND_HALF_DOWN)+round($obj->cgst_total,2,PHP_ROUND_HALF_DOWN)+round($obj->sgst_total,2,PHP_ROUND_HALF_DOWN)+round($obj->igst_total,2,PHP_ROUND_HALF_DOWN))),2,PHP_ROUND_HALF_DOWN);
            //$roundoff = round((round($obj->invoice_amt,2,PHP_ROUND_HALF_DOWN)-(round($roundrate,2,PHP_ROUND_HALF_DOWN)-round($rounddiscount,2,PHP_ROUND_HALF_DOWN)+round($roundcgst,2,PHP_ROUND_HALF_DOWN)+round($roundsgst,2,PHP_ROUND_HALF_DOWN)+round($roundigst,2,PHP_ROUND_HALF_DOWN))),2,PHP_ROUND_HALF_DOWN);
            //to fetch voucher number
            //step 1 : Remove the 1st 4 financial yr indications
            $str2 = substr($obj->invoice_no, 4);

            //step 2 : Remove the last number from remaining inv series
            $str3 = substr($str2, 0, -1);

            //step3 : Check if the middle numbers leaving last disgit > 0
            if ($str3 > 0) {
                $vno = $str2;  //vch no shld contain all those numbers
            } else {
                $vno = substr($str2, -1); //vch no will be the last non zero digit
            }

            //step4: Remove the preceding zeroes
            $vch_no = ltrim($vno, "0");

            $tallymsg = $reqdata->addChild("TALLYMESSAGE");
            $voucher = $tallymsg->addChild("VOUCHER");
            $voucher->addAttribute("VCHTYPE", "Purchase");
            $voucher->addAttribute("ACTION", "Create");
            $dt = date('Y-m-d', strtotime($obj->invoice_dt));
            $invdate = preg_replace("/[^0-9]+/", "", $dt);
            $voucher->addChild("DATE", $invdate);
            $voucher->addChild("VOUCHERTYPENAME", "Purchase");
            $voucher->addChild("REFERENCE", $obj->invoice_no);
            $voucher->addChild("VOUCHERNUMBER", $vch_no);
            $voucher->addChild("PARTYLEDGERNAME", "Cotton King Pvt. Ltd.");
            $voucher->addChild("PERSISTEDVIEW", "Accounting Voucher View");
            $allledgerentrieslist1 = $voucher->addChild("ALLLEDGERENTRIES.LIST"); // this node for inv amt
            $allledgerentrieslist1->addChild("LEDGERNAME", "Cotton King Pvt. Ltd.");
            $allledgerentrieslist1->addChild("GSTCLASS");
            $allledgerentrieslist1->addChild("ISDEEMEDPOSITIVE", "No");
            $allledgerentrieslist1->addChild("ISPARTYLEDGER", "Yes");
            $allledgerentrieslist1->addChild("AMOUNT", round($obj->invoice_amt, 2, PHP_ROUND_HALF_DOWN));
            $allledgerentrieslist1->addChild("BANKALLOCATIONS.LIST", " ");
            $allbillallocationslist = $allledgerentrieslist1->addChild("BILLALLOCATIONS.LIST");
            $allbillallocationslist->addChild("NAME", $obj->invoice_no);
            $allbillallocationslist->addChild("AMOUNT", round($obj->invoice_amt, 2, PHP_ROUND_HALF_DOWN));


            //for tax rate 5 calculations
            if (isset($tax_5_obj)) {
                $allledgerentrieslist2 = $voucher->addChild("ALLLEDGERENTRIES.LIST", " "); // this node is for total_mrp
                if (trim($obj->igst_total) != "" && trim($obj->igst_total) != "0") {
                    $sales_str = "MRP of 5% Purchase OMS";
                    $discount_str = "Discount for 5% Sales OMS";
                } else {
                    $sales_str = "MRP of 5% Purchase MS";
                    $discount_str = "Discount for 5% Purchase MS";
                }
                $allledgerentrieslist2->addChild("LEDGERNAME", $sales_str);
                //} 
                $allledgerentrieslist2->addChild("GSTCLASS");
                $allledgerentrieslist2->addChild("ISDEEMEDPOSITIVE", "Yes");
                $allledgerentrieslist2->addChild("AMOUNT", "-" . round($tax_5_obj->total_price_qty, 2, PHP_ROUND_HALF_DOWN));
                $allledgerentrieslist3 = $voucher->addChild("ALLLEDGERENTRIES.LIST");

                $allledgerentrieslist3->addChild("LEDGERNAME", $discount_str);
                $allledgerentrieslist3->addChild("GSTCLASS");
                $allledgerentrieslist3->addChild("ISDEEMEDPOSITIVE", "No");
                $allledgerentrieslist3->addChild("AMOUNT", round($tax_5_obj->discount_val, 2, PHP_ROUND_HALF_DOWN));
  
                if (trim($obj->igst_total) == "" || trim($obj->igst_total) == "0") {
                    $allledgerentrieslist5 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
                    $allledgerentrieslist5->addChild("LEDGERNAME", "CGST Paid @ 2.5%");
                    $allledgerentrieslist5->addChild("GSTCLASS");
                    $allledgerentrieslist5->addChild("ISDEEMEDPOSITIVE", "Yes");
                    $allledgerentrieslist5->addChild("AMOUNT", "-" . round($tax_5_obj->cgst, 2, PHP_ROUND_HALF_DOWN));

                    $allledgerentrieslist6 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
                    $allledgerentrieslist6->addChild("LEDGERNAME", "SGST Paid @ 2.5%");
                    $allledgerentrieslist6->addChild("GSTCLASS");
                    $allledgerentrieslist6->addChild("ISDEEMEDPOSITIVE", "Yes");
                    $allledgerentrieslist6->addChild("AMOUNT", "-" . round($tax_5_obj->sgst, 2, PHP_ROUND_HALF_DOWN));
                }

                if (trim($obj->igst_total) != "" && trim($obj->igst_total) != "0") {
                    $allledgerentrieslist7 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
                    $allledgerentrieslist7->addChild("LEDGERNAME", "IGST Paid @ 5%");
                    $allledgerentrieslist7->addChild("GSTCLASS");
                    $allledgerentrieslist7->addChild("ISDEEMEDPOSITIVE", "Yes");
                    $allledgerentrieslist7->addChild("AMOUNT", "-" . round($tax_5_obj->igst, 2, PHP_ROUND_HALF_DOWN));
                }
            }
            //for tax rate 12 calulations
            if (isset($tax_12_obj)) {
                $allledgerentrieslist8 = $voucher->addChild("ALLLEDGERENTRIES.LIST", " ");
                if (trim($obj->igst_total) != "" && trim($obj->igst_total) != "0") {
                    $sales_str12 = "MRP of 12% Purchase OMS";
                    $discount_str12 = "Discount For 12% Purchase OMS";
                } else {
                    $sales_str12 = "MRP of 12% Purchase MS";
                    $discount_str12 = "Discount For 12% Purchase MS";
                }
                $allledgerentrieslist8->addChild("LEDGERNAME", $sales_str12);
                $allledgerentrieslist8->addChild("GSTCLASS");
                $allledgerentrieslist8->addChild("ISDEEMEDPOSITIVE", "Yes");
                $allledgerentrieslist8->addChild("AMOUNT", "-" . round($tax_12_obj->total_price_qty, 2, PHP_ROUND_HALF_DOWN));
                $allledgerentrieslist9 = $voucher->addChild("ALLLEDGERENTRIES.LIST");

                $allledgerentrieslist9->addChild("LEDGERNAME", $discount_str12);
                $allledgerentrieslist9->addChild("GSTCLASS");
                $allledgerentrieslist9->addChild("ISDEEMEDPOSITIVE", "No");
                $allledgerentrieslist9->addChild("AMOUNT", round($tax_12_obj->discount_val, 2, PHP_ROUND_HALF_DOWN));
  
                if (trim($obj->igst_total) == "" || trim($obj->igst_total) == "0") {
                    $allledgerentrieslist10 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
                    $allledgerentrieslist10->addChild("LEDGERNAME", "CGST Paid @ 6%");
                    $allledgerentrieslist10->addChild("GSTCLASS");
                    $allledgerentrieslist10->addChild("ISDEEMEDPOSITIVE", "Yes");
                    $allledgerentrieslist10->addChild("AMOUNT", "-" . round($tax_12_obj->cgst, 2, PHP_ROUND_HALF_DOWN));

                    $allledgerentrieslist11 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
                    $allledgerentrieslist11->addChild("LEDGERNAME", "SGST Paid @ 6%");
                    $allledgerentrieslist11->addChild("GSTCLASS");
                    $allledgerentrieslist11->addChild("ISDEEMEDPOSITIVE", "Yes");
                    $allledgerentrieslist11->addChild("AMOUNT", "-" . round($tax_12_obj->sgst, 2, PHP_ROUND_HALF_DOWN));
                }

                if (trim($obj->igst_total) != "" && trim($obj->igst_total) != "0") {
                    $allledgerentrieslist12 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
                    $allledgerentrieslist12->addChild("LEDGERNAME", "IGST Paid @ 12%");
                    $allledgerentrieslist12->addChild("GSTCLASS");
                    $allledgerentrieslist12->addChild("ISDEEMEDPOSITIVE", "Yes");
                    $allledgerentrieslist12->addChild("AMOUNT", "-" . round($tax_12_obj->igst, 2, PHP_ROUND_HALF_DOWN));
                }
            }

            //for tax rate 28 calulations
            if (isset($tax_28_obj)) {
                $allledgerentrieslist8 = $voucher->addChild("ALLLEDGERENTRIES.LIST", " ");
                if (trim($obj->igst_total) != "" && trim($obj->igst_total) != "0") {
                    $sales_str28 = "MRP of 28% Purchase OMS";
                    $discount_str28 = "Discount For 28% Purchase OMS";
                } else {
                    $sales_str28 = "MRP of 28% Purchase MS";
                    $discount_str28 = "Discount For 28% Purchase MS";
                }
                $allledgerentrieslist13->addChild("LEDGERNAME", $sales_str28);
                $allledgerentrieslist13->addChild("GSTCLASS");
                $allledgerentrieslist13->addChild("ISDEEMEDPOSITIVE", "Yes");
                $allledgerentrieslist13->addChild("AMOUNT", "-" . round($tax_28_obj->total_price_qty, 2, PHP_ROUND_HALF_DOWN));
                $allledgerentrieslist13 = $voucher->addChild("ALLLEDGERENTRIES.LIST");

                $allledgerentrieslist14->addChild("LEDGERNAME", $discount_str28);
                $allledgerentrieslist14->addChild("GSTCLASS");
                $allledgerentrieslist14->addChild("ISDEEMEDPOSITIVE", "No");
                $allledgerentrieslist14->addChild("AMOUNT", round($tax_28_obj->discount_val, 2, PHP_ROUND_HALF_DOWN));
  
                if (trim($obj->igst_total) == "" || trim($obj->igst_total) == "0") {
                    $allledgerentrieslist15 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
                    $allledgerentrieslist15->addChild("LEDGERNAME", "CGST Paid @ 14%");
                    $allledgerentrieslist15->addChild("GSTCLASS");
                    $allledgerentrieslist15->addChild("ISDEEMEDPOSITIVE", "Yes");
                    $allledgerentrieslist15->addChild("AMOUNT", "-" . round($tax_28_obj->cgst, 2, PHP_ROUND_HALF_DOWN));

                    $allledgerentrieslist16 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
                    $allledgerentrieslist16->addChild("LEDGERNAME", "SGST Paid @ 14%");
                    $allledgerentrieslist16->addChild("GSTCLASS");
                    $allledgerentrieslist16->addChild("ISDEEMEDPOSITIVE", "Yes");
                    $allledgerentrieslist16->addChild("AMOUNT", "-" . round($tax_28_obj->sgst, 2, PHP_ROUND_HALF_DOWN));
                }

                if (trim($obj->igst_total) != "" && trim($obj->igst_total) != "0") {
                    $allledgerentrieslist17 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
                    $allledgerentrieslist17->addChild("LEDGERNAME", "IGST Paid @ 28%");
                    $allledgerentrieslist17->addChild("GSTCLASS");
                    $allledgerentrieslist17->addChild("ISDEEMEDPOSITIVE", "Yes");
                    $allledgerentrieslist17->addChild("AMOUNT", "-" . round($tax_28_obj->igst, 2, PHP_ROUND_HALF_DOWN));
                }
            }

                                     //for tax rate 18 calulations
            if (isset($tax_18_obj)) {
                $allledgerentrieslist19 = $voucher->addChild("ALLLEDGERENTRIES.LIST", " ");
                if (trim($obj->igst_total) != "" && trim($obj->igst_total) != "0") {
                    $sales_str18 = "MRP of 18% Purchase OMS";
                    $discount_str18 = "Discount For 18% Purchase OMS";
                } else {
                    $sales_str18 = "MRP of 18% Purchase MS";
                    $discount_str18 = "Discount For 18% Purchase MS";
                }
                $allledgerentrieslist19->addChild("LEDGERNAME", $sales_str18);
                $allledgerentrieslist19->addChild("GSTCLASS");
                $allledgerentrieslist19->addChild("ISDEEMEDPOSITIVE", "Yes");
                $allledgerentrieslist19->addChild("AMOUNT", "-" . round($tax_18_obj->total_price_qty, 2, PHP_ROUND_HALF_DOWN));
                $allledgerentrieslist20 = $voucher->addChild("ALLLEDGERENTRIES.LIST");

                $allledgerentrieslist20->addChild("LEDGERNAME", $discount_str18);
                $allledgerentrieslist20->addChild("GSTCLASS");
                $allledgerentrieslist20->addChild("ISDEEMEDPOSITIVE", "No");
                $allledgerentrieslist20->addChild("AMOUNT", round($tax_18_obj->discount_val, 2, PHP_ROUND_HALF_DOWN));
  
                if (trim($obj->igst_total) == "" || trim($obj->igst_total) == "0") {
                    $allledgerentrieslist21 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
                    $allledgerentrieslist21->addChild("LEDGERNAME", "CGST Paid @ 9%");
                    $allledgerentrieslist21->addChild("GSTCLASS");
                    $allledgerentrieslist21->addChild("ISDEEMEDPOSITIVE", "Yes");
                    $allledgerentrieslist21->addChild("AMOUNT", "-" . round($tax_18_obj->cgst, 2, PHP_ROUND_HALF_DOWN));

                    $allledgerentrieslist22 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
                    $allledgerentrieslist22->addChild("LEDGERNAME", "SGST Paid @ 9%");
                    $allledgerentrieslist22->addChild("GSTCLASS");
                    $allledgerentrieslist22->addChild("ISDEEMEDPOSITIVE", "Yes");
                    $allledgerentrieslist22->addChild("AMOUNT", "-" . round($tax_18_obj->sgst, 2, PHP_ROUND_HALF_DOWN));
                }

                if (trim($obj->igst_total) != "" && trim($obj->igst_total) != "0") {
                    $allledgerentrieslist23 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
                    $allledgerentrieslist23->addChild("LEDGERNAME", "IGST Paid @ 18%");
                    $allledgerentrieslist23->addChild("GSTCLASS");
                    $allledgerentrieslist23->addChild("ISDEEMEDPOSITIVE", "Yes");
                    $allledgerentrieslist23->addChild("AMOUNT", "-" . round($tax_18_obj->igst, 2, PHP_ROUND_HALF_DOWN));
                }
            }

            /* -------------------------------------------------------------------------------------- */
            $allledgerentrieslist18 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
            $allledgerentrieslist18->addChild("LEDGERNAME", "Round Off");
            if ($roundoff > 0) {
                $allledgerentrieslist18->addChild("ISDEEMEDPOSITIVE", "Yes");
                $allledgerentrieslist18->addChild("AMOUNT", "-" . $roundoff);
            } else if ($roundoff < 0) {
                $allledgerentrieslist18->addChild("ISDEEMEDPOSITIVE", "No");
                $allledgerentrieslist18->addChild("AMOUNT", $roundoff * -1);
            } else if ($roundoff == 0) {
                $allledgerentrieslist18->addChild("ISDEEMEDPOSITIVE", "No");
                $allledgerentrieslist18->addChild("AMOUNT", $roundoff);
            }

            if(isset($obj->tcs_0075pct) && $obj->tcs_0075pct != NULL ){
                $allledgerentrieslist14 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
                $allledgerentrieslist14->addChild("LEDGERNAME","TCS Paid on Purchase");
                $allledgerentrieslist14->addChild("GSTCLASS");
                $allledgerentrieslist14->addChild("ISDEEMEDPOSITIVE","No");
                $allledgerentrieslist14->addChild("AMOUNT",$tcs_0075pct * -1);
                
                
}

if(isset($obj->additional_disc_val) && $obj->additional_disc_val != NULL ){

                $allledgerentrieslist14 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
                $allledgerentrieslist14->addChild("LEDGERNAME","Additional discount value");
                $allledgerentrieslist14->addChild("GSTCLASS");
                $allledgerentrieslist14->addChild("ISDEEMEDPOSITIVE","No");
                $allledgerentrieslist14->addChild("AMOUNT",$additional_disc_val * -1);
                
                
}
        }
    }

    header('Content-Disposition: attachment;filename=' . $name);
    header('Content-Type: application/xml; charset=utf-8');
    echo $envelope->saveXML();
} else {
    print "No Invoices availabe in the selected date range.";
}
?>
