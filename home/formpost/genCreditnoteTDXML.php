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
$d2=$dtarr[1];

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
    $name = "TurnoverDiscount-CreditNote".$dt1."_".$dt2.".xml";
    
     $query = " select * FROM it_creditnote_td WHERE date(from_datetime) >= $startdate  and date(to_datetime) <= $enddate  order by id";
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
                          $qtr=$obj->qtr;


                        $tallymsg1 = $reqdata->addChild("TALLYMESSAGE");
                            $voucher = $tallymsg1->addChild("VOUCHER");
//                            $voucher->addAttribute("REMOTEID", "13c395f3-84cf-4db8-a75d-a52dd7d74387-0005f14b");
//                            $voucher->addAttribute("VCHKEY", "13c395f3-84cf-4db8-a75d-a52dd7d74387-0000a889:00000034");
                            $voucher->addAttribute("VCHTYPE", "Credit Note GST");
                            $voucher->addAttribute("ACTION", "Create");
                            $voucher->addAttribute("OBJVIEW", "Accounting Voucher View");                                                        
//                            $dt = date('Y-m-d',strtotime($obj->to_datetime));
                            $dt = date('Y-m-d',strtotime($obj->createtime));
                            $dt1=date('Y-m-d',strtotime($obj->ref_date));
                               $invdate = preg_replace("/[^0-9]+/", "", $dt);
                               $refdate = preg_replace("/[^0-9]+/", "", $dt1);
                               
                               $oldauditentryidslist1 = $voucher->addChild("OLDAUDITENTRYIDS.LIST");
                               $oldauditentryidslist1->addAttribute("TYPE", "Number");
                               $oldauditentryidslist1->addChild("OLDAUDITENTRYIDS", "-1");
                                $voucher->addChild("DATE",$invdate);
                                $voucher->addChild("REFERENCEDATE",$refdate);
                                //$voucher->addChild("GUID","13c395f3-84cf-4db8-a75d-a52dd7d74387-0005f14b");
                                //$voucher->addChild("STATENAME","Maharashtra");
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
                                $dt3=date('Y-M-d h:i:s',strtotime($obj->to_datetime));
                                
                             //   $nar="Being Turnover Discount Qtr-".$num." F. Y. $financial_yr";
                               
                                $nar=$obj->remark;
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
                               // $voucher->addChild("CSTFORMISSUETYPE");
                                //$voucher->addChild("CSTFORMRECVTYPE");
                                //$voucher->addChild("FBTPAYMENTTYPE","Default");
                                
                                ////////FBTPAYMENTTYPE>Default
                                $voucher->addChild("PERSISTEDVIEW","Accounting Voucher View");
                                
                                
                                
//                                $voucher->addChild("PLACEOFSUPPLY","Maharashtra");
//                                $voucher->addChild("BASICBUYERNAME",$sname);
//                                $voucher->addChild("BASICDATETIMEOFREMOVAL",$dt3);
                                
                                
                                
