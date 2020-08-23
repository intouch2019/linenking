<?php
$currStore = getCurrStore();
if (!$currStore) {
	// show main page only if successfully logged in
} else {
$menuitem="daily";
include "storemenu.php";
?>
</div> <!-- div=colOne -->
<?php
require_once "lib/codes/clsCodes.php";

$clsCodes = new clsCodes();
$fanSummary = $clsCodes->getFanSummary($currStore->id);
}
?>
<div id="colTwo">
	<div class="box">
		<h3>Daily Statistics</h3>
	
		<p class="bottom"><strong>Welcome <?php echo $currStore->store_name ?>. You have <?= $fanSummary->activeFans ?> fans.</strong>. Morbi sit amet magna ac lacus dapibus interdum. Donec nec risus vel sem dignissim tristique. Sed neque. Fusce in elit. Quisque condimentum, ante id convallis convallis, sapien est dignissim elit, vel pulvinar augue eros quis metus. Suspendisse pede nisl, gravida iaculis, auctor vitae, bibendum sit amet, mauris. Cras adipiscing libero et risus. Donec rutrum tempus massa. Proin at mauris sed elit venenatis porttitor. Morbi quam nisl, fringilla quis, sagittis nec, adipiscing at, elit. Maecenas sed sem sit amet lectus mattis molestie. Integer quis eros.</p>
	</div>
	<div class="box">
		<h3>Maecenas lectus mattis</h3>
		<h4><strong>12.01.2006</strong> | <a href="#">16 Comments</a></h4>
		<p class="bottom">Morbi sit amet magna ac lacus dapibus interdum. Donec nec risus vel sem dignissim tristique. Sed neque. Fusce in elit. Quisque condimentum, ante id convallis convallis, sagittis nec, adipiscing at, elit. Maecenas sed sem sit amet lectus mattis molestie. Integer quis eros sapien est dignissim elit, vel pulvinar augue eros quis metus. Suspendisse pede nisl, gravida iaculis, auctor vitae, bibendum sit amet, mauris. Cras adipiscing libero et risus. Donec rutrum tempus massa. Proin at mauris sed elit venenatis porttitor. Morbi quam nisl, fringilla quis, sagittis nec, adipiscing at, elit.</p>
	</div>
</div>
