<?php
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";

$db = new DBConn();

extract($_GET);

//$store_id = 150;

$query = "select * from it_store_ratios where store_id = $store_id";

$obj_store_ratio = $db->fetchObjectArray($query);

$output = "";
$headers = array('Category','Design No','Style','Size','Ratio Type','Ratio');

header('Content-type: text/csv');
header('Content-disposition: attachment;filename='.$store_id.'_ratioDetails.csv');

$fh = @fopen('php://output', 'w');
fputcsv($fh, $headers);


foreach ($obj_store_ratio as $obj){
    
    $rowdata = array();
    
    $query = "select * from it_categories where id = $obj->ctg_id";
    $obj_cat = $db->fetchObject($query);
    $category = "-";
    if(isset($obj_cat)){
        $category = $obj_cat->name;
    }
    $rowdata[] = $category;  //set category name
    
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
    
    $rowdata[] = $design_no;  // set design no
    
    $style = "-";
    $query = "select * from it_styles where id = $obj->style_id";
    $obj_style = $db->fetchObject($query);
    if(isset($obj_style)){
        $style = $obj_style->name;
    }
    
    $rowdata[] = $style;  // set style
    
    $size = "-";
    $query = "select * from it_sizes where id = $obj->size_id";
    $obj_size = $db->fetchObject($query);
    if(isset($obj_size)){
        $size = $obj_size->name;
    }
    
    $rowdata[] = $size;  // set size
     
    $ratio_type = RatioType::getName($obj->ratio_type);
    
    $rowdata[] = $ratio_type;  // set ratio type
    
    $ratio = $obj->ratio;
    
    $rowdata[] = $ratio;  // set ratio
    
    fputcsv($fh, array_values($rowdata));
    unset($rowdata);
    
    //$output .=$category.",".$design_no.",".$style.",".$size.",".$ratio_type.",".$ratio."<br/>";
    
}

//print $output;

// Close the file
    fclose($fh);
// Make sure nothing else is sent, our file is done
    exit;