//                                $voucher->addChild("VCHGSTCLASS");
//                                //$voucher->addChild("CONSIGNEESTATENAME","Maharashtra");
//                                $voucher->addChild("ENTEREDBY","yogesh");
//                                $voucher->addChild("DIFFACTUALQTY","No");
//                                $voucher->addChild("ISMSTFROMSYNC","No");
//                                $voucher->addChild("ASORIGINAL","No");
//                                $voucher->addChild("AUDITED","No");
//                                $voucher->addChild("FORJOBCOSTING","No");
//                                $voucher->addChild("ISOPTIONAL","No");
//                                
//                                
//                                $voucher->addChild("EFFECTIVEDATE",$invdate);// dbt
//                                $voucher->addChild("USEFOREXCISE","No");
//                                $voucher->addChild("ISFORJOBWORKIN","No");
//                                $voucher->addChild("ALLOWCONSUMPTION","No");
//                                $voucher->addChild("USEFORINTEREST","No");
//                                $voucher->addChild("USEFORGAINLOSS","No");
//                                $voucher->addChild("USEFORGODOWNTRANSFER","No");
//                                $voucher->addChild("USEFORCOMPOUND","No");
//                                $voucher->addChild("USEFORSERVICETAX","No");
//                                $voucher->addChild("ISEXCISEVOUCHER","No");
//                                $voucher->addChild("EXCISETAXOVERRIDE","No");
//                                
//                                
//                                
//                                $voucher->addChild("USEFORTAXUNITTRANSFER","No");
//                                $voucher->addChild("EXCISEOPENING","No");
//                                $voucher->addChild("USEFORFINALPRODUCTION","No");
//                                $voucher->addChild("ISTDSOVERRIDDEN","No");
//                                $voucher->addChild("ISTCSOVERRIDDEN","No");
//                                $voucher->addChild("ISTDSTCSCASHVCH","No");
//                                $voucher->addChild("INCLUDEADVPYMTVCH","No");
//                                $voucher->addChild("ISSUBWORKSCONTRACT","No");
//                                $voucher->addChild("ISVATOVERRIDDEN","No");
//                                $voucher->addChild("IGNOREORIGVCHDATE","No");
//                                
//                                $voucher->addChild("ISSERVICETAXOVERRIDDEN","No");
//                                $voucher->addChild("ISISDVOUCHER","No");
//                                $voucher->addChild("ISEXCISEOVERRIDDEN","No");
//                                $voucher->addChild("ISEXCISESUPPLYVCH","No");
//                                $voucher->addChild("ISGSTOVERRIDDEN","No");
//                                $voucher->addChild("GSTNOTEXPORTED","No");
//                                $voucher->addChild("ISVATPRINCIPALACCOUNT","No");
//                                $voucher->addChild("ISBOENOTAPPLICABLE","No");
//                                $voucher->addChild("ISSHIPPINGWITHINSTATE","No");
//                                $voucher->addChild("ISCANCELLED","No");
//                              
//                                $voucher->addChild("HASCASHFLOW","No");
//                                $voucher->addChild("ISPOSTDATED","No");
//                                $voucher->addChild("USETRACKINGNUMBER","No");
//                                $voucher->addChild("ISINVOICE","No");
//                                $voucher->addChild("MFGJOURNAL","No");
//                                $voucher->addChild("HASDISCOUNTS","No");
//                                $voucher->addChild("ASPAYSLIP","No");
//                                $voucher->addChild("ISCOSTCENTRE","No");
//                                $voucher->addChild("ISSTXNONREALIZEDVCH","No");
//                                $voucher->addChild("ISEXCISEMANUFACTURERON","No");
//                                
//                                
//                                $voucher->addChild("ISBLANKCHEQUE","No");
//                                $voucher->addChild("ISVOID","No");
//                                $voucher->addChild("ISONHOLD","No");
//                                $voucher->addChild("ORDERLINESTATUS","No");
//                                $voucher->addChild("VATISAGNSTCANCSALES","No");
//                                $voucher->addChild("VATISPURCEXEMPTED","No");
//                                $voucher->addChild("ISVATRESTAXINVOICE","No");
//                                $voucher->addChild("VATISASSESABLECALCVCH","No");
//                                $voucher->addChild("ISVATDUTYPAID","Yes");
//                                $voucher->addChild("ISDELIVERYSAMEASCONSIGNEE","No");
//                                $voucher->addChild("ISDISPATCHSAMEASCONSIGNOR","No");
//                                $voucher->addChild("ISDELETED","No");
//                                $voucher->addChild("CHANGEVCHMODE","No");
//                                $voucher->addChild("ALTERID","638386");
//                                $voucher->addChild("MASTERID","389451");
//                                $voucher->addChild("VOUCHERKEY","185306363985972");
//                                
//
//                                
//                                $voucher->addChild("EXCLUDEDTAXATIONS.LIST"," ");
//                                $voucher->addChild("OLDAUDITENTRIES.LIST"," ");
//                                $voucher->addChild("ACCOUNTAUDITENTRIES.LIST"," ");
//                                $voucher->addChild("AUDITENTRIES.LIST"," ");
//                                $voucher->addChild("DUTYHEADDETAILS.LIST"," ");
//                                $voucher->addChild("SUPPLEMENTARYDUTYHEADDETAILS.LIST"," ");
//                                $voucher->addChild("INVOICEDELNOTES.LIST"," ");
//                                $voucher->addChild("INVOICEORDERLIST.LIST"," ");
//                                $voucher->addChild("INVOICEINDENTLIST.LIST"," ");
//                                $voucher->addChild("ATTENDANCEENTRIES.LIST"," ");
//                                $voucher->addChild("ORIGINVOICEDETAILS.LIST"," ");
//                                $voucher->addChild("INVOICEEXPORTLIST.LIST"," ");
                             
                                
                                
                                
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
                                
                                $allledgerentrieslist1->addChild("AMOUNT",$obj->gst_total);
                                $allledgerentrieslist1->addChild("VATEXPAMOUNT",$obj->gst_total);
                                
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
                                $billalloctaionlist1->addChild("AMOUNT", $obj->gst_total);
