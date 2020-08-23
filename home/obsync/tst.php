<?php
$store_name=$argv[1];
$store_name=time();
$code=strtolower(preg_replace("/[^A-Za-z0-9]/", '', $store_name));
print "[$store_name=$code]\n";

