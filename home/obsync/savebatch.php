<?php
include "checkAccess.php";
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";

extract($_POST);
//$record='[{"ticketId":"CN86-192000187","ticketType":1,"discountPct":0.0,"discountValue":0.0,"creditNoteValue":0.0,"total_taxable_value":-3026.79,"total_tax_value":-363.22,"total_cgst_value":-181.61,"total_sgst_value":-181.61,"total_igst_value":0.0,"subTotal":-3390.0,"netTotal":-3390.0,"user":"POS Manager","ticketdate":"1562497409000","itemcount":-2.0,"noPeople":"","serverName":"","custFirstName":"","custLastName":"","custPhone":"","custEmail":"","salesman":"","ticketlines":[{"prodcode":"8900000985135","hsncode":"6205","prodreference":"98513","price":1595.0,"qty":-1.0,"proddiscountval":" ","proddiscountlabel":" ","discountval":0.0,"discountpct":0.0,"cgst_amount":0.0,"sgst_amount":0.0,"igst_amount":0.0,"lineTotal":-1595.0,"salesman_person":"0 ","prod_cat_name":"SLIM SHIRT"},{"prodcode":"8900000881871","hsncode":"6205","prodreference":"88187","price":1795.0,"qty":-1.0,"proddiscountval":" ","proddiscountlabel":" ","discountval":0.0,"discountpct":0.0,"cgst_amount":0.0,"sgst_amount":0.0,"igst_amount":0.0,"lineTotal":-1795.0,"salesman_person":"0 ","prod_cat_name":"SLIM SHIRT"}],"paymentinfo":[{"m_dTicket":-3390.0,"m_sName":"creditnoteout","m_returnMessage":"CN86-192000187"}]}]';

//$record='[{"ticketId":"LK-192000788","ticketType":0,"discountPct":0.0,"discountValue":0.0,"creditNoteValue":0.0,"total_taxable_value":1156.25,"total_tax_value":138.76,"total_cgst_value":69.38,"total_sgst_value":69.38,"total_igst_value":0.0,"subTotal":1295.0,"netTotal":1295.0,"user":"HO","ticketdate":"1561012020000","itemcount":1.0,"noPeople":"","serverName":"","custFirstName":"","custLastName":"","custPhone":"","custEmail":"","salesman":"3","ticketlines":[{"prodcode":"8900000598854","hsncode":"6204","prodreference":"59885","price":1295.0,"qty":1.0,"proddiscountval":" ","proddiscountlabel":" ","discountval":0.0,"discountpct":0.0,"cgst_amount":0.0,"sgst_amount":0.0,"igst_amount":0.0,"lineTotal":1295.0,"salesman_person":"3","prod_cat_name":"TROUSER"}],"paymentinfo":[{"m_dTicket":1295.0,"m_sName":"cash","m_transactionID":"no ID","m_returnMessage":"OK"}]}]';
if (!$record) {
	print "1::The order information is missing. Please make sure the receipt information is displayed before re-submitting.";
	return;
}

