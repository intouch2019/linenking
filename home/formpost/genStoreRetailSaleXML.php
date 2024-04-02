<?php

require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once 'lib/users/clsUsers.php';



extract($_GET);
//print_r($_GET);
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
$name = "Store_RetailSale_" . $dt1 . "_" . $dt2 . ".xml";

//change here later
//$cash_close_date = "select count(*) as row from it_orders where is_cashclosed = 1 and store_id=$user->id and (bill_datetime>$dt1 and bill_datetime<=$dt2)";
//$closecount = $db->fetchObject($cash_close_date);
////print $cash_close_date;
////print_r($closecount->row);
//$cash_not_close = "select count(*) as row from it_orders where is_cashclosed = 0 and store_id=$user->id and (bill_datetime>$dt1 and bill_datetime<=$dt2)";
////print $cash_not_close;
//$notclosecount = $db->fetchObject($cash_not_close);
////print_r($notclosecount->row);
//$closedate_qry = "select bill_datetime from it_orders where is_cashclosed = 1 and store_id=$user->id order by id desc limit 1";
////$closedate_qry = "select count(*) as row from it_orders where is_cashclosed = 0 and store_id=$user->id and (bill_datetime>$dt1 and bill_datetime<=$dt2)";
//$closedate_obj = $db->fetchObject($closedate_qry);
//$closedate = $closedate_obj->bill_datetime;
////print count($closecount)."|||||||||".count($notclosecount);
//if ($notclosecount->row == 0) {
//    
//} else/* ($closecount->row != $notclosecount->row) */ {
//    $dt2 = $db->safe($closedate);
//}
//print "later".$dt1."<>".$dt2;
//$net_total = "select sum(net_total) as net,o.bill_datetime  from it_orders o,it_order_payments p where o.id=p.order_id and o.store_id=$user->id and o.bill_datetime>$dt1 and o.bill_datetime<=$dt2 and o.tickettype in (1,0) and p.payment_name in ('creditnoteout','cash','magcard') and is_cashclosed=1  group by DAYOFMONTH(o.bill_datetime)";
$net_total = "select sum(net_total) as net,o.bill_datetime  from it_orders o,it_order_payments p where o.id=p.order_id and o.store_id=$user->id and o.bill_datetime>$dt1 and o.bill_datetime<=$dt2 and o.tickettype in (1,0) and p.payment_name in ('creditnoteout','cash','magcard')   group by DAYOFMONTH(o.bill_datetime)";
//print  $net_total;

