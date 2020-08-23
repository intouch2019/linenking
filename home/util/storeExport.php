<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "Classes/PHPExcel.php";
require_once 'Classes/PHPExcel/Writer/Excel2007.php';


    //$cnt=0;
    //$dealersList = array();
    $table = "it_codes";
    $db = new DBConn();
    $query = "select id,store_name,tally_name,IF(is_autorefill = 1, 'Yes', 'No') AS is_autorefill, IF(is_closed = 1, 'Yes', 'No') AS is_closed,IF(sbstock_active = 1, 'Yes', 'No') AS Standing_Base_Stock,owner,address,city,zipcode,phone,phone2,email,email2,vat,store_number,pancard_no,min_stock_level,server_change_id,createtime as Store_Create_Time, IF(inactive = 1, 'Yes', 'No') AS inactive, gstin_no from $table where usertype=4 order by createtime";
    $alldealersobj = $db->fetchObjectArray($query);
    //print_r($alldealersobj);
    //return;
    //$db->closeConnection();
    $objPHPExcel = new PHPExcel();        
    if(!empty($alldealersobj)) {
       $fpath = createexcel($alldealersobj, $objPHPExcel);             
       unset($dealersList);          
    }
    
    
    
    function createexcel($alldealersobj, $objPHPExcel){
    
    $sheetIndex=0;
    // Create new PHPExcel object
    
    // Create a first sheet
    $objPHPExcel->setActiveSheetIndex($sheetIndex);
    $objPHPExcel->getActiveSheet()->setTitle('Franchisees below MSL');
    $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Id');
    $objPHPExcel->getActiveSheet()->setCellValue('B1', 'Store Number');        
    $objPHPExcel->getActiveSheet()->setCellValue('C1', 'Store Name');
    $objPHPExcel->getActiveSheet()->setCellValue('D1', 'Tally Name');
    $objPHPExcel->getActiveSheet()->setCellValue('E1', 'Is_Autorefill');
    $objPHPExcel->getActiveSheet()->setCellValue('F1', 'Is_Closed');
    $objPHPExcel->getActiveSheet()->setCellValue('G1', 'Standing_Base_Stock');    
    $objPHPExcel->getActiveSheet()->setCellValue('H1', 'Owner');
    $objPHPExcel->getActiveSheet()->setCellValue('I1', 'Address');
    $objPHPExcel->getActiveSheet()->setCellValue('J1', 'City');
    $objPHPExcel->getActiveSheet()->setCellValue('K1', 'Zipcode');
    $objPHPExcel->getActiveSheet()->setCellValue('L1', 'Phone');
    $objPHPExcel->getActiveSheet()->setCellValue('M1', 'Phone2');
    $objPHPExcel->getActiveSheet()->setCellValue('N1', 'Email');
    $objPHPExcel->getActiveSheet()->setCellValue('O1', 'Email2');
    $objPHPExcel->getActiveSheet()->setCellValue('P1', 'Vat');
    $objPHPExcel->getActiveSheet()->setCellValue('Q1', 'GSTIN No');
    $objPHPExcel->getActiveSheet()->setCellValue('R1', 'Pancard No'); 
    $objPHPExcel->getActiveSheet()->setCellValue('S1', 'Min Stock Level');
    $objPHPExcel->getActiveSheet()->setCellValue('T1', 'Server Change Id');
    $objPHPExcel->getActiveSheet()->setCellValue('U1', 'Store CreateTime');
    $objPHPExcel->getActiveSheet()->setCellValue('V1', 'Inactive');

    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
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
    $objPHPExcel->getActiveSheet()->getColumnDimension('U')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('V')->setWidth(20);

    
    $colCount=0;
    $rowCount=2;
    
    //foreach ($alldealersobj as $key => $value){
    foreach ($alldealersobj as $dealer){
        
  /*      print_r($dealer);
        return;*/
        
        $diff=0;
        //$arr = explode("::",$value);
        $id = trim($dealer->id);
        $store_name = trim($dealer->store_name);
        $tally_name = trim($dealer->tally_name) ;
        $is_autorefill = trim($dealer->is_autorefill);
        $is_closed = trim($dealer->is_closed);
        $Standing_Base_Stock = trim($dealer->Standing_Base_Stock);
        $owner = trim($dealer->owner);
        $address = trim($dealer->address);
        $city = trim($dealer->city);
        $zipcode = trim($dealer->zipcode);
        $phone= trim($dealer->phone);
        $phone2 = trim($dealer->phone2);
        $email = trim($dealer->email);
        $email2 = trim($dealer->email2);
        $vat = trim($dealer->vat);
        $store_number = trim($dealer->store_number);
        $pancard_no = trim($dealer->pancard_no);
        $min_stock_level = trim($dealer->min_stock_level);
        $server_change_id = trim($dealer->server_change_id);
        $Store_Create_Time = trim($dealer->Store_Create_Time);
        $inactive = trim($dealer->inactive);
        $gstin_no = trim($dealer->gstin_no);
        
        //$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $rowCount, $key);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $rowCount, $id);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $rowCount, $store_number); 
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $rowCount, $store_name);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $rowCount, $tally_name);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $rowCount, $is_autorefill);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $rowCount, $is_closed);        
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $rowCount, $Standing_Base_Stock);        
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $rowCount, $owner); 
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $rowCount, $address);        
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(9, $rowCount, $city );        
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(10, $rowCount, $zipcode);        
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(11, $rowCount, $phone);        
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(12, $rowCount, $phone2);        
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(13, $rowCount, $email); 
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(14, $rowCount, $email2);        
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(15, $rowCount, $vat); 
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(16, $rowCount, $gstin_no);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(17, $rowCount, $pancard_no);        
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(18, $rowCount, $min_stock_level);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(19, $rowCount, $server_change_id);        
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(20, $rowCount, $Store_Create_Time);        
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(21, $rowCount, $inactive);        
        $rowCount++;
    }}
//echo "Row Count=======>".$rowCount."\n";
$filename = "StoreDetail_".date("Ymd-His").".xls";     
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename='.$filename);
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output'); 
