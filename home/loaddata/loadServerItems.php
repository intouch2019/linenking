<?php
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/serverChanges/clsServerChanges.php";

$flg=1;
$countitem=0;
$countupdated=0;
$errors = array();
$scuccess = "";
$db = new DBConn();
$serverCh = new clsServerChanges();
//$fh = fopen($argv[1],"r");
$count=0;

try{
    $allitemquery = "select i.id,i.batch_id,i.barcode,i.mfg_id,i.ctg_id,d.id as design_id,i.MRP,i.brand_id,i.style_id,i.size_id,i.prod_type_id,i.material_id,i.fabric_type_id from it_items i , it_ck_designs d where i.design_no = d.design_no  and  i.ctg_id = d.ctg_id group by i.id ";
    $objs = $db->fetchObjectArray($allitemquery);
    foreach($objs as $obj){
        $count++;
        print "\n Inserted:-".$count;
        $server_ch = "[".json_encode($obj)."]";
        $ser_type = changeType::items;
        $serverCh->insert($ser_type, $server_ch);
    }
}catch(Exception $xcp){
    print $xcp;
}
print "0::success";

?>
