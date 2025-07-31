<?php

include '/var/www/html/linenking/it_config.php';
//require_once("/../it_config.php");
require_once 'lib/db/DBConn.php';
require_once 'lib/core/Constants.php';
require_once "Classes/PHPExcel.php";
require_once "lib/core/strutil.php";
require_once 'lib/users/clsUsers.php';
require_once "lib/serverChanges/clsServerChanges.php";

$start_date = date('Y-m-d H:i:s');
echo "<br>Execution start...<br> datetime: ".$start_date."<br>";
//exit();
try {
    $db = new DBConn();
    $cnt = 0;

    $query = "select id from it_codes where usertype = " . UserType::Dealer  ;
    $all_stores = $db->fetchObjectArray($query);

    if (!empty($all_stores)) {
        foreach ($all_stores as $store) {
            $inactiveCpQuery = "select id, store_id,points_to_upload from it_store_redeem_points where is_sent=0 and active=0 and is_completely_used=0 and store_id=$store->id order by id limit 1;";
//            echo $inactiveCpQuery."<br>";
//            exit();
            $inactiveCpObj = $db->fetchObject($inactiveCpQuery);
//            print_r($inactiveCpObj);
//            exit();
            if (!empty($inactiveCpObj)) {
//                echo "select sum(points_to_upload) as cu from it_store_redeem_points where store_id=$store->id and is_completely_used=0 and active=1 and is_sent=1;<br>";
//                exit();
                //if less than 2 Credit points remaining in active points it will marked as completely used so we can activate the next INACTIVE points
                $uploaded_cp = $db->fetchObject("select id, sum(points_to_upload) as uploaded_points from it_store_redeem_points where store_id=$store->id and is_completely_used=0 and active=1 and is_sent=1");
                $difference = 0;
                if (isset($uploaded_cp) && !empty($uploaded_cp) && $uploaded_cp->uploaded_points != null) {
                    $partial_cp_used = $db->fetchObject("select sum(points_used) as partail_points from it_store_redeem_points_partial where it_store_redeem_points_id=$uploaded_cp->id");
                    if (isset($partial_cp_used) && !empty($partial_cp_used) && $partial_cp_used->partail_points != null) {
                        $partail_points_sum = $partial_cp_used->partail_points;
                    } else {
                        $partail_points_sum = 0;
                    }
                    $difference = $uploaded_cp->uploaded_points - $partail_points_sum;
                    if ($difference <= 2) {
                        $squery = "update it_store_redeem_points set is_completely_used=1 where store_id=$store->id and id=$uploaded_cp->id";
                        $db->execUpdate($squery);
                    }
                }
                $is_cp_used = $db->fetchObject("select sum(points_to_upload) as cu from it_store_redeem_points where store_id=$store->id and is_completely_used=0 and active=1 and is_sent=1");
//               print_r($is_cp_used);
                if (isset($is_cp_used) && !empty($is_cp_used) && $is_cp_used->cu!=null) {
//                    echo "Hi";
                    continue;
                } else {
//                    echo $inactiveCpQuery; exit();
//                    echo '<br>';
                    $uquery = "update it_store_redeem_points set active=1 where id=$inactiveCpObj->id";
//                    echo $uquery;
                    $db->execUpdate($uquery);
//                    exit();
                   //Credit Points Sync To WH Logic
//                    print_r($inactiveCpObj->id);
//                    echo "select id, store_id,points_to_upload from it_store_redeem_points where is_sent=0 and active=1 and id=$inactiveCpObj->id;<br>";
                    $serverCh = new clsServerChanges();
                    
                    $obj1 = $db->fetchObject("select id, store_id,points_to_upload from it_store_redeem_points where is_sent=0 and active=1 and id=$inactiveCpObj->id");
                    echo "<br>";
                    print_r($obj1);
                    if (!empty($obj1)) {
                        print_r($obj1);
                        $credit_point = array();
                        $credit_points = array();
                        $item = array();
                        $workorderno = 0;
                            $item['server_id'] = intval($obj1->id);
                            $item['store_id'] = intval($obj1->store_id);
                            $item['points_to_upload'] = intval($obj1->points_to_upload);
                            $credit_point[] = json_encode($item);
                        
                        $credit_points['items'] = json_encode($credit_point);
                        $server_ch = json_encode($credit_points);
                        $CKWHStoreid = DEF_CK_WAREHOUSE_ID;
                        $ser_type = changeType::crditPoints;
                        $serverCh->save($ser_type, $server_ch, $CKWHStoreid, $workorderno);

                        $sql = "update it_store_redeem_points set is_sent=1 where id=$inactiveCpObj->id";
                        $db->execUpdate($sql);
                    }
                    $cnt++;
                }
            }
        }
    }

    $end_date = date('Y-m-d H:i:s');
echo "Execution end.<br> datetime: ".$end_date;
echo '<br>';

} catch (Exception $xcp) {
    print $xcp->getMessage();
}

print "Total Active Credit Point Stores: ".$cnt;