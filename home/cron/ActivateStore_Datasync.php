<?php
//for live
ini_set('memory_limit', '-1');
ini_set('max_execution_time', '-1');
require_once("/var/www/html/linenking/it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/serverChanges/clsServerChanges.php";
require_once "lib/logger/clsLogger.php";

//for test
//require_once("/../../it_config.php");
//require_once("session_check.php");
//require_once "lib/db/DBConn.php";
//require_once "lib/logger/clsLogger.php";
//require_once 'lib/core/Constants.php';




try {
    $db = new DBConn();
    $dt = new DateTime();
    $serverdate = $dt->format('Y-m-d H:i:s');
    $cnt = 0;
    $dealersList = array();

    $alldealersobj = $db->fetchObjectArray("select id,store_name from it_codes where usertype = " . UserType::Dealer . " and is_closed = 0 and id not in (98,164,367,436) order by id "); //and inactive = 0

    foreach ($alldealersobj as $dealerobj) {

        //checking createtime from it_current stock table
        $query = "select createtime from it_current_stock where store_id = $dealerobj->id order by id desc limit 1 ";
        $cobj = $db->fetchObject($query);
        if (isset($cobj)) {
            $synchdate = $cobj->createtime;
            $diffdate = floor((strtotime($serverdate) - strtotime($synchdate)) / (60 * 60 * 24));
            if ($diffdate > 2) {

		$query2 = "select pingtime from it_store_pingtime where store_id= $dealerobj->id ";
     		 $cobj2 = $db->fetchObject($query2);
                //checking updatetime from it_current stock table if diffencce is geather than 2 i.e not Synch within 2 days to portal
                $query1 = "select updatetime from it_current_stock where store_id = $dealerobj->id order by updatetime desc limit 1 ";
                $cobj1 = $db->fetchObject($query1);
                if (isset($cobj1)) {
                    $synchdateUpdate = $cobj1->updatetime;
                    $diffdate2 = floor((strtotime($serverdate) - strtotime($synchdateUpdate)) / (60 * 60 * 24));
                    if($diffdate < $diffdate2){ 
                        $showdiff = $diffdate;
                        $showdate = $synchdate;
                    }else{ 
                        $showdiff = $diffdate2; 
                        $showdate = $synchdateUpdate;
                    }
                    if ($showdiff > 2) {
                         $disablestoreqry="Update it_codes set inactive_bydatasync=1 where id= ".$dealerobj->id."";
                           $db->execUpdate($disablestoreqry);

//        print_r(" 1 -> ".$disablestoreqry);exit();
                    }else if ($showdiff < 2){
                         $enablestoreqry="Update it_codes set inactive_bydatasync=0 where id= ".$dealerobj->id."";
                          $db->execUpdate($enablestoreqry);
//        print_r(" 2 -> ".$enablestoreqry); exit();
                    }

                }
            }else if ($diffdate < 2){
                $enablestoreqry="Update it_codes set inactive_bydatasync=0 where id= ".$dealerobj->id."";
                $db->execUpdate($enablestoreqry);
//                 print_r(" 3 -> ".$enablestoreqry); exit();
            }

        }

    }

    $db->closeConnection();
} catch (Exception $xcp) {
    print $xcp->getMessage();
}