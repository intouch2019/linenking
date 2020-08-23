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
//    $spObj = $db->fetchObject("select * from it_codes where id = ".DEF_SP_LIFESTYLE_ID); // S.P. Life Style ID
    $query = " select i.*,c.tally_name,c.store_name FROM it_sp_invoices i, it_codes c WHERE c.id = i.store_id and i.invoice_type = 0 and date(i.invoice_dt) >= $startdate  and date(i.invoice_dt) <= $enddate  order by id ";
//    $query = " select i.* FROM it_invoices i  WHERE  i.invoice_type = 0 and date(i.invoice_dt) >= $startdate  and date(i.invoice_dt) <= $enddate  order by id ";
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
                        $staticvariable->addChild("SVCURRENTCOMPANY","Sp Lifestyle Brands Pvt. Ltd.");
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
                        $itemobj = $db->fetchObject("select * from it_sp_invoice_items where invoice_id = $obj->id ");
//                        if (strpos($itemobj->item_code,"89000") !== false) {
//                          if(isset($spObj->tally_name) && trim($spObj->tally_name) != ""){ $sname = $spObj->tally_name;}else{ $sname = $spObj->store_name ; }  
//                        }else{
//                         $stObj = $db->fetchObject("select * from it_codes where id = $obj->store_id");
                         if(isset($obj->tally_name) && trim($obj->tally_name) != ""){ $sname = $obj->tally_name;}else{ $sname = $obj->store_name ; }
