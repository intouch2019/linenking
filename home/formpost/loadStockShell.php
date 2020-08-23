#!/usr/bin/php -q
<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once 'Classes/PHPExcel/IOFactory.php';

$commit=false;
$newfile = $argv[1];

$db=new DBConn();

//$objs = $db->fetchObjectArray("select * from it_categories");
//foreach ($objs as $obj) { $g_ctg_ids[strtoupper($obj->name)] = $obj->id; }
//$objs = $db->fetchObjectArray("select * from it_styles");
//foreach ($objs as $obj) { $g_style_ids[strtoupper($obj->name)] = $obj->id; }
//$objs = $db->fetchObjectArray("select * from it_sizes");
//foreach ($objs as $obj) { $g_size_ids[strtoupper($obj->name)] = $obj->id; }
//$g_size_ids['30']=20;
//$g_size_ids['32']=21;
//$g_size_ids['34']=22;
//$g_size_group["36"]="17,23";
//$g_size_group["36/91"]="17,23";
//$g_size_group["38"]="18,24";
//$g_size_group["38/97"]="18,24";
//$g_size_group["40"]="14,25";
//$g_size_group["40/102"]="14,25";
//$g_size_group["42"]="15,26";
//$g_size_group["42/107"]="15,26";
//$g_size_group["44"]="16,27";
//$g_size_group["44/112"]="16,27";

$objPHPExcel = PHPExcel_IOFactory::load($newfile);
$objWorksheet = $objPHPExcel->getActiveSheet();
$rowno=0;$tot_qty=0;$skip_qty=0;
if ($commit) $db->execUpdate("update it_items set curr_qty = 0, updatetime=now()");
$error="Error:";
foreach ($objWorksheet->getRowIterator() as $row) {
$cellIterator = $row->getCellIterator();
$cellIterator->setIterateOnlyExistingCells(false);
$colno=0;
$item_code=false; $mrp=false; $ctg_name=false; $ctg_id=false; $style_name=false; $style_id=false; $size_name=false; $size_id=false; $design_no=false; $prod_type=false; $curr_qty=false;
foreach ($cellIterator as $cell) {
$value = strval($cell->getValue());
if ($colno == 0 && !preg_match('/^[0-9]/',$value)) { break; }
if ($colno == 0) { $item_code=$value; }
//if ($colno == 1) { $mrp=$value; }
//if ($colno == 2) { $ctg_name=$value; }
//if ($colno == 3) { $style_name=$value; }
//if ($colno == 4) { $size_name=$value; }
//if ($colno == 5) { $design_no=$value; }
//if ($colno == 6) { $prod_type=$value; }
//if ($colno == 21) { $curr_qty=intval($value); }
if ($colno == 1) { $curr_qty=intval($value); }
$colno++;
}
//$ctg_id = isset($g_ctg_ids[strtoupper($ctg_name)]) ? $g_ctg_ids[strtoupper($ctg_name)] : false;
//$style_id = isset($g_style_ids[strtoupper($style_name)]) ? $g_style_ids[strtoupper($style_name)] : false;
//$size_id = isset($g_size_ids[strtoupper($size_name)]) ? $g_size_ids[strtoupper($size_name)] : false;
//if ($colno != 0 && $ctg_id &&  $style_id &&  $size_id) {
//	$design_no = $db->safe($design_no);
	//print "$item_code,$mrp,$ctg_name,$ctg_id,$style_name,$style_id,$size_name,$size_id,$design_no,$prod_type,$curr_qty<br />";
//	$item = $db->fetchObject("select * from it_items where ctg_id=$ctg_id and MRP=$mrp and design_no=$design_no and style_id=$style_id and size_id=$size_id");
	$item = $db->fetchObject("select * from it_items where barcode='$item_code'");
	if ($item) {
		if ($commit) $db->execUpdate("update it_items set curr_qty=$curr_qty, updatetime=now() where id=$item->id");
		$tot_qty += $curr_qty;
        }else{
            $skip_qty++;
        } 
        ////else {
//		$size_group = isset($g_size_group[$size_name]) ? $g_size_group[$size_name] : false;
//		$item = $db->fetchObject("select * from it_items where ctg_id=$ctg_id and MRP=$mrp and design_no=$design_no and style_id=$style_id and size_id in ($size_group)");
//		if ($item) {
//			if ($commit) $db->execUpdate("update it_items set curr_qty=$curr_qty, updatetime=now() where id=$item->id");
//			$tot_qty += $curr_qty;
//		} else {
//			$skip_qty += $curr_qty;
//			$error .= "Item not found:$rowno::$item_code,$mrp,$ctg_name:$ctg_id,$style_name:$style_id,$size_name:$size_id,$design_no,$prod_type,$curr_qty\n<br />";
//		}
//	}
//} else if ($colno != 0) {
//	$skip_qty += $curr_qty;
//	$error .= "Skipping Row No:$rowno::$item_code,$mrp,$ctg_name,$style_name,$size_name,$design_no,$prod_type,$curr_qty\n<br />";
//}
$rowno++;
}
$db->closeConnection();
if ($error == "Error:") {
print "Total Qty=$tot_qty, Skipped Qty=$skip_qty\n<br />";
if ($commit) print "Database Committed\n<br />";
else print "No Database Commit\n<br />";
} else {
print "Error:[$error]\n<br />";
print "Total Qty=$tot_qty, Skipped Qty=$skip_qty\n<br />";
}
