<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once 'lib/users/clsUsers.php';

extract($_POST);
$user = getCurrUser();
$errors=array();
$success=array();
$db = new DBConn();
$startdate = $db->safe(yymmdd($from));
$enddate = $db->safe(yymmdd($to));

$user = getCurrUser();
$userpage = new clsUsers();
$pagecode = $db->safe($_SESSION['pagecode']);
$page = $db->fetchObject("select * from it_pages where pagecode = $pagecode");
if($page){
    $allowed = $userpage->isAuthorized($user->id, $page->pagecode);
    if (!$allowed) { header("Location: ".DEF_SITEURL."unauthorized"); return; }
}else{ header("Location:".DEF_SITEURL."nopagefound"); return; }

 if($tallytype == "2"){ // means sales voucher xml
     $redirect = "formpost/generateSalesVoucherXML.php?d1=$from&d2=$to";
     header("Location: ".DEF_SITEURL."$redirect");
 }else if($tallytype == "3"){ // means credit voucher xml
     $redirect = "formpost/generateCreditVoucherXML.php?d1=$from&d2=$to";
     header("Location: ".DEF_SITEURL."$redirect");
 }else if($tallytype == "4"){ // means purchase xml
     $redirect = "formpost/generatePurchaseXML.php?d1=$from&d2=$to";
     header("Location: ".DEF_SITEURL."$redirect");
 }else if($tallytype == "5"){ // means  gst sales voucher xml     
     $redirect = "formpost/generateGSTNatchXL.php?d1=$from&d2=$to";
     header("Location: ".DEF_SITEURL."$redirect");    
 }else if($tallytype == "6"){ // means  gst sales voucher xml     
     $redirect = "formpost/generateSaleBackGSTPurchaseVoucherXML2018.php?d1=$from&d2=$to";
     header("Location: ".DEF_SITEURL."$redirect");  
 }else if($tallytype == "7"){ // means  gst sales voucher xml     
     $redirect = "formpost/generateGSTCreditVoucherXML2018.php?d1=$from&d2=$to";
     header("Location: ".DEF_SITEURL."$redirect");
 }
 else if($tallytype == "8"){ // means  gst sales voucher for defective garmentss   
     $redirect = "formpost/generateGSTDGCreditVoucherXML2019.php?d1=$from&d2=$to";
     header("Location: ".DEF_SITEURL."$redirect");  
 
 }else if($tallytype == "9"){ // means  Debitnote voucher xml     
     $redirect = "formpost/genDebitnoteXML.php?d1=$from&d2=$to";
 header("Location: ".DEF_SITEURL."$redirect");}
 else{  
   // means receipt voucher xml
    $envelope = new SimpleXMLElement('<ENVELOPE/>');    
    $dt1 = str_replace( "-", "", $from);
    $dt2 = str_replace( "-", "", $to);
    $name = "ReceiptVoucher_".$dt1."_".$dt2.".xml";
//    $spObj = $db->fetchObject("select * from it_codes where id = ".DEF_SP_LIFESTYLE_ID); // S.P. Life Style ID
    $query = " select i.*,c.tally_name,c.store_name FROM it_sp_invoices i, it_codes c WHERE c.id = i.store_id  and i.invoice_type = 0 and date(i.invoice_dt) >= $startdate  and date(i.invoice_dt) <= $enddate order by id ";    
//     $query = " select i.*  FROM it_sp_invoices i  WHERE  i.invoice_type = 0 and date(i.invoice_dt) >= $startdate  and date(i.invoice_dt) <= $enddate order by id ";
    $objs = $db->fetchObjectArray($query);
    if($objs){
        $header = $envelope->addChild("HEADER");
        $header->addChild("TALLYREQUEST","Import Data");
        $body = $envelope->addChild("BODY");
            $importdata = $body->addChild("IMPORTDATA");
                $reqdesc = $importdata->addChild("REQUESTDESC");//"REPORTNAME","Vouchers"
                $reqdesc->addChild("REPORTNAME","Vouchers");
                        $staticvariable = $reqdesc->addChild("STATICVARIABLES");
                        $staticvariable->addChild("SVCURRENTCOMPANY","Cotton King Pvt. Ltd. 14-15");
                $reqdata = $importdata->addChild("REQUESTDATA");
                 foreach($objs as $obj){
                        $itemobj = $db->fetchObject("select * from it_sp_invoice_items where invoice_id = $obj->id ");
//                        if (strpos($itemobj->item_code,"89000") !== false) {
//                          if(isset($spObj->tally_name) && trim($spObj->tally_name) != ""){ $sname = $spObj->tally_name;}else{ $sname = $spObj->store_name ; }  
//                        }else{
//                          $stObj = $db->fetchObject("select * from it_codes where id = $obj->store_id");
                          if(isset($obj->tally_name) && trim($obj->tally_name) != ""){ $sname = $obj->tally_name;}else{ $sname = $obj->store_name ; }
//                        }
                        $tallymsg = $reqdata->addChild("TALLYMESSAGE");
                            $voucher = $tallymsg->addChild("VOUCHER");
                            //$dt = date_format($obj->invoice_dt,'%Y-%m-%d');
                            $dt = date('Y-m-d',strtotime($obj->invoice_dt));
                               $invdate = preg_replace("/[^0-9]+/", "", $dt);
                                $voucher->addChild("DATE",$invdate);
                                $voucher->addChild("VOUCHERTYPENAME","Receipt");
                                $voucher->addChild("PARTYLEDGERNAME",$sname);
                                $voucher->addChild("PERSISTEDVIEW","Accounting Voucher View");
                                    $allledgerentrieslist = $voucher->addChild("ALLLEDGERENTRIES.LIST");
                                        $allledgerentrieslist->addChild("LEDGERNAME", $sname);
                                        $allledgerentrieslist->addChild("ISDEEMEDPOSITIVE","NO");
                                        $allledgerentrieslist->addChild("AMOUNT",round($obj->invoice_amt,2,PHP_ROUND_HALF_DOWN));
                                        $allbillallocationslist = $allledgerentrieslist->addChild("BILLALLOCATIONS.LIST");
                                            $allbillallocationslist->addChild("NAME", $obj->invoice_no);                                                                               
                                            $allbillallocationslist->addChild("AMOUNT", round($obj->invoice_amt,2,PHP_ROUND_HALF_DOWN));                                       
                                     $allledgerentrieslist = $voucher->addChild("ALLLEDGERENTRIES.LIST"); 
                                         $allledgerentrieslist->addChild("LEDGERNAME", "Axis Bank Ltd. CMS A/c.");
                                         $allledgerentrieslist->addChild("ISDEEMEDPOSITIVE","YES");
                                         $allledgerentrieslist->addChild("AMOUNT","-".round($obj->invoice_amt,2,PHP_ROUND_HALF_DOWN));
                                         $bankallocationslist = $allledgerentrieslist->addChild("BANKALLOCATIONS.LIST");
                                            $bankallocationslist->addChild("DATE", $invdate);
                                            $bankallocationslist->addChild("TRANSACTIONTYPE", "Cheque/DD");
                                            $bankallocationslist->addChild("PAYMENTFAVOURING", $sname);
                                            $arr = explode("::",$obj->payment);
                                            $cheque_no = $arr[1];
                                            if((stripos(trim($cheque_no),"RTGS") !== false)){$pmode = "RTGS";}else{$pmode = "CHEQUE";}
                                            $bankallocationslist->addChild("INSTRUMENTNUMBER", $cheque_no);
                                            $bankallocationslist->addChild("PAYMENTMODE", $pmode);
                                            $bankallocationslist->addChild("AMOUNT", "-".round($obj->invoice_amt,2,PHP_ROUND_HALF_DOWN));                                     
             }
             
         header('Content-Disposition: attachment;filename='.$name);    
         header('Content-Type: application/xml; charset=utf-8');
         echo $envelope->saveXML();  
         
    }else{
        $errors['inv']="No invoices present in the specified date range.<br/>Please enter date range again";
    }   

if (count($errors) > 0) {
	$_SESSION['form_errors'] = $errors;
        session_write_close();
        header("Location: ".DEF_SITEURL."admin/tallytransfer");
        exit;
} else {
	unset($_SESSION['form_errors']);
        $_SESSION['form_success'] = $success;
}
 }
?>