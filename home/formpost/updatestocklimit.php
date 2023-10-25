<?php

require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once 'Classes/PHPExcel/IOFactory.php';
require_once "lib/serverChanges/clsServerChanges.php";
require_once "lib/core/strutil.php";
require_once 'lib/users/clsUsers.php';

extract($_POST);


$db = new DBConn();

$errors = array();
$success = "";
$err = "";
$store_count = 0;

if ($_FILES["file"]["error"] > 0) {
    $errors['err'] = "Error: " . $_FILES["file"]["error"] . "<br>";
} else {
    $db = new DBConn();
    $storeid = getCurrUserId();
    $date = date('Ymd_His');
    $textname = $_FILES['file']['name'];
 
 
    $ext = pathinfo($textname, PATHINFO_EXTENSION);
    $textnamediv = explode(".", $textname);
  
    if ($textnamediv[0]) {
        $name = $textnamediv[0];
          

    } else {
        $name = $textname;
          
    }
    $newname = $date . "$name" . ".$ext";
    $newdir = $newname;

    if (move_uploaded_file($_FILES['file']['tmp_name'], $newdir)) {
       
      
        $err .= checkfile($newdir);
        
         if($textnamediv[1] =="xls" || $textnamediv[1] =="xlsx"){
 
    }else{
        $imltpflag = true;
        $err .= "File extension must be .xls or .xlsx";
    }
        if (trim($err) != "") {
            $errors['chkfile'] = $err;
        }
        if (count($errors) == 0) {
            $success = "<div style='font-size:14px;background-color:white'> Total $store_count Stores Data Uploaded Successfully</div>";
            unlink($newdir);
        }
    } else {
        $errors['file'] = "The file failed to upload";
    }
}

if (count($errors) > 0) {

    unset($_SESSION['form_success']);
    unset($_SESSION['fpath']);
    unset($_SESSION['storeseq']);
    $_SESSION['form_errors'] = $errors;
} else {

    unset($_SESSION['form_errors']);
    $_SESSION['form_success'] = $success;
    $_SESSION['fpath'] = $newdir;
    unset($_SESSION['storeseq']);
}


session_write_close();
header("Location: " . DEF_SITEURL . "admin/stores/stocklimitupdate");
exit;

function checkfile($newdir) {
    $db = new DBConn();
    $imltpflag = false;
    $objPHPExcel = PHPExcel_IOFactory::load($newdir);
    $objWorksheet = $objPHPExcel->getActiveSheet();
    $return = "";
    $row = 0;
    $rcnt = 1;
    global $store_count;
    $rowCount = 1;
    $store_count = -1;  //excel sheet fetch extra empty row - to reduce that row declare -1
    
    
    foreach ($objWorksheet->getRowIterator() as $row) {
   
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false);
        $colno = 0;
        $rcnt++;
        $storeid=0;
        $msl=0;
        $maxsl=0;

        foreach ($cellIterator as $cell) {
     
            if ($rowCount == 1) {     //check column name at 1st row
                $value = trim(strval($cell->getValue()));
                if ($colno == 0) {

                    $storeid = $value;
                    if ($storeid != "Id") {
                        $return = "Column name $value does not match</br>";
                    }
                }
                 if ($colno == 1) {

                    $storename = $value;
                    if ($storename != "Store Name") {
                        $return = "Column name $value does not match</br>";
                    }
                }
                if ($colno == 2) {
                    $msl = $value;
                    if ($msl != "Min Stock Level") {
                        $return = "Column name $value does not match</br>";
                    }
                }
                if ($colno == 3) {
                    $maxsl = $value;
                    if ($maxsl != "Max Stock Level") {
                        $return = "Column name $value does not match</br>";
                    }
                }
             
                $colno++;
            } 
        }
        $rowCount++;
        //check empty fields in excel sheet
        if ($rowCount >= 2) {
            foreach ($cellIterator as $cell) {
                $value = trim(strval($cell->getValue()));
                if ($colno == 0) {
                    $storeid = $value;
                    if ($storeid == "") {
                        $imltpflag = true;
                        $return = "Empty field in *Id* Column</br>";
                    }
                    if(!preg_match('/^[0-9]+$/', $storeid)){
                        $imltpflag = true;
                        $return .= "Non-numeric field in *Id* Column</br>";
                    }
                    $qry="SELECT CASE WHEN EXISTS (SELECT id FROM it_codes WHERE usertype=4 and id = $value) THEN 1 ELSE 0 END AS result";
                      $res=$db->fetchObject($qry);
                    
                      if($res->result==0){
                          
                           $imltpflag = true;
                        $return = "Please check storeid $value store not avaibale in database</br>";
                      }
                    
                }
                if ($colno == 1) {
                    $storename = $value;
                    if ($storename == "") {
                        $imltpflag = true;
                        $return = "Empty field in *Store Name* Column</br>";
                    }
                }
                if ($colno == 2) {
                    $msl = $value;
                    if ($msl == "") {
                        $imltpflag = true;
                        $return = "Empty field in *Min Stock Level* Column</br>";
                    }
                    if(!preg_match('/^[0-9]+$/', $msl)){
                        $imltpflag = true;
                        $return .= "Non-numeric field in *Min Stock Level* Column</br>";
                    }
                }
                if ($colno == 3) {
                    $maxsl = $value;
                    if ($maxsl == "") {
                        $imltpflag = true;
                        $return = "Empty field in *Max Stock Level* Column</br>";
                    }
                    if(!preg_match('/^[0-9]+$/', $maxsl)){
                        $imltpflag = true;
                        $return .= "Non-numeric field in *Max Stock Level* Column</br>";
                    }
                }
                
                $colno++;
            }
            $store_count++;
        }
    }
    if ($store_count == 0) {
        $imltpflag = true;
        $return = "File is Empty</br>";
    }
    if ($imltpflag == false &&  trim($return) == "") {
        $status = updateStockLimit($newdir);
    }
    if (isset($status)) {
        $return = $status;
    }

    return $return;
}

//Insert data into db
function updateStockLimit($newdir) {
    $db = new DBConn();
    $objPHPExcel = PHPExcel_IOFactory::load($newdir);
    $objWorksheet = $objPHPExcel->getActiveSheet();
    $row = 0;
    $rowCount = 1;

    foreach ($objWorksheet->getRowIterator() as $row) {
        
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false);
        $colno = 0;
      $storeid=0;
      $msl=0;
      $maxsl=0;
        $iquery = "";
        
        if ($rowCount >= 2) {
            foreach ($cellIterator as $cell) {

                $value = trim(strval($cell->getValue()));
                if (trim($value) != "") {
                    if ($colno == 0) {
                        $storeid = $db->safe(trim($value));
                    }
                    if ($colno == 2) {
                        $msl = $db->safe(trim($value));
                    }
                    if ($colno == 3) {
                        $maxsl = $db->safe(trim($value));
                    }
                    
                }
                $colno++;
            }
        }
        if ($rowCount >= 2 && trim($storeid) != "" && trim($msl) != "" && trim($maxsl) != "" ) {
            //validation check
            $iquery = "update it_codes set min_stock_level=$msl,max_stock_level=$maxsl where id=$storeid";
                
            $db->execInsert($iquery);
            
        }
        $rowCount++;
    }
}