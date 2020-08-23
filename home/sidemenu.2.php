<?php
require_once 'sidemenu.php';
//$menu = array(
//	"Orders" => array(
//		"activeorders" => array("Active", "dispatch/orders/active"),
//                "autorefill" => array("Auto Refill Orders","admin/autorefill"),
//		"packingorders" => array("In Picking", "dispatch/orders/packing"),
//                "orderPickCompl" => array("Picking Complete" , "orders/picking/complete"),
//		"shippedorders" => array("Shipped", "orders/shipped"),
//                "cancelledorders" => array("Cancelled", "orders/cancelled")
//	),
//        "Extra Shippment" => array (
//                "addship" => array("Create Shipment","dispatch/addship")
//        ),
//        "Store Discounts" => array(
//                "discount" => array("Discounts", "admin/discounts")
//        ),
//	"Designs" => array(
////		"designlist" => array("Design List", "designs/list"),
//		"designsearch" => array("Design Search", "designs/search"),
//                "designline" => array("Set Design Line+Rack","admin/designline")
//		),
//	"Manage Account" => array(
//		"settings" => array("Settings", "user/settings")
//		)
//	);
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
?>
                    <li><a <?php echo $selected; ?> href="<?php echo $menudetail[1]; ?>"><?php echo $menudetail[0]; ?></a></li>
<?php } ?>
                </ul>
            </li>
<?php } ?>
        </ul>
    </div>
</div>
