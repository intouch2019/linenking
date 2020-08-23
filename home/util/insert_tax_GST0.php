<?php

require_once("../../it_config.php");
require_once "../lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/serverChanges/clsServerChanges.php";

try {
    $db = new DBConn();
    $serverCh = new clsServerChanges();

    $query = "select * from it_mrp_taxes where tax_name = 'GST_0%'";
    $obj_chk = $db->fetchObject($query);

    if (isset($obj_chk)) {
        echo " GST_0% TAX is Already Added !! ";
    } else {
        $query = "insert into it_mrp_taxes set mrp = 00 , tax_name = 'GST_0%' , tax_percent = 0 , tax_rate = 0.0 , validfrom = '2017-07-01 00:00:00' , createtime = now() ";
        $inserted_id = $db->execInsert($query);


        $query = "select * from it_mrp_taxes where id = $inserted_id ";
        $obj = $db->fetchObject($query);
        if (isset($obj) && !empty($obj) && $obj != null) {
            $server = json_encode($obj);
            $server_ch = "[" . $server . "]"; // converting n storing in obj format so that easy retrival at pos side                    );
            $ser_type = changeType::mrptaxes;
            //$serverCh->save($ser_type, $server_ch,$store_id,$obj->id);  
            $serverCh->insert($ser_type, $server_ch, $obj->id);
        }
        echo "Tax 0% added successfully !!";
    }
} catch (Exception $xcp) {
    
}

