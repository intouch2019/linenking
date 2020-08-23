<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once 'Classes/PHPExcel/IOFactory.php';

$commit = false;
$errors = array();
$success = "";
$err = "";
$resp = "";

extract($_GET);

if (!isset($filename) && trim($filename) == "") {
    $errors['file'] = "File not found";
} else {
    $commit = true;
}

if (count($errors) == 0) {
    $db = new DBConn();
    $err = checkfile($filename);
//    print "ERR: $err";
    if(trim($err)==""){
        updateSeq($filename);
        $success = "Store(s) sequence successfully updated. ";
    }
}

if (count($errors) > 0) {
    unset($_SESSION['form_success']);
    unset($_SESSION['fpath']);
    $_SESSION['form_errors'] = $errors;
} else {
    unset($_SESSION['form_errors']);
    unset($_SESSION['fpath']);
    $_SESSION['form_success'] = $success;
    $_SESSION['storeseq'] = "done";
}

session_write_close();
header("Location: " . DEF_SITEURL . "store/sequence");
exit;



function checkfile($newdir){
    $db = new DBConn();
    $itemfound= "";$resp="";
    $objPHPExcel = PHPExcel_IOFactory::load($newdir);
    $objWorksheet = $objPHPExcel->getActiveSheet();
    $return=""; $first = 1; $code='';
    $row=0;
    $flg = 0 ;
    $rcnt=1;
    $array_seq = array();
    foreach ($objWorksheet->getRowIterator() as $row) {
        if($flg==0){ $flg++; continue; }
//        print "<br>";
//        print_r($row);
//        print "<br>";
        
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false);
        $colno=0;
        $rcnt++;
        $id = ""; $store_name="";$sequence = "";
        foreach ($cellIterator as $cell) {            
            $value = trim(strval($cell->getValue()));
            if(trim($value)!=""){
                if($colno==0){
                    $id = $value;
                }
                if($colno==1){
                    $store_name = $value;
                }
                if($colno==2){
                    $sequence = $value;
                }
            }
           
            $colno++;
        }
         //print_r($array_seq);
           // print "<br> ID: ".$id." STORE NAME: ".$store_name." SEQUENCE: ".$sequence;
            if(trim($id)!="" && trim($store_name)!="" && trim($sequence)!=""){
                //validation chk
                $no_space = str_replace(" ", "", $store_name);
                $store_name_db = $db->safe(trim($no_space));
                $check = " replace(store_name ,' ','') = $store_name_db ";
                $query = "select * from it_codes where id = $id and $check ";
                $obj = $db->fetchObject($query);
                if(isset($obj)){ 
                    //do nothing
                }else{
                    
                    $return .= "<br>Invalid Store found at row $rcnt. Please upload correct excel";                  
                }
                if(array_key_exists($sequence, $array_seq)){
                       $return .= "<br>Duplicate Sequence not allowed , found on row $rcnt ";
                   }else{
                       $array_seq[$sequence] = $sequence;
                   }
                //print_r($array_seq);
            }
            
    }
    $db->closeConnection();
    //unset($array_seq);
    return $return;
}


function updateSeq($newdir){
    $db = new DBConn();
    $itemfound= "";$resp="";
    $objPHPExcel = PHPExcel_IOFactory::load($newdir);
    $objWorksheet = $objPHPExcel->getActiveSheet();
    $return=""; $first = 1; $code='';
    $row=0;
    $flg = 0 ;
    $rcnt=1;
    $array_seq = array();
    //reset all previous sequence
    $sq = "update it_codes set sequence = null ";
    $db->execUpdate($sq);
    
    foreach ($objWorksheet->getRowIterator() as $row) {
        if($flg==0){ $flg++; continue; }
//        print "<br>";
//        print_r($row);
//        print "<br>";
        
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false);
        $colno=0;
        $rcnt++;
        $id = ""; $store_name="";$sequence = "";
        foreach ($cellIterator as $cell) {            
            $value = trim(strval($cell->getValue()));
            if(trim($value)!=""){
                if($colno==0){
                    $id = $value;
                }
                if($colno==1){
                    $store_name = $value;
                }
                if($colno==2){
                    $sequence = $value;
                }
            }
           
            $colno++;
        }
         //print_r($array_seq);
           // print "<br> ID: ".$id." STORE NAME: ".$store_name." SEQUENCE: ".$sequence;
            if(trim($id)!="" && trim($store_name)!="" && trim($sequence)!=""){
                //validation chk
                $no_space = str_replace(" ", "", $store_name);
                $store_name_db = $db->safe(trim($no_space));
                $check = " replace(store_name ,' ','') = $store_name_db ";
                $query = "select * from it_codes where id = $id and $check ";
                $obj = $db->fetchObject($query);
                if(isset($obj)){ 
                    $uquery = "update it_codes set sequence = $sequence where id = $obj->id ";
//                   print "<br> $uquery";
                    $db->execUpdate($uquery);
                }
            }
            
    }
    $db->closeConnection();
    //unset($array_seq);
    
}