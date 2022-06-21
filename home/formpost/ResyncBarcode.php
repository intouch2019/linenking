<?php

require_once "../../it_config.php";
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once 'lib/core/Constants.php';
require_once "lib/serverChanges/clsServerChanges.php";



   $db = new DBConn();
   $serverCh = new clsServerChanges();
   $cnt = 0;
   
   $errors = array();
   $success = array();
   $ctg_id = isset($_POST['sel_cat']) ? $_POST['sel_cat'] : false;
   $design_id = isset($_POST['designnos']) ? $_POST['designnos'] : false;
   $mrp = isset($_POST['mrp']) ? $_POST['mrp'] : false;

   extract($_POST);
   //print_r($_POST);

 
 try{
     

   if(isset($mrp) && $mrp!=0){
  
   $query = "select * from it_mrp_taxes where mrp=$mrp ";
   //$query = "select * from it_mrp_taxes where mrp<=1050 ";
                $objs = $db->fetchObjectArray($query);
                if(isset($objs) && !empty($objs) && $objs != null){
                    foreach ($objs as $obj){
                       $server = json_encode($obj);
                       $server_ch = "[".$server."]"; // converting n storing in obj format so that easy retrival at pos side                    );
                       $ser_type = changeType::mrptaxes; 
                       $cnt++;
                       
                       $serverCh->insert($ser_type, $server_ch ,$obj->id);
                    }
             
            
               }
              
               $success = "mrp update successfully";
   }
   
 if(isset($design_id) && $design_id!=0){    
    $obj = $db->fetchObject("select * from it_ck_designs where id=$design_id");

    if(isset($obj) && !empty($obj) && $obj != null){
                    $server = json_encode($obj);
                    $server_ch = "[".$server."]"; // converting n storing in obj format so that easy retrival at pos side
                    $ser_type = changeType::ck_designs;
                    $serverCh->insert($ser_type, $server_ch,$obj->id);
                    $cnt++;
    }
     
 }
 if(isset($design_id) && $design_id!=0){
 
    
      $objj1 = $db->fetchObjectArray("select * from it_items where design_id=$design_id");
      $db->closeConnection();
                //$server_ch = json_encode($obj1);
                foreach ($objj1 as $obj1){
                    $server = json_encode($obj1);
                    $server_ch = "[".$server."]"; // converting n storing in obj format so that easy retrival at pos side
                    $ser_type = changeType::items;
                    $serverCh->insert($ser_type, $server_ch,$obj1->id);
                    $cnt++;
                }
                $success = "design_no update successfully";
 }
 

//      $query = "select * from it_properties ";
//                $objs = $db->fetchObjectArray($query);
//                if(isset($objs) && !empty($objs) && $objs != null){
//                    foreach ($objs as $obj){
//                       $server = json_encode($obj);
//                       $server_ch = "[".$server."]"; // converting n storing in obj format so that easy retrival at pos side                    );
//                       $ser_type = changeType::properties;                       
//                       $cnt++;
//                       
//                       $serverCh->insert($ser_type, $server_ch ,$obj->ID);
//                    }
//                }
//   
   
//             $query = "select * from it_categories where name = 'Thermal Roll'";
 if(isset($ctg_id) && $ctg_id!=0){
     
 
             $query = "select * from it_categories where id=$ctg_id";
             $objs = $db->fetchObjectArray($query);
             if(isset($objs) && !empty($objs) && $objs != null){
                  foreach ($objs as $obj){
                    $server = json_encode($obj);
                    $server_ch = "[".$server."]"; // converting n storing in obj format so that easy retrival at pos side                    );
                    $ser_type = changeType::categories;                                   
                    //$store_id = DEF_WAREHOUSE_ID;
                    //fetch stores
                    $serverCh->insert($ser_type, $server_ch,$obj->id);
                    $cnt++;
//                    $qry = "select * from it_codes where pos_version = '2.30.12'";
//                    $sobjs = $db->fetchObjectArray($qry);
//                    foreach($sobjs as $sobj){
//                        $serverCh->save($ser_type, $server_ch,$sobj->id,$obj->id);   
//                    }
                   // $serverCh->save($ser_type, $server_ch,$store_id,$obj->id);  
             
                  }  
                   
                  }
                  $success = "category update successfully";
 }
 
      
   
//}catch(Exception $xcp){
  //  print $xcp->getMessage();
//}
//print "Tot_inserted_rows: ".$cnt;
//---------------------------------------------------------------------------------



if($mrp !=0 && $design_id !=0 && $mrp !=0){
     $success = "all batch update successfully";
}


} catch (Exception $xcp) {

    $errors['status'] = "There was a problem processing your request. Please try again later";
}


if (count($errors) > 0) {
    $_SESSION['form_errors'] = $errors;
} else {
    $_SESSION['form_success'] = $success;
}


session_write_close();
header("Location: " . DEF_SITEURL . "Resync/batch");

exit;




