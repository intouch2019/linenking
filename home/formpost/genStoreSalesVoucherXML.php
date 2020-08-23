<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once 'lib/users/clsUsers.php';



extract($_GET);
$db = new DBConn();
$startdate = yymmdd($d1);
$enddate = yymmdd($d2);
$user = getCurrUser();
$userpage = new clsUsers();
$pagecode = $db->safe($_SESSION['pagecode']);
$page = $db->fetchObject("select * from it_pages where pagecode = $pagecode");
if($page){
    $allowed = $userpage->isAuthorized($user->id, $page->pagecode);
    if (!$allowed) { header("Location: ".DEF_SITEURL."unauthorized"); return; }
}else{ header("Location:".DEF_SITEURL."nopagefound"); return; }


    $envelope = new SimpleXMLElement('<ENVELOPE/>');    
    $dt1 = str_replace( "-", "", $d1);
    $dt2 = str_replace( "-", "", $d2);
    $name = "Store_SalesVoucher_".$dt1."_".$dt2.".xml";    
       
    $query = "select sum(o.amount) as tot_sales ,date(o.bill_datetime) as bill_datetime FROM it_orders o WHERE o.bill_datetime >= '$startdate 00:00:00' and o.bill_datetime <= '$enddate 23:59:59' and o.store_id = $user->id and o.tickettype = 0 group by date(o.bill_datetime)";
//    print $query;
//    error_log("\nStoreSalesXML".$query,3,"tmp.txt");
    $objs = $db->fetchObjectArray($query);
    if($objs){
        if(isset($user->tally_name) && trim($user->tally_name) != ""){ $sname = $user->tally_name;}else{ $sname = $user->store_name ; }       
        $header = $envelope->addChild("HEADER");
        $header->addChild("TALLYREQUEST","Import Data");
        $body = $envelope->addChild("BODY");
            $importdata = $body->addChild("IMPORTDATA");
                $reqdesc = $importdata->addChild("REQUESTDESC");//"REPORTNAME","Vouchers"
                $reqdesc->addChild("REPORTNAME","Vouchers");
                        $staticvariable = $reqdesc->addChild("STATICVARIABLES");
                        $staticvariable->addChild("SVCURRENTCOMPANY",$sname);
                $reqdata = $importdata->addChild("REQUESTDATA");
                 foreach($objs as $obj){                        
                        $tallymsg = $reqdata->addChild("TALLYMESSAGE");
                            $voucher = $tallymsg->addChild("VOUCHER");                            
                            $dt = date('Y-m-d',strtotime($obj->bill_datetime));
                               $invdate = preg_replace("/[^0-9]+/", "", $dt);
                                $voucher->addChild("DATE",$invdate);
                                $voucher->addChild("VOUCHERTYPENAME","Sales");
                                $voucher->addChild("PARTYLEDGERNAME",$sname);
                                $voucher->addChild("PERSISTEDVIEW","Accounting Voucher View");
                                    $allledgerentrieslist1 = $voucher->addChild("ALLLEDGERENTRIES.LIST"); // this node for inv amt
                                    $allledgerentrieslist1->addChild("LEDGERNAME",$sname);                                        
                                    $allledgerentrieslist1->addChild("ISDEEMEDPOSITIVE","Yes");
                                    $allledgerentrieslist1->addChild("AMOUNT","-".$obj->tot_sales);
                                    $allledgerentrieslist1->addChild("BANKALLOCATIONS.LIST"," ");
                                    $allledgerentrieslist1->addChild("BILLALLOCATIONS.LIST"," ");                                                                                                                      
             }
             
         header('Content-Disposition: attachment;filename='.$name);    
         header('Content-Type: application/xml; charset=utf-8');
         echo $envelope->saveXML();  
    }else{ print "No Sales availabe in the selected date range.";}    
