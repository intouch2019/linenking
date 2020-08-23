<?php

include "../appConfig.php";
include "ofc/php-ofc-library/open-flash-chart.php";

// generate some random data
srand((double)microtime()*1000000);

$max = 20;
$tmp = array();
for( $i=0; $i<9; $i++ )
{
  $tmp[] = rand(0,$max);
}

$title = new title( date("D M d, Y") );

$bar = new bar();
$bar->set_values( $tmp );

$chart = new open_flash_chart();
$chart->set_title( $title );
$chart->add_element( $bar );

$y = new y_axis();
// grid steps:
$y->set_range( 0, 30, 5);
$chart->set_y_axis( $y );
                    
echo $chart->toString();

?>
