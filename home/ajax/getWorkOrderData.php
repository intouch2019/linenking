<?php

ini_set('max_execution_time', 3000);
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
//require_once "lib/serverChanges/clsServerChanges.php";
require_once "lib/logger/clsLogger.php";


extract($_POST);
//print_r($_POST);
if (!isset($_POST)) {
    print 'Y';
    return;
} else {
      try {
  //      $serverCh = new clsServerChanges();
        $exist_category = FALSE;
    //    $exist_prod_type = FALSE;
        $db = new DBConn();
        //$records= "17028<>1"; //17001,17004
        //print_r($_POST);
        $records = $_POST['order_id']."<>2";
        $url = "http://15.206.125.234/ckwip/home/syncDealerportal/sendworkorderDet.php";
      // $url = "http://192.168.0.36/ckwip/home/syncDealerportal/sendworkorderDet.php";
        $fields = array('records' => urlencode($records));
        $fields_string = "";
        foreach ($fields as $key => $value) {
            $fields_string .= $key . '=' . $value . '&';
        }
        rtrim($fields_string, '&');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, count($fields));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);

        $output = curl_exec($ch);
        $info = curl_getinfo($ch); 
        curl_close($ch);
//ok logic
    // $output='{"wo_id":"20016","category":"Slim Shirt","designno":"27769","mrp":"895","production_type":"AEROSOFT ","product_id":"11"}[{"style":"F\/S"},{"style":"H\/S"}][{"size":"S"},{"size":"M"},{"size":"L"},{"size":"XL"},{"size":"XXL"}]';   
     // $output='{"wo_id":"20104","category":"Jeans","designno":"27935","mrp":"1","production_type":"DENIM","product_id":"106","label_name":"Jeans Set"}[{"style":"NP"}][{"size":"28"},{"size":"30"},{"size":"32"},{"size":"34"},{"size":"36"},{"size":"38"},{"size":"40"},{"size":"42"},{"size":"44"}]';  
                                      //,"label_name":"Jeans Set"

        
        
        
        $outputs=explode('[',$output);    
         $jsondata =$outputs[0];
        $encoded = json_encode($jsondata);
        $deocode = json_decode($outputs[0]);
      
        if ($deocode != NULL) {

            $cat_name = $db->safe($deocode->category);
            $prod_type_name = $db->safe($deocode->production_type);

            //maping for label as material
            $materials=$deocode->label_name;
            if($materials=="Golden Label"){
            $materials="100% Pure Linen";}
            if($materials=="Golden Label + Super Net"){
            $materials="100% Pure Linen";}
            if($materials=="Silver Label"){
            $materials="Linen Rich";}
            if($materials=="Jeans Set"){
            $materials="Ultra Slim";}
             if($materials=="Black label"){
             $materials="Linen Look";}
             
             
             $material_name = $db->safe($materials);
           $materials_query = "select id from it_materials where name='".$materials."'"; 
           //error_log("\n|JSON result:$materials_query ",3,"tmp_1.txt");
               $mtrl_obj = $db->fetchObject($materials_query);
               
            if (isset($mtrl_obj)) {
            $material_id=$mtrl_obj->id;
         
               } else { $material_id="0"; }
            
            
            //to add category id in json array --start
       // $category_query = "select name,id from it_categories";

            $category_query = "select id from it_categories where name=$cat_name";
           
//       $new_cat = "demoCategor";
//        $category_query = "select id from it_categories where name='$new_cat'";
        //print $category_query;
            $cat_obj = $db->fetchObject($category_query);
            if (isset($cat_obj)) {
                $exist_category = TRUE;
                $second_array = array('category_id' => $cat_obj->id);
                //print_r($second_array);
                $deocode = array_merge((array) $deocode, (array) $second_array);
                //print_r($deocode);
            }
         else {
                $exist_category = FALSE;
//                session_start();
//                 $_SESSION["new_category_name_1"] = $cat_name;
            }
            //-------------------------------------end
            //-----start for producstion types
            $prod_type_query = "select id from it_prod_types where name = $prod_type_name";
            //$safe_prod_name = $db->safe($prod_type_name);
            $prod_obj = $db->fetchObject($prod_type_query);
            if (isset($prod_obj)) {
      //          $exist_prod_type = TRUE;
                $third_array = array('production_type_id' => $prod_obj->id);
                $deocode = array_merge((array) $deocode, (array) $third_array);
            } else {
                //$exist_prod_type = FALSE;
//                $insert_production_type = "insert into it_prod_types set name=" . $prod_type_name . ",createtime=now()";
//                $set = $db->execInsert($insert_production_type);
//                $obj = $db->fetchObject("select * from it_prod_types where id = $set");
//                if(isset($obj)){
//                    $server = json_encode($obj);
//                    $server_ch = "[".$server."]";
//                    $ser_type = constant("changeType::prod_types");
//                    $serverCh->insert($ser_type, $server_ch,$obj->id);                    
//                }
                $set="0";
                $third_array = array('production_type_id' => $set);
                $deocode = array_merge((array) $deocode, (array) $third_array);
            }
            //------end
                $by  = "select name from it_mfg_by  where id=3;";
                $mfg_by = $db->fetchObject($by);
                //print_r($mfg_by);
                $fourth_array = array('mfg_by_name' => $mfg_by->name);
                $deocode = array_merge((array) $deocode, (array) $fourth_array);
                
                $fifth_array = array('mfg_by_id' => "3");                
                $deocode = array_merge((array) $deocode, (array) $fifth_array);

                //code for Styles
                $outputs[1]=str_replace(' ', '', $outputs[1]);
                $outputs[1]=str_replace('"', '', $outputs[1]);
                $outputs[1]=str_replace('}', '', $outputs[1]);
                $outputs[1]=str_replace('{', '', $outputs[1]);
                $outputs[1]=str_replace(']', '', $outputs[1]);
                //$outputs[1]=str_replace('\/', '', $outputs[1]);

                $styles_list=explode(',',$outputs[1]);
                $styles="";
                $i=0;
                
                foreach($styles_list as $style)
                {
                    if((substr_count($style,":"))>0)
                    { 
                   
                   $stl= explode(":", $style); 
                    if($i!=0)
                    {
                        $styles .=",";
                    } $i=1;
                     if($stl[1]=='F\/S'){$stl[1]="FS";}
                      if($stl[1]=='H\/S'){$stl[1]="HS";}
                    $style_query = "select id from it_styles where name ='".$stl[1]."'";
                      // error_log("\n|Style  ENQUERY: $style_query ",3,"tmp_1.txt");
                        $stl_obj =$db->fetchObject($style_query);
                        
                    $styles .=$stl_obj->id;
                         }
                }
               
               // error_log("\n|JSON result: $styles ",3,"tmp_1.txt");
                 //2.code for sizes
                $outputs[2]=str_replace(' ', '', $outputs[2]);
                $outputs[2]=str_replace('"', '', $outputs[2]);
                $outputs[2]=str_replace('}', '', $outputs[2]);
                $outputs[2]=str_replace('{', '', $outputs[2]);
                $outputs[2]=str_replace(']', '', $outputs[2]);
                
                $sizes_list=explode(',',$outputs[2]);
                $sizes="";
                $i=0;
               
                foreach($sizes_list as $size)
                {
                    if(substr_count($size,":")>0)
                    {
                    $sze= explode(":", $size); 
                    if($i!=0)
                    {
                        $sizes .=",";
                    }
                    $i=1;
                    $cat=  strtolower($cat_name);
                    if(substr_count($cat, "jeans")>0 ||substr_count($cat, "trouser")>0 )
                    {   if($sze[1]=='28'){$sze[1]="28\/71";}
                        if($sze[1]=='30'){$sze[1]="30\/76";}
                        if($sze[1]=='32'){$sze[1]="32\/81";}
                        if($sze[1]=='34'){$sze[1]="34\/86";}
                        if($sze[1]=='36'){$sze[1]="36\/91";}
                        if($sze[1]=='40'){$sze[1]="40\/102";}
                        if($sze[1]=='42'){$sze[1]="42\/107";}
                        if($sze[1]=='44'){$sze[1]="44\/112";} 
                        if($sze[1]=='S'){$sze[1]="38/97";}
                        if($sze[1]=='38'){$sze[1]="38/97";}
                        }                       
                        if($sze[1]=='S'){$sze[1]="38";}
                        if($sze[1]=='M'){$sze[1]="39";}
                        if($sze[1]=='L'){$sze[1]="40";}
                        if($sze[1]=='XL'){$sze[1]="42";}
                        if($sze[1]=='XXL'){$sze[1]="44";}
                       $size_query = "select id from it_sizes where name ='".$sze[1]."'";
                    // error_log("\n|Style  ENQUERY: $size_query ",3,"tmp_1.txt");
                        $sze_obj =$db->fetchObject($size_query);
                    $sizes .=$sze_obj->id;  
                        }
                }
//            
            if ($exist_category == FALSE) {
                $cat_nm = trim($cat_name, "'");
                $result = "1::category new<>" .$cat_nm;
            }else {
                $result = json_encode($deocode);              
            }
        }else{
            echo json_encode(array("error" => "1", "message" => "problem in getting data"));
            return;
        }
        //print $result;
    } catch (Exception $e) {
   
    }

    echo json_encode(array("error" => "0", "message" => $result,"styles" => $styles,"sizes" => $sizes,"material_id" => $material_id));
}
?>