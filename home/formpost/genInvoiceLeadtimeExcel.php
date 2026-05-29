<?php

require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once "Classes/PHPExcel.php";
require_once "Classes/PHPExcel/Writer/Excel2007.php";

try {

    $db = new DBConn();

    $dtrange = isset($_GET['dtrange']) ? $_GET['dtrange'] : "";
    $storeid = isset($_GET['storeid']) ? $_GET['storeid'] : "-1";

    $dtClause = "";
    if (isset($dtrange) && trim($dtrange) != "") {

        $dtarr = explode(" - ", $dtrange);

        if (count($dtarr) == 1) {

            list($dd, $mm, $yy) = explode("-", trim($dtarr[0]));
            $sdate = "$yy-$mm-$dd";

            $dtClause = " 
                and i.invoice_dt >= '$sdate 00:00:00' 
                and i.invoice_dt <= '$sdate 23:59:59' 
            ";

        } else if (count($dtarr) == 2) {

            list($dd, $mm, $yy) = explode("-", trim($dtarr[0]));
            $sdate = "$yy-$mm-$dd";

            list($dd, $mm, $yy) = explode("-", trim($dtarr[1]));
            $edate = "$yy-$mm-$dd";

            $dtClause = " 
                and i.invoice_dt >= '$sdate 00:00:00' 
                and i.invoice_dt <= '$edate 23:59:59' 
            ";
        }
    }

    $sClause = "";
    if (
        isset($storeid) &&
        trim($storeid) != "" &&
        trim($storeid) != "-1"
    ) {

        $sClause = " and i.store_id in ($storeid) ";
    }

    $exeId = getCurrUser()->id;

    $query = "

        select 

            c.store_name,

            date(fo.first_order_active_time) as first_order_date,
            time(fo.first_order_active_time) as first_order_time,

            i.invoice_no,

            date(i.invoice_dt) as invoice_date,
            time(i.invoice_dt) as invoice_time,

            date(i.invoice_pull_date) as pull_date,
            time(i.invoice_pull_date) as pull_time,

            if(
                i.invoice_status = 1,
                'Received at store',
                'Intransit'
            ) as status,

            if(
                i.invoice_status = 1
                and i.invoice_pull_date is not null,

                round(
                    timestampdiff(
                        second,
                        i.invoice_dt,
                        i.invoice_pull_date
                    ) / 86400,
                    0
                ),

                null
            ) as transit_leadtime_days,

            if(
                fo.first_order_active_time is not null
                and i.invoice_pull_date is not null,

                round(
                    timestampdiff(
                        second,
                        fo.first_order_active_time,
                        i.invoice_pull_date
                    ) / 86400,
                    0
                ),

                null
            ) as order_pulled_leadtime_days

        from it_invoices i

        join it_codes c
            on i.store_id = c.id

        left join it_sp_invoices s
            on s.id = i.sp_invoice_id

        left join it_ck_pickgroup pg
            on binary pg.invoice_no = binary s.invoice_no

        left join (
            select
                pickgroup,
                min(active_time) as first_order_active_time
            from it_ck_orders
            where active_time is not null
            group by pickgroup
        ) fo
            on fo.pickgroup = pg.id

        where c.is_closed = 0

        and c.id in (
            select store_id
            from executive_assign
            where exe_id = $exeId
        )

        $dtClause
        $sClause

        order by i.invoice_no desc

    ";

    $avgQuery = "

        select 

            round(
                avg(
                    timestampdiff(
                        second,
                        i.invoice_dt,
                        i.invoice_pull_date
                    )
                ) / 86400,
                0
            ) as avg_days

        from it_invoices i

        join it_codes c
            on i.store_id = c.id

        where c.is_closed = 0

        and c.id in (
            select store_id
            from executive_assign
            where exe_id = $exeId
        )

        $dtClause
        $sClause

        and i.invoice_status = 1
        and i.invoice_pull_date is not null

    ";

    $avgOrderQuery = "

        select 

            round(
                avg(
                    timestampdiff(
                        second,
                        fo.first_order_active_time,
                        i.invoice_pull_date
                    )
                ) / 86400,
                0
            ) as avg_days

        from it_invoices i

        join it_codes c
            on i.store_id = c.id

        left join it_sp_invoices s
            on s.id = i.sp_invoice_id

        left join it_ck_pickgroup pg
            on binary pg.invoice_no = binary s.invoice_no

        left join (
            select
                pickgroup,
                min(active_time) as first_order_active_time
            from it_ck_orders
            where active_time is not null
            group by pickgroup
        ) fo
            on fo.pickgroup = pg.id

        where c.is_closed = 0

        and c.id in (
            select store_id
            from executive_assign
            where exe_id = $exeId
        )

        $dtClause
        $sClause

        and i.invoice_status = 1
        and i.invoice_pull_date is not null
        and fo.first_order_active_time is not null

    ";

    $avgObj = $db->fetchObject($avgQuery);
    $avgDays = ($avgObj && isset($avgObj->avg_days))
        ? $avgObj->avg_days
        : "";

    $avgOrderObj = $db->fetchObject($avgOrderQuery);
    $avgOrderDays = ($avgOrderObj && isset($avgOrderObj->avg_days))
        ? $avgOrderObj->avg_days
        : "";

    $cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_in_memory_serialized;
    $cacheSettings = array('memoryCacheSize' => '1500MB');
    PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);

    $objPHPExcel = new PHPExcel();

    $objPHPExcel->setActiveSheetIndex(0);
    $sheet = $objPHPExcel->getActiveSheet();

    $sheet->setTitle('Invoice Leadtime');

    $headers = array(
        'Store',
        '1st Order Date',
        '1st Order Time',
        'Invoice No',
        'Invoice Date',
        'Invoice Time',
        'Pull Date',
        'Pull Time',
        'Status',
        'Transit Lead Time (days)',
        'Order Lead Time (days)'
    );

    $col = 0;
    foreach ($headers as $header) {

        $sheet->setCellValueByColumnAndRow(
            $col,
            1,
            $header
        );

        $col++;
    }

    $sheet->getColumnDimension('A')->setWidth(40);
    $sheet->getColumnDimension('B')->setWidth(15);
    $sheet->getColumnDimension('C')->setWidth(15);
    $sheet->getColumnDimension('D')->setWidth(20);
    $sheet->getColumnDimension('E')->setWidth(15);
    $sheet->getColumnDimension('F')->setWidth(15);
    $sheet->getColumnDimension('G')->setWidth(15);
    $sheet->getColumnDimension('H')->setWidth(15);
    $sheet->getColumnDimension('I')->setWidth(22);
    $sheet->getColumnDimension('J')->setWidth(25);
    $sheet->getColumnDimension('K')->setWidth(25);

    $rowCount = 2;

    $result = $db->getConnection()->query($query);

    if (!$result) {
        die("Query Error : " . $db->getConnection()->error);
    }

    while ($obj = $result->fetch_object()) {

        $sheet->setCellValueByColumnAndRow(0, $rowCount, $obj->store_name);
        $sheet->setCellValueByColumnAndRow(1, $rowCount, $obj->first_order_date);
        $sheet->setCellValueByColumnAndRow(2, $rowCount, $obj->first_order_time);
        $sheet->setCellValueByColumnAndRow(3, $rowCount, $obj->invoice_no);
        $sheet->setCellValueByColumnAndRow(4, $rowCount, $obj->invoice_date);
        $sheet->setCellValueByColumnAndRow(5, $rowCount, $obj->invoice_time);
        $sheet->setCellValueByColumnAndRow(6, $rowCount, $obj->pull_date);
        $sheet->setCellValueByColumnAndRow(7, $rowCount, $obj->pull_time);
        $sheet->setCellValueByColumnAndRow(8, $rowCount, $obj->status);
        $sheet->setCellValueByColumnAndRow(9, $rowCount, $obj->transit_leadtime_days);
        $sheet->setCellValueByColumnAndRow(10, $rowCount, $obj->order_pulled_leadtime_days);

        $rowCount++;
    }

    $sheet->setCellValueByColumnAndRow(
        8,
        $rowCount,
        'Avg Transit Lead Time (days)'
    );

    $sheet->setCellValueByColumnAndRow(
        9,
        $rowCount,
        $avgDays
    );

    $rowCount++;

    $sheet->setCellValueByColumnAndRow(
        8,
        $rowCount,
        'Avg Order Lead Time (days)'
    );

    $sheet->setCellValueByColumnAndRow(
        10,
        $rowCount,
        $avgOrderDays
    );

    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="InvoiceLeadTime.xls"');
    header('Cache-Control: max-age=0');

    $objWriter = PHPExcel_IOFactory::createWriter(
        $objPHPExcel,
        'Excel5'
    );

    $objWriter->save('php://output');

} catch (Exception $xcp) {

    print $xcp->getMessage();
}

?>