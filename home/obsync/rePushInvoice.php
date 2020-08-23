<?php
include "checkAccess.php";
require_once "../../it_config.php";
require_once "lib/logger/clsLogger.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once "lib/serverChanges/clsServerChanges.php";
require_once "lib/logger/clsLogger.php";

extract($_POST);

if(!isset($record) || trim($record) == ""){	
	print "1::Missing parameter [record]";
	return;
}

//$record = "L161702123<>Sat Oct 08 12:51:36 IST 2016<>63<>27814.0<>37620.0<>9405.0<>1975.05<>1574.397";

try{
    $db = new DBConn();
    $store_id = $gCodeId;
   //$store_id = 83;
    $serverCh = new clsServerChanges();
    $arr = explode("<>", $record);
    $invoice_no = trim($arr[0]);
    
    if(trim($invoice_no)==""){
        print "1::Invoice No missing";
        return;
    }
    
    //check if existing invoice no is provided
    $invoice_no_db = $db->safe(trim($invoice_no));
    $query =  "select sp.*  from  it_sp_invoices sp where sp.invoice_no = $invoice_no_db  and store_id = $store_id ";
    //print $query;
    $invoice = $db->fetchObject($query);
    
    if(isset($invoice) && !empty($invoice) && $invoice != null){
        // now push to server changes
        
        $invoice = $db->fetchObject($query);
        $json_obj = array();   
        $json_invoice = array();
        $json_invoice['invoice_id'] = $invoice->id;
        $json_invoice['store_id'] = $invoice->store_id;
        $json_invoice['invoice_no'] = $invoice->invoice_no;
        $json_invoice['invoice_dt'] = $invoice->invoice_dt;
        $json_invoice['invoice_amt'] = $invoice->invoice_amt;
        $json_invoice['invoice_qty'] = $invoice->invoice_qty;
        $json_invoice['total_mrp'] = $invoice->total_mrp;
        $json_invoice['discount_1'] = $invoice->discount_1;
        $json_invoice['discount_2'] = $invoice->discount_2;
        $json_invoice['tax'] = $invoice->tax;
        $json_invoice['payment'] = $invoice->payment;
        $json_invoice['tax_type'] = $invoice->tax_type;
        $json_invoice['tax_percent'] = $invoice->tax_percent;


        $items = $db->fetchObjectArray("select barcode,price,quantity from it_sp_invoice_items where invoice_id = $invoice->id");
        $json_items = array();
        foreach ($items as $item) {                               
            $json_items[] = json_encode($item);                             
        }
        $json_invoice['items']=  json_encode($json_items);
                                                                       
        $server_ch = json_encode($json_invoice);       
       // print_r($json_invoice);
        $ser_type = changeType::invoices;  
        $sp_store_id = $invoice->store_id;
     // here $invoice_id is id of table it_invoices so it becomes data_id
       $serverCh->save($ser_type, $server_ch,$store_id,$invoice->id);
           
           
           print "0::success";
           return;
    }else{
        //print "1::Invalid invoice no provided ";
        print "0::success"; // to delete other invs pushed by mistake
        return;
    }
    
    print "1::null";
    
}catch(Exception $xcp){
    print $xcp->getMessage();
}