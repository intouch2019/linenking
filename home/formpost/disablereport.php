<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once "lib/core/clsProperties.php";
require_once "Classes/PHPExcel.php";
require_once  "Classes/PHPExcel/Writer/Excel2007.php";

$user = getCurrUser();
if (!$user || ($user->usertype != UserType::Admin && $user->usertype != UserType::CKAdmin && $user->usertype != UserType::Accounts)) { print "Unauthorized Access"; return;}

try{
    $db = new DBConn();
    $sheetIndex=0;
    $cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_in_memory_serialized;
    $cacheSettings = array( 'memoryCacheSize' => '1500MB');
    PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
    // Create new PHPExcel object
    $objPHPExcel = new PHPExcel();
    // Create a first sheet
    $objPHPExcel->setActiveSheetIndex($sheetIndex);
    $objPHPExcel->getActiveSheet()->setTitle('Disable Store');
    $objPHPExcel->getActiveSheet()->setCellValue('A1', 'ID');
    $objPHPExcel->getActiveSheet()->setCellValue('B1', 'Store Name');
    $objPHPExcel->getActiveSheet()->setCellValue('C1', 'Disabled by');
    $objPHPExcel->getActiveSheet()->setCellValue('D1', 'Disable reason');
            
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(100);
    $styleArray = array(
        'font'  => array(
            'bold'  => false,
           // 'color' => array('rgb' => 'FF0000'),
            'size'  => 10,
        ));
    $objPHPExcel->getActiveSheet()->getStyle('A')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('B')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('C')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('D')->applyFromArray($styleArray);
    
    $yes=0;
    $colCount=0;
    $rowCount=2;
//    $qry1="select * from it_ck_properties where id=1 and value=1";
//    $allstoredisabled=$db->fetchObject($qry1);
    $dbProperties = new dbProperties();
    $allstoredisabled = $dbProperties->getBoolean(Properties::DisableUserLogins);
    if($allstoredisabled){
        $result="select id,store_name,(case when inactive=1 then inactivating_reason else disablelogins_reason end) as disable_reason,(case when inactive=1 and inactivated_by is not null then inactivated_by else loginsdisable_by end)as login_disabled_by from it_codes where usertype=4  and is_closed=0 and disablelogins_reason is not null;";
        $yes=1;
    }else{
        $result="select id,store_name,inactivated_by,inactivating_reason from it_codes where inactive=1 and is_closed=0 and usertype=".UserType::Dealer." and inactivating_reason is not null";//group by inv.distid , td.id,mate.id,cate.id,ttkitem.id  order by inv.distid "; //$categroupBy
    }
    //error_log("\nSalesOvr Exl TAB 1:".$query."\n",3,"tmp.txt");
    //$objs = $db->fetchObjectArray($query);
    //print $result;exit();
     $objs = $db->getConnection()->query($result);
     while($obj=$objs->fetch_object()){    
         $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $rowCount, $obj->id);
         $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $rowCount, $obj->store_name);
         if($yes==1){
             if(isset($obj->login_disabled_by)){
            $qrywhom="select store_name from it_codes where id=$obj->login_disabled_by";
            $whom=$db->fetchObject($qrywhom);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $rowCount, $whom->store_name);
            }
         }else{
             if(isset($obj->inactivated_by)){
            $qrywhom="select store_name from it_codes where id=$obj->inactivated_by";
            $whom=$db->fetchObject($qrywhom);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $rowCount, $whom->store_name); 
            }
         }
         if($yes==1){
            if(isset( $obj->disable_reason)){ 
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $rowCount, $obj->disable_reason);        
            }else{
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $rowCount, "NO REASON");        
            }
         }else{
         $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $rowCount, $obj->inactivating_reason);        
         }
         $rowCount++;
     }   
    

// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="DisableStores.xls"');
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');     
}catch(Exception $xcp){
    print $xcp->getMessage();
}
?>