//echo "Records=".$record;
try {
$db = new DBConn();
//$gCodeId= 125;
//$gCodeId= 62;

$datatype = 1 ; // old pos type


if(strpos($record,'|||||') === false){
   $datatype = 2 ; // json format
}


if(trim($datatype)==1){

$arr = explode("|||||", $record);
foreach ($arr as $orderinfo) {
	$orderinfo = trim($orderinfo);
	if ($orderinfo == "") { continue; }
	$records = explode("<==>", $orderinfo);
	if (count($records) == 0) { continue; }    
	$custinfo = trim($records[1]);
        $paymentinfo="";
        $salesman_code = "";
        $sClause = "";
//        print "<br>COUNT: ";
//        print count($records);
//        print "<br>";
        if(isset($records[3]) && trim($records[3])!= ""){ //means payment info provided
         $paymentinfo = $records[3];
        }
                             
        
       if(isset($records[4]) && trim($records[4])!= ""){ //means saleman code provided
         $salesman_code = trim($records[4]);
         $sClause .= " , salesman_code = $salesman_code ";
        }
        
//        print "<br> SALES MAN CODE: $salesman_code ";
//        print "<br> SCLAUSE: $sClause ";
        
	$user_id = false;
	if ($custinfo != "") {
		list($firstname, $lastname, $phoneno) = explode("<>", $custinfo);
		$phoneno = $db->safe($phoneno);
		$user = $db->fetchObject("select * from it_users where phoneno = $phoneno");
		$name = $db->safe("$firstname $lastname");
		if ($user) {
			$user_id = $user->id;
			$db->execUpdate("update it_users set name=$name where id=$user_id");
		}
		else { $user_id = $db->execInsert("insert into it_users set name=$name, phoneno=$phoneno"); }
	}

	$orderinfo = $db->safe($orderinfo);
	$query = "insert into it_orders set store_id=$gCodeId, orderinfo=$orderinfo $sClause ";
//	print "<br>$query ";
        if ($user_id) { $query .= ", user_id=$user_id"; }
	list($billtype,$bill_no, $timeInMillis, $amount, $quantity, $discount_val, $discount_pct, $voucher_amt, $tax) = explode("<>", $records[0]);
	$billtype = trim($billtype);
        //echo "<br/>BILL TYPE: ".$billtype."<br/>";
        if ($billtype && $billtype!="") { $query .= ", tickettype=$billtype"; } 
        $orderobj = $db->fetchObject("select id from it_orders where bill_no='$bill_no' and store_id=$gCodeId");
        if ($billtype == '3') { // means cancelled bill type
            $billobj = $db->fetchObject("select id from it_orders where bill_no='$bill_no' and tickettype = $billtype and store_id=$gCodeId");
//            echo "<br/>select id from it_orders where bill_no='$bill_no' and tickettype = $billtype and store_id=$gCodeId<br/>";
//            echo "<br/>In bill type 3: <br/>";
           if( $billobj!=null){ 
                // step 1 : update the order text
                $orderinfo_append = $db->safe(trim("<=##New_TEXT##=>".$orderinfo));
                //update all bill header details as well
                $baddClause = "";
                if (trim($amount) != "") { $baddClause .= ", amount=$amount"; }
                if (trim($quantity) != "") { $baddClause .= ", quantity=$quantity"; }
                if ($discount_val && trim($discount_val) != "") { $baddClause .= ", discount_val=$discount_val"; }
                if ($discount_pct && trim($discount_pct) != "") { $baddClause .= ", discount_pct=$discount_pct"; }
                if ($voucher_amt && trim($voucher_amt) != "") { $baddClause .= ", voucher_amt=$voucher_amt"; }
                if (trim($tax) != "") { $baddClause .= ", tax=$tax"; }
                if ($timeInMillis && trim($timeInMillis) != "") {
                        $timeInSeconds=intval(doubleVal($timeInMillis)/1000); // convert millis to seconds
                        $bill_datetime = date("Y-m-d H:i:s",intval($timeInSeconds));
                        $baddClause .= ", bill_datetime = '$bill_datetime'";
                }
                
                $qry = "update it_orders set orderinfo = concat(orderinfo,$orderinfo_append) , updatetime = now() $baddClause ,is_sent =0 where id = $billobj->id  ";
//                print "<br>TEXT APPEND QRY: $qry <br>";
                $db->execUpdate($qry);
                reSaveCancelBill($billobj->id,$billtype,$gCodeId,$records);
               continue; // same bill came again so continue            
           }
           if(!$orderobj && $billtype == '3'){
              // means a cancelled bill has come for first time then only insert n continue
               if ($bill_no && trim($bill_no) != "") { $query .= ", bill_no='$bill_no'"; }
                if (trim($amount) != "") { $query .= ", amount=$amount"; }
                if (trim($quantity) != "") { $query .= ", quantity=$quantity"; }
                if ($discount_val && trim($discount_val) != "") { $query .= ", discount_val=$discount_val"; }
                if ($discount_pct && trim($discount_pct) != "") { $query .= ", discount_pct=$discount_pct"; }
                if ($voucher_amt && trim($voucher_amt) != "") { $query .= ", voucher_amt=$voucher_amt"; }
                if (trim($tax) != "") { $query .= ", tax=$tax"; }
                if ($timeInMillis && trim($timeInMillis) != "") {
                        $timeInSeconds=intval(doubleVal($timeInMillis)/1000); // convert millis to seconds
                        $bill_datetime = date("Y-m-d H:i:s",intval($timeInSeconds));
                        $query .= ", bill_datetime = '$bill_datetime'";
                }
                //echo "\n$query\n";
                $order_id = $db->execInsert($query);
                $itemlines = explode("<++>", $records[2]);
                $items=array();
                foreach ($itemlines as $currlineitem) {
                        $currlineitem=trim($currlineitem);
                        if ($currlineitem == "") { continue; }
                        list($item_id, $barcode, $price, $quantity, $discount_val, $discount_pct) = explode("<>", $currlineitem);
    //                    print "\n".$item_id." ".$barcode." ".$price." ".$quantity." ".$discount_val." ".$discount_pct;
                        $query = "insert into it_order_items set store_id=$gCodeId, order_id=$order_id";
                        if ($item_id && trim($item_id) != "") { $query .= ", item_id=$item_id"; }
                        if ($barcode && trim($barcode) != "") { $query .= ", barcode='$barcode'"; }
                        if ($price && trim($price) != "") { $query .= ", price=$price"; }
                        if ($quantity && trim($quantity) != "") { $query .= ", quantity=$quantity"; }
                        if ($discount_val && trim($discount_val) != "") { $query .= ", discount_val=$discount_val"; }
                        if ($discount_pct && trim($discount_pct) != "") { $query .= ", discount_pct=$discount_pct"; }
        // disable for now		if ($tax && trim($tax) != "") { $query .= ", tax=$tax"; }
    //                    print "\n".$query."\n";
                        $db->execInsert($query);
                }  
                
                //save payment info if provided
                if(trim($paymentinfo)!=""){
                $paymentlines = explode("<++>", $records[3]);
//                print "<br>PAYMENT INFO 1: ";
//                print_r($paymentlines);
                
                 foreach($paymentlines as $currpaymentline){
                     $currpaymentline = trim($currpaymentline);
                     if($currpaymentline == ""){ continue; }
                     list($payment_name,$payment_amt,$payment_msg) = explode("<>", $currpaymentline);
                     if(trim($payment_name) == "" || trim($payment_amt)==""){ continue; }
                     $payment_name_db = $db->safe(trim($payment_name));
                     $pquery = " insert into it_order_payments set order_id = $order_id , payment_name = $payment_name_db , amount = $payment_amt , createtime = now() ";
//                     print "<br>PAYMENT QRY: $pquery";
                     if( trim($payment_msg) != ""){
                         $payment_msg_db = $db->safe(trim($payment_msg));
                         $pquery .= " , msg = $payment_msg_db  ";
                     }
                     $db->execInsert($pquery);
                 }
                 //update disc pct only if gfvoucher is used
                 updateDiscPct($order_id);
                }
                
                continue;
           }   
           //means a sale bill was cancelled n send again
           $orderinfo_cappend = $db->safe(trim("<=##New_TEXT##=>".$orderinfo));
                $db->execUpdate("update it_orders set orderinfo = concat(orderinfo,$orderinfo_cappend) ,tickettype=3, updatetime=now() ,is_sent =0 where id=$orderobj->id");
//                echo "<br/>update it_orders set tickettype=3, updatetime=now() where id=$orderobj->id<br/>";
                reSaveCancelBill($orderobj->id,$billtype,$gCodeId,$records);
                $itemlines = explode("<++>", $records[2]);
                 foreach ($itemlines as $currlineitem) {
                    $currlineitem=trim($currlineitem);
                    if ($currlineitem == "") { continue; }
                    list($item_id, $barcode, $price, $quantity, $discount_val, $discount_pct) = explode("<>", $currlineitem);                
                    $barcode = $db->safe(trim($barcode));
                    $qry = "select * from it_current_stock where barcode = $barcode and store_id = $gCodeId ";
//                    echo "<br/>$qry<br/>";
                    $exists = $db->fetchObject($qry);
                    if($exists){  // hav to do rev so '+' sign                     
                        $db->execUpdate("update it_current_stock set quantity = quantity + $quantity , updatetime = now() where id = $exists->id ");
                    }else{  $iqry = "select * from it_items where barcode = $barcode ";
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
                        $insQry = "insert into it_current_stock set barcode = $barcode , store_id = $gCodeId ,ctg_id = $ctg_id, design_id = $design_id , style_id = $style_id , size_id = $size_id , $qClause createtime = now() ";
                        
//                        $insQry = "insert into it_current_stock set barcode = $barcode , store_id = $gCodeId , $qClause createtime = now() ";
                        $inserted = $db->execInsert($insQry);
                    }
                 } 
           
        } else {           
            if ($billtype == '0' ) { // sales bill type
               $billobj = $db->fetchObject("select id from it_orders where bill_no='$bill_no' and tickettype = $billtype and store_id=$gCodeId");  
              if($billobj!=null){ //same bill came again
                // step 1 : update the order text
                    $orderinfo_append = $db->safe(trim("<=##New_TEXT##=>".$orderinfo));
                    //update bill header as well
                    $baddClause = "";
                    if (trim($amount) != "") { $baddClause .= ", amount=$amount"; }
                    if (trim($quantity) != "") { $baddClause .= ", quantity=$quantity"; }
                    if ($discount_val && trim($discount_val) != "") { $baddClause .= ", discount_val=$discount_val"; }
                    if ($discount_pct && trim($discount_pct) != "") { $baddClause .= ", discount_pct=$discount_pct"; }
                    if ($voucher_amt && trim($voucher_amt) != "") { $baddClause .= ", voucher_amt=$voucher_amt"; }
                    if (trim($tax) != "") { $baddClause .= ", tax=$tax"; }
                    if ($timeInMillis && trim($timeInMillis) != "") {
                            $timeInSeconds=intval(doubleVal($timeInMillis)/1000); // convert millis to seconds
                            $bill_datetime = date("Y-m-d H:i:s",intval($timeInSeconds));
                            $baddClause .= ", bill_datetime = '$bill_datetime'";
                    }
                    
                    $qry = "update it_orders set orderinfo = concat(orderinfo,$orderinfo_append) , updatetime = now() $baddClause ,is_sent =0 where id = $billobj->id  ";
//                    print "<br>TEXT APPEND QRY: $qry <br>";
                    $db->execUpdate($qry);
                    //step 2: Do Stock update revert n del old orderitems & old orderpayments
                    stockUpdateRevert($billobj->id,$billtype,$gCodeId);   
                    $order_id = $billobj->id;
                //continue;
              }else{  
                if ($bill_no && trim($bill_no) != "") { $query .= ", bill_no='$bill_no'"; }
                if (trim($amount) != "") { $query .= ", amount=$amount"; }
                if (trim($quantity) != "") { $query .= ", quantity=$quantity"; }
                if ($discount_val && trim($discount_val) != "") { $query .= ", discount_val=$discount_val"; }
                if ($discount_pct && trim($discount_pct) != "") { $query .= ", discount_pct=$discount_pct"; }
                if ($voucher_amt && trim($voucher_amt) != "") { $query .= ", voucher_amt=$voucher_amt"; }
                if (trim($tax) != "") { $query .= ", tax=$tax"; }
                if ($timeInMillis && trim($timeInMillis) != "") {
                        $timeInSeconds=intval(doubleVal($timeInMillis)/1000); // convert millis to seconds
                        $bill_datetime = date("Y-m-d H:i:s",intval($timeInSeconds));
                        $query .= ", bill_datetime = '$bill_datetime'";
                }
                $order_id = $db->execInsert($query);
            }
            $itemlines = explode("<++>", $records[2]);
            $items=array();
            foreach ($itemlines as $currlineitem) {
                    $currlineitem=trim($currlineitem);
                    if ($currlineitem == "") { continue; }
                    list($item_id, $barcode, $price, $quantity, $discount_val, $discount_pct) = explode("<>", $currlineitem);
//                    print "\n".$item_id." ".$barcode." ".$price." ".$quantity." ".$discount_val." ".$discount_pct;
                    $query = "insert into it_order_items set store_id=$gCodeId, order_id=$order_id";
                    if ($item_id && trim($item_id) != "") { $query .= ", item_id=$item_id"; }
                    if ($barcode && trim($barcode) != "") { $query .= ", barcode='$barcode'"; }
                    if ($price && trim($price) != "") { $query .= ", price=$price"; }
                    if ($quantity && trim($quantity) != "") { $query .= ", quantity=$quantity"; }
                    if ($discount_val && trim($discount_val) != "") { $query .= ", discount_val=$discount_val"; }
                    if ($discount_pct && trim($discount_pct) != "") { $query .= ", discount_pct=$discount_pct"; }
    // disable for now		if ($tax && trim($tax) != "") { $query .= ", tax=$tax"; }
//                    print "\n".$query."\n";
                    $db->execInsert($query);
                    $barcode = $db->safe(trim($barcode));
                    $qry = "select * from it_current_stock where barcode = $barcode and store_id = $gCodeId ";
                    $exists = $db->fetchObject($qry);
                    if($exists){                        
                        $db->execUpdate("update it_current_stock set quantity = quantity - $quantity , updatetime = now() where id = $exists->id ");
                    }else{                                                   
                        $qClause = "quantity = -$quantity ,";   
                        $iqry = "select * from it_items where barcode = $barcode ";
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
                        $insQry = "insert into it_current_stock set barcode = $barcode , store_id = $gCodeId ,ctg_id = $ctg_id, design_id = $design_id , style_id = $style_id , size_id = $size_id , $qClause createtime = now() ";
                        //$insQry = "insert into it_current_stock set barcode = $barcode , store_id = $gCodeId , $qClause createtime = now() ";
                        $inserted = $db->execInsert($insQry);
                    }
        
            }
            
             //save payment info 
            if(trim($paymentinfo)!=""){
                $paymentlines = explode("<++>", $records[3]);

                 foreach($paymentlines as $currpaymentline){
                     $currpaymentline = trim($currpaymentline);
                     if($currpaymentline == ""){ continue; }
                     list($payment_name,$payment_amt,$payment_msg) = explode("<>", $currpaymentline);
                     if(trim($payment_name) == "" || trim($payment_amt)==""){ continue; }
                     $payment_name_db = $db->safe(trim($payment_name));                 
                     $pquery = " insert into it_order_payments set order_id = $order_id , payment_name = $payment_name_db , amount = $payment_amt , createtime = now() ";
//                     print "<br>PAYMENT QRY: $pquery ";
                     if( trim($payment_msg) != ""){
                         $payment_msg_db = $db->safe(trim($payment_msg));
                         $pquery .= " , msg = $payment_msg_db  ";
                     }
                     $db->execInsert($pquery);
                 }
                 //update disc_pct if gift voucher is used
                 updateDiscPct($order_id);
            }
        }
        }
    }
}else{
    //json format
    
    $arr = json_decode($record);
    foreach($arr as $key => $tobj){ 
         $user_id = false;
         $sClause = "";
         // customer info section missing {}
         $firstname = $tobj->custFirstName;
         if(isset($tobj->custLastName)){
         $lastname = $tobj->custLastName;
         }else{
         $lastname = "";    
         }
         $phoneno = $tobj->custPhone;
         $email = $tobj->custEmail;
         if(trim($phoneno)!=""){
            $phoneno = $db->safe($phoneno);
            $user = $db->fetchObject("select * from it_users where phoneno = $phoneno");
            $name = $db->safe("$firstname $lastname");
            $eClause = "";
            if(trim($email)!=""){ $email_db = $db->safe(trim($email)); $eClause .= ", email = $email_db "; }
            if (isset($user)) {
                    $user_id = $user->id;
                    $db->execUpdate("update it_users set name=$name  $eClause where id=$user_id");
            }                
            else { 
//                    print "insert into it_users set name=$name, phoneno=$phoneno";
                $user_id = $db->execInsert("insert into it_users set name=$name, phoneno=$phoneno $eClause "); }
        }
         
        
         //salesman sec missing
         if(isset($tobj->salesman) && trim($tobj->salesman)!=""){
            $sClause .= " , salesman_code = $tobj->salesman "; 
         }
         
        $orderinfo = json_encode($tobj);
        $orderinfo_db = $db->safe($orderinfo);
	$query = "insert into it_orders set store_id=$gCodeId, orderinfo=$orderinfo_db $sClause ";
	if ($user_id) { $query .= ", user_id=$user_id"; }
        
        $billtype = $tobj->ticketType;
        $bill_no = trim($tobj->ticketId);
        $timeInMillis = $tobj->ticketdate;
        $amount = $tobj->netTotal;
        $quantity = $tobj->itemcount;
        $discount_val = $tobj->discountValue;
        $discount_pct = $tobj->discountPct;
        $voucher_amt = $tobj->creditNoteValue;
        $tax = $tobj->total_tax_value;
        $total_taxable_value = $tobj->total_taxable_value;
        $total_tax_value = $tobj->total_tax_value;
        $total_cgst_value = $tobj->total_cgst_value;
        $total_sgst_value = $tobj->total_sgst_value;
        $total_igst_value = $tobj->total_igst_value;
        $sub_total = $tobj->subTotal;
        $net_total = $tobj->netTotal;
        
        $billtype = trim($billtype);
        //echo "<br/>BILL TYPE: ".$billtype."<br/>";
          //code for salesman incentive report
        $return_vouchernum=":";
        if(isset($tobj->creditNoteUsed)){
        $return_vouchernum=$tobj->creditNoteUsed;}
        $itemlines1 = $tobj->ticketlines;
        $return_percent=($voucher_amt/$sub_total)*100;
       // $incentive_amount_per=100*(1-$return_percent);
        
        $return_voucher= explode(":", $return_vouchernum);
       insertSalesmanReport($gCodeId,$bill_no,$billtype,$return_voucher[1],$return_percent,$timeInMillis,$itemlines1);
        
        
        
        if ($billtype && $billtype!="") { $query .= ", tickettype=$billtype"; } 
        $orderobj = $db->fetchObject("select id from it_orders where bill_no='$bill_no' and store_id=$gCodeId");
                if ($billtype == '3') { // means cancelled bill type
            $billobj = $db->fetchObject("select id from it_orders where bill_no='$bill_no' and tickettype = $billtype and store_id=$gCodeId");
//            echo "<br/>select id from it_orders where bill_no='$bill_no' and tickettype = $billtype and store_id=$gCodeId<br/>";
//            echo "<br/>In bill type 3: <br/>";
           if( $billobj!=null){ 
               // step 1 : update the order text
                $orderinfo_append = $db->safe(trim("<=##New_TEXT##=>".$orderinfo));
                //update all bill header details as well
                $baddClause = "";
                if (trim($amount) != "") { $baddClause .= ", amount=$amount"; }
                if (trim($quantity) != "") { $baddClause .= ", quantity=$quantity"; }
                if ($discount_val && trim($discount_val) != "") { $baddClause .= ", discount_val=$discount_val"; }
                if ($discount_pct && trim($discount_pct) != "") { $baddClause .= ", discount_pct=$discount_pct"; }
                if ($voucher_amt && trim($voucher_amt) != "") { $baddClause .= ", voucher_amt=$voucher_amt"; }
                if (trim($tax) != "") { $baddClause .= ", tax=$tax"; }
                if ($timeInMillis && trim($timeInMillis) != "") {
                        $timeInSeconds=intval(doubleVal($timeInMillis)/1000); // convert millis to seconds
                        $bill_datetime = date("Y-m-d H:i:s",intval($timeInSeconds));
                        $baddClause .= ", bill_datetime = '$bill_datetime'";
                }
                if (trim($total_taxable_value) != "") { $baddClause .= ", total_taxable_value=$total_taxable_value"; }
                if (trim($total_tax_value) != "") { $baddClause .= ", total_tax_value=$total_tax_value"; }
                if (trim($total_cgst_value) != "") { $baddClause .= ", total_cgst_value=$total_cgst_value"; }
                if (trim($total_sgst_value) != "") { $baddClause .= ", total_sgst_value=$total_sgst_value"; }
                if (trim($total_igst_value) != "") { $baddClause .= ", total_igst_value=$total_igst_value"; }
                if (trim($sub_total) != "") { $baddClause .= ", sub_total=$sub_total"; }
                if (trim($net_total) != "") { $baddClause .= ", net_total=$net_total"; }
                if (trim($firstname) != "") { $baddClause .= ", cust_name='$firstname'"; }
                if (trim($phoneno) != "") { $baddClause .= ", cust_phone=$phoneno"; }
                
                $qry = "update it_orders set orderinfo = concat(orderinfo,$orderinfo_append) , updatetime = now() $baddClause ,is_sent =0 where id = $billobj->id  ";
//                print "<br>TEXT APPEND QRY: $qry <br>";
                $db->execUpdate($qry);
                reSaveCancelBill2($billobj->id,$billtype,$gCodeId,$tobj);
                continue;
               //continue; // same bill came again so continue            
           }
           if(!$orderobj && $billtype == '3'){
//               print "<br>In Cancel Bill 1st time<br>";
              // means a cancelled bill has come for first time then only insert n continue
                    if ($bill_no && trim($bill_no) != "") { $query .= ", bill_no='$bill_no'"; }
                    if (trim($amount) != "") { $query .= ", amount=$amount"; }
                    if (trim($quantity) != "") { $query .= ", quantity=$quantity"; }
                    if ($discount_val && trim($discount_val) != "") { $query .= ", discount_val=$discount_val"; }
                    if ($discount_pct && trim($discount_pct) != "") { $query .= ", discount_pct=$discount_pct"; }
                    if ($voucher_amt && trim($voucher_amt) != "") { $query .= ", voucher_amt=$voucher_amt"; }
                    if (trim($tax) != "") { $query .= ", tax=$tax"; }
                    if ($timeInMillis && trim($timeInMillis) != "") {
                            $timeInSeconds=intval(doubleVal($timeInMillis)/1000); // convert millis to seconds
                            $bill_datetime = date("Y-m-d H:i:s",intval($timeInSeconds));
                            $query .= ", bill_datetime = '$bill_datetime'";
                    }
                    
                    if (trim($total_taxable_value) != "") { $query .= ", total_taxable_value=$total_taxable_value"; }
                    if (trim($total_tax_value) != "") { $query .= ", total_tax_value=$total_tax_value"; }
                    if (trim($total_cgst_value) != "") { $query .= ", total_cgst_value=$total_cgst_value"; }
                    if (trim($total_sgst_value) != "") { $query .= ", total_sgst_value=$total_sgst_value"; }
                    if (trim($total_igst_value) != "") { $query .= ", total_igst_value=$total_igst_value"; }
                    if (trim($sub_total) != "") { $query .= ", sub_total=$sub_total"; }
                    if (trim($net_total) != "") { $query .= ", net_total=$net_total"; }
                    if (trim($firstname) != "") { $query .= ", cust_name='$firstname'"; }
                    if (trim($phoneno) != "") { $query .= ", cust_phone=$phoneno"; }
                    //echo "\n$query\n";
                      $order_id = $db->execInsert($query);
                $itemlines = $tobj->ticketlines;
                $items=array();
                foreach ($itemlines as $currlineitem) {
                        //$currlineitem=trim($currlineitem);
                        //if ($currlineitem == "") { continue; }
                        if (isset($currlineitem) && !empty($currlineitem) && $currlineitem != null) { 
                        
                       // list($item_id, $barcode, $price, $quantity, $discount_val, $discount_pct) = explode("<>", $currlineitem);
                        
                        $item_id = $currlineitem->prodreference;
                        $barcode = $currlineitem->prodcode;
                        $price = $currlineitem->price;
                        $quantity = $currlineitem->qty;
                        $discount_val = $currlineitem->discountval;
                        $discount_pct = $currlineitem->discountpct;
                        $cgst_amount = $currlineitem->cgst_amount;
                        $sgst_amount = $currlineitem->sgst_amount;
                        $igst_amount = $currlineitem->igst_amount;
                        $lineTotal = $currlineitem->lineTotal;
                        $hsncode = trim($currlineitem->hsncode);
                        
    //                    print "\n".$item_id." ".$barcode." ".$price." ".$quantity." ".$discount_val." ".$discount_pct;
                        $query = "insert into it_order_items set store_id=$gCodeId, order_id=$order_id";
                        if ($item_id && trim($item_id) != "") { $query .= ", item_id=$item_id"; }
                        if ($barcode && trim($barcode) != "") { $query .= ", barcode='$barcode'"; }
                        if ($price && trim($price) != "") { $query .= ", price=$price"; }
                        if ($quantity && trim($quantity) != "") { $query .= ", quantity=$quantity"; }
                        if ($discount_val && trim($discount_val) != "") { $query .= ", discount_val=$discount_val"; }
                        if ($discount_pct && trim($discount_pct) != "") { $query .= ", discount_pct=$discount_pct"; }
        // disable for now		if ($tax && trim($tax) != "") { $query .= ", tax=$tax"; }
                       if ($cgst_amount && trim($cgst_amount) != "") { $query .= ", cgst_amount=$cgst_amount"; }
                       if ($sgst_amount && trim($sgst_amount) != "") { $query .= ", sgst_amount=$sgst_amount"; }
                       if ($igst_amount && trim($igst_amount) != "") { $query .= ", igst_amount=$igst_amount"; }
                       if ($lineTotal && trim($lineTotal) != "") { $query .= ", lineTotal=$lineTotal"; }
    //                    print "\n".$query."\n";
                       if ($hsncode && trim($hsncode) != "") { $query .= ", hsncode='$hsncode'"; }
                       
                       $db->execInsert($query);
                        }  
                }  
                
                //save payment info if provided
               if(isset($tobj->paymentinfo)){
                $paymentlines = $tobj->paymentinfo;
//                print "<br>PAYMENT INFO 1: ";
//                print_r($paymentlines);
                
                 foreach($paymentlines as $currpaymentline){
                     //$currpaymentline = trim($currpaymentline);
                     //if($currpaymentline == ""){ continue; }
                     if(isset($currpaymentline) && !empty($currpaymentline) && $currpaymentline != null){ 
                        
                        //list($payment_name,$payment_amt,$payment_msg) = explode("<>", $currpaymentline);
                        $payment_name = $currpaymentline->m_sName;
                        $payment_amt = $currpaymentline->m_dTicket;
//                        $payment_msg = $currpaymentline->m_returnMessage;
                        if($payment_name == 'magcard' || $payment_name == 'upi'){
                           $payment_msg = $currpaymentline->m_transactionID;   
                        }else{
                           $payment_msg = $currpaymentline->m_returnMessage;
                        } 
                         
                        if(trim($payment_name) == "" || trim($payment_amt)==""){ continue; }
                        $payment_name_db = $db->safe(trim($payment_name));
                        $pquery = " insert into it_order_payments set order_id = $order_id , payment_name = $payment_name_db , amount = $payment_amt , createtime = now() ";
   //                     print "<br>PAYMENT QRY: $pquery";
                        if( trim($payment_msg) != ""){
                            $payment_msg_db = $db->safe(trim($payment_msg));
                            $pquery .= " , msg = $payment_msg_db  ";
                        }
                        $db->execInsert($pquery);
                     }
                 }
                 //update disc pct only if gfvoucher is used
                 updateDiscPct($order_id);
                }
                continue;
           }   
//           print "<br>In Sale Bill Cancelled and send again <br>";
           //means a sale bill was cancelled n send again
                $orderinfo_cappend = $db->safe(trim("<=##New_TEXT##=>".$orderinfo));
                $db->execUpdate("update it_orders set orderinfo = concat(orderinfo,$orderinfo_cappend) ,tickettype=3, updatetime=now() ,is_sent =0 where id=$orderobj->id");
//                echo "<br/>update it_orders set tickettype=3, updatetime=now() where id=$orderobj->id<br/>";
                reSaveCancelBill2($orderobj->id,$billtype,$gCodeId,$tobj);
                $itemlines = $tobj->ticketlines;
                 foreach ($itemlines as $currlineitem) {
                    //$currlineitem=trim($currlineitem);
                    //if ($currlineitem == "") { continue; }
                    if(isset($currlineitem) && !empty($currlineitem) && $currlineitem != null){
                    //list($item_id, $barcode, $price, $quantity, $discount_val, $discount_pct) = explode("<>", $currlineitem);                
                    
                        $item_id = $currlineitem->prodreference;
                        $barcode = $currlineitem->prodcode;
                        $price = $currlineitem->price;
                        $quantity = $currlineitem->qty;
                        $discount_val = $currlineitem->discountval;
                        $discount_pct = $currlineitem->discountpct;
                        $cgst_amount = $currlineitem->cgst_amount;
                        $sgst_amount = $currlineitem->sgst_amount;
                        $igst_amount = $currlineitem->igst_amount;
                        $lineTotal = $currlineitem->lineTotal;    
                        
                    $barcode = $db->safe(trim($barcode));
                    $qry = "select * from it_current_stock where barcode = $barcode and store_id = $gCodeId ";
//                    echo "<br/>$qry<br/>";
                    $exists = $db->fetchObject($qry);
                    if($exists){  // hav to do rev so '+' sign                     
                        $db->execUpdate("update it_current_stock set quantity = quantity + $quantity , updatetime = now() where id = $exists->id ");
                    }else{ 
                        $qClause = "quantity = -$quantity ,";
                        $iqry = "select * from it_items where barcode = $barcode ";
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
                        $insQry = "insert into it_current_stock set barcode = $barcode , store_id = $gCodeId ,ctg_id = $ctg_id, design_id = $design_id , style_id = $style_id , size_id = $size_id , $qClause createtime = now() ";
                        $inserted = $db->execInsert($insQry);
                    }
                    }
                 } 
           
        }else {           
             if ($billtype == '0' || $billtype =='6' ) { // sales bill type or corporate type bill
               $billobj = $db->fetchObject("select id from it_orders where bill_no='$bill_no' and tickettype = $billtype and store_id=$gCodeId");  
              if($billobj!=null){ //same bill came again                    
                    // step 1 : update the order text
                    $orderinfo_append = $db->safe(trim("<=##New_TEXT##=>".$orderinfo));
                    //update bill header as well
                    $baddClause = "";
                    if (trim($amount) != "") { $baddClause .= ", amount=$amount"; }
                    if (trim($quantity) != "") { $baddClause .= ", quantity=$quantity"; }
                    if ($discount_val && trim($discount_val) != "") { $baddClause .= ", discount_val=$discount_val"; }
                    if ($discount_pct && trim($discount_pct) != "") { $baddClause .= ", discount_pct=$discount_pct"; }
                    if ($voucher_amt && trim($voucher_amt) != "") { $baddClause .= ", voucher_amt=$voucher_amt"; }
                    if (trim($tax) != "") { $baddClause .= ", tax=$tax"; }
                    if ($timeInMillis && trim($timeInMillis) != "") {
                            $timeInSeconds=intval(doubleVal($timeInMillis)/1000); // convert millis to seconds
                            $bill_datetime = date("Y-m-d H:i:s",intval($timeInSeconds));
                            $baddClause .= ", bill_datetime = '$bill_datetime'";
                    }
                    
                    if (trim($total_taxable_value) != "") { $baddClause .= ", total_taxable_value=$total_taxable_value"; }
                    if (trim($total_tax_value) != "") { $baddClause .= ", total_tax_value=$total_tax_value"; }
                    if (trim($total_cgst_value) != "") { $baddClause .= ", total_cgst_value=$total_cgst_value"; }
                    if (trim($total_sgst_value) != "") { $baddClause .= ", total_sgst_value=$total_sgst_value"; }
                    if (trim($total_igst_value) != "") { $baddClause .= ", total_igst_value=$total_igst_value"; }
                    if (trim($sub_total) != "") { $baddClause .= ", sub_total=$sub_total"; }
                    if (trim($net_total) != "") { $baddClause .= ", net_total=$net_total"; }
                    if (trim($firstname) != "") { $baddClause .= ", cust_name='$firstname'"; }
                    if (trim($phoneno) != "") { $baddClause .= ", cust_phone=$phoneno"; }
                    
                    $qry = "update it_orders set orderinfo = concat(orderinfo,$orderinfo_append) , updatetime = now() $baddClause ,is_sent =0 where id = $billobj->id  ";
//                    print "<br>TEXT APPEND QRY: $qry <br>";
                    $db->execUpdate($qry);
                    //step 2: Do Stock update revert n del old orderitems & old orderpayments
                    stockUpdateRevert($billobj->id,$billtype,$gCodeId);   
                    $order_id = $billobj->id;
              }else{  
                if ($bill_no && trim($bill_no) != "") { $query .= ", bill_no='$bill_no'"; }
                if (trim($amount) != "") { $query .= ", amount=$amount"; }
                if (trim($quantity) != "") { $query .= ", quantity=$quantity"; }
                if ($discount_val && trim($discount_val) != "") { $query .= ", discount_val=$discount_val"; }
                if ($discount_pct && trim($discount_pct) != "") { $query .= ", discount_pct=$discount_pct"; }
                if ($voucher_amt && trim($voucher_amt) != "") { $query .= ", voucher_amt=$voucher_amt"; }
                if (trim($tax) != "") { $query .= ", tax=$tax"; }
                if ($timeInMillis && trim($timeInMillis) != "") {
                        $timeInSeconds=intval(doubleVal($timeInMillis)/1000); // convert millis to seconds
                        $bill_datetime = date("Y-m-d H:i:s",intval($timeInSeconds));
                        $query .= ", bill_datetime = '$bill_datetime'";
                }
                
                if (trim($total_taxable_value) != "") { $query .= ", total_taxable_value=$total_taxable_value"; }
                if (trim($total_tax_value) != "") { $query .= ", total_tax_value=$total_tax_value"; }
                if (trim($total_cgst_value) != "") { $query .= ", total_cgst_value=$total_cgst_value"; }
                if (trim($total_sgst_value) != "") { $query .= ", total_sgst_value=$total_sgst_value"; }
                if (trim($total_igst_value) != "") { $query .= ", total_igst_value=$total_igst_value"; }
                if (trim($sub_total) != "") { $query .= ", sub_total=$sub_total"; }
                if (trim($net_total) != "") { $query .= ", net_total=$net_total"; }
                if (trim($firstname) != "") { $query .= ", cust_name='$firstname'"; }
                if (trim($phoneno) != "") { $query .= ", cust_phone=$phoneno"; }
                
                $order_id = $db->execInsert($query);
              }
            $itemlines = $tobj->ticketlines;
            $items=array();
            foreach ($itemlines as $currlineitem) {
                    //$currlineitem=trim($currlineitem);
                    //if ($currlineitem == "") { continue; }
                if(isset($currlineitem) && !empty($currlineitem) && $currlineitem != null){
                   // list($item_id, $barcode, $price, $quantity, $discount_val, $discount_pct) = explode("<>", $currlineitem);
//                    print "\n".$item_id." ".$barcode." ".$price." ".$quantity." ".$discount_val." ".$discount_pct;
                    $item_id = $currlineitem->prodreference;
                    $barcode = $currlineitem->prodcode;
                    $price = $currlineitem->price;
                    $quantity = $currlineitem->qty;
                    $discount_val = $currlineitem->discountval;
                    $discount_pct = $currlineitem->discountpct;
                    $cgst_amount = $currlineitem->cgst_amount;
                    $sgst_amount = $currlineitem->sgst_amount;
                    $igst_amount = $currlineitem->igst_amount;
                    $lineTotal = $currlineitem->lineTotal;
                    $hsncode = trim($currlineitem->hsncode);
                    
                    $query = "insert into it_order_items set store_id=$gCodeId, order_id=$order_id";
                    if ($item_id && trim($item_id) != "") { $query .= ", item_id=$item_id"; }
                    if ($barcode && trim($barcode) != "") { $query .= ", barcode='$barcode'"; }
                    if ($price && trim($price) != "") { $query .= ", price=$price"; }
                    if ($quantity && trim($quantity) != "") { $query .= ", quantity=$quantity"; }
                    if ($discount_val && trim($discount_val) != "") { $query .= ", discount_val=$discount_val"; }
                    if ($discount_pct && trim($discount_pct) != "") { $query .= ", discount_pct=$discount_pct"; }
    // disable for now		if ($tax && trim($tax) != "") { $query .= ", tax=$tax"; }
//                    print "\n".$query."\n";
                    if ($cgst_amount && trim($cgst_amount) != "") { $query .= ", cgst_amount=$cgst_amount"; }
                    if ($sgst_amount && trim($sgst_amount) != "") { $query .= ", sgst_amount=$sgst_amount"; }
                    if ($igst_amount && trim($igst_amount) != "") { $query .= ", igst_amount=$igst_amount"; }
                    if ($lineTotal && trim($lineTotal) != "") { $query .= ", lineTotal=$lineTotal"; }
                    if ($hsncode && trim($hsncode) != "") { $query .= ", hsncode='$hsncode'"; }
                    
                    $db->execInsert($query);
                    $barcode = $db->safe($barcode);
                    $qry = "select * from it_current_stock where barcode = $barcode and store_id = $gCodeId ";
//                    print "<br> ST DPL: ";
                    $exists = $db->fetchObject($qry);
                    if($exists){                        
//                        print "<br> update it_current_stock set quantity = quantity - $quantity , updatetime = now() where id = $exists->id ";
                        $db->execUpdate("update it_current_stock set quantity = quantity - $quantity , updatetime = now() where id = $exists->id ");
                    }else{                                                   
                        $qClause = "quantity = -$quantity ,"; 
                        $iqry = "select * from it_items where barcode = $barcode ";
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
                        $insQry = "insert into it_current_stock set barcode = $barcode , store_id = $gCodeId ,ctg_id = $ctg_id, design_id = $design_id , style_id = $style_id , size_id = $size_id , $qClause createtime = now() ";
                        //$insQry = "insert into it_current_stock set barcode = $barcode , store_id = $gCodeId , $qClause createtime = now() ";
                        $inserted = $db->execInsert($insQry);
                    }
                }    
            }

            //save payment info 
            if(isset($tobj->paymentinfo)){
                $paymentlines = $tobj->paymentinfo;

                 foreach($paymentlines as $currpaymentline){
//                     $currpaymentline = trim($currpaymentline);
//                     if($currpaymentline == ""){ continue; }
                   if(isset($currpaymentline) && !empty($currpaymentline) && $currpaymentline != null){ 
                     //list($payment_name,$payment_amt,$payment_msg) = explode("<>", $currpaymentline);
                    $payment_name = $currpaymentline->m_sName;
                    $payment_amt = $currpaymentline->m_dTicket;
                    //$payment_msg = $currpaymentline->m_returnMessage;
                    if($payment_name == 'magcard' || $payment_name == 'upi'){
                           $payment_msg = $currpaymentline->m_transactionID;   
                        }else{
                           $payment_msg = $currpaymentline->m_returnMessage;
                        } 
                        
                     if(trim($payment_name) == "" || trim($payment_amt)==""){ continue; }
                     $payment_name_db = $db->safe(trim($payment_name));                 
                     $pquery = " insert into it_order_payments set order_id = $order_id , payment_name = $payment_name_db , amount = $payment_amt , createtime = now() ";
                     if( trim($payment_msg) != ""){
                         $payment_msg_db = $db->safe(trim($payment_msg));
                         $pquery .= " , msg = $payment_msg_db  ";
                     }
                     $db->execInsert($pquery);
                    }
                 }
                 //update disc_pct if gift voucher is used
                 updateDiscPct($order_id);
            }
        }else if ($billtype == '1' ) { // credit bill type
               $billobj = $db->fetchObject("select id from it_orders where bill_no='$bill_no' and tickettype = $billtype and store_id=$gCodeId");  
              if($billobj!=null){ //same bill came again                    
                    // step 1 : update the order text
                    $orderinfo_append = $db->safe(trim("<=##New_TEXT##=>".$orderinfo));
                    //update bill header as well
                    $baddClause = "";
                    if (trim($amount) != "") { $baddClause .= ", amount=$amount"; }
                    if (trim($quantity) != "") { $baddClause .= ", quantity=$quantity"; }
                    if ($discount_val && trim($discount_val) != "") { $baddClause .= ", discount_val=$discount_val"; }
                    if ($discount_pct && trim($discount_pct) != "") { $baddClause .= ", discount_pct=$discount_pct"; }
                    if ($voucher_amt && trim($voucher_amt) != "") { $baddClause .= ", voucher_amt=$voucher_amt"; }
                    if (trim($tax) != "") { $baddClause .= ", tax=$tax"; }
                    if ($timeInMillis && trim($timeInMillis) != "") {
                            $timeInSeconds=intval(doubleVal($timeInMillis)/1000); // convert millis to seconds
                            $bill_datetime = date("Y-m-d H:i:s",intval($timeInSeconds));
                            $baddClause .= ", bill_datetime = '$bill_datetime'";
                    }
                    
                    if (trim($total_taxable_value) != "") { $baddClause .= ", total_taxable_value=$total_taxable_value"; }
                    if (trim($total_tax_value) != "") { $baddClause .= ", total_tax_value=$total_tax_value"; }
                    if (trim($total_cgst_value) != "") { $baddClause .= ", total_cgst_value=$total_cgst_value"; }
                    if (trim($total_sgst_value) != "") { $baddClause .= ", total_sgst_value=$total_sgst_value"; }
                    if (trim($total_igst_value) != "") { $baddClause .= ", total_igst_value=$total_igst_value"; }
                    if (trim($sub_total) != "") { $baddClause .= ", sub_total=$sub_total"; }
                    if (trim($net_total) != "") { $baddClause .= ", net_total=$net_total"; }
                    if (trim($firstname) != "") { $query .= ", cust_name='$firstname'"; }
                    if (trim($phoneno) != "") { $query .= ", cust_phone=$phoneno"; }
                    
                    $qry = "update it_orders set orderinfo = concat(orderinfo,$orderinfo_append) , updatetime = now() $baddClause ,is_sent =0 where id = $billobj->id  ";
//                    print "<br>TEXT APPEND QRY: $qry <br>";
                    $db->execUpdate($qry);
                    //step 2: Do Stock update revert n del old orderitems & old orderpayments
                    stockUpdateRevert($billobj->id,$billtype,$gCodeId);   
                    $order_id = $billobj->id;
              }else{  
                if ($bill_no && trim($bill_no) != "") { $query .= ", bill_no='$bill_no'"; }
                if (trim($amount) != "") { $query .= ", amount=$amount"; }
                if (trim($quantity) != "") { $query .= ", quantity=$quantity"; }
                if ($discount_val && trim($discount_val) != "") { $query .= ", discount_val=$discount_val"; }
                if ($discount_pct && trim($discount_pct) != "") { $query .= ", discount_pct=$discount_pct"; }
                if ($voucher_amt && trim($voucher_amt) != "") { $query .= ", voucher_amt=$voucher_amt"; }
                if (trim($tax) != "") { $query .= ", tax=$tax"; }
                if ($timeInMillis && trim($timeInMillis) != "") {
                        $timeInSeconds=intval(doubleVal($timeInMillis)/1000); // convert millis to seconds
                        $bill_datetime = date("Y-m-d H:i:s",intval($timeInSeconds));
                        $query .= ", bill_datetime = '$bill_datetime'";
                }
                
                if (trim($total_taxable_value) != "") { $query .= ", total_taxable_value=$total_taxable_value"; }
                if (trim($total_tax_value) != "") { $query .= ", total_tax_value=$total_tax_value"; }
                if (trim($total_cgst_value) != "") { $query .= ", total_cgst_value=$total_cgst_value"; }
                if (trim($total_sgst_value) != "") { $query .= ", total_sgst_value=$total_sgst_value"; }
                if (trim($total_igst_value) != "") { $query .= ", total_igst_value=$total_igst_value"; }
                if (trim($sub_total) != "") { $query .= ", sub_total=$sub_total"; }
                if (trim($net_total) != "") { $query .= ", net_total=$net_total"; }
                if (trim($firstname) != "") { $query .= ", cust_name='$firstname'"; }
                if (trim($phoneno) != "") { $query .= ", cust_phone=$phoneno"; }
                
                $order_id = $db->execInsert($query);
              }
            $itemlines = $tobj->ticketlines;
            $items=array();
            foreach ($itemlines as $currlineitem) {
                    //$currlineitem=trim($currlineitem);
                    //if ($currlineitem == "") { continue; }
                if(isset($currlineitem) && !empty($currlineitem) && $currlineitem != null){
                   // list($item_id, $barcode, $price, $quantity, $discount_val, $discount_pct) = explode("<>", $currlineitem);
//                    print "\n".$item_id." ".$barcode." ".$price." ".$quantity." ".$discount_val." ".$discount_pct;
                    $item_id = $currlineitem->prodreference;
                    $barcode = $currlineitem->prodcode;
                    $price = $currlineitem->price;
                    $quantity = $currlineitem->qty;
                    $discount_val = $currlineitem->discountval;
                    $discount_pct = $currlineitem->discountpct;
                    $cgst_amount = $currlineitem->cgst_amount;
                    $sgst_amount = $currlineitem->sgst_amount;
                    $igst_amount = $currlineitem->igst_amount;
                    $lineTotal = $currlineitem->lineTotal;
                    $hsncode = trim($currlineitem->hsncode);
                    
                    $query = "insert into it_order_items set store_id=$gCodeId, order_id=$order_id";
                    if ($item_id && trim($item_id) != "") { $query .= ", item_id=$item_id"; }
                    if ($barcode && trim($barcode) != "") { $query .= ", barcode='$barcode'"; }
                    if ($price && trim($price) != "") { $query .= ", price=$price"; }
                    if ($quantity && trim($quantity) != "") { $query .= ", quantity=$quantity"; }
                    if ($discount_val && trim($discount_val) != "") { $query .= ", discount_val=$discount_val"; }
                    if ($discount_pct && trim($discount_pct) != "") { $query .= ", discount_pct=$discount_pct"; }
    // disable for now		if ($tax && trim($tax) != "") { $query .= ", tax=$tax"; }
//                    print "\n".$query."\n";
                    if ($cgst_amount && trim($cgst_amount) != "") { $query .= ", cgst_amount=$cgst_amount"; }
                    if ($sgst_amount && trim($sgst_amount) != "") { $query .= ", sgst_amount=$sgst_amount"; }
                    if ($igst_amount && trim($igst_amount) != "") { $query .= ", igst_amount=$igst_amount"; }
                    if ($lineTotal && trim($lineTotal) != "") { $query .= ", lineTotal=$lineTotal"; }
                    if ($hsncode && trim($hsncode) != "") { $query .= ", hsncode='$hsncode'"; }
                    
                    $db->execInsert($query);
                    $barcode = $db->safe($barcode);
                    $qry = "select * from it_current_stock where barcode = $barcode and store_id = $gCodeId ";
//                    print "<br> ST DPL: ";
                    $exists = $db->fetchObject($qry);
                    if($exists){                        
                        //as qty comes -ve so in order to add in stock do minus
                        $db->execUpdate("update it_current_stock set quantity = quantity - $quantity , updatetime = now() where id = $exists->id ");
                    }else{                                                   
                        $qClause = "quantity = -$quantity ,"; 
                        $iqry = "select * from it_items where barcode = $barcode ";
                        $iobj = $db->fetchObject($iqry);
                        if(isset($iobj)){
                            $ctg_id = $iobj->ctg_id;
                            $design_id = $iobj->design_id;
                            $style_id = $iobj->style_id;
                            $size_id = $iobj->size_id;
                        }else{
                            $ctg_id = 0;
//                 foreach($paymentlines as $currpaymentline){
                            $design_id = 0;
                            $style_id = 0;
                            $size_id = 0;
//                        }
                        $insQry = "insert into it_current_stock set barcode = $barcode , store_id = $gCodeId ,ctg_id = $ctg_id, design_id = $design_id , style_id = $style_id , size_id = $size_id , $qClause createtime = now() ";
                        //$insQry = "insert into it_current_stock set barcode = $barcode , store_id = $gCodeId , $qClause createtime = now() ";
                        $inserted = $db->execInsert($insQry);
                    }
                }    
            }

            //save payment info 
            if(isset($tobj->paymentinfo)){
                $paymentlines = $tobj->paymentinfo;

//                     $currpaymentline = trim($currpaymentline);
//                     if($currpaymentline == ""){ continue; }
                   if(isset($currpaymentline) && !empty($currpaymentline) && $currpaymentline != null){ 
                     //list($payment_name,$payment_amt,$payment_msg) = explode("<>", $currpaymentline);
                    $payment_name = $currpaymentline->m_sName;
                    $payment_amt = $currpaymentline->m_dTicket;
                    //$payment_msg = $currpaymentline->m_returnMessage;
                    if($payment_name == 'magcard' || $payment_name == 'upi'){
                           $payment_msg = $currpaymentline->m_transactionID;   
                        }else{
                           $payment_msg = $currpaymentline->m_returnMessage;
                        } 
                        
                     if(trim($payment_name) == "" || trim($payment_amt)==""){ continue; }
                     $payment_name_db = $db->safe(trim($payment_name));                 
                     $pquery = " insert into it_order_payments set order_id = $order_id , payment_name = $payment_name_db , amount = $payment_amt , createtime = now() ";
                     if( trim($payment_msg) != ""){
                         $payment_msg_db = $db->safe(trim($payment_msg));
                         $pquery .= " , msg = $payment_msg_db  ";
                     }
                     $db->execInsert($pquery);
                    }
                 }
                 //update disc_pct if gift voucher is used
                 updateDiscPct($order_id);
            }
        }
        }
    }// main for each ends
}    


print "0::Success";
} catch (Exception $ex) {
	print "1::Error-".$ex->getMessage();
}

