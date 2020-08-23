<?php
session_start();
$sessionStarted=true;
$gCurrStore = null;
if (isset($_SESSION['currStore'])) { $gCurrStore = $_SESSION['currStore']; }
function getCurrStoreId() {
global $gCurrStore;
if ($gCurrStore) { return $gCurrStore->id; }
else return -1;
}
function getCurrStore() {
global $gCurrStore;
return $gCurrStore ;
}
?>
