<?php
include "../../it_config.php";
require_once "session_check.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";

function formatLeadTimeDays($days) {
    if ($days === null || $days === "") {
        return "-";
    }
    return intval($days) . " days";
}

$aColumns = array(
    'store_name',
    'first_order_date',
    'first_order_time',
    'invoice_no',
    'invoice_date',
    'invoice_time',
    'pull_date',
    'pull_time',
    'invoice_status',
    'transit_leadtime_days',
    'order_leadtime_days'
);

$sColumns = array(
    'c.store_name',
    "date(fo.first_order_active_time)",
    "time(fo.first_order_active_time)",
    'i.invoice_no',
    "date(i.invoice_dt)",
    "time(i.invoice_dt)",
    "date(i.invoice_pull_date)",
    "time(i.invoice_pull_date)",
    'i.invoice_status',
    "if(i.invoice_status=1 and i.invoice_pull_date is not null,
        round(timestampdiff(second,i.invoice_dt,i.invoice_pull_date)/86400,0),
        null)",
    "if(fo.first_order_active_time is not null and i.invoice_pull_date is not null,
        round(timestampdiff(second,fo.first_order_active_time,i.invoice_pull_date)/86400,0),
        null)"
);

$db = new DBConn();

$dtrange = isset($_GET['dtrange']) ? $_GET['dtrange'] : "";
$storeid = isset($_GET['storeid']) ? $_GET['storeid'] : "";

/*
 * Paging
 */
$sLimit = "";
if (isset($_GET['iDisplayStart']) && $_GET['iDisplayLength'] != '-1') {

    $start = intval($_GET['iDisplayStart']);
    $length = intval($_GET['iDisplayLength']);

    $sLimit = " LIMIT $start, $length ";
}

/*
 * Ordering
 */
$sOrder = " ORDER BY i.invoice_no DESC ";

$sortColumns = array(
    'c.store_name',
    "date(fo.first_order_active_time)",
    "time(fo.first_order_active_time)",
    'i.invoice_no',
    "date(i.invoice_dt)",
    "time(i.invoice_dt)",
    "date(i.invoice_pull_date)",
    "time(i.invoice_pull_date)",
    'i.invoice_status',
    'transit_leadtime_days',
    'order_leadtime_days'
);

if (isset($_GET['iSortCol_0'])) {

    $sOrder = " ORDER BY ";

    for ($i = 0; $i < intval($_GET['iSortingCols']); $i++) {

        $sortCol = intval($_GET['iSortCol_' . $i]);

        if ($_GET['bSortable_' . $sortCol] == "true") {

            if (!isset($sortColumns[$sortCol])) {
                continue;
            }

            $dir = strtolower($_GET['sSortDir_' . $i]) == 'asc' ? 'asc' : 'desc';

            $sOrder .= $sortColumns[$sortCol] . " $dir, ";
        }
    }

    $sOrder = substr_replace($sOrder, "", -2);

    if ($sOrder == " ORDER BY") {
        $sOrder = "";
    }
}

/*
 * Searching
 */
$sWhere = "";

if (isset($_GET['sSearch']) && trim($_GET['sSearch']) != "") {

    $search = $db->getConnection()->real_escape_string(trim($_GET['sSearch']));

    $searchArr = array();

    foreach ($sColumns as $col) {
        $searchArr[] = "$col LIKE '%$search%'";
    }

    $sWhere = " AND (" . implode(" OR ", $searchArr) . ")";
}

/*
 * Individual column filtering
 */
for ($i = 0; $i < count($sColumns); $i++) {

    if (
        isset($_GET['bSearchable_' . $i]) &&
        $_GET['bSearchable_' . $i] == "true" &&
        isset($_GET['sSearch_' . $i]) &&
        trim($_GET['sSearch_' . $i]) != ''
    ) {

        $searchVal = $db->getConnection()->real_escape_string(trim($_GET['sSearch_' . $i]));

        $sWhere .= " AND " . $sColumns[$i] . " LIKE '%$searchVal%' ";
    }
}

/*
 * Date Filter
 */
