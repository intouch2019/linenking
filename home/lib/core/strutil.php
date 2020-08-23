<?php

function hyphenate($str) {
$str=trim($str);
$str = preg_replace("[\W]", "-", $str); // convert non-words to hyphens
$str = preg_replace('/-+/', "-", $str); // convert multiple consequtive hyphens to one hyphen
$str = preg_replace('/^-(.*)$/', "$1", $str); // remove the leading hyphen if any
$str = preg_replace('/^(.*)-$/', "$1", $str); // remove the trailing hyphen if any
return $str;
}

function timediff_to_str($diff) {
$m = $diff/60;
$h = $m/60;
$d = $h/24;
if ($d>1) {
$dh = $h%24;
return daystr(intval($d))." and ".hourstr($dh);
} else if ($h>1) {
$hm=$m%60;
return hourstr(intval($h))." and ".minstr($hm);
} else if ($m>1) {
return minstr(intval($m));
} else {
return intval($diff)." seconds";
}
}

function daystr($d) { if ($d > 1) return "$d days"; else return "$d day"; }
function hourstr($h) { if ($h > 1) return "$h hours"; else return "$h hour"; }
function minstr($m) { if ($m > 1) return "$m minutes"; else return "$m minute"; }

function makeLinksClickable($text) {

  $text = eregi_replace('(((f|ht){1}tp://)[-a-zA-Z0-9@:%_\+.~#?&//=]+)',
    '<a href="\\1" target="_blank">\\1</a>', $text);
  $text = eregi_replace('([[:space:]()[{}])(www.[-a-zA-Z0-9@:%_\+.~#?&//=]+)',
    '\\1<a href="http://\\2">\\2</a>', $text);
  $text = eregi_replace('([_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,3})',
    '<a href="mailto:\\1">\\1</a>', $text);

  return $text;
}

function removeExtraSpaces($str) {
$pat[0] = "/^\s+/";
$pat[1] = "/\s{2,}/";
$pat[2] = "/\s+\$/";
$rep[0] = "";
$rep[1] = " ";
$rep[2] = "";
$str = preg_replace($pat,$rep,$str);
return $str;
}

function mmddyy($mysql_timestamp) {
if (!$mysql_timestamp) return "";
$datetime = strtotime($mysql_timestamp);
return date("d/m/Y", $datetime)." ".date("g:ia", $datetime);
}

function ddmmyy2($mysql_timestamp) {
if (!$mysql_timestamp) return "";
$datetime = strtotime($mysql_timestamp);
return date("d/m/Y", $datetime);
}

function ddmmyy($mysql_timestamp) {
if (!$mysql_timestamp) return "";
$datetime = strtotime($mysql_timestamp);
return date("d-m-Y", $datetime);
}

function yymmdd($mysql_timestamp) {
if (!$mysql_timestamp) return "";
$datetime = strtotime($mysql_timestamp);
return date("Y-m-d", $datetime);
}

function startsWith($haystack, $needle) {
    $length = strlen($needle);
    return (substr($haystack, 0, $length) === $needle);
}

function isNumber($var) {
	if (!$var || trim($var) == "") { return false; }
	return ctype_digit(strval($var));
}