function stockUpdateRevert($order_id,$bill_type,$store_id){
    $db = new DBConn();
    //step 1 : first revert stock changes done by the old order items
    $query = "select * from it_order_items where order_id = $order_id";
    $oiobjs = $db->fetchObjectArray($query);   
    
        foreach($oiobjs as $obj){           
            if($bill_type == 0 ||$bill_type == 6){ // sale bill do revert action means '+'
                $sign = "+";
            }else if($bill_type == 3){ // return bill do revert actions mean '-'
                $sign = "-";
            }else if($bill_type == 1){ // credit bill do revert actions mean '-'
                $sign = "+"; // qty comes as -ve so to minus add it
            }
            $qry = "select * from it_current_stock where barcode = $obj->barcode and store_id = $store_id ";
            $exists = $db->fetchObject($qry);
            if($exists){  
                $q = "update it_current_stock set quantity = quantity ".$sign." $obj->quantity , updatetime = now() where id = $exists->id ";
//                print "<br>UPDATE QRY: $q";
                $db->execUpdate($q);
            }
        }
        
       //step 2 : delete old order items
        $dqry  = "delete from it_order_items where order_id = $order_id";
        $db->execQuery($dqry);
        
       //step 3 : delete old payment items
        $dpqry = "delete from it_order_payments where order_id = $order_id";
        $db->execQuery($dpqry);
        $db->closeConnection();
    
}


