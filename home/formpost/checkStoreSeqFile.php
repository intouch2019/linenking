<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once 'Classes/PHPExcel/IOFactory.php';

//step 1:- checkfile fn reads the file n sends store info to checkForItems fn
//step 2:- checkForItems fn checks for whether items curr_qty is available
//step 3:- If items available it sends to fn createItems
//step 4:- createItems fn sends to saveOrder fn which calc avail tot order qty n amt

$dir = "../data/storeSequence/";

$errors = array();
$success = "";
$err="";
if ($_FILES["file"]["error"] > 0)
  {
    $errors['err'] = "Error: " . $_FILES["file"]["error"] . "<br>";
  }
else {
      $db = new DBConn();
      $storeid = getCurrUserId();
      $date = date('Ymd_His');
      $textname = $_FILES['file']['name'];
      $ext = pathinfo($textname, PATHINFO_EXTENSION);
      $textnamediv = explode(".", $textname);
      if ($textnamediv[0]) {$name=$textnamediv[0]; } else {$name=$textname; }
      $newname = $date.".StoreSeq.".$storeid."."."$name".".$ext";
      $newdir = $dir.$newname;


        if (move_uploaded_file($_FILES['file']['tmp_name'], $newdir)) {           
            $err .= checkfile($newdir);
            if(trim($err)!=""){
                $errors['chkfile']= $err;
//                $errors[]= $err;
            }
            if(count($errors)==0){
             $success.= "File is valid";
            }

        } else {
            $errors['file']= "The file failed to upload";
        }
  }
  if (count($errors)>0) {
        unset($_SESSION['form_success']);
        unset($_SESSION['fpath']);
        unset($_SESSION['storeseq']);
        $_SESSION['form_errors'] = $errors;
  } else {
        unset($_SESSION['form_errors']);
        $_SESSION['form_success'] = $success;
        $_SESSION['fpath']=$newdir;
        unset($_SESSION['storeseq']);
  }

session_write_close();
header("Location: ".DEF_SITEURL."store/sequence");
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
//                $sq = "select * from it_codes where sequence = $sequence and id != $id ";
//                $sqobj = $db->fetchObject($sq);
//                if(isset($sqobj)){
//                    $return .= "<br>Two stores with same sequence not allowed";                  
//                }
            }
            
    }
    //unset($array_seq);
    return $return;
}
?>