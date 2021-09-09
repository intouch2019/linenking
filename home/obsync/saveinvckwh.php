<?php
include "checkAccess.php";
require_once "../../it_config.php";
require_once "lib/logger/clsLogger.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once "lib/serverChanges/clsServerChanges.php";
require_once "lib/logger/clsLogger.php";

$clsLogger = new clsLogger();

extract($_POST);
//$records='21220064<>0<>1627650445000<>11317.0<>40.0<>58950.0<>21183.13<>0.0<>538.34<>aa::aa<>Administrator<><><>TUSHAR ENTERPRISES  TEJ COURIERS<><>1<>129<>10766.87<>21183.13<>10766.87<>269.17<>269.17<>0.0<>11305.21<>11.31<>27000.0<><==>GST 5%<>0.05<>538.34<++><==>62<==>8900000409150<>SLIM SHIRT<>1495.0<>12.0<>17940.0<>257.2030089058524<>3086.436106870229<>6636.77<>684.7328244274809<>8216.79<>3086.436106870229<>77.16<>77.16<>0.0<>0.05<++>8900000089338<>FORMAL SHIRT<>1695.0<>18.0<>30510.0<>291.61078880407126<>5248.994198473283<>11286.96<>776.3358778625955<>13974.05<>5248.994198473283<>131.22<>131.22<>0.0<>0.05<++>8900000260942<>TROUSER<>1050.0<>10.0<>10500.0<>243.14396946564892<>2431.4396946564893<>3259.4<>480.91603053435114<>4809.16<>2431.4396946564893<>60.79<>60.79<>0.0<>0.05<++><==>';
if(!isset($records) || trim($records) == ""){
	$logger->logError("Missing parameter [records]:".print_r($_POST, true));
	print "1::Missing parameter [records]";
	return;
}
//print $records;
try {
    $db = new DBConn();
    $serverCh = new clsServerChanges();
    $arr = explode("|||||",$records);   
    foreach ($arr as $record) {
            $invoice_id=false; 
            $invoice_type = null;
            $store_id="";
            $tot_qty=0;
            $tot_amt=0;
            $items=array(); 
            $invoice_text = $db->safe(trim($record));
            if (trim($record) == "") { continue; }
            $invRecord = explode("<==>", $record);
            if (count($invRecord) == 0) { continue; }
         //   print_r($invRecord);
            
            $invHeader = $invRecord[0];
            $invfields = explode("<>",$invHeader);
            $stock_balance_flag =  0; // default kept as 0
            $pickingId = "";
            $invoice_no = $db->safe(trim($invfields[0]));
            if ($invfields) {
                    //$invoice_no = $db->safe(trim($invfields[0]));
                    $exists = $db->fetchObject("select * from it_invoices where invoice_no=$invoice_no");   
                    
//                    echo "select * from it_invoices where invoice_no=$invoice_no";
                    //if ($exists) break; // stop if invoice_no already exists
                    //for time being update inv amt n then break
                    $store_id=$invRecord[2];
                    if(isset($exists)){  
                        
                     $existsinsp = $db->fetchObject("select * from it_sp_invoices where invoice_no=$invoice_no");    
                    // $invoice_amt = floatval($invfields[3]);
                    $invoice_type = $db->safe($invfields[1]);
//                  $invoice_dt = $db->safe($invfields[2]);
                    $dt = $invfields[2];
                    $dt /= 1000;
                    $invoice_dt = $db->safe(date("Y-m-d H:i:s", $dt));
                    $invoice_amt = floatval($invfields[3]);		
                    $invoice_qty = doubleval($invfields[4]);
                    $total_mrp  = doubleval($invfields[5]);                    
                    $discount_1  = doubleval($invfields[6]);
                    $discount_2  = doubleval($invfields[7]);
                    $tax  = doubleval($invfields[8]);
                    $payment  = $db->safe($invfields[9]);
                    $tax_type = $db->safe($invfields[11]);
                    $tax_percent = doubleval($invfields[12]);
                    $transport_dtl = $db->safe($invfields[13]);
                    $remarks = $db->safe($invfields[14]);
                    $noofchallans = $db->safe($invfields[15]);
                    $challan_numbers = $db->safe($invfields[16]);
                    
                    $rate_subtotal = $invfields[17];
                    $discount_val = $invfields[18];
                    $total_taxable_value = $invfields[19];
                    $cgst_total = $invfields[20];
                    $sgst_total = $invfields[21];
                    $igst_total = $invfields[22];
                    $net_amount = $invfields[23];
                    $tcs75 = $invfields[24];
                    $total_add_disc = $invfields[25];
                    
                    $iClause = "";
                    if(trim($rate_subtotal)!=""){ $iClause .= " , rate_subtotal = $rate_subtotal "; }
                    if(trim($discount_val)!=""){ $iClause .= " , discount_val = $discount_val "; }
                    if(trim($total_taxable_value)!=""){ $iClause .= " , total_taxable_value = $total_taxable_value "; }
                    if(trim($cgst_total)!=""){ $iClause .= " , cgst_total = $cgst_total "; }
                    if(trim($sgst_total)!=""){ $iClause .= " , sgst_total = $sgst_total "; }
                    if(trim($igst_total)!=""){ $iClause .= " , igst_total = $igst_total "; }
                    if(trim($net_amount)!=""){ $iClause .= " , net_amount = $net_amount "; }
                    if(trim($tcs75)!=""){ $iClause .= " , tcs_0075pct = $tcs75 "; }
                    if(trim($total_add_disc)!=""){ $iClause .= " , additional_disc_val   = $total_add_disc "; }
                    if(trim($transport_dtl)!=""){ $iClause .= " , transportdtl   = $transport_dtl "; }
                    if(trim($remarks)!=""){ $iClause .= " , transportdtl_remark   = $remarks "; } 
                        
                    $sqlquery = "update it_invoices set invoice_text = $invoice_text , invoice_amt =  $invoice_amt , store_id=$store_id,"
                            . "invoice_qty = $invoice_qty , total_mrp = $total_mrp , updatetime = now() $iClause where id = $exists->id ";
                    $db->execUpdate($sqlquery);
                    
                    if(isset($existsinsp)){
                        //update header informations and delete items and reinsert items
                            $sqlSPquery = "update it_sp_invoices set invoice_text = $invoice_text , invoice_amt =  $invoice_amt ,"
                                    . " store_id=$store_id, invoice_qty = $invoice_qty , total_mrp = $total_mrp ,"
                                    . " updatetime = now() $iClause where id = $existsinsp->id ";
                            $db->execUpdate($sqlSPquery);
                    } else{
                         $qursp="insert into it_sp_invoices set invoice_text = $invoice_text,store_id=$store_id, invoice_no=$invoice_no,"
                    . " invoice_dt=$invoice_dt, invoice_amt=$invoice_amt, invoice_type=$invoice_type,invoice_qty=$invoice_qty, "
                    . "total_mrp = $total_mrp , discount_1=$discount_1,discount_2=$discount_2, tax=$tax, payment = $payment,"
                    . " tax_type = $tax_type , tax_percent = $tax_percent ,ck_invoice_id=$exists->id $iClause";
                        //print $qursp;
                         $sp_id=$db->execInsert($qursp);
                         $db->execUpdate("update it_invoices set sp_invoice_id=$sp_id where id=$exists->id");
                    }
                    //print $sqlquery;
//                    $spupdatequery = "update it_sp_invoices set invoice_text = $invoice_text , invoice_amt =  $invoice_amt , "
//                            . "invoice_qty = $invoice_qty , total_mrp = $total_mrp , updatetime = now() $iClause where id = $exists->id ";
//                     $db->execUpdate($spupdatequery);  
                     $itemdelete=$db->execQuery("delete from it_invoice_items where invoice_id=$exists->id");
                     if(isset($existsinsp)){
                     $itemSPdelete=$db->execQuery("delete from it_sp_invoice_items where invoice_id=$existsinsp->id");
                     }
                    $itemlines = explode("<++>", $invRecord[3]);

                foreach ($itemlines as $currlineitem) {
                            $currlineitem=trim($currlineitem);
                            if ($currlineitem == "") { continue; }
                            $fields = explode("<>", $currlineitem );
                            $ck_code = $db->safe($fields[0]);
        //		$sp_code = $db->safe($fields[1]);
                            $price = floatval($fields[2]);
                            $quantity = doubleval($fields[3]);
                            $tot_qty += $quantity;
                            $tot_amt += ($price * $quantity);
                            $total_price_qty = floatval($fields[4]); //price * quantity
                            $rate = floatval($fields[5]);
                            $total_rate_qty = floatval($fields[6]); // rate * quantity
                            $discount_val = floatval($fields[7]);
                            $add_discount_val = floatval($fields[8]);
                            $total_add_discount_val = floatval($fields[9]);
                            $taxable_value = floatval($fields[10]);
                            $cgst = floatval($fields[11]);
                            $sgst = floatval($fields[12]);
                            $igst = floatval($fields[13]);
                            $tax_rate = floatval($fields[14]);

                            $liClause = "";
                            if(trim($total_price_qty)!=""){ $liClause .= " , total_price_qty = $total_price_qty "; }
                            if(trim($rate)!=""){ $liClause .= " , rate = $rate "; }
                            if(trim($total_rate_qty)!=""){ $liClause .= " , total_rate_qty = $total_rate_qty "; }
                            if(trim($discount_val)!=""){ $liClause .= " , discount_val = $discount_val "; }
                            if(trim($taxable_value)!=""){ $liClause .= " , taxable_value = $taxable_value "; }
                            if(trim($add_discount_val)!=""){ $liClause .= " , additional_disc_val = $add_discount_val "; }
                            if(trim($total_add_discount_val)!=""){ $liClause .= " , total_additional_disc_val = $total_add_discount_val "; }
                            if(trim($cgst)!=""){ $liClause .= " , cgst = $cgst "; }
                            if(trim($sgst)!=""){ $liClause .= " , sgst = $sgst "; }
                            if(trim($igst)!=""){ $liClause .= " , igst = $igst "; }
                            if(trim($tax_rate)!=""){ $liClause .= " , tax_rate = $tax_rate "; }

                            $query = "insert into it_invoice_items set invoice_id=$exists->id, item_code=$ck_code,  price=$price,"
                                    . " quantity=$quantity $liClause";                        
                            //echo "<br/>".$query."<br/>";
                            $inserted = $db->execInsert($query);
                            if(isset($existsinsp)){
                            $querysp = "insert into it_sp_invoice_items set invoice_id=$existsinsp->id, barcode=$ck_code,  price=$price,"
                                    . " quantity=$quantity $liClause";                        
                            $db->execInsert($querysp);
                            }else{
                                $queryspin = "insert into it_sp_invoice_items set invoice_id=$sp_id, barcode=$ck_code, price=$price, "
                                . "quantity=$quantity $liClause";
                                //print $queryspin;
                                $db->execInsert($queryspin);
                            }
                    
                            
//                             $query = "update it_sp_invoice_items set invoice_id=$invoice_id, item_code=$ck_code,  price=$price,"
//                                    . " quantity=$quantity $liClause where invoice_id = $exists->id and item_code=$ck_code";                        
//                            //echo "<br/>".$query."<br/>";
//                            $inserted = $db->execUpdate($query);
    //                        if (!$inserted) { break; }
                            $items[$ck_code] = $quantity;
                       }

                        //cheque amt update
                        if(isset($invRecord) && trim($invRecord[4])!=""){
                            $pickNstorearr = explode("<>",$invRecord[4]);
                            $pickingId = $pickNstorearr[0];
                            $store_id = $pickNstorearr[1];


                            $pq = "select * from it_ck_pickgroup where id = $pickingId ";
                            $pobj = $db->fetchObject($pq);
                            if(isset($pobj)){
                                $invarr = explode(",", $pobj->invoice_no);
                                //print_r($invarr);
                                $camt=0;
                                foreach($invarr as $key => $value){
                                   $inv_db = $db->safe(trim($value));
                                   $q = "select invoice_amt from it_invoices where invoice_no = $inv_db ";
                                  // print "\n$q";
                                   $qobj = $db->fetchObject($q);
                                  // print "\n$inv_db -> $qobj->invoice_amt ";
                                   if(isset($qobj)){ $camt = $camt+$qobj->invoice_amt; }
                                } 
                                //$store_id=$invoice->store_id;
//                //print_r("store_id".$store_id);
//                $serverCh->save($ser_type, $server_ch,$store_id,$invoice_id);

                                $sqlquery2 = "update it_ck_pickgroup set cheque_amt = $camt  where id = $pickingId  ";
                                //print "\n".$sqlquery2;
                                $db->execUpdate($sqlquery2);
                            }
                        } 
                        
                        //check also on it_sp_invoices for that invoice no if exist then update this table also    
                   
                    
                        if(isset($existsinsp)){
                        $querysp = "select * from it_sp_invoices where id = $existsinsp->id ";               
                        $invoice = $db->fetchObject($querysp);
                        }else{
                            $querysp="select * from it_sp_invoices where invoice_no=$invoice_no";
                            $invoice = $db->fetchObject($querysp);
                        }
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
                                $json_invoice['additional_disc_val'] = $invoice->additional_disc_val;
                                $json_invoice['total_taxable_value'] = $invoice->total_taxable_value;
                                $json_invoice['cgst_total'] = $invoice->cgst_total;
                                $json_invoice['sgst_total'] = $invoice->sgst_total;
                                $json_invoice['igst_total'] = $invoice->igst_total;


                                $items = $db->fetchObjectArray("select barcode,price,quantity,total_price_qty,rate,total_rate_qty,"
                                        . "discount_val,taxable_value,cgst,sgst,igst,tax_rate,additional_disc_val from it_sp_invoice_items where "
                                        . "invoice_id = $invoice->id");
                                $json_items = array();
                                foreach ($items as $item) {
                                    $item->quantity = intval($item->quantity);
                                    $json_items[] = json_encode($item);
                                }
                                $json_invoice['items']=  json_encode($json_items);

                         $server_ch = json_encode($json_invoice);                        
                         $ser_type = changeType::invoices;   
                         $sp_store_id = $invoice->store_id;
                      // here $invoice_id is id of table it_invoices so it becomes data_id
                         $serverCh->save($ser_type, $server_ch,$sp_store_id,$invoice->id);
                       
                        break;
                    }
                    //check in it_sp_invoices is invoice exist or not 
                    $existsSP = $db->fetchObject("select * from it_sp_invoices where invoice_no=$invoice_no");
                    if($existsSP){
                        continue;
                    }
                    
                    
                    $invoice_type = $db->safe($invfields[1]);
//                    $invoice_dt = $db->safe($invfields[2]);
                    $dt = $invfields[2];
                    $dt /= 1000;
                    $invoice_dt = $db->safe(date("Y-m-d H:i:s", $dt));
                    $invoice_amt = floatval($invfields[3]);		
                    $invoice_qty = doubleval($invfields[4]);
                    $total_mrp  = doubleval($invfields[5]);                    
                    $discount_1  = doubleval($invfields[6]);
                    $discount_2  = doubleval($invfields[7]);
                    $tax  = doubleval($invfields[8]);
                    $payment  = $db->safe($invfields[9]);
                    $tax_type = $db->safe($invfields[11]);
                    $tax_percent = doubleval($invfields[12]);
                    $transport_dtl = $db->safe($invfields[13]);
                    $remarks = $db->safe($invfields[14]);
                    $noofchallans = $db->safe($invfields[15]);
                    $challan_numbers = $db->safe($invfields[16]);
                    
                    $rate_subtotal = $invfields[17];
                    $discount_val = $invfields[18];
                    $total_taxable_value = $invfields[19];
                    $cgst_total = $invfields[20];
                    $sgst_total = $invfields[21];
                    $igst_total = $invfields[22];
                    $net_amount = $invfields[23];
                    $tcs75 = $invfields[24];
                    $total_add_disc=$invfields[25];
                    
                    $iClause = "";
                    if(trim($rate_subtotal)!=""){ $iClause .= " , rate_subtotal = $rate_subtotal "; }
                    if(trim($discount_val)!=""){ $iClause .= " , discount_val = $discount_val "; }
                    if(trim($total_taxable_value)!=""){ $iClause .= " , total_taxable_value = $total_taxable_value "; }
                    if(trim($cgst_total)!=""){ $iClause .= " , cgst_total = $cgst_total "; }
                    if(trim($sgst_total)!=""){ $iClause .= " , sgst_total = $sgst_total "; }
                    if(trim($igst_total)!=""){ $iClause .= " , igst_total = $igst_total "; }
                    if(trim($net_amount)!=""){ $iClause .= " , net_amount = $net_amount "; }
                    if(trim($tcs75)!=""){ $iClause .= " , tcs_0075pct = $tcs75 "; }
                    if(trim($total_add_disc)!=""){ $iClause .= " , additional_disc_val   = $total_add_disc "; }
                    if(trim($transport_dtl)!=""){ $iClause .= " , transportdtl   = $transport_dtl "; }
                    if(trim($remarks)!=""){ $iClause .= " , transportdtl_remark   = $remarks "; } 
                    
                    //$query = "insert into it_invoices set invoice_no=$invoice_no, invoice_dt=$invoice_dt, invoice_type=$invoice_type, invoice_amt=$invoice_amt, invoice_qty=$invoice_qty";
                    $query = "insert into it_invoices set invoice_text = $invoice_text,invoice_no=$invoice_no,"
                            . " invoice_dt=$invoice_dt, invoice_type=$invoice_type, invoice_amt=$invoice_amt, "
                            . "invoice_qty=$invoice_qty , total_mrp = $total_mrp , discount_1 = $discount_1 ,"
                            . " discount_2 = $discount_2, tax = $tax , tax_type = $tax_type , tax_percent = $tax_percent ,"
                            . " payment = $payment , no_of_challans = $noofchallans , challan_nos =  $challan_numbers $iClause ";
//                    echo "<br/>$query<br/>";
                    $invoice_id = $db->execInsert($query);
                    if (!$invoice_id) { break; }
                    
            } 
             //store tax info           
            $taxinfo = $invRecord[1];
            
            if(trim($taxinfo)!=""){              
                saveInvTaxes($invoice_id,$taxinfo);
            }

            $itemlines = explode("<++>", $invRecord[3]);
           
            foreach ($itemlines as $currlineitem) {
                        $currlineitem=trim($currlineitem);
                        if ($currlineitem == "") { continue; }
                        $fields = explode("<>", $currlineitem );
                        $ck_code = $db->safe($fields[0]);
    //		$sp_code = $db->safe($fields[1]);
                        $price = floatval($fields[2]);
                        $quantity = doubleval($fields[3]);
                        $tot_qty += $quantity;
                        $tot_amt += ($price * $quantity);
                        $total_price_qty = floatval($fields[4]); //price * quantity
                        $rate = floatval($fields[5]);
                        $total_rate_qty = floatval($fields[6]); // rate * quantity
                        $discount_val = floatval($fields[7]);
                        $add_discount_val = floatval($fields[8]);
                        $total_add_discount_val = floatval($fields[9]);
                        $taxable_value = floatval($fields[10]);
                        $cgst = floatval($fields[11]);
                        $sgst = floatval($fields[12]);
                        $igst = floatval($fields[13]);
                        $tax_rate = floatval($fields[14]);
                        
                        
                        $liClause = "";
                        if(trim($total_price_qty)!=""){ $liClause .= " , total_price_qty = $total_price_qty "; }
                        if(trim($rate)!=""){ $liClause .= " , rate = $rate "; }
                        if(trim($total_rate_qty)!=""){ $liClause .= " , total_rate_qty = $total_rate_qty "; }
                        if(trim($discount_val)!=""){ $liClause .= " , discount_val = $discount_val "; }
                        if(trim($taxable_value)!=""){ $liClause .= " , taxable_value = $taxable_value "; }
                        if(trim($add_discount_val)!=""){ $liClause .= " , additional_disc_val = $add_discount_val "; }
                        if(trim($total_add_discount_val)!=""){ $liClause .= " , total_additional_disc_val = $total_add_discount_val "; }
                        if(trim($cgst)!=""){ $liClause .= " , cgst = $cgst "; }
                        if(trim($sgst)!=""){ $liClause .= " , sgst = $sgst "; }
                        if(trim($igst)!=""){ $liClause .= " , igst = $igst "; }
                        if(trim($tax_rate)!=""){ $liClause .= " , tax_rate = $tax_rate "; }
                        
                        $query = "insert into it_invoice_items set invoice_id=$invoice_id, item_code=$ck_code,  price=$price, "
                                . "quantity=$quantity $liClause ";                        
                        //echo "<br/>".$query."<br/>";
                        $inserted = $db->execInsert($query);
//                        if (!$inserted) { break; }
                        $items[$ck_code] = $quantity;
           }
           //echo "<br/>INV TYPE:".$invoice_type."<br/>";
           if($invoice_type == "'0'" || $invoice_type == "'6'" || $invoice_type == "'7'"){ // echo "here";
          // save store_id n picking details only in case of sales inv
               $store_id=$invRecord[2];
          if(isset($invRecord[4]) && trim($invRecord[4])!=""){
//           print "<br> inside if"   ;
           //print_r($invRecord);
           $pickNstorearr = explode("<>",$invRecord[4]);
           $pickingId = $pickNstorearr[0];
           $store_id = $pickNstorearr[1];
           $stock_balance_flag = isset($pickNstorearr[2]) ? $pickNstorearr[2] : 0 ;          
          }
          //echo "<br/> STORE_ID=".$store_id."<br/>";
           $query = " update it_invoices set store_id = $store_id  where id = $invoice_id ";
           //echo "<br/>$query<br/>";
           $db->execUpdate($query);
           //put same invoice info into it_sp_invoices to avoid depencies of that table
            $qursp="insert into it_sp_invoices set invoice_text = $invoice_text,store_id=$store_id, invoice_no=$invoice_no,"
                    . " invoice_dt=$invoice_dt, invoice_amt=$invoice_amt, invoice_type=$invoice_type,invoice_qty=$invoice_qty, "
                    . "total_mrp = $total_mrp , discount_1=$discount_1,discount_2=$discount_2, tax=$tax, payment = $payment,"
                    . " tax_type = $tax_type , tax_percent = $tax_percent ,ck_invoice_id=$invoice_id $iClause";
           $sp_id=$db->execInsert($qursp);
           //print_r($sp_id);
//           print ($qursp);
           if(isset($invoice_id)){
               $db->execUpdate("update it_invoices set sp_invoice_id=$sp_id where id=$invoice_id");
           }
           
           foreach ($itemlines as $currlineitem) {
                        $currlineitem=trim($currlineitem);
                        if ($currlineitem == "") { continue; }
                        $fields = explode("<>", $currlineitem );
                        $ck_code = $db->safe($fields[0]);
    //		$sp_code = $db->safe($fields[1]);
                        $price = floatval($fields[2]);
                        $quantity = doubleval($fields[3]);
                        $tot_qty += $quantity;
                        $tot_amt += ($price * $quantity);
                        $total_price_qty = floatval($fields[4]); //price * quantity
                        $rate = floatval($fields[5]);
                        $total_rate_qty = floatval($fields[6]); // rate * quantity
                        $discount_val = floatval($fields[7]);
                        $add_discount_val = floatval($fields[8]);
                        $total_add_discount_val = floatval($fields[9]);
                        $taxable_value = floatval($fields[10]);
                        $cgst = floatval($fields[11]);
                        $sgst = floatval($fields[12]);
                        $igst = floatval($fields[13]);
                        $tax_rate = floatval($fields[14]);
                        
                        
                        $liSPClause = "";
                        if(trim($total_price_qty)!=""){ $liSPClause .= " , total_price_qty = $total_price_qty "; }
                        if(trim($rate)!=""){ $liSPClause .= " , rate = $rate "; }
                        if(trim($total_rate_qty)!=""){ $liSPClause .= " , total_rate_qty = $total_rate_qty "; }
                        if(trim($discount_val)!=""){ $liSPClause .= " , discount_val = $discount_val "; }
                        if(trim($taxable_value)!=""){ $liSPClause .= " , taxable_value = $taxable_value "; }
                        if(trim($add_discount_val)!=""){ $liSPClause .= " , additional_disc_val = $add_discount_val "; }
                        if(trim($total_add_discount_val)!=""){ $liSPClause .= " , total_additional_disc_val = $total_add_discount_val "; }
                        if(trim($cgst)!=""){ $liSPClause .= " , cgst = $cgst "; }
                        if(trim($sgst)!=""){ $liSPClause .= " , sgst = $sgst "; }
                        if(trim($igst)!=""){ $liSPClause .= " , igst = $igst "; }
                        if(trim($tax_rate)!=""){ $liSPClause .= " , tax_rate = $tax_rate "; }
                        
                        $query = "insert into it_sp_invoice_items set invoice_id=$sp_id, barcode=$ck_code, price=$price, "
                                . "quantity=$quantity $liSPClause";                  
//                        echo "<br/>".$query."<br/>";
                        $insertedinsp = $db->execInsert($query);
//                        if (!$inserted) { break; }
                        $items[$ck_code] = $quantity;
           }
           
		//$db->execInsert($query);
            //echo "limelight storeid: ".$store_id;
           // stock balance code below
           if(trim($pickingId) != ""){
               $query = "select * from it_ck_pickgroup where id = $pickingId";              
               $orderObj = $db->fetchObject($query);                    
               if(isset($orderObj)){
                    $order_ids = explode(",", $orderObj->order_ids);
                    if($invoice_type == "'0'" || $invoice_type == "'6'" || $invoice_type == "'7'"){ //stock balance only for sale type inv
                       if(trim($orderObj->invoice_no)==""){ // means first inv against pickgroup id
                          /*  foreach($order_ids as $key=>$o_id){ // adding orderred qty to it_items
                             $orderNo = $db->safe(trim($o_no));
                             $query = "select * from it_ck_orders where order_no = $orderNo order by id desc limit 1";                            
                            // error_log("\nORDER QRY:\n".$query,3,"../ajax/tmp.txt");
                             $obj = $db->fetchObject($query);
                             $query1 = "select * from it_ck_orderitems where order_id = $o_id and store_id = $store_id ";
                            // error_log("\nORDER ITEMS QRY:\n".$query1,3,"../ajax/tmp.txt");
                             $orderItemsObj = $db->fetchObjectArray($query1);
                             foreach($orderItemsObj as $orderItem){
                                $itemqry = "select * from it_items where id = $orderItem->item_id";
                               // error_log("\nIN FOR ADD ITEMS QRY:\n".$itemqry,3,"../ajax/tmp.txt");
                                $itemobj = $db->fetchObject($itemqry);
                                if($itemobj->curr_qty >= 0){ // if item stock +ve then only add fr stock balance
                                    $query2 = "update it_items set curr_qty = curr_qty + $orderItem->order_qty, updatetime=now() where id = $orderItem->item_id ";
                                   // error_log("\nUPDATE QRY:\n".$query2,3,"../ajax/tmp.txt");
                                    $db->execUpdate($query2);
                                    //--> code to log it_items update track
                                    $ipaddr =  $_SERVER['REMOTE_ADDR'];
                                    $pg_name = __FILE__;              
                                    $clsLogger->logInfo($query2,false, $pg_name,$ipaddr);
                                    //--> log code ends here
                                }
                             }
                           }*/
                        
                        $updateqry = "update it_ck_pickgroup set shipped_qty = $invoice_qty , shipped_mrp = $total_mrp , cheque_amt = $invoice_amt , cheque_dtl = $payment , invoice_no = $invoice_no , transport_dtl = $transport_dtl , remark = $remarks , shipped_time = $invoice_dt where id = $pickingId ";
                        // error_log("\npickgrp update qry: $updateqry\n",3,"../ajax/tmp.txt");
                       // print "<br>$updateqry";
                        $db->execUpdate($updateqry);
                        $uporderqry = "update it_ck_orders set status = ".OrderStatus::Shipped." where id in ($orderObj->order_ids)";
                        //error_log("\nstatus update qry: $uporderqry\n",3,"../ajax/tmp.txt");
                        $db->execUpdate($uporderqry);   
                     
                      }else{
                          // means another inv against same pickgrp id
                        $invoice_db = $db->safe(",".trim($invfields[0]));
                        $payment_db = $db->safe(",".trim($invfields[9]));
                        $transport_dtl_db = $db->safe(",".trim($invfields[13]));
                        $remarks_db = $db->safe(",".trim($invfields[14]));
                        $updateqry = "update it_ck_pickgroup set shipped_qty = shipped_qty + $invoice_qty , shipped_mrp = shipped_mrp + $total_mrp , cheque_amt = cheque_amt + $invoice_amt , cheque_dtl = concat(cheque_dtl,$payment_db) , invoice_no = concat(invoice_no,$invoice_db) , transport_dtl = concat(transport_dtl,$transport_dtl_db) , remark = concat(remark,$remarks_db) , shipped_time = $invoice_dt where id = $pickingId ";
                       // print "<br>$updateqry<br>";
                        // error_log("\npickgrp update qry: $updateqry\n",3,"../ajax/tmp.txt");
                        $db->execUpdate($updateqry);
                        $uporderqry = "update it_ck_orders set status = ".OrderStatus::Shipped." where id in ($orderObj->order_ids)";
                        //error_log("\nstatus update qry: $uporderqry\n",3,"../ajax/tmp.txt");
                        $db->execUpdate($uporderqry);
                          
                      }
                      
                      if(trim($stock_balance_flag)==1){
                           foreach($order_ids as $key=>$o_id){ // adding orderred qty to it_items                             
                             $query1 = "select * from it_ck_orderitems where order_id = $o_id and store_id = $store_id ";
                            // error_log("\nORDER ITEMS QRY:\n".$query1,3,"../ajax/tmp.txt");
                             $orderItemsObj = $db->fetchObjectArray($query1);
                             foreach($orderItemsObj as $orderItem){
                                $itemqry = "select * from it_items where id = $orderItem->item_id";
                               // error_log("\nIN FOR ADD ITEMS QRY:\n".$itemqry,3,"../ajax/tmp.txt");
                                $itemobj = $db->fetchObject($itemqry);
                                if($itemobj->curr_qty >= 0){ // if item stock +ve then only add fr stock balance
                                    $query2 = "update it_items set curr_qty = curr_qty + $orderItem->order_qty, updatetime=now() where id = $orderItem->item_id ";
                                   // error_log("\nUPDATE QRY:\n".$query2,3,"../ajax/tmp.txt");
                                    $db->execUpdate($query2);
                                    //--> code to log it_items update track
                                    $ipaddr =  $_SERVER['REMOTE_ADDR'];
                                    $pg_name = __FILE__;              
                                    $clsLogger->logInfo($query2,false, $pg_name,$ipaddr);
                                    //--> log code ends here
                                }
                             }
                           }
                      }
                     }else{
                        //$updateqry = "update it_ck_pickgroup set shipped_qty = $invoice_qty ,  cheque_amt = $invoice_amt , invoice_no = $invoice_no where id = $pickingId ";
                        $updateqry = "update it_ck_pickgroup set shipped_qty = $invoice_qty , shipped_mrp = $total_mrp , cheque_amt = $invoice_amt , cheque_dtl = $payment , invoice_no = $invoice_no , transport_dtl = $transport_dtl , remark = $remarks , shipped_time = $invoice_dt where id = $pickingId ";
                        $db->execUpdate($updateqry);
                        $uporderqry = "update it_ck_orders set status = ".OrderStatus::Shipped." where id in ($orderObj->order_ids)";
                        $db->execUpdate($uporderqry); 
                     }
                     
               }
               
               //code for inserting email details n sending sms
               $pickgroup = $db->fetchObject("select * from it_ck_pickgroup where id=$pickingId");
               $store = $db->fetchObject("select * from it_codes where id=$store_id");               
               if ($store && $store->phone) {
                    require_once "lib/sms/clsSMSServe.php";
                    $cls = new clsSMSServe();
                    $msg = "Dear $store->owner, Your payment is deposited against PO:$pickgroup->order_nos , Ship:$transport_dtl. Thank You." ; // Invoice No:$invoice_no, Chq Amt:$cheque_amt, Chq No:$cheque_dtl, Ship:$transport_dtl. Thank You.";
                    $cls->saveSMSreply($store->phone, $msg);
                    if ($remarks) $cls->saveSMSreply($store->phone, "Message from CK: ".trim($remarks,"'"));
                    if ($store->phone2) {
                            $cls->saveSMSreply($store->phone2, $msg);
                            if ($remarks) $cls->saveSMSreply($store->phone2, "Message from CK: ".trim($remarks,"'"));
                    }
               }
               if ($store && ($store->email || $store->email2)) {
                $pickgroup = $db->fetchObject("select p.*, c.store_name from it_ck_pickgroup p, it_codes c where p.id=$pickingId and p.storeid = c.id");               
                $message = $db->safe(getMessage($pickgroup));
                $subject = $db->safe("Your order [$pickgroup->order_nos] has been shipped");
                if ($store->email) {
                        $db->execInsert("insert into it_emails set storeid=$pickgroup->storeid, emailaddress='$store->email', subject=$subject, body=$message");
                }
                if ($store->email2) {
                        $db->execInsert("insert into it_emails set storeid=$pickgroup->storeid, emailaddress='$store->email2', subject=$subject, body=$message");
                }
              }
           }
    } 
        if($invoice_type == "'0'" || $invoice_type == "'6'" || $invoice_type == "'7'"){
            if(trim($stock_balance_flag)==1){  //do stock reflect only when flag signal is provided
                //stock_balance_flag from pos states when to do stock balancing                   
                   
                //fetch all the invoices against the pick group 
                $query = "select * from it_ck_pickgroup where id = $pickingId";    
                $invpObj = $db->fetchObject($query);                                      
                $inv_nos = explode(",",$invpObj->invoice_no);
                //print "<br>INV NOS: <br>";
                foreach($inv_nos as $key=>$inv_no){ // iterate for all invs against that pickgroup
                       // fetch inv items
                    $inv_no_db = $db->safe(trim($inv_no));
                    $iquery = "select ii.* from it_invoice_items ii , it_invoices i where ii.invoice_id = i.id and i.invoice_no = $inv_no_db ";
                    $invitem_objs = $db->fetchObjectArray($iquery);

                     foreach($invitem_objs as $invitemobj){  // sub inv_items qty from it_items
                        if(isset($invitemobj) && !empty($invitemobj) && $invitemobj != null){
                            $item_code_db = $db->safe(trim($invitemobj->item_code));  
                            $iquery = "select * from it_items where barcode = $item_code_db ";
                            $iobj = $db->fetchObject($iquery);
                           // if($invoice_type == "'0'" || $invoice_type == "'6'"){  // means sale inv                   
                                if($iobj->curr_qty > 0 ){ 
                                //   $q1 ="update it_items set curr_qty = curr_qty - $value , updatetime = now() where barcode = $key";
                                     $q1 ="update it_items set curr_qty = curr_qty - $invitemobj->quantity , updatetime = now() where barcode = $item_code_db";
                                    $db->execUpdate($q1);  
                                   //--> code to log it_items insert/update track
                                     $ipaddr =  $_SERVER['REMOTE_ADDR'];
                                     $pg_name = __FILE__;              
                                     $clsLogger->logInfo($q1,false, $pg_name,$ipaddr);
                                     //--> log code ends here
                                }               
                           // }
                            /* No Stock Reflect for credit inv -> 14-Dec-2015
                            if($invoice_type == "'5'"){ // means credit note                   
                                if($iobj->curr_qty > 0 ){ 
                                   $q1 ="update it_items set curr_qty = curr_qty + $value , updatetime = now() where barcode = $key";
                                   $db->execUpdate($q1); 
                                }else{
                                    $q1 ="update it_items set curr_qty =  $value , updatetime = now() where barcode = $key";
                                    $db->execUpdate($q1); 
                                }
                            }*/
                     }
                    }
            }
        }
    }
           unset($items);
          // if ($invoice_id) {
            $db->execUpdate("update it_invoices set invoice_amt=$invoice_amt, invoice_qty=$invoice_qty where id=$invoice_id");             
//            if($invoice_type == "'0'" || $invoice_type == "'6'"){ // only sales inv shld get inserted in it_server_changes                
//                $query = "SELECT ii . * , sp.invoice_no AS sp_invoice_no FROM it_invoices ii LEFT OUTER JOIN it_sp_invoices sp ON ii.sp_invoice_id = sp.id WHERE ii.id = $invoice_id AND ii.invoice_type in (0,6)";                
//                $invoice = $db->fetchObject($query);
//                $json_obj = array();   
//                        $json_invoice = array();
//                        $json_invoice['invoice_id'] = $invoice->id;
//                        $json_invoice['store_id'] = $invoice->store_id;
//                        $json_invoice['invoice_no'] = $invoice->invoice_no;
//                        $json_invoice['invoice_dt'] = $invoice->invoice_dt;
//                        $json_invoice['invoice_amt'] = $invoice->invoice_amt;
//                        $json_invoice['invoice_qty'] = $invoice->invoice_qty;
//                        $json_invoice['total_mrp'] = $invoice->total_mrp;
//                        $json_invoice['discount_1'] = $invoice->discount_1;
//                        $json_invoice['discount_2'] = $invoice->discount_2;
//                        $json_invoice['tax'] = $invoice->tax;
//                        $json_invoice['payment'] = $invoice->payment;
//                        $json_invoice['tax_type'] = $invoice->tax_type;
//                        $json_invoice['tax_percent'] = $invoice->tax_percent;
//                        $json_invoice['no_of_challans'] = $invoice->no_of_challans;
//                        $json_invoice['challan_numbers'] = $invoice->challan_nos;
//                        $json_invoice['sp_invoice_no'] = $invoice->sp_invoice_no;
//                        
//                        $items = $db->fetchObjectArray("select item_code,price,quantity from it_invoice_items where invoice_id = $invoice->id");
//                        $json_items = array();
//                        foreach ($items as $item) {                               
////                            $json_items[] = json_encode($item);
//                             $json_items[] = $item;
//                        }
////                        $json_invoice['items']=  json_encode($json_items);
//                        $json_invoice['items']= $json_items;
//                        
//                       
//                 $server_ch = json_encode($json_invoice);                         
//                 $ser_type = changeType::invoices;  
//                 $wareh_store_id = DEF_WAREHOUSE_ID;
//              // here $invoice_id is id of table it_invoices so it becomes data_id
//                $serverCh->save($ser_type, $server_ch,$wareh_store_id,$invoice_id);
////                $store_id=$invoice->store_id;
////                //print_r("store_id".$store_id);
////                $serverCh->save($ser_type, $server_ch,$store_id,$invoice_id);
//            }
            if($invoice_type == "'0'" || $invoice_type == "'6'" || $invoice_type == "'7'"){ // only sales inv shld get inserted in it_server_changes
                //$query = "select * from it_invoices where id = $invoice_id ";                
                $query = "select sp.*  from  it_sp_invoices sp where sp.id = $sp_id and sp.invoice_type in ('0','6','7')";
//                echo $query."<br>";
                // add code to include sp_invoice_no field
                
                $invoice = $db->fetchObject($query);
                //print_r($invoice);
                $json_obj = array();   
                        $json_invoice = array();
                        //$json_invoice['invoice_id'] = $invoice->id;
                       // $json_invoice['store_id'] = $invoice->store_id;
                        $json_invoice['invoice_id'] = $invoice->id;
                        $json_invoice['invoice_no'] = $invoice->invoice_no;
                        $json_invoice['invoice_dt'] = $invoice->invoice_dt;
                        $json_invoice['invoice_amt'] = $invoice->invoice_amt;
                        $json_invoice['invoice_qty'] = intval($invoice->invoice_qty);
                        $json_invoice['total_mrp'] = $invoice->total_mrp;
                        $json_invoice['discount_1'] = $invoice->discount_1;
                        $json_invoice['discount_2'] = $invoice->discount_2;
                        $json_invoice['additional_disc_val'] = $invoice->additional_disc_val;
                        $json_invoice['tax'] = $invoice->tax;
                        $json_invoice['payment'] = $invoice->payment;
                                //gst changes
                        $json_invoice['rate_subtotal'] = $invoice->rate_subtotal;
                        $json_invoice['discount_val'] = $invoice->discount_val;
                        $json_invoice['total_taxable_value'] = $invoice->total_taxable_value;
                        $json_invoice['cgst_total'] = $invoice->cgst_total;
                        $json_invoice['sgst_total'] = $invoice->sgst_total;
                        $json_invoice['igst_total'] = $invoice->igst_total;

//                        echo "select barcode,price,quantity,total_price_qty,rate,total_rate_qty,"
//                                . " discount_val,taxable_value,cgst,sgst,igst,tax_rate,additional_disc_val from it_sp_invoice_items "
//                                . "where invoice_id = $invoice->id";
                        
                        $items = $db->fetchObjectArray("select barcode,price,quantity,total_price_qty,rate,total_rate_qty,"
                                . " discount_val,taxable_value,cgst,sgst,igst,tax_rate,additional_disc_val from it_sp_invoice_items "
                                . "where invoice_id = $invoice->id");
                        $json_items = array();
                        foreach ($items as $item) {                               
                            $json_items[] = json_encode($item);                             
                        }
                        $json_invoice['items']=  json_encode($json_items);
                                                                       
                 $server_ch = json_encode($json_invoice);                         
                 $ser_type = changeType::invoices;  
                 $sp_store_id = $invoice->store_id;
              // here $invoice_id is id of table it_invoices so it becomes data_id
              //print_r($server_ch);
                $serverCh->save($ser_type, $server_ch,$sp_store_id,$invoice->id);
//                $query = "select * from it_invoices where id = $invoice_id ";               
//                        $invoice = $db->fetchObject($query);
//                        $json_obj = array();  
//                                $json_invoice = array();
//                                $json_invoice['invoice_id'] = $invoice->sp_invoice_id;
//                                $json_invoice['invoice_no'] = $invoice->invoice_no;
//                                $json_invoice['invoice_dt'] = $invoice->invoice_dt;
//                                $json_invoice['invoice_amt'] = $invoice->invoice_amt;
//                                $json_invoice['invoice_qty'] = intval($invoice->invoice_qty);
//                                $json_invoice['total_mrp'] = $invoice->total_mrp;
//                                $json_invoice['discount_1'] = $invoice->discount_1;
//                                $json_invoice['discount_2'] = $invoice->discount_2;
//                                $json_invoice['tax'] = $invoice->tax;
//                                $json_invoice['payment'] = $invoice->payment;
//                                //gst changes
//                                $json_invoice['rate_subtotal'] = $invoice->rate_subtotal;
//                                $json_invoice['discount_val'] = $invoice->discount_val;
//                                $json_invoice['total_taxable_value'] = $invoice->total_taxable_value;
//                                $json_invoice['cgst_total'] = $invoice->cgst_total;
//                                $json_invoice['sgst_total'] = $invoice->sgst_total;
//                                $json_invoice['igst_total'] = $invoice->igst_total;
//
//
//                                $items = $db->fetchObjectArray("select item_code,price,quantity,total_price_qty,rate,total_rate_qty,"
//                                        . "discount_val,taxable_value,cgst,sgst,igst,tax_rate from it_invoice_items where "
//                                        . "invoice_id = $invoice->id");
//                                $json_items = array();
//                                foreach ($items as $item) {
//                                    $item->quantity = intval($item->quantity);
//                                    $json_items[] = json_encode($item);
//                                }
//                                $json_invoice['items']=  json_encode($json_items);
//
//                         $server_ch = json_encode($json_invoice);                        
//                         $ser_type = changeType::invoices;   
//                         $sp_store_id = $invoice->store_id;
//                      // here $invoice_id is id of table it_invoices so it becomes data_id
//                         $serverCh->save($ser_type, $server_ch,$sp_store_id,$invoice_id);
            }
          // }
    }
    
//    $db->closeConnection();
    print "0::Success";
} catch (Exception $ex) {
	print "1::Error-".$ex->getMessage();
}
//}

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



function saveInvTaxes($invoice_id,$taxinfo){
    $db = new DBConn();
    $taxlines = explode("<++>",$taxinfo);
    foreach($taxlines as $currtaxlineitem){
        $currtaxlineitem=trim($currtaxlineitem);
        if ($currtaxlineitem == "") { continue; }
        $fields = explode("<>", $currtaxlineitem );
        $tax_type = $fields[0];
        $tax_per = $fields[1];
        $tax_amount = $fields[2];
        if(trim($tax_type)!="" && trim($tax_per)!="" && trim($tax_amount) != ""){
            $tax_type_db = $db->safe(trim($tax_type));
            $query = "insert into it_invoice_taxes set invoice_id = $invoice_id , tax_type = $tax_type_db,tax_percent = $tax_per, tax_amount = $tax_amount , createtime = now() ";
           // print "\nTAXES INS QRY: $query\n";
            $db->execInsert($query);
        }
    }
    $db->closeConnection();
}
?>