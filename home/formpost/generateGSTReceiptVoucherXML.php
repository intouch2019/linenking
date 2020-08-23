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
$startdate = $db->safe(yymmdd($d1));
$enddate = $db->safe(yymmdd($d2));
$user = getCurrUser();
$userpage = new clsUsers();
$pagecode = $db->safe($_SESSION['pagecode']);
$page = $db->fetchObject("select * from it_pages where pagecode = $pagecode");
if($page){
    $allowed = $userpage->isAuthorized($user->id, $page->pagecode);
    if (!$allowed) { header("Location: ".DEF_SITEURL."unauthorized"); return; }
}else{ header("Location:".DEF_SITEURL."nopagefound"); return; }


   // means receipt voucher xml => old one , not used now (11-11-2016)
    $envelope = new SimpleXMLElement('<ENVELOPE/>');    
    $dt1 = str_replace( "-", "", $d1);
    $dt2 = str_replace( "-", "", $d2);
    $name = "ReceiptVoucher_".$dt1."_".$dt2.".xml";
    $spObj = $db->fetchObject("select * from it_codes where id = ".DEF_SP_LIFESTYLE_ID); // S.P. Life Style ID
   // $query = " select i.*,c.tally_name,c.store_name FROM it_invoices i, it_codes c WHERE c.id = i.store_id  and i.invoice_type = 0 and date(i.invoice_dt) >= $startdate  and date(i.invoice_dt) <= $enddate order by id ";    
     $query = " select i.*  FROM it_invoices i  WHERE  i.invoice_type = 0  and i.invoice_dt >= '2017-07-01 00:00:00'  and date(i.invoice_dt) >= $startdate  and date(i.invoice_dt) <= $enddate order by id ";
    //error_log("\n$query",3,"tmp.txt");
     $objs = $db->fetchObjectArray($query);
    if($objs){
        $header = $envelope->addChild("HEADER");
        $header->addChild("TALLYREQUEST","Import Data");
        $body = $envelope->addChild("BODY");
            $importdata = $body->addChild("IMPORTDATA");
                $reqdesc = $importdata->addChild("REQUESTDESC");//"REPORTNAME","Vouchers"
                $reqdesc->addChild("REPORTNAME","Vouchers");
                        $staticvariable = $reqdesc->addChild("STATICVARIABLES");
                        $staticvariable->addChild("SVCURRENTCOMPANY","Cotton King Pvt. Ltd. 15-16");
                $reqdata = $importdata->addChild("REQUESTDATA");
                 foreach($objs as $obj){
//                        $itemobj = $db->fetchObject("select * from it_invoice_items where invoice_id = $obj->id ");
//                        if (strpos($itemobj->item_code,"89000") !== false) {
//                          if(isset($spObj->tally_name) && trim($spObj->tally_name) != ""){ $sname = $spObj->tally_name;}else{ $sname = $spObj->store_name ; }  
//                        }else{
                          $stObj = $db->fetchObject("select * from it_codes where id = $obj->store_id");
                          if(isset($stObj->tally_name) && trim($stObj->tally_name) != ""){ $sname = $stObj->tally_name;}else{ $sname = $stObj->store_name ; }
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
                                        $allledgerentrieslist->addChild("GSTCLASS");
                                        $allledgerentrieslist->addChild("ISDEEMEDPOSITIVE","NO");
                                        //$allledgerentrieslist->addChild("AMOUNT",round($obj->invoice_amt,2,PHP_ROUND_HALF_DOWN));
//                                        $allbillallocationslist = $allledgerentrieslist->addChild("BILLALLOCATIONS.LIST");
//                                            $allbillallocationslist->addChild("NAME", $obj->invoice_no);                                                                               
//                                            $allbillallocationslist->addChild("AMOUNT", round($obj->invoice_amt,2,PHP_ROUND_HALF_DOWN));                                       
                                        $payment_amt = 0;
                                       // $payment_amt = round($obj->invoice_amt,2,PHP_ROUND_HALF_DOWN);
//                                        error_log("\n Payment Amt 1st: $payment_amt",3,"tmp.txt");
                                        //check for corresponding excise invoice
                                       $equery = "select * from it_ck_pickgroup where invoice_no like '$obj->invoice_no%'";
//                                       error_log("\n$equery",3,"tmp.txt");
                                       $eobj = $db->fetchObject($equery);
                                       if(isset($eobj)){
                                           if (strpos($eobj->invoice_no,",") !== false) {
                                               $arr = explode(",", $eobj->invoice_no);
                                                foreach($arr as $key => $invoice_no){
                                                    //to calc tot_amt
//                                                   error_log("\n PICK INV :  $invoice_no :: INV $obj->invoice_no",3,"tmp.txt");
//                                                   if(strcmp($obj->invoice_no,$invoice_no)== 0){
//                                                       continue;
//                                                   }else{
                                                      //fetch inv details 
                                                       $invqry = "select * from it_invoices where invoice_no = '$invoice_no'";
                                                       $invobj = $db->fetchObject($invqry);
                                                       if(isset($invobj)){
//                                                           error_log("\n INV AMT : $invobj->invoice_amt",3,"tmp.txt");
                                                            $payment_amt = $payment_amt + $invobj->invoice_amt;
//                                                              error_log("\n TOT  AMT IN LOOP : $payment_amt",3,"tmp.txt");
                                                       }
//                                                   }
                                               }
                                              $allledgerentrieslist->addChild("AMOUNT",round($payment_amt,2,PHP_ROUND_HALF_DOWN));
                                               
                                              foreach($arr as $key => $invoice_no){
//                                                   error_log("\n PICK INV :  $invoice_no :: INV $obj->invoice_no",3,"tmp.txt");
//                                                   if(strcmp($obj->invoice_no,$invoice_no)== 0){
//                                                       continue;
//                                                   }else{
                                                      //fetch inv details 
                                                       $invqry = "select * from it_invoices where invoice_no = '$invoice_no'";
                                                       $invobj = $db->fetchObject($invqry);
                                                       if(isset($invobj)){
                                                            $allbillallocationslist2 = $allledgerentrieslist->addChild("BILLALLOCATIONS.LIST");
                                                            $allbillallocationslist2->addChild("NAME", $invobj->invoice_no);                                                                               
                                                            $allbillallocationslist2->addChild("AMOUNT", round($invobj->invoice_amt,2,PHP_ROUND_HALF_DOWN));
                                                            //$payment_amt = $payment_amt + $invobj->invoice_amt;
                                                       }
//                                                   }
                                               }
                                           }
                                       }else{
                                           //one invoice case
                                           $allledgerentrieslist->addChild("AMOUNT",round($obj->invoice_amt,2,PHP_ROUND_HALF_DOWN));
                                           $allbillallocationslist = $allledgerentrieslist->addChild("BILLALLOCATIONS.LIST");
                                            $allbillallocationslist->addChild("NAME", $obj->invoice_no);                                                                               
                                            $allbillallocationslist->addChild("AMOUNT", round($obj->invoice_amt,2,PHP_ROUND_HALF_DOWN));                     
                                            $payment_amt = $obj->invoice_amt;
                                       }
//                                       
                                       $allledgerentrieslist = $voucher->addChild("ALLLEDGERENTRIES.LIST"); 
                                         $allledgerentrieslist->addChild("LEDGERNAME", "Axis Bank Ltd. CMS A/c.");
                                         $allledgerentrieslist->addChild("GSTCLASS");
                                         $allledgerentrieslist->addChild("ISDEEMEDPOSITIVE","YES");
                                         //$allledgerentrieslist->addChild("AMOUNT","-".round($obj->invoice_amt,2,PHP_ROUND_HALF_DOWN));
                                         $allledgerentrieslist->addChild("AMOUNT","-".round($payment_amt,2,PHP_ROUND_HALF_DOWN));
                                         $bankallocationslist = $allledgerentrieslist->addChild("BANKALLOCATIONS.LIST");
                                            $bankallocationslist->addChild("DATE", $invdate);
                                            $bankallocationslist->addChild("TRANSACTIONTYPE", "Cheque/DD");
                                            $bankallocationslist->addChild("PAYMENTFAVOURING", $sname);
                                            $arr = explode("::",$obj->payment);
                                            $cheque_no = $arr[1];
                                            if((stripos(trim($cheque_no),"RTGS") !== false)){$pmode = "RTGS";}else{$pmode = "CHEQUE";}
                                            $bankallocationslist->addChild("INSTRUMENTNUMBER", $cheque_no);
                                            $bankallocationslist->addChild("PAYMENTMODE", $pmode);
                                          //  $bankallocationslist->addChild("AMOUNT", "-".round($obj->invoice_amt,2,PHP_ROUND_HALF_DOWN));                                     
                                              $bankallocationslist->addChild("AMOUNT", "-".round($payment_amt,2,PHP_ROUND_HALF_DOWN));                                     
             }
             
         header('Content-Disposition: attachment;filename='.$name);    
         header('Content-Type: application/xml; charset=utf-8');
         echo $envelope->saveXML();  
         
    }else{
        //$errors['inv']="No invoices present in the specified date range.<br/>Please enter date range again";
        print "No Invoices availabe in the selected date range.";
    }

