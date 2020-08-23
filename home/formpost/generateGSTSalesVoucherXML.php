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



    $envelope = new SimpleXMLElement('<ENVELOPE/>');    
    $dt1 = str_replace( "-", "", $d1);
    $dt2 = str_replace( "-", "", $d2);
    $name = "SalesVoucher_".$dt1."_".$dt2.".xml";
    //$spObj = $db->fetchObject("select * from it_codes where id = ".DEF_SP_LIFESTYLE_ID); // S.P. Life Style ID
    //$query = " select i.*,c.tally_name,c.store_name FROM it_invoices i, it_codes c WHERE c.id = i.store_id and i.invoice_type = 0 and date(i.invoice_dt) >= $startdate  and date(i.invoice_dt) <= $enddate  order by id ";
    $query = " select i.* FROM it_invoices i  WHERE  i.invoice_type = 0 and i.invoice_dt >= '2017-07-01 00:00:00' and  date(i.invoice_dt) >= $startdate  and date(i.invoice_dt) <= $enddate  order by id ";
    //error_log("\nSaleXML".$query,3,"tmp.txt");
    $objs = $db->fetchObjectArray($query);
    if($objs){
        $header = $envelope->addChild("HEADER");
        $header->addChild("TALLYREQUEST","Import Data");
        $body = $envelope->addChild("BODY");
            $importdata = $body->addChild("IMPORTDATA");
                $reqdesc = $importdata->addChild("REQUESTDESC");//"REPORTNAME","Vouchers"
                $reqdesc->addChild("REPORTNAME","Vouchers");
                        $staticvariable = $reqdesc->addChild("STATICVARIABLES");
                        $staticvariable->addChild("SVCURRENTCOMPANY","Cotton King Pvt. Ltd. 16-17");
                $reqdata = $importdata->addChild("REQUESTDATA");
                 foreach($objs as $obj){ 
                    if(isset($obj) && !empty($obj) && $obj != null){
                        $stObj = $db->fetchObject("select * from it_codes where id = $obj->store_id");
                        if(isset($stObj) && !empty($stObj) && $stObj != null){
                            if(isset($stObj->tally_name) && trim($stObj->tally_name) != ""){ $sname = $stObj->tally_name;}else{ $sname = $stObj->store_name ; }
                        }else{
                            $sname="-";
                        }
                        $roundoff=0;                        
                        $roundoff = round((round($obj->invoice_amt,2,PHP_ROUND_HALF_DOWN)-(round($obj->rate_subtotal,2,PHP_ROUND_HALF_DOWN)-round($obj->discount_val,2,PHP_ROUND_HALF_DOWN)+round($obj->cgst_total,2,PHP_ROUND_HALF_DOWN)+round($obj->sgst_total,2,PHP_ROUND_HALF_DOWN)+round($obj->igst_total,2,PHP_ROUND_HALF_DOWN))),2,PHP_ROUND_HALF_DOWN);
                        
                        //to fetch voucher number
                        //step 1 : Remove the 1st 4 financial yr indications
                        $str2 = substr($obj->invoice_no, 4);
                        
                        //step 2 : Remove the last number from remaining inv series
                        $str3 = substr($str2, 0,-1);
                        
                        //step3 : Check if the middle numbers leaving last disgit > 0
                        if($str3 > 0){
                            $vno = $str2;  //vch no shld contain all those numbers
                        }else{
                             $vno = substr($str2, -1); //vch no will be the last non zero digit
                        }
                        
                        //step4: Remove the preceding zeroes
                        $vch_no = ltrim($vno,"0");
                        

                        $tallymsg = $reqdata->addChild("TALLYMESSAGE");
                            $voucher = $tallymsg->addChild("VOUCHER");                            
                            $dt = date('Y-m-d',strtotime($obj->invoice_dt));
                               $invdate = preg_replace("/[^0-9]+/", "", $dt);
                                $voucher->addChild("DATE",$invdate);
                                $voucher->addChild("VOUCHERTYPENAME","Sales-GST");
                                $voucher->addChild("REFERENCE",$obj->invoice_no);
                                $voucher->addChild("VOUCHERNUMBER",$vch_no);
                                $voucher->addChild("PARTYLEDGERNAME",$sname);
                                $voucher->addChild("PERSISTEDVIEW","Accounting Voucher View");
                                    $allledgerentrieslist1 = $voucher->addChild("ALLLEDGERENTRIES.LIST"); // this node for inv amt
                                        $allledgerentrieslist1->addChild("LEDGERNAME", $sname);
                                        $allledgerentrieslist1->addChild("GSTCLASS");
                                        $allledgerentrieslist1->addChild("ISDEEMEDPOSITIVE","YES");
                                        $allledgerentrieslist1->addChild("AMOUNT","-".round($obj->invoice_amt,2,PHP_ROUND_HALF_DOWN));
                                        $allledgerentrieslist1->addChild("BANKALLOCATIONS.LIST"," ");
                                        $allbillallocationslist = $allledgerentrieslist1->addChild("BILLALLOCATIONS.LIST");
                                            $allbillallocationslist->addChild("NAME", $obj->invoice_no);                                                                               
                                            $allbillallocationslist->addChild("AMOUNT", "-".round($obj->invoice_amt,2,PHP_ROUND_HALF_DOWN));                                       
                                   $allledgerentrieslist2 = $voucher->addChild("ALLLEDGERENTRIES.LIST"," "); // this node is for total_mrp
                                        //$allledgerentrieslist2->addChild("LEDGERNAME","Sales@".$taxpercent."%".$taxType);
//                                       if($tax_percent == 0){
//                                        $allledgerentrieslist2->addChild("LEDGERNAME","MRP Fabric Sales");
//                                       }else{
                                      if(trim($obj->igst_total) != "" && trim($obj->igst_total) != "0"){ 
                                         $sales_str = "Sales -OMS" ;
                                         $discount_str = "Discount on OMS Sales";
                                      }else{
                                         $sales_str = "Sales - MS" ; 
                                         $discount_str = "Discount on MS Sales";
                                      }
                                        $allledgerentrieslist2->addChild("LEDGERNAME",$sales_str);   
                                       //} 
                                        $allledgerentrieslist2->addChild("GSTCLASS");
                                        $allledgerentrieslist2->addChild("ISDEEMEDPOSITIVE","No");
                                        $allledgerentrieslist2->addChild("AMOUNT",round($obj->rate_subtotal,2,PHP_ROUND_HALF_DOWN));
                                   $allledgerentrieslist3 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
//                                       if($tax_percent == 0){
//                                        $allledgerentrieslist3->addChild("LEDGERNAME","Discount 1 on fabric");
//                                       }else{
                                        $allledgerentrieslist3->addChild("LEDGERNAME","Discount on MS Sales");   
                                      // } 
                                        $allledgerentrieslist3->addChild("GSTCLASS");
                                        $allledgerentrieslist3->addChild("ISDEEMEDPOSITIVE","YES");
                                        $allledgerentrieslist3->addChild("AMOUNT","-".round($obj->discount_val,2,PHP_ROUND_HALF_DOWN));
//                                   $allledgerentrieslist4 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
//                                        $allledgerentrieslist4->addChild("LEDGERNAME","Discount 2".$str);
//                                        $allledgerentrieslist4->addChild("ISDEEMEDPOSITIVE","YES");
//                                        $allledgerentrieslist4->addChild("AMOUNT","-".round($obj->discount_2,2,PHP_ROUND_HALF_DOWN));
                                   //if($tax_percent != 0){   
                                        if(trim($obj->igst_total) == "" || trim($obj->igst_total) == "0"){       
                                            $allledgerentrieslist5 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
                                                 $allledgerentrieslist5->addChild("LEDGERNAME","CGST Collected");
                                                 $allledgerentrieslist5->addChild("GSTCLASS");
                                                 $allledgerentrieslist5->addChild("ISDEEMEDPOSITIVE","No");
                                                 $allledgerentrieslist5->addChild("AMOUNT",round($obj->cgst_total,2,PHP_ROUND_HALF_DOWN)); 

                                            $allledgerentrieslist6 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
                                                 $allledgerentrieslist6->addChild("LEDGERNAME","SGST Collected");
                                                 $allledgerentrieslist6->addChild("GSTCLASS");
                                                 $allledgerentrieslist6->addChild("ISDEEMEDPOSITIVE","No");
                                                 $allledgerentrieslist6->addChild("AMOUNT",round($obj->sgst_total,2,PHP_ROUND_HALF_DOWN));      
                                       }     

                                       if(trim($obj->igst_total) != "" && trim($obj->igst_total) != "0"){      
                                        $allledgerentrieslist7 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
                                             $allledgerentrieslist7->addChild("LEDGERNAME","IGST Collected");
                                             $allledgerentrieslist7->addChild("GSTCLASS");
                                             $allledgerentrieslist7->addChild("ISDEEMEDPOSITIVE","No");
                                             $allledgerentrieslist7->addChild("AMOUNT",round($obj->igst_total,2,PHP_ROUND_HALF_DOWN)); 
                                       }     
                                        
                                   $allledgerentrieslist8 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
                                        $allledgerentrieslist8->addChild("LEDGERNAME","Round Off");
                                       //  $allledgerentrieslist6->addChild("AMOUNT",$roundoffobj->round_off);
                                        if($roundoff > 0){
                                           // $roundoff = $roundoff*(-1);
                                            $allledgerentrieslist8->addChild("ISDEEMEDPOSITIVE","No");
                                            $allledgerentrieslist8->addChild("AMOUNT",$roundoff);
                                        }else if($roundoff < 0){
                                           $allledgerentrieslist8->addChild("ISDEEMEDPOSITIVE","Yes");
                                            $allledgerentrieslist8->addChild("AMOUNT", $roundoff);
                                        }else if($roundoff == 0){
                                           $allledgerentrieslist8->addChild("ISDEEMEDPOSITIVE","Yes");
                                           $allledgerentrieslist8->addChild("AMOUNT",$roundoff);
                                        }
                                                                              
                 }                   
             }
             
         header('Content-Disposition: attachment;filename='.$name);    
         header('Content-Type: application/xml; charset=utf-8');
         echo $envelope->saveXML();  
    }else{ print "No Invoices availabe in the selected date range.";}        
    

?>
