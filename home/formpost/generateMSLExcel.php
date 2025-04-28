<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "Classes/PHPExcel.php";
require_once 'Classes/PHPExcel/Writer/Excel2007.php';


try{
    $db = new DBConn();
    $cnt=0;
    $dealersList = array();
    $filenameas_storename = "Allstores_";
      if(getCurrUser()->usertype==UserType::Dealer){
      	$filenameas_storename = getCurrUser()->store_name . "_";			
       	$alldealersobj = $db->fetchObjectArray("select id,store_name,min_stock_level,max_stock_level  from it_codes where usertype = ".UserType::Dealer." and id= ".getCurrUser()->id." and is_closed = 0 and min_stock_level is not null" );
       
     }else{
            $alldealersobj = $db->fetchObjectArray("select id,store_name,min_stock_level,max_stock_level  from it_codes where usertype= ".UserType::Dealer." and id in (select store_id from executive_assign where exe_id=".getCurrUser()->id." )  and is_closed = 0 and min_stock_level is not null;" );
        }
    //$alldealersobj = $db->fetchObjectArray("select id,store_name,min_stock_level,max_stock_level  from it_codes where usertype = ".UserType::Dealer."   and is_closed = 0 and min_stock_level is not null" );  //and inactive = 0
    foreach($alldealersobj as $dealerobj){ 
//        if(trim($dealerobj->min_stock_level)!=""){
//     
//                
//            $tot_stk = 0;
//            $tot_curr_stk = 0;
//            $tot_mask_stk = 0;
//            
//            $query = "select sum(c.quantity * i.MRP) as appreal_curr_stock from it_current_stock c , it_items i where c.store_id = $dealerobj->id  and c.barcode = i.barcode and i.ctg_id not in (53,54)";
//            $cobj = $db->fetchObject($query);
//            if(isset($cobj) && trim($cobj->appreal_curr_stock)!=""){ $store_appreal_stock_val = $cobj->appreal_curr_stock ;}
//            else{ $store_appreal_stock_val = 0; }
//            
//            $tot_curr_stk += $store_appreal_stock_val;  
//            
//               ////////////////////////////////////////////////
//             $query = "select sum(c.quantity * i.MRP) as mask_curr_stock from it_current_stock c , it_items i where c.store_id = $dealerobj->id  and c.barcode = i.barcode and i.ctg_id  in (53,54)";
//            $cobj = $db->fetchObject($query);
//            if(isset($cobj) && trim($cobj->mask_curr_stock)!=""){ $store_mask_stock_val = $cobj->mask_curr_stock ;}
//            else{ $store_mask_stock_val = 0; }
//            
//            $tot_curr_stk += $store_mask_stock_val;  
//            
//                ////////////////////////////////////////////////
//            $query2 = "select sum(i.MRP*oi.quantity) as appreal_stock_intransit from it_sp_invoices o , it_sp_invoice_items oi , it_items i where oi.invoice_id = o.id and o.invoice_type in ( 0 , 6 ) and o.store_id = $dealerobj->id and o.is_procsdForRetail = 0 and oi.barcode = i.barcode  and i.ctg_id not in(53,54)";
//            $tobj = $db->fetchObject($query2);
//            if(isset($tobj) && trim($tobj->appreal_stock_intransit)!=""){ $intransit_appreal_val = $tobj->appreal_stock_intransit ;}
//            else{ $intransit_appreal_val = 0; }
//            
//            $tot_mask_stk += $intransit_appreal_val;    
//            
//                 ////////////////////////////////////////////////
//            $query2 = "select sum(i.MRP*oi.quantity) as mask_stock_intransit from it_sp_invoices o , it_sp_invoice_items oi , it_items i where oi.invoice_id = o.id and o.invoice_type in ( 0 , 6 ) and o.store_id = $dealerobj->id and o.is_procsdForRetail = 0 and oi.barcode = i.barcode  and i.ctg_id  in(53,54)";
//            $tobj = $db->fetchObject($query2);
//            if(isset($tobj) && trim($tobj->mask_stock_intransit)!=""){ $intransit_appreal_val = $tobj->mask_stock_intransit ;}
//            else{ $intransit_mask_val = 0; }
//            
//            $tot_mask_stk += $intransit_mask_val; 
//            
//            
//             ////////////////////////////////////////////////
//            $appreal_tot_stock = $store_appreal_stock_val + $intransit_appreal_val;  //
//            $mask_tot_stock = $store_mask_stock_val + $intransit_mask_val;
//            $tot_stk = $appreal_tot_stock + $mask_tot_stock;
//
//            $dealersList[$dealerobj->id] = $dealerobj->store_name . "::" . $store_appreal_stock_val . "::" . $store_mask_stock_val . "::" . $tot_curr_stk . "::" . "$intransit_appreal_val" . "::" . "$intransit_mask_val" . "::" . "$tot_mask_stk" . "::" . "$appreal_tot_stock" . "::" . "$mask_tot_stock" . "::" . "$tot_stk" . "::" . $dealerobj->min_stock_level;
//        
//            
//            
//        }     
         if (trim($dealerobj->min_stock_level) != "") {
            //step 1: fetch current stock value
            $query = "select sum(c.quantity * i.MRP) as curr_stock_value from it_current_stock c , it_items i where c.store_id = $dealerobj->id  and c.barcode = i.barcode and i.ctg_id not in (53,54,64,62,63,41,56,52,51,61,46,42,43,65)";
            $cobj = $db->fetchObject($query);
            if (isset($cobj) && trim($cobj->curr_stock_value) != "") {
                $store_stock_val = $cobj->curr_stock_value;
            } else {
                $store_stock_val = 0;
            }

            //step 2: fetch intransit stock value
            //$query2 = "select sum(invoice_amt) as intransit_stock_value from it_invoices where  invoice_type = 0 and store_id = $dealerobj->id and is_procsdForRetail = 0 ";
            // $query2 = "select sum(oi.price*oi.quantity) as intransit_stock_value from it_invoices o , it_invoice_items oi where oi.invoice_id = o.id and o.invoice_type = 0 and o.store_id = $dealerobj->id and o.is_procsdForRetail = 0";
            $query2 = "select sum(i.MRP*oi.quantity) as intransit_stock_value from it_invoices o , it_invoice_items oi , it_items i where oi.invoice_id = o.id and o.invoice_type in ( 0 , 6 ) and o.store_id = $dealerobj->id and o.is_procsdForRetail = 0 and oi.item_code = i.barcode";
            $tobj = $db->fetchObject($query2);
            if (isset($tobj) && trim($tobj->intransit_stock_value) != "") {
                $intransit_stock_val = $tobj->intransit_stock_value;
            } else {
                $intransit_stock_val = 0;
            }
            
            $active_amount = $db->fetchObject("select sum(order_amount) as active_amount from it_ck_orders where status=1 and store_id=$dealerobj->id");
            if (isset($active_amount) && $active_amount->active_amount != "" && $active_amount->active_amount != null) {
                $activeamt = $active_amount->active_amount;
            } else {
                $activeamt = 0;
            }

            $tot_stk_val = $store_stock_val + $intransit_stock_val;
//            if($tot_stk_val < $dealerobj->min_stock_level){
            $dealersList[$dealerobj->id] = $dealerobj->store_name . "::" . $store_stock_val . "::" . $intransit_stock_val . "::" . $tot_stk_val . "::" . $dealerobj->min_stock_level . "::" . $dealerobj->max_stock_level. "::" . $activeamt;
//            }
        }
    } 
    $db->closeConnection();
    if(!empty($dealersList)) {
       $fpath = createexcel($dealersList,$filenameas_storename);             
       unset($dealersList);          
    }
}catch(Exception $xcp){
    print $xcp->getMessage();
}