function reSaveCancelBill($order_id,$bill_type,$store_id,$records){
    $db = new DBConn();
    //IN this first delete old details
    //Save New details
    
    //step 1 : delete old order items
    $dqry  = "delete from it_order_items where order_id = $order_id";
    $db->execQuery($dqry);

   //step 2 : delete old payment items
    $dpqry = "delete from it_order_payments where order_id = $order_id";
    $db->execQuery($dpqry);
    
    
  //Save New Details  
    $itemlines = explode("<++>", $records[2]);
    $items=array();
    foreach ($itemlines as $currlineitem) {
            $currlineitem=trim($currlineitem);
            if ($currlineitem == "") { continue; }
            list($item_id, $barcode, $price, $quantity, $discount_val, $discount_pct) = explode("<>", $currlineitem);
//                    print "\n".$item_id." ".$barcode." ".$price." ".$quantity." ".$discount_val." ".$discount_pct;
            $query = "insert into it_order_items set store_id=$store_id, order_id=$order_id";
            if ($item_id && trim($item_id) != "") { $query .= ", item_id=$item_id"; }
            if ($barcode && trim($barcode) != "") { $query .= ", barcode='$barcode'"; }
            if ($price && trim($price) != "") { $query .= ", price=$price"; }
            if ($quantity && trim($quantity) != "") { $query .= ", quantity=$quantity"; }
            if ($discount_val && trim($discount_val) != "") { $query .= ", discount_val=$discount_val"; }
            if ($discount_pct && trim($discount_pct) != "") { $query .= ", discount_pct=$discount_pct"; }
// disable for now		if ($tax && trim($tax) != "") { $query .= ", tax=$tax"; }
//                    print "\n".$query."\n";
            $db->execInsert($query);
    }  

    //save payment info if provided
    $paymentinfo="";
    if(count($records)==4){ // means payment details provided
     $paymentinfo = trim($records[3]);
    }
    if(trim($paymentinfo)!=""){
    $paymentlines = explode("<++>", $records[3]);
//    print "<br>PAYMENT INFO 3: ";
//    print_r($paymentlines);
    
     foreach($paymentlines as $currpaymentline){
         $currpaymentline = trim($currpaymentline);
         if($currpaymentline == ""){ continue; }
         list($payment_name,$payment_amt,$payment_msg) = explode("<>", $currpaymentline);
         if(trim($payment_name) == "" || trim($payment_amt)==""){ continue; }
         $payment_name_db = $db->safe(trim($payment_name));
         $pquery = " insert into it_order_payments set order_id = $order_id , payment_name = $payment_name_db , amount = $payment_amt , createtime = now() ";
//         print "<br>PAYMENT QRY: $pquery";
         if( trim($payment_msg) != ""){
             $payment_msg_db = $db->safe(trim($payment_msg));
             $pquery .= " , msg = $payment_msg_db  ";
         }
         $db->execInsert($pquery);
     }
     
      //update disc pct only if gfvoucher is used
      updateDiscPct($order_id);
    }
     $db->closeConnection();
}


