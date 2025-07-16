<?php

require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once "Classes/PHPExcel.php";
require_once "Classes/PHPExcel/Writer/Excel2007.php";

//print_r($_POST);
//exit();

$db = new DBConn();
$all_stores = $db->fetchObjectArray("SELECT id FROM it_codes WHERE usertype=" . UserType::Dealer);
$store_ids = array_map(function ($store) {
    return (int) $store->id;
}, $all_stores);

$store_id_str = implode(",", array_map('intval', $store_ids)); // final sanitized comma-separated string

$scheme     = isset($_POST['scheme']) ? $_POST['scheme'] : null;
$monthyear  = isset($_POST['monthyear']) ? $_POST['monthyear'] : null;

$from_dt = isset($_POST['fromDate']) && $_POST['fromDate'] !== ''
    ? $_POST['fromDate'] . " 00:00:00"
    : null;

$to_dt = isset($_POST['toDate']) && $_POST['toDate'] !== ''
    ? $_POST['toDate'] . " 23:59:59"
    : null;

$filename="";
$month_key = 0;
if (isset($monthyear)) {
    $month_key = str_replace("-", "", $monthyear);   // Remove hyphen to get YYYYMM
}
//echo $month_key; exit();



if (isset($scheme) && $scheme == Discount_scheme::loyalty_membership && $month_key != 0) {

    $query = "SELECT id,store_id,store_name,discountset,tax_combo,total_mrp,total_sale_wo_discount,total_discount,totalvalue,scheme_type,month_key,inactive from it_store_discountscheme_summary where month_key=$month_key and scheme_type=" . Discount_scheme::loyalty_membership . " and inactive=0";
//    echo $query; exit();
    
    $filename = "Loyalty";

} else if (isset($scheme) && $scheme == Discount_scheme::dealer_discount && $from_dt && $to_dt) {
//    echo "Right now we can not generate excel for dealer discount!";
//    exit();
    $query = "SELECT c.id AS store_id, c.store_name, c.discountset, CONCAT( CASE WHEN ict.tax_rate IS NOT NULL THEN ict.tax_rate * 100 WHEN i.MRP > 1050 THEN 12 ELSE 5 END, '-', CASE WHEN ict.tax_rate IS NOT NULL THEN ict.tax_rate * 100 WHEN ( CASE WHEN o.discount_pct IS NOT NULL THEN (((100 - o.discount_pct) / 100) * oi.price) * CASE WHEN o.tickettype = 1 THEN ABS(oi.quantity) ELSE oi.quantity END ELSE oi.price * CASE WHEN o.tickettype = 1 THEN ABS(oi.quantity) ELSE oi.quantity END END ) > 1050 THEN 12 ELSE 5 END ) AS tax_combo, SUM( CASE WHEN discounted_orders.order_id IS NOT NULL THEN i.MRP * oi.quantity ELSE 0 END ) AS total_mrp, SUM(IFNULL(oi.discount_val, 0.0)) AS total_discount, SUM( CASE WHEN discounted_orders.order_id IS NOT NULL THEN CASE WHEN o.discount_pct IS NOT NULL THEN (((100 - o.discount_pct) / 100) * oi.price) ELSE oi.price END * CASE WHEN o.tickettype = 1 THEN ABS(oi.quantity) ELSE oi.quantity END ELSE 0 END ) AS totalvalue, SUM( CASE WHEN discounted_orders.order_id IS  NULL THEN i.MRP * oi.quantity ELSE 0 END ) AS total_sale_wo_discount FROM it_orders o JOIN it_order_items oi ON oi.order_id = o.id JOIN it_items i ON i.id = oi.item_id JOIN it_codes c ON o.store_id = c.id JOIN states s ON s.id = c.state_id JOIN region r ON c.region_id = r.id LEFT JOIN it_category_taxes ict ON ict.category_id = i.ctg_id LEFT JOIN ( SELECT DISTINCT order_id FROM it_order_items WHERE IFNULL(discount_val, 0.0) > 0 ) AS discounted_orders ON o.id = discounted_orders.order_id JOIN ( SELECT o.id AS order_id, o.tickettype, MAX(CASE WHEN oi.quantity < 0 THEN 1 ELSE 0 END) AS has_negative_qty, MAX(CASE WHEN IFNULL(oi.discount_val, 0.0) > 0 THEN 1 ELSE 0 END) AS has_discount FROM it_orders o JOIN it_order_items oi ON oi.order_id = o.id JOIN it_order_payments p ON p.order_id = o.id WHERE TRIM(p.payment_name) != 'loyalty' AND o.tickettype IN (0, 1, 6) AND o.store_id IN ($store_id_str) AND o.bill_datetime BETWEEN '$from_dt' AND '$to_dt' GROUP BY o.id, o.tickettype ) AS bt ON bt.order_id = o.id WHERE o.store_id IN ($store_id_str) AND o.bill_datetime BETWEEN '$from_dt' AND '$to_dt' AND NOT EXISTS ( SELECT 1 FROM it_order_payments lp WHERE lp.order_id = o.id AND TRIM(lp.payment_name) = 'loyalty' ) GROUP BY c.id, tax_combo ORDER BY c.id, tax_combo;";
//    echo $query; exit();
    
    $filename = "EOSS";
//    echo $filename; exit();
    
} else {
    $errors['status']= "Invalid or missing inputs. Please check form data.";
    $_SESSION['form_errors'] = $errors;
    $redirect = "cp/calculations";
    session_write_close();
    header("Location: ".DEF_SITEURL.$redirect);
    exit;
}

