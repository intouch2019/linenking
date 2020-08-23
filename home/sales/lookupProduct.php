<?php
include "../../it_config.php";
include "session_check.php";
require_once "lib/catalog/clsCatalog.php";

$clsCatalog = new clsCatalog();

$codeid = $gCurrStore->id;
$letters = $_GET['letters'];
$letters = preg_replace("/[^a-z0-9 ]/si","",$letters);
if (strlen($letters) < 3) {
	echo "1###Enter first 3 letters|";
	return; 
}

$arr = $clsCatalog->lookupProducts($codeid, $letters);
if (!$arr || count($arr) == 0) {
	echo "N###No matching products|";
	return;
}
foreach($arr as $product) {
echo $product->id."###".$product->product_name."|";
}

?>
