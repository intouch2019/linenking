<!--#!/usr/bin/php -q-->
<?php
include '/var/www/html/linenking/it_config.php';
//include '/var/www/limelight_new/it_config.php';


require_once 'lib/db/DBConn.php';
require_once 'lib/core/Constants.php';
require_once "Classes/PHPExcel.php";
require_once 'Classes/PHPExcel/Writer/Excel2007.php';
require_once "lib/email/EmailHelper.php";

//excel file name while be as below 
// dealersBelowMSL_".date("Ymd-His").".xls


try {
    $db = new DBConn();
    $cnt = 0;
    $dealersList = array();
    $alldealersobj = $db->fetchObjectArray("select id,store_name,min_stock_level  from it_codes where usertype = ".UserType::Dealer."  and is_closed = 0 " );  // and inactive = 0 
    
    foreach ($alldealersobj as $dealerobj) {

        $dt = new DateTime();
        $serverdate = $dt->format('Y-m-d H:i:s');

        //checking createtime from it_store_pingtime stock table
        $query = "select  pingtime from it_store_pingtime where  store_id=$dealerobj->id  ";
        // echo $query;
        $cobj = $db->fetchObject($query);
        if (isset($cobj)) {
            $synchdate = $cobj->pingtime;
            $diffdate = floor((strtotime($serverdate) - strtotime($synchdate)) / (60 * 60 * 24));
            if ($diffdate > 7) {

                $dealersList[$dealerobj->id] = $dealerobj->store_name . "::" . $synchdate . "::" . $serverdate . "::" . "Store Not Synch From Last Seven Days";
//            
            }

        }
    }
  //  print_r($dealersList);
    $db->closeConnection();
    if (!empty($dealersList)) {
        $fpath = createexcel($dealersList);
        unset($dealersList);
        //echo 'path' . $fpath;
        sendEmail($fpath);
    } else {

        sendEmail1();
    }
} catch (Exception $xcp) {
    print $xcp->getMessage();
}

function createexcel($dealersList) {
    $db = new DBConn();
    $sheetIndex = 0;
    // Create new PHPExcel object
    $objPHPExcel = new PHPExcel();
    // Create a first sheet
    $objPHPExcel->setActiveSheetIndex($sheetIndex);
    $objPHPExcel->getActiveSheet()->setTitle('Franchisees Not Synch');
    $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Store ID');
    $objPHPExcel->getActiveSheet()->setCellValue('B1', 'Store Name');
    $objPHPExcel->getActiveSheet()->setCellValue('C1', 'Last Synch Date');
    $objPHPExcel->getActiveSheet()->setCellValue('D1', 'Date Check');
    $objPHPExcel->getActiveSheet()->setCellValue('E1', 'Status');

    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);



    $styleArray = array(
        'font' => array(
            'bold' => true,
            'size' => 10,
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

    $colCount = 0;
    $rowCount = 3;

    foreach ($dealersList as $key => $value) {
        $arr = explode("::", $value);
        $store_name = trim($arr[0]);
        $synchdateUpdate = trim($arr[1]);
        $serverdate = trim($arr[2]);
        $status = trim($arr[3]);



        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $rowCount, $key);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $rowCount, $store_name);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $rowCount, $synchdateUpdate);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $rowCount, $serverdate);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $rowCount, $status);

        $rowCount++;
    }



    
     $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $filename = "dealersNotSynch_".date("Ymd-His").".xls";  
    //$fpath = "/home/limelight/daemons/dealerBelowMSLFiles/$filename";
     $fpath = "/var/www/html/linenking/daemons/dealerBelowMSLFiles/$filename";
        //$fpath = "/var/www/limelight_new/daemons/dealerNotSynchFiles/$filename";
//    $fpath = "/var/www/cottonking_new/daemons/dealerBelowMSLFiles/$filename";
    $objWriter->save(str_replace(__FILE__, $fpath, __FILE__));    
    
//    return $filename;
    return $fpath;
    
    
}

function sendEmail($fpath) {

    $filename = basename($fpath);
    //echo 'filename' . $filename;
    $db = new DBconn();
    $emailHelper = new EmailHelper();
    $qry = "select * from it_codes where usertype = " . UserType::CKAdmin . " and id in (68,129,130)"; //and id in (67,218)"; //specific id chks for koushik n kunal
    $aobjs = $db->fetchObjectArray($qry);
    // sends email to koushik,kunal
    if ($aobjs) {
        $toArray = array();
        foreach ($aobjs as $aobj) {
            $emails = explode(",", $aobj->email);


            foreach ($emails as $email) {
                array_push($toArray, $email);
            }
        }
        if (!empty($toArray)) {
            print "<br>";
            //print_r($toArray);
            $subject = "Linenking Franchisee(s) Store Sales And Stock Not Synch From Last Seven Days";
            $body = "<p>This Weekly Email Provides a List Of Franchisees(s) Store Sales And Stock Not Synch From Last Seven Days. </p><br/>";
            $body .= "PFA, $filename<br/>";
            $errormsg = $emailHelper->send($toArray, $subject, $body, array($fpath));
            print "<br>EMAIL SENT RESP:" . $errormsg;
            if ($errormsg != "0") {
                $errors['mail'] = " <br/> Error in sending mail, please try again later.";
            }
        }
    }
}

function sendEmail1() {

    //$filename = basename($fpath);
    //echo 'filename' . $filename;
    $db = new DBconn();
    $emailHelper = new EmailHelper();
    $qry = "select * from it_codes where usertype = " . UserType::CKAdmin . " and id in (68,129,130)"; //and id in (67,218)"; //specific id chks for koushik n kunal
    print $qry;
    $aobjs = $db->fetchObjectArray($qry);
    // sends email to koushik,kunal
    if ($aobjs) {
        $toArray = array();
        foreach ($aobjs as $aobj) {
            $emails = explode(",", $aobj->email);
            // $emails = "abhamare@intouchrewards.com";

            foreach ($emails as $email) {
                array_push($toArray, $email);
            }
        }
        if (!empty($toArray)) {
            print "<br>";
            //print_r($toArray);
            $subject = "Linenking Franchisee(s) Store Sales And Stock Not Synch From Last Seven Days";
            $body = "<p>This Weekly Email Provides a List Of Franchisees(s) Store Not Synch From Last Seven Days. </p><br/>";
            //$body .= "PFA, $filename<br/>";
            $body .= "No Store Synch From Last Seven Days";
            $errormsg = $emailHelper->send($toArray, $subject, $body, null);
            print "<br>EMAIL SENT RESP:" . $errormsg;
            if ($errormsg != "0") {
                $errors['mail'] = " <br/> Error in sending mail, please try again later.";
            }
        }
    }
}
