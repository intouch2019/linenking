<?php

require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
require_once "lib/orders/clsOrders.php";
require_once("session_check.php");
require_once "lib/logger/clsLogger.php";
$db = new DBConn();

$values = $_GET["values"];
$valuearray = explode("/", $values);
//print_r($valuearray);
//exit();
$designNo = $db->safe($valuearray[0]);
$size = $valuearray[1];
$style = $db->safe($valuearray[2]);
//print_r($designNo);
//exit();
$barcodeMRP = $db->fetchObject("select i.barcode, i.MRP from it_items i left outer join it_mfg_by m on i.mfg_id = m.id"
        . " left outer join it_categories c on i.ctg_id = c.id"
        . " left outer join it_brands b on i.brand_id = b.id"
        . " left outer join it_styles st on i.style_id = st.id"
        . " left outer join it_sizes si on i.size_id = si.id"
        . " left outer join it_prod_types p on i.prod_type_id = p.id"
        . " left outer join it_materials mt on i.material_id = mt.id"
        . " left outer join it_fabric_types f on i.fabric_type_id = f.id where"
        . " i.design_no =$designNo and si.name=$size and st.name=$style order by i.batch_id desc limit 1");
//print_r($barcodeMRP);
//exit();
if (isset($barcodeMRP)) {
    echo $barcodeMRP->barcode . $barcodeMRP->MRP;
} else {
    echo "Not Found";
}