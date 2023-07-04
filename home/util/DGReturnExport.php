<?php

require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "Classes/PHPExcel.php";
require_once 'Classes/PHPExcel/Writer/Excel2007.php';

$db = new DBConn();
$dQuery = isset($_GET['dtrange']) && trim($_GET['dtrange']) != "" ? $_GET['dtrange'] : false;
$storeid = isset($_GET['storeid']) && trim($_GET['storeid']) != "" ? $_GET['storeid'] : false;
//exit();
//$cnt=0;
//$dealersList = array();
//print_r($_GET['storeid']);
//print_r($dQuery);
//print_r($storeid);
//exit();
$dtarr = explode(" - ", $dQuery);
if (count($dtarr) == 1) {
    list($dd, $mm, $yy) = explode("-", $dtarr[0]);
    $sdate = "$yy-$mm-$dd";
    $edate = "$yy-$mm-$dd";
    $dQuery = "  d.createdate like '%$edate%' ";
//    $newfname = "DefectiveGarmentReport_" . $sdate . "_" . $edate . ".csv";

    if ($edate == date("Y-m-d")) {
        $dQuery = "   d.createdate like '%$edate%' ";
    }
} else if (count($dtarr) == 2) {
    list($dd, $mm, $yy) = explode("-", $dtarr[0]);
    $sdate = "$yy-$mm-$dd";
    list($dd, $mm, $yy) = explode("-", $dtarr[1]);
    $edate = "$yy-$mm-$dd";
    $dQuery = "  d.createdate >= '$sdate 00:00:00' and d.createdate <= '$edate 23:59:59' ";
} else {
    $dQuery = "";
}



//print_r($dQuery);
//exit();

                            if ($storeid == -1) {
//                                $iquery = "select  c.store_name ,r.points_to_upload,r.points_upload_date,if(r.is_reddeme =1,'Yes','No' ) as Is_Redeem,if(r.is_reddeme =1,r.points_redeemdate,'-' ) as points_redeemdate,if(r.is_reddeme =1,r.invoice_no,'-' ) as invoice_no,r.remark from it_codes c,it_store_redeem_points r where  $dQuery  and r.active=1 and r.store_id=c.id order by c.store_name ";
                                $iquery = "select d.createdate, d.customer_name, d.customer_mobile_no, "
                                        . "if(d.cust_old_bill_no!='',d.cust_old_bill_no,'-') as Customer_Old_Bill_No, "
                                        . "if(d.old_bill_date!='',d.old_bill_date,'-') as Old_Bill_Date, "
                                        . "if(d.orignal_purchase_store_name!='',d.orignal_purchase_store_name,'-') "
                                        . "as Original_Purchase_Store_Name, d.exchange_bill_no, d.exchange_bill_date, "
                                        . "c.store_name as exchange_given_at_store, d.store_address, "
                                        . "d.store_manager_name, d.store_manager_mob_no, "
                                        . "d.product, d.design_no ,d.size,d.style,d.barcode, d.mrp, d.defects, "
                                        . "if(d.remark_for_other_defects!='',d.remark_for_other_defects,'-') as "
                                        . "remark from defective_garment_form d inner join it_codes c on "
                                        . "d.exchange_given_at_store=c.id "
                                        . "where $dQuery order by c.store_name";
                            } else {
                                $iquery = "select d.createdate, d.customer_name, d.customer_mobile_no, "
                                        . "if(d.cust_old_bill_no!='',d.cust_old_bill_no,'-') as Customer_Old_Bill_No, "
                                        . "if(d.old_bill_date!='',d.old_bill_date,'-') as Old_Bill_Date, "
                                        . "if(d.orignal_purchase_store_name!='',d.orignal_purchase_store_name,'-') "
                                        . "as Original_Purchase_Store_Name, d.exchange_bill_no, d.exchange_bill_date, "
                                        . "c.store_name as exchange_given_at_store, d.store_address, "
                                        . "d.store_manager_name, d.store_manager_mob_no, "
                                        . "d.product, d.design_no, d.defects,d.size,d.style,d.barcode, d.mrp, "
                                        . "if(d.remark_for_other_defects!='',d.remark_for_other_defects,'-') as "
                                        . "remark from defective_garment_form d inner join it_codes c on "
                                        . "d.exchange_given_at_store=c.id "
                                        . "where $dQuery and d.exchange_given_at_store=$storeid order by c.store_name";
                            }
 $items = $db->fetchObjectArray($iquery);
