<?php

require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once ("lib/core/strutil.php");
require_once "Classes/PHPExcel.php";
require_once 'Classes/PHPExcel/Writer/Excel2007.php';

$db = new DBConn();
$filenameas_storename = "";  
$sheetIndex=0;
$sid  = isset($_GET['sid']) ? $_GET['sid'] : null;
//echo $sid;
//exit();
// Create new PHPExcel object
$objPHPExcel = new PHPExcel();
// Create a first sheet
$objPHPExcel->setActiveSheetIndex($sheetIndex);
$objPHPExcel->getActiveSheet()->setTitle('MRP Wise Store Current Stock');
$objPHPExcel->getActiveSheet()->setCellValue('A1', 'Sr No');
$objPHPExcel->getActiveSheet()->setCellValue('B1', 'Store Name');
$objPHPExcel->getActiveSheet()->setCellValue('C1', 'Total Qty (MRP <=1050)');
$objPHPExcel->getActiveSheet()->setCellValue('D1', 'Total Value (MRP <=1050)');
$objPHPExcel->getActiveSheet()->setCellValue('E1', 'Total Qty (MRP 1051-2800)');
$objPHPExcel->getActiveSheet()->setCellValue('F1', 'Total Value (MRP 1051-2800)');    
$objPHPExcel->getActiveSheet()->setCellValue('G1', 'Total Qty (MRP >2800)');
$objPHPExcel->getActiveSheet()->setCellValue('H1', 'Total Value (MRP >2800)');
$objPHPExcel->getActiveSheet()->setCellValue('I1', 'Total Qty');
$objPHPExcel->getActiveSheet()->setCellValue('J1', 'Total Value');
$objPHPExcel->getActiveSheet()->setCellValue('K1', 'Debit Note Value');
$objPHPExcel->getActiveSheet()->setCellValue('L1', 'Credit Note Value');

$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(45);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(22);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(22);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(23);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(23);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(22);
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(22);
$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(15);


$styleArray = array(
    'font'  => array(
        'bold'  => true,    
        'size'  => 10,           
    ),
    'alignment' => array(
        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
    )
    );

$styleArray1 = array(
    'font' => array(
        'bold' => true,
//        'color' => array('rgb' => 'FF0000'),
        'size' => 10,
    ),
    'alignment' => array(
        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
    )
);

 $cellstyleArray = array(
    'font' => array(
        'bold' => false,
//        'color' => array('rgb' => 'FF0000'),
        'size' => 10,
    ),
    'alignment' => array(
        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
    )
);
$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('B1')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('C1')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('D1')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('E1')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('F1')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('G1')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('H1')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('I1')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('J1')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('K1')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('L1')->applyFromArray($styleArray);

$objPHPExcel->getActiveSheet()->getStyle('A')->applyFromArray($cellstyleArray);
$objPHPExcel->getActiveSheet()->getStyle('B')->applyFromArray($cellstyleArray);
$objPHPExcel->getActiveSheet()->getStyle('C')->applyFromArray($cellstyleArray);
$objPHPExcel->getActiveSheet()->getStyle('D')->applyFromArray($cellstyleArray);
$objPHPExcel->getActiveSheet()->getStyle('E')->applyFromArray($cellstyleArray);
$objPHPExcel->getActiveSheet()->getStyle('F')->applyFromArray($cellstyleArray);
$objPHPExcel->getActiveSheet()->getStyle('G')->applyFromArray($cellstyleArray);
$objPHPExcel->getActiveSheet()->getStyle('H')->applyFromArray($cellstyleArray);
$objPHPExcel->getActiveSheet()->getStyle('I')->applyFromArray($cellstyleArray);
$objPHPExcel->getActiveSheet()->getStyle('J')->applyFromArray($cellstyleArray);
$objPHPExcel->getActiveSheet()->getStyle('K')->applyFromArray($cellstyleArray);
$objPHPExcel->getActiveSheet()->getStyle('L')->applyFromArray($cellstyleArray);

$rowCount=2;
$srno = 1;