$dtClause = "";

if ($dtrange != "") {

    $dtarr = explode(" - ", $dtrange);

    if (count($dtarr) == 1) {

        list($dd, $mm, $yy) = explode("-", trim($dtarr[0]));

        $sdate = "$yy-$mm-$dd";

        $dtClause = " 
            AND i.invoice_dt >= '$sdate 00:00:00'
            AND i.invoice_dt <= '$sdate 23:59:59'
        ";

    } else if (count($dtarr) == 2) {

        list($dd1, $mm1, $yy1) = explode("-", trim($dtarr[0]));
        list($dd2, $mm2, $yy2) = explode("-", trim($dtarr[1]));

        $sdate = "$yy1-$mm1-$dd1";
        $edate = "$yy2-$mm2-$dd2";

        $dtClause = "
            AND i.invoice_dt >= '$sdate 00:00:00'
            AND i.invoice_dt <= '$edate 23:59:59'
        ";
    }
}

/*
 * Store Filter
 */
$sClause = "";

if ($storeid != "" && $storeid != "-1") {
    $sClause = " AND i.store_id IN ($storeid) ";
}

$exeId = getCurrUser()->id;

/*
 * Main Query
 */
$sQuery = "
    SELECT SQL_CALC_FOUND_ROWS
        c.store_name,

        DATE(fo.first_order_active_time) AS first_order_date,
        TIME(fo.first_order_active_time) AS first_order_time,

        i.invoice_no,

        DATE(i.invoice_dt) AS invoice_date,
        TIME_FORMAT(i.invoice_dt, '%H:%i:%s') AS invoice_time,

        DATE(i.invoice_pull_date) AS pull_date,
        TIME(i.invoice_pull_date) AS pull_time,

        i.invoice_status,

        IF(
            i.invoice_status = 1
            AND i.invoice_pull_date IS NOT NULL,
            ROUND(
                TIMESTAMPDIFF(
                    SECOND,
                    i.invoice_dt,
                    i.invoice_pull_date
                ) / 86400,
            0),
            NULL
        ) AS transit_leadtime_days,

        IF(
            fo.first_order_active_time IS NOT NULL
            AND i.invoice_pull_date IS NOT NULL,

            ROUND(
                TIMESTAMPDIFF(
                    SECOND,
                    fo.first_order_active_time,
                    i.invoice_pull_date
                ) / 86400,
            0),

            NULL
        ) AS order_leadtime_days

    FROM it_invoices i

    INNER JOIN it_codes c
        ON i.store_id = c.id

    LEFT JOIN it_sp_invoices s
        ON s.id = i.sp_invoice_id

    LEFT JOIN it_ck_pickgroup pg
        ON CONVERT(pg.invoice_no USING utf8)
           COLLATE utf8_unicode_ci =
           CONVERT(s.invoice_no USING utf8)
           COLLATE utf8_unicode_ci

    LEFT JOIN (
        SELECT
            pickgroup,
            MIN(active_time) AS first_order_active_time
        FROM it_ck_orders
        WHERE active_time IS NOT NULL
        GROUP BY pickgroup
    ) fo
        ON fo.pickgroup = pg.id

    WHERE c.is_closed = 0

    AND c.id IN (
        SELECT store_id
        FROM executive_assign
        WHERE exe_id = $exeId
    )

    $dtClause
    $sClause
    $sWhere

    $sOrder
    $sLimit
";

//print_r($sQuery); exit;

$objs = $db->fetchObjectArray($sQuery);

/*
 * Filtered Count
 */
$obj = $db->fetchObject("SELECT FOUND_ROWS() AS TOTAL_ROWS");

$iFilteredTotal = $obj ? $obj->TOTAL_ROWS : 0;

/*
 * Average Transit Lead Time
 */