//                                $billalloctaionlist1->addChild("INTERESTCOLLECTION.LIST", " ");
//                                $billalloctaionlist1->addChild("STBILLCATEGORIES.LIST", " ");
//                                
//                                $allledgerentrieslist1->addChild("INTERESTCOLLECTION.LIST"," ");
//                                $allledgerentrieslist1->addChild("OLDAUDITENTRIES.LIST"," ");
//                                $allledgerentrieslist1->addChild("ACCOUNTAUDITENTRIES.LIST"," ");
//                                $allledgerentrieslist1->addChild("AUDITENTRIES.LIST"," ");
//                                $allledgerentrieslist1->addChild("INPUTCRALLOCS.LIST"," ");
//                                $allledgerentrieslist1->addChild("DUTYHEADDETAILS.LIST"," ");
//                                $allledgerentrieslist1->addChild("EXCISEDUTYHEADDETAILS.LIST"," ");
//                                $allledgerentrieslist1->addChild("RATEDETAILS.LIST"," ");
//                                $allledgerentrieslist1->addChild("SUMMARYALLOCS.LIST"," ");
//                                $allledgerentrieslist1->addChild("STPYMTDETAILS.LIST"," ");
//                                $allledgerentrieslist1->addChild("EXCISEPAYMENTALLOCATIONS.LIST"," ");
//                                $allledgerentrieslist1->addChild("TAXBILLALLOCATIONS.LIST"," ");
//                                $allledgerentrieslist1->addChild("TAXOBJECTALLOCATIONS.LIST"," ");
//                                $allledgerentrieslist1->addChild("TDSEXPENSEALLOCATIONS.LIST"," ");
//                                $allledgerentrieslist1->addChild("VATSTATUTORYDETAILS.LIST"," ");
//                                $allledgerentrieslist1->addChild("COSTTRACKALLOCATIONS.LIST"," ");
//                                $allledgerentrieslist1->addChild("REFVOUCHERDETAILS.LIST"," ");
//                                $allledgerentrieslist1->addChild("INVOICEWISEDETAILS.LIST"," ");
//                                $allledgerentrieslist1->addChild("VATITCDETAILS.LIST"," ");
//                                $allledgerentrieslist1->addChild("ADVANCETAXDETAILS.LIST"," ");
//                                
                               
                                
                                
                                
                                $allledgerentrieslist2 = $voucher->addChild("ALLLEDGERENTRIES.LIST"); 
//                                $oldauditentryidslist2=$allledgerentrieslist2->addChild("OLDAUDITENTRYIDS.LIST");
//                                $oldauditentryidslist2->addAttribute("TYPE", "Number");
//                                $oldauditentryidslist2->addChild("OLDAUDITENTRYIDS", -1);
                                $allledgerentrieslist2->addChild("LEDGERNAME","GST Turnover Discount Net 12%");
                                $allledgerentrieslist2->addChild("GSTCLASS");
                                $allledgerentrieslist2->addChild("ISDEEMEDPOSITIVE","Yes");
                                $allledgerentrieslist2->addChild("LEDGERFROMITEM","No");  
                                $allledgerentrieslist2->addChild("REMOVEZEROENTRIES","No");
                                $allledgerentrieslist2->addChild("ISPARTYLEDGER","No");
                                $allledgerentrieslist2->addChild("ISLASTDEEMEDPOSITIVE","Yes");
                                
                                //$totalgst=$obj->igst_paid+($obj->cgst_paid*2);
                                $allledgerentrieslist2->addChild("AMOUNT",($obj->gst_net*(-1)));
                                $allledgerentrieslist2->addChild("VATEXPAMOUNT",($obj->gst_net*(-1)));
                                
