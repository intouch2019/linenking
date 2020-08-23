<?php

ini_set('max_execution_time', 3000);
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
require_once "lib/serverChanges/clsServerChanges.php";
require_once "lib/logger/clsLogger.php";
//$date=date("d/M/Y h:i:s A");
extract($_POST);
//$records="c8284";
//error_log("\n|JSON result: $records time:=>".$date."",3,"tmp_1.txt");
  $design_no="";
  
if(isset($records))
{
    $rec = explode("<>",$records);
    $design_no = $rec[0];
    $category_id = $rec[1];
//    $design_no=$records;
try
{
     $db = new DBConn();
     $cat_name="";
     $result="";
   // $design_no=8284;
     $cat_query="select distinct ctg_id from it_items where design_no='".$design_no."' and ctg_id = $category_id";
     $cat_obj=$db->fetchObject($cat_query);
      if (isset($cat_obj)) 
                {
          
          
          $cat_name_query="select name from it_categories where id=$cat_obj->ctg_id";
                 $cat_name_obj=$db->fetchObject($cat_name_query);
      if (isset($cat_name_obj)) 
                {
          $cat_name=$cat_name_obj->name;
               }
               
           $styleobj = $db->fetchObjectArray("select s1.style_id, s2.name as style_name from it_ck_styles s1, it_styles s2 where s1.style_id=s2.id and ctg_id=$cat_obj->ctg_id and s2.is_active = 1 order by sequence");
           $no_styles = count($styleobj);
          // echo "number of stylees=".$no_styles;
            $sizeobj = $db->fetchObjectArray("select s1.size_id, s2.name as size_name from it_ck_sizes s1, it_sizes s2 where s1.size_id = s2.id and ctg_id=$cat_obj->ctg_id order by sequence");
            $no_sizes = count($sizeobj); 
           // echo "number of sizes=".$no_sizes;
          
               for ($k = 0; $k < $no_styles; $k++) {
                   $stl=$styleobj[$k]->style_name;
                    if($stl=="Full Sleeve"){$stl='F/S';}
                      if($stl=="Half Sleeve"){$stl='H/S';}
                      
                   $result.=$stl."|";
                   $stylcod = $styleobj[$k]->style_id;
                   
                    for ($i = 0; $i < $no_sizes; $i++) {
                        $result.=$sizeobj[$i]->size_name.":";
                        
                        $sizeid = $sizeobj[$i]->size_id;
                        $query1 = "select id from it_items where design_no = '".$design_no."'  and ctg_id=$cat_obj->ctg_id and style_id = $stylcod and size_id = $sizeid "; //and curr_qty > 0 ";
                       // print "<br>".$query1.";";
                         $getitm1 = $db->fetchObject($query1);
                         if (isset($getitm1)) {
			$query = "select id,sum(curr_qty) as qty from it_items where design_no ='". $design_no."'  and ctg_id=$cat_obj->ctg_id and style_id = $stylcod and size_id = $sizeid "; //and curr_qty > 0 ";
                        // print "<br>".$query.";";
                         $getitm = $db->fetchObject($query);
                         //check to see if specific item exists in order, if exist -> stores qty in order_qty 
                        if (isset($getitm)) {
                                   $result.= $getitm->qty.",";
                                                        }
                        
                                                        }
                                                        else
                                                        {
                                                          $result.="0,";  
                                                        }
                    }
                    $result = rtrim($result, ",");
                    $result.="<>";
               }
               $result = rtrim($result, "<>");
           echo json_encode(array("Cat_name"=>$cat_name,"Result"=>$result)); 
           
      }
} catch (Exception $ex) {

}
}
else
{
    echo"Designno number missing";
}