<?php
include "checkAccess.php";
require_once "../../it_config.php";

require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/serverChanges/clsServerChanges.php";

extract($_POST);
if (!$records) {
	print "1::The order information is missing. Please make sure the receipt information is displayed before re-submitting.";
	return;
}

try {
//$records = "1238<>L141502975<>0<>1402557139000<>69817.0<>61.0<>92350.0<>18470.0<>7388.0<>3324.6<>Axis Bank::dsfsdfs<>Administrator<>L-VAT1<>0.05<><><==>81<==>8900000274512<>SLIM SHIRT<>1795.0<>1.0<>1795.0<++>8900000272686<>KURTA<>1095.0<>1.0<>1095.0<++>8900000267897<>FORMAL SHIRT<>995.0<>1.0<>995.0<++>8900000248278<>T-Shirt<>650.0<>1.0<>650.0<++>8900000257089<>TROUSER<>1895.0<>1.0<>1895.0<++>8900000239078<>TROUSER<>1895.0<>1.0<>1895.0<++>8900000262588<>TROUSER<>1995.0<>1.0<>1995.0<++>8900000263516<>TROUSER<>1995.0<>1.0<>1995.0<++>8900000273355<>TROUSER<>895.0<>1.0<>895.0<++>8900000273928<>JEANS<>1295.0<>1.0<>1295.0<++>8900000275243<>SLIM SHIRT<>1695.0<>1.0<>1695.0<++>8900000275250<>SLIM SHIRT<>1695.0<>1.0<>1695.0<++>8900000248049<>SLIM SHIRT<>1795.0<>1.0<>1795.0<++>8900000264483<>FORMAL SHIRT<>1595.0<>1.0<>1595.0<++>8900000220519<>SHORT KURTA<>1695.0<>1.0<>1695.0<++>8900000163670<>SLIM SHIRT<>1495.0<>1.0<>1495.0<++>8900000266555<>SLIM SHIRT<>1295.0<>1.0<>1295.0<++>8900000269006<>SLIM SHIRT<>1595.0<>1.0<>1595.0<++>8900000276271<>SLIM SHIRT<>1695.0<>1.0<>1695.0<++>8900000163649<>SLIM SHIRT<>1495.0<>1.0<>1495.0<++>8900000219209<>SHORT KURTA<>1595.0<>1.0<>1595.0<++>8900000257430<>SLIM SHIRT<>1595.0<>1.0<>1595.0<++>8900000266869<>SLIM SHIRT<>1795.0<>1.0<>1795.0<++>8900000257416<>SLIM SHIRT<>1595.0<>1.0<>1595.0<++>8900000266852<>SLIM SHIRT<>1795.0<>1.0<>1795.0<++>8900000264612<>SLIM SHIRT<>1595.0<>1.0<>1595.0<++>8900000265893<>SLIM SHIRT<>1695.0<>1.0<>1695.0<++>8900000165025<>SLIM SHIRT<>995.0<>2.0<>1990.0<++>8900000275458<>SLIM SHIRT<>1695.0<>1.0<>1695.0<++>8900000236268<>FORMAL SHIRT<>1495.0<>1.0<>1495.0<++>8900000267576<>SLIM SHIRT<>995.0<>1.0<>995.0<++>8900000260362<>FORMAL SHIRT<>1295.0<>1.0<>1295.0<++>8900000266463<>FORMAL SHIRT<>1595.0<>1.0<>1595.0<++>8900000264773<>FORMAL SHIRT<>1595.0<>1.0<>1595.0<++>8900000239283<>SLIM SHIRT<>1795.0<>1.0<>1795.0<++>8900000165285<>SLIM SHIRT<>1795.0<>1.0<>1795.0<++>8900000267965<>FORMAL SHIRT<>995.0<>1.0<>995.0<++>8900000245017<>SLIM SHIRT<>1795.0<>1.0<>1795.0<++>8900000273591<>FORMAL SHIRT<>1695.0<>1.0<>1695.0<++>8900000266654<>FORMAL SHIRT<>695.0<>1.0<>695.0<++>8900000262939<>SLIM SHIRT<>1695.0<>1.0<>1695.0<++>8900000231041<>SLIM SHIRT<>1695.0<>1.0<>1695.0<++>8900000116393<>SLIM SHIRT<>1295.0<>1.0<>1295.0<++>8900000272440<>SLIM SHIRT<>1595.0<>1.0<>1595.0<++>8900000116409<>SLIM SHIRT<>1295.0<>1.0<>1295.0<++>8900000118847<>SLIM SHIRT<>1495.0<>1.0<>1495.0<++>8900000275168<>SLIM SHIRT<>1695.0<>1.0<>1695.0<++>8900000118878<>SLIM SHIRT<>1495.0<>1.0<>1495.0<++>8900000101429<>FORMAL SHIRT<>595.0<>1.0<>595.0<++>8900000253920<>SLIM SHIRT<>1695.0<>1.0<>1695.0<++>8900000266562<>SLIM SHIRT<>1795.0<>1.0<>1795.0<++>8900000240401<>SLIM SHIRT<>1795.0<>1.0<>1795.0<++>8900000119226<>FORMAL SHIRT<>1495.0<>1.0<>1495.0<++>8900000099818<>SLIM SHIRT<>1495.0<>2.0<>2990.0<++>8900000208302<>SLIM SHIRT<>1695.0<>1.0<>1695.0<++>8900000130627<>FORMAL SHIRT<>1595.0<>1.0<>1595.0<++>8900000271733<>SLIM SHIRT<>1795.0<>1.0<>1795.0<++>8900000099825<>SLIM SHIRT<>1495.0<>2.0<>2990.0<++><==>||||";    
//$records = "822<>14150035<>0<>1397542545000<>6033.0<>5.0<>1741.6<>0.0<>Administrator<==>83<==>8900000249305<>SHIRT PIECE<>1595.0<>1.0<>1595.0<++>8900000249374<>SHIRT PIECE<>1495.0<>1.0<>1495.0<++>8900000248636<>SHIRT PIECE<>1495.0<>1.0<>1495.0<++>8900000248568<>SHIRT PIECE<>1595.0<>1.0<>1595.0<++>8900000249152<>SHIRT PIECE<>1595.0<>1.0<>1595.0<++>|||||822<>14150039<>0<>1397542545000<>6033.0<>5.0<>1741.6<>0.0<>Administrator<==>83<==>8900000249305<>SHIRT PIECE<>1595.0<>1.0<>1595.0<++>8900000249374<>SHIRT PIECE<>1495.0<>1.0<>1495.0<++>8900000248636<>SHIRT PIECE<>1495.0<>1.0<>1495.0<++>8900000248568<>SHIRT PIECE<>1595.0<>1.0<>1595.0<++>8900000249152<>SHIRT PIECE<>1595.0<>1.0<>1595.0<++>|||||";
$db = new DBConn();
$serverCh = new clsServerChanges();
//check if invoice text is complete ,then only proceed.
    if (strpos($records,'|||||') === false) {   
        print "1::Invoice text incomplete";
        return;
    }
$arr = explode("|||||", $records);
foreach ($arr as $invInfo) {
        $invInfo = trim($invInfo);
	if ($invInfo == "") { continue; }
	$records = explode("<==>", $invInfo);
	if (count($records) == 0) { continue; }
        $invfields = explode("<>",$records[0]);
          if ($invfields) {
                  $ck_invoice_id = trim($invfields[0]);
//                  echo "<br/>ck inv id: $ck_invoice_id <br/>";
                  $invoice_no = $db->safe($invfields[1]);
                  $exists = $db->fetchObject("select * from it_sp_invoices where invoice_no=$invoice_no");                       
                  if ($exists){ continue;} // continue if invoice_no already exists
                  $invoice_type = $db->safe($invfields[2]);
    //                    $invoice_dt = $db->safe($invfields[2]);
                  $dt = $invfields[3];
                  $dt /= 1000;
                  $invoice_dt = $db->safe(date("Y-m-d H:i:s", $dt));
                  $invoice_amt = floatval($invfields[4]);		
                  $invoice_qty = doubleval($invfields[5]);
                  $total_mrp  = doubleval($invfields[6]);                    
                  $discount_1  = doubleval($invfields[7]);
                  $discount_2  = doubleval($invfields[8]);
                  $tax  = doubleval($invfields[9]);
                  $payment  = $db->safe($invfields[10]);
                  $tax_type = $db->safe($invfields[12]);
                  $tax_percent = doubleval($invfields[13]);                  
          } 

        $store_id=$records[1];
        $invInfo = $db->safe($invInfo);
	$query = "insert into it_sp_invoices set invoice_text = $invInfo,store_id=$store_id, invoice_no=$invoice_no, invoice_dt=$invoice_dt, invoice_amt=$invoice_amt, invoice_type=$invoice_type,invoice_qty=$invoice_qty, total_mrp = $total_mrp , discount_1=$discount_1,discount_2=$discount_2, tax=$tax, payment = $payment, tax_type = $tax_type , tax_percent = $tax_percent ";
	if ($ck_invoice_id && trim($ck_invoice_id) != "" && intval($invoice_amt) == 0) {
		$query .= ", return_id=$ck_invoice_id";
	} else if ($ck_invoice_id && trim($ck_invoice_id) != "") {
		$query .= ", ck_invoice_id=$ck_invoice_id";
	}

	$invoice_id = $db->execInsert($query);
        if (!$invoice_id) { continue; }
	if ($ck_invoice_id && trim($ck_invoice_id) != "") {
		$db->execUpdate("update it_invoices set sp_invoice_id=$invoice_id where id=$ck_invoice_id");
	}
	$itemlines = explode("<++>", $records[2]);
	foreach ($itemlines as $currlineitem) {
		$currlineitem=trim($currlineitem);
		if ($currlineitem == "") { continue; }
		list($barcode, $lineItemName, $unitPrice, $lineQuantity, $lineTotal) = explode("<>", $currlineitem);
		$query = "insert into it_sp_invoice_items set invoice_id=$invoice_id, barcode=$barcode, price=$unitPrice, quantity=$lineQuantity";
		$db->execInsert($query);
	}
        
         if($invoice_type == "'0'"){ // only sales inv shld get inserted in it_server_changes
                //$query = "select * from it_invoices where id = $invoice_id ";                
                $query = "select sp.*  from  it_sp_invoices sp where sp.id = $invoice_id and sp.invoice_type = '0'";
                // add code to include sp_invoice_no field
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
                 $ser_type = changeType::invoices;  
                 $sp_store_id = $invoice->store_id;
              // here $invoice_id is id of table it_invoices so it becomes data_id
                $serverCh->save($ser_type, $server_ch,$sp_store_id,$invoice_id);
            }
}
$db->closeConnection();
print "0::Success";
} catch (Exception $ex) {
$msg = print_r($ex,true);
error_log($msg, 3, "/home/limelight/logs/limelight-error.log");
print "1::Error-$msg";
}
