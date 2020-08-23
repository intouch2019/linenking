<?php
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
require_once 'lib/core/Constants.php';
require_once "lib/serverChanges/clsServerChanges.php";
$db = new DBConn();
$serverCh = new clsServerChanges();
$count=0;
try{
    //$query = "select ii.* ,sp.invoice_no as sp_invoice_no from it_invoices ii , it_sp_invoices sp where ii.sp_invoice_id = sp.id and ii.invoice_type = 0 ";
    $query = "SELECT ii . *  FROM it_invoices ii  WHERE  ii.invoice_type = '0'";
    //$allMissedServerInvs = $db->fetchObjectArray($query);
    $result = $db->execQuery($query);
     if ($db->getConnection()->error) { throw new Exception($db->getConnection()->error); }
     if (!$result) { print "no results\n"; $db->closeConnection(); return; }
      while ($obj = $result->fetch_object()) {
        $invoice_id = $obj->id;
        //$query2 = "select * from it_invoices where id = $invoice_id ";          
        $query2 = "select ii.* ,sp.invoice_no as sp_invoice_no,sp.store_id as storeid from it_invoices ii , it_sp_invoices sp where ii.sp_invoice_id = sp.id and  ii.id = $invoice_id and ii.invoice_type = 0 ";
        $invoice = $db->fetchObject($query2);
        $store_id = $invoice->storeid;
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
        $json_invoice['tax_type'] = $invoice->tax_type;
        $json_invoice['tax_percent'] = $invoice->tax_percent;
        $json_invoice['no_of_challans'] = $invoice->no_of_challans;
        $json_invoice['challan_numbers'] = $invoice->challan_nos;
        $json_invoice['sp_invoice_no'] = $invoice->sp_invoice_no;

        $items = $db->fetchObjectArray("select item_code,price,quantity from it_invoice_items where invoice_id = $invoice->id");
        $json_items = array();
        foreach ($items as $item) {                               
            $json_items[] = json_encode($item);
        }
        $json_invoice['items']=  json_encode($json_items);

         $server_ch = json_encode($json_invoice);                         
         $ser_type = changeType::ckinvoices;    
      // here $invoice_id is id of table it_invoices so it becomes data_id
        $serverCh->save($ser_type, $server_ch,$store_id,$invoice_id); 
        $count++;
                       
    }
    $result->close();
    $db->closeConnection();
    
    
}catch( Exception $xcp){
    print $xcp->getMessage();
}
print "0::success \n Total ".$count." invoices inserted in server changes table ";
?>
