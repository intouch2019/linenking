<?php
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
require_once "lib/orders/clsOrders.php";
require_once("session_check.php");

$db = new DBConn();
$design_no = isset($_GET['design_no']) ? $db->safe($_GET['design_no']) : false;
$mrp = isset($_GET['mrp']) ? intval($_GET['mrp']) : false;

$store_id = getCurrUserId();
$clsOrders = new clsOrders();
$cart = $clsOrders->getCart($store_id);

$itemidsarray = "";
foreach ($_GET as $name => $value) {
    if (startsWith($name, "item_")) {
        $arr = explode("_", $name);
        if ($arr[1]!="0") {
            $itemidsarray .= "'$arr[1]',";
        }
    }
}
$itemidsarray =  substr($itemidsarray, 0, -1);

if ($cart) {
        $query = "delete from it_ck_orderitems where order_id=$cart->id and store_id=$store_id and design_no=$design_no and MRP=$mrp and item_id in ($itemidsarray)";
        //return error($query);
        $db->execQuery($query);
	// update it_ck_orders
	$clsOrders->updateCartTotals($cart->id);
}

//$cartinfo="<h3>Total Items: ".$countdesigns." | Qty: ".$totqt." | Price: ".$totprice."<h3>";
$cartinfo = $clsOrders->getCartInfo($store_id);
$cartinfoStr = $clsOrders->printCartInfo($cartinfo);
success ($cartinfoStr);

function error($msg) {
    echo json_encode(array(
            "error" => "1",
            "message" => $msg
            ));
}

function success($msg) {
    echo json_encode(array(
            "error" => "0",
            "cartinfo" => $msg
            ));
}

function startsWith($haystack, $needle)
{
    $length = strlen($needle);
    return (substr($haystack, 0, $length) === $needle);
}
?>