//print $query;
//exit;
//print_r($alldealersobj);
//return;
//$db->closeConnection();
$objPHPExcel = new PHPExcel();
if (!empty($items)) {
    createexcel($items, $objPHPExcel);
//                    unset($dealersList);
}

function createexcel($items, $objPHPExcel) {

    $sheetIndex = 0;
    // Create new PHPExcel object
    // Create a first sheet
    $objPHPExcel->setActiveSheetIndex($sheetIndex);
    $objPHPExcel->getActiveSheet()->setTitle('Defective Garment Report');
    $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Date');
    $objPHPExcel->getActiveSheet()->setCellValue('B1', 'Customer Name');
    $objPHPExcel->getActiveSheet()->setCellValue('C1', 'Customer Mobile No');
    $objPHPExcel->getActiveSheet()->setCellValue('D1', 'Customer Old Bill No');
    $objPHPExcel->getActiveSheet()->setCellValue('E1', 'Old Bill Date');
    $objPHPExcel->getActiveSheet()->setCellValue('F1', 'Original Purchase Store Name');
    $objPHPExcel->getActiveSheet()->setCellValue('G1', 'Exchange Bill No');
    $objPHPExcel->getActiveSheet()->setCellValue('H1', 'Exchange Bill Date');
    $objPHPExcel->getActiveSheet()->setCellValue('I1', 'Exchange given at the store');
    $objPHPExcel->getActiveSheet()->setCellValue('J1', 'Store Address');
    $objPHPExcel->getActiveSheet()->setCellValue('K1', 'Store Manager Name');
    $objPHPExcel->getActiveSheet()->setCellValue('L1', 'Store Manager Mobile No');
    $objPHPExcel->getActiveSheet()->setCellValue('M1', 'Product');
    $objPHPExcel->getActiveSheet()->setCellValue('N1', 'Design No');
    $objPHPExcel->getActiveSheet()->setCellValue('O1', 'Size');
    $objPHPExcel->getActiveSheet()->setCellValue('P1', 'Style');
    $objPHPExcel->getActiveSheet()->setCellValue('Q1', 'Defective Garment Barcode');
    $objPHPExcel->getActiveSheet()->setCellValue('R1', 'Defective Garment MRP');
    $objPHPExcel->getActiveSheet()->setCellValue('S1', 'Defects');
    $objPHPExcel->getActiveSheet()->setCellValue('T1', 'Remark');

    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth(20);

    $rowCount = 2;
    
    foreach ($items as $item) {
//        print_r($item);
        
        //$arr = explode("::",$value);
        $createdate = trim($item->createdate);
        $customer_name = trim($item->customer_name);
        $customer_mobile_no = trim($item->customer_mobile_no);
       
        $Customer_Old_Bill_No = trim($item->Customer_Old_Bill_No);
        $Old_Bill_Date = trim($item->Old_Bill_Date);
        $Original_Purchase_Store_Name = trim($item->Original_Purchase_Store_Name);
        $exchange_bill_no = trim($item->exchange_bill_no);
        $exchange_bill_date = trim($item->exchange_bill_date);
        $exchange_given_at_store = trim($item->exchange_given_at_store);
        $store_address = trim($item->store_address);
        $store_manager_name = trim($item->store_manager_name);
        $store_manager_mob_no = trim($item->store_manager_mob_no);
        $product = trim($item->product);
        $design_no = trim($item->design_no);
        $size = trim($item->size);
        $style = trim($item->style);
        $barcode = trim($item->barcode);
        $mrp = trim($item->mrp);
        $defects = trim($item->defects);
        $remark = trim($item->remark);

        //$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $rowCount, $key);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $rowCount, $createdate);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $rowCount, $customer_name);
//         print_r($customer_mobile_no);
        
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $rowCount, $customer_mobile_no);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $rowCount, $Customer_Old_Bill_No);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $rowCount, $Old_Bill_Date);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $rowCount, $Original_Purchase_Store_Name);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $rowCount, $exchange_bill_no);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $rowCount, $exchange_bill_date);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $rowCount, $exchange_given_at_store);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(9, $rowCount, $store_address);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(10, $rowCount, $store_manager_name);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(11, $rowCount, $store_manager_mob_no);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(12, $rowCount, $product);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(13, $rowCount, $design_no);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(14, $rowCount, $size);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(15, $rowCount, $style);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(16, $rowCount, $barcode);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(17, $rowCount, $mrp);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(18, $rowCount, $defects);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(19, $rowCount, $remark);
        $rowCount++;
    }
}

//echo "Row Count=======>".$rowCount."\n";
$filename = "DGReturnReport_" . date('Y-m-d H:i:s') . ".xls";
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename=' . $filename);
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
