<?php
$currStore = getCurrStore();
if ($currStore) {
require_once "inc.main.php";
} else {
require_once "inc.storehome.php";
}
?>
