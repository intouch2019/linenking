<?php
require_once("../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";

$user = getCurrUser();
if (!$user || ($user->usertype != UserType::Admin && $user->usertype != UserType::CKAdmin && $user->usertype != UserType::Accounts && $user->id!=100 && $user->roles != RollType::IT && $user->roles != RollType::Sales )) { print 'You have to be logged in to run this program. Login <a href="home/login">here</a>'; return; }

$fp=fopen("items.csv","w");
if (!$fp) { print 'Unable to open file items.csv'; exit; }
$db = new DBConn();
//$items = $db->fetchObjectArray("select i.batch_id,i.id, i.barcode, i.num_units, m.name as mfg_by, c.name as category, i.design_no, i.MRP, b.name as brand, st.name as style, si.name as size, p.name as prod_type, mt.name as material, f.name as fabric_type from it_items i left outer join it_mfg_by m on i.mfg_id = m.id left outer join it_categories c on i.ctg_id = c.id left outer join it_brands b on i.brand_id = b.id left outer join it_styles st on i.style_id = st.id left outer join it_sizes si on i.size_id = si.id left outer join it_prod_types p on i.prod_type_id = p.id left outer join it_materials mt on i.material_id = mt.id left outer join it_fabric_types f on i.fabric_type_id = f.id order by i.id");
$items = $db->execQuery("select i.batch_id,i.id, i.barcode, i.num_units, m.name as mfg_by, c.name as category, i.design_no, i.MRP, b.name as brand, st.name as style, si.name as size, p.name as prod_type, mt.name as material, f.name as fabric_type from it_items i left outer join it_mfg_by m on i.mfg_id = m.id left outer join it_categories c on i.ctg_id = c.id left outer join it_brands b on i.brand_id = b.id left outer join it_styles st on i.style_id = st.id left outer join it_sizes si on i.size_id = si.id left outer join it_prod_types p on i.prod_type_id = p.id left outer join it_materials mt on i.material_id = mt.id left outer join it_fabric_types f on i.fabric_type_id = f.id order by i.id");
if ($db->getConnection()->error) { throw new Exception($db->getConnection()->error); }
if (!$items) { print "no results\n"; $db->closeConnection(); return; }
fputs($fp,"Batch Id,Barcode,Manufacturer,Product,Design,MRP,Brand,Style,Size,Production Type,Material,Fabric Type,Units\n");
//foreach ($items as $item) {
while($item = $items->fetch_object()){
 fputs($fp,"$item->batch_id,$item->barcode,$item->mfg_by,$item->category,$item->design_no,$item->MRP,$item->brand,$item->style,$item->size,$item->prod_type,$item->material,$item->fabric_type,$item->num_units\n");
}
$db->closeConnection();
fclose($fp);
?>
<a href="items.csv">Download Items</a>