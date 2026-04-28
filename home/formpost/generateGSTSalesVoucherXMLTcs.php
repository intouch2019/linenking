<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once 'lib/users/clsUsers.php';

//here sales@ is total_mrp

extract($_GET);
//print_r($_GET); exit();

$db = new DBConn();
$startdate = $db->safe(yymmdd($d1));
$enddate = $db->safe(yymmdd($d2));
$user = getCurrUser();
$userpage = new clsUsers();
$pagecode = $db->safe($_SESSION['pagecode']);
//$checkomscofo=$_GET["checkomscofo"];
$page = $db->fetchObject("select * from it_pages where pagecode = $pagecode");
$db->closeConnection();
if($page){
    $allowed = $userpage->isAuthorized($user->id, $page->pagecode);
    if (!$allowed) { header("Location: ".DEF_SITEURL."unauthorized"); return; }
}else{ header("Location:".DEF_SITEURL."nopagefound"); return; }
    $envelope = new SimpleXMLElement('<ENVELOPE/>');    
    $dt1 = str_replace( "-", "", $d1);
    $dt2 = str_replace( "-", "", $d2);
    $fname = "SalesVoucher_TCS_".$dt1."_".$dt2.".xml";
//    $checkomscofo=$_GET["checkomscofo"];
//    if($checkomscofo==1){
//       $whquery= "i.is_omscofo =1 and";
//    }else{
//        $whquery= "i.is_omscofo=0 and";
//    }
//        $stateid=$_GET["stateid"];
//    if($stateid){
//       $wherequery= "c.state_id=$stateid and";
//    }else{
//        $wherequery= "";
//    }
    //echo 'hi';
    $query = "select i.*, i.id as invid, c.* FROM it_invoices i , it_codes c  WHERE c.id = i.store_id and  i.invoice_type = 0 and i.invoice_dt >= '2020-10-01 00:00:00' and date(i.invoice_dt) >= $startdate  and date(i.invoice_dt) <= $enddate   order by invoice_no";
//    if($stateid){
//        $query = "select i.store_id, i.brand_type, i.region_id, i.id, i.store_name, i.round_off, i.tcs_0075pct, i.invoice_no, i.invoice_dt, i.invoice_amt, i.igst_total FROM it_invoices i,it_codes c  WHERE c.id=i.store_id and i.invoice_type = 0 and $whquery $wherequery i.invoice_dt >= '2020-10-01 00:00:00' and date(i.invoice_dt) >= $startdate  and date(i.invoice_dt) <= $enddate   order by invoice_no";
//    }else{
//        $query = "select i.store_id, i.brand_type, i.region_id, i.id, i.store_name, i.round_off, i.tcs_0075pct, i.invoice_no, i.invoice_dt, i.invoice_amt, i.igst_total FROM it_invoices i where i.invoice_type = 0 and $whquery i.invoice_dt >= '2020-10-01 00:00:00' and date(i.invoice_dt) >= $startdate  and date(i.invoice_dt) <= $enddate   order by invoice_no";
//    }
//    $query = "select i.* FROM it_invoices i inner join it_codes c on i.store_id=c.id  WHERE  i.invoice_type = 0  and $whquery  i.invoice_dt >= '2020-10-01 00:00:00' and date(i.invoice_dt) >= $startdate  and date(i.invoice_dt) <= $enddate   order by invoice_no";
   
    //error_log("\nSaleXML".$query,3,"tmp.txt");
    
