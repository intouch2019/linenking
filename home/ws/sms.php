<html>
<head>
</head>
<body>
<?php
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";

extract($_GET);
if (!isset($_GET['pg']) || $pg < 0) { $pg = 0; }
$num=10;
if (isset($_GET['num'])) { $num = $_GET['num']; }
$limit = "limit ".$pg*$num;
$limit .= ", $num";
$dtclause="";
$storeid = isset($_GET['storeid']) ? $_GET['storeid'] : false;
if ($storeid) { $sClause = " where storeid=$storeid "; }

$db = new DBConn();
$query = "select * from it_smsserve $sClause order by id desc $limit";
$objs = $db->fetchObjectArray($query);
$prevpg = $pg - 1;
if (count($objs) == 0) { $nextpg = $pg; }
else { $nextpg = $pg + 1; }
?>
<a href="?pg=<?php echo $prevpg ?>">Prev</a>    <a href="?pg=<?php echo $nextpg ?>">Next</a>    
<br />
<br />
<div style="position:relative;float:left;height:500px;overflow:auto;width:100%;">
<table border=1 style="width:100%;">
<tr>
<th>Id</th>
<th>Store Id</th>
<th>Status</th>
<th>Number</th>
<th>Message</th>
<th>Timestamp</th>
</tr>
<?php
foreach ($objs as $obj) {
?>
<tr>
<td><?php echo $obj->id ?></td>
<td><?php echo $obj->storeid ?></td>
<td><?php echo $obj->status ?></td>
<td><?php echo $obj->phoneno ?></td>
<td><?php echo $obj->message ?></td>
<td><?php echo $obj->createtime ?></td>
</tr>
<?php
}
?>
</table>
</div>
</body>
</html>
