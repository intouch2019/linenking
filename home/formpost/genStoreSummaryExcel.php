<?php

require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once "Classes/PHPExcel.php";
require_once "Classes/PHPExcel/Writer/Excel2007.php";

try {
    $db = new DBConn();

    $dtrange = isset($_GET['dtrange']) ? $_GET['dtrange'] : "";
    $storeid = isset($_GET['storeid']) ? $_GET['storeid'] : "-1";

    $dtClause = "";
    if (isset($dtrange) && trim($dtrange) != "") {
        $dtarr = explode(" - ", $dtrange);
        if (count($dtarr) == 1) {
            list($dd, $mm, $yy) = explode("-", $dtarr[0]);
            $sdate = "$yy-$mm-$dd";
            $dtClause = " and s.stock_datetime >= '$sdate 00:00:00' and s.stock_datetime <= '$sdate 23:59:59' ";
        } else if (count($dtarr) == 2) {
            list($dd, $mm, $yy) = explode("-", $dtarr[0]);
            $sdate = "$yy-$mm-$dd";
            list($dd, $mm, $yy) = explode("-", $dtarr[1]);
            $edate = "$yy-$mm-$dd";
            $dtClause = " and s.stock_datetime >= '$sdate 00:00:00' and s.stock_datetime <= '$edate 23:59:59' ";
        } else {
            $dtClause = "";
        }
    }

    // Show data after 23 April 2026 only.
    $dtClause .= " and s.stock_datetime >= '2026-04-23 00:00:00' ";

    $sClause = "";
    if (isset($storeid) && trim($storeid) != "" && trim($storeid) != "-1") {
        $sClause = " and s.store_id in ($storeid) ";
    }

    $exeId = getCurrUser()->id;

    $query = "
        select
            c.store_name,
            s.stock_datetime,
            s.min_stock_limit,
            s.max_stock_limit,
            s.stock_value,
            round((s.stock_value/nullif(s.min_stock_limit,0))*100,2) as percentage
        from it_store_stock_summary s, it_codes c
        where s.store_id = c.id
          and c.is_closed=0
          and c.id in (select store_id from executive_assign where exe_id=$exeId)
          $dtClause
          $sClause
        order by s.id desc
    ";

    $sheetIndex = 0;
    $cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_in_memory_serialized;
    $cacheSettings = array('memoryCacheSize' => '1500MB');
    PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);

    $objPHPExcel = new PHPExcel();
    $objPHPExcel->setActiveSheetIndex($sheetIndex);
    $objPHPExcel->getActiveSheet()->setTitle('Store Summary');

    $headers = array(
        'Store Name',
        'Stock DateTime',
        'Min Stock Limit',
        'Max Stock Limit',
        'Stock Value',
        'Percentage'
    );

    $col = 0;
    foreach ($headers as $h) {
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, 1, $h);
        $col++;
    }

    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(22);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(18);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(18);
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(18);
    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(14);

    $rowCount = 2;
    $result = $db->getConnection()->query($query);
    while ($obj = $result->fetch_object()) {
        $pctText = ($obj->percentage === null || $obj->percentage === "") ? "" : ($obj->percentage . "%");
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $rowCount, $obj->store_name);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $rowCount, $obj->stock_datetime);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $rowCount, $obj->min_stock_limit);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $rowCount, $obj->max_stock_limit);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $rowCount, $obj->stock_value);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $rowCount, $pctText);
        $rowCount++;
    }

    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="StoreSummary.xls"');
    header('Cache-Control: max-age=0');

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->save('php://output');
} catch (Exception $xcp) {
    print $xcp->getMessage();
}

?>

