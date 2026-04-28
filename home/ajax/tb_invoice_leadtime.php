<?php
include "../../it_config.php";
require_once "session_check.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";

function formatLeadTimeDays($days) {
    if ($days === null || $days === "") { return "-"; }
    $d = intval($days);
    return $d . " days";
}

$aColumns = array('store_name', 'invoice_no', 'invoice_dt', 'invoice_pull_date', 'invoice_status', 'leadtime_days');
$sColumns = array('c.store_name', 'i.invoice_no', 'i.invoice_dt', 'i.invoice_pull_date', 'i.invoice_status', 'i.invoice_pull_date');

$db = new DBConn();

$dtrange = isset($_GET['dtrange']) ? $_GET['dtrange'] : false;
$storeid = isset($_GET['storeid']) ? $_GET['storeid'] : false;

/*
 * Paging
 */
$sLimit = "";
if (isset($_GET['iDisplayStart']) && $_GET['iDisplayLength'] != '-1') {
    $sLimit = " LIMIT " . $db->getConnection()->real_escape_string($_GET['iDisplayStart']) . ", " .
            $db->getConnection()->real_escape_string($_GET['iDisplayLength']);
}

/*
 * Ordering
 */
$sOrder = " ORDER BY i.invoice_no desc ";
$sortColumns = array('c.store_name', 'i.invoice_no', 'i.invoice_dt', 'i.invoice_pull_date', 'i.invoice_status', 'leadtime_days');
if (isset($_GET['iSortCol_0'])) {
    $sOrder = " ORDER BY ";
    for ($i = 0; $i < intval($_GET['iSortingCols']); $i++) {
        if ($_GET['bSortable_' . intval($_GET['iSortCol_' . $i])] == "true") {
            $colIdx = intval($_GET['iSortCol_' . $i]);
            if (!isset($sortColumns[$colIdx])) { continue; }
            $sOrder .= $sortColumns[$colIdx] . "
			 	" . $db->getConnection()->real_escape_string($_GET['sSortDir_' . $i]) . ", ";
        }
    }
    $sOrder = substr_replace($sOrder, "", -2);
    if ($sOrder == " ORDER BY ") { $sOrder = ""; }
}

/*
 * Filtering
 */
$sWhere = "";
if (isset($_GET['sSearch']) && $_GET['sSearch'] != "") {
    $sWhere = "WHERE (";
    for ($i = 0; $i < count($sColumns); $i++) {
        $sWhere .= $sColumns[$i] . " LIKE '%" . $db->getConnection()->real_escape_string($_GET['sSearch']) . "%' OR ";
    }
    $sWhere = substr_replace($sWhere, "", -3);
    $sWhere .= ')';
}

for ($i = 0; $i < count($sColumns); $i++) {
    if (isset($_GET['bSearchable_' . $i]) && $_GET['bSearchable_' . $i] == "true" && isset($_GET['sSearch_' . $i]) && $_GET['sSearch_' . $i] != '') {
        if ($sWhere == "") { $sWhere = "WHERE "; }
        else { $sWhere .= " AND "; }
        $sWhere .= $sColumns[$i] . " LIKE '%" . $db->getConnection()->real_escape_string($_GET['sSearch_' . $i]) . "%' ";
    }
}

if ($sWhere == "") { $sWhere .= " where "; }
else { $sWhere .= " and "; }

$dtClause = "";
if (isset($dtrange) && trim($dtrange) != "") {
    $dtarr = explode(" - ", $dtrange);
    if (count($dtarr) == 1) {
        list($dd, $mm, $yy) = explode("-", $dtarr[0]);
        $sdate = "$yy-$mm-$dd";
        $dtClause = " and i.invoice_dt >= '$sdate 00:00:00' and i.invoice_dt <= '$sdate 23:59:59' ";
    } else if (count($dtarr) == 2) {
        list($dd, $mm, $yy) = explode("-", $dtarr[0]);
        $sdate = "$yy-$mm-$dd";
        list($dd, $mm, $yy) = explode("-", $dtarr[1]);
        $edate = "$yy-$mm-$dd";
        $dtClause = " and i.invoice_dt >= '$sdate 00:00:00' and i.invoice_dt <= '$edate 23:59:59' ";
    } else {
        $dtClause = "";
    }
}

$sClause = "";
if (isset($storeid) && trim($storeid) != "" && trim($storeid) != "-1") {
    $sClause = " and i.store_id in ($storeid)";
}

$sWhere .= " i.store_id = c.id and c.is_closed=0 and c.id in (select store_id from executive_assign where exe_id=" . getCurrUser()->id . " ) $dtClause $sClause";

$sQuery = "
	select SQL_CALC_FOUND_ROWS
            c.store_name, i.invoice_no, i.invoice_dt, i.invoice_pull_date, i.invoice_status,
            if(i.invoice_status=1 and i.invoice_pull_date is not null, round(timestampdiff(second,i.invoice_dt,i.invoice_pull_date)/86400,0), null) as leadtime_days
	from it_invoices i, it_codes c
	$sWhere
	$sOrder
	$sLimit
";

$objs = $db->fetchObjectArray($sQuery);

$obj = $db->fetchObject("SELECT FOUND_ROWS() AS TOTAL_ROWS");
$iFilteredTotal = $obj ? $obj->TOTAL_ROWS : 0;

// Average leadtime for filtered rows (only received invoices).
$avgQuery = "
    select round(avg(timestampdiff(second,i.invoice_dt,i.invoice_pull_date))/86400,0) as avg_days
    from it_invoices i, it_codes c
    $sWhere and i.invoice_status=1 and i.invoice_pull_date is not null
";
$avgObj = $db->fetchObject($avgQuery);
$avgLeadTime = ($avgObj && isset($avgObj->avg_days)) ? $avgObj->avg_days : null;

$rows = array();
$iTotal = 0;
foreach ($objs as $obj) {
    $row = array();
    foreach ($aColumns as $col) {
        if ($col == 'store_name') {
            $row[] = $obj->store_name;
        } else if ($col == 'invoice_no') {
            $row[] = $obj->invoice_no;
        } else if ($col == 'invoice_dt') {
            $row[] = $obj->invoice_dt;
        } else if ($col == 'invoice_pull_date') {
            $row[] = ($obj->invoice_pull_date) ? $obj->invoice_pull_date : "-";
        } else if ($col == 'invoice_status') {
            $row[] = ($obj->invoice_status == 1) ? "Received at store" : "Intransit";
        } else if ($col == 'leadtime_days') {
            $row[] = formatLeadTimeDays($obj->leadtime_days);
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
    "avg_leadtime_days" => $avgLeadTime
);

echo json_encode($output);
?>
