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

extract($_POST);

$dir = "../data/returnGarment/";
//print_r($_POST);
//exit();

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
    $newname = $date . ".ReturnGarmentDesignExcel." . $storeid . "." . "$name" . ".$ext";
    $newdir = $dir . $newname;

    if (move_uploaded_file($_FILES['file']['tmp_name'], $newdir)) {
        $err .= checkfile($newdir);
//        echo "file chekced";
//        exit();
//        
        
        if (trim($err) != "") {
            $errors['chkfile'] = $err;
//                $errors[]= $err;
        }
        if (count($errors) == 0) {
            $success .= "File is valid";
        }
    } else {
        $errors['file'] = "The file failed to upload";
    }
}
//if (count($errors) > 0) {
//    unset($_SESSION['form_success']);
//    unset($_SESSION['fpath']);
//    unset($_SESSION['returngarment']);
//    $_SESSION['form_errors'] = $errors;
//} else {
//    unset($_SESSION['form_errors']);
//    $_SESSION['form_success'] = $success;
//    $_SESSION['fpath'] = $newdir;
//    unset($_SESSION['returngarment']);
//}
//
//session_write_close();
//header("Location: " . DEF_SITEURL . "return/garment");
//exit;

if (!isset($filename) && trim($filename) == "") {
    $errors['file'] = "File not found";
} else {
    $commit = true;
} 
//print_r($errors);
//exit();

if (count($errors) == 0) {
    $db = new DBConn();

//    $err = checkfile($filename);
//    print "ERR: $err";
//    if (trim($err) == "") {
        updateSeq($newdir);
        $success = " ";
//    }
}

if (count($errors) > 0) {
    unset($_SESSION['form_success']);
    unset($_SESSION['fpath']);
    $_SESSION['form_errors'] = $errors;
} else {
    unset($_SESSION['form_errors']);
    unset($_SESSION['fpath']);
    $_SESSION['form_success'] = $success;
    $_SESSION['returngarment'] = "done";
    
}

session_write_close();
header("Location: " . DEF_SITEURL . "return/garment");
exit;

function checkfile($newdir) {
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

    foreach ($objWorksheet->getRowIterator() as $row) {

        if ($flg == 0) {
            $flg++;
            continue;
        }
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(true);
        $colno = 0;
        $rcnt++;
        $id = "";
        $barcode = "";
        $sequence = "";
        $unique = uniqid();

        foreach ($cellIterator as $cell) {

            $value = trim(strval($cell->getValue()));
            if (trim($value) == null) {
                if ($colno == 0) {
                    $barcode = $value;
                }
            }
            if (trim($value) != "") {
                if ($colno == 0) {
                    $barcode = $value;
                }
            }
            $colno++;
            if (trim($barcode) != "") {
                //validation chk
                $no_space = str_replace(" ", "", $barcode);
                $query = "select * from it_items where barcode = $barcode";
                $obj = $db->fetchObject($query);
                if (isset($obj)) {
                    //do nothing
                } else {

                    $return .= "<br>Invalid barcode found at row $rcnt. Please upload correct excel";
                }
            }
        }
    }
    //unset($array_seq);
    return $return;
}

function updateSeq($newdir) {
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
    $dsgndata = array();

    //reset all previous sequence

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
        $sequence = "";
        foreach ($cellIterator as $cell) {
            $value = trim(strval($cell->getValue()));
            if (trim($value) == null) {
                if ($colno == 0) {
                    echo $value;
                    $barcode = $value;
                }
            }
            if (trim($value) != "") {
                if ($colno == 0) {
                    $barcode = $value;
                }
            }
            $colno++;
        }

        if (trim($barcode) != "") {
            //validation chk
        
            $barcode_db = $db->safe(trim($barcode));
             $productgroup = $_POST['productgroup'];
             
            if ($productgroup == 1 || $productgroup == 5) {
                $query = "select i.design_no,s.name as sz_name , s.wip_shirt_id as wip_szid from it_items i join it_sizes s on i.size_id=s.id where i.barcode=$barcode ";
//                $query = "select i.design_no, i.size_id ,s.wip_shirt_id as wip_szid from it_items i join it_sizes s on i.size_id=s.id where barcode=$barcode ";
            } else if($productgroup == 4)
            {
               $query = "select i.design_no,s.name as sz_name , s.wip_salwarpyjama_id as wip_szid from it_items i join it_sizes s on i.size_id=s.id where i.barcode=$barcode "; 
            }
            else{
                $query = "select i.design_no,s.name as sz_name , s.wip_trouser_id as wip_szid from it_items i join it_sizes s on i.size_id=s.id where i.barcode=$barcode ";
//                $query = "select i.design_no, i.size_id ,s.wip_trouser_id as wip_szid from it_items i join it_sizes s on i.size_id=s.id where barcode=$barcode ";
            }
//             print_r($query);
//              exit();
            $obj = $db->fetchObject($query);
            if (isset($obj)) {
                
                $szname=$obj->sz_name;
                $szid = $obj->wip_szid;
//                $key = "$obj->design_no" . "_" . "$obj->size_id";
                
                $key = "$obj->design_no" . "_" . "$szname" . "_" . "$szid";

                if (array_key_exists($key, $dsgndata)) {
                    if (isset($dsgndata[$key])) {
                        $dsgndatakey = $dsgndata[$key];
                        if (isset($dsgndata[$key]) && $dsgndata[$key] != "") {
                            $dsgndata[$key]++;
                        }
                    }
                } else {
//                    if (!isset($dsgndata[$key]) && $dsgndata[$key] == "") {
                    $dsgndata[$key] = 1;

//                    }
                }
            }
        }
    }
    generateDesignExcel($dsgndata);
//    exit();
    $db->closeConnection();
    //unset($array_seq);
}

function generateDesignExcel($dsgndata) {
    $db = new DBConn();
    $sheetIndex = 0;
    $cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_in_memory_serialized;
    $cacheSettings = array('memoryCacheSize' => '1500MB');
    PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
    // Create new PHPExcel object
    $objPHPExcel = new PHPExcel();
    // Create a first sheet
    $objPHPExcel->setActiveSheetIndex($sheetIndex);
    $objPHPExcel->getActiveSheet()->setTitle('Return Garment Design Excel');
//    $objPHPExcel->getActiveSheet()->setCellValue('A1', 'ID');
    $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Design No');
    $objPHPExcel->getActiveSheet()->setCellValue('B1', 'Size Name');
     $objPHPExcel->getActiveSheet()->setCellValue('C1', 'Size ID');
    $objPHPExcel->getActiveSheet()->setCellValue('D1', 'Quantity');
    $objPHPExcel->getActiveSheet()->setCellValue('E1', 'Product Group');
    

    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);

    $styleArray = array(
        'font' => array(
            'bold' => false,
            //        'color' => array('rgb' => 'FF0000'),
            'size' => 12,
    ));
    $objPHPExcel->getActiveSheet()->getStyle('A')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('B')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('C')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('D')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('E')->applyFromArray($styleArray);
    

    $colCount = 0;
    $rowCount = 2;

    foreach ($dsgndata as $x => $x_qty) {


        $designdata = explode("_", $x);

        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $rowCount, $designdata[0]);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $rowCount, $designdata[1]);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $rowCount, $designdata[2]);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $rowCount, $x_qty);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $rowCount, $_POST['productgroup']);

        $rowCount++;
    }

$uniqueexcelname= date("Y-m-d H:i:s");
// Redirect output to a client’s web browser (Excel5)
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="ReturnGarment_Barcode_"'.$uniqueexcelname.'".xls"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->save('php://output');
}