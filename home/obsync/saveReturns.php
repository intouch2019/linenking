<?php
include "checkAccess.php";
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
require_once "lib/serverChanges/clsServerChanges.php";
require_once "lib/core/Constants.php";
// this sync is called by store 
// when store returns items to warehouse
//record pattern :- "ReturnNumber<>timeInMillis<>ReturnAmount<>ReturnQty<>MRP_TOTAL<>DISC1v<>DISC2v<>TAX<>TYPE<>DISC1P<>DISC2P<>TAXRATE"
//		+ "<==>barcode<>itemname<>unitprice<>quantity<++>barcode<>itemname<>unitprice<>quantity|||||";
extract($_POST);
    //
//$records="8<>1553767576000<>197.5<>1<>250.0<>52.5<>0.0<>0.0<>0<>21.0<>5.0<>0.05<==>8905000019873<>SLIM SHIRT<>250.0<>1<++>|||||";

if (!$records) {
	print "1::The order information is missing. Please make sure the receipt information is displayed before re-submitting.";
	return;
}
// record pattern => ReturnNumber<>timeInMillis<>RetdurnAmount<>ReturnQty" + "<==>barcode<>itemname<>unitprice<>quantity<>linetotal<++>barcode<>itemname<>unitprice<>quantity<>linetotal
//$record = "1<>1392805746000<>650.0<>1<==>0010010010001<>SHIRT<>650.0<>1<>650.0<++>|||||";
//$records = "1<>1400853240000<>892.7625<>1<>895.0<>0.0<>44.75<>42.5125<>0<>0.0<>5.0<>0.05<==>0010070041357<>Formal Shirt<>895.0<>1<++>|||||";
//print "resultsss".$records;
//return;
try {
    
$serverCh = new clsServerChanges();
//$db = new DBConn();

//$store_id=99;
$store_id = $gCodeId;
$return_id=0;
$errflg=0;
$arr = explode("|||||", $records);
foreach ($arr as $ticketInfo) {
	$ticketInfo = trim($ticketInfo);
	if ($ticketInfo == "") { continue; }
	$records = explode("<==>", $ticketInfo);
	if (count($records) == 0) { continue; }
	list($return_no, $timeInMillis, $return_amt, $return_qty, $total_mrp,$disc1v,$disc2v,$tax,$type,$disc1p,$disc2p,$tax_rate) = explode("<>", $records[0]);

	$timeInSeconds=intval(doubleVal($timeInMillis)/1000); // convert millis to seconds
	$db = new DBConn();
        $return_dt = $db->safe(date("Y-m-d H:i:s",intval($timeInSeconds)));
        $db->closeConnection();
        if(trim($return_no)== "" || trim($timeInMillis)== "" || trim($return_amt) == "" || trim($return_qty) == "" || trim($total_mrp) == "" || trim($disc1v) == "" || trim($disc2v) == "" ||  trim($disc1p) == "" || trim($disc2p) == "" || trim($type) == "" ){
            $errflg=1; break;
        }
        $db = new DBConn();
        $return_no = $db->safe($return_no);
        $db->closeConnection();
        $qry = "select * from it_store_returns where store_id = $store_id and return_no = $return_no ";
        $db = new DBConn();
        $exists = $db->fetchObject($qry);
        $db->closeConnection();
        if($exists){ continue; }
 
	$query = "insert into it_store_returns set store_id=$store_id, return_no=$return_no, date=$return_dt, amount=$return_amt, quantity=$return_qty , total_mrp = $total_mrp , disc_1_value = $disc1v , disc_2_value = $disc2v , tax = $tax , type = $type , disc_1_per = $disc1p , disc_2_per = $disc2p , tax_rate = $tax_rate ";
        // echo "<br/>$query";
         //return;
        $db = new DBConn();
	$return_id = $db->execInsert($query);
        $db->closeConnection();
         
	$itemlines = explode("<++>", $records[1]);
	foreach ($itemlines as $currlineitem) {
		$currlineitem=trim($currlineitem);
		if ($currlineitem == "") { continue; }
		list($barcode, $name,  $price, $quantity) = explode("<>", $currlineitem);
                $db = new DBConn();
                $barcode = $db->safe(trim($barcode));
                $name = $db->safe($name);
                $db->closeConnection();
		$query = "insert into it_store_returnitems set return_id=$return_id, item_code=$barcode, name = $name , quantity=$quantity , price = $price ";
		//echo "<br/>$query";
              //  return;
                $db = new DBConn();
                $db->execInsert($query);
                $db->closeConnection();
                $qry = "select * from it_current_stock where barcode = $barcode and store_id = $store_id ";
                $db = new DBConn();
                $exists = $db->fetchObject($qry);
                $db->closeConnection();
                if($exists){    
                    $db = new DBConn();
                    $db->execUpdate("update it_current_stock set quantity = quantity - $quantity , updatetime = now() where id = $exists->id ");
                    $db->closeConnection();
                    
                }else{
                    $iqry = "select * from it_items where barcode = $barcode ";
                    $db = new DBConn();
                    $iobj = $db->fetchObject($iqry);
                    $db->closeConnection();
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
                    $insQry = "insert into it_current_stock set barcode = $barcode , store_id = $store_id , quantity = $quantity , ctg_id = $ctg_id, design_id = $design_id , style_id = $style_id , size_id = $size_id , createtime = now() ";
                    $db = new DBConn();
                    $inserted = $db->execInsert($insQry);
                    $db->closeConnection();
                    
                    }
	}
}
//push to server chgs
                            $query = "select * from it_store_returns where id = $return_id ";   
                            // echo $query;
//                            exit();
                            $db = new DBConn();
                            $return = $db->fetchObject($query);
                            $db->closeConnection();
                            $json_obj = array();  
                                    $json_invoice = array();
                                    if(isset($return)){
//                                        echo "select return_id,return_no,item_code,name,quantity,price,challan_id,tax_rate,disc1,invoice_no,invoice_date from it_store_returnitems where return_id=$return->id";
//                                  exit();
                                    $json_invoice['server_id'] = $return->id;
                                    $json_invoice['store_id'] = $return->store_id;
                                    $json_invoice['return_no'] = $return->return_no;
                                    $json_invoice['date'] = $return->date;
                                    $json_invoice['amount'] = $return->amount;
                                    $json_invoice['quantity'] = intval($return->quantity);  //total_mrp
                                    $json_invoice['total_mrp'] = intval($return->total_mrp);
                                    $json_invoice['disc_1_value'] = $return->disc_1_value;
                                    $json_invoice['type'] = $return->type;
                                    $json_invoice['disc_1_per'] = $return->disc_1_per;
                                
                                   // $items = $db->fetchObjectArray("select return_id,return_no,item_code,name,quantity,price,challan_id,tax_rate,disc1,invoice_no,invoice_date from it_store_returnitems where return_id=$return->id");
                                    $db = new DBConn();
                                    $items = $db->fetchObjectArray("select return_id,item_code,name,sum(quantity) as quantity ,price from it_store_returnitems where return_id=$return->id group by item_code");
                                    $db->closeConnection();
                                   // echo "select return_id,item_code,name,sum(quantity) as quantity from it_store_returnitems where return_id=$return->id group by item_code";
                                   // print_r($items) ;
                                   // return
                                 //  echo "select return_id,return_no,item_code,name,quantity,price,challan_id,tax_rate,($disc1p+$disc2p),invoice_no,invoice_date from it_store_returnitems where return_id=$return->id";
//                                    exit();
                                    $json_items = array();
                                    foreach ($items as $item)
                                        {
                                        $item->quantity = intval($item->quantity);
                                        $item->price = intval($item->price);
                                        $json_items[] = json_encode($item);
                                        }
                                    $json_invoice['items']=  json_encode($json_items);
                                    $server_ch = json_encode($json_invoice);     
                                    $ckwhid = DEF_CK_WAREHOUSE_ID;
                                    
                                //    echo $ckwhid;
                                 //   exit;
                                    
                                    $ser_type = changeType::puchasereturn;  
                                    //   print $ser_type;
                                    // here $invoice_id is id of table it_invoices so it becomes data_id
                                    $serverCh->save($ser_type, $server_ch,$ckwhid,$return->id);
                                    
                                    
    } 
$db->closeConnection();
if($errflg == 1){
    print "1::error header parameters missing ";
}else{
    print "0::Success";
}
} catch (Exception $ex) {
$msg = print_r($ex,true);
print "1::Error-$msg";
}