function reSaveCancelBill2($order_id,$bill_type,$store_id,$tobj){
    $db = new DBConn();
    //IN this first delete old details
    //Save New details
    
    //step 1 : delete old order items
    $dqry  = "delete from it_order_items where order_id = $order_id";
    $db->execQuery($dqry);

   //step 2 : delete old payment items
    $dpqry = "delete from it_order_payments where order_id = $order_id";
    $db->execQuery($dpqry);
    
    
  //Save New Details  
    $itemlines = $tobj->ticketlines;
    $items=array();
    foreach ($itemlines as $currlineitem) {
//            $currlineitem=trim($currlineitem);
//            if ($currlineitem == "") { continue; }
        if(isset($currlineitem) && !empty($currlineitem) && $currlineitem != null){
            //list($item_id, $barcode, $price, $quantity, $discount_val, $discount_pct) = explode("<>", $currlineitem);
//                    print "\n".$item_id." ".$barcode." ".$price." ".$quantity." ".$discount_val." ".$discount_pct;
            $item_id = $currlineitem->prodreference;
                    $barcode = $currlineitem->prodcode;
                    $price = $currlineitem->price;
                    $quantity = $currlineitem->qty;
                    $discount_val = $currlineitem->discountval;
                    $discount_pct = $currlineitem->discountpct;
                    $cgst_amount = $currlineitem->cgst_amount;
                    $sgst_amount = $currlineitem->sgst_amount;
                    $igst_amount = $currlineitem->igst_amount;
                    $lineTotal = $currlineitem->lineTotal;
                    $hsncode = trim($currlineitem->hsncode);
            $query = "insert into it_order_items set store_id=$store_id, order_id=$order_id";
            if ($item_id && trim($item_id) != "") { $query .= ", item_id=$item_id"; }
            if ($barcode && trim($barcode) != "") { $query .= ", barcode='$barcode'"; }
            if ($price && trim($price) != "") { $query .= ", price=$price"; }
            if ($quantity && trim($quantity) != "") { $query .= ", quantity=$quantity"; }
            if ($discount_val && trim($discount_val) != "") { $query .= ", discount_val=$discount_val"; }
            if ($discount_pct && trim($discount_pct) != "") { $query .= ", discount_pct=$discount_pct"; }
// disable for now		if ($tax && trim($tax) != "") { $query .= ", tax=$tax"; }
//                    print "\n".$query."\n";
            if ($cgst_amount && trim($cgst_amount) != "") { $query .= ", cgst_amount=$cgst_amount"; }
            if ($sgst_amount && trim($sgst_amount) != "") { $query .= ", sgst_amount=$sgst_amount"; }
            if ($igst_amount && trim($igst_amount) != "") { $query .= ", igst_amount=$igst_amount"; }
            if ($lineTotal && trim($lineTotal) != "") { $query .= ", lineTotal=$lineTotal"; }
                    
            $db->execInsert($query);
        }   
    }  

    //save payment info if provided
//    $paymentinfo="";
//    if(count($records)==4){ // means payment details provided
//     $paymentinfo = trim($records[3]);
//    }
    if(isset($tobj->paymentinfo)){
    $paymentlines = $tobj->paymentinfo;
//    print "<br>PAYMENT INFO 3: ";
//    print_r($paymentlines);
    
     foreach($paymentlines as $currpaymentline){
//         $currpaymentline = trim($currpaymentline);
//         if($currpaymentline == ""){ continue; }
        if(isset($currpaymentline) && !empty($currpaymentline) && $currpaymentline != null){  
        // list($payment_name,$payment_amt,$payment_msg) = explode("<>", $currpaymentline);
        $payment_name = $currpaymentline->m_sName;
        $payment_amt = $currpaymentline->m_dTicket;
       // $payment_msg = $currpaymentline->m_returnMessage;
                        if($payment_name == 'magcard' || $payment_name == 'upi'){
                           $payment_msg = $currpaymentline->m_transactionID;   
                        }else{
                           $payment_msg = $currpaymentline->m_returnMessage;
                        } 
            
         if(trim($payment_name) == "" || trim($payment_amt)==""){ continue; }
         $payment_name_db = $db->safe(trim($payment_name));
         $pquery = " insert into it_order_payments set order_id = $order_id , payment_name = $payment_name_db , amount = $payment_amt , createtime = now() ";
//         print "<br>PAYMENT QRY: $pquery";
         if( trim($payment_msg) != ""){
             $payment_msg_db = $db->safe(trim($payment_msg));
             $pquery .= " , msg = $payment_msg_db  ";
         }
         $db->execInsert($pquery);
        }
     }
     
      //update disc pct only if gfvoucher is used
      updateDiscPct($order_id);
    }
     $db->closeConnection();
}

