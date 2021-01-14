<?php
require_once("../../it_config.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "session_check.php";

if (isset($_POST['sid']))
	$store_id = $_POST['sid'];
if (isset($_POST['cat_id']))
	$cat_id = $_POST['cat_id'];
if (isset($_POST['design_id']))
	$design_id = $_POST['design_id'];
if (isset($_POST['r_type']))
	$r_type = $_POST['r_type'];
if (isset($_POST['user_id']))
	$user_id = $_POST['user_id'];
if (isset($_POST['design_no']))
	$design_no = $_POST['design_no'];

$db = new DBConn();
try
{
    
   $styleobj = $db->fetchObjectArray("select s1.style_id,s2.name as style_name from it_ck_styles s1,it_styles s2 where s1.ctg_id=$cat_id and s1.style_id=s2.id  and s2.is_active = 1 order by s1.sequence");
                $no_styles = count($styleobj);
//                  print_r($styleobj);
                $sizeobj = $db->fetchObjectArray("select s1.size_id, s2.name as size_name from it_ck_sizes s1,it_sizes s2 where s1.ctg_id=$cat_id and s1.size_id=s2.id order by s1.sequence");
//                  print_r($sizeobj);
                $no_sizes = count($sizeobj);
    
                    for ($i = 0; $i < $no_styles; $i++) {
                        $style_id=$styleobj[$i]->style_id;
                    for ($j = 0; $j < $no_sizes; $j++) {
                        $size_id=$sizeobj[$j]->size_id;
                         $query="select id from it_store_ratios where store_id = $store_id and ctg_id = $cat_id and "
                                . "ratio_type = $r_type and style_id = $style_id and size_id = $size_id and "
                                . "design_id = $design_id";
//                        print $query.";<br/>";
                        $obj = $db->fetchObject($query);
                        if(isset($obj) && !empty($obj)){
                            $query = "update it_store_ratios set ratio=1,updated_by=$user_id,updatetime=now() where id = $obj->id";
                            $db->execUpdate($query);
                        }else{
                            $query = "insert into it_store_ratios set store_id=$store_id,ctg_id=$cat_id,"
                                    . "style_id=$style_id,size_id=$size_id,ratio_type=$r_type,"
                                    . "ratio=1,design_id = $design_id, updated_by=$user_id,createtime=now()";
                            $db->execInsert($query);
                        }
                        
                        
                    }
                }

                
                
    echo json_encode(array("error" => "0", "message" => "Default ratio (i.e. 1) set Successfully for design no:".$design_no)); 
    

}
 catch (Exception $xcp) {
    echo json_encode(array("error" => "1", "message" => "Exception:" . $xcp->getMessage()));
}
?>