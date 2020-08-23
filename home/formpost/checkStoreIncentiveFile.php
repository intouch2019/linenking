<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once 'Classes/PHPExcel/IOFactory.php';

require_once "lib/core/strutil.php";
require_once 'lib/users/clsUsers.php';

//step 1:- checkfile fn reads the file n sends store info to checkForItems fn
//step 2:- checkForItems fn checks for whether items curr_qty is available
//step 3:- If items available it sends to fn createItems
//step 4:- createItems fn sends to saveOrder fn which calc avail tot order qty n amt
extract($_POST);
$dir = "../data/turnoverDisc/";
$startdate =$from;
$enddate = $to;

$db = new DBConn();
//$invtyp=$invoicetype;
$qt1date = yymmdd($from);
$qt2date = yymmdd($to);
$parts = explode('-', $qt1date);
$yr = $parts[0];
$qtrquery = "SELECT QUARTER('$qt1date') as qt1";
$qtr2query = "SELECT QUARTER('$qt1date') as qt2";
$qt1obj = $db->fetchObject($qtrquery);
$qt2obj = $db->fetchObject($qtr2query);
$qt1 = $qt1obj->qt1;
$qt2 = $qt2obj->qt2;

$refdatequery = "";



if ($qt1 == 1) {
    //$refdatequery = "and invoice_dt>='" . ($yr - 1) . "-11-01' and invoice_dt<='$yr-03-31'";
    $qt1=4;
    $dated=($yr - 1)."-".$yr;
} else if ($qt1 == 2) {
    //$refdatequery = "and invoice_dt>='$yr-01-01' and invoice_dt<='$yr-06-30'";
    $qt1=1;
    $dated=$yr."-".($yr+1);
    //$dated="$yr-06-30";
} else if ($qt1 == 3) {
    //$refdatequery = "and invoice_dt>='$yr-03-01' and invoice_dt<='$yr-09-30'";
    $qt1=2;
    $dated=$yr."-".($yr+1);
    //$dated="$yr-09-30";
} else {
    //$refdatequery = "and invoice_dt>='$yr-06-01' and invoice_dt<='$yr-12-31'";
    $qt1=3;
    $dated=$yr."-".($yr+1);
    //$dated="$yr-12-31";
}

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
                //$fname="turnoverdisc";
                $fname="formpost/genpdfTDCN.php?from=$startdate&to=$enddate";
               // $success = "File ($textname) is successfully uploaded for Qtr.:$qt1 of year $dated. </br><span>Please click here to</span><br/><a href='$fname' class='btn btn-primary btn-lg active' role='button'>Generate TD Credit Note</a>";
                $success = "File ($textname) is successfully uploaded for Qtr.:$qt1 of year $dated. </br><span>Please click here to</span><br/><form action=''><input type='button' value='Generate TD Credit Note' onclick=downloadpdf('$fname'); ></form>";
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
header("Location: ".DEF_SITEURL."turnoverdisc");
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
//        print "<br>";
//        print_r($row);
//        print "<br>";
        
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false);
        $colno=0;
        $rcnt++;
        $id = ""; $store_name="";$incentive_multiplier = "";$remark="";
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
                    //$incentive_multiplier = ""
                    $incentive_multiplier = $value;
                    
                    if($incentive_multiplier==0)
                    {
                        $imltpflag=true;
                        $return ="Incentive multiplier should be not be blank,Kindly correct and upload again";
                    }
                    if($incentive_multiplier<0 or $incentive_multiplier>2)
                    {
                        //print "$incentive_multiplier";
                        $imltpflag=true;
                        $return ="Incentive multiplier should be <=2,Kindly correct and upload again";
                        
                    }
                    else {
                             updateIncentives($newdir);
                        }
                }
                if($colno==3){
                    $remark = $value;
                }
            }
           
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
    $sq = "update it_codes set incentive_percent = null ";
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
                        $uquery = "update it_codes set incentive_percent = $intper,remark='' where id = $obj->id ";
                    }
                    else {
                        $uquery = "update it_codes set incentive_percent = $intper,remark='$remark' where id = $obj->id ";
                    }
                    
//  
                  //print "<br> $uquery";
                    $db->execUpdate($uquery);
                }
            }
            
    }
    $db->closeConnection();
    //unset($array_seq);
    
}
?>