//    print $query;
//    exit();
    $db = new DBConn();
    $objs = $db->fetchObjectArray($query);
    $db->closeConnection();

    if($objs){
        $header = $envelope->addChild("HEADER");
        $header->addChild("TALLYREQUEST","Import Data");
        $body = $envelope->addChild("BODY");
            $importdata = $body->addChild("IMPORTDATA");
                $reqdesc = $importdata->addChild("REQUESTDESC");//"REPORTNAME","Vouchers"
                $reqdesc->addChild("REPORTNAME","Vouchers");
                        $staticvariable = $reqdesc->addChild("STATICVARIABLES");
                        $staticvariable->addChild("SVCURRENTCOMPANY","Fashionking Brands Pvt. Ltd.");
                $reqdata = $importdata->addChild("REQUESTDATA");
                 foreach($objs as $obj){ 
//                     echo '.<br>.';
//                     echo $obj->store_id;
//                      echo '.<br>.';
                     $dealerDisc = 0;
                     $sbquery ="select d.dealer_discount, d.store_id from it_ck_storediscount d, it_invoices i where d.store_id=$obj->store_id and i.brand_type=0";
//                     echo $sbquery;
                     
                  $dealerDiscountObj=$db->fetchObject("select d.dealer_discount, d.store_id from it_ck_storediscount d, it_invoices i where d.store_id=$obj->store_id and i.brand_type=0");
                  if(isset($dealerDiscountObj)){
                       $dealerDisc = $dealerDiscountObj->dealer_discount;
                  }
              // $regionsid=$db->fetchObject("select region_id from it_codes where id=$obj->store_id");
           //    $region=$regionsid->region_id;
//             $brandtype=$obj->brand_type;
//             if($brandtype==0){
//                 
//                 $brand_name="Cotton King";
//             }
//             else if($brandtype==1){
                 $brand_name="Linenking";
                 
//             }
             
//             else{
//                    $brand_name="NA";
//                 
//             }
             
            
             $region=$obj->region_id;
             if($region==0){
                 $regions="NA";
                 
             }else{
                $region_name=$db->fetchObject("select region from region where id=$region");
                $regions= $region_name->region." "."Region";
                    
             }
             //echo $region_name->region; 
               //return;
//               print_r($obj);exit();
             $brandtype=1;
                    if(isset($obj) && !empty($obj) && $obj != null){
                          $db = new DBConn();
                          $tax_5_obj =$db->fetchObject("select a.id,a.invoice_no,a.invoice_dt,sum(b.taxable_value+b.cgst+b.sgst+b.igst) as taxable_valwithtax,"
                                  . "sum(b.quantity) as qty,sum(b.total_price_qty) as total_price_qty,round(sum(b.discount_val),2) as discount_val,a.discount_2,"
                                  . "sum(b.cgst+b.sgst+b.igst) as total_tax, a.createtime, round(sum(b.total_rate_qty),2) as Rate, sum(b.taxable_value) as total_taxable_val,"
                                  . "sum(cgst) as cgst, sum(sgst) as sgst, sum(igst) as igst,b.tax_rate*100,sum(total_additional_disc_val) as add_disc from it_invoices a,it_invoice_items b "
                                  . "where a.id=b.invoice_id and b.invoice_id=$obj->invid and b.tax_rate*100 = 5 group by b.tax_rate,a.id order by invoice_dt"); 
//                          
//                          print_r("select a.id,a.invoice_no,a.invoice_dt,sum(b.taxable_value+b.cgst+b.sgst+b.igst) as taxable_valwithtax,"
//                                  . "sum(b.quantity) as qty,sum(b.total_price_qty) as total_price_qty,round(sum(b.discount_val),2) as discount_val,a.discount_2,"
//                                  . "sum(b.cgst+b.sgst+b.igst) as total_tax, a.createtime, round(sum(b.total_rate_qty),2) as Rate, sum(b.taxable_value) as total_taxable_val,"
//                                  . "sum(cgst) as cgst, sum(sgst) as sgst, sum(igst) as igst,b.tax_rate*100,sum(total_additional_disc_val) as add_disc from it_invoices a,it_invoice_items b "
//                                  . "where a.id=b.invoice_id and b.invoice_id=$obj->invid and b.tax_rate*100 = 5 group by b.tax_rate,a.id order by invoice_dt");exit();
//                          $query="select a.id,a.invoice_no,a.invoice_dt,sum(b.taxable_value+b.cgst+b.sgst+b.igst) as taxable_valwithtax,"
//                                  . "sum(b.quantity) as qty,sum(b.total_price_qty) as total_price_qty,round(sum(b.discount_val),2) as discount_val,a.discount_2,"
//                                  . "sum(b.cgst+b.sgst+b.igst) as total_tax, a.createtime, round(sum(b.total_rate_qty),2) as Rate, sum(b.taxable_value) as total_taxable_val,"
//                                  . "sum(cgst) as cgst, sum(sgst) as sgst, sum(igst) as igst,b.tax_rate*100,sum(total_additional_disc_val) as add_disc from it_invoices a,it_invoice_items b "
//                                  . "where a.id=b.invoice_id and b.invoice_id=$obj->invid and b.tax_rate*100 = 5 group by b.tax_rate,a.id order by invoice_dt";
                          
//                          echo $query; exit();
                          $db->closeConnection();
                          $db = new DBConn();
                          $tax_12_obj =$db->fetchObject("select a.id,a.invoice_no,a.invoice_dt,sum(b.taxable_value+b.cgst+b.sgst+b.igst) as taxable_valwithtax,"
                                  . "sum(b.quantity) as qty,sum(b.total_price_qty) as total_price_qty,round(sum(b.discount_val),2) as discount_val,a.discount_2,"
                                  . "sum(b.cgst+b.sgst+b.igst) as total_tax, a.createtime, round(sum(b.total_rate_qty),2) as Rate, sum(b.taxable_value) as total_taxable_val,"
                                  . "sum(cgst) as cgst, sum(sgst) as sgst, sum(igst) as igst,b.tax_rate*100,sum(total_additional_disc_val) as add_disc from it_invoices a,it_invoice_items b "
                                  . "where a.id=b.invoice_id and b.invoice_id=$obj->invid and b.tax_rate*100 = 12 group by b.tax_rate,a.id order by invoice_dt");
                          $db->closeConnection();
                          $db = new DBConn();
                          $tax_18_obj =$db->fetchObject("select a.id,a.invoice_no,a.invoice_dt,sum(b.taxable_value+b.cgst+b.sgst+b.igst) as taxable_valwithtax,"
                                  . "sum(b.quantity) as qty,sum(b.total_price_qty) as total_price_qty,round(sum(b.discount_val),2) as discount_val,a.discount_2,"
                                  . "sum(b.cgst+b.sgst+b.igst) as total_tax, a.createtime, round(sum(b.total_rate_qty),2) as Rate, sum(b.taxable_value) as total_taxable_val,"
                                  . "sum(cgst) as cgst, sum(sgst) as sgst, sum(igst) as igst,truncate(b.tax_rate*100,0),sum(total_additional_disc_val) as add_disc from it_invoices a,it_invoice_items b "
                                  . "where a.id=b.invoice_id and b.invoice_id=$obj->invid and b.tax_rate = 0.18 group by b.tax_rate,a.id order by invoice_dt");
                        $db->closeConnection();
                        $db = new DBConn();
                          
                          $tax_28_obj =$db->fetchObject("select a.id,a.invoice_no,a.invoice_dt,sum(b.taxable_value+b.cgst+b.sgst+b.igst) as taxable_valwithtax,"
                                  . "sum(b.quantity) as qty,sum(b.total_price_qty) as total_price_qty,round(sum(b.discount_val),2) as discount_val,a.discount_2,"
                                  . "sum(b.cgst+b.sgst+b.igst) as total_tax, a.createtime, round(sum(b.total_rate_qty),2) as Rate, sum(b.taxable_value) as total_taxable_val,"
                                  . "sum(cgst) as cgst, sum(sgst) as sgst, sum(igst) as igst,truncate(b.tax_rate*100,0),sum(total_additional_disc_val) as add_disc from it_invoices a,it_invoice_items b "
                                  . "where a.id=b.invoice_id and b.invoice_id=$obj->invid and b.tax_rate = 0.28 group by b.tax_rate,a.id order by invoice_dt");
                        $db->closeConnection();
                        $db = new DBConn();
                          $itemobj = $db->fetchObject("select * from it_invoice_items where invoice_id = $obj->invid ");
                         $db->closeConnection();
                          $name =$obj->tally_name;
                          $filternmme = preg_replace('/[^a-zA-Z0-9\s]/i',' ',$name);
                          $sname= $filternmme;
                          //$sname = "sample Co-J.M Road";
                          
//                        if(isset($obj->round_off) && $obj->round_off != NULL){
//                            $roundoff=$obj->round_off;
//                           // $roundoff=0.0;
//                        }else{
//                            $roundoff=0.0;
//                        }
                        
                        
                        
                     $roundoff= round($obj->invoice_amt - $obj->net_amount,2);
                        
                        if(isset($obj->tcs_0075pct) && $obj->tcs_0075pct != NULL ){
                          
                            $tcs_0075pct=$obj->tcs_0075pct;
                      
                        }else{
                            $tcs_0075pct=0.0;
                        }
                        
//                        $tcs_0075pct_Apply=false;
//                        if(isset($obj->tcs_0075pct) && $obj->tcs_0075pct != NULL && $obj->tcs_0075pct != "0" || $obj->tcs_0075pct != "0.0"){
//                           if( $obj->tcs_0075pct <= "0"){
//                               $tcs_0075pct=0.0;
//                           }else{
//                            $tcs_0075pct=$obj->tcs_0075pct;
//                            $tcs_0075pct_Apply=true;
//                           }
//                           // $roundoff=0.0;
//                        }else{
//                            $tcs_0075pct=0.0;
//                        }
                      //  $tcs_0075pct_Apply=false;
                   
                        //$roundoff = round((round($obj->invoice_amt,2,PHP_ROUND_HALF_DOWN)-(round($obj->rate_subtotal,2,PHP_ROUND_HALF_DOWN)-round($obj->discount_val,2,PHP_ROUND_HALF_DOWN)+round($obj->cgst_total,2,PHP_ROUND_HALF_DOWN)+round($obj->sgst_total,2,PHP_ROUND_HALF_DOWN)+round($obj->igst_total,2,PHP_ROUND_HALF_DOWN))),2,PHP_ROUND_HALF_DOWN);
                       //$roundoff = round((round($obj->invoice_amt,2,PHP_ROUND_HALF_DOWN)-(round($roundrate,2,PHP_ROUND_HALF_DOWN)-round($rounddiscount,2,PHP_ROUND_HALF_DOWN)+round($roundcgst,2,PHP_ROUND_HALF_DOWN)+round($roundsgst,2,PHP_ROUND_HALF_DOWN)+round($roundigst,2,PHP_ROUND_HALF_DOWN))),2,PHP_ROUND_HALF_DOWN);

                       //$roundoff = round((($obj->invoice_amt)-($roundrate)-($rounddiscount)+($roundcgst)+($roundsgst)+($roundigst)),2,PHP_ROUND_HALF_DOWN);
                        

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
                                if(strpos($obj->invoice_no, "FP") === 0){
                                     $voucher->addChild("VOUCHERTYPENAME","Sales Other");
                                }else{
                                    $storeid = $obj->store_id;
                                    $stquery = "select store_name as omscofo_store from it_codes where is_omscofo=1 and id=$storeid";
//                                    echo $stquery;
                                    $storeObj = $db->fetchObject($stquery);
//                                    print_r($storeObj); exit();
                                    
                                    if(isset($storeObj) && !empty($storeObj)){
                                    $oms_store = trim($storeObj->omscofo_store);
                                    $name_oms_store = explode('-', $oms_store);
                                    $desiredOmsName = trim($name_oms_store[0]);
                                            $voucher->addChild("VOUCHERTYPENAME","Sales-GST $desiredOmsName");
                                    } else {
                                            $voucher->addChild("VOUCHERTYPENAME","Sales-GST");
                                    }        
                                }
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
                                        
                                        
                                          $allCATEGORYALLOCATIONSlist = $allledgerentrieslist1->addChild("CATEGORYALLOCATIONS.LIST");
                                        $allCATEGORYALLOCATIONSlist->addChild("CATEGORY","$brand_name");
                                         $allCATEGORYALLOCATIONSlist->addChild("NAME","$regions");
                                         
                                        
                                        $allbillallocationslist = $allledgerentrieslist1->addChild("BILLALLOCATIONS.LIST");
                                            $allbillallocationslist->addChild("NAME", $obj->invoice_no);                                                                               
                                            $allbillallocationslist->addChild("AMOUNT", "-".round($obj->invoice_amt,2,PHP_ROUND_HALF_DOWN));                                       
                                   /*--------------------------------------------------------------------------------------*/
                                   //for tax rate 5 calculations
                                   if(isset($tax_5_obj)){
                                       $allledgerentrieslist2 = $voucher->addChild("ALLLEDGERENTRIES.LIST"," "); // this node is for total_mrp
                                    if($dealerDisc == 12){ 
                                      if(trim($obj->igst_total) != "" && trim($obj->igst_total) != "0"){ 
                                         $sales_str = "MRP of 5% Sales OMS - COFO" ;
                                         $discount_str = "Discount for 5% Sales OMS - COFO";
                                         $additional_discont_str="Discount 2 OMS @5% - COFO";
                                      }else{
                                         $sales_str = "MRP of 5% Sales MS - COFO" ; 
                                         $discount_str = "Discount for 5% Sales MS - COFO";
                                         $additional_discont_str="Discount 2 MS @5% - COFO";
                                      }
                                    } else { 
                                      if(trim($obj->igst_total) != "" && trim($obj->igst_total) != "0"){ 
                                         $sales_str = "MRP of 5% Sales OMS" ;
                                         $discount_str = "Discount for 5% Sales OMS";
                                         $additional_discont_str="Discount 2 OMS @5%";
                                      }else{
                                          if($brandtype==1){
                                         $sales_str = "MRP of 5% Sales MS - LK" ; 
                                         $discount_str = "Discount for 5% Sales MS - LK";
                                         $additional_discont_str="Discount 2 MS @5% - LK";
                                          }else{
                                         $sales_str = "MRP of 5% Sales MS" ; 
                                         $discount_str = "Discount for 5% Sales MS";
                                         $additional_discont_str="Discount 2 MS @5%";
                                          }
                                      }
                                    }
                                        $allledgerentrieslist2->addChild("LEDGERNAME",$sales_str);   
                                       //} 
                                        $allledgerentrieslist2->addChild("GSTCLASS");
                                        $allledgerentrieslist2->addChild("ISDEEMEDPOSITIVE","No");
                                       // $allledgerentrieslist2->addChild("AMOUNT",round($obj->rate_subtotal,2,PHP_ROUND_HALF_DOWN));
                                         $allledgerentrieslist2->addChild("AMOUNT",round($tax_5_obj->total_price_qty,2,PHP_ROUND_HALF_DOWN));
                                   $allledgerentrieslist3 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
//                                       if($tax_percent == 0){
//                                        $allledgerentrieslist3->addChild("LEDGERNAME","Discount 1 on fabric");
//                                       }else{
                                        $allledgerentrieslist3->addChild("LEDGERNAME",$discount_str);   
                                      // } 
                                        $allledgerentrieslist3->addChild("GSTCLASS");
                                        $allledgerentrieslist3->addChild("ISDEEMEDPOSITIVE","YES");
                                        //$allledgerentrieslist3->addChild("AMOUNT","-".round($obj->discount_val,2,PHP_ROUND_HALF_DOWN));
                                        $allledgerentrieslist3->addChild("AMOUNT","-".round($tax_5_obj->discount_val,2,PHP_ROUND_HALF_DOWN));
                                        
                                         if(trim($tax_5_obj->add_disc) != "" && trim($tax_5_obj->add_disc) != "0"){ 
                                       $allledgerentrieslist4 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
                                        $allledgerentrieslist4->addChild("LEDGERNAME",$additional_discont_str);
                                        $allledgerentrieslist4->addChild("ISDEEMEDPOSITIVE","YES");
                                        $allledgerentrieslist4->addChild("AMOUNT","-".round($tax_5_obj->add_disc,2,PHP_ROUND_HALF_DOWN));
//                                        
                                       } 
//                                        
                                   //if($tax_percent != 0){   
                                        if(trim($obj->igst_total) == "" || trim($obj->igst_total) == "0"){       
                                            $allledgerentrieslist5 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
                                                 $allledgerentrieslist5->addChild("LEDGERNAME","CGST Collected @ 2.5%");
                                                 $allledgerentrieslist5->addChild("GSTCLASS");
                                                 $allledgerentrieslist5->addChild("ISDEEMEDPOSITIVE","No");
                                                 $allledgerentrieslist5->addChild("AMOUNT",round($tax_5_obj->cgst,2,PHP_ROUND_HALF_DOWN)); 
                                                 //$allledgerentrieslist5->addChild("AMOUNT",round($obj->cgst_total,2,PHP_ROUND_HALF_DOWN)); 

                                            $allledgerentrieslist6 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
                                                 $allledgerentrieslist6->addChild("LEDGERNAME","SGST Collected @ 2.5%");
                                                 $allledgerentrieslist6->addChild("GSTCLASS");
                                                 $allledgerentrieslist6->addChild("ISDEEMEDPOSITIVE","No");
                                                 $allledgerentrieslist6->addChild("AMOUNT",round($tax_5_obj->sgst,2,PHP_ROUND_HALF_DOWN));      
                                                 //$allledgerentrieslist6->addChild("AMOUNT",round($obj->sgst_total,2,PHP_ROUND_HALF_DOWN));      
                                       }     

                                       if(trim($obj->igst_total) != "" && trim($obj->igst_total) != "0"){      
                                        $allledgerentrieslist7 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
                                             $allledgerentrieslist7->addChild("LEDGERNAME","IGST Collected @ 5%");
                                             $allledgerentrieslist7->addChild("GSTCLASS");
                                             $allledgerentrieslist7->addChild("ISDEEMEDPOSITIVE","No");
                                             $allledgerentrieslist7->addChild("AMOUNT",round($tax_5_obj->igst,2,PHP_ROUND_HALF_DOWN)); 
                                            // $allledgerentrieslist7->addChild("AMOUNT",round($obj->igst_total,2,PHP_ROUND_HALF_DOWN)); 
                                       }   
                                   }
                                        //for tax rate 12 calulations
                                   if(isset($tax_12_obj)){
                                       $allledgerentrieslist8 = $voucher->addChild("ALLLEDGERENTRIES.LIST"," "); 
                                     if($dealerDisc == 12){ 
                                           if(trim($obj->igst_total) != "" && trim($obj->igst_total) != "0"){ 
                                         $sales_str12 = "MRP of 12% Sales OMS - COFO" ;
                                         $discount_str12 = "Discount for 12% OMS Sales - COFO";
                                         $additional_discont_str="Discount 2 OMS @12% - COFO";
                                      }else{
                                         $sales_str12 = "MRP of 12% Sales MS - COFO" ; 
                                         $discount_str12 = "Discount For 12% MS Sales - COFO";
                                         $additional_discont_str="Discount 2 MS @12% - COFO";
                                      }
                                    } else {                                 
                                       if(trim($obj->igst_total) != "" && trim($obj->igst_total) != "0"){ 
                                         $sales_str12 = "MRP of 12% Sales OMS" ;
                                         $discount_str12 = "Discount for 12% OMS Sales";
                                         $additional_discont_str="Discount 2 OMS @12%";
                                      }else{
                                          if($brandtype==1){
                                              $sales_str12 = "MRP of 12% Sales MS - LK" ; 
                                         $discount_str12 = "Discount For 12% MS Sales - LK";
                                         $additional_discont_str="Discount 2 MS @12% - LK";
                                          }else{
                                              $sales_str12 = "MRP of 12% Sales MS" ; 
                                         $discount_str12 = "Discount For 12% MS Sales";
                                         $additional_discont_str="Discount 2 MS @12%";
                                          }
                                         
                                      }
                                    }
                                        $allledgerentrieslist8->addChild("LEDGERNAME",$sales_str12);   
                                       //} 
                                        $allledgerentrieslist8->addChild("GSTCLASS");
                                        $allledgerentrieslist8->addChild("ISDEEMEDPOSITIVE","No");
                                       // $allledgerentrieslist2->addChild("AMOUNT",round($obj->rate_subtotal,2,PHP_ROUND_HALF_DOWN));
                                         $allledgerentrieslist8->addChild("AMOUNT",round($tax_12_obj->total_price_qty,2,PHP_ROUND_HALF_DOWN));
                                   $allledgerentrieslist9 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
//                                       if($tax_percent == 0){
//                                        $allledgerentrieslist3->addChild("LEDGERNAME","Discount 1 on fabric");
//                                       }else{
                                        $allledgerentrieslist9->addChild("LEDGERNAME",$discount_str12);   
                                      // } 
                                        $allledgerentrieslist9->addChild("GSTCLASS");
                                        $allledgerentrieslist9->addChild("ISDEEMEDPOSITIVE","YES");
                                        //$allledgerentrieslist3->addChild("AMOUNT","-".round($obj->discount_val,2,PHP_ROUND_HALF_DOWN));
                                        $allledgerentrieslist9->addChild("AMOUNT","-".round($tax_12_obj->discount_val,2,PHP_ROUND_HALF_DOWN));
//                                   $allledgerentrieslist4 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
//                                        $allledgerentrieslist4->addChild("LEDGERNAME","Discount 2".$str);
//                                        $allledgerentrieslist4->addChild("ISDEEMEDPOSITIVE","YES");
//                                        $allledgerentrieslist4->addChild("AMOUNT","-".round($obj->discount_2,2,PHP_ROUND_HALF_DOWN));
                                          if(trim($tax_12_obj->add_disc) != "" && trim($tax_12_obj->add_disc) != "0"){ 
                                       $allledgerentrieslist4 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
                                        $allledgerentrieslist4->addChild("LEDGERNAME",$additional_discont_str);
                                        $allledgerentrieslist4->addChild("ISDEEMEDPOSITIVE","YES");
                                        $allledgerentrieslist4->addChild("AMOUNT","-".round($tax_12_obj->add_disc,2,PHP_ROUND_HALF_DOWN));
//                                        
                                       } 
//                                        
//                                        
                                   //if($tax_percent != 0){   
                                        if(trim($obj->igst_total) == "" || trim($obj->igst_total) == "0"){       
                                            $allledgerentrieslist10 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
                                                 $allledgerentrieslist10->addChild("LEDGERNAME","CGST Collected @ 6%");
                                                 $allledgerentrieslist10->addChild("GSTCLASS");
                                                 $allledgerentrieslist10->addChild("ISDEEMEDPOSITIVE","No");
                                                 $allledgerentrieslist10->addChild("AMOUNT",round($tax_12_obj->cgst,2,PHP_ROUND_HALF_DOWN)); 
                                                 //$allledgerentrieslist5->addChild("AMOUNT",round($obj->cgst_total,2,PHP_ROUND_HALF_DOWN)); 

                                            $allledgerentrieslist11 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
                                                 $allledgerentrieslist11->addChild("LEDGERNAME","SGST Collected @ 6%");
                                                 $allledgerentrieslist11->addChild("GSTCLASS");
                                                 $allledgerentrieslist11->addChild("ISDEEMEDPOSITIVE","No");
                                                 $allledgerentrieslist11->addChild("AMOUNT",round($tax_12_obj->sgst,2,PHP_ROUND_HALF_DOWN));      
                                                 //$allledgerentrieslist6->addChild("AMOUNT",round($obj->sgst_total,2,PHP_ROUND_HALF_DOWN));      
                                       }     

                                       if(trim($obj->igst_total) != "" && trim($obj->igst_total) != "0"){      
                                        $allledgerentrieslist12 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
                                             $allledgerentrieslist12->addChild("LEDGERNAME","IGST Collected @ 12%");
                                             $allledgerentrieslist12->addChild("GSTCLASS");
                                             $allledgerentrieslist12->addChild("ISDEEMEDPOSITIVE","No");
                                             $allledgerentrieslist12->addChild("AMOUNT",round($tax_12_obj->igst,2,PHP_ROUND_HALF_DOWN)); 
                                            // $allledgerentrieslist7->addChild("AMOUNT",round($obj->igst_total,2,PHP_ROUND_HALF_DOWN)); 
                                       } 
                                   }
                                   
                                    //for tax rate 28 calculations
                                   if(isset($tax_28_obj)){
                                       $allledgerentrieslist2 = $voucher->addChild("ALLLEDGERENTRIES.LIST"," "); // this node is for total_mrp
                                       if($dealerDisc == 12){ 
                                          if(trim($obj->igst_total) != "" && trim($obj->igst_total) != "0"){ 
                                         $sales_str = "MRP of 28% Sales OMS - COFO" ;
                                         $discount_str = "Discount for 28% Sales OMS - COFO";
                                         $additional_discont_str="Discount 2 OMS @28% - COFO";
                                      }else{
                                         $sales_str = "MRP of 28% Sales MS - COFO" ; 
                                         $discount_str = "Discount for 28% Sales MS - COFO";
                                         $additional_discont_str="Discount 2 MS @28% - COFO";
                                      }  
                                       } else {
                                           if(trim($obj->igst_total) != "" && trim($obj->igst_total) != "0"){ 
                                         $sales_str = "MRP of 28% Sales OMS" ;
                                         $discount_str = "Discount for 28% Sales OMS";
                                         $additional_discont_str="Discount 2 OMS @28%";
                                      }else{
                                          if($brandtype==1){
                                              $sales_str = "MRP of 28% Sales MS - LK" ; 
                                         $discount_str = "Discount for 28% Sales MS - LK";
                                         $additional_discont_str="Discount 2 MS @28% - LK";
                                          }else{
                                              $sales_str = "MRP of 28% Sales MS" ; 
                                         $discount_str = "Discount for 28% Sales MS";
                                         $additional_discont_str="Discount 2 MS @28%";
                                          }
                                         
                                      } 
                                       }
                                     
                                        $allledgerentrieslist2->addChild("LEDGERNAME",$sales_str);   
                                       //} 
                                        $allledgerentrieslist2->addChild("GSTCLASS");
                                        $allledgerentrieslist2->addChild("ISDEEMEDPOSITIVE","No");
                                       // $allledgerentrieslist2->addChild("AMOUNT",round($obj->rate_subtotal,2,PHP_ROUND_HALF_DOWN));
                                         $allledgerentrieslist2->addChild("AMOUNT",round($tax_28_obj->total_price_qty,2,PHP_ROUND_HALF_DOWN));
                                   $allledgerentrieslist3 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
//                                       if($tax_percent == 0){
//                                        $allledgerentrieslist3->addChild("LEDGERNAME","Discount 1 on fabric");
//                                       }else{
                                        $allledgerentrieslist3->addChild("LEDGERNAME",$discount_str);   
                                      // } 
                                        $allledgerentrieslist3->addChild("GSTCLASS");
                                        $allledgerentrieslist3->addChild("ISDEEMEDPOSITIVE","YES");
                                        //$allledgerentrieslist3->addChild("AMOUNT","-".round($obj->discount_val,2,PHP_ROUND_HALF_DOWN));
                                        $allledgerentrieslist3->addChild("AMOUNT","-".round($tax_28_obj->discount_val,2,PHP_ROUND_HALF_DOWN));
//                                   $allledgerentrieslist4 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
//                                        $allledgerentrieslist4->addChild("LEDGERNAME","Discount 2".$str);
//                                        $allledgerentrieslist4->addChild("ISDEEMEDPOSITIVE","YES");
//                                        $allledgerentrieslist4->addChild("AMOUNT","-".round($obj->discount_2,2,PHP_ROUND_HALF_DOWN));
                                       if(trim($tax_28_obj->add_disc) != "" && trim($tax_28_obj->add_disc) != "0"){ 
                                        $allledgerentrieslist4 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
                                        $allledgerentrieslist4->addChild("LEDGERNAME",$additional_discont_str);
                                        $allledgerentrieslist4->addChild("ISDEEMEDPOSITIVE","YES");
                                        $allledgerentrieslist4->addChild("AMOUNT","-".round($tax_28_obj->add_disc,2,PHP_ROUND_HALF_DOWN));  
                                       } 
                                   //if($tax_percent != 0){   
                                        if(trim($obj->igst_total) == "" || trim($obj->igst_total) == "0"){       
                                            $allledgerentrieslist5 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
                                                 $allledgerentrieslist5->addChild("LEDGERNAME","CGST Collected @ 14%");
                                                 $allledgerentrieslist5->addChild("GSTCLASS");
                                                 $allledgerentrieslist5->addChild("ISDEEMEDPOSITIVE","No");
                                                 $allledgerentrieslist5->addChild("AMOUNT",round($tax_28_obj->cgst,2,PHP_ROUND_HALF_DOWN)); 
                                                 //$allledgerentrieslist5->addChild("AMOUNT",round($obj->cgst_total,2,PHP_ROUND_HALF_DOWN)); 

                                            $allledgerentrieslist6 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
                                                 $allledgerentrieslist6->addChild("LEDGERNAME","SGST Collected @ 14%");
                                                 $allledgerentrieslist6->addChild("GSTCLASS");
                                                 $allledgerentrieslist6->addChild("ISDEEMEDPOSITIVE","No");
                                                 $allledgerentrieslist6->addChild("AMOUNT",round($tax_28_obj->sgst,2,PHP_ROUND_HALF_DOWN));      
                                                 //$allledgerentrieslist6->addChild("AMOUNT",round($obj->sgst_total,2,PHP_ROUND_HALF_DOWN));      
                                       }     

                                       if(trim($obj->igst_total) != "" && trim($obj->igst_total) != "0"){      
                                        $allledgerentrieslist7 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
                                             $allledgerentrieslist7->addChild("LEDGERNAME","IGST Collected @ 28%");
                                             $allledgerentrieslist7->addChild("GSTCLASS");
                                             $allledgerentrieslist7->addChild("ISDEEMEDPOSITIVE","No");
                                             $allledgerentrieslist7->addChild("AMOUNT",round($tax_28_obj->igst,2,PHP_ROUND_HALF_DOWN)); 
                                            // $allledgerentrieslist7->addChild("AMOUNT",round($obj->igst_total,2,PHP_ROUND_HALF_DOWN)); 
                                       }   
                                   }
                                      //for tax rate 18 calculations
                                   if(isset($tax_18_obj)){
                                       $allledgerentrieslist2 = $voucher->addChild("ALLLEDGERENTRIES.LIST"," "); // this node is for total_mrp
                                    if($dealerDisc == 12){ 
                                       if(trim($obj->igst_total) != "" && trim($obj->igst_total) != "0"){ 
                                         $sales_str = "MRP of 18% Sales OMS - COFO" ;
                                         $discount_str = "Discount for 18% Sales OMS - COFO";
                                         $additional_discont_str="Discount 2 OMS @18% - COFO";
                                      }else{
                                         $sales_str = "MRP of 18% Sales MS - COFO" ; 
                                         $discount_str = "Discount for 18% Sales MS - COFO";
                                         $additional_discont_str="Discount 2 MS @18% - COFO";
                                      } 
                                    } else {
                                      if(trim($obj->igst_total) != "" && trim($obj->igst_total) != "0"){ 
                                         $sales_str = "MRP of 18% Sales OMS" ;
                                         $discount_str = "Discount for 18% Sales OMS";
                                         $additional_discont_str="Discount 2 OMS @18%";
                                      }else{
                                          if($brandtype==1){
                                              $sales_str = "MRP of 18% Sales MS - LK" ; 
                                         $discount_str = "Discount for 18% Sales MS - LK";
                                         $additional_discont_str="Discount 2 MS @18% - LK";
                                          }else{
                                              $sales_str = "MRP of 18% Sales MS" ; 
                                         $discount_str = "Discount for 18% Sales MS";
                                         $additional_discont_str="Discount 2 MS @18%";
                                          }
                                         
                                      }  
                                    }  
                                      
                                        $allledgerentrieslist2->addChild("LEDGERNAME",$sales_str);   
                                       //} 
                                        $allledgerentrieslist2->addChild("GSTCLASS");
                                        $allledgerentrieslist2->addChild("ISDEEMEDPOSITIVE","No");
                                       // $allledgerentrieslist2->addChild("AMOUNT",round($obj->rate_subtotal,2,PHP_ROUND_HALF_DOWN));
                                         $allledgerentrieslist2->addChild("AMOUNT",round($tax_18_obj->total_price_qty,2,PHP_ROUND_HALF_DOWN));
                                   $allledgerentrieslist3 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
//                                       if($tax_percent == 0){
//                                        $allledgerentrieslist3->addChild("LEDGERNAME","Discount 1 on fabric");
//                                       }else{
                                        $allledgerentrieslist3->addChild("LEDGERNAME",$discount_str);   
                                      // } 
                                        $allledgerentrieslist3->addChild("GSTCLASS");
                                        $allledgerentrieslist3->addChild("ISDEEMEDPOSITIVE","YES");
                                        //$allledgerentrieslist3->addChild("AMOUNT","-".round($obj->discount_val,2,PHP_ROUND_HALF_DOWN));
                                        $allledgerentrieslist3->addChild("AMOUNT","-".round($tax_18_obj->discount_val,2,PHP_ROUND_HALF_DOWN));
//                                   $allledgerentrieslist4 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
//                                        $allledgerentrieslist4->addChild("LEDGERNAME","Discount 2".$str);
//                                        $allledgerentrieslist4->addChild("ISDEEMEDPOSITIVE","YES");
//                                        $allledgerentrieslist4->addChild("AMOUNT","-".round($obj->discount_2,2,PHP_ROUND_HALF_DOWN));
                                        if(trim($tax_18_obj->add_disc) != "" && trim($tax_18_obj->add_disc) != "0"){ 
                                            $allledgerentrieslist4 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
                                            $allledgerentrieslist4->addChild("LEDGERNAME",$additional_discont_str);
                                            $allledgerentrieslist4->addChild("ISDEEMEDPOSITIVE","YES");
                                            $allledgerentrieslist4->addChild("AMOUNT","-".round($tax_18_obj->add_disc,2,PHP_ROUND_HALF_DOWN));  
                                        }
                                   //if($tax_percent != 0){   
                                        if(trim($obj->igst_total) == "" || trim($obj->igst_total) == "0"){       
                                            $allledgerentrieslist5 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
                                                 $allledgerentrieslist5->addChild("LEDGERNAME","CGST Collected @ 9%");
                                                 $allledgerentrieslist5->addChild("GSTCLASS");
                                                 $allledgerentrieslist5->addChild("ISDEEMEDPOSITIVE","No");
                                                 $allledgerentrieslist5->addChild("AMOUNT",round($tax_18_obj->cgst,2,PHP_ROUND_HALF_DOWN)); 
                                                 //$allledgerentrieslist5->addChild("AMOUNT",round($obj->cgst_total,2,PHP_ROUND_HALF_DOWN)); 

                                            $allledgerentrieslist6 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
                                                 $allledgerentrieslist6->addChild("LEDGERNAME","SGST Collected @ 9%");
                                                 $allledgerentrieslist6->addChild("GSTCLASS");
                                                 $allledgerentrieslist6->addChild("ISDEEMEDPOSITIVE","No");
                                                 $allledgerentrieslist6->addChild("AMOUNT",round($tax_18_obj->sgst,2,PHP_ROUND_HALF_DOWN));      
                                                 //$allledgerentrieslist6->addChild("AMOUNT",round($obj->sgst_total,2,PHP_ROUND_HALF_DOWN));      
                                       }     

                                       if(trim($obj->igst_total) != "" && trim($obj->igst_total) != "0"){      
                                        $allledgerentrieslist7 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
                                             $allledgerentrieslist7->addChild("LEDGERNAME","IGST Collected @ 18%");
                                             $allledgerentrieslist7->addChild("GSTCLASS");
                                             $allledgerentrieslist7->addChild("ISDEEMEDPOSITIVE","No");
                                             $allledgerentrieslist7->addChild("AMOUNT",round($tax_18_obj->igst,2,PHP_ROUND_HALF_DOWN)); 
                                            // $allledgerentrieslist7->addChild("AMOUNT",round($obj->igst_total,2,PHP_ROUND_HALF_DOWN)); 
                                       }   
                                   }  
                                       /*--------------------------------------------------------------------------------------*/
                                   $allledgerentrieslist13 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
                                        $allledgerentrieslist13->addChild("LEDGERNAME","Round Off");
                                       //  $allledgerentrieslist6->addChild("AMOUNT",$roundoffobj->round_off);
                                        if($roundoff > 0){
                                           // $roundoff = $roundoff*(-1);
                                            $allledgerentrieslist13->addChild("ISDEEMEDPOSITIVE","No");
                                            $allledgerentrieslist13->addChild("AMOUNT",$roundoff);
                                        }
                                        else if($roundoff < 0){
                                           $allledgerentrieslist13->addChild("ISDEEMEDPOSITIVE","Yes");
                                            $allledgerentrieslist13->addChild("AMOUNT", $roundoff);
                                        }else if($roundoff == 0){
                                           $allledgerentrieslist13->addChild("ISDEEMEDPOSITIVE","Yes");
                                           $allledgerentrieslist13->addChild("AMOUNT",$roundoff);
                                        }
                                        
                                        
                                   
                                            $allledgerentrieslist14 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
                                            $allledgerentrieslist14->addChild("LEDGERNAME","TCS Colleted On Sales");
                                            $allledgerentrieslist14->addChild("GSTCLASS");
                                            $allledgerentrieslist14->addChild("ISDEEMEDPOSITIVE","No");
                                            $allledgerentrieslist14->addChild("AMOUNT",$tcs_0075pct);
                                
//                                        if($tcs_0075pct_Apply==true){
//                                          $allledgerentrieslist14 = $voucher->addChild("ALLLEDGERENTRIES.LIST");
//                                        $allledgerentrieslist14->addChild("LEDGERNAME","TCS Colleted On Sales");
//                                       //  $allledgerentrieslist6->addChild("AMOUNT",$roundoffobj->round_off);
//                                        if($tcs_0075pct > 0){
//                                           // $roundoff = $roundoff*(-1);
//                                             $allledgerentrieslist14->addChild("GSTCLASS");
//                                            $allledgerentrieslist14->addChild("ISDEEMEDPOSITIVE","No");
//                                            $allledgerentrieslist14->addChild("AMOUNT",$tcs_0075pct);
//                                        }   
//                                      }
                                      
                                                                              
                 }                  
             }
//             exit();
//    print_r($envelope);exit();
         header('Content-Disposition: attachment;filename='.$fname);    
         header('Content-Type: application/xml; charset=utf-8');
         echo $envelope->saveXML();  
    }else{ print "No Invoices availabe in the selected date range.";}        
    

?>