$saleObjs = $db->fetchObjectArray($query);

//echo '<pre>'; print_r($saleObjs); echo '</pre>'; 
//exit();

try {
    $sheetIndex = 0;
    $cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_in_memory_serialized;
    $cacheSettings = array('memoryCacheSize' => '1500MB');
    PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
    // Create new PHPExcel object
    $objPHPExcel = new PHPExcel();
    // Create a first sheet
    $objPHPExcel->setActiveSheetIndex($sheetIndex);
    $objPHPExcel->getActiveSheet()->setTitle('Credit Point Calculations');

    //define style for column name
    $styleArray = array(
        'font' => array(
            'bold' => true,
//            'color' => array('rgb' => 'FF0000'),
            'size' => 11,
    ));
    $styleArray1 = array(
        'font' => array(
            'bold' => true,
            'color' => array('rgb' => '000000'), // Black font
            'size' => 11,
        ),
        'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('rgb' => 'FFFF00') // Yellow background
        )
    );
    $styleArray2 = array(
        'font' => array(
            'bold' => true,
            'color' => array('rgb' => '000000'), // Black font
            'size' => 11,
        ),
        'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('rgb' => 'FFFF00') // Yellow background
        )
    );
// Set the value in the merged cell(1st row)
    $objPHPExcel->getActiveSheet()->mergeCells('G1:J1');
    $objPHPExcel->getActiveSheet()->setCellValue('G1', 'P12 S12')->getStyle('G1')->applyFromArray($styleArray)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->mergeCells('K1:N1');
    $objPHPExcel->getActiveSheet()->setCellValue('K1', 'P12 S5')->getStyle('K1')->applyFromArray($styleArray)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->mergeCells('O1:R1');
    $objPHPExcel->getActiveSheet()->setCellValue('O1', 'P5 S5')->getStyle('O1')->applyFromArray($styleArray)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->mergeCells('S1:V1');
    $objPHPExcel->getActiveSheet()->setCellValue('S1', 'P18 S18')->getStyle('S1')->applyFromArray($styleArray)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

