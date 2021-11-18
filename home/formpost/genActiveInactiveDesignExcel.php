<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "Classes/PHPExcel.php";
require_once 'Classes/PHPExcel/Writer/Excel2007.php';


ini_set('memory_limit', '512M');

$db = new DBConn();

extract($_GET);
//$state = isset($_GET['is_design_mrp_active']) ? $_GET['is_design_mrp_active'] : 0;

 $Where = " where d.ctg_id=ctg.id and d.design_no = i.design_no and d.ctg_id = i.ctg_id and d.id = i.design_id and  i.is_design_mrp_active = ". $status." ";
 

$sheetIndex=0;
// Create new PHPExcel object.
$objPHPExcel = new PHPExcel();
// Create a first sheet
$objPHPExcel->setActiveSheetIndex($sheetIndex);
$objPHPExcel->getActiveSheet()->setTitle('Active Inactive Design');
$objPHPExcel->getActiveSheet()->setCellValue('A1', 'Design No');
$objPHPExcel->getActiveSheet()->setCellValue('B1', 'Category');
$objPHPExcel->getActiveSheet()->setCellValue('C1', 'MRP');
$objPHPExcel->getActiveSheet()->setCellValue('D1', 'LineNo');
$objPHPExcel->getActiveSheet()->setCellValue('E1', 'RackNo');    
$objPHPExcel->getActiveSheet()->setCellValue('F1', 'Active');

$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);

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

$objPHPExcel->getActiveSheet()->getStyle('A')->applyFromArray($cellstyleArray);
$objPHPExcel->getActiveSheet()->getStyle('B')->applyFromArray($cellstyleArray);
$objPHPExcel->getActiveSheet()->getStyle('C')->applyFromArray($cellstyleArray);
$objPHPExcel->getActiveSheet()->getStyle('D')->applyFromArray($cellstyleArray);
$objPHPExcel->getActiveSheet()->getStyle('E')->applyFromArray($cellstyleArray);
$objPHPExcel->getActiveSheet()->getStyle('F')->applyFromArray($cellstyleArray);
$rowCount=2;

$query = "
            select d.design_no,d.lineno,d.rackno ,ctg.name as ctg_name,i.MRP,i.is_design_mrp_active 
            from it_ck_designs d,it_categories ctg , it_items i                  
            $Where   
                 group by d.design_no,d.ctg_id,i.MRP order by d.design_no";
//echo $query;
$objs = $db->fetchObjectArray($query);

foreach ($objs as $obj) {    
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $rowCount, $obj->design_no);
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $rowCount, $obj->ctg_name);
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $rowCount, $obj->MRP);
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $rowCount, $obj->lineno);
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $rowCount, $obj->rackno);  
    if($obj->is_design_mrp_active==0){
        $status = "Inactive";
    }else{
        $status="Active";        
    }
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $rowCount,$status);        
            
    $rowCount++;
}    


// Redirect output to a client’s web browser (Excel5)
$filename = "ActivesInactiveDesign_".date("Ymd-His").".xls";     
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename='.$filename);
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');    
	


