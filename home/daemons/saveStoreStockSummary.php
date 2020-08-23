#!/usr/bin/php -q
<?php
include '../../it_config.php';
//include '/var/www/cottonking_new/it_config.php';

///var/www/limelight_new/it_config.php
require_once 'lib/db/DBConn.php';
require_once 'lib/core/Constants.php';
require_once "Classes/PHPExcel.php";
require_once 'Classes/PHPExcel/Writer/Excel2007.php';
require_once "lib/email/EmailHelper.php";


try{
    $db = new DBConn();
    $query = "select * from it_codes where usertype = ".UserType::Dealer;
    $allStores = $db->fetchObjectArray($query);
    $cnt=0;
    foreach($allStores as $sobj){
//        print "\n";
//        print_r($sobj);
       //for each store save daily 
       //store stock value, stock qty n stock intransit 
       //step 1 : Calc Store's Stock in value  n qty
        $squery = "select sum(cs.quantity) as stockqty, sum(cs.quantity * i.MRP) as stockvalue from it_current_stock cs , it_items i where cs.barcode = i.barcode and cs.store_id = $sobj->id ";
//        print "\n".$squery;
        $stobj = $db->fetchObject($squery);
        if(isset($stobj)){
           $stockqty = $stobj->stockqty;
           $stockvalue = $stobj->stockvalue;
        }else{
            //default
            $stockqty = 0;
            $stockvalue = 0;
        }
        //step 2 : Fetch store stock intransit
        $tquery2 = "select sum(i.MRP*oi.quantity) as intransit_stock_value from it_invoices o , it_invoice_items oi , it_items i where oi.invoice_id = o.id and o.invoice_type in ( 0, 6) and o.store_id = $sobj->id and o.is_procsdForRetail = 0 and oi.item_code = i.barcode";
//        print "\n$tquery2";
        $tobj = $db->fetchObject($tquery2);
        if(isset($tobj) && trim($tobj->intransit_stock_value)!=""){ $intransit_stock_val = $tobj->intransit_stock_value ;}
        else{ $intransit_stock_val = 0; }
        
        $qry = "insert into it_store_stock_summary set store_id = $sobj->id , stock_datetime = now() , stock_value = $stockvalue , stock_qty = $stockqty , stock_intransit = $intransit_stock_val , createtime = now() ";
        $db->execInsert($qry);
//        print "\n$qry";
        
        $cnt++; 
    }
    
    
    
}catch(Exception $xcp){
   print $xcp->getMessage(); 
}

//print "Tot_rows inserted: ".$cnt;
