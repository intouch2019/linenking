<?php
include "../appConfig.php";
require_once "store/session_check.php";
include "ofc/php-ofc-library/open-flash-chart.php";
require_once "lib/db/DBConn.php";

$currStore = getCurrStore();
if (!$currStore) { print "User session timedout. Please login again"; return; }

$d1Clause="";
if (isset($_GET['d1'])) {
$d1Clause = " and date(bill_datetime) >= '".$_GET['d1']."' ";
}
$d2Clause="";
if (isset($_GET['d2'])) {
$d2Clause = " and date(bill_datetime) <= '".$_GET['d2']."' ";
}
if (isset($_GET['d1']) && isset($_GET['d2'])) { $_SESSION['daterange']=$_GET['d1'].",".$_GET['d2']; }
$type=1; // 1=numOrders, 2=revenue
if (isset($_GET['type'])) { $type=$_GET['type']; }

	$aColumns = array( 'bdate', 'numOrders', 'totalAmount', 'totalQuantity', 'avgPrice' );
	$sColumns = array( 'bdate' );
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
	
	$sWhere = "where storeid=$currStore->id and inactive=0 and status>0 $d1Clause $d2Clause group by bdate ";
	
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
		select SQL_CALC_FOUND_ROWS date(bill_datetime) as bdate, count(*) as numOrders, round(sum(bill_amount)) as totalAmount, sum(bill_quantity) as totalQuantity, round(sum(bill_amount)/sum(bill_quantity)) as avgPrice from it_orders 
		$sWhere
		$sHaving
		$sOrder
		$sLimit
	";
	$objs = $db->fetchObjectArray($sQuery);

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
