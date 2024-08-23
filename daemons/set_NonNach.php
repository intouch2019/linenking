<?php 

require_once("../it_config.php"); //test
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");
require_once ("lib/logger/clsLogger.php");
require_once ("lib/core/clsProperties.php");
require_once ("lib/email/EmailHelper.php");
 
$db = new DBConn(); 
$clsLogger = new clsLogger();
$res ="";
$error="";
$ordercount=0;
$totalorderQty=0;
$storeid_list= array();
$pg_name="";
$ipaddr="";
$start_date = date('Y-m-d H:i:s');
echo "<br>Execution start...<br> datetime: ".$start_date."<br>";

try {
    
    $query = "select id, min_stock_level from it_codes where is_natch_required = 1 and is_closed=0 and id in (161,164) order by createtime desc";
//    $query = "select * from it_codes where is_natch_required = 1 and is_closed=0 order by createtime desc";
    $objs = $db->fetchObjectArray($query); // fetch non nach active stores
//    print_r($objs); exit();
    
    if(isset($objs) && !empty($objs)){
        
        foreach ($objs as $storeobj) {
//            select sum(c.quantity * i.MRP) as curr_stock_value from it_current_stock c , it_items i where c.store_id =  and c.barcode = i.barcode and i.ctg_id not in (4,13,14,15,21,23,29,35,38,39,40,41,42,43,44,45,47,48,49,50,51,52,53,54,55,56,57,58);
            $squery = "select sum(c.quantity * i.MRP) as curr_stock_value from it_current_stock c , it_items i where c.store_id = $storeobj->id  and c.barcode = i.barcode and i.ctg_id not in (4,13,14,15,21,23,29,35,38,39,40,41,42,43,44,45,47,48,49,50,51,52,53,54,55,56,57,58)";
            $sObj = $db->fetchObject($squery); //fetch current stock values for each store
//            print_r($sObj); exit();
            $currValue = 0;
            $min_stock = 0;
            $limitValue = 0;
            
            if(isset($sObj) && !empty($sObj)){
                $currValue = $sObj->curr_stock_value;
                $min_stock = $storeobj->min_stock_level;
                
                $limitValue = $min_stock - ($min_stock*0.15);  //less than 15% of min stock
                echo "min stock Value-   "; print_r($min_stock); echo "<br>";
                echo "current Value-   "; print_r($currValue); echo "<br>";
                 echo "limit Value-   "; print_r($limitValue); 
//                 exit();
                if($currValue <= $limitValue ){   //if current stock value is less than limit value update store to Non-NACH (is_natch_required=1)
                    $uquery = "update it_codes set is_natch_required = 0 where id = $storeobj->id";
                    $uObj = $db->execUpdate($uquery);   
                    
                    $storeid = $storeobj->id;
                    
                    array_push($storeid_list, $storeid);
                } else {
                    continue;
                }
            } else {
                continue;
            } 
        }
        if(!empty($storeid_list)){
            print_r("calling sendNachEmail");
         sendNachEmail($storeid_list);  //call send email function
        }
    } else {
        echo "No Store Found";
    }

                //--> code to log it_items update track
//                $ipaddr =0;
//                $pg_name = __FILE__;
//               
//                if($error !=""){
//                 $clsLogger->logInfo1($error,false, $pg_name,$ipaddr);}
//                 if($totalorderQty>= 0){
//                 $clsLogger->logInfo1(" Order placed for :".$storeid_list." and total order quantity:".$totalorderQty,false, $pg_name,$ipaddr);
//                }
                   //--> log code ends here   
                
//} catch (Exception $xcp) {
//    
//    $clsLogger->logInfo1($xcp->getMessage(),false, $pg_name,$ipaddr);
////    print $xcp->getMessage();
//}
$end_date = date('Y-m-d H:i:s');
echo "Execution end.<br> datetime: ".$end_date;

} catch(Exception $xcp){
   print $xcp->getMessage(); 
}

function sendNachEmail($storeid_list){    
     $db = new DBconn();
     $emailHelper = new EmailHelper();
     $qry = "select * from it_codes where usertype = ".UserType::CKAdmin ." and id in (68,90)"; //specific id chks for koushik n kunal
     $aobjs = $db->fetchObjectArray($qry);
     // sends email to koushik,kunal
     if($aobjs){
        $toArray = array();
        foreach($aobjs as $aobj){ 
            $emails = explode(",",$aobj->email);
            foreach($emails as $email){ array_push($toArray, $email);}
              
        }
        if(!empty($toArray)){                                   
            print "<br>";
            //print_r($toArray);
            $subject = "Franchisee(s) list convert to Non Natch";           
            $body = "<p>This email provides a list of franchisees(s) whose store converted to Non Nach / Advance Party </p><br/>";
            $body .= "<table border='1'>"
                    . "<tr>"
                    . "<th>Store ID</th>"
                    . "<th>Store Name</th>"
                    . "<th>Date</th>"
                    . "</tr>";

            // Adding today's date
            $currDate = date('Y-m-d');
            foreach($storeid_list as $storeid){
                $sql = "select id, store_name from it_codes where id= $storeid";
                $storeObj = $db->fetchObject($sql);
                $body .= "<tr>"
                    . "<td>".$storeObj->id."</td>"
                    . "<td>".$storeObj->store_name."</td>"
                    . "<td>".$currDate."</td>"
                    . "</tr>";
            }
            $body .= "</table>";
            
            $errormsg = $emailHelper->send($toArray, $subject, $body);
            print "<br>EMAIL SENT RESP:".$errormsg;
            if ($errormsg != "0") {
                $errors['mail'] = " <br/> Error in sending mail, please try again later.";
            } 
        }
     }
 }
 
exit;