//                                $allledgerentrieslist2->addChild("SERVICETAXDETAILS.LIST"," ");
//                                $allledgerentrieslist2->addChild("BANKALLOCATIONS.LIST"," ");
//                                $allledgerentrieslist2->addChild("BILLALLOCATIONS.LIST"," ");
//                                $allledgerentrieslist2->addChild("INTERESTCOLLECTION.LIST"," ");
//                                $allledgerentrieslist2->addChild("OLDAUDITENTRIES.LIST"," ");
//                                $allledgerentrieslist2->addChild("ACCOUNTAUDITENTRIES.LIST"," ");
//                                $allledgerentrieslist2->addChild("AUDITENTRIES.LIST"," ");
//                                $allledgerentrieslist2->addChild("INPUTCRALLOCS.LIST"," ");
//                                $allledgerentrieslist2->addChild("DUTYHEADDETAILS.LIST"," ");
//                                $allledgerentrieslist2->addChild("EXCISEDUTYHEADDETAILS.LIST"," ");
//                                $allledgerentrieslist2->addChild("RATEDETAILS.LIST"," ");
//                                $allledgerentrieslist2->addChild("SUMMARYALLOCS.LIST"," ");
//                                $allledgerentrieslist2->addChild("STPYMTDETAILS.LIST"," ");
//                                $allledgerentrieslist2->addChild("EXCISEPAYMENTALLOCATIONS.LIST"," ");
//                                $allledgerentrieslist2->addChild("TAXBILLALLOCATIONS.LIST"," ");
//                                $allledgerentrieslist2->addChild("TAXOBJECTALLOCATIONS.LIST"," ");
//                                $allledgerentrieslist2->addChild("TDSEXPENSEALLOCATIONS.LIST"," ");
//                                $allledgerentrieslist2->addChild("VATSTATUTORYDETAILS.LIST"," ");
//                                $allledgerentrieslist2->addChild("COSTTRACKALLOCATIONS.LIST"," ");
//                                $allledgerentrieslist2->addChild("REFVOUCHERDETAILS.LIST"," ");
//                                $allledgerentrieslist2->addChild("INVOICEWISEDETAILS.LIST"," ");
//                                $allledgerentrieslist2->addChild("VATITCDETAILS.LIST"," ");
//                                $allledgerentrieslist2->addChild("ADVANCETAXDETAILS.LIST"," ");
                                   /*--------------------------------------------------------------------------------------*/
                               if($obj->igst_paid!=0)
                               {
                                $allledgerentrieslist3 = $voucher->addChild("ALLLEDGERENTRIES.LIST"); 
                                $oldauditentryidslist3=$allledgerentrieslist3->addChild("OLDAUDITENTRYIDS.LIST");
                                $oldauditentryidslist3->addAttribute("TYPE", "Number");
                                $oldauditentryidslist3->addChild("OLDAUDITENTRYIDS", -1);
                                $allledgerentrieslist3->addChild("LEDGERNAME","IGST Turnover Discount Paid 12%");
                                $allledgerentrieslist3->addChild("GSTCLASS");
                                $allledgerentrieslist3->addChild("ISDEEMEDPOSITIVE","Yes");
                                $allledgerentrieslist3->addChild("LEDGERFROMITEM","No");  
                                $allledgerentrieslist3->addChild("REMOVEZEROENTRIES","No");
                                $allledgerentrieslist3->addChild("ISPARTYLEDGER","No");
                                $allledgerentrieslist3->addChild("ISLASTDEEMEDPOSITIVE","Yes");
                   
                                $allledgerentrieslist3->addChild("AMOUNT",($obj->igst_paid*(-1)));
                                $allledgerentrieslist3->addChild("VATEXPAMOUNT",($obj->igst_paid*(-1)));
                                
//                                $allledgerentrieslist3->addChild("SERVICETAXDETAILS.LIST"," ");
//                                $allledgerentrieslist3->addChild("BANKALLOCATIONS.LIST"," ");
//                                $allledgerentrieslist3->addChild("BILLALLOCATIONS.LIST"," ");
//                                $allledgerentrieslist3->addChild("INTERESTCOLLECTION.LIST"," ");
//                                $allledgerentrieslist3->addChild("OLDAUDITENTRIES.LIST"," ");
//                                $allledgerentrieslist3->addChild("ACCOUNTAUDITENTRIES.LIST"," ");
//                                $allledgerentrieslist3->addChild("AUDITENTRIES.LIST"," ");
//                                $allledgerentrieslist3->addChild("INPUTCRALLOCS.LIST"," ");
//                                $allledgerentrieslist3->addChild("DUTYHEADDETAILS.LIST"," ");
//                                $allledgerentrieslist3->addChild("EXCISEDUTYHEADDETAILS.LIST"," ");
//                                $allledgerentrieslist3->addChild("RATEDETAILS.LIST"," ");
//                                $allledgerentrieslist3->addChild("SUMMARYALLOCS.LIST"," ");
//                                $allledgerentrieslist3->addChild("STPYMTDETAILS.LIST"," ");
//                                $allledgerentrieslist3->addChild("EXCISEPAYMENTALLOCATIONS.LIST"," ");
//                                $allledgerentrieslist3->addChild("TAXBILLALLOCATIONS.LIST"," ");
//                                $allledgerentrieslist3->addChild("TAXOBJECTALLOCATIONS.LIST"," ");
//                                $allledgerentrieslist3->addChild("TDSEXPENSEALLOCATIONS.LIST"," ");
//                                $allledgerentrieslist3->addChild("VATSTATUTORYDETAILS.LIST"," ");
//                                $allledgerentrieslist3->addChild("COSTTRACKALLOCATIONS.LIST"," ");
//                                $allledgerentrieslist3->addChild("REFVOUCHERDETAILS.LIST"," ");
//                                $allledgerentrieslist3->addChild("INVOICEWISEDETAILS.LIST"," ");
//                                $allledgerentrieslist3->addChild("VATITCDETAILS.LIST"," ");
//                                $allledgerentrieslist3->addChild("ADVANCETAXDETAILS.LIST"," ");
                               }
                               else
                               {
                                  //Discount Scheme Sale SGST @ 2.5% 
                                $allledgerentrieslist3 = $voucher->addChild("ALLLEDGERENTRIES.LIST"); 
                                $oldauditentryidslist3=$allledgerentrieslist3->addChild("OLDAUDITENTRYIDS.LIST");
                                $oldauditentryidslist3->addAttribute("TYPE", "Number");
                                $oldauditentryidslist3->addChild("OLDAUDITENTRYIDS", -1);
                                $allledgerentrieslist3->addChild("LEDGERNAME","SGST Turnover Discount Paid 6%");
                                $allledgerentrieslist3->addChild("GSTCLASS");
                                $allledgerentrieslist3->addChild("ISDEEMEDPOSITIVE","Yes");
                                $allledgerentrieslist3->addChild("LEDGERFROMITEM","No");  
                                $allledgerentrieslist3->addChild("REMOVEZEROENTRIES","No");
                                $allledgerentrieslist3->addChild("ISPARTYLEDGER","No");
                                $allledgerentrieslist3->addChild("ISLASTDEEMEDPOSITIVE","Yes");
                   
                                $allledgerentrieslist3->addChild("AMOUNT",($obj->cgst_paid*(-1)));
                                $allledgerentrieslist3->addChild("VATEXPAMOUNT",($obj->cgst_paid*(-1)));
                                
//                                $allledgerentrieslist3->addChild("SERVICETAXDETAILS.LIST"," ");
//                                $allledgerentrieslist3->addChild("BANKALLOCATIONS.LIST"," ");
//                                $allledgerentrieslist3->addChild("BILLALLOCATIONS.LIST"," ");
//                                $allledgerentrieslist3->addChild("INTERESTCOLLECTION.LIST"," ");
//                                $allledgerentrieslist3->addChild("OLDAUDITENTRIES.LIST"," ");
//                                $allledgerentrieslist3->addChild("ACCOUNTAUDITENTRIES.LIST"," ");
//                                $allledgerentrieslist3->addChild("AUDITENTRIES.LIST"," ");
//                                $allledgerentrieslist3->addChild("INPUTCRALLOCS.LIST"," ");
//                                $allledgerentrieslist3->addChild("DUTYHEADDETAILS.LIST"," ");
//                                $allledgerentrieslist3->addChild("EXCISEDUTYHEADDETAILS.LIST"," ");
//                                $allledgerentrieslist3->addChild("RATEDETAILS.LIST"," ");
//                                $allledgerentrieslist3->addChild("SUMMARYALLOCS.LIST"," ");
//                                $allledgerentrieslist3->addChild("STPYMTDETAILS.LIST"," ");
//                                $allledgerentrieslist3->addChild("EXCISEPAYMENTALLOCATIONS.LIST"," ");
//                                $allledgerentrieslist3->addChild("TAXBILLALLOCATIONS.LIST"," ");
//                                $allledgerentrieslist3->addChild("TAXOBJECTALLOCATIONS.LIST"," ");
//                                $allledgerentrieslist3->addChild("TDSEXPENSEALLOCATIONS.LIST"," ");
//                                $allledgerentrieslist3->addChild("VATSTATUTORYDETAILS.LIST"," ");
//                                $allledgerentrieslist3->addChild("COSTTRACKALLOCATIONS.LIST"," ");
//                                $allledgerentrieslist3->addChild("REFVOUCHERDETAILS.LIST"," ");
//                                $allledgerentrieslist3->addChild("INVOICEWISEDETAILS.LIST"," ");
//                                $allledgerentrieslist3->addChild("VATITCDETAILS.LIST"," ");
//                                $allledgerentrieslist3->addChild("ADVANCETAXDETAILS.LIST"," ");
                                
                                
                                //Discount Scheme Sale CGST @ 2.5% 
                                
                                $allledgerentrieslist4 = $voucher->addChild("ALLLEDGERENTRIES.LIST"); 
                                $oldauditentryidslist4=$allledgerentrieslist1->addChild("OLDAUDITENTRYIDS.LIST");
                                $oldauditentryidslist4->addAttribute("TYPE", "Number");
                                $oldauditentryidslist4->addChild("OLDAUDITENTRYIDS", -1);
                                $allledgerentrieslist4->addChild("LEDGERNAME","CGST Turnover Discount Paid 6%");
                                $allledgerentrieslist4->addChild("GSTCLASS");
                                $allledgerentrieslist4->addChild("ISDEEMEDPOSITIVE","Yes");
                                $allledgerentrieslist4->addChild("LEDGERFROMITEM","No");  
                                $allledgerentrieslist4->addChild("REMOVEZEROENTRIES","No");
                                $allledgerentrieslist4->addChild("ISPARTYLEDGER","No");
                                $allledgerentrieslist4->addChild("ISLASTDEEMEDPOSITIVE","Yes");
                   
                                $allledgerentrieslist4->addChild("AMOUNT",($obj->cgst_paid*(-1)));
                                $allledgerentrieslist4->addChild("VATEXPAMOUNT",($obj->cgst_paid*(-1)));
                                
//                                $allledgerentrieslist4->addChild("SERVICETAXDETAILS.LIST"," ");
//                                $allledgerentrieslist4->addChild("BANKALLOCATIONS.LIST"," ");
//                                $allledgerentrieslist4->addChild("BILLALLOCATIONS.LIST"," ");
//                                $allledgerentrieslist4->addChild("INTERESTCOLLECTION.LIST"," ");
//                                $allledgerentrieslist4->addChild("OLDAUDITENTRIES.LIST"," ");
//                                $allledgerentrieslist4->addChild("ACCOUNTAUDITENTRIES.LIST"," ");
//                                $allledgerentrieslist4->addChild("AUDITENTRIES.LIST"," ");
//                                $allledgerentrieslist4->addChild("INPUTCRALLOCS.LIST"," ");
//                                $allledgerentrieslist4->addChild("DUTYHEADDETAILS.LIST"," ");
//                                $allledgerentrieslist4->addChild("EXCISEDUTYHEADDETAILS.LIST"," ");
//                                $allledgerentrieslist4->addChild("RATEDETAILS.LIST"," ");
//                                $allledgerentrieslist4->addChild("SUMMARYALLOCS.LIST"," ");
//                                $allledgerentrieslist4->addChild("STPYMTDETAILS.LIST"," ");
//                                $allledgerentrieslist4->addChild("EXCISEPAYMENTALLOCATIONS.LIST"," ");
//                                $allledgerentrieslist4->addChild("TAXBILLALLOCATIONS.LIST"," ");
//                                $allledgerentrieslist4->addChild("TAXOBJECTALLOCATIONS.LIST"," ");
//                                $allledgerentrieslist4->addChild("TDSEXPENSEALLOCATIONS.LIST"," ");
//                                $allledgerentrieslist4->addChild("VATSTATUTORYDETAILS.LIST"," ");
//                                $allledgerentrieslist4->addChild("COSTTRACKALLOCATIONS.LIST"," ");
//                                $allledgerentrieslist4->addChild("REFVOUCHERDETAILS.LIST"," ");
//                                $allledgerentrieslist4->addChild("INVOICEWISEDETAILS.LIST"," ");
//                                $allledgerentrieslist4->addChild("VATITCDETAILS.LIST"," ");
//                                $allledgerentrieslist4->addChild("ADVANCETAXDETAILS.LIST"," ");
                                
                               }
                               //<PAYROLLMODEOFPAYMENT.LIST>      </PAYROLLMODEOFPAYMENT.LIST>
                               //<ATTDRECORDS.LIST>      </ATTDRECORDS.LIST>
                               //<TEMPGSTRATEDETAILS.LIST>      </TEMPGSTRATEDETAILS.LIST>
