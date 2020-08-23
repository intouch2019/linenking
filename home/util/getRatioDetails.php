<?php
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";

$db = new DBConn();

$store_id = 96;

$query = "select * from it_store_ratios where store_id = $store_id";

$obj_store_ratio = $db->fetchObjectArray($query);

$output = "";
$output ="Category,Design No,Style,Size,Ratio Type,Ratio<br/>";
foreach ($obj_store_ratio as $obj){
    
    $query = "select * from it_categories where id = $obj->ctg_id";
    $obj_cat = $db->fetchObject($query);
    $category = "";
    if(isset($obj_cat)){
        $category = $obj_cat->name;
    }
    
    $design_no = "";
    $obj_ck_design = null;
    if($obj->design_id > 0){
        $query = "select * from it_ck_designs where id = $obj->design_id";
        $obj_ck_design = $db->fetchObject($query);
    }
    
    if(isset($obj_ck_design)){
        $design_no = $obj_ck_design->design_no;
    }
    
    if($design_no == ""){
        $design_no = "NA";
    }
    
    $style = "";
    $query = "select * from it_styles where id = $obj->style_id";
    $obj_style = $db->fetchObject($query);
    if(isset($obj_style)){
        $style = $obj_style->name;
    }
    
    $size = "";
    $query = "select * from it_sizes where id = $obj->size_id";
    $obj_size = $db->fetchObject($query);
    if(isset($obj_size)){
        $size = $obj_size->name;
    }
    
    $ratio_type = RatioType::getName($obj->ratio_type);
    
    $ratio = $obj->ratio;

    
    $output .=$category.",".$design_no.",".$style.",".$size.",".$ratio_type.",".$ratio."<br/>";
    
}

print $output;






