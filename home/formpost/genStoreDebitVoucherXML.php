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
if($page){
    $allowed = $userpage->isAuthorized($user->id, $page->pagecode);
    if (!$allowed) { header("Location: ".DEF_SITEURL."unauthorized"); return; }
}else{ header("Location:".DEF_SITEURL."nopagefound"); return; }


    $envelope = new SimpleXMLElement('<ENVELOPE/>');    
    $dt1 = str_replace( "-", "", $d1);
    $dt2 = str_replace( "-", "", $d2);
    $name = "Store_DebitVoucher_".$dt1."_".$dt2.".xml";    
    
    $query = " select i.*  FROM it_sp_invoices i  WHERE i.invoice_type = 5 and date(i.invoice_dt) >= $startdate  and date(i.invoice_dt) <= $enddate and store_id = $user->id order by id ";
    //error_log("\nStoreDebitXML".$query,3,"tmp.txt");
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
                         if(trim($obj->tax_type)==""){
                            //fetch tax details frm another table
                           $tquery = "select * from it_invoice_taxes where invoice_id = $obj->id ";
                           $tobj = $db->fetchObject($tquery);
                           if(isset($tobj)){
                             $tax_type = substr(trim($tobj->tax_type),0,3);
                             $tax_percent = $tobj->tax_percent * 100 ;
                           }else{
                               $tax_type="";
                               $tax_percent = 0;
                           }  
                          }else{ // fetch tax as per old format
                             $tax_type = substr(trim($obj->tax_type),0,3);
                             $tax_percent = $obj->tax_percent * 100 ; 
                          }
                        //error_log("\n TAX TYPE: ".$tax_type."\n",3,"tmp.txt");
                        if($tax_type == 'CST'){ $str = "CST"; }else{ $str = "";}
                        
                         //error_log("\n TAX PERCENT: ".$tax_percent."\n",3,"tmp.txt");                        
                        $roundoff=0;

                        $roundoff = round((round($obj->invoice_amt,2,PHP_ROUND_HALF_DOWN)-(round($obj->total_mrp,2,PHP_ROUND_HALF_DOWN)-round($obj->discount_1,2,PHP_ROUND_HALF_DOWN)-round($obj->discount_2,2,PHP_ROUND_HALF_DOWN)+round($obj->tax,2,PHP_ROUND_HALF_DOWN))),2,PHP_ROUND_HALF_DOWN);
                       // $invoice_amt= round(($obj->total_mrp - $obj->discount_1 - $obj->discount_2 + $obj->tax),2);                       
                        $invoice_amt = round($obj->invoice_amt,2, PHP_ROUND_HALF_DOWN);
                        $tallymsg = $reqdata->addChild("TALLYMESSAGE");
                            $voucher = $tallymsg->addChild("VOUCHER");                            
                            $dt = date('Y-m-d',strtotime($obj->invoice_dt));
                               $invdate = preg_replace("/[^0-9]+/", "", $dt);
                                $voucher->addChild("DATE",$invdate);
                                $voucher->addChild("VOUCHERTYPENAME","Debit Note");
                                $voucher->addChild("PARTYLEDGERNAME","Sp Lifestyle Brands Pvt. Ltd.");
                                $voucher->addChild("PERSISTEDVIEW","Accounting Voucher View");
                                    $allledgerentrieslist1 = $voucher->addChild("ALLLEDGERENTRIES.LIST"); // this node for inv amt
                                        $allledgerentrieslist1->addChild("LEDGERNAME","Sp Lifestyle Brands Pvt. Ltd.");
                                        if($tax_percent == 0){
                                            $d = "Yes";$s="-";
                                        }else{
                                            $d = "No";$s="";
                                        }
                                        $allledgerentrieslist1->addChild("ISDEEMEDPOSITIVE",$d);
                                        $allledgerentrieslist1->addChild("AMOUNT",$s.$invoice_amt);
                                        $allledgerentrieslist1->addChild("BANKALLOCATIONS.LIST"," ");
                                        $allbillallocationslist = $allledgerentrieslist1->addChild("BILLALLOCATIONS.LIST");
                                            $allbillallocationslist->addChild("NAME", $obj->invoice_no);                                                                               
                                            $allbillallocationslist->addChild("AMOUNT", $s.$invoice_amt);                                       
                                   $allledgerentrieslist2 = $voucher->addChild("ALLLEDGERENTRIES.LIST"," "); // this node is for total_mrp
                                        //$allledgerentrieslist2->addChild("LEDGERNAME","Sales@".$taxpercent."%".$taxType);
                                       if($tax_percent == 0){
                                        $allledgerentrieslist2->addChild("LEDGERNAME","Purchase Return After 6 Month Mrp");
                                        $dpos = "No"; $sgn="";
                                       }else{
                                        $dpos = "Yes"; $sgn="-";   
                                        $allledgerentrieslist2->addChild("LEDGERNAME","Purchase");   
                                       } 
                                        $allledgerentrieslist2->addChild("ISDEEMEDPOSITIVE",$dpos);
                                        $allledgerentrieslist2->addChild("AMOUNT",$sgn.round($obj->total_mrp,2, PHP_ROUND_HALF_DOWN));
                                   $allledgerentrieslist3 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
                                       if($tax_percent == 0){
                                        $dp = "Yes";$dsgn="-";   
                                        $allledgerentrieslist3->addChild("LEDGERNAME","Pur. Return After 6 Month Disc-1");
                                       }else{
                                        $dp = "No";$dsgn="";
                                        $allledgerentrieslist3->addChild("LEDGERNAME","Discount 1");   
                                       } 
                                        $allledgerentrieslist3->addChild("ISDEEMEDPOSITIVE",$dp);
                                        $allledgerentrieslist3->addChild("AMOUNT",$dsgn.round($obj->discount_1,2, PHP_ROUND_HALF_DOWN));
                                   $allledgerentrieslist4 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
                                        if($tax_percent == 0){
                                         $allledgerentrieslist4->addChild("LEDGERNAME","Pur. Return After 6 Monthdisc-2");   
                                        }else{
                                         $allledgerentrieslist4->addChild("LEDGERNAME","Discount 2");
                                        }
                                        $allledgerentrieslist4->addChild("ISDEEMEDPOSITIVE",$dp);
                                        $allledgerentrieslist4->addChild("AMOUNT",$dsgn.round($obj->discount_2,2, PHP_ROUND_HALF_DOWN));
                                   if($tax_percent != 0){      
                                   $allledgerentrieslist5 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
                                        $allledgerentrieslist5->addChild("LEDGERNAME",$tax_percent." % ".$tax_type." paid ");
                                        $allledgerentrieslist5->addChild("ISDEEMEDPOSITIVE","Yes");
                                        $allledgerentrieslist5->addChild("AMOUNT","-".round($obj->tax,2, PHP_ROUND_HALF_DOWN)); 
                                   }
                                   $allledgerentrieslist6 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
                                        $allledgerentrieslist6->addChild("LEDGERNAME","Round Off");                                        
                                        if($roundoff < 0){ 
                                            if($tax_percent == 0){ // means older then 6 months
                                                $tdp = "Yes"; 
                                            }else{ 
                                              $tdp = "No";
                                              $roundoff = $roundoff*(-1);
                                            }
                                            $allledgerentrieslist6->addChild("ISDEEMEDPOSITIVE",$tdp);
                                            $allledgerentrieslist6->addChild("AMOUNT",$roundoff); 
                                        }else if($roundoff > 0){  
                                            if($tax_percent == 0){ // means older then 6 months
                                                $tdp = "No"; $tsgn = "";
                                            }else{ $tdp = "Yes"; $tsgn = "-"; } 
                                            $allledgerentrieslist6->addChild("ISDEEMEDPOSITIVE",$tdp);
                                            $allledgerentrieslist6->addChild("AMOUNT",$tsgn.$roundoff);  
                                        }else if($roundoff == 0){
                                            if($tax_percent == 0){ // means older then 6 months
                                                $tdp = "Yes"; $tsgn = "-";
                                            }else{ $tdp = "No"; $tsgn = ""; } 
                                            $allledgerentrieslist6->addChild("ISDEEMEDPOSITIVE",$tdp);
                                            $allledgerentrieslist6->addChild("AMOUNT",$tsgn.$roundoff);   
                                        }     
             }
             
         header('Content-Disposition: attachment;filename='.$name);    
         header('Content-Type: application/xml; charset=utf-8');
         echo $envelope->saveXML();  
    }else{ print "No Invoices availabe in the selected date range.";}    
?>
