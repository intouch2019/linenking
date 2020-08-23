<?php
require_once "UriHandler.class.php";

//$urih = new UriHandler("store/checkin?alreadycheckedin=1");
$urih = new UriHandler("");
print $urih->getIncludeFile();

?>
