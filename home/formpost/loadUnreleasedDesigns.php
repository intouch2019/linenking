<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/logger/clsLogger.php";

$errors = array();
$success = "File uploaded Successfully <br>";
$commit=false;

extract($_GET);
if (!isset($filename) && trim($filename) == "") {
    $errors['file'] = "File not found";
} else {
    $commit = true;
}
if (count($errors) == 0) {
    $db = new DBConn();
    $success .= loadunreleased($filename,$commit);
}

//print $success;

if (count($errors) > 0) {
    unset($_SESSION['form_success']);
    unset($_SESSION['ufpath']);
    $_SESSION['form_errors'] = $errors;
} else {
    unset($_SESSION['form_errors']);
    unset($_SESSION['ufpath']);
    $_SESSION['form_success'] = $success;
    $_SESSION['loadedunreleased'] = "done";
}

session_write_close();
header("Location: " . DEF_SITEURL . "grn/allrelease");
exit;



function loadunreleased($urfile,$commit){
   $fresp = "<br/>Loaded Unreleased Design(s)<br/>";
    $fh = fopen($urfile, "r");
    if (!$fh) { $fresp .= "File not found\n"; return; }

    $db=new DBConn(); 
    
    //    print "<br>File Name in fn $newfile ";
    $flg=1;   
    $errflg=0;
    $skipped_cnt=0;
    $resp="";
    $fresp = "";
    $cnt=0;
    $fh = fopen($urfile, "r");
    if (!$fh) { //print "File not found\n"; 
        $fresp .= "0::File not found";
    return; }
    //$fresp = "1::";
    $db=new DBConn();

    $rowno=0;$tot_qty=0;$skip_qty=0;
//    if ($commit) $db->execUpdate("update it_items set curr_qty = 0, updatetime=now()");
//    $error="Error:";
    $str = "";
    while(($data=fgetcsv($fh,0,",")) !== FALSE) {
        if($flg== 1){
            $flg=0;
        }else{
//            print_r($data);
            $ctg_name = trim($data[0]);  
            $design_no = trim($data[1]);  
            $mrp = trim($data[2]);
            $missing_ctg_flag = 0;
            if(trim($ctg_name)!="" && trim($design_no)!="" && trim($mrp)!=""){
                    $design_no_db = $db->safe(trim($design_no));
                    $ctg_name_db = $db->safe(trim($ctg_name));
                    //fetch ctg_id
                    $cqry = "select * from it_categories where name = $ctg_name_db ";
//                    print "\n$cqry";
                    $cobj = $db->fetchObject($cqry);
                    if(isset($cobj)){
                        $ctg_id = $cobj->id;
                        $missing_ctg_flag = 0;
                    }else{
                        $ctg_id = -1;
                        $errflg=1;
                        $skipped_cnt++;
                        $missing_ctg_flag = 1;
                         $str .= "<br>Category named  $ctg_name_db not found in database ";
                    }
                    
                    
                    if(trim($missing_ctg_flag) == 0){
                        $query = "select count(*) as cnt from it_items where ctg_id = $ctg_id and design_no = $design_no_db and MRP = $mrp ";
//                        print "\n$query\n";

                        $obj = $db->fetchObject($query);
                        if(isset($obj) && trim($obj->cnt) > 0){
                            $cnt++;                
                            $uqry = "update it_items set grn_qty = grn_qty + curr_qty , curr_qty = 0 where ctg_id = $ctg_id and design_no = $design_no_db and MRP = $mrp ";
                           // print "\n$uqry";
                            if(trim($commit)==1){
                             $db->execUpdate($uqry);                
                            }
                        }else{
                            $errflg=1;
                            $skipped_cnt++;
                            $str .= "<br>Missing data in combination , Catgeory as '$ctg_name' , Designno as '$design_no' and mrp as $mrp ";
                           // $skipped_array[] = $ctg_name."::".$design_no."::".$ctg_id;
                        }
                    }
            }
        }
        
    }    
        

    $fresp .= "<br/>Total Designs loaded for unreleased=$cnt<br/>Total Skipped rows=$skipped_cnt";
    $fresp .= $str;
    if($errflg==1){ // means error occured
        $resp = $fresp."<br/> Please Upload a Valid file and try again later";
    }else{
        $resp = $fresp;
    }
    fclose($fh);
    //return $fresp;
    $db->closeConnection();
    return $resp;
}

