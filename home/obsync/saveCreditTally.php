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
$logger = new clsLogger();

if(!isset($records) || trim($records) == ""){
	$logger->logError("Missing parameter [records]:".print_r($_POST, true));
	print "1::Missing parameter [records]";
	return;
}
//print($records);


try {
    $db = new DBConn();
    $serverCh = new clsServerChanges();
    $clsLogger = new clsLogger();
    $errflg=0;
    //check if invoice text is complete ,then only proceed.
    if (strpos($records,'|||||') === false) {   
        print "1::Invoice text incomplete";
        return;
    }
    $arr = explode("|||||",$records);
    
$is_limelight=false;
$is_tyson=false;
    foreach ($arr as $record) {
        $invoice_text = $db->safe(trim($record));
        if (trim($record) == "") { continue; }
        $invRecord = explode("<==>", $record);
        if (count($invRecord) == 0) { continue; }
        $invHeader = $invRecord[0];
        $invfields = explode("<>",$invHeader);
        $stock_balance_flag =  0; // default kept as 0
//        $reqnofields = sizeof($invfields);        
//        // as the header should have 15 fields in it
//        if($reqnofields != 15 ){ $errflg=1; break;}
        

        //----------------------------------------------------------------------//
            $invoice_id=false; 
            $invoice_type = null;
            $store_id="";
            $tot_qty=0;
            $tot_amt=0;
            $items=array(); 
            if ($invfields) {
                    $invoice_no = $db->safe($invfields[0]);
                    $exists = $db->fetchObject("select * from it_invoices_creditnote where invoice_no=$invoice_no");
           //         if ($exists) break; // stop if invoice_no already exists
			if(isset($exists)){
                            $deleteitemQuery="delete from it_invoice_items_creditnote where invoice_id=$exists->id";
                            $db->execQuery($deleteitemQuery);
                            
                            $deleteinvQuery="delete from it_invoices_creditnote where id=$exists->id";
                            $db->execQuery($deleteinvQuery);
                        
                       }

                    $invoice_type = $db->safe($invfields[1]);
//                    $invoice_dt = $db->safe($invfields[2]);
                    $dt = $invfields[2];
                    $dt /= 1000;
                    $invoice_dt = $db->safe(date("Y-m-d H:i:s", $dt));
                    $invoice_amt = floatval($invfields[3]);		
                    $invoice_qty = floatval($invfields[4]);
                    $total_mrp  = doubleval($invfields[5]);
                    $discount_1  = doubleval($invfields[6]);
                    $discount_2  = doubleval($invfields[7]);
                    $tax  = doubleval($invfields[8]);
                    $payment  = $db->safe($invfields[9]);
                    $tax_type = $db->safe($invfields[11]);
                    $tax_percent = doubleval($invfields[12]);
                    $transport_dtl = $db->safe($invfields[13]);
                    $remarks = $db->safe($invfields[14]);
                    //??= [15]
                    //??=[16]
                    $rate_subtotal = $invfields[17];
                    $discount_val = $invfields[18];
                    $total_taxable_value = $invfields[19];
                    $cgst_total = $invfields[20];
                    $sgst_total = $invfields[21];
                    $igst_total = $invfields[22];
                    $round_off = $invfields[23];     
                    $irn=$invfields[24]; 
                    $ack_no=$invfields[25]; 
                    $ack_date=$invfields[26];                    
                    
                    $iClause = "";
                    if(trim($rate_subtotal)!=""){ $iClause .= " , rate_subtotal = $rate_subtotal "; }
                    if(trim($discount_val)!=""){ $iClause .= " , discount_val = $discount_val "; }
                    if(trim($total_taxable_value)!=""){ $iClause .= " , total_taxable_value = $total_taxable_value "; }
                    if(trim($cgst_total)!=""){ $iClause .= " , cgst_total = $cgst_total "; }
                    if(trim($sgst_total)!=""){ $iClause .= " , sgst_total = $sgst_total "; }
                    if(trim($igst_total)!=""){ $iClause .= " , igst_total = $igst_total "; }
                    if(trim($round_off)!=""){ $iClause .= " , round_off = $round_off "; }     
                     if(trim($round_off)!=""){ $iClause .= " , irn_no = '$irn' "; }   
                      if(trim($round_off)!=""){ $iClause .= " , ack_no ='$ack_no' "; }   
                       if(trim($round_off)!=""){ $iClause .= " , ack_date = '$ack_date' "; }                   
                   
                    //if any of the value is blank;
                    //print("\ninv_dt:".trim($invoice_dt)."\ninv_amt:".trim($invoice_amt)."\ninv_qty:".trim($invoice_qty)."\ntotal_mrp:".trim($total_mrp)."\ndisc1:".trim($discount_1)."\ndisc2:".trim($discount_2)."\ntax:".trim($tax)."\npayment:".trim($payment)."\ntax_type:".trim($tax_type)."\ntax_per:".trim($tax_percent)."\n");
                    if(trim($invoice_dt)== "" || trim($invoice_amt)== "" || trim($invoice_qty) == "" || trim($total_mrp) == "" || trim($discount_1) == "" || trim($discount_2) == "" || trim($tax) == "" || trim($payment) == "" || trim($tax_type) == "" || trim($tax_percent) == "" || trim($transport_dtl) == "" ){
                        $errflg=1; break;
                    }
                    $store_nm = $invRecord[5];
                    $store_id = $invRecord[2];                  
                        $storeobj = $db->fetchObject("select * from it_codes where usertype = ".UserType::Dealer." and id = $store_id");                    
                        $query = "insert into it_invoices_creditnote set invoice_text = $invoice_text,invoice_no=$invoice_no, invoice_dt=$invoice_dt, invoice_type=$invoice_type, invoice_amt=$invoice_amt, store_name='$store_nm', store_id = $store_id ,invoice_qty=$invoice_qty , total_mrp = $total_mrp , discount_1 = $discount_1 , discount_2 = $discount_2, tax = $tax , tax_type = $tax_type , tax_percent = $tax_percent , payment = $payment  $iClause ";
                        
                        $invoice_id = $db->execInsert($query); 
                    
                    if (!$invoice_id) {$errflg=2; break; }
                
            } 
            //store tax info           
            $taxinfo = $invRecord[1];
            
            if(trim($taxinfo)!=""){              
                //saveInvTaxes($invoice_id,$taxinfo);
            }
            
            //picking details
           $pickNstorearr = explode("<>",$invRecord[4]);
           $pickingId = $pickNstorearr[0];
           $stock_balance_flag = isset($pickNstorearr[2]) ? $pickNstorearr[2] : 0 ;          
           if(!$is_limelight && !$is_tyson && trim($pickingId)!=""){ //means pickgroup provided
               $pickClause = " , pickgroup_id = $pickingId ";
           }else{
               $pickClause = "";
           }
            
            $query = "update it_invoices_creditnote set store_id = $store_id  $pickClause where id = $invoice_id ";
            $db->execUpdate($query);
            $itemlines = explode("<++>", $invRecord[3]);
           
            foreach ($itemlines as $currlineitem) {
                        $currlineitem=trim($currlineitem);
                        if ($currlineitem == "") { continue; }
                        $fields = explode("<>", $currlineitem );
                        $ck_code = $db->safe($fields[0]);
    //		      on the [1] is coming ctg_name not used
                        $price = floatval($fields[2]);
                        $quantity = floatval($fields[3]);
                        $total_price_qty = floatval($fields[4]); //price * quantity
                        $rate = floatval($fields[5]);
                        $total_rate_qty = floatval($fields[6]); // rate * quantity
                        $discount_val = floatval($fields[7]);
                        $taxable_value = floatval($fields[8]);
                        $cgst = floatval($fields[9]);
                        $sgst = floatval($fields[10]);
                        $igst = floatval($fields[11]);
                        $tax_rate = floatval($fields[12]);
                        
                        $liClause = "";
                        if(trim($total_price_qty)!=""){ $liClause .= " , total_price_qty = $total_price_qty "; }
                        if(trim($rate)!=""){ $liClause .= " , rate = $rate "; }
                        if(trim($total_rate_qty)!=""){ $liClause .= " , total_rate_qty = $total_rate_qty "; }
                        if(trim($discount_val)!=""){ $liClause .= " , discount_val = $discount_val "; }
                        if(trim($taxable_value)!=""){ $liClause .= " , taxable_value = $taxable_value "; }
                        if(trim($cgst)!=""){ $liClause .= " , cgst = $cgst "; }
                        if(trim($sgst)!=""){ $liClause .= " , sgst = $sgst "; }
                        if(trim($igst)!=""){ $liClause .= " , igst = $igst "; }
                        if(trim($tax_rate)!=""){ $liClause .= " , tax_rate = $tax_rate "; }
                        
                        $tot_qty += $quantity;
                        $tot_amt += ($price * $quantity);
                        $query = "insert into it_invoice_items_creditnote set invoice_id=$invoice_id, item_code=$ck_code,  price=$price, quantity=$quantity $liClause ";
                        $inserted = $db->execInsert($query);
                        if (!$inserted) { $errflg = 2; break; }
                        $items[$ck_code] = $quantity;
           }
          
    }
    
    
//    $db->closeConnection();
    if($errflg == 1){
     print "1::error header parameters missing ";
    }if($errflg == 2){
     print "2::Did not Insert";
    }else{
     print "0::Success";
    }
    
} catch (Exception $ex) {
	print "1::Error-".$ex->getMessage();
}


