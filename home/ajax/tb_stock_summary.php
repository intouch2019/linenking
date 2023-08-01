<?php
include "../../it_config.php";
require_once "session_check.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";

$currStore = getCurrUser();


$aColumns = array( 'id', 'store_name','min_stock_level', 'stock_datetime','stock_value','stock_qty','stock_intransit');
$sColumns = array('s.id', 'c.store_name', 'c.min_stock_level','s.stock_datetime','s.stock_value','s.stock_qty','s.stock_intransit');
/* Indexed column (used for fast and accurate table cardinality) */
$db = new DBConn();

$dtrange = isset($_GET['dtrange']) ? $_GET['dtrange'] : false;
$storeid = isset($_GET['storeid']) ? $_GET['storeid'] : false;
//error_log("\nDT RANGE : ".$dtrange."\n",3,"tmp.txt");
/* 
 * Paging
 */
$sLimit = "";
if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
{
	$sLimit = " LIMIT ".$db->getConnection()->real_escape_string( $_GET['iDisplayStart'] ).", ".
		$db->getConnection()->real_escape_string( $_GET['iDisplayLength'] );
}


/*
 * Ordering
 */
$sOrder = "";
if ( isset( $_GET['iSortCol_0'] ) )
{
	$sOrder = " ORDER BY  ";
	for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
	{
		if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
		{
			$sOrder .= $aColumns[ intval( $_GET['iSortCol_'.$i] ) ]."
			 	".$db->getConnection()->real_escape_string( $_GET['sSortDir_'.$i] ) .", ";
		}
	}
	
	$sOrder = substr_replace( $sOrder, "", -2 );
	if ( $sOrder == " ORDER BY " )
	{
		$sOrder = "";
	}
}


/* 
 * Filtering
 * NOTE this does not match the built-in DataTables filtering which does it
 * word by word on any field. It's possible to do here, but concerned about efficiency
 * on very large tables, and MySQL's regex functionality is very limited
 */

$sWhere = "";
if ( isset($_GET['sSearch']) && $_GET['sSearch'] != "" )
{
	$sWhere = "WHERE (";
	for ( $i=0 ; $i<count($sColumns) ; $i++ )
	{
		$sWhere .= $sColumns[$i]." LIKE '%".$db->getConnection()->real_escape_string( $_GET['sSearch'] )."%' OR ";
	}
	$sWhere = substr_replace( $sWhere, "", -3 );
	$sWhere .= ')';
}

/* Individual column filtering */
for ( $i=0 ; $i<count($sColumns) ; $i++ )
{
	if ( isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == "true" && isset($_GET['sSearch_'.$i]) && $_GET['sSearch_'.$i] != '' )
	{
		if ( $sWhere == "" )
		{
			$sWhere = "WHERE ";
		}
		else
		{
			$sWhere .= " AND ";
		}
		$sWhere .= $sColumns[$i]." LIKE '%".$db->getConnection()->real_escape_string($_GET['sSearch_'.$i])."%' ";
	}
}

/*
 * SQL queries
 * Get data to display
 */

if($sWhere==""){
    $sWhere .= " where ";
}else{
    $sWhere .= " and ";
}
if(isset($dtrange) && trim($dtrange)!=""){
    $dtClause = "" ;
    $dtarr = explode(" - ", $dtrange);
    //$_SESSION['storeid'] = $this->storeidreport;
    if (count($dtarr) == 1) {
            list($dd,$mm,$yy) = explode("-",$dtarr[0]);
            $sdate = "$yy-$mm-$dd";		
            $dtClause = " and s.stock_datetime >= '$sdate 00:00:00' and s.stock_datetime <= '$sdate 23:59:59' ";
    } else if (count($dtarr) == 2) {
            list($dd,$mm,$yy) = explode("-",$dtarr[0]);
            $sdate = "$yy-$mm-$dd";
            list($dd,$mm,$yy) = explode("-",$dtarr[1]);
            $edate = "$yy-$mm-$dd";		
            $dtClause = " and s.stock_datetime >= '$sdate 00:00:00' and s.stock_datetime <= '$edate 23:59:59' ";
    } else {
            $dtClause = "";
    }
}else{ $dtClause=""; }

if(isset($storeid) && trim($storeid)!="" && trim($storeid) != "-1"){
   $sClause=" and s.store_id in ($storeid)";
   
}else{ $sClause="" ;}

$sWhere .= " s.store_id = c.id and c.id in (select store_id from executive_assign where exe_id=".getCurrUser()->id." )  $dtClause $sClause"; 
$sQuery = "
	select SQL_CALC_FOUND_ROWS s.*,c.store_name,c.min_stock_level
	from it_store_stock_summary s, it_codes c
	$sWhere 
	$sOrder
	$sLimit
";
//error_log("\nSS query: ".$sQuery."\n",3,"tmp.txt");
$objs = $db->fetchObjectArray($sQuery);

/* Data set length after filtering */
$sQuery = "
	SELECT FOUND_ROWS() AS TOTAL_ROWS
";
$obj = $db->fetchObject($sQuery);
$iFilteredTotal = $obj->TOTAL_ROWS;

$rows = array(); $iTotal=0;
foreach ($objs as $obj)
{       $tot_stk = 0;
	$row = array();
	for ( $i=0 ; $i<count($aColumns) ; $i++ )
	{
             if ($aColumns[$i] == 'id') {
                 $row[] = $obj->id;
             }else if($aColumns[$i] == 'store_name'){
                 $row[] = $obj->store_name;
             }else if($aColumns[$i] == 'min_stock_level'){
                 $str = $obj->min_stock_level;
                 if(trim($str)!=""){
                     $row[] = $obj->min_stock_level;
                 }else{
                     $row[] = "-";
                 }
             }else if($aColumns[$i] == 'stock_datetime'){
                 $row[] = $obj->stock_datetime;
             }else if($aColumns[$i] == 'stock_value'){
                 $row[] = $obj->stock_value;
             }else if($aColumns[$i] == 'stock_qty'){
                 $row[] = $obj->stock_qty;
             }else if($aColumns[$i] == 'stock_intransit'){
                 $row[] = $obj->stock_intransit;
             }else{
                 $row[] = "-";
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
	//"sEcho" => intval($_GET['sEcho']),
	"iTotalRecords" => $iTotal,
	"iTotalDisplayRecords" => $iFilteredTotal,
	"aaData" => $rows
);

echo json_encode( $output );
?>