$netobj = $db->fetchObjectArray($net_total);
//print_r($netobj);
$storename = "select store_name,tally_name,address,(select state from states where id = (select state_id from it_codes where id = $user->id)) as state,retail_saletally_name,retail_sale_cash_name,retail_sale_card_name, retail_sale_upi_name from it_codes where id = $user->id";
$store_obj = $db->fetchObject($storename);
if (isset($store_obj->retail_saletally_name)) {
    if (isset($netobj)) {
        $HEADER = $envelope->addChild("HEADER");
        $TALLYREQUEST = $HEADER->addChild("TALLYREQUEST", "Import Data");
        $BODY = $envelope->addChild("BODY");
        $IMPORTDATA = $BODY->addChild("IMPORTDATA");
        $REQUESTDESC = $IMPORTDATA->addChild("REQUESTDESC");
        $REPORTNAME = $REQUESTDESC->addChild("REPORTNAME", "Vouchers");
        $STATICVARIABLES = $REQUESTDESC->addChild("STATICVARIABLES");
        $SVCURRENTCOMPANY = $STATICVARIABLES->addChild("SVCURRENTCOMPANY", "Fashionking Brands Pvt. Ltd.");
        $REQUESTDATA = $IMPORTDATA->addChild("REQUESTDATA");
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
            $ALLLEDGERENTRIES_LIST->addChild("AMOUNT",round($data->net * -1) );
            $ALLLEDGERENTRIES_LIST->addChild("VATEXPAMOUNT", round($data->net * -1));
            $payment_type = "";
            $st_dt = $db->safe(date_format(date_create($data->bill_datetime), "Y-m-d 00:00:00"));
            $ed_dt = $db->safe(date_format(date_create($data->bill_datetime), "Y-m-d 23:59:59"));
//change here after jar //            $data_fetch_cash_creditnoteout = "select sum(net_total) as nettotal,o.bill_datetime,p.payment_name from it_orders o,it_order_payments p where p.order_id=o.id and o.store_id=$user->id and o.bill_datetime>$st_dt and o.bill_datetime<=$ed_dt and o.tickettype in (1,0) and p.payment_name in ('creditnoteout','cash') and is_cashclosed=1 group by DAYOFMONTH(o.bill_datetime)";
            $data_fetch_cash_creditnoteout = "select sum(net_total) as nettotal,o.bill_datetime,p.payment_name from it_orders o,it_order_payments p where p.order_id=o.id and o.store_id=$user->id and o.bill_datetime>$st_dt and o.bill_datetime<=$ed_dt and o.tickettype in (1,0) and p.payment_name in ('creditnoteout','cash')  group by DAYOFMONTH(o.bill_datetime)";            
            $cash_credit = $db->fetchObject($data_fetch_cash_creditnoteout);
            //print $data_fetch_cash_creditnoteout."\n";
//change here after jar //            $data_fetch_magcard = "select sum(net_total) as nettotal,o.bill_datetime,p.payment_name from it_orders o,it_order_payments p where p.order_id=o.id and o.store_id=$user->id and o.bill_datetime>$st_dt and o.bill_datetime<=$ed_dt and o.tickettype in (0) and p.payment_name in ('magcard') and is_cashclosed=1 group by DAYOFMONTH(o.bill_datetime)";
            $data_fetch_magcard = "select sum(net_total) as nettotal,o.bill_datetime,p.payment_name from it_orders o,it_order_payments p where p.order_id=o.id and o.store_id=$user->id and o.bill_datetime>$st_dt and o.bill_datetime<=$ed_dt and o.tickettype in (0) and p.payment_name in ('magcard')  group by DAYOFMONTH(o.bill_datetime)";
            $card = $db->fetchObject($data_fetch_magcard);
            //print $data_fetch_magcard."\n";
            $data_fetch_upi = "select sum(net_total) as nettotal,o.bill_datetime,p.payment_name from it_orders o,it_order_payments p where p.order_id=o.id and o.store_id=$user->id and o.bill_datetime>$st_dt and o.bill_datetime<=$ed_dt and o.tickettype in (0) and p.payment_name in ('upi')  group by DAYOFMONTH(o.bill_datetime)";
            $upi = $db->fetchObject($data_fetch_upi);
            if (isset($card) && !empty($card)) {
                $payment_type = "Card Sale";
                $amt = $card->nettotal;
                $ALLLEDGERENTRIES_LIST_1 = $VOUCHER->addChild("ALLLEDGERENTRIES.LIST");
                $ALLLEDGERENTRIES_LIST_1->addChild("LEDGERNAME", "$store_obj->retail_sale_card_name");
                $ALLLEDGERENTRIES_LIST_1->addChild("ISDEEMEDPOSITIVE", "No");                                
                $ALLLEDGERENTRIES_LIST_1->addChild("AMOUNT",round($amt));
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
                $ALLLEDGERENTRIES_LIST_1->addChild("AMOUNT",round($amt));
                $ALLLEDGERENTRIES_LIST_1->addChild("VATEXPAMOUNT", round($amt));
            }
        }
        header('Content-Disposition: attachment;filename=' . $name);
        header('Content-Type: application/xml; charset=utf-8');
        echo $envelope->saveXML();
    }else{
        print 'No Retail Sale Bill availabe in the selected date range.';    
    }
} else {
    print 'Retail Sale Tally Name not Available..please Update!';
}

?>