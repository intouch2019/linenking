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
//echo "select * from it_pages where pagecode = $pagecode"; exit();//
$page = $db->fetchObject("select * from it_pages where pagecode = $pagecode");
if($page){
    $allowed = $userpage->isAuthorized($user->id, $page->pagecode);
    if (!$allowed) { header("Location: ".DEF_SITEURL."unauthorized"); return; }
}else{ header("Location:".DEF_SITEURL."nopagefound"); return; }



    $envelope = new SimpleXMLElement('<ENVELOPE/>');    
    $dt1 = str_replace( "-", "", $d1);
    $dt2 = str_replace( "-", "", $d2);
    $name = "DGCreditVoucherGST_".$dt1."_".$dt2.".xml";
    $query = "select i.* FROM it_portalinv_creditnote i  WHERE  i.invoice_type = 5 and i.approve_dt >= '2018-01-01 00:00:00' and date(i.approve_dt) >= $startdate  and date(i.approve_dt) <= $enddate and i.is_approved=1 order by invoice_no";
//   echo $query;    exit();//
    //error_log("\nSaleXML".$query,3,"tmp.txt");//
    $objs = $db->fetchObjectArray($query);
    if($objs){
        $header = $envelope->addChild("HEADER");
        $header->addChild("TALLYREQUEST","Import Data");
        $body = $envelope->addChild("BODY");
            $importdata = $body->addChild("IMPORTDATA");
                $reqdesc = $importdata->addChild("REQUESTDESC");//"TALLYNAME","Vouchers"
                $reqdesc->addChild("TALLYNAME","Vouchers");
                        $staticvariable = $reqdesc->addChild("STATICVARIABLES");
                        $staticvariable->addChild("SVCURRENTCOMPANY","Cotton King Pvt. Ltd. 16-17");
                $reqdata = $importdata->addChild("REQUESTDATA");
                 foreach($objs as $obj){ 
                    if(isset($obj) && !empty($obj) && $obj != null){

                          $tax_5_obj =$db->fetchObject("select a.id,a.invoice_no,a.approve_dt,sum(b.taxable_value+b.cgst+b.sgst+b.igst) as taxable_valwithtax,"
                                  . "sum(b.quantity) as qty,sum(b.price*b.quantity) as total_price_qty,round(sum(b.discount_val),2) as discount_val ,"
                                  . "sum(b.cgst+b.sgst+b.igst) as total_tax, a.createtime, round(sum(b.total_rate_qty),2) as Rate, sum(b.taxable_value) as total_taxable_val,"
                                  . "sum(cgst) as cgst, sum(sgst) as sgst, sum(igst) as igst  from it_portalinv_creditnote a,it_portalinv_items_creditnote b "
                                  . "where a.id=b.invoice_id and b.invoice_id=$obj->id and b.tax_rate*100 = 5 group by b.tax_rate,a.id order by approve_dt");  
//                          $q="select a.id,a.invoice_no,a.approve_dt,sum(b.taxable_value+b.cgst+b.sgst+b.igst) as taxable_valwithtax,"
//                                  . "sum(b.quantity) as qty,sum(b.price*b.quantity) as total_price_qty,round(sum(b.discount_val),2) as discount_val, "
//                                  . "sum(b.cgst+b.sgst+b.igst) as total_tax, a.createtime, round(sum(b.total_rate_qty),2) as Rate, sum(b.taxable_value) as total_taxable_val,"
//                                  . "sum(cgst) as cgst, sum(sgst) as sgst, sum(igst) as igst  from it_portalinv_creditnote a,it_portalinv_items_creditnote b "
//                                  . "where a.id=b.invoice_id and b.invoice_id=$obj->id and b.tax_rate*100 = 5 group by b.tax_rate,a.id order by approve_dt";
//                          echo $q;                          exit();
                          $tax_12_obj =$db->fetchObject("select a.id,a.invoice_no,a.approve_dt,sum(b.taxable_value+b.cgst+b.sgst+b.igst) as taxable_valwithtax,"
                                  . "sum(b.quantity) as qty,sum(b.price*b.quantity) as total_price_qty,round(sum(b.discount_val),2) as discount_val ,"
                                  . "sum(b.cgst+b.sgst+b.igst) as total_tax, a.createtime, round(sum(b.total_rate_qty),2) as Rate, sum(b.taxable_value) as total_taxable_val,"
                                  . "sum(cgst) as cgst, sum(sgst) as sgst, sum(igst) as igst  from it_portalinv_creditnote a,it_portalinv_items_creditnote b "
                                  . "where a.id=b.invoice_id and b.invoice_id=$obj->id and b.tax_rate*100 = 12 group by b.tax_rate,a.id order by approve_dt");
                        
                          $itemobj = $db->fetchObject("select * from it_portalinv_items_creditnote where invoice_id = $obj->id ");
                        //$storeids= $obj->store_id;
                          //$storename = $db->fetchObject("select tally_name from it_codes where id=$storeids");
                          
                          $sname=$obj->store_name;
                        // $sname=$storename;
//                        
                        if(isset($obj->round_off) && $obj->round_off != NULL){
                            $roundoff=$obj->round_off;
//                            $roundoff=0.0;
                        }else{
                            $roundoff=0.0;
                        }
                        //$roundoff = round((round($obj->invoice_amt,2,PHP_ROUND_HALF_DOWN)-(round($obj->rate_subtotal,2,PHP_ROUND_HALF_DOWN)-round($obj->discount_val,2,PHP_ROUND_HALF_DOWN)+round($obj->cgst_total,2,PHP_ROUND_HALF_DOWN)+round($obj->sgst_total,2,PHP_ROUND_HALF_DOWN)+round($obj->igst_total,2,PHP_ROUND_HALF_DOWN))),2,PHP_ROUND_HALF_DOWN);
                       //$roundoff = round((round($obj->invoice_amt,2,PHP_ROUND_HALF_DOWN)-(round($roundrate,2,PHP_ROUND_HALF_DOWN)-round($rounddiscount,2,PHP_ROUND_HALF_DOWN)+round($roundcgst,2,PHP_ROUND_HALF_DOWN)+round($roundsgst,2,PHP_ROUND_HALF_DOWN)+round($roundigst,2,PHP_ROUND_HALF_DOWN))),2,PHP_ROUND_HALF_DOWN);

                        //to fetch voucher number
                        //step 1 : Remove the 1st 4 financial yr indications
                        $str2 = substr($obj->invoice_no, 7);
                        
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
                            $voucher->addAttribute("VCHTYPE", "Credit Note GST");
                            $voucher->addAttribute("ACTION", "Create");
                            $dt = date('Y-m-d',strtotime($obj->approve_dt));
                               $invdate = preg_replace("/[^0-9]+/", "", $dt);
                                $voucher->addChild("DATE",$invdate);
                                $voucher->addChild("VOUCHERTYPENAME","Credit Note GST");
                                $voucher->addChild("REFERENCE",$obj->invoice_no);
                                $voucher->addChild("VOUCHERNUMBER",$vch_no);
                                $voucher->addChild("PARTYLEDGERNAME",$sname);
                                $voucher->addChild("PERSISTEDVIEW","Accounting Voucher View");
                                    $allledgerentrieslist1 = $voucher->addChild("ALLLEDGERENTRIES.LIST"); // this node for inv amt
                                        $allledgerentrieslist1->addChild("LEDGERNAME", $sname);
                                        $allledgerentrieslist1->addChild("GSTCLASS");
                                        $allledgerentrieslist1->addChild("ISDEEMEDPOSITIVE","No");
                                        $allledgerentrieslist1->addChild("AMOUNT",round($obj->invoice_amt,2,PHP_ROUND_HALF_DOWN));
                                        $allledgerentrieslist1->addChild("BANKALLOCATIONS.LIST"," ");
                                        $allbillallocationslist = $allledgerentrieslist1->addChild("BILLALLOCATIONS.LIST");
                                            $allbillallocationslist->addChild("NAME", $obj->invoice_no);                                                                               
                                            $allbillallocationslist->addChild("AMOUNT", round($obj->invoice_amt,2,PHP_ROUND_HALF_DOWN));                                       
                                   

                                   /*--------------------------------------------------------------------------------------*/
                                   //for tax rate 5 calculations
                                   if(isset($tax_5_obj)){
                                       $allledgerentrieslist2 = $voucher->addChild("ALLLEDGERENTRIES.LIST"," "); // this node is for total_mrp
                                      if(trim($obj->igst_total) != "" && trim($obj->igst_total) != "0"){ 
                                         $sales_str = "MRP of 5 % Sales Return-OMS" ;
                                         $discount_str = "Discount for 5% Sales Return OMS";
                                      }else{
                                         $sales_str = "MRP of 5% Sales Return MS" ; 
                                         $discount_str = "Discount for 5% Sales Return MS";
                                      }
                                        $allledgerentrieslist2->addChild("LEDGERNAME",$sales_str);   
                                       //} 
                                        $allledgerentrieslist2->addChild("GSTCLASS");
                                        $allledgerentrieslist2->addChild("ISDEEMEDPOSITIVE","Yes");
                                       // $allledgerentrieslist2->addChild("AMOUNT",round($obj->rate_subtotal,2,PHP_ROUND_HALF_DOWN));
                                         $allledgerentrieslist2->addChild("AMOUNT","-".round($tax_5_obj->total_price_qty,2,PHP_ROUND_HALF_DOWN));
                                   $allledgerentrieslist3 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
//                                       if($tax_percent == 0){
//                                        $allledgerentrieslist3->addChild("LEDGERNAME","Discount 1 on fabric");
//                                       }else{
                                        $allledgerentrieslist3->addChild("LEDGERNAME",$discount_str);   
                                      // } 
                                        $allledgerentrieslist3->addChild("GSTCLASS");
                                        $allledgerentrieslist3->addChild("ISDEEMEDPOSITIVE","No");
                                        //$allledgerentrieslist3->addChild("AMOUNT","-".round($obj->discount_val,2,PHP_ROUND_HALF_DOWN));
                                        $allledgerentrieslist3->addChild("AMOUNT",round($tax_5_obj->discount_val,2,PHP_ROUND_HALF_DOWN));
//                                   $allledgerentrieslist4 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
//                                        $allledgerentrieslist4->addChild("LEDGERNAME","Discount 2".$str);
//                                        $allledgerentrieslist4->addChild("ISDEEMEDPOSITIVE","YES");
//                                        $allledgerentrieslist4->addChild("AMOUNT","-".round($obj->discount_2,2,PHP_ROUND_HALF_DOWN));
                                   //if($tax_percent != 0){   
                                        if(trim($obj->igst_total) == "" || trim($obj->igst_total) == "0"){       
                                            $allledgerentrieslist5 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
                                                 $allledgerentrieslist5->addChild("LEDGERNAME","CGST Sales Return @ 2.5%");
                                                 $allledgerentrieslist5->addChild("GSTCLASS");
                                                 $allledgerentrieslist5->addChild("ISDEEMEDPOSITIVE","Yes");
                                                 $allledgerentrieslist5->addChild("AMOUNT","-".round($tax_5_obj->cgst,2,PHP_ROUND_HALF_DOWN)); 
                                                 //$allledgerentrieslist5->addChild("AMOUNT",round($obj->cgst_total,2,PHP_ROUND_HALF_DOWN)); 

                                            $allledgerentrieslist6 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
                                                 $allledgerentrieslist6->addChild("LEDGERNAME","SGST Sales Return @ 2.5%");
                                                 $allledgerentrieslist6->addChild("GSTCLASS");
                                                 $allledgerentrieslist6->addChild("ISDEEMEDPOSITIVE","Yes");
                                                 $allledgerentrieslist6->addChild("AMOUNT","-".round($tax_5_obj->sgst,2,PHP_ROUND_HALF_DOWN));      
                                                 //$allledgerentrieslist6->addChild("AMOUNT",round($obj->sgst_total,2,PHP_ROUND_HALF_DOWN));      
                                       }     

                                       if(trim($obj->igst_total) != "" && trim($obj->igst_total) != "0"){      
                                        $allledgerentrieslist7 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
                                             $allledgerentrieslist7->addChild("LEDGERNAME","IGST Sales Return @ 5%");
                                             $allledgerentrieslist7->addChild("GSTCLASS");
                                             $allledgerentrieslist7->addChild("ISDEEMEDPOSITIVE","Yes");
                                             $allledgerentrieslist7->addChild("AMOUNT","-".round($tax_5_obj->igst,2,PHP_ROUND_HALF_DOWN)); 
                                            // $allledgerentrieslist7->addChild("AMOUNT",round($obj->igst_total,2,PHP_ROUND_HALF_DOWN)); 
                                       }   
                                   }
                                        //for tax rate 12 calulations
                                   if(isset($tax_12_obj)){
                                       $allledgerentrieslist8 = $voucher->addChild("ALLLEDGERENTRIES.LIST"," "); 
                                       if(trim($obj->igst_total) != "" && trim($obj->igst_total) != "0"){ 
                                         $sales_str12 = "MRP of 12 % Sales Return-OMS" ;
                                         $discount_str12 = "Discount for 12% OMS Sales Return";
                                      }else{
                                         $sales_str12 = "MRP for 12% Sales Return MS" ; 
                                         $discount_str12 = "Discount for 12% Sales Return MS";
                                      }
                                        $allledgerentrieslist8->addChild("LEDGERNAME",$sales_str12);   
                                       //} 
                                        $allledgerentrieslist8->addChild("GSTCLASS");
                                        $allledgerentrieslist8->addChild("ISDEEMEDPOSITIVE","Yes");
                                       // $allledgerentrieslist2->addChild("AMOUNT",round($obj->rate_subtotal,2,PHP_ROUND_HALF_DOWN));
                                         $allledgerentrieslist8->addChild("AMOUNT","-".round($tax_12_obj->total_price_qty,2,PHP_ROUND_HALF_DOWN));
                                   $allledgerentrieslist9 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
//                                       if($tax_percent == 0){
//                                        $allledgerentrieslist3->addChild("LEDGERNAME","Discount 1 on fabric");
//                                       }else{
                                        $allledgerentrieslist9->addChild("LEDGERNAME",$discount_str12);   
                                      // } 
                                        $allledgerentrieslist9->addChild("GSTCLASS");
                                        $allledgerentrieslist9->addChild("ISDEEMEDPOSITIVE","No");
                                        //$allledgerentrieslist3->addChild("AMOUNT","-".round($obj->discount_val,2,PHP_ROUND_HALF_DOWN));
                                        $allledgerentrieslist9->addChild("AMOUNT",round($tax_12_obj->discount_val,2,PHP_ROUND_HALF_DOWN));
//                                   $allledgerentrieslist4 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
//                                        $allledgerentrieslist4->addChild("LEDGERNAME","Discount 2".$str);
//                                        $allledgerentrieslist4->addChild("ISDEEMEDPOSITIVE","YES");
//                                        $allledgerentrieslist4->addChild("AMOUNT","-".round($obj->discount_2,2,PHP_ROUND_HALF_DOWN));
                                   //if($tax_percent != 0){   
                                        if(trim($obj->igst_total) == "" || trim($obj->igst_total) == "0"){       
                                            $allledgerentrieslist10 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
                                                 $allledgerentrieslist10->addChild("LEDGERNAME","CGST Sales Return @ 6%");
                                                 $allledgerentrieslist10->addChild("GSTCLASS");
                                                 $allledgerentrieslist10->addChild("ISDEEMEDPOSITIVE","Yes");
                                                 $allledgerentrieslist10->addChild("AMOUNT","-".round($tax_12_obj->cgst,2,PHP_ROUND_HALF_DOWN)); 
                                                 //$allledgerentrieslist5->addChild("AMOUNT",round($obj->cgst_total,2,PHP_ROUND_HALF_DOWN)); 

                                            $allledgerentrieslist11 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
                                                 $allledgerentrieslist11->addChild("LEDGERNAME","SGST Sales Return @ 6%");
                                                 $allledgerentrieslist11->addChild("GSTCLASS");
                                                 $allledgerentrieslist11->addChild("ISDEEMEDPOSITIVE","Yes");
                                                 $allledgerentrieslist11->addChild("AMOUNT","-".round($tax_12_obj->sgst,2,PHP_ROUND_HALF_DOWN));      
                                                 //$allledgerentrieslist6->addChild("AMOUNT",round($obj->sgst_total,2,PHP_ROUND_HALF_DOWN));      
                                       }     

                                       if(trim($obj->igst_total) != "" && trim($obj->igst_total) != "0"){      
                                        $allledgerentrieslist12 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
                                             $allledgerentrieslist12->addChild("LEDGERNAME","IGST Sales Return @ 12%");
                                             $allledgerentrieslist12->addChild("GSTCLASS");
                                             $allledgerentrieslist12->addChild("ISDEEMEDPOSITIVE","Yes");
                                             $allledgerentrieslist12->addChild("AMOUNT","-".round($tax_12_obj->igst,2,PHP_ROUND_HALF_DOWN)); 
                                            // $allledgerentrieslist7->addChild("AMOUNT",round($obj->igst_total,2,PHP_ROUND_HALF_DOWN)); 
                                       } 
                                   }
                                        
                                       /*--------------------------------------------------------------------------------------*/
                                   $allledgerentrieslist13 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
                                        $allledgerentrieslist13->addChild("LEDGERNAME","Round Off");
                                       //  $allledgerentrieslist6->addChild("AMOUNT",$roundoffobj->round_off);
                                        if($roundoff > 0){
                                           // $roundoff = $roundoff*(-1);
                                            $allledgerentrieslist13->addChild("ISDEEMEDPOSITIVE","Yes");
                                            $allledgerentrieslist13->addChild("AMOUNT","-".$roundoff);
                                        }else if($roundoff < 0){
                                           $allledgerentrieslist13->addChild("ISDEEMEDPOSITIVE","No");
                                            $allledgerentrieslist13->addChild("AMOUNT", $roundoff*-1);
                                        }else if($roundoff == 0){
                                           $allledgerentrieslist13->addChild("ISDEEMEDPOSITIVE","No");
                                           $allledgerentrieslist13->addChild("AMOUNT",$roundoff);
                                        }
//                                        echo 'hiii';exit
                                                                              
                 }                  
             }
         header('Content-Disposition: attachment;filename='.$name);    
         header('Content-Type: application/xml; charset=utf-8');
         echo $envelope->saveXML();  
    }else{ print "No Invoices availabe in the selected date range.";}        
?>
