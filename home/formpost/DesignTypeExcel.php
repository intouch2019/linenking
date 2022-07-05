<?php

require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once "Classes/PHPExcel.php";
require_once "Classes/PHPExcel/Writer/Excel2007.php";

try {
//    print_r($_GET['core']);
    extract($_GET);
    if (isset($_GET['core']) && $_GET['core'] != null) {
        $core = $_GET['core'];
        $corequery = "where core=$core";
    }
    if ($core == -1) {
        $corequery = "";
    }
//    exit();
    $db = new DBConn();
    $sheetIndex = 0;
    $cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_in_memory_serialized;
    $cacheSettings = array('memoryCacheSize' => '1500MB');
    PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
    // Create new PHPExcel object
    $objPHPExcel = new PHPExcel();
    // Create a first sheet
    $objPHPExcel->setActiveSheetIndex($sheetIndex);
    $objPHPExcel->getActiveSheet()->setTitle('Design Details');
    $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Design No');
    $objPHPExcel->getActiveSheet()->setCellValue('B1', 'Category Name');
    $objPHPExcel->getActiveSheet()->setCellValue('C1', 'Design Type');

    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
    $styleArray = array(
        'font' => array(
            'bold' => false,
            //        'color' => array('rgb' => 'FF0000'),
            'size' => 10,
    ));
    $objPHPExcel->getActiveSheet()->getStyle('A')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('B')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('C')->applyFromArray($styleArray);
    $colCount = 0;
    $rowCount = 2;
    $query = "select id, design_no,(select name from it_categories where id=ctg_id)as ctgname,core from it_ck_designs $corequery ";

    $objs = $db->getConnection()->query($query);
    while ($obj = $objs->fetch_object()) {
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $rowCount, $obj->design_no);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $rowCount, $obj->ctgname);
        if ($obj->core == 1) {
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $rowCount, "Core");
        } else {
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $rowCount, "Non Core");
        }

        $rowCount++;
    }
// Redirect output to a client’s web browser (Excel5)
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename="Design_Details.xls"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->save('php://output');
} catch (Exception $xcp) {
    print $xcp->getMessage();
}
