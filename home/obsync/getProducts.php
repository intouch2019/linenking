<?php
include "checkAccess.php";
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";

extract($_POST);

if (!isset($lastid) || trim($lastid) == "") {
	print "1::Missing parameter";
	return;
}

$db = new DBConn();
$items = $db->fetchObjectArray("select i.id, i.barcode, m.name as mfg_by, c.name as category, i.design_no, i.MRP, b.name as brand, st.name as style, si.name as size, p.name as prod_type, mt.name as material, f.name as fabric_type from it_items i left outer join it_mfg_by m on i.mfg_id = m.id left outer join it_categories c on i.ctg_id = c.id left outer join it_brands b on i.brand_id = b.id left outer join it_styles st on i.style_id = st.id left outer join it_sizes si on i.size_id = si.id left outer join it_prod_types p on i.prod_type_id = p.id left outer join it_materials mt on i.material_id = mt.id left outer join it_fabric_types f on i.fabric_type_id = f.id where i.id > $lastid order by i.id limit 100");
$json_objs = array();
foreach ($items as $item) {
$json_objs[]=array(
$item->id,
$item->barcode,
$item->mfg_by,
$item->category,
$item->design_no,
$item->MRP,
$item->brand,
$item->style,
$item->size,
$item->prod_type,
$item->material,
$item->fabric_type
);
}
$db->closeConnection();
$json_str=json_encode($json_objs);
print "0::$json_str";
