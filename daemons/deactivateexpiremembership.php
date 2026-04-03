<?php

//include '../it_config.php'; //Test path for config file
include '/var/www/html/linenking/it_config.php'; //Live path for config file
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "Classes/PHPExcel.php";
require_once 'Classes/PHPExcel/Writer/Excel2007.php';
require_once "lib/email/EmailHelper.php";

$db = new DBConn();
$query = "select * from membership_customer_details where membership_expiry_date < now() and is_membership_active=1";
$output = $db->fetchObjectArray($query);
if (!empty($output) && is_array($output)) {
    createExcel($output);
} else {
    echo 'No data found';
}

function createExcel($output) {

    $db = new DBConn();
    foreach ($output as $obj) {
        $obj = (array) $obj;
        $query2 = "update membership_customer_details set is_membership_active=0 where id=" . $obj['id'] . "";
        $exupdate = $db->execUpdate($query2);

        //Logs of insertion
        $seleforlogs = "SELECT * FROM membership_customer_details where id=" . $obj['id'] . "";
        $selectlogsobj = $db->fetchObject($seleforlogs);

        if ($selectlogsobj) {

            $membership_number = isset($selectlogsobj->membership_number) ? $selectlogsobj->membership_number : 0;
            $member_mobno = isset($selectlogsobj->member_mobno) ? $selectlogsobj->member_mobno : '';
            $member_name = isset($selectlogsobj->member_name) ? $selectlogsobj->member_name : '';
            $membership_enroll_amt = isset($selectlogsobj->membership_enroll_amt) ? $selectlogsobj->membership_enroll_amt : 0;
            $membership_enroll_date = isset($selectlogsobj->membership_enroll_date) ? $selectlogsobj->membership_enroll_date : '0000-00-00';
            $membership_expiry_date = isset($selectlogsobj->membership_expiry_date) ? $selectlogsobj->membership_expiry_date : '0000-00-00';
            $member_enroll_bystore = isset($selectlogsobj->member_enroll_bystore) ? $selectlogsobj->member_enroll_bystore : '';
            $member_last_purchase = isset($selectlogsobj->member_last_purchase) ? $selectlogsobj->member_last_purchase : '0000-00-00 00:00:00';
            $is_membership_active = isset($selectlogsobj->is_membership_active) ? $selectlogsobj->is_membership_active : 0;
            $query_executedby = 1; //it-admin userid
            $query_type = Membership_querytype::Delete;

            $inserlogs = "insert into membership_customer_details_logs SET membership_number = '$membership_number', member_mobno = '$member_mobno', member_name = '$member_name', membership_enroll_amt = '$membership_enroll_amt', membership_enroll_date = '$membership_enroll_date', membership_expiry_date = '$membership_expiry_date', member_enroll_bystore = '$member_enroll_bystore', member_last_purchase = '$member_last_purchase', is_membership_active = '$is_membership_active', query_type = '$query_type', query_executedby = '$query_executedby', query_exe_time = now(), update_date = now()";
            $insertresult = $db->execInsert($inserlogs);
        }
    }

//  print_r($exupdate);

    $sheetIndex = 0;
    // Create new PHPExcel object
    $objPHPExcel = new PHPExcel();
    // Create a first sheet
    $objPHPExcel->setActiveSheetIndex($sheetIndex);
    $objPHPExcel->getActiveSheet()->setTitle('Expired Membership Users');
    $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Sr No.');
    $objPHPExcel->getActiveSheet()->setCellValue('B1', 'Membership Number');
    $objPHPExcel->getActiveSheet()->setCellValue('C1', 'Member Mobno');
    $objPHPExcel->getActiveSheet()->setCellValue('D1', 'Member Name');
    $objPHPExcel->getActiveSheet()->setCellValue('E1', 'Membership Enrollment Amt');
    $objPHPExcel->getActiveSheet()->setCellValue('F1', 'Membership Enrollment Date');
    $objPHPExcel->getActiveSheet()->setCellValue('G1', 'Membership Expiry Date');
    $objPHPExcel->getActiveSheet()->setCellValue('H1', 'Membership Enrollment Bystore');
    $objPHPExcel->getActiveSheet()->setCellValue('I1', 'Member Last Purchase date');

    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);

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
    $objPHPExcel->getActiveSheet()->getStyle('F1')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('G1')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('H1')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('I1')->applyFromArray($styleArray);

    $objPHPExcel->getActiveSheet()->getStyle('A')->applyFromArray($cellstyleArray);
    $objPHPExcel->getActiveSheet()->getStyle('B')->applyFromArray($cellstyleArray);
    $objPHPExcel->getActiveSheet()->getStyle('C')->applyFromArray($cellstyleArray);
    $objPHPExcel->getActiveSheet()->getStyle('D')->applyFromArray($cellstyleArray);
    $objPHPExcel->getActiveSheet()->getStyle('E')->applyFromArray($cellstyleArray);
    $objPHPExcel->getActiveSheet()->getStyle('F')->applyFromArray($cellstyleArray);
    $objPHPExcel->getActiveSheet()->getStyle('G')->applyFromArray($cellstyleArray);
    $objPHPExcel->getActiveSheet()->getStyle('H')->applyFromArray($cellstyleArray);
    $objPHPExcel->getActiveSheet()->getStyle('I')->applyFromArray($cellstyleArray);

    $colCount = 0;
    $rowCount = 2;
//    print_r($output);
    foreach ($output as $obj) {
//            print_r($obj);

        $obj = (array) $obj;

        $store_nameselqry = $db->fetchObject("select store_name from it_codes where id=" . $obj['member_enroll_bystore'] . " and usertype=4");
        $store_name = $store_nameselqry->store_name;
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $rowCount, $rowCount - 1);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $rowCount, $obj['membership_number']);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $rowCount, $obj['member_mobno']);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $rowCount, $obj['member_name']);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $rowCount, $obj['membership_enroll_amt']);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $rowCount, $obj['membership_enroll_date']);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $rowCount, $obj['membership_expiry_date']);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $rowCount, $store_name);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $rowCount, $obj['member_last_purchase']);
        $rowCount++;
    }
//    exit();
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $filename = "expiremebership_details" . date("Ymd-His") . ".xls";
    $fpath = "/var/www/html/linenking/daemons/expiremebershipdeatilscsv/$filename"; //live folder path to save csv
//    $fpath = "C:/xampp/htdocs/linenking/daemons/expiremebershipdeatilscsv/$filename"; //local folder path to save csv
    $objWriter->save(str_replace(__FILE__, $fpath, __FILE__));

    sendEmail($fpath);
}

function sendEmail($fpath) {
//         print_r($fpath);exit();
    $filename = basename($fpath);
    $db = new DBconn();
    $emailHelper = new EmailHelper();
    $toArray = array();
    $emaillist = $db->fetchObjectArray("SELECT DISTINCT email FROM it_codes WHERE usertype=6");

    foreach ($emaillist as $emailObj) {
        $toArray[] = $emailObj->email;
    }
    if (!empty($toArray)) {
        print "<br>";
        $subject = "Cottonking Membership Expiry";
        $body = "<p>This email provides a list of Member details whose membership is expired. These Membership will be automatically canceled.</p><br/>";
        $body .= "PFA $filename<br/>";
        $errormsg = $emailHelper->send($toArray, $subject, $body, array($fpath));
        print "<br>EMAIL SENT RESP:" . $errormsg;
        if ($errormsg != "0") {
            $errors['mail'] = " <br/> Error in sending mail, please try again later.";
        }
    }
}
