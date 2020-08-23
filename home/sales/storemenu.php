<?php
$selected = array (
// Dashboard
	"home" => "",
// Revenue
	"soverview" => "",
	"daily" => "",
	"weekly" => "",
	"monthly" => "",
// Products
	"poverview" => "",
	"pcategories" => "",
	"pctgmanage" => "",
// Customers
	"coverview" => "",
// Manage Account
	"settings" => "",
// Mall
	"moverview" => "",
// Inventory
	"itemmaster" => "",
        "newshipment" => "",
        "suppliers" => "",
        "purchaseorders" => ""
);

if ($menuitem) {
	$selected[$menuitem]='class = "selected"';
}
$currStore = getCurrStore();
if ($currStore && $currStore->id == 20) {
?>
	<div class="menubox">
		<h3>Dashboard</h3>
		<ul class="menubottom">
			<li class="first"><a <?php echo $selected["moverview"]; ?> href="mall/overview">Overview</a></li>
		</ul>
	</div>
	<div class="menubox">
		<h3>Promtions</h3>
		<ul class="menubottom">
			<li class="first"><a <?php echo $selected["xoverview"]; ?> href="mall/overview">Manage Promotions</a></li>
		</ul>
	</div>
<?php } else { ?>
	<div class="menubox">
		<h3>Dashboard</h3>
		<ul class="menubottom">
			<li class="first"><a <?php echo $selected["home"]; ?> href="dashboard">Home</a></li>
		</ul>
	</div>
	<div class="menubox">
		<h3>Sales</h3>
		<ul class="menubottom">
			<li class="first"><a <?php echo $selected["soverview"]; ?> href="stats/overview">Overview</a></li>
			<li><a <?php echo $selected["daily"]; ?> href="stats/daily">Daily Stats</a></li>
		</ul>
	</div>
	<div class="menubox">
		<h3>Products</h3>
		<ul class="menubottom">
			<li class="first"><a <?php echo $selected["poverview"]; ?> href="products/overview">Overview</a></li>
			<li><a <?php echo $selected["pcategories"]; ?> href="products/categories">Categories</a></li>
			<li><a <?php echo $selected["pctgmanage"]; ?> href="products/categories/manage">Manage Categories</a></li>
		</ul>
	</div>
<?php if ($currStore && $currStore->id == 10) { ?>
	<div class="menubox">
		<h3>Shoppers</h3>
		<ul class="menubottom">
			<li class="first"><a <?php echo $selected["coverview"]; ?> href="shoppers/overview">Overview</a></li>
			<li class="first"><a <?php echo $selected["clist"]; ?> href="shoppers/list">Shopper's List</a></li>
		</ul>
	</div>
<?php } ?>
<?php } ?>
	<div class="menubox">
		<h3>Inventory</h3>
		<ul class="menubottom">
                        <li class="first"><a <?php echo $selected["itemmaster"]; ?> href="inventory/itemmaster">Item Master</a></li>
                        <li><a <?php echo $selected["newshipment"]; ?> href="inventory/newshipment">Shipments</a></li>
                        <li><a <?php echo $selected["suppliers"]; ?> href="inventory/suppliers">Suppliers</a></li>
                        <li><a <?php echo $selected["purchaseorders"]; ?> href="inventory/purchaseorders">Purchase Orders</a></li>
                </ul>
	</div>
	<div class="menubox">
		<h3>Manage Account</h3>
		<ul class="menubottom">
			<li class="first"><a <?php echo $selected["settings"]; ?>? href="settings">Settings</a></li>
			<li><a href="logout.php">Logout</a></li>
		</ul>
	</div>
