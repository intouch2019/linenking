<?php
//print "Yes";
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once 'lib/users/clsUsers.php';

//here sales@ is total_mrp


extract($_GET);
$dtarr = explode(" - ", $var);
$d1=$dtarr[0];
if($dtarr[1]!=="")
$d2=$dtarr[1];




$db = new DBConn();

$startdate = $db->safe(yymmdd($d1));
if($dtarr[1])
$enddate = $db->safe(yymmdd($d2));

//var_dump($enddate);
$yr_arr=explode($startdate,"-");
$fyr=$yr_arr[0];


if (date('m') <= 3) {
                            //Upto June 2016-2017
     $financial_yr = (date('Y')-1) . '-' . date('Y');
     
           } else {//After June 2017-2018 ....
                                $financial_yr = date('Y') . '-' . (date('Y') + 1);
                            }
                            
                            
                            //$financial_yr="2017-2018";
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
    $name = "DiscountScheme-CreditNote".$dt1."_".$dt2.".xml";
    
    if($dt2>2000)
    {
        $query = " select * FROM it_creditnote_ds WHERE date(createtime) >= $startdate  and date(createtime) <= $enddate  order by id";
        
    }
    else {
        
        $query = " select * FROM it_creditnote_ds WHERE date(createtime) >= $startdate order by id";
    }   
     //print "$query:$dt2";
    //$query = " select * FROM it_creditnote_td WHERE id=11";
    //error_log("\nSaleXML".$query,3,"tmp.txt");
    $objs = $db->fetchObjectArray($query);
    //print_r($objs);
    if($objs){
        $header = $envelope->addChild("HEADER");
        $header->addChild("TALLYREQUEST","Import Data");
        $body = $envelope->addChild("BODY");
            $importdata = $body->addChild("IMPORTDATA");
                $reqdesc = $importdata->addChild("REQUESTDESC");//"REPORTNAME","Vouchers"
                $reqdesc->addChild("REPORTNAME","Vouchers");
                        $staticvariable = $reqdesc->addChild("STATICVARIABLES");
                        $staticvariable->addChild("SVCURRENTCOMPANY","Fashionking Brands Pvt. Ltd. $financial_yr");
                $reqdata = $importdata->addChild("REQUESTDATA");
                 foreach($objs as $obj){ 
                    if(isset($obj) && !empty($obj) && $obj != null){
                         
                          //$sname=$obj->store_name;
                        $squery="select * from it_codes where id=$obj->store_id";
                        $sobj=$db->fetchObject($squery);
                        $sname=$obj->tally_name;
                          $qtr=$obj->qtr;


                        $tallymsg1 = $reqdata->addChild("TALLYMESSAGE");
                            $voucher = $tallymsg1->addChild("VOUCHER");
                            $voucher->addAttribute("VCHTYPE", "Credit Note GST");
                            $voucher->addAttribute("ACTION", "Create");
                            $voucher->addAttribute("OBJVIEW", "Accounting Voucher View");                                                        
                            $dt = date('Y-m-d',strtotime($obj->createtime));
                            $dt1=date('Y-m-d',strtotime($obj->ref_date));
                               $invdate = preg_replace("/[^0-9]+/", "", $dt);
                               $refdate = preg_replace("/[^0-9]+/", "", $dt1);
                               
                               $oldauditentryidslist1 = $voucher->addChild("OLDAUDITENTRYIDS.LIST");
                               $oldauditentryidslist1->addAttribute("TYPE", "Number");
                               $oldauditentryidslist1->addChild("OLDAUDITENTRYIDS", "-1");
                                $voucher->addChild("DATE",$invdate);
                                $voucher->addChild("REFERENCEDATE",$refdate);
                    
                                $num=$obj->qtr;
                                if($num==1)   
                                {
                                    $num=$num."st";
                                }else if($num==2)
                                {
                                    $num=$num."nd";
                                }
                                else if($num==3)
                                {
                                    $num=$num."rd";
                                }
                                else {
                                    $num=$num."th";
                                }
                                $dt3=date('Y-M-d h:i:s',strtotime($obj->createtime));
                               // $nar="Discount Scheme Flat @ 20% Period From 13/07/2018 to 22/07/2018";
                                //$nar="Being Turnover Discount Qtr-".$num." F. Y. $financial_yr";
                                $nar=$obj->ds_remark;
                                $voucher->addChild("NARRATION",$nar);
                                $voucher->addChild("VOUCHERTYPENAME","Credit Note GST");
                                
                                //$voucher->addChild("REFERENCE",$obj->ref_no);
                                if(isset($obj->ref_no))
                                {
                                    $voucher->addChild("REFERENCE",$obj->ref_no);
                                }
                                else {
                                    $voucher->addChild("REFERENCE","  ");
                                }
                                
                                //$voucher->
                                
                                $voucher->addChild("VOUCHERNUMBER",$obj->id);
                                $voucher->addChild("PARTYLEDGERNAME",$sname);
                          
                                
                                ////////FBTPAYMENTTYPE>Default
                                $voucher->addChild("PERSISTEDVIEW","Accounting Voucher View");
                                
                                
                                
                                
                                
                                $allledgerentrieslist1 = $voucher->addChild("ALLLEDGERENTRIES.LIST"); 
                                $oldauditentryidslist1=$allledgerentrieslist1->addChild("OLDAUDITENTRYIDS.LIST");
                                $oldauditentryidslist1->addAttribute("TYPE", "Number");
                                $oldauditentryidslist1->addChild("OLDAUDITENTRYIDS", -1);
                                $allledgerentrieslist1->addChild("LEDGERNAME", $sname);
                                $allledgerentrieslist1->addChild("GSTCLASS");
                                $allledgerentrieslist1->addChild("ISDEEMEDPOSITIVE","No");
                                $allledgerentrieslist1->addChild("LEDGERFROMITEM","No");  
                                $allledgerentrieslist1->addChild("REMOVEZEROENTRIES","No");
                                $allledgerentrieslist1->addChild("ISPARTYLEDGER","Yes");
                                $allledgerentrieslist1->addChild("ISLASTDEEMEDPOSITIVE","No");
                                $gst_total=$obj->taxable_amt+$obj->igst_paid+($obj->cgst_paid*2);
                                $allledgerentrieslist1->addChild("AMOUNT",$gst_total);
                                $allledgerentrieslist1->addChild("VATEXPAMOUNT",$gst_total);
                                
                                $allledgerentrieslist1->addChild("SERVICETAXDETAILS.LIST","  ");
                                
                                $allledgerentrieslist1->addChild("BANKALLOCATIONS.LIST"," ");
                                //BILLALLOCATIONS.LIST
                                $str_length = 4;
                                $num=$obj->id;
                                $str = substr("0000{$num}", -$str_length);
                                $billalloctaionlist1=$allledgerentrieslist1->addChild("BILLALLOCATIONS.LIST");
                                $billalloctaionlist1->addChild("NAME","CN".$obj->cn_no);
                                $billalloctaionlist1->addChild("BILLTYPE", "New Ref");
                                $billalloctaionlist1->addChild("TDSDEDUCTEEISSPECIALRATE", "No");
                                $billalloctaionlist1->addChild("AMOUNT", $gst_total);                          
                                
                                
                                   /*--------------------------------------------------------------------------------------*/
                               if($obj->igst_paid!=0)
                               {
                                   
                                $allledgerentrieslist2 = $voucher->addChild("ALLLEDGERENTRIES.LIST"); 

                                $allledgerentrieslist2->addChild("LEDGERNAME","Discount Scheme Sale IGST @ $obj->taxpct% Net");
                                $allledgerentrieslist2->addChild("GSTCLASS");
                                $allledgerentrieslist2->addChild("ISDEEMEDPOSITIVE","Yes");
                                $allledgerentrieslist2->addChild("LEDGERFROMITEM","No");  
                                $allledgerentrieslist2->addChild("REMOVEZEROENTRIES","No");
                                $allledgerentrieslist2->addChild("ISPARTYLEDGER","No");
                                $allledgerentrieslist2->addChild("ISLASTDEEMEDPOSITIVE","Yes");
                                
                              
                                $allledgerentrieslist2->addChild("AMOUNT",($obj->taxable_amt*(-1)));
                                $allledgerentrieslist2->addChild("VATEXPAMOUNT",($obj->taxable_amt*(-1)));
                            
                                $allledgerentrieslist3 = $voucher->addChild("ALLLEDGERENTRIES.LIST"); 
                                $oldauditentryidslist3=$allledgerentrieslist3->addChild("OLDAUDITENTRYIDS.LIST");
                                $oldauditentryidslist3->addAttribute("TYPE", "Number");
                                $oldauditentryidslist3->addChild("OLDAUDITENTRYIDS", -1);
                                $allledgerentrieslist3->addChild("LEDGERNAME","Discount Scheme Sale IGST @ ".($obj->taxpct)."% Paid");
                                $allledgerentrieslist3->addChild("GSTCLASS");
                                $allledgerentrieslist3->addChild("ISDEEMEDPOSITIVE","Yes");
                                $allledgerentrieslist3->addChild("LEDGERFROMITEM","No");  
                                $allledgerentrieslist3->addChild("REMOVEZEROENTRIES","No");
                                $allledgerentrieslist3->addChild("ISPARTYLEDGER","No");
                                $allledgerentrieslist3->addChild("ISLASTDEEMEDPOSITIVE","Yes");
                   
                                $allledgerentrieslist3->addChild("AMOUNT",($obj->igst_paid*(-1)));
                                $allledgerentrieslist3->addChild("VATEXPAMOUNT",($obj->igst_paid*(-1)));

                               }
                               else
                               {
                                  //Discount Scheme Sale SGST @ 2.5%
                                
                                $allledgerentrieslist2 = $voucher->addChild("ALLLEDGERENTRIES.LIST"); 

                                $allledgerentrieslist2->addChild("LEDGERNAME","Discount Scheme Sale GST @ $obj->taxpct% Net");
                                $allledgerentrieslist2->addChild("GSTCLASS");
                                $allledgerentrieslist2->addChild("ISDEEMEDPOSITIVE","Yes");
                                $allledgerentrieslist2->addChild("LEDGERFROMITEM","No");  
                                $allledgerentrieslist2->addChild("REMOVEZEROENTRIES","No");
                                $allledgerentrieslist2->addChild("ISPARTYLEDGER","No");
                                $allledgerentrieslist2->addChild("ISLASTDEEMEDPOSITIVE","Yes");
                                
                              
                                $allledgerentrieslist2->addChild("AMOUNT",($obj->taxable_amt*(-1)));
                                $allledgerentrieslist2->addChild("VATEXPAMOUNT",($obj->taxable_amt*(-1)));

                                $allledgerentrieslist3 = $voucher->addChild("ALLLEDGERENTRIES.LIST"); 
                                $oldauditentryidslist3=$allledgerentrieslist3->addChild("OLDAUDITENTRYIDS.LIST");
                                $oldauditentryidslist3->addAttribute("TYPE", "Number");
                                $oldauditentryidslist3->addChild("OLDAUDITENTRYIDS", -1);
                                $allledgerentrieslist3->addChild("LEDGERNAME","Discount Scheme Sale SGST @ ".($obj->taxpct/2)."% Paid");
                                $allledgerentrieslist3->addChild("GSTCLASS");
                                $allledgerentrieslist3->addChild("ISDEEMEDPOSITIVE","Yes");
                                $allledgerentrieslist3->addChild("LEDGERFROMITEM","No");  
                                $allledgerentrieslist3->addChild("REMOVEZEROENTRIES","No");
                                $allledgerentrieslist3->addChild("ISPARTYLEDGER","No");
                                $allledgerentrieslist3->addChild("ISLASTDEEMEDPOSITIVE","Yes");
                   
                                $allledgerentrieslist3->addChild("AMOUNT",($obj->cgst_paid*(-1)));
                                $allledgerentrieslist3->addChild("VATEXPAMOUNT",($obj->cgst_paid*(-1)));
                                
//                       
                                
                                
                                //Discount Scheme Sale CGST @ 2.5% 
                                
                                $allledgerentrieslist4 = $voucher->addChild("ALLLEDGERENTRIES.LIST"); 
                                $oldauditentryidslist4=$allledgerentrieslist1->addChild("OLDAUDITENTRYIDS.LIST");
                                $oldauditentryidslist4->addAttribute("TYPE", "Number");
                                $oldauditentryidslist4->addChild("OLDAUDITENTRYIDS", -1);
                                $allledgerentrieslist4->addChild("LEDGERNAME","Discount Scheme Sale CGST @ ".($obj->taxpct/2)."% Paid");
                                $allledgerentrieslist4->addChild("GSTCLASS");
                                $allledgerentrieslist4->addChild("ISDEEMEDPOSITIVE","Yes");
                                $allledgerentrieslist4->addChild("LEDGERFROMITEM","No");  
                                $allledgerentrieslist4->addChild("REMOVEZEROENTRIES","No");
                                $allledgerentrieslist4->addChild("ISPARTYLEDGER","No");
                                $allledgerentrieslist4->addChild("ISLASTDEEMEDPOSITIVE","Yes");
                   
                                $allledgerentrieslist4->addChild("AMOUNT",($obj->cgst_paid*(-1)));
                                $allledgerentrieslist4->addChild("VATEXPAMOUNT",($obj->cgst_paid*(-1)));
                                
                               }

                                                                              
                 }                  
             }
             

//             
         header('Content-Disposition: attachment;filename='.$name);    
 
         header('Content-Type: application/xml; charset=utf-8');
         echo $envelope->saveXML();  
    }else{ print "No Invoices availabe in the selected date range.";}        
    

?>
