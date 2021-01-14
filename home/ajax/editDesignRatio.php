<?php
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
require_once "lib/orders/clsOrders.php";
require_once ("session_check.php");

extract($_GET);
$db=new DBConn();
$items = array();
$msg="";
foreach ($_GET as $name => $value) {
//    $msg .="name:".$name."=>value:".$value;
   //    if (startsWith($name, "item_")) {
        if (strpos($name, 'item_') !== false){
        $arr = explode("_", $name);
        $items[] = (object) array(
            "style_id" => $arr[1],
            "size_id" => $arr[2],
            "req_qty" => $value
        );
    }
}



$designids = $_GET['designids'];
$category_id = $_GET['category'];
$store_id = $_GET['sid'];
$ratio_type = $_GET['rtype'];
$userid = $_GET['userid'];

//$msg=$store_id.":".$category_id.":".$designids.":".$ratio_type.":".$userid."||";
try{
    
foreach ($items as $item) { 
//    $msg.=$item->style_id.":".$item->size_id.":".$item->req_qty."|";
    
    $query="select id from it_store_ratios where store_id = $store_id and ctg_id = $category_id and "
                                . "ratio_type = $ratio_type and style_id = $item->style_id and size_id = $item->size_id and "
                                . "design_id = $designids";
//                        print $query.";<br/>";
                        $obj = $db->fetchObject($query);
                        if(isset($obj) && !empty($obj)){
                            $query = "update it_store_ratios set ratio=$item->req_qty,updated_by=$userid,updatetime=now() where id = $obj->id";
                            $db->execUpdate($query);
                        }else{
                            $query = "insert into it_store_ratios set store_id=$store_id,ctg_id=$category_id,"
                                    . "style_id=$item->style_id,size_id=$item->size_id,ratio_type=$ratio_type,"
                                    . "ratio=$item->req_qty ,design_id = $designids, updated_by=$userid,createtime=now()";
                            $db->execInsert($query);
                        }
    
    
    
    
    
}


}
 catch (Exception $xcp) {
    echo json_encode(array("error" => "1", "message" => "There was a problem processing your request. Please try again later"));
}


    echo json_encode(array(
            "error" => "0",
            "message" =>"ratio update successfully"
            ));
