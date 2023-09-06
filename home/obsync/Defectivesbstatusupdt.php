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


if(!isset($records) || trim($records) == ""){
	$logger->logError("Missing parameter [records]:".print_r($_POST, true));
	print "1::Missing parameter [records]";
	return;
}


try {
    $db = new DBConn();
    $serverCh = new clsServerChanges();
    $clsLogger = new clsLogger();
    $store_id = $gCodeId;
 
   
    
    //$store_id = 125; // comment when this page given for live
    $errflg=0;
    //check if invoice text is complete ,then only proceed.
    if (strpos($records,'<<>>') === false) {   
        print "1::Invoice text incomplete";
        return;
    }
    $arr = explode("<<>>",$records);
   
$is_limelight=false;
$is_tyson=false;
    foreach ($arr as $record) {
        $invoice_text = $db->safe(trim($record));
        if (trim($record) == "") { continue; }

        
        
        //dgsaleback start
        $cnanddate = explode("<>", $record);

                if ((!empty($cnanddate[0])) || (!empty($cnanddate[1]))) {


                    $cnst = $cnanddate[0];
                    $salebckdate = $cnanddate[1];

                    if (isset($cnst) && isset($salebckdate)) {
                        if ($cnst != "" || $cnst != 0) {
                            
				$query="select bill_no from it_orders where store_id=$store_id and bill_no='$cnst'";

				$invoice = $db->fetchObject($query);
			if(isset($invoice) && $invoice!="") {
                            $sqlcnquery = "update it_orders set is_saleback=1,saleback_time='$salebckdate' where store_id=$store_id and bill_no='$cnst'";
                            $rtn = $db->execUpdate($sqlcnquery);
                                }else{
                                    $errflg = 1;
                                }

                           
                        }
                    }
                }else{
                    $errflg=2;
                }
            }
        
        //dgsaleback end
           
          
    
    
   
//    $db->closeConnection();
    if($errflg == 1){
     print "1::Invoice not found ";
    }else if($errflg == 2 ){
     print "1::Data mismatch";
    }else{
     print "0::Success";
    }
    
} catch (Exception $ex) {
	print "1::Error-".$ex->getMessage();
}

