<?php
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
require_once 'lib/core/Constants.php';
//note:-wen running this  first be sure  the below query has been executed
//update it_server_changes set id = id+2 order by id desc;
try{
    $db = new DBConn();
    $idcnt = 1;
    $objs = $db->fetchObjectArray("select * from it_taxes");
//    print_r($objs);
    foreach($objs as $obj){
       $server_ch = $db->safe("[".json_encode($obj)."]");
       $ser_type = changeType::taxes;
       $data_id = $obj->id;
       $query = "insert into it_server_changes set id = $idcnt, type=$ser_type , changedata =  $server_ch , data_id = $data_id ";
//       echo $query;
       $idcnt++;
       $ins=$db->execInsert($query);
    }
       print "inserted id: ".$ins;
}catch(Exception $xcp){
    $xcp->getMessage();
}    
?>
