<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once 'lib/users/clsUsers.php';

//here purchase@ is total_mrp


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
    $name = "PurchaseXML_".$dt1."_".$dt2.".xml";
    $chkclause = " and total_mrp is not null and discount_1 is not null and discount_2 is not null and tax is not null and tax_type is not null and tax_percent is not null ";
//    $spObj = $db->fetchObject("select * from it_codes where id = ".DEF_SP_LIFESTYLE_ID); // S.P. Life Style ID
    $query = " select i.*  FROM it_invoices i WHERE  i.invoice_type = 0 and date(i.invoice_dt) >= $startdate  and date(i.invoice_dt) <= $enddate $chkclause order by id ";
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
//                      echo "<br/>";
//                      print_r($obj);
                       // $tax_type = substr(trim($obj->tax_type),0,3);
                        //error_log("\n TAX TYPE: ".$tax_type."\n",3,"tmp.txt");
                        if($tax_type == 'CST'){ $str = "CST"; }else{ $str = "";}
                       // $tax_percent = $obj->tax_percent * 100 ;
                         //error_log("\n TAX PERCENT: ".$tax_percent."\n",3,"tmp.txt");
//                        $itemobj = $db->fetchObject("select * from it_sp_invoice_items where invoice_id = $obj->id ");
//                        if (strpos($itemobj->item_code,"89000") !== false) {
//                          if(isset($spObj->tally_name) && trim($spObj->tally_name) != ""){ $sname = $spObj->tally_name;}else{ $sname = $spObj->store_name ; }  
//                        }else{
//                         $stObj = $db->fetchObject("select * from it_codes where id = $obj->store_id");
//                         if(isset($obj->tally_name) && trim($obj->tally_name) != ""){ $sname = $obj->tally_name;}else{ $sname = $obj->store_name ; }
//                        } 
                        //$roundoffquery="select round(invoice_amt-(total_mrp-discount_1-discount_2+tax),2) as round_off from it_invoices where invoice_no = '$obj->invoice_no';";
//                        $roundoffquery="select round(round($obj->invoice_amt,2)-(round($obj->total_mrp,2)-round($obj->discount_1,2)-round($obj->discount_2,2)+round($obj->tax,2)),2) as round_off from it_invoices where invoice_no = '$obj->invoice_no';";
//                        $roundoffquery="select round($obj->invoice_amt-($obj->total_mrp-$obj->discount_1-$obj->discount_2 +$obj->tax),2) as round_off from it_invoices where invoice_no = '$obj->invoice_no'";
////                        echo "<br/>$roundoffquery<br/>";
                        $roundoff=0;
                        //$roundoffobj = $db->fetchObject($roundoffquery);
                        /*if(isset($roundoffobj)){
                          $roundoff = $roundoffobj->round_off;
//                          if($roundoff < 0){$roundoff=$roundoff*(-1);}
                        }*/
                        $roundoff = round((round($obj->invoice_amt,2,PHP_ROUND_HALF_DOWN)-(round($obj->total_mrp,2,PHP_ROUND_HALF_DOWN)-round($obj->discount_1,2,PHP_ROUND_HALF_DOWN)-round($obj->discount_2,2,PHP_ROUND_HALF_DOWN)+round($obj->tax,2,PHP_ROUND_HALF_DOWN))),2,PHP_ROUND_HALF_DOWN);
                        $invoice_amt = round($obj->invoice_amt,2,PHP_ROUND_HALF_DOWN);
                        //$invoice_amt= round(($obj->total_mrp - $obj->discount_1 - $obj->discount_2 + $obj->tax),2);
                        $tallymsg = $reqdata->addChild("TALLYMESSAGE");
                            $voucher = $tallymsg->addChild("VOUCHER");                            
                            $dt = date('Y-m-d',strtotime($obj->invoice_dt));
                               $invdate = preg_replace("/[^0-9]+/", "", $dt);
                                $voucher->addChild("DATE",$invdate);
                                $voucher->addChild("VOUCHERTYPENAME","Purchase");
                                $voucher->addChild("PARTYLEDGERNAME","Cotton King Pvt. Ltd.");
                                $voucher->addChild("PERSISTEDVIEW","Accounting Voucher View");
                                    $allledgerentrieslist1 = $voucher->addChild("ALLLEDGERENTRIES.LIST"); // this node for inv amt
                                        $allledgerentrieslist1->addChild("LEDGERNAME", "Cotton King Pvt. Ltd.");
                                        $allledgerentrieslist1->addChild("ISDEEMEDPOSITIVE","No");
                                        $allledgerentrieslist1->addChild("AMOUNT",$invoice_amt);
                                        $allledgerentrieslist1->addChild("BANKALLOCATIONS.LIST"," ");
                                        $allbillallocationslist = $allledgerentrieslist1->addChild("BILLALLOCATIONS.LIST");
                                            $allbillallocationslist->addChild("NAME", $obj->invoice_no);                                                                               
                                            $allbillallocationslist->addChild("AMOUNT",$invoice_amt);                                       
                                   $allledgerentrieslist2 = $voucher->addChild("ALLLEDGERENTRIES.LIST"," "); // this node is for total_mrp
                                        //$allledgerentrieslist2->addChild("LEDGERNAME","Sales@".$taxpercent."%".$taxType);
