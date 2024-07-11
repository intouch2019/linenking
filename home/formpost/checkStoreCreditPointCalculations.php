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
$dir = "../data/cp/calculations";

$db = new DBConn();

$errors = array();
$success = "";
$err = "";
$i = 0;
$resp = "";
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
header("Location: " . DEF_SITEURL . "cp/calculations");
exit;

function checkfile($newdir) {
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
        $p12s12 = "";
        $p12s5 = "";
        $p5s5 = "";
        $srNo = "";
        $rowLabels = "";
        $storeId = "";
        $cpheading = "";
        $dealerMargin = "";
        $schemeDiscount = "";
        $mrpSale = "";
        $saleWoDiscount = "";
        $discount = "";
        $totalvalue = "";

        foreach ($cellIterator as $cell) {

            if ($rowCount == 1) {     //check column name at 1st row
                $value = trim(strval($cell->getValue()));
                if ($colno == 6) {

                    $p12s12 = $value;
                    if ($p12s12 != "P12 S12") {
                        $return = "Column name $value does not match";
                    }
                }
                if ($colno == 10) {
                    $p12s5 = $value;
                    if ($p12s5 != "P12 S5") {
                        $return = "Column name $value does not match";
                    }
                }
                if ($colno == 14) {
                    $p5s5 = $value;
                    if ($p5s5 != "P5 S5") {
                        $return = "Column name $value does not match";
                    }
                }
                $colno++;
            } elseif ($rowCount == 2) {   //check column name at 2nd row
                $value = trim(strval($cell->getValue()));
                if ($colno == 0) {
                    $srNo = $value;
                    if ($srNo != "Sr No") {
                        $return = "Column name $value does not match";
                    }
                }
                if ($colno == 1) {
                    $rowLabels = $value;
                    if ($rowLabels != "Row Labels") {
                        $return = "Column name $value does not match";
                    }
                }
                if ($colno == 2) {
                    $storeId = $value;
                    if ($storeId != "Store ID") {
                        $return = "Column name $value does not match";
                    }
                }
                if ($colno == 3) {
                    $cpheading = $value;
                    if ($cpheading != "Credit Point Heading") {
                        $return = "Column name $value does not match";
                    }
                }
                if ($colno == 4) {
                    $dealerMargin = $value;
                    if ($dealerMargin != "Dealer Margin") {
                        $return = "Column name $value does not match";
                    }
                }
                if ($colno == 5) {
                    $schemeDiscount = $value;
                    if ($schemeDiscount != "Scheme Discount") {
                        $return = "Column name $value does not match";
                    }
                }
                if ($colno == 6) {
                    $mrpSale = $value;
                    if ($mrpSale != "MRP Sale") {
                        $return = "Column name $value does not match";
                    }
                }
                if ($colno == 7) {
                    $saleWoDiscount = $value;
                    if ($saleWoDiscount != "Sale Without Discount") {
                        $return = "Column name $value does not match";
                    }
                }
                if ($colno == 8) {
                    $discount = $value;
                    if ($discount != "Discount") {
                        $return = "Column name $value does not match";
                    }
                }
                if ($colno == 9) {
                    $totalvalue = $value;
                    if ($totalvalue != "Total Value") {
                        $return = "Column name $value does not match";
                    }
                }
                if ($colno == 10) {
                    $mrpSale = $value;
                    if ($mrpSale != "MRP Sale") {
                        $return = "Column name $value does not match";
                    }
                }
                if ($colno == 11) {
                    $saleWoDiscount = $value;
                    if ($saleWoDiscount != "Sale Without Discount") {
                        $return = "Column name $value does not match";
                    }
                }
                if ($colno == 12) {
                    $discount = $value;
                    if ($discount != "Discount") {
                        $return = "Column name $value does not match";
                    }
                }
                if ($colno == 13) {
                    $totalvalue = $value;
                    if ($totalvalue != "Total Value") {
                        $return = "Column name $value does not match";
                    }
                }
                if ($colno == 14) {
                    $mrpSale = $value;
                    if ($mrpSale != "MRP Sale") {
                        $return = "Column name $value does not match";
                    }
                }
                if ($colno == 15) {
                    $saleWoDiscount = $value;
                    if ($saleWoDiscount != "Sale Without Discount") {
                        $return = "Column name $value does not match";
                    }
                }
                if ($colno == 16) {
                    $discount = $value;
                    if ($discount != "Discount") {
                        $return = "Column name $value does not match";
                    }
                }
                if ($colno == 17) {
                    $totalvalue = $value;
//                    print_r($value);    exit();
                    if ($totalvalue != "Total Value") {
                        $return = "Column name $value does not match";
                    }
                }
                $colno++;
            }
        }
        $rowCount++;
        //check empty fields in excel sheet
        if ($rowCount > 2) {  
            foreach ($cellIterator as $cell) {
                $value = trim(strval($cell->getValue()));
                if ($colno == 0) {
                    $srNo = $value;
                    if ($srNo == "") {
                        $imltpflag = true;
                        $return = "Empty field in *Sr No* Column";
                    }
                }
                if ($colno == 1) {
                    $rowLabels = $value;
                    if ($rowLabels == "") {
                        $imltpflag = true;
                        $return = "Empty field in *Row Labels* Column";
                    }
                }
                if ($colno == 2) {
                    $storeId = $value;
                    if ($storeId == "") {
                        $imltpflag = true;
                        $return = "Empty field in *Store ID* Column";
                    }
                }
                if ($colno == 3) {
                    $cpheading = $value;
                    if ($cpheading == "") {
                        $imltpflag = true;
                        $return = "Empty field in *Credit Point Heading* Column";
                    }
                }
                if ($colno == 4) {
                    $dealerMargin = $value;
                    if ($dealerMargin == "") {
                        $imltpflag = true;
                        $return = "Empty field in *Dealer Margin* Column";
                    }
                }
                if ($colno == 5) {
                    $schemeDiscount = $value;
                    if ($schemeDiscount == "") {
                        $imltpflag = true;
                        $return = "Empty field in *Scheme Discount* Column";
                    }
                }
                if ($colno == 6) {
                    $mrpSale = $value;
                    if ($mrpSale == "") {
                        $imltpflag = true;
                        $return = "Empty field in *P12 S12-> MRP Sale* Column";
                    }
                }
                if ($colno == 7) {
                    $saleWoDiscount = $value;
                    if ($saleWoDiscount == "") {
                        $imltpflag = true;
                        $return = "Empty field in *P12 S12-> Sale Without Discount* Column";
                    }
                }
                if ($colno == 8) {
                    $discount = $value;
                    if ($discount == "") {
                        $imltpflag = true;
                        $return = "Empty field in *P12 S12-> Discount* Column";
                    }
                }
                if ($colno == 9) {
                    $totalvalue = $value;
                    if ($totalvalue == "") {
                        $imltpflag = true;
                        $return = "Empty field in *P12 S12-> Total Value* Column";
                    }
                }
                if ($colno == 10) {
                    $mrpSale = $value;
                    if ($mrpSale == "") {
                        $imltpflag = true;
                        $return = "Empty field in *P12 S5-> MRP Sale* Column";
                    }
                }
                if ($colno == 11) {
                    $saleWoDiscount = $value;
                    if ($saleWoDiscount == "") {
                        $imltpflag = true;
                        $return = "Empty field in *P12 S5-> Sale Without Discount* Column";
                    }
                }
                if ($colno == 12) {
                    $discount = $value;
                    if ($discount == "") {
                        $imltpflag = true;
                        $return = "Empty field in *P12 S5-> Discount* Column";
                    }
                }
                if ($colno == 13) {
                    $totalvalue = $value;
                    if ($totalvalue == "") {
                        $imltpflag = true;
                        $return = "Empty field in *P12 S5-> Total Value* Column";
                    }
                }
                if ($colno == 14) {
                    $mrpSale = $value;
                    if ($mrpSale == "") {
                        $imltpflag = true;
                        $return = "Empty field in *P5 S5-> MRP Sale* Column";
                    }
                }
                if ($colno == 15) {
                    $saleWoDiscount = $value;
                    if ($saleWoDiscount == "") {
                        $imltpflag = true;
                        $return = "Empty field in *P5 S5-> Sale Without Discount* Column";
                    }
                }
                if ($colno == 16) {
                    $discount = $value;
//                    print_r($discount); exit();
                    if ($discount == "") {
                        $imltpflag = true;
                        $return = "Empty field in *P5 S5-> Discount* Column";
                    }
                }
                if ($colno == 17) {
                    $totalvalue = $value;
//                    print_r($totalvalue); exit();
                    if ($totalvalue == "") {
                        $imltpflag = true;
                        $return = "Empty field in *P5 S5-> Total Value* Column";
                    }
                }
                
                if ($colno == 18) {
                    $nonschemesalevalue = $value;
                    if ($nonschemesalevalue == "") {
                        $imltpflag = true;
                        $return = "Empty field in *P5 S5-> Non Scheme Sale Value* Column";
                    }
                }
                $colno++;
            }
            $store_count++;
        }
    }
    if ($store_count == 0) {
        $imltpflag = true;
        $return = "File is Empty";
    }
    if ($imltpflag == false) {
        $status = insertCpFile($newdir);
    }
    if (isset($status)) {
        $return = $status;
    }

    return $return;
}

