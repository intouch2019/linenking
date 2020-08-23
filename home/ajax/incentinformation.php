<?php
//ini_set('max_execution_time', 300);
include "../../it_config.php";
require_once "session_check.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
//require_once "lib/logger/clsLogger.php";
require_once ("lib/core/strutil.php");



$userid=getCurrUserId();
$currStore = getCurrUser();
$error = array();

if (!$currStore) {
    print "User session timedout. Please login again";
    return;
}


try{
    $db = new DBConn();
 
    
    $storeid = isset($_GET['storeid']) ? ($_GET['storeid']) : false;
      $fromdate = isset($_GET['fromdate']) ? ($_GET['fromdate']) : false;
       $enddate = isset($_GET['enddate']) ? ($_GET['enddate']) : false;
   // $reasontype=22; set
    if(!$storeid){ $error['storeid'] = "Not able to get Store Id"; }
    if(!$fromdate){ $error['fromdate'] = "Not able to get Start Date"; }
    if(!$enddate){ $error['enddate'] = "Not able to get End Date"; }
    
    if(count($error) == 0){
       // print "select  * from it_sales_incentive where store_id=$storeid and start_date='$fromdate 00:00:00' and end_date='$enddate 23:59:59'";
         
         $incen ="";
         $selectedinc=5;
         $selestoreinc=5;
         $query ="select salesman_incentive from it_salesman_incen";     
         $obj_salesman= $db->fetchObjectArray($query);
         
         $query ="select salesman_incentive from it_salesman_incen";     
         $obj_salesman= $db->fetchObjectArray($query);
        
         $salesincentive =$db->fetchObject("select  * from it_sales_incentive where store_id=$storeid and start_date='$fromdate 00:00:00' and end_date='$enddate 23:59:59'");
         if(isset($salesincentive)){
         $selectedinc = $salesincentive->salesman_incentive;
         $selestoreinc = $salesincentive->store_incentive;
         }
         else{ 
             echo '<=====>';
             
         }
          if($obj_salesman != NULL){
            ?><?php
            
            foreach($obj_salesman as $incen){
                $selected = "";
                if($incen->salesman_incentive == $selectedinc){
                    $selected = "selected";
                }?>
                    <option value="<?php echo $incen->salesman_incentive;?>" <?php  echo $selected ?>  > <?php echo $incen->salesman_incentive;?></option>
            <?php   }?>
            <?php echo '<=====>';  ?>
            
            
            
           <?php   foreach($obj_salesman as $incen){
                $selected = "";
                if($incen->salesman_incentive == $selestoreinc){
                    $selected = "selected";
                }?>
                    <option value="<?php echo $incen->salesman_incentive;?>" <?php  echo $selected ?>  > <?php echo $incen->salesman_incentive;?></option>
            <?php }
            
            
            
        }
        
        
        
        
 
    }
 
}catch(Exception $xcp){
    print($xcp->getMessage());
    
}