function getMessage($pickgroup) {
$message = '<table border="0">';
$message .= "<tr>";
$message .= "<th colspan=2>$pickgroup->store_name</th>";
$message .= "</tr>";
$message .= "<tr>";
$message .= "<td>Order No:</td><td>$pickgroup->order_nos</td>";
$message .= "</tr>";
$message .= "<tr>";
$message .= "<td>Order Quantity:</td><td>$pickgroup->order_qty</td>";
$message .= "</tr>";
$message .= "<tr>";
$message .= "<td>Order Amount:</td><td>$pickgroup->order_amount</td>";
$message .= "</tr>";
$message .= "<tr>";
$message .= "<td>Designs:</td><td>$pickgroup->num_designs</td>";
$message .= "</tr>";
$message .= "<tr>";
$message .= "<td>Invoice No:</td><td>$pickgroup->invoice_no</td>";
$message .= "</tr>";
$message .= "<tr>";
$message .= "<td>Shipped Qty:</td><td>$pickgroup->shipped_qty</td>";
$message .= "</tr>";
$message .= "<tr>";
$message .= "<td>Shipped MRP:</td><td>$pickgroup->shipped_mrp</td>";
$message .= "</tr>";
$message .= "<tr>";
$message .= "<td>Cheque Amount:</td><td>$pickgroup->cheque_amt</td>";
$message .= "</tr>";
$message .= "<tr>";
$message .= "<td>Cheque Detail:</td><td>$pickgroup->cheque_dtl</td>";
$message .= "</tr>";
$message .= "<tr>";
$message .= "<td>Transport Detail:</td><td>$pickgroup->transport_dtl</td>";
$message .= "</tr>";
$message .= "<tr>";
$message .= '<td colspan=2 style="font-weight:bold;color:#ff0000;">Remarks:<br />'.$pickgroup->remark.'</td>';
$message .= "</tr>";
$message .= "<tr>";
$message .= "<td>Shipped Date:</td><td>".mmddyy($pickgroup->shipped_time)."</td>";
$message .= "</tr>";
$message .= '</table>';
return $message;
}

//
//function saveInvTaxes($invoice_id,$taxinfo){
//    $db = new DBConn();
//    $taxlines = explode("<++>",$taxinfo);
//    foreach($taxlines as $currtaxlineitem){
//        $currtaxlineitem=trim($currtaxlineitem);
//        if ($currtaxlineitem == "") { continue; }
//        $fields = explode("<>", $currtaxlineitem );
//        $tax_type = $fields[0];
//        $tax_per = $fields[1];
//        $tax_amount = $fields[2];
//        if(trim($tax_type)!="" && trim($tax_per)!="" && trim($tax_amount) != ""){
//            $tax_type_db = $db->safe(trim($tax_type));
//            $query = "insert into it_invoice_taxes set invoice_id = $invoice_id , tax_type = $tax_type_db,tax_percent = $tax_per, tax_amount = $tax_amount , createtime = now() ";
//           // print "\nTAXES INS QRY: $query\n";
//            $db->execInsert($query);
//        }
//    }
//    $db->closeConnection();
//}