//set the value in 2nd row
    $objPHPExcel->getActiveSheet()->setCellValue('A2', 'Sr No')->getStyle('A2')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->setCellValue('B2', 'Row Labels')->getStyle('B2')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->setCellValue('C2', 'Store ID')->getStyle('C2')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->setCellValue('D2', 'Credit Point Heading')->getStyle('D2')->applyFromArray($styleArray1);
    $objPHPExcel->getActiveSheet()->setCellValue('E2', 'Dealer Margin')->getStyle('E2')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->setCellValue('F2', 'Scheme Discount')->getStyle('F2')->applyFromArray($styleArray2);
    $objPHPExcel->getActiveSheet()->setCellValue('G2', 'MRP Sale')->getStyle('G2')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->setCellValue('H2', 'Sale Without Discount')->getStyle('H2')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->setCellValue('I2', 'Discount')->getStyle('I2')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->setCellValue('J2', 'Total Value')->getStyle('J2')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->setCellValue('K2', 'MRP Sale')->getStyle('K2')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->setCellValue('L2', 'Sale Without Discount')->getStyle('L2')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->setCellValue('M2', 'Discount')->getStyle('M2')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->setCellValue('N2', 'Total Value')->getStyle('N2')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->setCellValue('O2', 'MRP Sale')->getStyle('O2')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->setCellValue('P2', 'Sale Without Discount')->getStyle('P2')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->setCellValue('Q2', 'Discount')->getStyle('Q2')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->setCellValue('R2', 'Total Value')->getStyle('R2')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->setCellValue('S2', 'MRP Sale')->getStyle('S2')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->setCellValue('T2', 'Sale Without Discount')->getStyle('T2')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->setCellValue('U2', 'Discount')->getStyle('U2')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->setCellValue('V2', 'Total Value')->getStyle('V2')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->setCellValue('W2', 'Non Scheme Sale')->getStyle('W2')->applyFromArray($styleArray);

    // Set width for each column
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(35);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(7);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(45);
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(13);
    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('U')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('V')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('W')->setWidth(15);
    
    $objPHPExcel->getActiveSheet()->getStyle('D:D')->applyFromArray($styleArray1);
    $objPHPExcel->getActiveSheet()->getStyle('F:F')->applyFromArray($styleArray2);

    $sr_no = 1;
    $rowCount = 3; // assuming row 1,2 has headers

    $groupedStores = [];

    if (isset($saleObjs) && !empty($saleObjs)) {
        // Step 1: Group by store_id
        foreach ($saleObjs as $obj) {
            $storeId = $obj->store_id;
            $taxCombo = $obj->tax_combo;

            // Initialize group if not exists
            if (!isset($groupedStores[$storeId])) {
                $groupedStores[$storeId] = [
                    'store_name' => $obj->store_name,
                    'dealer_margin' => $obj->discountset . '%',
                    'store_id' => $storeId,
                    'tax_combos' => []
                ];
            }

            // Save tax combo data
            $groupedStores[$storeId]['tax_combos'][$taxCombo] = [
                'mrp'              => round(!empty($obj->total_mrp) ? $obj->total_mrp : 0),
                'sale_wo_discount' => round(!empty($obj->total_sale_wo_discount) ? $obj->total_sale_wo_discount : 0),
                'discount'         => round(!empty($obj->total_discount) ? $obj->total_discount : 0),
                'value'            => round(!empty($obj->totalvalue) ? $obj->totalvalue : 0)
            ];

        }

        // Step 2: Output to Excel
        foreach ($groupedStores as $storeData) {
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $rowCount, $sr_no);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $rowCount, $storeData['store_name']);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $rowCount, $storeData['store_id']);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $rowCount, "");
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $rowCount, $storeData['dealer_margin']);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $rowCount, "");

            // Define column mapping per tax combo
            $columnMap = [
                '12-12' => 6,
                '12-5' => 10,
                '5-5' => 14,
                '18-18' => 18
            ];
            
            $non_scheme_sale = 0;
            foreach ($columnMap as $taxCombo => $startCol) {
                if (isset($storeData['tax_combos'][$taxCombo])) {
                    $data = $storeData['tax_combos'][$taxCombo];
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($startCol, $rowCount, $data['mrp']);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($startCol + 1, $rowCount, 0);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($startCol + 2, $rowCount, $data['discount']);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($startCol + 3, $rowCount, $data['value']);
                    $non_scheme_sale += $data['sale_wo_discount'];
                }
            }

            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(22, $rowCount, $non_scheme_sale);

            $rowCount++;
            $sr_no++;
        }
    }


// Redirect output to a client’s web browser (Excel5)
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="cpcalculations_' . $filename . '.xls"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

    $objWriter->save('php://output');
} catch (Exception $xcp) {
    print $xcp->getMessage();
}