<?php
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
require_once("session_check.php");
require_once "lib/core/Constants.php";
require_once 'lib/users/clsUsers.php';

extract($_POST);
//print_r($_POST);
$db=new DBConn();
$store = getCurrUser();

$category_name = isset($_POST['category']) ? $_POST['category'] : false;
$design_no = isset($_POST['cdesign']) ? $_POST['cdesign'] : false;

if(trim($category_name) == "" || trim($design_no) == ""){
    print "1:: Missing Parameters"; return;
}

try{
    //step 1: fetch ctg_id  from the name given
    $ctg_nm_db = $db->safe(trim($category_name));
    $design_no_db = $db->safe(trim($design_no));
    $query = "select * from it_categories where name = $ctg_nm_db";
    $cobj = $db->fetchObject($query);
    if(isset($cobj)){
        $ctg_id = $cobj->id;
        $query = "select cstyl.style_id, styl.name as style,csz.size_id,sz.name as size,sum(i.curr_qty) as qty from it_items i, it_ck_styles cstyl , it_ck_sizes csz,it_sizes sz, it_styles styl where i.design_no = $design_no_db  and i.ctg_id=$ctg_id and i.style_id = cstyl.style_id and i.size_id = csz.size_id and cstyl.style_id = styl.id and cstyl.ctg_id = $ctg_id and csz.size_id = sz.id and csz.ctg_id = $ctg_id group by cstyl.style_id,csz.size_id order by styl.name,sz.name";
        //$query = "select styl.name as style,sz.name as size,sum(i.curr_qty) as qty from it_items i, it_ck_styles cstyl , it_ck_sizes csz,it_sizes sz, it_styles styl where design_no = $design_no_db  and i.ctg_id=$ctg_id and i.style_id = cstyl.style_id and i.size_id = csz.size_id and cstyl.ctg_id = $ctg_id and csz.ctg_id = $ctg_id group by cstyl.style_id,csz.size_id";
        $objs = $db->fetchObjectArray($query);
        if(! empty($objs)){
         print "0::".json_encode($objs);
        }else{
         print "1::No records found";
        }
        
        /*
        //step 2 : fetch all styles against te category
        $q1 = "select s1.style_id, s2.name as style_name from it_ck_styles s1, it_styles s2 where s1.style_id=s2.id and ctg_id=$ctg_id and s2.is_active = 1 order by sequence";
        $styleobj = $db->fetchObjectArray($q1);
        $no_styles = count($styleobj);
        //step 3 : fech all sizes
        $q2 = "select s1.size_id, s2.name as size_name from it_ck_sizes s1, it_sizes s2 where s1.size_id = s2.id and ctg_id=$ctg_id order by sequence";
        $sizeobj = $db->fetchObjectArray($q2);
        $no_sizes = count($sizeobj);
        $arr=array();
        $json=array();
        for ($k = 0; $k < $no_styles; $k++) {
             $stylcod = $styleobj[$k]->style_id;
             $arr['style']=$stylcod;
             for ($i = 0; $i < $no_sizes; $i++) {
                $sizeid = $sizeobj[$i]->size_id;
                $query = "select id,sum(curr_qty) as qty from it_items where design_no = $design_no and MRP=$design->MRP and ctg_id=$ctg_id and style_id = $stylcod and size_id = $sizeid "; //and curr_qty > 0 ";
//                                                        echo "<br/>$query<br/>";
                $getitm = $db->fetchObject($query);

                //check to see if specific item exists in order, if exist -> stores qty in order_qty  
                if (isset($getitm)) {
                        print "[ $getitm->qty ]";
                }
             }
        }*/
//        
    }else{
        print "1:Missing categroy";
    }
    //select id,sum(curr_qty) as qty from it_items where design_no = $design_no and MRP=$design->MRP and ctg_id=$ctg_id and style_id = $stylcod and size_id = $sizeid "
    
}catch(Exception $xcp){
    print $xcp->getMessage();
}
