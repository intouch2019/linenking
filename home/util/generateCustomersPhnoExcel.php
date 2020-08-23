<?php
require_once("../../it_config.php");
//require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";


try{
$db = new DBConn();

$objs=$db->fetchObjectArray("select phoneno from it_users where createtime >= '2014-04-01 00:00:00'; ");
//print_r($objs);
header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment;Filename=Customers_Phno.xls");

echo "<html>";
echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=Windows-1252\">";
echo "<body>";
echo "<table>";
echo "<th>Phone No</th>";

  foreach($objs as $obj){
echo "<tr>";
      foreach ($obj as $key => $value) {
        echo "<td>$value </td>";
      }
      echo "</tr>";
      //echo "<b>$obj->c.name</b> \t <u>$obj->totalitems</u> \t <u>$obj->mappeditems</u> \t \n ";
  }
echo "</table>";
echo "</body>";
echo "</html>";
}catch (Exception $xcp) {
	$xcp->getPrevious();
}


?>
