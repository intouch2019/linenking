<?php
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
require_once 'lib/core/Constants.php';
require_once "lib/serverChanges/clsServerChanges.php";
$db = new DBConn();

try{
    // the it_server_changed table id was ->264127
    // it contained the invoice_no->C141500270
     $store_id = 127; // whose invoice it was
     $query = "select * from it_invoices where invoice_no = 'C141500270' ";
     $serverCh = new clsServerChanges();
            //echo "<br/>".$query."<br/>";
            $invoice = $db->fetchObject($query);
            $json_obj = array();
                    $json_invoice = array();
                    $json_invoice['invoice_id'] = $invoice->id;
                    $json_invoice['invoice_no'] = $invoice->invoice_no;
                    $json_invoice['invoice_dt'] = $invoice->invoice_dt;
                    $json_invoice['invoice_amt'] = $invoice->invoice_amt;
                    $json_invoice['invoice_qty'] = $invoice->invoice_qty;
                    $json_invoice['total_mrp'] = $invoice->total_mrp;
                    $json_invoice['discount_1'] = $invoice->discount_1;
                    $json_invoice['discount_2'] = $invoice->discount_2;
                    $json_invoice['tax'] = $invoice->tax;
                    $json_invoice['payment'] = $invoice->payment;
                    //$items = $db->fetchObjectArray("select * from it_invoice_items where invoice_id = $invoice->id");
                    $items = $db->fetchObjectArray("select item_code,price,quantity from it_invoice_items where invoice_id = $invoice->id");
                    $json_items = array();
                    foreach ($items as $item) {
                        $json_items[] = json_encode($item);
                    }
                    $json_invoice['items']=  json_encode($json_items);
             $server_ch = json_encode($json_invoice);                      
             $ser_type = changeType::invoices;    
          // here $invoice_id is id of table it_invoices so it becomes data_id
            $serverCh->save($ser_type, $server_ch,$store_id,$invoice->id);
    $db->closeConnection();
}catch(Exception $xcp){
    print $xcp;
}
print "0::Success";
?>
