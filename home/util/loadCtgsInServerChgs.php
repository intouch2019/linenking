<?php
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
require_once 'lib/core/Constants.php';
//note:-wen running this  first be sure below queries are executed before
//update it_categories set vat_id = 1 where id not in (13,24);
//update it_categories set vat_id = 2 where id in (13,24);

try{
    $db = new DBConn();
    $db->execQuery("delete from it_server_changes where type = 1");
    $cnt = 0;
    $idcnt = 3; // as curr server chg id fr ctg starts frm 3.
    //exclude 'Long kurta' ctg as its server ch id is diff
    $objs = $db->fetchObjectArray("select * from it_categories where id != 30");
//    print_r($objs);
    foreach($objs as $obj){
       $server_ch = $db->safe("[".json_encode($obj)."]");
       $ser_type = changeType::categories;
       $data_id = $obj->id;
       $query = "insert into it_server_changes set id = $idcnt, type=$ser_type , changedata =  $server_ch , data_id = $data_id ";
//       echo "<br/>".$query."<br/>";
       $idcnt++;
       $ins=$db->execInsert($query);
       if($ins){ $cnt++;}
    }
    
    // insert seperately for long kurta ctg
    $obj = $db->fetchObject("select * from it_categories where id = 30");
    $idcnt = 31290;  //current server chg id for long kurta
    $server_ch = $db->safe("[".json_encode($obj)."]");
    $ser_type = changeType::categories;
    $data_id = $obj->id;
    $query = "insert into it_server_changes set id = $idcnt, type=$ser_type , changedata =  $server_ch , data_id = $data_id ";
//    echo "<br/>".$query."<br/>";
   
    $ins=$db->execInsert($query);
    if($ins){ $cnt++;}
    
    print "inserted nos: ".$cnt;
}catch(Exception $xcp){
    $xcp->getMessage();
}    
?>
