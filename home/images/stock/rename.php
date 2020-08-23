<?php

$dh = @opendir( "." );
while( false !== ( $file = readdir( $dh ) ) ) {
	if ($file == ".") { continue; }
	if ($file == "..") { continue; }
	$fname = pathinfo($file, PATHINFO_FILENAME);
	$ext = pathinfo($file, PATHINFO_EXTENSION);
	if ($ext == "php") { continue; }
	$ext = strtolower($ext);
	$newfile="$fname.$ext";
	if ($file == $newfile) { continue; }
	print "$file::$newfile\n";
	rename($file,$newfile);
}
closedir($dh);
