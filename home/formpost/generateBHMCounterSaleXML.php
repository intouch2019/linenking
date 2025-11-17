<?php

require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once 'lib/users/clsUsers.php';

extract($_GET);
//print_r($_GET);exit();
$db = new DBConn();
$startdate = yymmdd($d1);
$enddate = yymmdd($d2);
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
$date = date_create($d1);
$dt1 = date_format($date, "Y-m-d 00:00:00");
$date = date_create($d2);
$dt2 = date_format($date, "Y-m-d 23:59:59");
//print "first".$dt1."<>".$dt2;
$dt1 = $db->safe($dt1);
$dt2 = $db->safe($dt2);
$name = "BHMStore_CounterSale_" . $dt1 . "_" . $dt2 . ".xml";

$bhmStoreQuery = "select id,is_bhmtallyxml,store_name from it_codes where is_bhmtallyxml !=0 and id not in (160,147) and is_closed=0"; //storeid = 160 is 50% bhmstore, 147 is dummy bhmstore
$storeObjs = $db->fetchObjectArray($bhmStoreQuery);
//echo '<pre>'; print_r($storeObjs); echo '</pre>'; exit();

if (!empty($storeObjs)) {
    $HEADER = $envelope->addChild("HEADER");
    $TALLYREQUEST = $HEADER->addChild("TALLYREQUEST", "Import Data");
    $BODY = $envelope->addChild("BODY");
    $IMPORTDATA = $BODY->addChild("IMPORTDATA");
    $REQUESTDESC = $IMPORTDATA->addChild("REQUESTDESC");
    $REPORTNAME = $REQUESTDESC->addChild("REPORTNAME", "Vouchers");
    $STATICVARIABLES = $REQUESTDESC->addChild("STATICVARIABLES");
    $SVCURRENTCOMPANY = $STATICVARIABLES->addChild("SVCURRENTCOMPANY", "BHM Textiles Hub LLP");
    $REQUESTDATA = $IMPORTDATA->addChild("REQUESTDATA");
    foreach ($storeObjs as $bhmStore) {
        $query =  "select sum(quantity) as quantity, sum(round(sub_total)) as net, bill_datetime from it_orders o where  o.store_id =$bhmStore->id and o.bill_datetime >= $dt1 and o.bill_datetime <= $dt2";
//        echo $query; exit();
        $netobj = $db->fetchObjectArray($query);

        $storename = "SELECT ic.store_name, ic.tally_name, ic.address, s.state, ic.retail_saletally_name, ic.retail_sale_cash_name, ic.retail_sale_card_name, ic.retail_sale_upi_name FROM it_codes ic LEFT JOIN states s ON s.id = ic.state_id WHERE ic.id = $bhmStore->id";
//        echo $storename; exit();
        $store_obj = $db->fetchObject($storename);

        if (!empty($store_obj) && isset($store_obj->retail_saletally_name)) {
            if (isset($netobj)) {

                foreach ($netobj as $data) {
                    $TALLYMESSAGE_1 = $REQUESTDATA->addChild("TALLYMESSAGE");
                    $VOUCHER = $TALLYMESSAGE_1->addChild("VOUCHER");
                    $VOUCHER->addAttribute("VCHTYPE", "Sales");
                    $VOUCHER->addAttribute("ACTION", "Create");
                    $VOUCHER->addAttribute("OBJVIEW", "Accounting Voucher View");
                    $VOUCHER->addChild("DATE", date_format(date_create($data->bill_datetime), "Ymd"));
                    $VOUCHER->addChild("STATENAME", $store_obj->state);
                    $VOUCHER->addChild("VOUCHERTYPENAME", "Sales");
                    $VOUCHER->addChild("PARTYLEDGERNAME", "$store_obj->retail_saletally_name");
                    $VOUCHER->addChild("BASICBASEPARTYNAME", "$store_obj->retail_saletally_name");
                    $VOUCHER->addChild("PERSISTEDVIEW", "Accounting Voucher View");
                    $VOUCHER->addChild("PLACEOFSUPPLY", $store_obj->state);
                    $VOUCHER->addChild("BASICBUYERNAME", "$store_obj->retail_saletally_name");
                    $converted_date = date_format(date_create($data->bill_datetime), "Y-M-d");
                    $converted_time = date_format(date_create($data->bill_datetime), "H:i");
                    $VOUCHER->addChild("CONSIGNEESTATENAME", $store_obj->state);
                    $converted_date_1 = date_format(date_create($data->bill_datetime), "Ymd");

                    $ALLLEDGERENTRIES_LIST = $VOUCHER->addChild("ALLLEDGERENTRIES.LIST");
                    $ALLLEDGERENTRIES_LIST->addChild("LEDGERNAME", "$store_obj->retail_saletally_name");
                    $ALLLEDGERENTRIES_LIST->addChild("ISDEEMEDPOSITIVE", "Yes");
                    $ALLLEDGERENTRIES_LIST->addChild("AMOUNT", round($data->net * -1));
                    $ALLLEDGERENTRIES_LIST->addChild("VATEXPAMOUNT", round($data->net * -1));
                    $payment_type = "";
                    $data_fetch_cash_creditnoteout = "SELECT SUM(p.amount) as nettotal, p.payment_name from it_order_payments p INNER JOIN it_orders o ON p.order_id = o.id WHERE o.store_id = $bhmStore->id AND o.bill_datetime >=$dt1 AND o.bill_datetime <=$dt2 and o.tickettype in (1, 0, 6) and p.payment_name in ('cash', 'creditnoteout', 'paperin', 'CorporateSale')";
                    $cash_credit = $db->fetchObject($data_fetch_cash_creditnoteout);

                    $data_fetch_magcard = "SELECT SUM(p.amount) as nettotal, p.payment_name from it_order_payments p INNER JOIN it_orders o ON p.order_id = o.id WHERE o.store_id = $bhmStore->id  AND o.bill_datetime >= $dt1 AND o.bill_datetime <=$dt2 and o.tickettype in (1, 0, 6) and p.payment_name in ('magcard')";
                    $card = $db->fetchObject($data_fetch_magcard);

                    $data_fetch_upi = "SELECT SUM(p.amount) as nettotal, p.payment_name from it_order_payments p INNER JOIN it_orders o ON p.order_id = o.id WHERE o.store_id = $bhmStore->id  AND o.bill_datetime >= $dt1 AND o.bill_datetime <=$dt2 and o.tickettype in (1, 0, 6) and p.payment_name in ('upi','online')";
                    $upi = $db->fetchObject($data_fetch_upi);
                    if (isset($card) && !empty($card)) {
                        $payment_type = "Card Sale";
                        $amt = $card->nettotal;
                        $ALLLEDGERENTRIES_LIST_1 = $VOUCHER->addChild("ALLLEDGERENTRIES.LIST");
                        $ALLLEDGERENTRIES_LIST_1->addChild("LEDGERNAME", "$store_obj->retail_sale_card_name");
                        $ALLLEDGERENTRIES_LIST_1->addChild("ISDEEMEDPOSITIVE", "No");
                        $ALLLEDGERENTRIES_LIST_1->addChild("AMOUNT", round($amt));
                        $ALLLEDGERENTRIES_LIST_1->addChild("VATEXPAMOUNT", round($amt));
                    }
                    if (isset($cash_credit) && !empty($cash_credit)) {
                        $payment_type = "Cash Sale";
                        $amt = $cash_credit->nettotal;
                        $ALLLEDGERENTRIES_LIST_1 = $VOUCHER->addChild("ALLLEDGERENTRIES.LIST");
                        $ALLLEDGERENTRIES_LIST_1->addChild("LEDGERNAME", "$store_obj->retail_sale_cash_name");
                        $ALLLEDGERENTRIES_LIST_1->addChild("ISDEEMEDPOSITIVE", "No");
                        $ALLLEDGERENTRIES_LIST_1->addChild("AMOUNT", round($amt));
                        $ALLLEDGERENTRIES_LIST_1->addChild("VATEXPAMOUNT", round($amt));
                    }
                    if (isset($upi) && !empty($upi)) {
                        $payment_type = "Upi Sale";
                        $amt = $upi->nettotal;
                        $ALLLEDGERENTRIES_LIST_1 = $VOUCHER->addChild("ALLLEDGERENTRIES.LIST");
                        $ALLLEDGERENTRIES_LIST_1->addChild("LEDGERNAME", "$store_obj->retail_sale_upi_name");
                        $ALLLEDGERENTRIES_LIST_1->addChild("ISDEEMEDPOSITIVE", "No");
                        $ALLLEDGERENTRIES_LIST_1->addChild("AMOUNT", round($amt));
                        $ALLLEDGERENTRIES_LIST_1->addChild("VATEXPAMOUNT", round($amt));
                    }
                }
                
            } else {
                print 'No Retail Sale Bill availabe in the selected date range.';
            }
        } else {
            print 'Retail Sale Tally Name not Available..please Update!'. $store_obj->store_name;
        }
    }
    header('Content-Disposition: attachment;filename=' . $name);
    header('Content-Type: application/xml; charset=utf-8');
    echo $envelope->saveXML();
} else {
    print 'No BHM Store Found';
}