function  updateDiscPct($order_id){
    $db = new DBConn();
    
    //update disc pct only if order payment has "giftvoucher"
    $query = "select * from it_order_payments where order_id = $order_id and payment_name = 'giftvoucher'";
//    print "<br>SEL QRY: $query<br>";
    $exists = $db->fetchObject($query);
    if(isset($exists)){
        
        //step 1 : First calc order amt bf disc
        $q1 = "select sum(amount) as amt_bf_disc from it_order_payments where order_id = $order_id ";
//        print "<br>ABFD QRY: $q1<br>";
        $obj = $db->fetchObject($q1);
       // print_r($obj);
        if(isset($obj)){ $amt_bf_disc =  $obj->amt_bf_disc; }else{ $amt_bf_disc = 0; }
        
        //step 2 : Calc payment order 
        $q2 = "select sum(amount) as gfvoucher_amt from it_order_payments where order_id = $order_id and payment_name ='giftvoucher'";
//       print "<br>GF AMT QRY: $q2<br>";
        $obj2 = $db->fetchObject($q2);
       // print_r($obj2);
        if(isset($obj2)){ $gfvoucher_amt =  $obj2->gfvoucher_amt; }else{ $gfvoucher_amt = 0; }
        
        //step 3 : Calc dist_pct
        $disc_pct = round((($gfvoucher_amt/$amt_bf_disc) * 100),2,PHP_ROUND_HALF_UP);
        //print "<br>DISC PCT: $disc_pct<br>";
        
        //step 4 : update order's dist pct
        $query2 = "update it_orders set discount_pct = $disc_pct ,is_sent =0 where id = $order_id ";
//        print "<br>UPDATE ORDER: $query2<br>";
        $db->execUpdate($query2);
        
        $db->closeConnection();
    }
}

