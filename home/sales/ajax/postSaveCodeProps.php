<?php
include "../appConfig.php";
require_once "session_check.php";
require_once "lib/codes/clsCodes.php";
require_once "lib/codes/CodeProps.php";

$currStore = getCurrStore();
if (!$currStore) { print "error||Unauthorized Access !!!"; return; }
extract($_POST);
$aslabs = ${CodeProps::AmountSlabs};
$hodslabs = ${CodeProps::HourOfDaySlabs};
if (!$aslabs) { print "error||Please specify the Amount Slabs"; return; }
// validate aslabs
//$aslabs=$argv[1];
$arr = explode(",",$aslabs);
if (count($arr) == 0) { print "error||Please specify the Amount Slabs"; return; }
$prev = -1;
foreach($arr as $slab) {
	$slab=trim($slab);
	if (!preg_match('/\d+/', $slab)) { print "error||Amount slab [$slab] is not valid. Only numbers allowed"; return; }
	$curr = intval($slab);
	if ($curr <= $prev) { print "error||Amount slab [$slab] is not valid. Numbers should be in increasing order"; return; }
	$prev = $curr;
}

//$hodslabs="";
if (!$hodslabs) { print "error||Please specify the Hour of Day Slabs"; return; }
// validate hodslabs
$arr = explode(",",$hodslabs);
if (count($arr) == 0) { print "error||Please specify the Hour of Day Slabs"; return; }
$prev = -1;
foreach($arr as $slab) {
	$slab=trim($slab);
	if (!preg_match('/\d+/', $slab)) { print "error||Hour of Day slab [$slab] is not valid. Only numbers allowed"; return; }
	$curr = intval($slab);
	if ($curr <= $prev) { print "error||Hour of Day slab [$slab] is not valid. Numbers should be in increasing order"; return; }
	$prev = $curr;
}
$props = array();
$props[CodeProps::AmountSlabs] = $aslabs;
$props[CodeProps::HourOfDaySlabs] = $hodslabs;
$clsCodes = new clsCodes();
$clsCodes->saveCodeProps($currStore->id, $props);
print "success||Update successful.";
?>
