<?php
//include "checkAccess.php";
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";


extract($_POST);

if ((!isset($Cn_Num) || trim($Cn_Num) == "") ){
	print "1::Missing parameters";
	return;
}

try{
    $db = new DBConn();
            
    $qry = "update creditnote_no set cn_no=$Cn_Num ,active=0;";
    $db->execUpdate($qry);
    print "0::Successfull";
}catch(Exception $ex){
    print "1::Error".$ex->getMessage();
}
?>