//                                       if($tax_percent == 0){
//                                        $allledgerentrieslist2->addChild("LEDGERNAME","MRP Fabric Sales");
//                                       }else{
                                        $allledgerentrieslist2->addChild("LEDGERNAME","Purchase @ ".$tax_percent."% MRP");   
//                                       } 
                                        $allledgerentrieslist2->addChild("ISDEEMEDPOSITIVE","Yes");
                                        $allledgerentrieslist2->addChild("ISLASTDEEMEDPOSITIVE","Yes");
                                        $allledgerentrieslist2->addChild("AMOUNT","-".round($obj->total_mrp,2,PHP_ROUND_HALF_DOWN));
                                   $allledgerentrieslist3 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
                                       if($tax_percent == 0){
                                        $allledgerentrieslist3->addChild("LEDGERNAME","Discount on fabric C.K.");
                                       }else{
                                        $allledgerentrieslist3->addChild("LEDGERNAME","Discount 1 From C.K.");   
                                       } 
                                        $allledgerentrieslist3->addChild("ISDEEMEDPOSITIVE","No");
                                        $allledgerentrieslist3->addChild("AMOUNT",round($obj->discount_1,2,PHP_ROUND_HALF_DOWN));
                                   $allledgerentrieslist4 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
                                        $allledgerentrieslist4->addChild("LEDGERNAME","Discount 2 From C.K.");
                                        $allledgerentrieslist4->addChild("ISDEEMEDPOSITIVE","No");
                                        $allledgerentrieslist4->addChild("AMOUNT",round($obj->discount_2,2,PHP_ROUND_HALF_DOWN));
//                                   if($tax_percent != 0){      
                                   $allledgerentrieslist5 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
                                        $allledgerentrieslist5->addChild("LEDGERNAME",$tax_percent."%".$tax_type." Paid(C.K.) ");
                                        $allledgerentrieslist5->addChild("ISDEEMEDPOSITIVE","Yes");
                                        $allledgerentrieslist5->addChild("AMOUNT","-".round($obj->tax,2,PHP_ROUND_HALF_DOWN)); 
//                                   }     
                                       $allledgerentrieslist6 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
                                        $allledgerentrieslist6->addChild("LEDGERNAME","Round Off");
//                                           $allledgerentrieslist5->addChild("ISDEEMEDPOSITIVE","Yes");
//                                            $allledgerentrieslist6->addChild("AMOUNT","-".$roundoff);  
                                        if($roundoff < 0){
                                            $roundoff = $roundoff*(-1);                                        
                                            $allledgerentrieslist6->addChild("ISDEEMEDPOSITIVE","No");
                                            $allledgerentrieslist6->addChild("AMOUNT",$roundoff); 
                                        }else if($roundoff > 0){
                                           $allledgerentrieslist6->addChild("ISDEEMEDPOSITIVE","Yes");
                                            $allledgerentrieslist6->addChild("AMOUNT","-".$roundoff);  
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
