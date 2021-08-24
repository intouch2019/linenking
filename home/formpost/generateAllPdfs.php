<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/conv/CurrencyConv.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once 'lib/users/clsUsers.php';

extract($_POST);
$user = getCurrUser();
$errors=array();
$success=array();
$db = new DBConn();
$conv=new CurrencyConv();

$invno=$_GET["invno"];

$invtyp=0;

 
	
            
  
$dtClause="";

 
    $sClause="and i.invoice_type=0" ;

if(isset($invno) && trim($invno)!="" && trim($invno) != "-1"){
    if($dtClause=="")
    {
        $iClause="i.invoice_no=$invno";
    }
    else
    {
        $iClause="and i.invoice_no=$invno";
    }
   
   
}else{  $iClause="" ;}

$query="select * from it_invoices i where $dtClause $iClause $sClause";


//print "Query".$query;
$objs=$db->fetchObjectArray($query);
//print_r($objs);
//
//
if(!$objs)
{
    $errors['nodata']="Please insert the correct invoice number";
}
$output1="";
$output="";
$output = "<!DOCTYPE html><html><head><title></title><meta charset=\"UTF-8\">"
                        . "<style>"
                        . "@page {"
                        . "size: a4 landscape;"
                        . "}"
                        . ".tabletop { border-top:1px solid #000000;}"
                        . ".tdborder { border:none;  }"
                        . "p {text-align:justify;}"
                        . "</style>"
                        . "</head>"
                        . "<body>";
