<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once 'Classes/PHPExcel/IOFactory.php';

// the file should be a csv file

$dir = "../data/";

$errors = array();
$success = "";
$resp = "";
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
      $tmp = explode(".",$textname);
      $fileext = end($tmp);
      if ($fileext != "csv") {
            $errors['file'] = 'Please upload an CSV file containing the stock information';
      }
      if ($textnamediv[0]) {$name=$textnamediv[0]; } else {$name=$textname; }
      $newname = $date.".Stock.".$storeid."."."$name".".$ext";
      $newdir = $dir.$newname;
        if (move_uploaded_file($_FILES['file']['tmp_name'], $newdir)) {
            //$success = "File is valid .<br/>";
            if(count($errors)==0){
                $resp .= checkfile($newdir);
//                print $resp;
                $arr = explode('::',$resp);
                if($arr[0]==0){
                  $errors[]  = $arr[1];
                }else{
                    $success .= $arr[1];
                }
            }
        } else {
            $errors[]= "The file failed to upload";
        }
//        print_r($errors);
  }
  if (count($errors)>0) {
        unset($_SESSION['form_success']);
        unset($_SESSION['fpath']);
        unset($_SESSION['stockuploaded']);
        $_SESSION['form_errors'] = $errors;
  } else {
        unset($_SESSION['form_errors']);
        $_SESSION['form_success'] = $success;
        $_SESSION['fpath']=$newdir;
        unset($_SESSION['stockuploaded']);
  }

session_write_close();
header("Location: ".DEF_SITEURL."admin/stock/upload");
exit;

function checkFile($newfile){
    $commit = false;
    $errflg=0;
    $resp="";
    $fresp = "";
    $fh = fopen($newfile, "r");
    if (!$fh) { //print "File not found\n"; 
        $fresp .= "0::File not found";
    return; }
    //$fresp = "1::";
    $db=new DBConn();

    $rowno=0;$tot_qty=0;$skip_qty=0;
//    if ($commit) $db->execUpdate("update it_items set curr_qty = 0, updatetime=now()");
//    $error="Error:";

    while(($row=fgetcsv($fh,0,"\t")) !== FALSE) {
    $colno=0;
    $item_code=false; 
    $item_code=trim($row[0]);
    $curr_qty=intval(trim($row[1]));
            $item = $db->fetchObject("select * from it_items where barcode='$item_code'");
            if ($item) {
                    //if ($commit) $db->execUpdate("update it_items set curr_qty=$curr_qty, updatetime=now() where id=$item->id");
                    $tot_qty += $curr_qty;
            } else {
                   $errflg=1;
                    //print "Item not found:$item_code\n";
                   $fresp .= "New barcode found:$item_code<br/>";
                    $skip_qty += $curr_qty;
            }
    $rowno++;
    }
    $fresp .= "<br/>Total Qty=$tot_qty<br/>Skipped Qty=$skip_qty";
    if($errflg==1){ // means error occured
        $resp = "0::".$fresp."<br/> Please Upload a Valid file and try again later";
    }else{
        $resp = "1::".$fresp;
    }
    fclose($fh);
    //return $fresp;
    return $resp;
}
?>