<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "Classes/PHPExcel.php";
require_once 'Classes/PHPExcel/Writer/Excel2007.php';



$db = new DBConn();
$dtrange = $_GET['dtrange'] ? $_GET['dtrange'] : false;
$storeid = $_GET['storeid'] ? $_GET['storeid'] : false;
//print_r($_GET);
if(isset($dtrange) && trim($dtrange)!=""){
    $dtClause = "" ;
    $dtarr = explode(" - ", $dtrange);
    //$_SESSION['storeid'] = $this->storeidreport;
    if (count($dtarr) == 1) {
            list($dd,$mm,$yy) = explode("-",$dtarr[0]);
            $sdate = "$yy-$mm-$dd";		
          
            $dtClause = "o.bill_datetime >= '$sdate 00:00:00' and o.bill_datetime <= '$sdate 23:59:59' ";
    } else if (count($dtarr) == 2) {
            list($dd,$mm,$yy) = explode("-",$dtarr[0]);
            $sdate = "$yy-$mm-$dd";
            list($dd,$mm,$yy) = explode("-",$dtarr[1]);
            $edate = "$yy-$mm-$dd";		
         
            $dtClause = " o.bill_datetime >= '$sdate 00:00:00' and o.bill_datetime <= '$edate 23:59:59' ";
    } else {
            $dtClause = "";
    }
}else{ $dtClause=""; }

if(isset($storeid) && trim($storeid)!="" && trim($storeid) != "-1"){
   $sClause=" and o.store_id in ($storeid)";
   
}else{ $sClause="" ;}



$sheetIndex=0;
// Create new PHPExcel object
$objPHPExcel = new PHPExcel();
// Create a first sheet
$objPHPExcel->setActiveSheetIndex($sheetIndex);
$objPHPExcel->getActiveSheet()->setTitle('Ratio Percentage');
$objPHPExcel->getActiveSheet()->setCellValue('A1', 'Total no of Bills');
$objPHPExcel->getActiveSheet()->setCellValue('B1', 'Customer mobile no against Bills');
$objPHPExcel->getActiveSheet()->setCellValue('C1', 'Percentage of Mobile no against Total no of Bills');
$objPHPExcel->getActiveSheet()->setCellValue('D1', 'Multiple times used mobile no list(Top 20)');
$objPHPExcel->getActiveSheet()->setCellValue('E1', 'Count');    

$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(40);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(40);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);

 
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

$objPHPExcel->getActiveSheet()->getStyle('A')->applyFromArray($cellstyleArray);
$objPHPExcel->getActiveSheet()->getStyle('B')->applyFromArray($cellstyleArray);
$objPHPExcel->getActiveSheet()->getStyle('C')->applyFromArray($cellstyleArray);
$objPHPExcel->getActiveSheet()->getStyle('D')->applyFromArray($cellstyleArray);
$objPHPExcel->getActiveSheet()->getStyle('E')->applyFromArray($cellstyleArray);


$rowCount=2;
if($storeid=="All Stores"){
  $query = "select bill_no,cust_phone from it_orders o where $dtClause"; 
  //echo $query;
//exit();
}
else{
$query = "select bill_no,cust_phone from it_orders o where store_id = $storeid  and $dtClause";
}

if($storeid=="All Stores"){
  $query1 = "SELECT  bill_no,cust_phone,COUNT(*) as icount FROM it_orders o where cust_phone is not null and $dtClause GROUP BY cust_phone ORDER BY COUNT(*) DESC limit 20 ";  
}
else{

$query1="SELECT  bill_no,cust_phone,COUNT(*) as icount FROM it_orders o where cust_phone is not null and store_id = $storeid  and $dtClause GROUP BY cust_phone ORDER BY COUNT(*) DESC limit 20 ";
}

$objs = $db->fetchObjectArray($query);
$rowcount=0;$tot_billno=0; $mobile_cnt=0;

$length = count($objs);
foreach ($objs as $obj) {
    
    if($obj->bill_no){
        $tot_billno++;
    }
     if($obj->cust_phone){
        $mobile_cnt++;
    }
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $rowCount, $obj->bill_no);
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $rowCount, $obj->cust_phone);
    
    $rowCount++;
    
    
} 
if($rowCount>=$length){
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $rowCount, "Total No Of Bills=".$tot_billno);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $rowCount, "Total No of Mobileno=".$mobile_cnt);
        if($tot_billno<=0){
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $rowCount);
        }
        else{
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $rowCount, "Percentage=".($mobile_cnt/$tot_billno)*100);
        }
    }
    
    
   $objs1 = $db->fetchObjectArray($query1);
  $rowCount=2;
   foreach ($objs1 as $obj) {

    if($obj->bill_no){
        $tot_billno++;
    }
     if($obj->cust_phone){
        $mobile_cnt++;
    }
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $rowCount, $obj->cust_phone);
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $rowCount, $obj-> icount);
    $rowCount++;
    
    
} 
   


// Redirect output to a client’s web browser (Excel5)
$filename = "RatioPercentage_".date("Ymd-His").".xls";     
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename='.$filename);
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');    



	

