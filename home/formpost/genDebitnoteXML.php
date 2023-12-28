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

$d1=$_GET['d1'];
$d2=$_GET['d2'];



$db = new DBConn();

$startdate = $db->safe(yymmdd($d1));
$enddate = $db->safe(yymmdd($d2));
$yr_arr=explode($startdate,"-");
$fyr=$yr_arr[0];


if (date('m') <= 3) {
                            //Upto June 2016-2017
     $financial_yr = (date('Y')-1) . '-' . date('Y');
     
           } else {//After June 2017-2018
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
    $name = "DebitNote".$dt1."_".$dt2.".xml";
    
     $query = "select * FROM it_debit_advice WHERE date(createtime) >= $startdate  and date(createtime) <= $enddate  order by id";
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
                        $sname=$sobj->tally_name;
                          $qtr="0";


                        $tallymsg1 = $reqdata->addChild("TALLYMESSAGE");
                            $voucher = $tallymsg1->addChild("VOUCHER");
                            $voucher->addAttribute("VCHTYPE", "Debit Note Sale Back");
                            $voucher->addAttribute("ACTION", "Create");
                            $voucher->addAttribute("OBJVIEW", "Accounting Voucher View");
                            $Addlist1 = $voucher->addChild("ADDRESS.LIST");
                            $Addlist1->addAttribute("TYPE", "Number");
                            $Addlist1->addChild("ADDRESS", $sobj->address);
                            $dt = date('Y-m-d',strtotime($obj->createtime));
                            $dt1=date('Y-m-d',strtotime($obj->ref_date));
                               $invdate = preg_replace("/[^0-9]+/", "", $dt);
                               $refdate = preg_replace("/[^0-9]+/", "", $dt1);
                               
                               $oldauditentryidslist1 = $voucher->addChild("OLDAUDITENTRYIDS.LIST");
                               $oldauditentryidslist1->addAttribute("TYPE", "Number");
                               $oldauditentryidslist1->addChild("OLDAUDITENTRYIDS", "-1");
                                $voucher->addChild("DATE",$invdate);
                                $voucher->addChild("REFERENCEDATE",$refdate);
                    
                               
                                $dt3=date('Y-M-d h:i:s',strtotime($obj->createtime));
                                $nar="Discount Scheme Flat @ 20% Period From 13/07/2018 to 22/07/2018";
                                //$nar="Being Turnover Discount Qtr-".$num." F. Y. $financial_yr";
                                $voucher->addChild("PARTYGSTIN",$sobj->gstin_no);
                                $voucher->addChild("PARTYNAME",$sname);
                                
                                $voucher->addChild("VOUCHERTYPENAME","Debit Note");
                                
                                //$voucher->addChild("REFERENCE",$obj->ref_no);
                                if(isset($obj->ref_no))
                                {
                                    $voucher->addChild("REFERENCE",$obj->ref_no);
                                }
                                else {
                                    $voucher->addChild("REFERENCE","  ");
                                }
                                
                                //$voucher->
                                
                                $voucher->addChild("STATENAME","MAHARASTRA");
                                $voucher->addChild("VOUCHERNUMBER",$obj->id);
                                $voucher->addChild("PARTYLEDGERNAME",$sname);
                          
                                
                                ////////FBTPAYMENTTYPE>Default
                                $voucher->addChild("PERSISTEDVIEW","Accounting Voucher View");
                                //VOUCHERTYPEORIGNAME
                                $voucher->addChild("VOUCHERTYPEORIGNAME","Sale Back Purchase-GST");
                                
                                
                                
                                
                                                      
                                $istwlv=false;
                                $price_5=0.0;
                                $disc_5=0.0;
                                $cgst_5=0.0;
                                $sgst_5=0.0;
                                $igst_5=0.0;
                                $price_12=0.0;
                                $disc_12=0.0;
                                $cgst_12=0.0;
                                $sgst_12=0.0;
                                $igst_12=0.0;
                                $taxlevelquery="select tax_rate,cgst,sgst,igst,discount_val,price FROM it_debit_advice_items where debit_id=$obj->id";
                                $taxobjs=$db->fetchObjectArray($taxlevelquery);
                                
                                foreach($taxobjs as $taxobj)
                                {
                                   if($taxobj->tax_rate==0.05)
                                   {
                                        $price_5+=$taxobj->price;
                                        $disc_5+=$taxobj->discount_val;
                                        $cgst_5+=$taxobj->cgst;
                                        $sgst_5+=$taxobj->sgst;
                                        $igst_5+=$taxobj->igst;
                                   }
                                   else {
                                       
                                        $istwlv=true;
                                        $price_12+=$taxobj->price;
                                        $disc_12+=$taxobj->discount_val;
                                        $cgst_12+=$taxobj->cgst;
                                        $sgst_12+=$taxobj->sgst;
                                        $igst_12+=$taxobj->igst;
                                   }
                                    
                                }
                                
                                $price_5=round($price_5,2);
                                $disc_5=round($disc_5,2);
                                $cgst_5=round($cgst_5,2);
                                $sgst_5=round($sgst_5,2);
                                $igst_5=round($igst_5,2);
                                $price_12=round($price_12,2);
                                $disc_12=round($disc_12,2);
                                $cgst_12=round($cgst_12,2);
                                $sgst_12=round($sgst_12,2);
                                $igst_12=round($igst_12,2);
                                
                                
                                 $allledgerentrieslist1 = $voucher->addChild("ALLLEDGERENTRIES.LIST"); 
                                $oldauditentryidslist1=$allledgerentrieslist1->addChild("OLDAUDITENTRYIDS.LIST");
                                $oldauditentryidslist1->addAttribute("TYPE", "Number");
                                $oldauditentryidslist1->addChild("OLDAUDITENTRYIDS", -1);
                                $allledgerentrieslist1->addChild("LEDGERNAME", $sname);
                                $allledgerentrieslist1->addChild("GSTCLASS");
                                $allledgerentrieslist1->addChild("ISDEEMEDPOSITIVE","Yes");
                                $allledgerentrieslist1->addChild("LEDGERFROMITEM","No");  
                                $allledgerentrieslist1->addChild("REMOVEZEROENTRIES","No");
                                $allledgerentrieslist1->addChild("ISPARTYLEDGER","Yes");
                                $allledgerentrieslist1->addChild("ISLASTDEEMEDPOSITIVE","Yes");
                                $gst_total=($price_5+$price_12+$cgst_5+$sgst_5+$igst_5+$cgst_12+$sgst_12+$igst_12)-($disc_5+$disc_12);
                                $allledgerentrieslist1->addChild("AMOUNT",($gst_total*-1));
                                $allledgerentrieslist1->addChild("VATEXPAMOUNT",($gst_total*-1));
                                
                                $allledgerentrieslist1->addChild("SERVICETAXDETAILS.LIST","  ");
                                
                                $allledgerentrieslist1->addChild("BANKALLOCATIONS.LIST"," ");
                                //BILLALLOCATIONS.LIST
                                $str_length = 4;
                                $num=$obj->id;
                                $str = substr("0000{$num}", -$str_length);
                                $billalloctaionlist1=$allledgerentrieslist1->addChild("BILLALLOCATIONS.LIST");
                                $billalloctaionlist1->addChild("NAME","DN".$obj->debit_no);
                                $billalloctaionlist1->addChild("BILLTYPE", "New Ref");
                                $billalloctaionlist1->addChild("TDSDEDUCTEEISSPECIALRATE", "No");
                                $billalloctaionlist1->addChild("AMOUNT",($gst_total*-1));   
                                   /*--------------------------------------------------------------------------------------*/
                               if($obj->igst_total!=0)
                               {
                                   
                                $allledgerentrieslist2 = $voucher->addChild("ALLLEDGERENTRIES.LIST"); 
                                $oldauditentryidslist3=$allledgerentrieslist2->addChild("OLDAUDITENTRYIDS.LIST");
                                $oldauditentryidslist3->addAttribute("TYPE", "Number");
                                $oldauditentryidslist3->addChild("OLDAUDITENTRYIDS", -1);
                                $allledgerentrieslist2->addChild("LEDGERNAME","CK GST Purchase @ 5% MRP OMS");
                                $allledgerentrieslist2->addChild("GSTCLASS");
                                $allledgerentrieslist2->addChild("ISDEEMEDPOSITIVE","No");
                                $allledgerentrieslist2->addChild("LEDGERFROMITEM","No");  
                                $allledgerentrieslist2->addChild("REMOVEZEROENTRIES","No");
                                $allledgerentrieslist2->addChild("ISPARTYLEDGER","No");
                                $allledgerentrieslist2->addChild("ISLASTDEEMEDPOSITIVE","No");
                                
                              
                                $allledgerentrieslist2->addChild("AMOUNT",round($price_5,2));
                                $allledgerentrieslist2->addChild("VATEXPAMOUNT",round($price_5,2));
                            
                                $allledgerentrieslist3 = $voucher->addChild("ALLLEDGERENTRIES.LIST"); 
                                $oldauditentryidslist4=$allledgerentrieslist3->addChild("OLDAUDITENTRYIDS.LIST");
                                $oldauditentryidslist4->addAttribute("TYPE", "Number");
                                $oldauditentryidslist4->addChild("OLDAUDITENTRYIDS", -1);
                                $allledgerentrieslist3->addChild("LEDGERNAME","CK Discount for 5% Purchase OMS");
                                $allledgerentrieslist3->addChild("GSTCLASS");
                                $allledgerentrieslist3->addChild("ISDEEMEDPOSITIVE","Yes");
                                $allledgerentrieslist3->addChild("LEDGERFROMITEM","No");  
                                $allledgerentrieslist3->addChild("REMOVEZEROENTRIES","No");
                                $allledgerentrieslist3->addChild("ISPARTYLEDGER","No");
                                $allledgerentrieslist3->addChild("ISLASTDEEMEDPOSITIVE","Yes");
                   
                                $allledgerentrieslist3->addChild("AMOUNT",round(($disc_5*(-1))),2);
                                $allledgerentrieslist3->addChild("VATEXPAMOUNT",round(($disc_5*(-1)),2));
                                
                                
                                
                                $allledgerentrieslist4 = $voucher->addChild("ALLLEDGERENTRIES.LIST"); 
                                $oldauditentryidslist5=$allledgerentrieslist4->addChild("OLDAUDITENTRYIDS.LIST");
                                $oldauditentryidslist5->addAttribute("TYPE", "Number");
                                $oldauditentryidslist5->addChild("OLDAUDITENTRYIDS", -1);
                                $allledgerentrieslist4->addAttribute("TYPE", "Number");
                                $allledgerentrieslist4->addChild("OLDAUDITENTRYIDS", -1);
                                $allledgerentrieslist4->addChild("LEDGERNAME","CK IGST Paid @ 5%");
                                $allledgerentrieslist4->addChild("GSTCLASS");
                                $allledgerentrieslist4->addChild("ISDEEMEDPOSITIVE","Yes");
                                $allledgerentrieslist4->addChild("LEDGERFROMITEM","No");  
                                $allledgerentrieslist4->addChild("REMOVEZEROENTRIES","No");
                                $allledgerentrieslist4->addChild("ISPARTYLEDGER","No");
                                $allledgerentrieslist4->addChild("ISLASTDEEMEDPOSITIVE","Yes");
                   
                                $allledgerentrieslist4->addChild("AMOUNT",($igst_5*(-1)));
                                $allledgerentrieslist4->addChild("VATEXPAMOUNT",($igst_5*(-1)));
                                 
                                if($istwlv){
                                    
                                $allledgerentrieslist5 = $voucher->addChild("ALLLEDGERENTRIES.LIST"); 
                                $oldauditentryidslist6=$allledgerentrieslist5->addChild("OLDAUDITENTRYIDS.LIST");
                                $oldauditentryidslist6->addAttribute("TYPE", "Number");
                                $oldauditentryidslist6->addChild("OLDAUDITENTRYIDS", -1);
                                $allledgerentrieslist5->addChild("LEDGERNAME","CK GST Purchase @ 12% MRP OMS");
                                $allledgerentrieslist5->addChild("GSTCLASS");
                                $allledgerentrieslist5->addChild("ISDEEMEDPOSITIVE","No");
                                $allledgerentrieslist5->addChild("LEDGERFROMITEM","No");  
                                $allledgerentrieslist5->addChild("REMOVEZEROENTRIES","No");
                                $allledgerentrieslist5->addChild("ISPARTYLEDGER","No");
                                $allledgerentrieslist5->addChild("ISLASTDEEMEDPOSITIVE","No");
                                
                              
                                $allledgerentrieslist5->addChild("AMOUNT",round($price_12,2));
                                $allledgerentrieslist5->addChild("VATEXPAMOUNT",round($price_12,2));
                            
                                $allledgerentrieslist6 = $voucher->addChild("ALLLEDGERENTRIES.LIST"); 
                                $oldauditentryidslist7=$allledgerentrieslist6->addChild("OLDAUDITENTRYIDS.LIST");
                                $oldauditentryidslist7->addAttribute("TYPE", "Number");
                                $oldauditentryidslist7->addChild("OLDAUDITENTRYIDS", -1);
                                $allledgerentrieslist6->addChild("LEDGERNAME","CK Discount for 12% Purchase OMS");
                                $allledgerentrieslist6->addChild("GSTCLASS");
                                $allledgerentrieslist6->addChild("ISDEEMEDPOSITIVE","Yes");
                                $allledgerentrieslist6->addChild("LEDGERFROMITEM","No");  
                                $allledgerentrieslist6->addChild("REMOVEZEROENTRIES","No");
                                $allledgerentrieslist6->addChild("ISPARTYLEDGER","No");
                                $allledgerentrieslist6->addChild("ISLASTDEEMEDPOSITIVE","Yes");
                   
                                $allledgerentrieslist6->addChild("AMOUNT",round(($disc_12*(-1)),2));
                                $allledgerentrieslist6->addChild("VATEXPAMOUNT",round(($disc_12*(-1)),2));
                                
                                
                                
                                $allledgerentrieslist7 = $voucher->addChild("ALLLEDGERENTRIES.LIST"); 
                                $oldauditentryidslist8=$allledgerentrieslist7->addChild("OLDAUDITENTRYIDS.LIST");
                                $oldauditentryidslist8->addAttribute("TYPE", "Number");
                                $oldauditentryidslist8->addChild("OLDAUDITENTRYIDS", -1);
                                $allledgerentrieslist7->addChild("LEDGERNAME","CK IGST Paid @ 12%");
                                $allledgerentrieslist7->addChild("GSTCLASS");
                                $allledgerentrieslist7->addChild("ISDEEMEDPOSITIVE","Yes");
                                $allledgerentrieslist7->addChild("LEDGERFROMITEM","No");  
                                $allledgerentrieslist7->addChild("REMOVEZEROENTRIES","No");
                                $allledgerentrieslist7->addChild("ISPARTYLEDGER","No");
                                $allledgerentrieslist7->addChild("ISLASTDEEMEDPOSITIVE","Yes");
                   
                                $allledgerentrieslist7->addChild("AMOUNT",($igst_12*(-1)));
                                $allledgerentrieslist7->addChild("VATEXPAMOUNT",($igst_12*(-1)));
                                 
                                    
                                    
                                }
                               }
                               else
                               {
                                  //Discount Scheme Sale SGST @ 2.5%
                                $allledgerentrieslist2 = $voucher->addChild("ALLLEDGERENTRIES.LIST"); 
                                $oldauditentryidslist3=$allledgerentrieslist2->addChild("OLDAUDITENTRYIDS.LIST");
                                $oldauditentryidslist3->addAttribute("TYPE", "Number");
                                $oldauditentryidslist3->addChild("OLDAUDITENTRYIDS", -1);
                                $allledgerentrieslist2->addChild("LEDGERNAME","CK GST Purchase @ 5% MRP MS");
                                $allledgerentrieslist2->addChild("GSTCLASS");
                                $allledgerentrieslist2->addChild("ISDEEMEDPOSITIVE","No");
                                $allledgerentrieslist2->addChild("LEDGERFROMITEM","No");  
                                $allledgerentrieslist2->addChild("REMOVEZEROENTRIES","No");
                                $allledgerentrieslist2->addChild("ISPARTYLEDGER","No");
                                $allledgerentrieslist2->addChild("ISLASTDEEMEDPOSITIVE","No");
                                
                              
                                $allledgerentrieslist2->addChild("AMOUNT",round($price_5,2));
                                $allledgerentrieslist2->addChild("VATEXPAMOUNT",round($price_5,2));
                            
                                $allledgerentrieslist3 = $voucher->addChild("ALLLEDGERENTRIES.LIST"); 
                                $oldauditentryidslist4=$allledgerentrieslist3->addChild("OLDAUDITENTRYIDS.LIST");
                                $oldauditentryidslist4->addAttribute("TYPE", "Number");
                                $oldauditentryidslist4->addChild("OLDAUDITENTRYIDS", -1);
                                $allledgerentrieslist3->addChild("LEDGERNAME","CK Discount for 5% Purchase MS");
                                $allledgerentrieslist3->addChild("GSTCLASS");
                                $allledgerentrieslist3->addChild("ISDEEMEDPOSITIVE","Yes");
                                $allledgerentrieslist3->addChild("LEDGERFROMITEM","No");  
                                $allledgerentrieslist3->addChild("REMOVEZEROENTRIES","No");
                                $allledgerentrieslist3->addChild("ISPARTYLEDGER","No");
                                $allledgerentrieslist3->addChild("ISLASTDEEMEDPOSITIVE","Yes");
                   
                                $allledgerentrieslist3->addChild("AMOUNT",round(($disc_5*(-1)),2));
                                $allledgerentrieslist3->addChild("VATEXPAMOUNT",round(($disc_5*(-1)),2));
                                
                                
                                
                                $allledgerentrieslist4 = $voucher->addChild("ALLLEDGERENTRIES.LIST"); 
                                $oldauditentryidslist5=$allledgerentrieslist4->addChild("OLDAUDITENTRYIDS.LIST");
                                $oldauditentryidslist5->addAttribute("TYPE", "Number");
                                $oldauditentryidslist5->addChild("OLDAUDITENTRYIDS", -1);
                                $allledgerentrieslist4->addAttribute("TYPE", "Number");
                                $allledgerentrieslist4->addChild("OLDAUDITENTRYIDS", -1);
                                $allledgerentrieslist4->addChild("LEDGERNAME","CK CGST Paid @ 2.5%");
                                $allledgerentrieslist4->addChild("GSTCLASS");
                                $allledgerentrieslist4->addChild("ISDEEMEDPOSITIVE","No");
                                $allledgerentrieslist4->addChild("LEDGERFROMITEM","No");  
                                $allledgerentrieslist4->addChild("REMOVEZEROENTRIES","No");
                                $allledgerentrieslist4->addChild("ISPARTYLEDGER","No");
                                $allledgerentrieslist4->addChild("ISLASTDEEMEDPOSITIVE","No");
                   
                                $allledgerentrieslist4->addChild("AMOUNT",round($cgst_5,2));
                                $allledgerentrieslist4->addChild("VATEXPAMOUNT",round($cgst_5,2));
                                 
                                
                                $allledgerentrieslist5 = $voucher->addChild("ALLLEDGERENTRIES.LIST"); 
                                $oldauditentryidslist6=$allledgerentrieslist5->addChild("OLDAUDITENTRYIDS.LIST");
                                $oldauditentryidslist6->addAttribute("TYPE", "Number");
                                $oldauditentryidslist6->addChild("OLDAUDITENTRYIDS", -1);
                                $allledgerentrieslist5->addChild("LEDGERNAME","CK SGST Paid @ 2.5%");
                                $allledgerentrieslist5->addChild("GSTCLASS");
                                $allledgerentrieslist5->addChild("ISDEEMEDPOSITIVE","No");
                                $allledgerentrieslist5->addChild("LEDGERFROMITEM","No");  
                                $allledgerentrieslist5->addChild("REMOVEZEROENTRIES","No");
                                $allledgerentrieslist5->addChild("ISPARTYLEDGER","No");
                                $allledgerentrieslist5->addChild("ISLASTDEEMEDPOSITIVE","No");
                                
                              
                                $allledgerentrieslist5->addChild("AMOUNT",round($sgst_5,2));
                                $allledgerentrieslist5->addChild("VATEXPAMOUNT",round($sgst_5,2));
                            
                                
                                
                                if($istwlv){
                                    
                                
                                $allledgerentrieslist6 = $voucher->addChild("ALLLEDGERENTRIES.LIST"); 
                                $oldauditentryidslist7=$allledgerentrieslist6->addChild("OLDAUDITENTRYIDS.LIST");
                                $oldauditentryidslist7->addAttribute("TYPE", "Number");
                                $oldauditentryidslist7->addChild("OLDAUDITENTRYIDS", -1);
                                $allledgerentrieslist6->addAttribute("TYPE", "Number");
                                $allledgerentrieslist6->addChild("OLDAUDITENTRYIDS", -1);
                                $allledgerentrieslist6->addChild("LEDGERNAME","CK GST Purchase @ 12% MRP MS");
                                $allledgerentrieslist6->addChild("GSTCLASS");
                                $allledgerentrieslist6->addChild("ISDEEMEDPOSITIVE","No");
                                $allledgerentrieslist6->addChild("LEDGERFROMITEM","No");  
                                $allledgerentrieslist6->addChild("REMOVEZEROENTRIES","No");
                                $allledgerentrieslist6->addChild("ISPARTYLEDGER","No");
                                $allledgerentrieslist6->addChild("ISLASTDEEMEDPOSITIVE","No");
                   
                                $allledgerentrieslist6->addChild("AMOUNT",round($price_12,2));
                                $allledgerentrieslist6->addChild("VATEXPAMOUNT",round($price_12,2));
                                
                                
                                
                                $allledgerentrieslist7 = $voucher->addChild("ALLLEDGERENTRIES.LIST"); 
                                $oldauditentryidslist8=$allledgerentrieslist7->addChild("OLDAUDITENTRYIDS.LIST");
                                $oldauditentryidslist8->addAttribute("TYPE", "Number");
                                $oldauditentryidslist8->addChild("OLDAUDITENTRYIDS", -1);
                                $allledgerentrieslist7->addAttribute("TYPE", "Number");
                                $allledgerentrieslist7->addChild("OLDAUDITENTRYIDS", -1);
                                $allledgerentrieslist7->addChild("LEDGERNAME","CK Discount for 12% Purchase MS");
                                $allledgerentrieslist7->addChild("GSTCLASS");
                                $allledgerentrieslist7->addChild("ISDEEMEDPOSITIVE","Yes");
                                $allledgerentrieslist7->addChild("LEDGERFROMITEM","No");  
                                $allledgerentrieslist7->addChild("REMOVEZEROENTRIES","No");
                                $allledgerentrieslist7->addChild("ISPARTYLEDGER","No");
                                $allledgerentrieslist7->addChild("ISLASTDEEMEDPOSITIVE","Yes");
                   
                                $allledgerentrieslist7->addChild("AMOUNT",round(($disc_12*(-1)),2));
                                $allledgerentrieslist7->addChild("VATEXPAMOUNT",round(($disc_12*(-1)),2));
                                
                                
                                
                                $allledgerentrieslist8 = $voucher->addChild("ALLLEDGERENTRIES.LIST"); 
                                $oldauditentryidslist9=$allledgerentrieslist8->addChild("OLDAUDITENTRYIDS.LIST");
                                $oldauditentryidslist9->addAttribute("TYPE", "Number");
                                $oldauditentryidslist9->addChild("OLDAUDITENTRYIDS", -1);
                                $allledgerentrieslist8->addAttribute("TYPE", "Number");
                                $allledgerentrieslist8->addChild("OLDAUDITENTRYIDS", -1);
                                $allledgerentrieslist8->addChild("LEDGERNAME","CK CGST Paid @ 6%");
                                $allledgerentrieslist8->addChild("GSTCLASS");
                                $allledgerentrieslist8->addChild("ISDEEMEDPOSITIVE","No");
                                $allledgerentrieslist8->addChild("LEDGERFROMITEM","No");  
                                $allledgerentrieslist8->addChild("REMOVEZEROENTRIES","No");
                                $allledgerentrieslist8->addChild("ISPARTYLEDGER","No");
                                $allledgerentrieslist8->addChild("ISLASTDEEMEDPOSITIVE","No");
                   
                                $allledgerentrieslist8->addChild("AMOUNT",round($cgst_12,2));
                                $allledgerentrieslist8->addChild("VATEXPAMOUNT",round($cgst_12,2));
                                
                                $allledgerentrieslist9 = $voucher->addChild("ALLLEDGERENTRIES.LIST"); 
                                $oldauditentryidslist10=$allledgerentrieslist9->addChild("OLDAUDITENTRYIDS.LIST");
                                $oldauditentryidslist10->addAttribute("TYPE", "Number");
                                $oldauditentryidslist10->addChild("OLDAUDITENTRYIDS", -1);
                                $allledgerentrieslist9->addAttribute("TYPE", "Number");
                                $allledgerentrieslist9->addChild("OLDAUDITENTRYIDS", -1);
                                $allledgerentrieslist9->addChild("LEDGERNAME","CK SGST Paid @ 6%");
                                $allledgerentrieslist9->addChild("GSTCLASS");
                                $allledgerentrieslist9->addChild("ISDEEMEDPOSITIVE","No");
                                $allledgerentrieslist9->addChild("LEDGERFROMITEM","No");  
                                $allledgerentrieslist9->addChild("REMOVEZEROENTRIES","No");
                                $allledgerentrieslist9->addChild("ISPARTYLEDGER","No");
                                $allledgerentrieslist9->addChild("ISLASTDEEMEDPOSITIVE","No");
                   
                                $allledgerentrieslist9->addChild("AMOUNT",round($sgst_12,2));
                                $allledgerentrieslist9->addChild("VATEXPAMOUNT",round($sgst_12,2));
                                 
                                }
                                
                                
                               }

                                                                              
                 }                  
             }
             

             
         header('Content-Disposition: attachment;filename='.$name);    
 
         header('Content-Type: application/xml; charset=utf-8');
         echo $envelope->saveXML();  
    }else{ print "No Invoices availabe in the selected date range.";}        
    

?>