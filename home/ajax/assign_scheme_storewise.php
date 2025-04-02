<?php

require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
require_once "lib/orders/clsOrders.php";
require_once "session_check.php";
require_once "lib/logger/clsLogger.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = new DBConn();

    $currentuserid = getCurrUser()->id;
    $currentuserid = isset($currentuserid) ? $currentuserid : 0;
    $schemeId = isset($_POST['schemeId']) ? intval($_POST['schemeId']) : 0;
    $storeIds = isset($_POST['storeIds']) ? explode(',', $_POST['storeIds']) : [];

    if ($currentuserid == 0 || $currentuserid < 0) {
        echo "Error: Invalid Current user id.";
        exit;
    }

    if ($schemeId <= 0 || empty($storeIds)) {
        echo "Error: Invalid scheme or store selection.";
        exit;
    }






    if (count($storeIds) === 1 && $storeIds[0] == "-1") {//All Stores selected
        $schemeoverridearray = array();
        $allstoreidqry = "SELECT id,store_name FROM it_codes WHERE is_closed=0 and id not in (70,147,160,162,168) and usertype=" . UserType::Dealer; //remove teststore,50%store,demostore
        $allstorearrayobj = $db->fetchObjectArray($allstoreidqry);
        foreach ($allstorearrayobj as $storearrayobj) {
            $qry = "select id from storewise_membership_schemes where scheme_id =$schemeId and store_id =$storearrayobj->id and is_data_deleted =0";
            $resqry = $db->fetchObject($qry);

            if ($resqry) {
                $schemeoverridearray[] = $storearrayobj->id;
            }
        }



        if (empty($schemeoverridearray)) {
            foreach ($allstorearrayobj as $storearrayobj) {

                $insertquery = "INSERT INTO storewise_membership_schemes (store_id,scheme_id, update_time, data_inserted_by) 
                        VALUES ($storearrayobj->id,$schemeId, NOW(), $currentuserid)";
                $db->execInsert($insertquery);
            }
            echo "Success: Scheme assigned successfully to All Store!";
        } else {

            $storeIds = implode(", ", $schemeoverridearray);
            echo "$storeIds these storesid's scheme date overlapped, please change the date before Assign scheme to store.";
        }
    } else {
//        print_r("in else");
//        exit();
        $schemeoverridearray = array();
        $storeIdses = implode(", ", $storeIds);
        $allstoreidqry = "SELECT id,store_name FROM it_codes WHERE is_closed=0 and id in ($storeIdses) AND usertype=" . UserType::Dealer;
//        print_r($allstoreidqry);        exit();
        $allstorearrayobj = $db->fetchObjectArray($allstoreidqry);
        foreach ($allstorearrayobj as $storearrayobj) {
            $qry = "select id from storewise_membership_schemes where scheme_id =$schemeId and store_id =$storearrayobj->id and is_data_deleted =0";
           
            $resqry = $db->fetchObject($qry);

            if ($resqry) {
                $schemeoverridearray[] = $storearrayobj->id;
            }
        }

        $cnt = 0;

        if (empty($schemeoverridearray)) {
            foreach ($storeIds as $storeId) {
                $cnt++;
                $insertquery = "INSERT INTO storewise_membership_schemes (store_id,scheme_id, update_time, data_inserted_by) 
                        VALUES ($storeId,$schemeId, NOW(), $currentuserid)";
                $db->execInsert($insertquery);
            }
            echo "Success: Scheme assigned successfully to " . $cnt . " Store!";
        } else {

            $storeIdss = implode(", ", $schemeoverridearray);
            echo "Cannot assign scheme to store as $storeIdss these storesid's scheme overlapped.";
        }
    }
}
?>
