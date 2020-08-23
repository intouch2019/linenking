<?php
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
require_once("session_check.php");

$ctg_id = isset($_GET['cat_id']) ? ($_GET['cat_id']) : false;
$arr = array();
try {
    $db = new DBConn();

    $exist = $db->fetchObjectArray("select * from it_ck_designs where ctg_id=$ctg_id  order by design_no"); //and active=1
    if ($exist) {
        foreach ($exist as $obj) {
            array_push($arr, $obj->id."<>".$obj->design_no);
        }
        echo json_encode(array("error" => "0", "message" => $arr));
    }else{
         echo json_encode(array("error" => "1", "message" => "No designs for this category"));
    }
} catch (Exception $xcp) {
    echo json_encode(array("error" => "1", "message" => "error:There was a problem processing your request. Please try again later."));
}
?>
