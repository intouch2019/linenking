<?php
include "../appConfig.php";
require_once "store/session_check.php";
require_once "lib/db/DBConn.php";

$currStore = getCurrStore();
if (!$currStore) { print "User session timedout. Please login again"; return; }

if (isset($_SESSION['daterange'])) {
list($d1,$d2)=explode(",",$_SESSION['daterange']);
$d0 = new DateTime($d1);
$d0->sub(new DateInterval('P1D'));
$d0 = $d0->format('Y-m-d');
} else {
print "Dates not set";
return;
}
$d1Clause = " and date(bill_datetime) >= '".$d1."' ";
$d2Clause = " and date(bill_datetime) <= '".$d2."' ";

	$aColumns = array( 'itemname', 'unitssold', 'totalamount', 'openingstock', 'stockin', 'closingstock' );
	$sColumns = array( 'itemname' );
	/* Indexed column (used for fast and accurate table cardinality) */
	$sIndexColumn = "id";
	$db = new DBConn();
	
	/* 
	 * Paging
	 */
	$sLimit = "";
	if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
	{
		$sLimit = "LIMIT ".$db->getConnection()->real_escape_string( $_GET['iDisplayStart'] ).", ".
			$db->getConnection()->real_escape_string( $_GET['iDisplayLength'] );
	}
	
	
	/*
	 * Ordering
	 */
	if ( isset( $_GET['iSortCol_0'] ) )
	{
		$sOrder = "ORDER BY  ";
		for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
		{
			if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
			{
				$sOrder .= $aColumns[ intval( $_GET['iSortCol_'.$i] ) ]."
				 	".$db->getConnection()->real_escape_string( $_GET['sSortDir_'.$i] ) .", ";
			}
		}
		
		$sOrder = substr_replace( $sOrder, "", -2 );
		if ( $sOrder == "ORDER BY" )
		{
			$sOrder = "";
		}
	}
	
	$sWhere = "where r.id = rl.rawitemid and rl.orderid = o.id and o.storeid=$currStore->id and o.status > 0 $d1Clause $d2Clause and o.inactive=0 and r.id = cs1.rawitemid and cs1.closing_dt = '$d0' and r.id = cs2.rawitemid and cs2.closing_dt = '$d2' group by rl.rawitemid ";
	$sWhere = "where r.id = rl.rawitemid and rl.orderid = o.id and o.storeid=$currStore->id $d1Clause $d2Clause and r.id = cs1.rawitemid and cs1.closing_dt = '$d0' and r.id = cs2.rawitemid and cs2.closing_dt = '$d2' group by rl.rawitemid ";

	/* 
	 * Filtering
	 * NOTE this does not match the built-in DataTables filtering which does it
	 * word by word on any field. It's possible to do here, but concerned about efficiency
	 * on very large tables, and MySQL's regex functionality is very limited
	 */
	$sHaving = "";
	if ( $_GET['sSearch'] != "" )
	{
		$sHaving = "HAVING (";
		for ( $i=0 ; $i<count($sColumns) ; $i++ )
		{
			$sHaving .= $sColumns[$i]." LIKE '%".$db->getConnection()->real_escape_string( $_GET['sSearch'] )."%' OR ";
		}
		$sHaving = substr_replace( $sHaving, "", -3 );
		$sHaving .= ')';
	}
	
	/*
	 * SQL queries
	 * Get data to display
	 */
	$sQuery = "
		select SQL_CALC_FOUND_ROWS r.itemname, sum(rl.linequantity) as unitssold, round(sum(rl.linetotal),0) as totalamount, (cs1.stock_in - cs1.sales) as openingstock, (cs2.stock_in - cs1.stock_in) as stockin, (cs2.stock_in - cs2.sales) as closingstock from it_rawitems r, it_rawitemlines rl, it_orders o, it_closing_stock cs1, it_closing_stock cs2
		$sWhere
		$sHaving
		$sOrder
		$sLimit
	";
	$objs = $db->fetchObjectArray($sQuery);
	if (count($objs) == 0) {
		$sWhere = "where r.id = rl.rawitemid and rl.orderid = o.id and o.storeid=$currStore->id and o.status > 0 $d1Clause $d2Clause and o.inactive=0 group by rl.rawitemid ";
		$sQuery = "
		select SQL_CALC_FOUND_ROWS r.itemname, sum(rl.linequantity) as unitssold, round(sum(rl.linetotal),0) as totalamount, '-' as openingstock, '-' as stockin, '-' as closingstock from it_rawitems r, it_rawitemlines rl, it_orders o
		$sWhere
		$sHaving
		$sOrder
		$sLimit
		";
		$objs = $db->fetchObjectArray($sQuery);
	}
//	require_once "lib/logger/clsLogger.php";
//	$logger = new clsLogger();
//	$logger->logInfo($sQuery);
	
	/* Data set length after filtering */
	$sQuery = "
		SELECT FOUND_ROWS() AS TOTAL_ROWS
	";
	$obj = $db->fetchObject($sQuery);
	$iFilteredTotal = $obj->TOTAL_ROWS;
	
	$rows = array(); $iTotal=0;
	foreach ($objs as $obj)
	{
		$row = array();
		$stocklevel = 0;
		for ( $i=0 ; $i<count($aColumns) ; $i++ )
		{
			if ( $aColumns[$i] != ' ' )
			{
				/* General output */
				$row[] = $obj->$aColumns[$i];
			}
		}
		$rows[] = $row;
		$iTotal++;
	}
	
	$db->closeConnection();
	/*
	 * Output
	 */
	$output = array(
		"sEcho" => intval($_GET['sEcho']),
		"iTotalRecords" => $iTotal,
		"iTotalDisplayRecords" => $iFilteredTotal,
		"aaData" => $rows
	);
	
	echo json_encode( $output );
?>
