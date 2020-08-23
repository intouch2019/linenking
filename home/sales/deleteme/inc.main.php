<?php
$currStore = getCurrStore();
if (!$currStore) {
	// show main page only if successfully logged in
} else {
require_once "lib/codes/clsCodes.php";

$clsCodes = new clsCodes();
$fanSummary = $clsCodes->getFanSummary($currStore->id);
}
$menuitem="home";
include "storemenu.php";
?>
</div> <!-- div=colOne -->
<div id="colTwo">
	<div class="box">
		<h3>Intouch Store Management Portal</h3>
		<img src="images/shopping-cart.jpg" />
		<p class="bottom">Find out what your customers are putting in that shopping cart<br />
		* Top/Least Selling Items<br />
		* Top/Least Selling Categories<br />
		* Daily Statistics - View Individual Receipts<br />
		* View sales by the hour<br />
		* Setup suppliers, alerts, etc<br />
		</p>
	</div>
</div>
