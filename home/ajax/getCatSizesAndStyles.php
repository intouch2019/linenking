<?php
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
require_once("session_check.php");

extract($_GET);

if (!$cid) { return error("missing parameters"); }

try{
    $db = new DBConn();
   // $cat=$db->safe($cat);
    //echo "update it_categories set active=$status where id=$cid";
    $sizes = $db->fetchObjectArray("select s.size_id, s.sequence, si.name from it_ck_sizes s, it_sizes si where s.size_id=si.id and s.ctg_id=$cid order by s.sequence asc");
    $styles = $db->fetchObjectArray("select s.style_id, s.sequence, si.name from it_ck_styles s, it_styles si where s.style_id=si.id and s.ctg_id=$cid order by s.sequence asc");
    //print_r ($sizes);
    //print_r ($styles);
    $fullarray = array();
    $sizearr = array();
    $stylearr = array();
    foreach ($sizes as $size) {
      $seq = $size->sequence;
      $name = $size->name;
      $size_id = $size->size_id;
      $sizearr[$seq] = $name.":".$size_id; 
    }
    foreach ($styles as $style) {
        $seq = $style->sequence;
        $name = $style->name;
        $style_id= $style->style_id;
        $stylearr[$seq] = $name.":".$style_id;
    }
    $fullarray = array("size" => $sizearr, "style" => $stylearr);
    success($fullarray);
}catch(Exception $xcp){
    echo "error:There was a problem processing your request. Please try again later.";
 //   return;
}

function error($msg) {
    print json_encode(array(
            "error" => "1",
            "message" => $msg
            ));
}

function success($msg) {
    print json_encode($msg);
}
?>
