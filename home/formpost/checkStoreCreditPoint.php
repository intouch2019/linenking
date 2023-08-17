<?php

require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once 'Classes/PHPExcel/IOFactory.php';
require_once "lib/serverChanges/clsServerChanges.php";
require_once "lib/core/strutil.php";
require_once 'lib/users/clsUsers.php';

//step 1:- checkfile fn reads the file n sends store info to checkForItems fn
//step 2:- checkForItems fn checks for whether items curr_qty is available
//step 3:- If items available it sends to fn createItems
//step 4:- createItems fn sends to saveOrder fn which calc avail tot order qty n amt
extract($_POST);
$dir = "../data/addcreditpoint/";


$db = new DBConn();
//$invtyp=$invoicetype;


$errors = array();
$success = "";
$err = "";
$err = "";
$totalcreditpoints=0;
$i=0;
$ozze_storecnt = 0;
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
    //print "Dir$newdir";

    if (move_uploaded_file($_FILES['file']['tmp_name'], $newdir)) {
        $err .= checkfile($newdir);
        if (trim($err) != "") {
            $errors['chkfile'] = $err;
//                $errors[]= $err;
        }
        if (count($errors) == 0) {
            //$success.= "File is Uploded Successfully";
            //$fname="turnoverdisc"   ;//link change to new tdcn
            //$fname="formpost/genTdCnNew.php?from=$startdate&to=$enddate";
            // $success = "File ($textname) is successfully uploaded for Qtr.:$qt1 of year $dated. </br><span>Please click here to</span><br/><a href='$fname' class='btn btn-primary btn-lg active' role='button'>Generate TD Credit Note</a>";
//            $success = "<div> File successfully uploaded and entries done</div>";
              $success =  "<div style='font-size:14px;background-color:white'> Total $ozze_storecnt Stores Credit Points Uploaded Successfully with Amount - $totalcreditpoints/-</div>";
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
header("Location: " . DEF_SITEURL . "addcreditpoint");
//header("Location: {$_SERVER['HTTP_REFERER']}");
exit;

function checkfile($newdir) {
    $db = new DBConn();
    $imltpflag = false;
    $itemfound = "";
    $resp = "";
    $objPHPExcel = PHPExcel_IOFactory::load($newdir);
    $objWorksheet = $objPHPExcel->getActiveSheet();
    $return = "";
    $first = 1;
    $code = '';
    $row = 0;
    $flg = 0;
    $rcnt = 1;
    $array_seq = array();
    foreach ($objWorksheet->getRowIterator() as $row) {
        if ($flg == 0) {
            $flg++;
            continue;
        }
//        print "<br>";
//        print_r($row);
//        print "<br>";
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false);
        $colno = 0;
        $rcnt++;
        $id = "";
        $store_name = "";
        $creditpoint = "";
        $remark = "";
        foreach ($cellIterator as $cell) {
            $value = trim(strval($cell->getValue()));
            //if(trim($value)!=""){
            if ($colno == 0) {
                $id = $value;
            }
            if ($colno == 1) {
                $store_name = $value;
            }
            if ($colno == 2) {
                $creditpoint = $value;
                if ($creditpoint == 0) {
                    $imltpflag = true;
                    $return = "Creditpoint should be not be blank or numeric,Kindly correct and upload again";
                }
                if (!is_numeric($creditpoint)) {
                    echo $creditpoint;
                    $imltpflag = true;
                    $return = "Creditpoint must be Numeric";
                }
                if ($creditpoint < 0){
                    $imltpflag = true;
                    $return = "Creditpoint can not be Negative";
                }
            }
            if ($colno == 3) {
                $remark = $value;
            }
            $colno++;
        }
    }
    if ($imltpflag == false) {
        $status = updatecreditpoints($newdir);
    }
    if (isset($status)) {
        $return = $status;
    }
    return $return;
}

/////////////////////////new addition

function updatecreditpoints($newdir) {
    $db = new DBConn();
    $itemfound = "";
    $resp = "";
    $objPHPExcel = PHPExcel_IOFactory::load($newdir);
    $objWorksheet = $objPHPExcel->getActiveSheet();
    $return = "";
    $first = 1;
    $code = '';
    $row = 0;
    $flg = 0;
    $rcnt = 1;
    $array_seq = array();
    //$i = 0;
    global $i;
    global $totalcreditpoints;
     global $ozze_storecnt;
    $i++;
    $credit_points_present = "";
    foreach ($objWorksheet->getRowIterator() as $row) {
        if ($flg == 0) {
            $flg++;
            continue;
        }
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false);
        $colno = 0;
        $rcnt++;
        $id = "";
        $store_name = "";
        $creditpoint = 0;
        $remark = "";
        foreach ($cellIterator as $cell) {
            $value = trim(strval($cell->getValue()));
            if (trim($value) != "") {
                if ($colno == 0) {
                    $id = $value;
                }
                if ($colno == 1) {
                    $store_name = $value;
                }
                if ($colno == 2) {
                    $creditpoint = $value;
                }
                if ($colno == 3) {
                $remark = $value;
            }
            }
            $colno++;
        }
        //print_r($array_seq);
      //  print "<br> ID: " . $id . " STORE NAME: " . $store_name . " CREDITPOINT: " . $creditpoint;
        if (trim($id) != "" && trim($store_name) != "" && trim($creditpoint) != "") {

            $no_space = str_replace(" ", "", $store_name);
            $store_name_db = $db->safe(trim($no_space));
            $check = " replace(store_name ,' ','') = $store_name_db ";
            $query = "select usertype from it_codes where id = $id and $check ";
//            print_r($query);
//            echo $query;
            $objcode = $db->fetchObject($query);

            if (!$objcode) {
                return "Incorrect Storename or StoreID at line no " . $i . " Kindly correct the file and upload again";
            }
            
            
//             $all_points_to_upload="select sum(points_to_upload) as ptu from it_store_redeem_points where store_id=$id and active =1;";
//             $total_points_upload=$db->fetchObject($all_points_to_upload);
//             
//            $all_points_used="select sum(rp.points_used) as pu from it_store_redeem_points r inner join it_store_redeem_points_partial rp on r.id=rp.it_store_redeem_points_id where store_id=$id and r.active =1;";
//             $total_points_used=$db->fetchObject($all_points_used);
//            
//
//             
//            if($total_points_upload->ptu != $total_points_used->pu){
//                $tott= $total_points_upload->ptu-$total_points_used->pu;
//                $credit_points_present.= "Store - $store_name already have $tott credit points available<br> <br>";
//                   
//               continue;
//            }
            
            
            $is_cp_used = $db->fetchObject("select sum(points_to_upload) as cu from it_store_redeem_points where store_id=$id and is_completely_used=0");
               
       if(isset($is_cp_used) && trim($is_cp_used->cu)){
           $credit_points_present .= "Store - $store_name already have credit points available<br> <br>";
           continue;
       }
            
             
//            if (isset($objcode) && $objcode->usertype == 4) {
//                $query = "INSERT INTO it_store_redeem_points (store_id,points_to_upload,remark,points_upload_date)VALUES ($id,$creditpoint,'$remark',now()); ";
//                $objredeem = $db->execInsert($query);
//                //$i++;
//                $totalcreditpoints+=$creditpoint;
//                $ii+=count($id);
//                $i=$ii;
//     
//                
//                $return = "values inserted successfully";
//            }
        }
    }
    
     if(isset($credit_points_present) && $credit_points_present != ""){
       return $credit_points_present;
    }
    
    
    foreach ($objWorksheet->getRowIterator() as $row) {
        if ($flg == 0) {
            $flg++;
            continue;
        }
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false);
        $colno = 0;
        $rcnt++;
        $id = "";
        $store_name = "";
        $creditpoint = 0;
         $remark = "";
        foreach ($cellIterator as $cell) {
            $value = trim(strval($cell->getValue()));
            if (trim($value) != "") {
                if ($colno == 0) {
                    $id = $value;
                }
                if ($colno == 1) {
                    $store_name = $value;
                }
                if ($colno == 2) {
                    $creditpoint = $value;
                }
                if ($colno == 3) {
                $remark = $value;
            }
            }
            $colno++;
        }
        //print_r($array_seq);
//        print "<br> ID: " . $id . " STORE NAME: " . $store_name . " CREDITPOINT: " . $creditpoint;
        if (trim($id) != "" && trim($store_name) != "" && trim($creditpoint) != "") {
            if($id=='ID'){
                continue;
            }

//            $no_space = str_replace(" ", "", $store_name);
//            $store_name_db = $db->safe(trim($no_space));
//            $check = " replace(store_name ,' ','') = $store_name_db ";
//            $query = "select usertype from it_codes where id = $id and $check ";
//            $objcode = $db->fetchObject($query);
//
//            if (!$objcode) {
//                return "Incorrect Storename or StoreID at line no " . $i . " Kindly correct the file and upload again";
//            }
            
//            
//             $all_points_to_upload="select sum(points_to_upload) as ptu from it_store_redeem_points where store_id=$id and active =1;";
//             $total_points_upload=$db->fetchObject($all_points_to_upload);
             
//            $all_points_used="select sum(rp.points_used) as pu from it_store_redeem_points r inner join it_store_redeem_points_partial rp on r.id=rp.it_store_redeem_points_id where store_id=$id and r.active =1;";
//             $total_points_used=$db->fetchObject($all_points_used);
            
           
            
//            if($total_points_upload->ptu != $total_points_used->pu){
//                $tott= $total_points_upload->ptu-$total_points_used->pu;
//                $credit_points_present.= "Store - $store_name already have $tott credit points available<br> <br>";
//                   
//               continue;
//            }
            

            if (isset($objcode) && $objcode->usertype == 4) {
                $query = "INSERT INTO it_store_redeem_points (store_id,points_to_upload,remark,points_upload_date)VALUES ($id,$creditpoint,'$remark',now()); ";
                $objredeem = $db->execInsert($query);
//                $i++;
                $totalcreditpoints+=$creditpoint;
                $ii+=count($id);
                $i=$ii;
                 $ozze_storecnt++;
                $return = "values inserted successfully";
            }
        }
    }
    
    
     $serverCh = new clsServerChanges();
      $objj1 = $db->fetchObjectArray("select id, store_id,points_to_upload from it_store_redeem_points where is_sent=0 and active=1");
       
       $credit_point = array();
       $credit_points =array();
       $item=array();
       $workorderno=0;
                foreach ($objj1 as $obj1){
                    $item['server_id']=intval($obj1->id);
                    $item['store_id']=intval($obj1->store_id);
                    $item['points_to_upload'] = intval($obj1->points_to_upload);
                    $credit_point[] = json_encode($item);
                   
                   $cnt++;
                }
//                $wip_stockdata['work_order_no']=$workorderno;
                 $credit_points['items']=json_encode($credit_point);
                 $server_ch = json_encode($credit_points); 
                             $CKWHStoreid = DEF_CK_WAREHOUSE_ID;
                             $ser_type = changeType::crditPoints;   
                             $serverCh->save($ser_type, $server_ch,$CKWHStoreid,$workorderno);
                             
                             $sql="update it_store_redeem_points set is_sent=1";
                             $db->execUpdate($sql);
   
    $db->closeConnection();
    //unset($array_seq);
}

?>