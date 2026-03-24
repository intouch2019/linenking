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
$form = $_POST['form_id'];


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
    $_SESSION['form_id'] = $form; // Store the form ID
    $_SESSION['form_errors'] = $errors;
} else {

    unset($_SESSION['form_errors']);
    $_SESSION['form_success'] = $success;
    $_SESSION['fpath'] = $newdir;
    $_SESSION['form_id'] = $form; // Store the form ID
    unset($_SESSION['storeseq']);
}


session_write_close();
header("Location: " . DEF_SITEURL . "admin/stores/stackcapacityupdate");
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
                    if ($msl != "Stack Capacity Adjusted (%)") {
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
                        $return = "Please check storeid $value store not available in database</br>";
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
                        $return = "Empty field in *Stack Capacity Adjusted (%)* Column</br>";
                    }
                    if(!preg_match('/^[0-9]+$/', $msl)){
                        $imltpflag = true;
                        $return .= "Non-numeric field in *Stack Capacity Adjusted (%)* Column</br>";
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
    $userid = getCurrUserId();
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
        $iquery = "";
        
        if ($rowCount >= 2) {
            foreach ($cellIterator as $cell) {

                $value = trim(strval($cell->getValue()));
                if (trim($value) != "") {
                    if ($colno == 0) {
                        $storeid = trim($value);
                    }
                    if ($colno == 2) {
                        $msl = trim($value)/100;
                    }
                    
                }
                $colno++;
            }
        }
        if ($rowCount >= 2 && trim($storeid) != "" && trim($msl) != "") {
            //validation check
            $iquery = "update stock_master_qty_wise set min_qty_allowed = min_qty_allowed + (min_qty_allowed * $msl),last_modified_by = $userid, update_time=now() where store_id=$storeid";
//            echo $iquery; 
            $db->execUpdate($iquery);
            
            $squery = "update stock_limit_ctg_wise set min_qty_ctg_wise = min_qty_ctg_wise + (min_qty_ctg_wise* $msl), max_qty_ctg_wise= max_qty_ctg_wise + (max_qty_ctg_wise * $msl),last_modified_by = $userid, update_time=now() where store_id=$storeid";
//            echo $squery; 
            $db->execUpdate($squery);
        }
        $rowCount++;
    }
}