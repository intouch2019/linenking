<?php

require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once 'lib/users/clsUsers.php';

//here sales@ is total_mrp


extract($_GET);
$db = new DBConn();
$startdate = yymmdd($d1);
$enddate = yymmdd($d2);
$user = getCurrUser();
$userpage = new clsUsers();
$pagecode = $db->safe($_SESSION['pagecode']);

$page = $db->fetchObject("select pagecode from it_pages where pagecode = $pagecode");
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
$name = "PaymentVoucher_" . $dt1 . "_" . $dt2 . ".xml";
$query = "select i.brand_type,i.region_id,i.store_name,i.invoice_dt,i.invoice_amt,i.invoice_no,i.payment FROM it_invoices_reports i  WHERE i.store_id='$user->id' and i.invoice_type = 0 and i.invoice_dt >= '2017-07-01 00:00:00' and i.invoice_dt >= '$startdate 00:00:00'  and i.invoice_dt <= '$enddate 23:59:59' order by invoice_no";

//error_log("\n$query",3,"tmp.txt");
//print $query;
//return;
//echo 'hi';
$objs = $db->fetchObjectArray($query);
if ($objs) {
    $header = $envelope->addChild("HEADER");
    $header->addChild("TALLYREQUEST", "Import Data");
    $body = $envelope->addChild("BODY");
    $importdata = $body->addChild("IMPORTDATA");
    $reqdesc = $importdata->addChild("REQUESTDESC"); //"REPORTNAME","Vouchers"
    $reqdesc->addChild("REPORTNAME", "Vouchers");
    $staticvariable = $reqdesc->addChild("STATICVARIABLES");
    $staticvariable->addChild("SVCURRENTCOMPANY", "Fashionking Brands Pvt. Ltd.");
    $reqdata = $importdata->addChild("REQUESTDATA");
    foreach ($objs as $obj) {


        $brandtype = $obj->brand_type;
        if ($brandtype == 0) {

            $brand_name = "Cotton King";
        } else if ($brandtype == 1) {
            $brand_name = "Linenking";
        } else {
            $brand_name = "NA";
        }


        $region = $obj->region_id;
        if ($region == 0) {
            $regions = "NA";
        } else {
            $region_name = $db->fetchObject("select region from region where id=$region");
            $regions = $region_name->region . " " . "Region";
        }
        $sname = "Fashionking Brands Pvt. Ltd.";
        $tallymsg = $reqdata->addChild("TALLYMESSAGE");
        $voucher = $tallymsg->addChild("VOUCHER");
        $voucher->addAttribute("VCHTYPE", "Payment");
        $voucher->addAttribute("ACTION", "Create");
        //$dt = date_format($obj->invoice_dt,'%Y-%m-%d');
        $dt = date('Y-m-d', strtotime($obj->invoice_dt));
        $invdate = preg_replace("/[^0-9]+/", "", $dt);
        $voucher->addChild("DATE", $invdate);
        $voucher->addChild("VOUCHERTYPENAME", "Payment");
        $voucher->addChild("PARTYLEDGERNAME", $sname);
        $voucher->addChild("PERSISTEDVIEW", "Accounting Voucher View");
        $allledgerentrieslist = $voucher->addChild("ALLLEDGERENTRIES.LIST");
        $allledgerentrieslist->addChild("LEDGERNAME", $sname);
        $allledgerentrieslist->addChild("GSTCLASS");
        $allledgerentrieslist->addChild("ISDEEMEDPOSITIVE", "NO");
        $payment_amt = 0;

        //one invoice case
        $allledgerentrieslist->addChild("AMOUNT", "-" .round($obj->invoice_amt, 2, PHP_ROUND_HALF_DOWN));
        $allbillallocationslist = $allledgerentrieslist->addChild("BILLALLOCATIONS.LIST");
        $allbillallocationslist->addChild("NAME", $obj->invoice_no);
        $allbillallocationslist->addChild("BILLTYPE", "Agst Ref");
        $allbillallocationslist->addChild("AMOUNT","-" . round($obj->invoice_amt, 2, PHP_ROUND_HALF_DOWN));
        $payment_amt = $obj->invoice_amt;

        $allledgerentrieslist = $voucher->addChild("ALLLEDGERENTRIES.LIST");
        $allledgerentrieslist->addChild("LEDGERNAME", "Axis Bank Ltd. CMS A/c.");
        $allledgerentrieslist->addChild("GSTCLASS");
        $allledgerentrieslist->addChild("ISDEEMEDPOSITIVE", "YES");
        $allledgerentrieslist->addChild("AMOUNT", round($payment_amt, 2, PHP_ROUND_HALF_DOWN));

        $allCATEGORYALLOCATIONSlist = $allledgerentrieslist->addChild("CATEGORYALLOCATIONS.LIST");
        $allCATEGORYALLOCATIONSlist->addChild("CATEGORY", "$brand_name");
        $allCATEGORYALLOCATIONSlist->addChild("NAME", "$regions");

        $bankallocationslist = $allledgerentrieslist->addChild("BANKALLOCATIONS.LIST");
        $bankallocationslist->addChild("DATE", $invdate);
        $bankallocationslist->addChild("NAME", uniqid());
        $bankallocationslist->addChild("TRANSACTIONTYPE", "Cheque/DD");
        $bankallocationslist->addChild("PAYMENTFAVOURING", $sname);
        $arr = explode("::", $obj->payment);
        $cheque_no = $arr[1];
        if ((stripos(trim($cheque_no), "RTGS") !== false)) {
            $pmode = "RTGS";
        } else {
            $pmode = "CHEQUE";
        }
        $bankallocationslist->addChild("INSTRUMENTNUMBER", $cheque_no);
        $bankallocationslist->addChild("PAYMENTMODE", "Transacted");
        $bankallocationslist->addChild("AMOUNT", round($payment_amt, 2, PHP_ROUND_HALF_DOWN));
    }

    header('Content-Disposition: attachment;filename=' . $name);
    header('Content-Type: application/xml; charset=utf-8');
    echo $envelope->saveXML();
} else {
    print "No invoices present in the specified date range.<br/>Please enter date range again";
}