$cntnew=0;
foreach($objs as $obj){

    $cntnew++;
$outputheader="";
$outputheaderforitems="";


$outputheader .=  "<br/><table width=\"95%\" border=\"1px\" align=\"center\">";
$outputheader .=  "<tr><th align='center' colspan=2><div style=\"font-size:18px; padding:12px 0 0px 0\">TAX INVOICE</div></th></tr>
";
$outputheader .="<tr><th align=\"left\"><div style=\"font-size:16px; padding:12px 0 0px 0\">&nbsp;Invoice No.:$obj->invoice_no</div></th>"
    . "<th align=\"left\"><div style=\"font-size:16px; padding:12px 0 0px 0\">&nbsp;Date:".yymmdd($obj->invoice_dt)."</div></th>"
       ."</tr>";
$storeid=$obj->store_id;
$query1="select  c.store_name,c.gstin_no,c.pancard_no,c.address,c.city,c.zipcode,c.phone,sd.dealer_discount from it_codes c ,it_ck_storediscount sd where c.id=sd.store_id and c.id=$storeid";
$storeobj=$db->fetchObject($query1);
//print "$query1";
if(!$storeobj->store_name)
{
    //$output="";
   continue;
}
$temp="To,<br/>"
        . "<b>".$storeobj->store_name."</b><br/>".$storeobj->address."<br/>$storeobj->city:$storeobj->zipcode<br/>Contact no:+91 $storeobj->phone<br/>Retail Net Margin:$storeobj->dealer_discount";
$outputheader .="<tr><td align=\"left\" width=\"55%\"><div style=\"font-size:12px;padding:4px\">$temp</div></td>"
    . "<td align=\"left\" width=\"50%\"><div style=\"font-size:12px;padding:4px\">From,<br/>
<b>Fashionking Brands Pvt. Ltd. (erstwhile Cottonking Pvt. Ltd.)</b><br/>
Plot No. 21,22,23 Hi-Tech Textile Park,<br/>
MIDC, Baramati, Dist. Pune- 413133<br/>
Phone : 02112-244121</div></td>"
       ."</tr>";







$outputheader .="<tr><td align=\"left\" width=\"50%\"><div style=\"font-size:12px;padding:3px\">GSTIN NO :&nbsp;&nbsp;$storeobj->gstin_no</div></td>"
    . "<td align=\"left\" width=\"50%\"><div style=\"font-size:12px;padding:3px\">GSTIN NO :&nbsp;&nbsp;27AAACC7418H1ZQ</div></td>"
       ."</tr>";

$outputheader .="<tr><td align=\"left\" width=\"50%\"><div style=\"font-size:12px;padding:3px\">PAN NO :&nbsp;&nbsp;$storeobj->pancard_no</div></td>"
    . "<td align=\"left\" width=\"50%\"><div style=\"font-size:12px;padding:3px\">PAN NO :&nbsp;&nbsp;AAACC7418H</div></td></tr></table>";
       //."</tr></table>";

$output1 .="<table width=\"95%\" border=\"1px\" align=\"center\">";
        
$outputheaderforitems.="<table width=\"95%\" border=\"1px\" align=\"center\"><thead><tr>"
        . "<th rowspan=\"2\" align=\"center\" width=\"4%\" bgcolor=#C0C0C0 ><div style=\"font-size:10px;padding:4px 0 0px 0; background:#C0C0C0\"><b>Sr.No</b></div></th>"
        . "<th rowspan=\"2\" align=\"center\" width=\"10%\" bgcolor=#C0C0C0><div style=\"font-size:10px;padding:4px 0 0px 0; background:#C0C0C0\"><b>Description of Goods</b></div></th>"
        . "<th rowspan=\"2\" align=\"center\" width=\"6%\" bgcolor=#C0C0C0><div style=\"font-size:10px;padding:4px 0 0px 0; background:#C0C0C0\"><b>HSN</b></div></th>"
        . "<th rowspan=\"2\" align=\"center\" width=\"5%\" bgcolor=#C0C0C0><div style=\"font-size:10px;padding:4px 0 0px 0; background:#C0C0C0\"><b>Qty</b></div></th>"
        . "<th rowspan=\"2\" align=\"center\" width=\"7%\" bgcolor=#C0C0C0><div style=\"font-size:10px;padding:4px 0 0px 0; background:#C0C0C0\"><b>&nbsp;MRP<br>(per item)</b></div></th>"
        . "<th rowspan=\"2\" align=\"center\" width=\"7%\" bgcolor=#C0C0C0><div style=\"font-size:10px;padding:4px 0 0px 0; background:#C0C0C0\"><b>Discount<br>(per item)</b></div></th>"
        . "<th rowspan=\"2\" align=\"center\" width=\"7%\" bgcolor=#C0C0C0><div style=\"font-size:10px;padding:4px 0 0px 0; background:#C0C0C0\"><b>Rate<br>(per item)</b></div></th>"
        . "<th rowspan=\"2\" align=\"center\" width=\"8%\" bgcolor=#C0C0C0><div style=\"font-size:10px;padding:4px 0 0px 0; background:#C0C0C0\"><b>MRP Total</b></div></th>"
        . "<th rowspan=\"2\" align=\"center\" width=\"7%\" bgcolor=#C0C0C0><div style=\"font-size:10px;padding:4px 0 0px 0; background:#C0C0C0\"><b>Total Disc</b></div></th>"
        . "<th rowspan=\"2\" align=\"center\" width=\"10%\" bgcolor=#C0C0C0><div style=\"font-size:10px;padding:4px 0 0px 0; background:#C0C0C0\"><b>Taxable value<br>(Qty * Rate)</b></div></th>"
        . "<th colspan=\"2\" align=\"center\" width=\"11%\" bgcolor=#C0C0C0><div style=\"font-size:10px;padding:4px 0 0px 0; background:#C0C0C0\"><b>CGST</b></div></th>"
        . "<th colspan=\"2\" align=\"center\" width=\"11%\" bgcolor=#C0C0C0><div style=\"font-size:10px;padding:4px 0 0px 0; background:#C0C0C0\"><b>SGST</b></div></th>"
        . "<th colspan=\"2\" align=\"center\" width=\"11%\" bgcolor=#C0C0C0><div style=\"font-size:10px;padding:4px 0 0px 0; background:#C0C0C0\"><b>IGST</b></div></th>"
        . "</tr>"
        . "<tr>"
        . "<th align=\"center\" width=\"5.5%\" bgcolor=#C0C0C0><div style=\"font-size:10px;padding:4px 0 0px 0; background:#C0C0C0\">Rate</div></th>"
        . "<th align=\"center\" width=\"5.5%\" bgcolor=#C0C0C0><div style=\"font-size:10px;padding:4px 0 0px 0; background:#C0C0C0\">Amount</div></th>"
        . "<th align=\"center\" width=\"5.5%\" bgcolor=#C0C0C0><div style=\"font-size:10px;padding:4px 0 0px 0; background:#C0C0C0\">Rate</div></th>"
        . "<th align=\"center\" width=\"5.5%\" bgcolor=#C0C0C0><div style=\"font-size:10px;padding:4px 0 0px 0; background:#C0C0C0\">Amount</div></th>"
        . "<th align=\"center\" width=\"5.5%\" bgcolor=#C0C0C0><div style=\"font-size:10px;padding:4px 0 0px 0; background:#C0C0C0\">Rate</div></th>"
        . "<th align=\"center\" width=\"5.5%\" bgcolor=#C0C0C0><div style=\"font-size:10px;padding:4px 0 0px 0; background:#C0C0C0\">Amount</div></th>"
        . "</tr></thead>";
        


//$itemslevelquery="select sum(quantity) as qty,price,discount_val,rate,sum(price) as totmrp,sum(discount_val) as totdisc,sum(taxable_value) as tottaxable,sum(cgst) as gst1,sum(sgst) as gst2,sum(igst) as gst3,tax_rate,item_code from it_invoice_items where invoice_id=$obj->id group by rate order by rate desc";
$itemslevelquery="select sum(it.quantity) as qty,it.price,it.discount_val,it.rate,sum(it.price) as totmrp,sum(it.discount_val) as totdisc,sum(it.taxable_value) as tottaxable,sum(it.cgst) as gst1,sum(it.sgst) as gst2,sum(it.igst) as gst3,it.tax_rate,c.name as cat,c.it_hsncode as hsn,it.price from it_invoice_items it,it_items i,it_categories c where c.id=i.ctg_id and barcode=it.item_code and it.invoice_id=$obj->id group by c.name,c.it_hsncode,it.price order by it.price";

//print "$itemslevelquery";
$itemsbyrate=$db->fetchObjectArray($itemslevelquery);
$i=1;
$count=1;
$cgst_5=0.0;
$cgst_12=0.0;
$cgst_18=0.0;
$cgst_28=0.0;
$igst_5=0.0;
$igst_12=0.0;
$igst_18=0.0;
$igst_28=0.0;

$txble_5=0.0;
$txble_12=0.0;
$txble_18=0.0;
$txble_28=0.0;
if($cntnew!=1)
{
   $output .= "<h2 align=\"right\">"."PTO"."</h2>";
   $output .= "<pdf:nextpage>";         
}

$output.=$outputheader.$outputheaderforitems;
$myCount=22;
$totalLinesCount=count($itemsbyrate);

foreach($itemsbyrate as $itembyrate)
{
    
    
    //print_r($itembyrate);

    //$hsnquery="select c.name as cat,c.it_hsncode as hsn from it_items i,it_categories c where c.id=i.ctg_id and barcode='$itembyrate->item_code'";
    //$hsn=$db->fetchObject($hsnquery);
    
    $output .=  "<tbody><tr><td align=\"center\" width=\"4%\"><div style=\"font-size:10px;padding:4px 0 0px 0\">$i</div></td>"
            . "<td align=\"left\" width=\"10%\"><div style=\"font-size:10px;padding:4px 0 0px 4px\">$itembyrate->cat</div></td>"
            . "<td align=\"center\" width=\"6%\"><div style=\"font-size:10px;padding:4px 4px 0px 0\">$itembyrate->hsn</div></td>"
            . "<td align=\"center\" width=\"5%\"><div style=\"font-size:10px;padding:4px 4px 0px 0\">$itembyrate->qty</div></td>"
            . "<td align=\"center\" width=\"7%\"><div style=\"font-size:10px;padding:4px 4px 0px 0\">$itembyrate->price</div></td>"
            . "<td align=\"center\" width=\"7%\"><div style=\"font-size:10px;padding:4px 4px 0px 0\">".round($itembyrate->discount_val,2)."</div></td>"
            . "<td align=\"center\" width=\"8%\"><div style=\"font-size:10px;padding:4px 4px 0px 0\">".round($itembyrate->rate,2)."</div></td>"
            . "<td align=\"center\" width=\"8%\"><div style=\"font-size:10px;padding:4px 4px 0px 0\">".round($itembyrate->totmrp,2)."</div></td>"
            . "<td align=\"center\" width=\"8%\"><div style=\"font-size:10px;padding:4px 4px 0px 0\">".round($itembyrate->totdisc,2)."</div></td>"
            . "<td align=\"center\" width=\"9%\"><div style=\"font-size:10px;padding:4px 4px 0px 0\">".round($itembyrate->tottaxable,2)."</div></td>";
     if($itembyrate->gst2==0)
     {
         $output .="<td align='center'>-</td>"
                 . "<td align='center'>-</td>"
                 . "<td align='center'>-</td>"
                 . "<td align='center'>-</td>"
            . "";
     }
    else {
         
           
           $output.= "<td align=\"center\" width=\"5.5%\"><div style=\"font-size:10px;padding:4px 4px 0px 0\">".((($itembyrate->tax_rate)/2)*100)."%</div></td>"
            . "<td align=\"center\" width=\"5.5%\"><div style=\"font-size:10px;padding:4px 4px 0px 0\">".round($itembyrate->gst1,2)."</div></td>"
            . "<td align=\"center\" width=\"5.5%\"><div style=\"font-size:10px;padding:4px 4px 0px 0\">".((($itembyrate->tax_rate)/2)*100)."%</div></td>"
            . "<td align=\"center\" width=\"5.5%\"><div style=\"font-size:10px;padding:4px 4px 0px 0\">".round($itembyrate->gst2,2)."</div></td>"
            . "";
           
           if($itembyrate->tax_rate==0.05)
           {
               $cgst_5 +=$itembyrate->gst1;
               $txble_5 +=$itembyrate->tottaxable;
           }
           else if($itembyrate->tax_rate==0.12)
           {
               $cgst_12 +=$itembyrate->gst1;
               $txble_12+=$itembyrate->tottaxable;
           }
           else if($itembyrate->tax_rate==0.18)
           {
               $cgst_18 +=$itembyrate->gst1;
               $txble_18 +=$itembyrate->tottaxable;
           }
           else if($itembyrate->tax_rate==0.28)
           {
               $cgst_28 +=$itembyrate->gst1;
               $txble_28 +=$itembyrate->tottaxable;
           }
            }
     if($itembyrate->gst3==0)
     {
         $output .="<td align='center'>-</td>"
                 . "<td align='center'>-</td>"
            . "";
     }
     else {
                  $output .="<td align=\"center\" width=\"5.5%\"><div style=\"font-size:10px;padding:4px 4px 0px 0\">".(($itembyrate->tax_rate)*100)."%</div></td>"
                 . "<td align=\"center\" width=\"5.5%\"><div style=\"font-size:10px;padding:4px 4px 0px 0\">".round($itembyrate->gst3,2)."</div></td>"
            . "";
                  
           if($itembyrate->tax_rate==0.05)
           {
               $igst_5 +=$itembyrate->gst3;
               $txble_5 +=$itembyrate->tottaxable;
           }
           else if($itembyrate->tax_rate==0.12)
           {
               $igst_12 +=$itembyrate->gst3;
               $txble_12 +=$itembyrate->tottaxable;
           }
           else if($itembyrate->tax_rate==0.18)
           {
               $igst_18 +=$itembyrate->gst3;
               $txble_18 +=$itembyrate->tottaxable;
           }
           else if($itembyrate->tax_rate==0.28)
           {
               $igst_28 +=$itembyrate->gst3;
               $txble_28 +=$itembyrate->tottaxable;
           }
     }
     $output.="</tr></tbody>";
     
    if($totalLinesCount>10){
                       if ($count >= $myCount && $count!=$totalLinesCount  ) {
                        $output .= "</table>";
                        $output .= "<h2 align=\"right\">"."PTO"."</h2>";
                        $output .= "<pdf:nextpage>";
                        $output .= $outputheader;
                       $output .= $outputheaderforitems;
                        //printHtml+="<table width=\"95%\" align=\"center\" border=\"1px\">";                                
                        //count = count-5;
                       //$totalLinesCount=$totalLinesCount-10;
                        $myCount+=22;
                       // tktlinecount+=24;
                       // print "mycount:$myCount";
                       }
                     } 
                     
                     
                     
                     if($count==$totalLinesCount && ($totalLinesCount > ($myCount-12)) && $totalLinesCount >=  ($myCount-22) ){
                         
                     if($totalLinesCount>10){
                        $output .= "</table>";
                        $output .= "<h2 align=\"right\">"."PTO"."</h2>";
                        $output .= "<pdf:nextpage>";
                        $output .= $outputheader;
                        $output .= $outputheaderforitems; 
                        
                    $ct = $myCount;
                    //print "ct>>$ct";
                    while ($ct < ($myCount+22-18)) {
                        //print "inside while";
                        $output .= "<tr>"
                                . "<td align=\"left\" width=\"4%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "<td align=\"left\" width=\"10%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "<td align=\"right\" width=\"6%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "<td align=\"right\" width=\"5%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "<td align=\"right\" width=\"7%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "<td align=\"left\" width=\"7%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "<td align=\"left\" width=\"8%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "<td align=\"left\" width=\"8%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "<td align=\"right\" width=\"8%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "<td align=\"right\" width=\"9%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "<td align=\"right\" width=\"5.5%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "<td align=\"right\" width=\"5.5%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "<td align=\"right\" width=\"5.5%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "<td align=\"right\" width=\"5.5%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "<td align=\"right\" width=\"5.5%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "<td align=\"right\" width=\"5.5%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "</tr>";
                        
                         $output .= "<tr>"
                                . "<td align=\"left\" width=\"4%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "<td align=\"left\" width=\"10%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "<td align=\"right\" width=\"6%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "<td align=\"right\" width=\"5%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "<td align=\"right\" width=\"7%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "<td align=\"left\" width=\"7%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "<td align=\"left\" width=\"8%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "<td align=\"left\" width=\"8%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "<td align=\"right\" width=\"8%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "<td align=\"right\" width=\"9%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "<td align=\"right\" width=\"5.5%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "<td align=\"right\" width=\"5.5%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "<td align=\"right\" width=\"5.5%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "<td align=\"right\" width=\"5.5%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "<td align=\"right\" width=\"5.5%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "<td align=\"right\" width=\"5.5%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "</tr>";
                        $ct++;
                    }
                 } 
                }else if($count==$totalLinesCount && $totalLinesCount < ($myCount-12) && $totalLinesCount >  ($myCount-22) ){
                    if($totalLinesCount<10){ 
                    $ct = $count;
                    while ($ct < ($myCount-12)) {
                        $output .= "<tr>"
                                . "<td align=\"left\" width=\"4%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "<td align=\"left\" width=\"10%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "<td align=\"right\" width=\"6%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "<td align=\"right\" width=\"5%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "<td align=\"right\" width=\"7%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "<td align=\"left\" width=\"7%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "<td align=\"left\" width=\"8%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "<td align=\"left\" width=\"8%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "<td align=\"right\" width=\"8%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "<td align=\"right\" width=\"9%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "<td align=\"right\" width=\"5.5%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "<td align=\"right\" width=\"5.5%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "<td align=\"right\" width=\"5.5%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "<td align=\"right\" width=\"5.5%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "<td align=\"right\" width=\"5.5%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "<td align=\"right\" width=\"5.5%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "</tr>";
                        $ct++;
                    }
                    }
                else{
                       $ct = $count;
                    while ($ct < ($myCount-16)) {
                        $output .= "<tr>"
                                . "<td align=\"left\" width=\"4%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "<td align=\"left\" width=\"10%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "<td align=\"right\" width=\"6%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "<td align=\"right\" width=\"5%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "<td align=\"right\" width=\"7%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "<td align=\"left\" width=\"7%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "<td align=\"left\" width=\"8%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "<td align=\"left\" width=\"8%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "<td align=\"right\" width=\"8%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "<td align=\"right\" width=\"9%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "<td align=\"right\" width=\"5.5%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "<td align=\"right\" width=\"5.5%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "<td align=\"right\" width=\"5.5%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "<td align=\"right\" width=\"5.5%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "<td align=\"right\" width=\"5.5%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "<td align=\"right\" width=\"5.5%\"><div style=\"padding:4px 0 0px 0\">&nbsp;</div></td>"
                                . "</tr>";
                        $ct++;
                    }  
                    }
                }
     
     
     
$count++;
$i++;


}
               

$invamtwords=$conv->getIndianCurrency(round($obj->invoice_amt,2));
$taxamtwords=$conv->getIndianCurrency($obj->tax);
$cgst_total="";
$sgst_total="";
$igst_total="";

if($obj->igst_total==0)
{
   $cgst_total="$obj->cgst_total";
     $sgst_total="$cgst_total";
     $igst_total="-";
}
 else     
 {
     $igst_total="$obj->igst_total";
     $sgst_total="-";
     $cgst_total="-";
     
 }
$output.= "<tfoot><tr>"
        . "<td></td>"
        . "<td align=\"center\"  width=\"10%\"><div style=\"font-size:10px;padding:4px 0 0px 4px\"><b>TOTAL</b></div></td>"
        . "<td></td>"
        . "<td align=\"center\" width=\"5%\"><div style=\"font-size:10px;padding:4px 4px 0px 0\">$obj->invoice_qty</div></td>"
        . "<td></td>"
        . "<td></td>"
        . "<td></td>"
        . "<td align=\"center\" width=\"8%\"><div style=\"font-size:10px;padding:4px 4px 0px 0\">$obj->total_mrp</div></td>"
        . "<td align=\"center\" width=\"8%\"><div style=\"font-size:10px;padding:4px 4px 0px 0\">$obj->discount_val</div></td>"
        . "<td align=\"center\" width=\"9%\"><div style=\"font-size:10px;padding:4px 4px 0px 0\">$obj->total_taxable_value</div></td>"
        . "<td colspan=\"2\" align=\"center\" width=\"11%\"><div style=\"font-size:10px;padding:4px 4px 0px 0\">$cgst_total</div></td>"
        . "<td colspan=\"2\" align=\"center\" width=\"11%\"><div style=\"font-size:10px;padding:4px 4px 0px 0\">$sgst_total</div></td>"
        . "<td colspan=\"2\" align=\"center\" width=\"11%\"><div style=\"font-size:10px;padding:4px 4px 0px 0\">$igst_total</div></td>"
        . "</tr></tfoot></table>";
        


$i=$i-1;


$output.= ""
        . ""
        . "<table width=\"95%\" align=\"center\" border=\"1px\">"
        . "<tr>"
        . "<td width=\"54.5%\">"//new code
        . "<table border=\"1px\">"
        . "<tr>"
        . "<td class=\"tdborder\" width=\"34%\" align=\"left\"><div style=\"font-size:10px;padding:4px 0 0px 4px\">Total Invoice Value (In Figure):</div></td>"
        . "<td class=\"tdborder\" width=\"71%\" align=\"left\"><div style=\"font-size:10px;padding:4px 0 0px 0\">".round($obj->invoice_amt,2)."</div></td>"
        . "</tr>"
        . "<tr>"
        . "<td class=\"tdborder\" width=\"34%\" align=\"left\"><div style=\"font-size:10px;padding:4px 0 0px 4px\">Total Invoice Value (In Words)</div></td>"
        . "<td class=\"tdborder\" width=\"71%\" align=\"left\"><div style=\"font-size:10px;padding:4px 0 0px 0\">$invamtwords</div></td>"
        . "</tr>"
        . "<tr>"
        . "<td class=\"tdborder\" width=\"34%\" align=\"left\"><div style=\"font-size:10px;padding:4px 0 0px 4px\">Total TAX Value (In Figure):</div></td>"
        . "<td class=\"tdborder\" width=\"71%\" align=\"left\"><div style=\"font-size:10px;padding:4px 0 0px 0\">$obj->tax</div></td>"
        . "</tr>"
        . "<tr>"
        . "<td class=\"tdborder\" width=\"34%\" align=\"left\"><div style=\"font-size:10px;padding:4px 0 0px 4px\">Total TAX Value (In Words)</div></td>"
        . "<td class=\"tdborder\" width=\"71%\" align=\"left\"><div style=\"font-size:10px;padding:4px 0 0px 0\">$taxamtwords</div></td>"
        . "</tr>"
        ."</table>"
        . "</td>"
        
        . "<td width=\"50.5%\">"
        . "<table border=1>"
        . "<tr>"
        . "<td width=\"58.8%\" align=\"center\"><div style=\"font-size:10px;padding:4px 0 0px 4px\">TOTAL OF TAXABLE VALUE</div></td>"
        . "<td width=\"46.2%\" align=\"right\"><div style=\"font-size:10px;padding:4px 4px 0px 0\">$obj->total_taxable_value</div></td>"
        . "</tr>"
        . "<tr>"
        . "<td width=\"58.8%\" align=\"center\"><div style=\"font-size:10px;padding:4px 0 0px 4px\">TOTAL OF CGST</div></td>"
        . "<td width=\"46.2%\" align=\"right\"><div style=\"font-size:10px;padding:4px 4px 0px 0\">$obj->cgst_total</div></td>"
        . "</tr>"
        . "<tr>"
        . "<td width=\"58.8%\" align=\"center\"><div style=\"font-size:10px;padding:4px 0 0px 4px\">TOTAL OF SGST</div></td>"
        . "<td width=\"46.2%\" align=\"right\"><div style=\"font-size:10px;padding:4px 4px 0px 0\">$obj->sgst_total</div></td>"
        . "</tr>"
        . "<tr>"
        . "<td width=\"58.8%\" align=\"center\"><div style=\"font-size:10px;padding:4px 0 0px 4px\">TOTAL OF IGST</div></td>"
        . "<td width=\"46.2%\" align=\"right\"><div style=\"font-size:10px;padding:4px 4px 0px 0\">$obj->igst_total</div></td>"
        . "</tr>"
        . "<tr>"
        . "<td width=\"58.8%\" align=\"center\"><div style=\"font-size:10px;padding:4px 0 0px 4px\">Round Off</div></td>"
        . "<td width=\"46.2%\" align=\"right\"><div style=\"font-size:10px;padding:4px 4px 0px 0\">+0.0</div></td>"
        . "</tr>"
        . "<tr>"
        . "<td width=\"58.8%\" align=\"center\"><div style=\"font-size:10px;padding:4px 0 0px 4px\"><b>Invoice Value</b></div></td>"
        . "<td width=\"46.2%\" align=\"right\"><div style=\"font-size:10px;padding:4px 4px 0px 0\"><b>$obj->invoice_amt</b></div></td>"
        . "</tr>"
        . "</table></td>"
        . "</tr>"
        
        . "<tr>"
        . "<td colspan=2>"
        . "<table align=\"center\" border=\"1px\">"   //last table
        . "<tr><th  width=\"22.5%\" align=\"center\"><div style=\"font-size:10px;padding:4px 0 0px 0\">Tax Rate</div></th>"
        . "<th  width=\"22.5%\" align=\"center\"><div style=\"font-size:10px;padding:4px 0 0px 0\">Taxable Value</div></th>"
        . "<th  width=\"22.5%\" align=\"center\"><div style=\"font-size:10px;padding:4px 0 0px 0\">CGST</div></th>"
        . "<th  width=\"22.5%\" align=\"center\"><div style=\"font-size:10px;padding:4px 0 0px 0\">SGST</div></th>"
        . "<th  width=\"22.5%\" align=\"center\"><div style=\"font-size:10px;padding:4px 0 0px 0\">IGST</div></th>"
        ."<th  rowspan=\"6\" width=\"54.7%\" align=\"center\"><div style=\"font-size:10px;padding:4px 0 0px 0\"><p>&nbsp;&nbsp;We declare that this invoice shows the actual price of the</br>&nbsp;&nbsp;&nbsp;&nbsp;goods described and that all particulars are true and </br> &nbsp;&nbsp;&nbsp;&nbsp;correct.\n".
"&nbsp;This is a Computer Generated Invoice.</p></div></th>"
        . "<th  rowspan=\"6\" width=\"42.8%\" align=\"center\"><div style=\"font-size:10px;padding:4px 0 0px 0\"><b>Fashionking Brands Pvt Ltd&nbsp;</b></br></br></br><b>Authorised Signatory&nbsp;</b></div></th>"
        . "</tr>"
        . "<tr><td width=\"21%\" align=\"center\"><div style=\"font-size:10px;padding:4px 0 0px 0\"><b>5%</b>"
        . "</div></td>"
        . "<td width=\"21%\" align=\"center\"><div style=\"font-size:10px;padding:4px 0 0px 0\">".round($txble_5,2)."</div></td>"
        . "<td width=\"21%\" align=\"center\"><div style=\"font-size:10px;padding:4px 0 0px 0\">".round($cgst_5,2)."</div></td>"
        . "<td width=\"21%\" align=\"center\"><div style=\"font-size:10px;padding:4px 0 0px 0\">".round($cgst_5,2)."</div></td>"
        . "<td width=\"21%\" align=\"center\"><div style=\"font-size:10px;padding:4px 0 0px 0\">".round($igst_5,2)."</div></td>"
        . "</tr>"
        . "<tr>"
        . "<td width=\"21%\" align=\"center\"><div style=\"font-size:10px;padding:4px 0 0px 0\"><b>12%</b></div></td>"
         . "<td width=\"21%\" align=\"center\"><div style=\"font-size:10px;padding:4px 0 0px 0\">".round($txble_12,2)."</div></td>"
        . "<td width=\"21%\" align=\"center\"><div style=\"font-size:10px;padding:4px 0 0px 0\">".round($cgst_12,2)."</div></td>"
        . "<td width=\"21%\" align=\"center\"><div style=\"font-size:10px;padding:4px 0 0px 0\">".round($cgst_12,2)."</div></td>"
       
        . "<td width=\"21%\" align=\"center\"><div style=\"font-size:10px;padding:4px 0 0px 0\">".round($igst_12,2)."</div></td>"
        . "</tr>"
        . "<tr><td width=\"21%\" align=\"center\"><div style=\"font-size:10px;padding:4px 0 0px 0\"><b>18%</b></div></td>"
        . "<td width=\"21%\" align=\"center\"><div style=\"font-size:10px;padding:4px 0 0px 0\">".round($txble_18,2)."</div></td>"
        . "<td width=\"21%\" align=\"center\"><div style=\"font-size:10px;padding:4px 0 0px 0\">".round($cgst_18,2)."</div></td>"
        . "<td width=\"21%\" align=\"center\"><div style=\"font-size:10px;padding:4px 0 0px 0\">".round($cgst_18,2)."</div></td>"
        
        . "<td width=\"21%\" align=\"center\"><div style=\"font-size:10px;padding:4px 0 0px 0\">".round($igst_18,2)."</div></td>"
        . "</tr>"
        . "<tr>"
        . "<td width=\"21%\" align=\"center\"><div style=\"font-size:10px;padding:4px 0 0px 0\"><b>28%</b></div></td>"
        . "<td width=\"21%\" align=\"center\"><div style=\"font-size:10px;padding:4px 0 0px 0\">".round($txble_28,2)."</div></td>"
        . "<td width=\"21%\" align=\"center\"><div style=\"font-size:10px;padding:4px 0 0px 0\">".round($cgst_28,2)."</div></td>"
        . "<td width=\"21%\" align=\"center\"><div style=\"font-size:10px;padding:4px 0 0px 0\">".round($cgst_28,2)."</div></td>"
        . "<td width=\"21%\" align=\"center\"><div style=\"font-size:10px;padding:4px 0 0px 0\">".round($igst_28,2)."</div></td>"
        . "</tr>"
        . "<tr><td width=\"21%\" align=\"center\"><div style=\"font-size:10px;padding:4px 0 0px 0\"><b>Total</b></div></td>"
        . "<td width=\"21%\" align=\"center\"><div style=\"font-size:10px;padding:4px 0 0px 0\">$obj->total_taxable_value</div></td>"
        . "<td width=\"21%\" align=\"center\"><div style=\"font-size:10px;padding:4px 0 0px 0\">$obj->cgst_total</div></td>"
        . "<td width=\"21%\" align=\"center\"><div style=\"font-size:10px;padding:4px 0 0px 0\">$obj->sgst_total</div></td>"
        . "<td width=\"21%\" align=\"center\"><div style=\"font-size:10px;padding:4px 0 0px 0\">$obj->igst_total</div></td>"
        . "</tr>"
        . "</table>"
        . "</td>"
        . ""
        . "</tr>"
        . "</table>";
        
}