$avgQuery = "
    SELECT
        ROUND(
            AVG(
                TIMESTAMPDIFF(
                    SECOND,
                    i.invoice_dt,
                    i.invoice_pull_date
                )
            ) / 86400,
        0) AS avg_days

    FROM it_invoices i

    INNER JOIN it_codes c
        ON i.store_id = c.id

    WHERE c.is_closed = 0

    AND c.id IN (
        SELECT store_id
        FROM executive_assign
        WHERE exe_id = $exeId
    )

    $dtClause
    $sClause
    $sWhere

    AND i.invoice_status = 1
    AND i.invoice_pull_date IS NOT NULL
";

$avgObj = $db->fetchObject($avgQuery);

$avgLeadTime = ($avgObj && isset($avgObj->avg_days))
    ? $avgObj->avg_days
    : null;

/*
 * Average Order to Pull Lead Time
 */
$avgOrderQuery = "
    SELECT
        ROUND(
            AVG(
                TIMESTAMPDIFF(
                    SECOND,
                    fo.first_order_active_time,
                    i.invoice_pull_date
                )
            ) / 86400,
        0) AS avg_days

    FROM it_invoices i

    INNER JOIN it_codes c
        ON i.store_id = c.id

    LEFT JOIN it_sp_invoices s
        ON s.id = i.sp_invoice_id

    LEFT JOIN it_ck_pickgroup pg
        ON CONVERT(pg.invoice_no USING utf8)
           COLLATE utf8_unicode_ci =
           CONVERT(s.invoice_no USING utf8)
           COLLATE utf8_unicode_ci

    LEFT JOIN (
        SELECT
            pickgroup,
            MIN(active_time) AS first_order_active_time
        FROM it_ck_orders
        WHERE active_time IS NOT NULL
        GROUP BY pickgroup
    ) fo
        ON fo.pickgroup = pg.id

    WHERE c.is_closed = 0

    AND c.id IN (
        SELECT store_id
        FROM executive_assign
        WHERE exe_id = $exeId
    )

    $dtClause
    $sClause
    $sWhere

    AND i.invoice_status = 1
    AND i.invoice_pull_date IS NOT NULL
    AND fo.first_order_active_time IS NOT NULL
";

$avgOrderObj = $db->fetchObject($avgOrderQuery);

$avgOrderLeadTime = ($avgOrderObj && isset($avgOrderObj->avg_days))
    ? $avgOrderObj->avg_days
    : null;

/*
 * Data Rows
 */
$rows = array();

$iTotal = 0;

foreach ($objs as $obj) {

    $row = array();

    foreach ($aColumns as $col) {

        if ($col == 'store_name') {

            $row[] = $obj->store_name;

        } else if ($col == 'first_order_date') {

            $row[] = $obj->first_order_date ? $obj->first_order_date : "-";

        } else if ($col == 'first_order_time') {

            $row[] = $obj->first_order_time ? $obj->first_order_time : "-";

        } else if ($col == 'invoice_no') {

            $row[] = $obj->invoice_no;

        } else if ($col == 'invoice_date') {

            $row[] = $obj->invoice_date ? $obj->invoice_date : "-";

        } else if ($col == 'invoice_time') {

            $row[] = $obj->invoice_time ? $obj->invoice_time : "-";

        } else if ($col == 'pull_date') {

            $row[] = $obj->pull_date ? $obj->pull_date : "-";

        } else if ($col == 'pull_time') {

            $row[] = $obj->pull_time ? $obj->pull_time : "-";

        } else if ($col == 'invoice_status') {

            $row[] = ($obj->invoice_status == 1)
                ? "Received at store"
                : "Intransit";

        } else if ($col == 'transit_leadtime_days') {

            $row[] = formatLeadTimeDays($obj->transit_leadtime_days);

        } else if ($col == 'order_leadtime_days') {

            $row[] = formatLeadTimeDays($obj->order_leadtime_days);

        } else {

            $row[] = "-";
        }
    }

    $rows[] = $row;

    $iTotal++;
}

$db->closeConnection();

$output = array(
    "iTotalRecords" => $iTotal,
    "iTotalDisplayRecords" => $iFilteredTotal,
    "aaData" => $rows,
    "avg_leadtime_days" => $avgLeadTime,
    "avg_order_leadtime_days" => $avgOrderLeadTime
);

echo json_encode($output);
?>