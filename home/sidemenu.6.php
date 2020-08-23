<?php
require_once 'sidemenu.php';
//global $g_categories;
//$ctgarray = array();
//foreach ($g_categories as $ctg_code => $ctg_name) {
//	$ctgarray[$ctg_code] = array($ctg_name, "store/designs/ctg=$ctg_code");
//}
//$ctgarray['oth'] = array("Others", "store/designs/others");
//$menu = array(
//	"Orders" => array(
//		"activeorders" => array("Active", "store/orders/active"),
//		"packingorders" => array("In Picking", "store/orders/packing"),
//		"shippedorders" => array("Shipped", "store/orders/shipped")
//		),
//        "Accounts" => array(
//                "reportaccounts" => array("Accounts Report", "report/accounts"),
//        ),
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
