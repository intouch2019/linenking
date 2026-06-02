<?php
require_once 'sidemenu.php';
require_once "formpost/MatersStockQtyCalc.php";
//require_once "lib/items/clsItems.php";
//
//$clsItems = new clsItems();
//$ctgs = $clsItems->getAllCategories();
//foreach ($ctgs as $ctg) {
//	$ctgarray[$ctg->id] = array($ctg->name, "store/designs/ctg=$ctg->id");
//}
////$ctgarray['29'] = array("Others", "store/designs/others");
//$othctg = $clsItems->getOtherCategory();
////print_r($othctg);
////$other_id = $othctg->id;
////print "id:-".$other_id;
//$ctgarray[$othctg->id] = array("Others", "store/designs/others");
//$menu = array(
//        "Featured Designs" => array(
//		"storefeature" => array("Featured Designs", "store/featured"),
//		),
//	"Catalog" => $ctgarray,
//	"Orders" => array(
//		"viewcart" => array("View Cart", "store/viewcart"),
//		"activeorders" => array("Active", "store/orders/active"),
//		"packingorders" => array("In Picking", "store/orders/packing"),
//                "orderPickCompl" => array("Picking Complete" , "orders/picking/complete"),
//		"shippedorders" => array("Shipped", "store/orders/shipped")
//		),
////        "Feedback" => array(
////                "sfeedback" => array("Send Feedback", "store/feedback"),
////                "sresponse" => array("View Responses", "store/response")
////        ),
//	"Manage Account" => array(
//		"settings" => array("Settings", "user/settings")
//		)
//	);
$storeid = getCurrUserId();
$eligibleStores = getMasterStackEligibleStores();

?>
<div class="grid_2">
<div id="section-menu">
        <ul class="section menu">
<?php foreach ($menu as $menuheading => $submenu) { ?>
            <li>
                <a class="menuitem"><?php echo $menuheading; ?></a>
                <ul class="submenu">
<?php foreach ($submenu as $menukey => $menudetail) {
	if ($menukey == $menuitem) { $selected = 'class="menuselect"'; } else { $selected = ""; }
if($menuheading == "Catalog" && in_array($storeid, $eligibleStores)){ //Initially, only a limited number of stores are eligible to place orders within the stack capacity.
                $permissibleCountFrom = 0;
                $permissibleCountTo = 0;
                $count_stock_from = 0;
                $count_stock_to = 0;
                $parts = explode('ctg=', $menudetail[1]);
                $ctg = isset($parts[1]) ? $parts[1] : null;
                $store_curr_stock = getCurrentStoreStockctgwise($storeid, $ctg);
                $store_master_stock = $db->fetchObject("SELECT sum(min_qty_allowed) as min_qty_allowed FROM stock_master_qty_wise WHERE store_id = $storeid AND category_id = $ctg");
                if(!empty($store_curr_stock) && !empty($store_master_stock)){
                $count_stock_from = $store_master_stock->min_qty_allowed - $store_curr_stock;
                $count_stock_to = ($store_master_stock->min_qty_allowed + round($store_master_stock->min_qty_allowed*0.2)) - $store_curr_stock;
                }
//                if($count_stock_from > 0 && $count_stock_to > 0){ 
                    $permissibleCountFrom = $count_stock_from; 
                    $permissibleCountTo=$count_stock_to; 
//                }
            ?>
                    <li><a <?php echo $selected; ?> href="<?php echo $menudetail[1]; ?>"><?php echo $menudetail[0]."(".$permissibleCountFrom."-".$permissibleCountTo.")"; ?></a></li>
<?php } else { ?>
                    <li><a <?php echo $selected; ?> href="<?php echo $menudetail[1]; ?>"><?php echo $menudetail[0]; ?></a></li>
<?php } ?>
<?php } ?>
                </ul>
            </li>
<?php } ?>
        </ul>
</div>
</div>
