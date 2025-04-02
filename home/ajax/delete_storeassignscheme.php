<?php
include "../../it_config.php";
require_once "session_check.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";

header('Content-Type: application/json'); // Ensure JSON response

extract($_POST);

$currStore = getCurrUser();
if (!isset($currStore) || !isset($currStore->id)) {
    echo json_encode(["status" => "error", "message" => "Current login details not found."]);
    exit();
}

$db = new DBConn();

if (empty($_POST['storeassignschemeid']) || $_POST['storeassignschemeid'] == 0) {
    echo json_encode(["status" => "error", "message" => "Invalid Action"]);
    exit();
}

$currentloginuser = $currStore->id;
$storeassignschemeid = (int) $_POST['storeassignschemeid']; // Ensure integer

try {
    $selectqry="select is_data_deleted from storewise_membership_schemes where id=$storeassignschemeid";
    $result=$db->fetchObject($selectqry);
    if($result->is_data_deleted==1){
        echo json_encode(["status" => "error", "message" => "Scheme Already Deleted"]);
    }else{
      $deleteassignschemeqry = "update storewise_membership_schemes set is_data_deleted=1,data_deleted_by=$currentloginuser,data_delete_time=now() where id=$storeassignschemeid";
    $rowseffect=$db->execUpdate($deleteassignschemeqry);

    // Check if rows were affected
    if ($rowseffect > 0) {
        echo json_encode(["status" => "success", "message" => "Scheme deleted successfully."]);
    } else {
        echo json_encode(["status" => "error", "message" => "No matching record found."]);
    }  
    }
     

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
}
?>