function createexcel($dealersList,$filenameas_storename){
    $db = new DBConn();
    $sheetIndex=0;
    // Create new PHPExcel object
    $objPHPExcel = new PHPExcel();
    // Create a first sheet
    $objPHPExcel->setActiveSheetIndex($sheetIndex);
    $objPHPExcel->getActiveSheet()->setTitle('Franchisees below MSL');
    $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Store ID');
    $objPHPExcel->getActiveSheet()->setCellValue('B1', 'Store Name');
    $objPHPExcel->getActiveSheet()->setCellValue('C1', 'Store Apparels Current Stock');
    $objPHPExcel->getActiveSheet()->setCellValue('D1', 'Active Order Stock');
    $objPHPExcel->getActiveSheet()->setCellValue('E1', 'Store Apparels Stock in Transit');
    $objPHPExcel->getActiveSheet()->setCellValue('F1', 'Store Apparels Total Stock Including Intransit');
    $objPHPExcel->getActiveSheet()->setCellValue('G1', 'Store Minimum Stock Level');
    $objPHPExcel->getActiveSheet()->setCellValue('H1', 'Store Maximum Stock Level');
    $objPHPExcel->getActiveSheet()->setCellValue('I1', 'Min_Difference');
    $objPHPExcel->getActiveSheet()->setCellValue('J1', 'Max_Difference');

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

    $colCount=0;
    $rowCount=3;
    
    foreach ($dealersList as $key => $value) {
        $min_diff = 0;
        $max_diff = 0;
        $arr = explode("::", $value);
        $store_name = trim($arr[0]);
        $curr_stock = trim($arr[1]);
        $stock_intransit = trim($arr[2]);
        $tot_stk = trim($arr[3]);
        $minsl = trim($arr[4]);
        $maxsl = trim($arr[5]);
        $activeamt=trim($arr[6]);
        $min_diff = $tot_stk - $minsl;
        $max_diff = $tot_stk - $maxsl + $activeamt;
        
        
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $rowCount, $key);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $rowCount, $store_name);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $rowCount, $curr_stock);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $rowCount, $activeamt);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $rowCount, $stock_intransit);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $rowCount, $tot_stk);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $rowCount, $minsl);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $rowCount, $maxsl);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $rowCount, $min_diff);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(9, $rowCount, $max_diff);
        $rowCount++;
    }    
    
    
/*    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $filename = "dealersBelowMSL_".date("Ymd-His").".xls";  
    $fpath = "/home/cottonking/daemons/dealerBelowMSLFiles/$filename";
//    $fpath = "/var/www/cottonking_new/daemons/dealerBelowMSLFiles/$filename";
    $objWriter->save(str_replace(__FILE__, $fpath, __FILE__));    */
    
// Redirect output to a client’s web browser (Excel5)
$filename = $filenameas_storename."dealersBelowMSL_".date("Ymd-His").".xls";     
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename='.$filename);
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');    
	
}