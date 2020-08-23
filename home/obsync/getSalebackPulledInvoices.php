<?php

include "checkAccess.php";
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";

extract($_POST);

if (!isset($record) || trim($record) == "") {
	print "1::Missing parameter";
	return;
}
//print "$record";
// format :- inv_no<>datetimeinlong||inv_no<>datetimeinlong||
//$record = "CK294-17180001<>1520944605000||CK294-17180002<>1520944605000||CK294-17180003<>1520944605000||CK294-17180004<>1520944605000||CK307-17180001<>1520945156000||";

try{
    $db = new DBConn();
    $arr = explode("||",$record);

    $ins =0;
    $upt = 0;
    $cnt = 0;
    $totquantity = 0 ; //for checking purpose

    foreach($arr as $rec){

        if ($rec == "") { continue; }
        $fields = explode("<>",$rec);
        $invoice_no = $db->safe($fields[0]);
        //print "$invoice_no </br>";
        $dt = $fields[1];
        $dt /= 1000;
        $proscd_dt = $db->safe(date("Y-m-d H:i:s", $dt));
        
        $idqry = "Select id from it_saleback_invoices  where invoice_no = $invoice_no and is_procsdForRetail = 0 and invoice_type=7";
            //print "$idqry";
        //error_log("\n inv qry: \n".$idqry,3,"../ajax/tmp.txt");
        $invobj = $db->fetchObject($idqry);
        if($invobj){
      
            $upqry = " update it_saleback_invoices set is_procsdForRetail = 1 , procsd_date = $proscd_dt where id = $invobj->id ";
            $upt=$upt+$db->execUpdate($upqry);
            
      }
    }
    
    if($upt>0)
            {
                print "0::Success";
                
            }
        
    
}catch(Exception $ex){
    print "1::Error-".$ex->getMessage();
}
?>

