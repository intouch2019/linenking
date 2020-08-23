<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";

$db = new DBConn();
$fh = fopen($argv[1],"r");
if (!$fh) { print "Failed to open $argv[1]\n"; return; }
$count=0;
$lineno=false;$rackno=false;
$prev_item_code=false;
while(($data=fgetcsv($fh)) !== FALSE) {
$count++;
/*
BARCODE ,MRP,LINE NO ,RACK NO
10300000305,795,2,1
10300000305,795,,
10300000305,795,,
10300000305,795,,
*/
if (!is_numeric($data[0])) { continue; }
$item_code=trim($data[0]);
$mrp=trim($data[1]);
$lno=false; $rno=false;
if (count($data)>2) { $lno=trim($data[2]); }
if (count($data)>3) { $rno=trim($data[3]); }
if ($lno && $rno) {
	$lineno = $lno; if (is_numeric($lineno)) $lineno = sprintf("%03d", $lineno);
	$rackno = $rno; if (is_numeric($rackno)) $rackno = sprintf("%03d", $rackno);
}
if ($item_code == $prev_item_code) { continue; }
$prev_item_code = $item_code;
$query = "select * from it_ck_items where item_code like '%$item_code'";
$obj = $db->fetchObject($query);
if (!$obj) { continue; }
//printf("%06d:%s,%s,%s,%s,%s\n",$count,$item_code,$obj->ctg_id,$obj->design_no,$lineno,$rackno);
printf("%06d\r",$count);
$ctg_id=$db->safe($obj->ctg_id);
$design_no=$db->safe($obj->design_no);
$query = "update it_ck_designs set updatetime=now(), lineno='$lineno', rackno='$rackno' where ctg_id=$ctg_id and design_no=$design_no";
$db->execUpdate($query);
}
fclose($fh);
$db->closeConnection();
print "\n";
