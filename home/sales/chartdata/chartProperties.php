<?php

$prop_daynames = array(
	"0" => "Monday",
	"1" => "Tuesday",
	"2" => "Wednesday",
	"3" => "Thursday",
	"4" => "Friday",
	"5" => "Saturday",
	"6" => "Sunday"
);

$prop_colors = array(
"#3465a4",
"#cc0000",
"#edd400",
"#fc7900",
"#c17d11",
"#73d216",
"#75507b",
"#c4a000",
"#ce5c00",
"#8f5902",
"#4e9a06",
"#204a87",
"#5c3566",
"#a40000"
);

function prop_getcolor($idx) {
global $prop_colors;
$idx = $idx % count($prop_colors);
return $prop_colors[$idx];
}

function create_y_axis($ymin, $ymax) {
$y = new y_axis();

$diff = $ymax - $ymin;
$numsteps = 10;
$perstep = intval($diff/$numsteps);
$len = strlen(""+$perstep);
$divideby = pow(10,($len-1));
$stepsize = intval($perstep/$divideby)*$divideby;
if ($stepsize == 0) {
	$y1 = $ymin-1;
	$y2 = $ymax+1;
	$stepsize=1;
} else {
	$y1 = floor($ymin/$stepsize) * $stepsize;
	$y2 = ceil($ymax/$stepsize) * $stepsize;
}

$y->set_range( $y1, $y2, $stepsize);
return $y;
/*

21, 1320
diff = 1320 - 21 = 1299
numsteps = 10
per step = 1299/10 = 129.9
round = 130
closer to 100 or 200 = 100
therefore, steps of 100

21 - divide by 100 = 0.2
floor() = 0
multiply by 100 = 0
1320 divide by 100 = 13.2
ceiling = 14
multiply by 100 = 1400
Therefore 0 to 1400 in steps of 100
*/
}

?>
