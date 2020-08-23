<?php
//ini_set('max_execution_time', 300);;;;
include "../../it_config.php";
require_once "session_check.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
//require_once "lib/logger/clsLogger.php";///
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
 
    
    $reasontype = isset($_GET['stateid']) ? ($_GET['stateid']) : false;
    
   // $reasontype=22;
    if(!$reasontype){ $error['stateid'] = "Not able to get state Id"; }
    
    if(count($error) == 0){

         $reg ="";
 
      $region =   $db->fetchObjectArray("select  * from region where state_id=$reasontype");
    //  print_r($region);
      //print 'hii';
      //return;

        if($region != NULL){
            ?><?php
            foreach($region as $reg){

                    ?>

<option value="<?php echo $reg->id;?>"> <?php echo $reg->region;?></option>

            <?php
            }
        }
        
        else{
            
            print 'region not set';
        }
    }
 
}catch(Exception $xcp){
    print($xcp->getMessage());
    
}


