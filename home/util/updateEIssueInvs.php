<?php
require_once("../../it_config.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/logger/clsLogger.php";

$db = new DBConn();

if((!isset($argv[1]) ) || ( !isset($argv[2]) )){ //&& trim($argv[1])=="" && trim($argv[2])==""
     print "\n Start_date and/or end date is missing\n";return;
}else{
    //check for correct date format
    // chk start date
    if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$argv[1])){
     print "\nProvide start date in correct format i.e. 'yyyy-MM-dd'\n";return; 
    }else{ 
       //$start_dt = $db->safe(trim($argv[1]));
        $start_dt = trim($argv[1]);
    }
    $commit = 0;
    if(isset($argv[3]) && trim($argv[3])!="" && trim($argv[3])!="0"){ $commit=1;}
    $fcnt=0;
    $scnt=0;
    $tcnt=0;
    
    print "COMMIT: ".$commit;
    // chk end date
    if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$argv[2])){
     print "\nProvide end date in correct format i.e. 'yyyy-MM-dd'\n";return; 
    }else{
        //$end_dt = $db->safe(trim($argv[2]));
        $end_dt = trim($argv[2]);
    }
    
    //script begings
    //step 1:Fetch all the orders between provided date range 
    $query = "select i.* from it_invoices i  where i.invoice_type in (0,6) and i.invoice_dt >= '$start_dt 00:00:00' and i.invoice_dt <= '$end_dt 23:59:59' ";
    print "\n$query\n";
    $objs = $db->fetchObjectArray($query);
    
    foreach($objs as $robj){
       $invRecord = explode("<==>",$robj->invoice_text);
       if (count($invRecord) == 0) { continue; }
        $invHeader = $invRecord[0];
        $invfields = explode("<>",$invHeader); 
        $pickNstorearr = explode("<>",$invRecord[4]);
        $pickingId = $pickNstorearr[0];
//           echo "<br/>ck pid:".$pickingId;
           //$store_id = $pickNstorearr[1]; this store id used in limelight saveInv sync not here
           
        
        //store tax info           
          $taxinfo = $invRecord[1];

          if(trim($taxinfo)!=""){                              
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
                        $query = "insert into it_invoice_taxes set invoice_id = $robj->id , tax_type = $tax_type_db,tax_percent = $tax_per, tax_amount = $tax_amount , createtime = now() ";
                       // print "\nTAXES INS QRY: $query\n";
                        if($commit==1){
                         $db->execInsert($query);
                         $tcnt++;
                        }else{
                            //for testing
                            $tcnt++;
                        }
                    }
                }                
          }
           
        $query = "select * from it_ck_pickgroup where id = $pickingId";               
        //print "<br>$query";
        $orderObj = $db->fetchObject($query);               
        if(isset($orderObj)){
                print_r($orderObj);
             $order_nos = explode(",", $orderObj->order_nos);             
             if(trim($orderObj->invoice_no)==""){ // means first inv against pickgroup id
                 $updateqry = "update it_ck_pickgroup set shipped_qty = $invoice_qty , shipped_mrp = $total_mrp , cheque_amt = $invoice_amt , cheque_dtl = $payment , invoice_no = $invoice_no , transport_dtl = $transport_dtl , remark = $remarks , shipped_time = $invoice_dt where id = $pickingId ";                
                 print "FIRST UPDATE: <br>$updateqry";
                 
                 $uporderqry = "update it_ck_orders set status = ".OrderStatus::Shipped." where id in ($orderObj->order_ids)";
                 //error_log("\nstatus update qry: $uporderqry\n",3,"../ajax/tmp.txt");
                 if($commit==1){
                    $db->execUpdate($updateqry);
                    $db->execUpdate($uporderqry);
                    $fcnt++;
                 }else{ //for testing
                     $fcnt++;
                 }
                 
             }else{ // means another inv against same pickgrp id
                 //if(strcmp($orderObj->invoice_no, $robj->invoice_no)!= 0){                     
                 if(strpos($orderObj->invoice_no, $robj->invoice_no) === false){
                    $invoice_db = $db->safe(",".trim($invfields[0]));
                    $invoice_type = $db->safe($invfields[1]);
//                    $invoice_dt = $db->safe($invfields[2]);
                    $dt = $invfields[2];
                    $dt /= 1000;
                    $invoice_dt = $db->safe(date("Y-m-d H:i:s", $dt));
                    $invoice_amt = floatval($invfields[3]);		
                    $invoice_qty = floatval($invfields[4]);
                    $total_mrp  = doubleval($invfields[5]);
                    $payment_db = $db->safe(",".trim($invfields[9]));
                    $transport_dtl_db = $db->safe(",".trim($invfields[13]));
                    $remarks_db = $db->safe(",".trim($invfields[14]));
                    $updateqry = "update it_ck_pickgroup set shipped_qty = shipped_qty + $invoice_qty , shipped_mrp = shipped_mrp + $total_mrp , cheque_amt = cheque_amt + $invoice_amt , cheque_dtl = concat(cheque_dtl,$payment_db) , invoice_no = concat(invoice_no,$invoice_db) , transport_dtl = concat(transport_dtl,$transport_dtl_db) , remark = concat(remark,$remarks_db) , shipped_time = $invoice_dt where id = $pickingId ";
                    print "SECOND UPDATE: <br>$updateqry<br>";
                    // error_log("\npickgrp update qry: $updateqry\n",3,"../ajax/tmp.txt");
                    
                    $uporderqry = "update it_ck_orders set status = ".OrderStatus::Shipped." where id in ($orderObj->order_ids)";
                    //error_log("\nstatus update qry: $uporderqry\n",3,"../ajax/tmp.txt");
                    if($commit==1){
                        $db->execUpdate($updateqry);
                        $db->execUpdate($uporderqry);
                        $scnt++;
                    }else{ //for testing
                        $scnt++;
                    }
                 }
             }
          
        }
        
       
        
    }
    
     $db->closeConnection();
     if($commit==1){
         print "\nChanges Committed !!\n";
         print "\nTot fresh $fcnt orders updated\n";
         print "\nTot second $scnt orders updated\n";
         print "\nTot $tcnt invoice(s) tax data inserted ";
     }else{
         print "\nTot fresh $fcnt orders will get updated\n";
         print "\nTot second $scnt orders will get updated\n";
         print "\nTot $tcnt invoice(s) tax data will get inserted ";
     }
}