$output."</body></html>";
echo "$output";
$myFile = '/var/www/html/linenking/home/tmp/TestInvoice.html'; // or .php   
$fh = fopen($myFile, 'w'); // or die("error");  
$stringData = "your html code php code goes here";   
fwrite($fh, $output);
fclose($fh);





//print "$cmd";
       
       if(isset($invno) && trim($invno)!="" && trim($invno) != "-1")
       {
           $fname="/var/www/html/linenking/home/tmp/Invoice-".$invno.".pdf";
           $location="/var/www/html/linenking/home/tmp/Invoice-".$invno.".pdf";
       }
       else {
           
           $fname="/var/www/html/linenking/home/tmp/Invoices$from"."to".$to.".pdf";
           $location="/var/www/html/linenking/home/tmp/Invoices$from"."to".$to.".pdf";
        }
       
$cmd = "pisa -s /var/www/html/linenking/home/tmp/TestInvoice.html ".$location;
$result = shell_exec($cmd);

if (count($errors) > 0) {
	$_SESSION['form_errors'] = $errors;
	$redirect = "reprintinvoices";
        session_write_close();
        header("Location: ".DEF_SITEURL.$redirect);
        exit;
}

header('Content-Type: application/pdf');
header("Content-Disposition: attachment; filename=\"".basename($fname)."\"");
echo file_get_contents($fname);




