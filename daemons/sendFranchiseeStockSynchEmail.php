<!--//#!/usr/bin/php -q-->
<?php
include '/var/www/html/linenking/it_config.php';
//include '/var/www/limelight_new/it_config.php';


require_once 'lib/db/DBConn.php';
require_once 'lib/core/Constants.php';
require_once "Classes/PHPExcel.php";
require_once 'Classes/PHPExcel/Writer/Excel2007.php';
require_once "lib/email/EmailHelper.php";

//excel file name while be as below 
// stockNotSync_".date("Ymd-His").".xls


try {
    $db = new DBConn();
    $dt = new DateTime();
    $serverdate = $dt->format('Y-m-d H:i:s');
    $cnt = 0;
    $dealersList = array();
    
    $alldealersobj = $db->fetchObjectArray("select id,store_name from it_codes where usertype = " . UserType::Dealer . " and is_closed = 0 and id not in (98,164,367,436) order by id "); //and inactive = 0
    
    foreach ($alldealersobj as $dealerobj) {

        //checking createtime from it_current stock table
        $query = "select createtime from it_current_stock where store_id = $dealerobj->id order by id desc limit 1 ";
        $cobj = $db->fetchObject($query);
        if (isset($cobj)) {
            $synchdate = $cobj->createtime;
            $diffdate = floor((strtotime($serverdate) - strtotime($synchdate)) / (60 * 60 * 24));
            if ($diffdate > 2) {
                
		$query2 = "select pingtime from it_store_pingtime where store_id= $dealerobj->id ";
     		 $cobj2 = $db->fetchObject($query2);
                //checking updatetime from it_current stock table if diffencce is geather than 2 i.e not Synch within 2 days to portal
                $query1 = "select updatetime from it_current_stock where store_id = $dealerobj->id order by updatetime desc limit 1 ";
                $cobj1 = $db->fetchObject($query1);
                if (isset($cobj1)) {
                    $synchdateUpdate = $cobj1->updatetime;
                    $diffdate2 = floor((strtotime($serverdate) - strtotime($synchdateUpdate)) / (60 * 60 * 24));
                    if($diffdate < $diffdate2){ 
                        $showdiff = $diffdate;
                        $showdate = $synchdate;
                    }else{ 
                        $showdiff = $diffdate2; 
                        $showdate = $synchdateUpdate;
                    }
                    if ($showdiff > 2) {
                        $dealersList[$dealerobj->id] = $dealerobj->store_name . "::" . $showdate . "::" . $serverdate . "::" . "Store Stock was Synced Before $showdiff Days "."::".$cobj2->pingtime;
                    }
                }else{
                    $dealersList[$dealerobj->id] = $dealerobj->store_name . "::" . $synchdate . "::" . $serverdate . "::" . "Store Stock was Synced Before $diffdate Days "."::".$cobj2->pingtime;
                }
            }
//            echo 'serverdate'.$serverdate;
//            echo 'synchdate'.$synchdate;
//            echo 'difference'.$diffdate;
//                echo '</br>';
        }
    }
//    print_r($dealersList);    exit();
    $db->closeConnection();
    if (!empty($dealersList)) {
        $fpath = createexcel($dealersList);
        unset($dealersList);
        //echo 'path' . $fpath;
        sendEmail($fpath);
    }else{
        sendEmail2();
    }
} catch (Exception $xcp) {
    print $xcp->getMessage();
}

function createexcel($dealersList) {
//    $db = new DBConn();
    $sheetIndex = 0;
    // Create new PHPExcel object
    $objPHPExcel = new PHPExcel();
    // Create a first sheet
    $objPHPExcel->setActiveSheetIndex($sheetIndex);
    $objPHPExcel->getActiveSheet()->setTitle('Stock Not Synch');
    $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Store ID');
    $objPHPExcel->getActiveSheet()->setCellValue('B1', 'Store Name');
    $objPHPExcel->getActiveSheet()->setCellValue('C1', 'Synch Date');
//    $objPHPExcel->getActiveSheet()->setCellValue('C1', 'Store Current Stock');
    $objPHPExcel->getActiveSheet()->setCellValue('D1', 'Current Date');
    $objPHPExcel->getActiveSheet()->setCellValue('E1', 'Status');
        $objPHPExcel->getActiveSheet()->setCellValue('F1', 'Ping Time');
//    $objPHPExcel->getActiveSheet()->setCellValue('F1', 'Store Minimum Stock Level');
//        
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(35);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(35);
    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);


    $styleArray = array(
        'font' => array(
            'bold' => true,
            'size' => 12,
        ),
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
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
//    
    $objPHPExcel->getActiveSheet()->getStyle('A')->applyFromArray($cellstyleArray);
    $objPHPExcel->getActiveSheet()->getStyle('B')->applyFromArray($cellstyleArray);
    $objPHPExcel->getActiveSheet()->getStyle('C')->applyFromArray($cellstyleArray);
    $objPHPExcel->getActiveSheet()->getStyle('D')->applyFromArray($cellstyleArray);
    $objPHPExcel->getActiveSheet()->getStyle('E')->applyFromArray($cellstyleArray);
    $objPHPExcel->getActiveSheet()->getStyle('F')->applyFromArray($cellstyleArray);
//    
//    $colCount = 0;
    $rowCount = 3;

    foreach ($dealersList as $key => $value) {
        $arr = explode("::", $value);
        $store_name = trim($arr[0]);
        $synchdateUpdate = trim($arr[1]);
        $serverdate = trim($arr[2]);
        $status = trim($arr[3]);
        //$msl = trim($arr[4]); 
            $pingtime= trim($arr[4]);

        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $rowCount, $key);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $rowCount, $store_name);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $rowCount, $synchdateUpdate);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $rowCount, $serverdate);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $rowCount, $status);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $rowCount, $pingtime);
