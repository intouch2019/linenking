<?php
require_once("../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";


$user = getCurrUser();
if (!$user || ($user->usertype != UserType::Admin && $user->usertype != UserType::CKAdmin && $user->usertype != UserType::Accounts)) { print 'You have to be logged in to run this program. Login <a href="home/login">here</a>'; return; }
$fp=fopen("tmp/StoreGSTIN_No.csv","w");
$table = "it_codes";
$db = new DBConn();
$result = $db->execQuery("select id,store_name,tally_name,gstin_no from $table where usertype=4 and is_closed=0 order by id");

if (!$result) { print "No items found. Kindly report this to Intouch"; return; }
fputs($fp,"id,Store_name,Tally_name,gstin_no\n");
while ($item = $result->fetch_object())
{
    $id = trim($item->id);
    $store_name = trim($item->store_name);
    $tally_name=trim($item->tally_name);
    $gstin_no = trim($item->gstin_no);
fputs($fp,"$id,$store_name,$tally_name,$gstin_no\n");
}

$result->close();
$db->closeConnection();
fclose($fp);
system("zip tmp/StoreGSTIN_No.zip tmp/StoreGSTIN_No.csv");
?>
<a href="tmp/StoreGSTIN_No.zip">Download GSTIN_NO_With_TallyName</a>