//                        } 
                        //$roundoffquery="select round(invoice_amt-(total_mrp-discount_1-discount_2+tax),2) as round_off from it_invoices where invoice_no = '$obj->invoice_no';";
                       /* $roundoffquery="select round(round($obj->invoice_amt,2)-(round($obj->total_mrp,2)-round($obj->discount_1,2)-round($obj->discount_2,2)+round($obj->tax,2)),2) as round_off from it_sp_invoices where invoice_no = '$obj->invoice_no';";
                        $roundoffobj = $db->fetchObject($roundoffquery);
                        //$roundoffobj = $db->fetchObject($roundoffquery);
                        if(isset($roundoffobj)){
                          $roundoff = $roundoffobj->round_off;
//                          if($roundoff < 0){$roundoff=$roundoff*(-1);}
                        }*/
                        $roundoff = round((round($obj->invoice_amt,2,PHP_ROUND_HALF_DOWN)-(round($obj->total_mrp,2,PHP_ROUND_HALF_DOWN)-round($obj->discount_1,2,PHP_ROUND_HALF_DOWN)-round($obj->discount_2,2,PHP_ROUND_HALF_DOWN)+round($obj->tax,2,PHP_ROUND_HALF_DOWN))),2,PHP_ROUND_HALF_DOWN);
                        $tallymsg = $reqdata->addChild("TALLYMESSAGE");
                            $voucher = $tallymsg->addChild("VOUCHER");                            
                            $dt = date('Y-m-d',strtotime($obj->invoice_dt));
                               $invdate = preg_replace("/[^0-9]+/", "", $dt);
                                $voucher->addChild("DATE",$invdate);
                                $voucher->addChild("VOUCHERTYPENAME","Sales Baramati");
                                $voucher->addChild("PARTYLEDGERNAME",$sname);
                                $voucher->addChild("PERSISTEDVIEW","Accounting Voucher View");
                                    $allledgerentrieslist1 = $voucher->addChild("ALLLEDGERENTRIES.LIST"); // this node for inv amt
                                        $allledgerentrieslist1->addChild("LEDGERNAME", $sname);
                                        $allledgerentrieslist1->addChild("ISDEEMEDPOSITIVE","YES");
                                        $allledgerentrieslist1->addChild("AMOUNT","-".round($obj->invoice_amt,2,PHP_ROUND_HALF_DOWN));
                                        $allledgerentrieslist1->addChild("BANKALLOCATIONS.LIST"," ");
                                        $allbillallocationslist = $allledgerentrieslist1->addChild("BILLALLOCATIONS.LIST");
                                            $allbillallocationslist->addChild("NAME", $obj->invoice_no);                                                                               
                                            $allbillallocationslist->addChild("AMOUNT", "-".round($obj->invoice_amt,2,PHP_ROUND_HALF_DOWN));                                       
                                   $allledgerentrieslist2 = $voucher->addChild("ALLLEDGERENTRIES.LIST"," "); // this node is for total_mrp
                                        //$allledgerentrieslist2->addChild("LEDGERNAME","Sales@".$taxpercent."%".$taxType);
                                       if($tax_percent == 0){
                                        $allledgerentrieslist2->addChild("LEDGERNAME","MRP Fabric Sales");
                                       }else{
                                        $allledgerentrieslist2->addChild("LEDGERNAME","Sales@".$tax_percent."%");   
                                       } 
                                        $allledgerentrieslist2->addChild("ISDEEMEDPOSITIVE","No");
                                        $allledgerentrieslist2->addChild("AMOUNT",round($obj->total_mrp,2,PHP_ROUND_HALF_DOWN));
                                   $allledgerentrieslist3 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
                                       if($tax_percent == 0){
                                        $allledgerentrieslist3->addChild("LEDGERNAME","Discount 1 on fabric");
                                       }else{
                                        $allledgerentrieslist3->addChild("LEDGERNAME","Discount 1".$str);   
                                       } 
                                        $allledgerentrieslist3->addChild("ISDEEMEDPOSITIVE","YES");
                                        $allledgerentrieslist3->addChild("AMOUNT","-".round($obj->discount_1,2,PHP_ROUND_HALF_DOWN));
                                   $allledgerentrieslist4 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
                                        //$allledgerentrieslist4->addChild("LEDGERNAME","Discount 2".$str);
                                   if($tax_percent == 0){
                                        $allledgerentrieslist4->addChild("LEDGERNAME","Discount 2 on fabric");
                                       }else{
                                        $allledgerentrieslist4->addChild("LEDGERNAME","Discount 2".$str);   
                                       } 
                                        $allledgerentrieslist4->addChild("ISDEEMEDPOSITIVE","YES");
                                        $allledgerentrieslist4->addChild("AMOUNT","-".round($obj->discount_2,2,PHP_ROUND_HALF_DOWN));
                                   if($tax_percent != 0){      
                                   $allledgerentrieslist5 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
                                        $allledgerentrieslist5->addChild("LEDGERNAME",$tax_percent."%".$tax_type);
                                        $allledgerentrieslist5->addChild("ISDEEMEDPOSITIVE","No");
                                        $allledgerentrieslist5->addChild("AMOUNT",round($obj->tax,2,PHP_ROUND_HALF_DOWN)); 
                                   }     
                                   $allledgerentrieslist6 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
                                        $allledgerentrieslist6->addChild("LEDGERNAME","Round Off");
                                        //$allledgerentrieslist6->addChild("AMOUNT",$roundoffobj->round_off);                                                                              
                                         if($roundoff > 0){
                                           // $roundoff = $roundoff*(-1);
                                            $allledgerentrieslist6->addChild("ISDEEMEDPOSITIVE","No");
                                            $allledgerentrieslist6->addChild("AMOUNT",$roundoff);
                                        }else if($roundoff < 0){
                                           $allledgerentrieslist6->addChild("ISDEEMEDPOSITIVE","Yes");
                                            $allledgerentrieslist6->addChild("AMOUNT", $roundoff);
                                        }else if($roundoff == 0){
                                           $allledgerentrieslist6->addChild("ISDEEMEDPOSITIVE","Yes");
                                           $allledgerentrieslist6->addChild("AMOUNT",$roundoff);
                                        }
             }
             
         header('Content-Disposition: attachment;filename='.$name);    
         header('Content-Type: application/xml; charset=utf-8');
         echo $envelope->saveXML();  
    }else{ print "No Invoices availabe in the selected date range.";}            
    

?>