//        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $rowCount, $msl);        
        $rowCount++;
    }


    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $filename = "stockNotSync_" . date("Ymd-His") . ".xls";
//    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
//    $filename = "stockNotSync_" . date("Ymd-His") . ".xlsx";
    
     $fpath = "/var/www/html/linenking/daemons/stockNotSyncFiles/$filename";
//    $fpath = "/var/www/limelight_new/daemons/stockNotSyncFiles/$filename";
//    $fpath = "C:/xampp/htdocs/ck_new_y/daemons/stockNotSyncFiles/$filename";
    
    $objWriter->save(str_replace(__FILE__, $fpath, __FILE__));

//    return $filename;
    return $fpath;
}

function sendEmail($fpath) {

    $filename = basename($fpath);
    //echo 'filename' . $filename;
//    $db = new DBconn();
    $emailHelper = new EmailHelper();
//    $qry = "select email from it_codes where usertype = " . UserType::CKAdmin . " and id in (67,362,360,361,359,363)"; //and id in (67,218)"; //specific id chks for koushik n kunal
//    $aobjs = $db->fetchObjectArray($qry);
//    // sends email to koushik,kunal
//    if ($aobjs) {
        $toArray = array();
//        foreach ($aobjs as $aobj) {
//            $emails = explode(",", $aobj->email);
//            // $emails = "abhamare@intouchrewards.com";
//
//            foreach ($emails as $email) {
//                if($email != null){
//                    array_push($toArray, $email);
//                }                
//            }
//        }
        
        array_push($toArray, 'koushik.marathe@kinglifestyle.com');
        array_push($toArray, 'kunal.marathe@kinglifestyle.com');
        array_push($toArray, 'harshada.marathe@kinglifestyle.com');
        array_push($toArray, 'rohan.phalke@kinglifestyle.com');
        array_push($toArray, 'prashant.mane@kinglifestyle.com');
        array_push($toArray, 'rghule@intouchrewards.com');
        
        if (!empty($toArray)) {
            print "<br>";
            print_r($toArray);
            $subject = "Linenking Franchisee(s) List Having Stock Not Synch From Last Two Days";
            $body = "<p>Hello sir/madam, </p>";            
            $body .= "PFA, $filename<br/>";
            $body .= "<p>This Email Provides a List Of Franchisees(s) Stock Not Synch From Last Two Days. </p>";
            $errormsg = $emailHelper->send($toArray, $subject, $body, array($fpath));
            print "<br>EMAIL SENT RESP:" . $errormsg;
            if ($errormsg != "0") {
                $errors['mail'] = " <br/> Error in sending mail, please try again later.";
            }
        }
//    }
}

function sendEmail2() {

    //$filename = basename($fpath);
    //echo 'filename' . $filename;
//    $db = new DBconn();
    $emailHelper = new EmailHelper();
//    $qry = "select * from it_codes where usertype = " . UserType::CKAdmin . " and id in (67,362,360,361,359,363)"; //and id in (67,218)"; //specific id chks for koushik n kunal
//    $aobjs = $db->fetchObjectArray($qry);
//    // sends email to koushik,kunal
//    if ($aobjs) {
        $toArray = array();
//        foreach ($aobjs as $aobj) {
//            $emails = explode(",", $aobj->email);
//            // $emails = "abhamare@intouchrewards.com";
//
//            foreach ($emails as $email) {
//                array_push($toArray, $email);
//            }
//        }
    
        
        array_push($toArray, 'koushik.marathe@kinglifestyle.com');
        array_push($toArray, 'kunal.marathe@kinglifestyle.com');
        array_push($toArray, 'harshada.marathe@kinglifestyle.com');
        array_push($toArray, 'rohan.phalke@kinglifestyle.com');
        array_push($toArray, 'prashant.mane@kinglifestyle.com');
        array_push($toArray, 'rghule@intouchrewards.com');
        
    
        if (!empty($toArray)) {
            print "<br>";
            //print_r($toArray);
            $subject = "Linenking Franchisee(s) List Having Stock Not Synch From Last Two Days";
            $body = "<p>This Email Provides a List Of Franchisees(s) Stock Not Synch From Last Two Days. </p><br/>";
            //$body .= "PFA, $filename<br/>";
            $body .= "<span style='color:red'> '0' Store Found Having Stock Not Synch In Last Two Days. </span>";
            
            $errormsg = $emailHelper->send($toArray, $subject, $body, null);
            print "<br>EMAIL SENT RESP:" . $errormsg;
            if ($errormsg != "0") {
                $errors['mail'] = " <br/> Error in sending mail, please try again later.";
            }
        }
//    }
}
