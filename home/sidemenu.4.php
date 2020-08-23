<?php
require_once 'sidemenu.php';
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