//Insert data into db
function insertCpFile($newdir) {
    $db = new DBConn();
    $objPHPExcel = PHPExcel_IOFactory::load($newdir);
    $objWorksheet = $objPHPExcel->getActiveSheet();
    $row = 0;
    $rowCount = 1;

    foreach ($objWorksheet->getRowIterator() as $row) {
        
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false);
        $colno = 0;
        $srNo = "";
        $rowLabels = "";
        $storeId = "";
        $cpheading = "";
        $dealerMargin = "";
        $schemeDiscount = "";
        $mrpSale = "";
        $saleWoDiscount = "";
        $discount = "";
        $totalvalue = "abc";
        $mrpSale_p12s12 = "";
        $saleWoDiscount_p12s12 = "";
        $discount_p12s12 = "";
        $totalvalue_p12s12 = "";
        $mrpSale_p12s5 = "";
        $saleWoDiscount_p12s5 = "";
        $discount_p12s5 = "";
        $totalvalue_p12s5 = "";
        $mrpSale_p5s5 = "";
        $saleWoDiscount_p5s5 = "";
        $discount_p5s5 = "";
        $totalvalue_p5s5 = "";
        $nonschemesalevalue = "";
        $iquery = "";
        
        if ($rowCount > 2) {
            foreach ($cellIterator as $cell) {

                $value = trim(strval($cell->getValue()));
                if (trim($value) != "") {
                    if ($colno == 0) {
                        $srNo = $db->safe(trim($value));
                    }
                    if ($colno == 1) {
                        $rowLabels = $db->safe(trim($value));
                    }
                    if ($colno == 2) {
                        $storeId = $db->safe(trim($value));
                    }
                    if ($colno == 3) {
                        $cpheading = $db->safe(trim($value));
                    }
                    if ($colno == 4) {
                        $dealerMargin = $db->safe(trim($value));
                    }
                    if ($colno == 5) {
                        $schemeDiscount = $db->safe(trim($value));
                    }
                    if ($colno == 6) {
                        $mrpSale = $db->safe(trim($value));
                        $mrpSale_p12s12 = $mrpSale;
                    }
                    if ($colno == 7) {
                        $saleWoDiscount = $db->safe(trim($value));
                        $saleWoDiscount_p12s12 = $saleWoDiscount;
                    }
                    if ($colno == 8) {
                        $discount = $db->safe(trim($value));
                        $discount_p12s12 = $discount;
                        // print_r($discount_p12s12); exit();
                    }
                    if ($colno == 9) {
                        $totalvalue = $db->safe(trim($value));
                        $totalvalue_p12s12 = $totalvalue;
//                    print_r($totalvalue_p12s12); exit();
                    }
                    if ($colno == 10) {
                        $mrpSale = $db->safe(trim($value));
                        $mrpSale_p12s5 = $mrpSale;
                    }
                    if ($colno == 11) {
                        $saleWoDiscount = $db->safe(trim($value));
                        $saleWoDiscount_p12s5 = $saleWoDiscount;
                    }
                    if ($colno == 12) {
                        $discount = $db->safe(trim($value));
                        $discount_p12s5 = $discount;
                    }
                    if ($colno == 13) {
                        $totalvalue = $db->safe(trim($value));
                        $totalvalue_p12s5 = $totalvalue;
//                     print_r($totalvalue_p12s5); exit();
                    }
                    if ($colno == 14) {
                        $mrpSale = $db->safe(trim($value));
                        $mrpSale_p5s5 = $mrpSale;
                    }
                    if ($colno == 15) {
                        $saleWoDiscount = $db->safe(trim($value));
                        $saleWoDiscount_p5s5 = $saleWoDiscount;
                    }
                    if ($colno == 16) {
                        $discount = $db->safe(trim($value));
                        $discount_p5s5 = $discount;
                    }
                    if ($colno == 17) {
                        $totalvalue = $db->safe(trim($value));
                        $totalvalue_p5s5 = $totalvalue;
                    }
                     if ($colno == 18) {
                        $nonschemesalevalue = $db->safe(trim($value));

                    }
                }
                $colno++;
            }
        }
        if ($rowCount > 2 && trim($srNo) != "" && trim($rowLabels) != "" && trim($storeId) != "" && trim($cpheading) != "" && trim($dealerMargin) != "" && trim($schemeDiscount) != "" && trim($mrpSale) != "" && trim($saleWoDiscount) != "" && trim($discount) != "" && trim($totalvalue) != "") {
            //validation check
            $iquery = "Insert into cp_calculations set Sr_No = $srNo , Row_Labels = $rowLabels, Store_ID = $storeId, Credit_Point_Heading = $cpheading, Dealer_Margin = $dealerMargin, 
                Scheme_Discount = $schemeDiscount, MRP_Sale_p12_s12 = $mrpSale_p12s12, Sale_Without_Discount_p12_s12 = $saleWoDiscount_p12s12, Discount_p12_s12 = $discount_p12s12, 
                Total_Value_p12_s12 = $totalvalue_p12s12, MRP_Sale_p12_s5 = $mrpSale_p12s5, Sale_Without_Discount_p12_s5 = $saleWoDiscount_p12s5, Discount_p12_s5 = $discount_p12s5, 
                Total_Value_p12_s5 = $totalvalue_p12s5, MRP_Sale_p5_s5 = $mrpSale_p5s5, Sale_Without_Discount_p5_s5 = $saleWoDiscount_p5s5, Discount_p5_s5 = $discount_p5s5, 
                Total_Value_p5_s5 = $totalvalue_p5s5,non_scheme_sale=$nonschemesalevalue";
            //    print_r($iquery); exit();
            $db->execInsert($iquery);
        }
        $rowCount++;
    }
}