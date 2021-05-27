<?php
require_once "../../it_config.php";
require_once("session_check.php");
require_once "lib/db/DBConn.php";

//print_r ($tdata);
header('Content-type: text/csv');
header('Content-disposition: attachment;filename='.$_GET['output']);

print file_get_contents("DgCreditNote.csv");
