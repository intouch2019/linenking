<?php
require_once 'sidemenu.php';
//$menu = array(
////	"Barcodes" => array(
////		"bnewbatch" => array("New Batch", "barcode/newbatch"),
////		"bbatches" => array("Batches", "barcode/batches"),
////		"battributes" => array("Attributes", "barcode/attributes"),
////		"bsearch" => array("Search", "barcode/search"),
////	),
////        "Invoices" => array(
////		"ckinvoices" => array("CK Invoices", "ck/invoices"),
////		"spinvoices" => array("SP Invoices", "sp/invoices"),
////	),
//        "Designs" => array(
//		"adddesign" => array("Add Design Image", "admin/adddesign"),
////		"designlist" => array("Design List", "designs/list"),
//		"designsearch" => array("Design Search", "designs/search"),
//                "designline" => array("Set Design Line+Rack","admin/designline"),
//                //"designanalysis" => array("Design Analysis", "designs/analysis")
//	),
//        "Store Feature" => array(
//                "featureddesigns" => array("Add Featured Designs", "admin/featureddesign"),
//                "featurepreview" => array("Feature Preview", "admin/previewfeatured")
//	),
//	"Orders" => array(
//		"activeorders" => array("Active", "admin/orders/active"),
//		"packingorders" => array("In Picking", "admin/orders/packing"),
//                //"orderPickCompl" => array("Picking Complete" , "orders/picking/complete"),
//		"shippedorders" => array("Shipped", "orders/shipped")
//	),
////	"Stock" => array(
////		  "loadstock" => array("Upload Stock", "admin/stock/upload"),
////                "currstock" => array("Current Stock", "admin/stock/current")
////	),
//        "Reports" => array(
//                "storesales" => array("Store Sales","report/storesales"),
//                "storecurrStock" => array("Store Current Stock","report/store/currStock"),
//                "aidesigns" => array("Active/Inactive Designs", "report/designs"),
//                "reportaccounts" => array("Accounts Report", "report/accounts"),
//                "reportordersize" => array("Order Size Report", "report/ordersize"),
//                "reportdispatcher" => array("Dispatcher Report", "report/dispatcher"),
//                "reportpicker" => array("Picker Report", "report/picker")
//        ),
////        "Graphical Reports" => array(
////                "ordergraph" => array("Order Analysis","admin/graph/orders"),
////                "dayordergraph" => array("Day-wise Order Analysis","admin/graph/dayorders"),
////                "storegraph" => array("Store Analysis","admin/graph/stores"),
////                "productgraph" => array("Product Analysis","admin/graph/products")
////                //"categorygraph" => array("Category Analysis", "admin/graph/categories")
////        ),
//	"Manage" => array(
////		"users" => array("Users", "admin/users"),
//                "stores" => array ("Stores", "admin/stores")
//	),
//	"Manage Account" => array(
//		"settings" => array("Settings", "user/settings")
//	)
//);
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
