<?php
//include '../it_config.php';
include '/var/www/html/linenking/it_config.php';
require_once "lib/db/DBConn.php";

  echo '<br><br>Before<br>';
$db = new DBConn();

$queries = [
    ["table" => "creditnote_no", "query" => "SELECT cn_no AS val FROM creditnote_no"],
    ["table" => "it_debitnote_num", "query" => "SELECT dbnum AS val FROM it_debitnote_num"]
];

foreach ($queries as $q) {

    $result = $db->fetchObjectArray($q['query']);

    foreach ($result as $row) {
        echo $q['table'] . " : " . $row->val . "<br>";
    }

    echo "----------------<br>";
}



$query1 = "update creditnote_no set cn_no='262700001'";
  $db->execQuery($query1);
$query2 = "update it_debitnote_num set dbnum='262700001', updatetime=now()";
  $db->execQuery($query2);

  
  echo '<br><br>After<br>';
  
  
$queries = [
    ["table" => "creditnote_no", "query" => "SELECT cn_no AS val FROM creditnote_no"],
    ["table" => "it_debitnote_num", "query" => "SELECT dbnum AS val FROM it_debitnote_num"]
];

foreach ($queries as $q) {

    $result = $db->fetchObjectArray($q['query']);

    foreach ($result as $row) {
        echo $q['table'] . " : " . $row->val . "<br>";
    }

    echo "----------------<br>";
}
  
  

$db->closeConnection();
