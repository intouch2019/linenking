<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once 'Classes/PHPExcel/IOFactory.php';
require_once "lib/core/strutil.php";
require_once 'lib/users/clsUsers.php';


$dir = "../data/DiscScheme/";
extract($_POST);


$refdatequery = "";


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
      $newname = $date."$name".".$ext";
      $newdir = $newname;
      //print "Dir$newdir";

        if (move_uploaded_file($_FILES['file']['tmp_name'], $newdir)) {           
            $err .= checkfile($newdir);
            if(trim($err)!=""){
                $errors['chkfile']= $err;
//                $errors[]= $err;
            }
            if(count($errors)==0){
             //$success.= "File is Uploded Successfully";
                //$fname="discscheme/creditnote";
                $fname="formpost/genpdfDSCN.php?taxpct=$taxpct";
                //$success = "Store(s) discount scheme successfully updated. </br><a href='$fname' class='btn btn-primary btn-lg active' role='button'>Generate DS Credit Note</a>";
                $success = "File ($textname) is successfully uploaded</br><span>Please click here to</span><br/><a href='$fname' class='btn btn-primary btn-lg active' role='button'>Generate DS Credit Note</a>";
                unlink($newdir);
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
header("Location: ".DEF_SITEURL."discscheme");
//header("Location: {$_SERVER['HTTP_REFERER']}");
exit;

function checkfile($newdir){
    
    $db = new DBConn();
    $imltpflag=false;
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
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false);
        $colno=0;
        $rcnt++;
        $id = ""; $store_name="";$ds_taxable = "";$remark="";
        foreach ($cellIterator as $cell) {            
            $value = trim(strval($cell->getValue()));
            //if(trim($value)!=""){
                if($colno==0){
                    $id = $value;
                }
                if($colno==1){
                    $store_name = $value;
                }
                if($colno==2){
                    //$ds_taxable = ""
                    $ds_taxable = $value;
                    if($ds_taxable=="")
                    {
                        $imltpflag=true;
                        $return ="Discount scheme taxable amount should be not blank,Kindly correct and upload again";
                        //return $return;
                    }
                    //print "inside check done$ds_taxable";
                    if($ds_taxable<=0)
                    {
                        //print "$ds_taxable";
                        $imltpflag=true;
                        $return ="Discount scheme taxable amount should be >0,Kindly correct and upload again";
                        
                    }
                    else {
                            
                             updateIncentives($newdir);
                        }
                }
                if($colno==3){
                    $remark = $value;
                }
            //}
           
            $colno++;
        }

            
    }
  
    return $return;
}


/////////////////////////new addition


function updateIncentives($newdir){
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
    $sq = "update it_codes set ds_taxable_amt = null ";
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
        $id = ""; $store_name="";$remark = "";$intper=0.0;
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
                    $intper = $value;
                }
                if($colno==3){
                    $remark = $value;
                }
            }
           
            $colno++;
        }
         //print_r($array_seq);
           // print "<br> ID: ".$id." STORE NAME: ".$store_name." SEQUENCE: ".$sequence;
            if(trim($id)!="" && trim($store_name)!=""){
                //validation chk
                $no_space = str_replace(" ", "", $store_name);
                $store_name_db = $db->safe(trim($no_space));
                $check = " replace(store_name ,' ','') = $store_name_db ";
                $query = "select * from it_codes where id = $id and $check ";
                $obj = $db->fetchObject($query);
                if(isset($obj)){
                    if($remark=="")
                    {
                        $uquery = "update it_codes set ds_taxable_amt = $intper,ds_remark='' where id = $obj->id ";
                    }
                    else {
                        $uquery = "update it_codes set ds_taxable_amt = $intper,ds_remark='$remark' where id = $obj->id ";
                    }
                    
  
                  //print "<br> $uquery";
                    $db->execUpdate($uquery);
                }
            }
            
    }
    $db->closeConnection();
    //unset($array_seq);
    
}
?>