//                                 $payrollmodeofpaymentlist = $voucher->addChild("PAYROLLMODEOFPAYMENT.LIST"," ");
//                                 $attlist = $voucher->addChild("ATTDRECORDS.LIST"," ");
//                                 $tmplist = $voucher->addChild("TEMPGSTRATEDETAILS.LIST"," ");
                                                                              
                 }                  
             }
             
             
//             $tallymsg2 = $reqdata->addChild("TALLYMESSAGE");
//             $cmp1=$tallymsg2->addChild("COMPANY");
//             $remotecmpinfolist=$cmp1->addChild("REMOTECMPINFO.LIST");
//             $remotecmpinfolist->addAttribute("MERGE", "Yes");
//             $remotecmpinfolist->addChild("NAME", "13c395f3-84cf-4db8-a75d-a52dd7d74387");
//             $remotecmpinfolist->addChild("REMOTECMPNAME", "Fashioking Brands Pvt. Ltd. $financial_yr");
//             $remotecmpinfolist->addChild("REMOTECMPSTATE", "Maharashtra");
//             
//             
//             $tallymsg3 = $reqdata->addChild("TALLYMESSAGE");
//             $cmp2=$tallymsg3->addChild("COMPANY");
//             $remotecmpinfolist1=$cmp1->addChild("REMOTECMPINFO.LIST");
//             $remotecmpinfolist1->addAttribute("MERGE", "Yes");
//             $remotecmpinfolist1->addChild("NAME", "13c395f3-84cf-4db8-a75d-a52dd7d74387");
//             $remotecmpinfolist1->addChild("REMOTECMPNAME", "Fashioking Brands Pvt. Ltd. $financial_yr");
//             $remotecmpinfolist1->addChild("REMOTECMPSTATE", "Maharashtra");
             
         header('Content-Disposition: attachment;filename='.$name);    
 
         header('Content-Type: application/xml; charset=utf-8');
         echo $envelope->saveXML();  
    }else{ print "No Invoices availabe in the selected date range.";}        
    

?>
