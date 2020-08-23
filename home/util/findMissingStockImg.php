<?php
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
require_once "Classes/PHPExcel.php";
require_once 'Classes/PHPExcel/Writer/Excel2007.php';

$db = new DBConn();
//fetch all designs frm db
//$query = "select * from it_ck_designs order by ctg_id";
$query = "select c.*,ctg.name as ctg_name from it_ck_designs c , it_categories ctg where c.ctg_id = ctg.id order by c.ctg_id ";
$allDesigns = $db->fetchObjectArray($query);
$missingImg = array();
//print "<br>";
//print_r($allDesigns);
//print "<br>";

foreach($allDesigns as $design){
    if(isset($design) && trim($design->image)!=""){
        //$filename = "8.F14392.jpeg";
        $filename = $design->image;
        $filepath = DEF_SITEURL."images/stock/".$filename;
       // var_dump($filepath);
        $sz = getimagesize($filepath);
//        print_r($sz);
        if(count($sz)>1){ 
//            print "<br>IMG EXISTS";    
        }else{ 
//            print "<br>IMG DOES NOT EXISTS"; 
            $missingImg[$design->image] = $design->ctg_name."::".$design->design_no;
        }
//        echo "<br>$filepath<br>";        
    }
}

//if(! empty($missingImg)){
    createMissImgFile($missingImg);
//}
//print "<br>BELOW IS THE LIST OF MISSING IMAGE IN FOLDER : <br>";
//print_r($missingImg);
//if (file_exists($filepath)) {
//   echo "FILE EXISTS";    
// }else{
//   echo "FILE DOES NOT EXISTS";
// }
function createMissImgFile($missingImg){
    $sheetIndex=0;
    // Create new PHPExcel object
    $objPHPExcel = new PHPExcel();
    // Create a first sheet, representing points by dealer data
    $objPHPExcel->setActiveSheetIndex($sheetIndex);
    $objPHPExcel->getActiveSheet()->setTitle('Missing Image in System');
    $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Category');
    $objPHPExcel->getActiveSheet()->setCellValue('B1', 'Design No');
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
    
    $styleArray = array(
    'font'  => array(
        'bold'  => false,
//        'color' => array('rgb' => 'FF0000'),
        'size'  => 10,
    ));
    
    $objPHPExcel->getActiveSheet()->getStyle('A')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('B')->applyFromArray($styleArray);
    $rowCount=2;
    
    foreach($missingImg as $key => $value){
        $arr = explode("::", $value);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $rowCount, $arr[0]);               
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1,$rowCount, $arr[1]);         
        $rowCount++;
    }
    
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="MissingImg.xls"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->save('php://output');
//    try{
//    header("Content-type: application/vnd.ms-excel");
//    header("Content-Disposition: attachment;Filename=missingImg.xls");
//
//    echo "<html>";
//    echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=Windows-1252\">";
//    echo "<body>";
//    echo "<table>";
//    echo "<th>Category</th>";
//    echo "<th>Design No</th>";
//
//      foreach($missingImg as $key => $value){
//          echo "<tr>";
//          $arr = explode("::", $value);
//          echo "<td>$arr[0]</td><td>$arr[1]</td>" ;
//          echo "</tr>";          
//      }
//    echo "</table>";
//    echo "</body>";
//    echo "</html>";
//    }catch(Exception $xcp){
//        print $xcp->getMessage();
//    }
}
