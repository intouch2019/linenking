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
/*$records = "311-1718002<>7<>1511848978000<>10998.01<>9.0<>13155.0<>1925.89<>0.0<>1178.36<><>HO<><><><><>1<> <>11745.54<>
 1925.89<>9819.65<>589.18<>589.18<>0.0<==>GST 12%<>0.12<>1409.46<++><==>Fashionking Brands Pvt. Ltd. (erstwhile Cottonking Pvt.
 Ltd.)<==>8902001645582<>Narrow Trouser<>1195.0<>3.0<>3585.0<>1066.96<>3200.88<>524.84<>2676.04<>160.56<>160.56<>0.0<>0.12
 <++>8902002260173<>TROUSER<>1595.0<>2.0<>3190.0<>1424.11<>2848.22<>467.01666666666665<>2381.2033333333334<>142.87333333333333
 <>142.87333333333333<>0.0<>0.12<++>8902002248027<>TROUSER<>1595.0<>4.0<>6380.0<>1424.11<>5696.44<>934.0333333333333<>
 4762.406666666667<>285.74666666666667<>285.74666666666667<>0.0<>0.12<++><==>80<><>|||||";*/
 

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
    $store_id = $gCodeId;
    //$store_id = 86; // comment when this page given for live
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
//            $store_id="";
            $tot_qty=0;
            $tot_amt=0;
            $items=array(); 
            if ($invfields) {
                    $invoice_no = $db->safe($invfields[0]);
                    $exists = $db->fetchObject("select * from it_saleback_invoices where invoice_no=$invoice_no");
			if(isset($exists)){      // update saleback inv if already exists
                            $invoice_type = $db->safe($invfields[1]);
                            $dt = $invfields[2];
                            $dt /= 1000;
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


                            $iClause = "";
                            if(trim($rate_subtotal)!=""){ $iClause .= " , rate_subtotal = $rate_subtotal "; }
                            if(trim($discount_val)!=""){ $iClause .= " , discount_val = $discount_val "; }
                            if(trim($total_taxable_value)!=""){ $iClause .= " , total_taxable_value = $total_taxable_value "; }
                            if(trim($cgst_total)!=""){ $iClause .= " , cgst_total = $cgst_total "; }
                            if(trim($sgst_total)!=""){ $iClause .= " , sgst_total = $sgst_total "; }
                            if(trim($igst_total)!=""){ $iClause .= " , igst_total = $igst_total "; }

                            $sqlquery = "update it_saleback_invoices set invoice_text = $invoice_text , invoice_amt = $invoice_amt , "
                                    . "invoice_qty=$invoice_qty , total_mrp = $total_mrp , updatetime = now()  $iClause "
                                    . " where id = $exists->id ";
                           // print "<br>$sqlquery";
                            $db->execUpdate($sqlquery);


                            //cheque amt update
                            $pickNstorearr = explode("<>",$invRecord[4]);
                            $pickingId = $pickNstorearr[0];
                            //$stock_balance_flag = isset($pickNstorearr[2]) ? $pickNstorearr[2] : 0 ;

                            $query = " update it_invoices set group_id = $pickingId where invoice_no=$invoice_no";
                            $db->execUpdate($query);

                             //first delete exists invoice items and reinsert into that table
                            $itemdelete=$db->execQuery("delete from it_saleback_invoice_items where invoice_id=$exists->id");

                            //insert into it_invoice_items after delete 
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
                                $query = "insert into it_saleback_invoice_items set invoice_id=$exists->id, item_code=$ck_code, "
                                        . " price=$price, quantity=$quantity $liClause";
                                $COUNT=+$quantity;
                                $inserted = $db->execInsert($query);
        //                        if (!$inserted) { break; }
                                $items[$ck_code] = $quantity;
                           }
                            //push to server chgs if it is only sale back invoice
                            if($invoice_type == "'7'"){
                            $query = "select * from it_saleback_invoices where id = $exists->id ";               
                            $invoice = $db->fetchObject($query);
                            $json_obj = array();  
                                    $json_invoice = array();
                                    $json_invoice['invoice_id'] = $invoice->id;
                                    $json_invoice['invoice_no'] = $invoice->invoice_no;
                                    $json_invoice['invoice_dt'] = $invoice->invoice_dt;
                                    $json_invoice['invoice_amt'] = $invoice->invoice_amt;
                                    $json_invoice['invoice_qty'] = intval($invoice->invoice_qty);
                                    $json_invoice['total_mrp'] = $invoice->total_mrp;
                                    $json_invoice['discount_1'] = $invoice->discount_1;
                                    $json_invoice['discount_2'] = $invoice->discount_2;
                                    $json_invoice['tax'] = $invoice->tax;
                                    $json_invoice['payment'] = $invoice->payment;
                                    //gst changes
                                    $json_invoice['rate_subtotal'] = $invoice->rate_subtotal;
                                    $json_invoice['discount_val'] = $invoice->discount_val;
                                    $json_invoice['total_taxable_value'] = $invoice->total_taxable_value;
                                    $json_invoice['cgst_total'] = $invoice->cgst_total;
                                    $json_invoice['sgst_total'] = $invoice->sgst_total;
                                    $json_invoice['igst_total'] = $invoice->igst_total;
                                    $json_invoice['store_id'] = $invoice->store_id;



                                    $items = $db->fetchObjectArray("select item_code,price,quantity,total_price_qty,"
                                            . "rate,total_rate_qty,discount_val,taxable_value,cgst,sgst,igst,tax_rate "
                                            . "from it_saleback_invoice_items where invoice_id = $invoice->id");
                                    $json_items = array();
                                    foreach ($items as $item) {
                                        $item->quantity = intval($item->quantity);
                                        $json_items[] = json_encode($item);
                                    }
                                    $json_invoice['items']=  json_encode($json_items);

                             $server_ch = json_encode($json_invoice);     
                             //print $server_ch;
                             $ser_type = changeType::saleback;
                             $CKWH_id = DEF_CK_WAREHOUSE_ID;
                          // here $invoice_id is id of table it_invoices so it becomes data_id
                             $serverCh->save($ser_type, $server_ch,$CKWH_id,$exists->id);
                            }
                        break;
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
                    //$round_off = $invfields[23];                    
                    
                    $iClause = "";
                    if(trim($rate_subtotal)!=""){ $iClause .= " , rate_subtotal = $rate_subtotal "; }
                    if(trim($discount_val)!=""){ $iClause .= " , discount_val = $discount_val "; }
                    if(trim($total_taxable_value)!=""){ $iClause .= " , total_taxable_value = $total_taxable_value "; }
                    if(trim($cgst_total)!=""){ $iClause .= " , cgst_total = $cgst_total "; }
                    if(trim($sgst_total)!=""){ $iClause .= " , sgst_total = $sgst_total "; }
                    if(trim($igst_total)!=""){ $iClause .= " , igst_total = $igst_total "; }
                    //if(trim($round_off)!=""){ $iClause .= " , round_off = $round_off "; }                    
                   
                    //if any of the value is blank;
                    //print("\ninv_dt:".trim($invoice_dt)."\ninv_amt:".trim($invoice_amt)."\ninv_qty:".trim($invoice_qty)."\ntotal_mrp:".trim($total_mrp)."\ndisc1:".trim($discount_1)."\ndisc2:".trim($discount_2)."\ntax:".trim($tax)."\npayment:".trim($payment)."\ntax_type:".trim($tax_type)."\ntax_per:".trim($tax_percent)."\n");
                    if(trim($invoice_dt)== "" || trim($invoice_amt)== "" || trim($invoice_qty) == "" || trim($total_mrp) == "" || trim($discount_1) == "" || trim($discount_2) == "" || trim($tax) == "" || trim($payment) == "" || trim($tax_type) == "" || trim($tax_percent) == "" || trim($transport_dtl) == "" ){
                        $errflg=1; break;
                    }
//                    $store_nm = $invRecord[5];
                    $store_nm = $invRecord[2];                  
                        $query = "insert into it_saleback_invoices set invoice_text = $invoice_text,invoice_no=$invoice_no, "
                                . "invoice_dt=$invoice_dt, invoice_type=$invoice_type, invoice_amt=$invoice_amt, "
                                . "store_name='$store_nm', invoice_qty=$invoice_qty , total_mrp = $total_mrp ,"
                                . " discount_1 = $discount_1 , discount_2 = $discount_2, tax = $tax , "
                                . "tax_type = $tax_type , tax_percent = $tax_percent , payment = $payment  $iClause ";
                        
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
//           $stock_balance_flag = isset($pickNstorearr[2]) ? $pickNstorearr[2] : 0 ;          
//           if(!$is_limelight && !$is_tyson && trim($pickingId)!=""){ //means pickgroup provided
//               $pickClause = " , pickgroup_id = $pickingId ";
//           }else{
//               $pickClause = "";
//           }
            
            $query = " update it_saleback_invoices set store_id = $store_id , group_id = $pickingId where id = $invoice_id ";
            //print $query;
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
                        $query = "insert into it_saleback_invoice_items set invoice_id=$invoice_id, item_code=$ck_code, "
                                . " price=$price, quantity=$quantity $liClause ";
                        $inserted = $db->execInsert($query);
                        if (!$inserted) { $errflg = 2; break; }
                        $items[$ck_code] = $quantity;
                        
                        $qry = "select * from it_current_stock where barcode = $ck_code and store_id = $store_id ";
                        $exists = $db->fetchObject($qry);
                        if($exists){                        
                              $db->execUpdate("update it_current_stock set quantity = quantity - $quantity , updatetime = now()"
                                   . " where id = $exists->id ");
                        }else{
                        $iqry = "select * from it_items where barcode = $ck_code ";
                        $iobj = $db->fetchObject($iqry);
                        if(isset($iobj)){
                        $ctg_id = $iobj->ctg_id;
                        $design_id = $iobj->design_id;
                        $style_id = $iobj->style_id;
                        $size_id = $iobj->size_id;
                        }else{
                        $ctg_id = 0;
                        $design_id = 0;
                        $style_id = 0;
                        $size_id = 0;
                        }
                        $insQry = "insert into it_current_stock set barcode = $ck_code , store_id = $store_id , quantity = $quantity ,"
                                . " ctg_id = $ctg_id, design_id = $design_id , style_id = $style_id , size_id = $size_id ,"
                                . " createtime = now() ";
                        $inserted = $db->execInsert($insQry);
                }
           }
           
          
           if ($invoice_id ) {
            //$db->execUpdate("update it_invoices set invoice_amt=$invoice_amt, invoice_qty=$invoice_qty where id=$invoice_id");
//           }
           
           // insert invoice into it_server_changes
            if($invoice_type == "'7'"){ // only saleback inv shld get inserted in it_server_changes
                $query = "select * from it_saleback_invoices where id = $invoice_id ";                
                $invoice = $db->fetchObject($query);
                $json_obj = array();   
                        $json_invoice = array();
                        $json_invoice['invoice_id'] = $invoice->id;
                        $json_invoice['invoice_no'] = $invoice->invoice_no;
                        $json_invoice['invoice_dt'] = $invoice->invoice_dt;
                        $json_invoice['invoice_amt'] = $invoice->invoice_amt;
                        $json_invoice['invoice_qty'] = intval($invoice->invoice_qty);
                        $json_invoice['total_mrp'] = $invoice->total_mrp;
                        $json_invoice['discount_1'] = $invoice->discount_1;
                        $json_invoice['discount_2'] = $invoice->discount_2;
                        $json_invoice['tax'] = $invoice->tax;
                        $json_invoice['payment'] = $invoice->payment;
                        //gst changes 
                        $json_invoice['rate_subtotal'] = $invoice->rate_subtotal;
                        $json_invoice['discount_val'] = $invoice->discount_val;
                        $json_invoice['total_taxable_value'] = $invoice->total_taxable_value;
                        $json_invoice['cgst_total'] = $invoice->cgst_total;
                        $json_invoice['sgst_total'] = $invoice->sgst_total;
                        $json_invoice['igst_total'] = $invoice->igst_total;
                        $json_invoice['store_id'] = $invoice->store_id;
                        
                        $items = $db->fetchObjectArray("select item_code,price,quantity,total_price_qty,rate,"
                                . "total_rate_qty,discount_val,taxable_value,cgst,sgst,igst,tax_rate from"
                                . " it_saleback_invoice_items where invoice_id = $invoice->id");
                        $json_items = array();
                        foreach ($items as $item) { 
                            $item->quantity = intval($item->quantity);
                            $json_items[] = json_encode($item);
                        }
                        $json_invoice['items']=  json_encode($json_items);
                       
                 $server_ch = json_encode($json_invoice);                         
                 $ser_type = changeType::saleback; 
                 $ckwhid = DEF_CK_WAREHOUSE_ID;
              // here $invoice_id is id of table it_invoices so it becomes data_id
                $serverCh->save($ser_type, $server_ch,$ckwhid,$invoice_id);
            }
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
