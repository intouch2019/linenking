<?php
session_start();
$sessionStarted=true;
$gCurrUser = null;
if (isset($_SESSION['currUser'])) { $gCurrUser = $_SESSION['currUser']; }
function getCurrUserId() {
    global $gCurrUser;
    if ($gCurrUser) { return $gCurrUser->id; }
    else return -1;
}
function getCurrUser() {
    global $gCurrUser;
    return $gCurrUser ;
}
?>
