<?php

require_once("../../it_config.php");
require_once "lib/db/DBConn.php";

$db = new DBConn();
 $query =  "select id,CheckSum,MsgId,Status,Error_code,Error_message,RefID,Filler1,Filler2,Filler3,Filler4,Filler5,Filler6,Filler7,Filler8,Filler9,Filler10,createtime,updatetime from emandate_response order by id desc limit 25";

    $result = $db->fetchObjectArray($query);
    
   echo "<table border='1' align='center'><tr><th>ID</th><th>CheckSum</th><th>MsgId</th><th>Status</th><th>Error_code</th><th>Error_message</th><th>RefID</th><th>Filler1</th><th>Filler2</th><th>Filler3</th><th>Filler4</th><th>Filler5</th><th>Filler6</th><th>Filler7</th><th>Filler8</th><th>Filler9</th><th>Filler10</th><th>createtime</th><th>updatetime</th></tr>"; 
    foreach($result as $obj){
        echo "<tr><td>".$obj->id."</td><td>".$obj->CheckSum."</td><td>".$obj->MsgId."</td><td>".$obj->Status."</td><td>".$obj->Error_code."</td><td>".$obj->Error_message."</td><td>".$obj->RefID."</td><td>".$obj->Filler1."</td><td>".$obj->Filler2."</td><td>".$obj->Filler3."</td><td>".$obj->Filler4."</td><td>".$obj->Filler5."</td><td>".$obj->Filler6."</td><td>".$obj->Filler7."</td><td>".$obj->Filler8."</td><td>".$obj->Filler9."</td><td>".$obj->Filler10."</td><td>".$obj->createtime."</td><td>".$obj->updatetime."</td></tr>";
        
    }
    echo '</table>';