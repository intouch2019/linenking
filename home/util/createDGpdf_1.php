<?php

 
//require_once("../../it_config.php");
require_once("/var/www/html/linenking/it_config.php");
require_once 'lib/users/clsUsers.php';


$date = date('Y-m-d H:i:s');
print_r($date);

$i = 0;
while (true) {

   
//$mydir = '/var/www/html/cottonking/home/DGCreditnote/';//'../DGCreditnote';
    $mydir = '/var/www/html/linenking/home/cnote/'; //'../DGCreditnote';
    $myfiles = scandir($mydir);
    foreach ($myfiles as $file) {
        if (substr($file, -5) == ".html") {

            $cmd = "pisa -s " . $mydir . $file;

            $result = shell_exec($cmd);
            unlink($mydir . $file);
        }
    }
    $i++;
    if ($i == 15) {
        break;
    }
    sleep(4);
}


exit();