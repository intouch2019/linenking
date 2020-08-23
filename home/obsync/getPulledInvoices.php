<?php
include "checkAccess.php";
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
//this syn is called by store pos

extract($_POST);
//////print_r($record);
if (!isset($record) || trim($record) == "") {
	print "1::Missing parameter";
	return;
}

// format :- inv_no<>datetimeinlong||inv_no<>datetimeinlong||
//$record = "C141501610<>1400745663000||";
try{
    $db = new DBConn();
    $arr = explode("||",$record);
    $store_id = $gCodeId;
    //$store_id = 98;
    $ins =0;
    $upt = 0;
    $cnt = 0;
    $totquantity = 0 ; //for checking purpose
//    print_r($arr);
    foreach($arr as $rec){
        //print $rec;
        if ($rec == "") { continue; }
        $fields = explode("<>",$rec);
        $invoice_no = $db->safe($fields[0]);
        $dt = $fields[1];
        $dt /= 1000;
        $proscd_dt = $db->safe(date("Y-m-d H:i:s", $dt));
        
        $idqry = "Select id from it_sp_invoices  where invoice_no = $invoice_no and is_procsdForRetail = 0 ";
        //error_log("\n inv qry: \n".$idqry,3,"../ajax/tmp.txt");
        $invobj = $db->fetchObject($idqry);
        if($invobj){
            $query = "select ii.barcode , sum(ii.quantity) as quantity from it_sp_invoice_items ii , it_sp_invoices i where ii.invoice_id = i.id and i.invoice_no = $invoice_no and i.store_id = $store_id group by ii.barcode ";
            //error_log("\n inv item qry: \n".$query,3,"../ajax/tmp.txt");
            $objs = $db->fetchObjectArray($query);
            if($objs){
                foreach($objs as $obj){
                    $barcode = $db->safe(trim($obj->barcode));
                    $quantity = trim($obj->quantity);              
                    $qry = "select * from it_current_stock where barcode = $barcode and store_id = $store_id";
                   // error_log("\n curr stk qry: \n".$qry,3,"../ajax/tmp.txt");
                    $exists = $db->fetchObject($qry);
                    if($exists){
                        $uptqry = " update it_current_stock set quantity = quantity + $quantity , updatetime = now() where barcode = $barcode and store_id = $store_id ";
                        $updated = $db->execUpdate($uptqry);
                        //error_log("\n curr stk up qry: \n".$uptqry,3,"../ajax/tmp.txt");
    //                    echo "<br/>update qry:- $uptqry<br/>";
                        if($updated){ $upt += 1 ;}
                    }else{
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
                        $insQry = "insert into it_current_stock set barcode = $barcode , store_id = $store_id , quantity = $quantity , ctg_id = $ctg_id, design_id = $design_id , style_id = $style_id , size_id = $size_id , createtime = now() ";
                        //$insQry = "insert into it_current_stock set barcode = $barcode , store_id = $store_id , quantity = $quantity , createtime = now() ";
                        $inserted = $db->execInsert($insQry);
                       // error_log("\n curr stk ins qry: \n".$insQry,3,"../ajax/tmp.txt");
    //                    echo "<br/>insert qry:- $insQry<br/>";
                        if($inserted){ $ins += 1; }
                    }

                }
            }
            $upqry = " update it_sp_invoices set is_procsdForRetail = 1 , procsd_date = $proscd_dt where id = $invobj->id ";
            $db->execUpdate($upqry);
      }
    }
        
    print "0::Success";
}catch(Exception $ex){
    print "1::Error-".$ex->getMessage();
}
?>