function insertSalesmanReport($gCodeId,$bill_no,$billtype,$return_voucher,$return_per,$timeInMillis,$itemlines1)
        {
    $db = new DBConn();
      if ($timeInMillis && trim($timeInMillis) != "") {
      $timeInSeconds=intval(doubleVal($timeInMillis)/1000);
      $bill_datetime = date("Y-m-d H:i:s",intval($timeInSeconds)); }
    //echo"Store id".$gCodeId."<br>Voucher Number".$bill_no."<br> Type".$billtype."<br>Return Voucher".$return_voucher."<br>Incentive Amount Percent".$incentive_amount_per."<br>Ticket lines";
 $dqry  = "delete from it_salesmanreport where bill_no='$bill_no' and store_id=$gCodeId";
          $db->execQuery($dqry);
  
          foreach ($itemlines1 as $currlineitem) { 
                if(isset($currlineitem) && !empty($currlineitem) && $currlineitem != null){ 
                     if($billtype==0||$billtype==6)
                         {
                     $barcode = $currlineitem->prodcode;
                     $incentive_amount=0;
                     $inquery="select im.multiplier as incentive from it_items i,it_sincentive_multipliers im where im.ctg_id=i.ctg_id and i.barcode='$barcode'";
                     $incobj=$db->fetchObject($inquery);
                     $qty=$currlineitem->qty;
                     if(isset($incobj))
                     {
                       $incentive_amount =$incobj->incentive*$qty; 
                     }
                       $catg_name=$currlineitem->prod_cat_name;
                     
                     $lineTotal = $currlineitem->lineTotal;
                     $salesman=$currlineitem->salesman_person;
                     $return_value=$lineTotal*$return_per/100; 
                     
                      if($incentive_amount<0) { $incentive_amount=0;} 
                       $query="insert into it_salesmanreport set store_id=$gCodeId ,bill_no='$bill_no',return_no='$return_voucher',bill_datetime = '$bill_datetime',barcode=$barcode,catg_name='$catg_name',net_total=$lineTotal,qty=$qty,return_total=$return_value,incentive_amount=$incentive_amount,bill_type=$billtype,salesman_no=$salesman,createtime=now()"; 
                      // echo $query;     
                       $db->execInsert($query); 
                           }
                         }
                       } 
                  }

?>