if ($sid == -1) {
    $filenameas_storename = "AllStore_";
    $iquery = "select c.store_name,SUM(cs.quantity) AS total_quantity,SUM(i.MRP * cs.quantity) AS total_value,SUM(CASE WHEN i.MRP <= 1050 THEN cs.quantity ELSE 0 END) AS qty_upto_1050,"
            . " SUM(CASE WHEN i.MRP <= 1050 THEN i.MRP * cs.quantity ELSE 0 END) AS val_upto_1050,SUM(CASE WHEN i.MRP BETWEEN 1051 AND 2800 THEN cs.quantity ELSE 0 END) AS qty_1051_2800,"
            . " SUM(CASE WHEN i.MRP BETWEEN 1051 AND 2800 THEN i.MRP * cs.quantity ELSE 0 END) AS val_1051_2800,SUM(CASE WHEN i.MRP > 2800 THEN cs.quantity ELSE 0 END) AS qty_above_2800,"
            . " SUM(CASE WHEN i.MRP > 2800 THEN i.MRP * cs.quantity ELSE 0 END) AS val_above_2800 from it_codes c,it_current_stock cs,"
            . " it_items i, it_categories ctg, it_brands br, it_styles st, it_sizes si, it_fabric_types fb, it_materials mt, it_prod_types pr,"
            . " it_mfg_by mfg where c.id = cs.store_id and c.usertype=".UserType::Dealer." and c.is_closed = 0 and cs.barcode = i.barcode and ctg.id=i.ctg_id and br.id=i.brand_id"
            . " and st.id=i.style_id and si.id=i.size_id and pr.id=i.prod_type_id and mt.id=i.material_id and fb.id=i.fabric_type_id and mfg.id=i.mfg_id"
            . " and cs.store_id = c.id and cs.quantity !=0 and ctg.id not in (65,16,13,14,18,19,20,24,25,26,27,28,29,33,35,41,42,43,44,45,46,48,49,50,51,52,53,54,55,56,57,58,59,60,61,62,63,64) group by c.store_name"; 
} else {
    $iquery = "select c.store_name,SUM(cs.quantity) AS total_quantity,SUM(i.MRP * cs.quantity) AS total_value,SUM(CASE WHEN i.MRP <= 1050 THEN cs.quantity ELSE 0 END) AS qty_upto_1050,"
            . " SUM(CASE WHEN i.MRP <= 1050 THEN i.MRP * cs.quantity ELSE 0 END) AS val_upto_1050,SUM(CASE WHEN i.MRP BETWEEN 1051 AND 2800 THEN cs.quantity ELSE 0 END) AS qty_1051_2800,"
            . " SUM(CASE WHEN i.MRP BETWEEN 1051 AND 2800 THEN i.MRP * cs.quantity ELSE 0 END) AS val_1051_2800,SUM(CASE WHEN i.MRP > 2800 THEN cs.quantity ELSE 0 END) AS qty_above_2800,"
            . " SUM(CASE WHEN i.MRP > 2800 THEN i.MRP * cs.quantity ELSE 0 END) AS val_above_2800 from it_codes c,it_current_stock cs,"
            . " it_items i, it_categories ctg, it_brands br, it_styles st, it_sizes si, it_fabric_types fb, it_materials mt, it_prod_types pr,"
            . " it_mfg_by mfg where c.id = cs.store_id and cs.store_id=$sid and cs.barcode = i.barcode and ctg.id=i.ctg_id and br.id=i.brand_id"
            . " and st.id=i.style_id and si.id=i.size_id and pr.id=i.prod_type_id and mt.id=i.material_id and fb.id=i.fabric_type_id and mfg.id=i.mfg_id"
            . " and cs.store_id = c.id and cs.quantity !=0 and ctg.id not in (65,16,13,14,18,19,20,24,25,26,27,28,29,33,35,41,42,43,44,45,46,48,49,50,51,52,53,54,55,56,57,58,59,60,61,62,63,64) group by c.store_name"; 
    $filenameas_storename = "";   
}
$alldealersobj = $db->fetchObjectArray($iquery);
//print_r($alldealersobj); exit();

if (!empty($alldealersobj) && isset($alldealersobj)) {
    foreach ($alldealersobj as $obj) {

        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $rowCount, $srno);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $rowCount, $obj->store_name);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $rowCount, $obj->qty_upto_1050);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $rowCount, $obj->val_upto_1050);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $rowCount, $obj->qty_1051_2800);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $rowCount, $obj->val_1051_2800);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $rowCount, $obj->qty_above_2800);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $rowCount, $obj->val_above_2800);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $rowCount, $obj->total_quantity);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(9, $rowCount, $obj->total_value);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(10, $rowCount, round($obj->val_1051_2800 - (($obj->val_1051_2800/1.12)*1.05)));
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(11, $rowCount, round($obj->val_above_2800 - (($obj->val_above_2800/1.12)*1.18)));
        
        $rowCount++;
        $srno++;
    }
}


// Redirect output to a client’s web browser (Excel5)
$filename = $filenameas_storename . "LK_" . date("Ymd-His") . ".xls";
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename=' . $filename);
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
